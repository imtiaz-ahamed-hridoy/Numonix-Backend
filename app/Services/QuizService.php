<?php

namespace App\Services;

use App\Models\GuestDevice;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use App\Repositories\Interfaces\QuizQuestionRepositoryInterface;
use App\Repositories\Interfaces\QuizRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service layer for lesson-based quiz operations.
 *
 * Manages quiz retrieval, answer submission, scoring, review,
 * retry logic, and per-question answer checking.
 */
class QuizService
{
    public function __construct(
        private QuizRepositoryInterface $quizRepository,
        private QuizQuestionRepositoryInterface $questionRepository,
        private QuizAttemptRepositoryInterface $attemptRepository,
        private LearningRepositoryInterface $learningRepository,
    ) {}

    /**
     * Retrieve a quiz with its questions for a given learning item.
     *
     * @param  User|null  $user
     * @param  GuestDevice|null  $guestDevice
     * @return array{quiz: array, questions: array}
     */
    public function getQuizByLearning(int $learningId, $user, $guestDevice): array
    {
        return $this->buildQuizData($learningId, $user, $guestDevice);
    }

    /**
     * Submit answers for a lesson quiz, score them, persist the attempt,
     * send a notification, and return the result with action buttons.
     *
     * @return array{result: array, actions: array}
     *
     * @throws NotFoundHttpException if no quiz is published for the learning.
     */
    public function submitQuiz(int $learningId, array $validated, $user, Request $request): array
    {
        $quiz = $this->quizRepository->getPublishedByLearningWithQuestions($learningId);
        if (! $quiz) {
            throw new NotFoundHttpException('No quiz found for this learning.');
        }

        $questions = $quiz->questions->keyBy('id');
        $totalQuestions = $questions->count();
        $correctAnswersCount = 0;
        $attemptedQuestionsCount = 0;
        $answersData = [];

        foreach ($validated['answers'] as $submitted) {
            $questionId = $submitted['question_id'];
            $userAnswerRaw = $submitted['answer'] ?? null;

            if (! isset($questions[$questionId])) {
                continue;
            }

            $question = $questions[$questionId];
            $userAnswer = $this->normalizeAnswer($userAnswerRaw);

            if (! is_null($userAnswer)) {
                $attemptedQuestionsCount++;
            }

            $isCorrect = ! is_null($userAnswer) && $this->compareAnswer($question, $userAnswer);

            if ($isCorrect) {
                $correctAnswersCount++;
            }

            $answersData[] = [
                'question_id' => $questionId,
                'type' => $question->type,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
            ];
        }

        $score = $totalQuestions > 0
            ? (int) round(($correctAnswersCount / $totalQuestions) * 100)
            : 0;

        $userId = $user?->id;

        $attemptData = [
            'quiz_id' => $quiz->id,
            'total_questions' => $totalQuestions,
            'attempted_questions' => $attemptedQuestionsCount,
            'correct_answers' => $correctAnswersCount,
            'score' => $score,
            'answers_data' => $answersData,
        ];

        if ($user) {
            $attemptData['user_id'] = $userId;
        } else {
            $guestDevice = $this->getGuestDevice($request);
            if ($guestDevice) {
                $attemptData['guest_device_id'] = $guestDevice->id;
                $guestDevice->markActivity();
            }
        }

        $attempt = $this->attemptRepository->create($attemptData);

        if ($user) {
            $user->notify(new AppNotification(
                'Quiz Completed!',
                "You scored {$score}% on the quiz.",
                ['quiz_id' => $quiz->id, 'score' => $score],
                'quiz_completed'
            ));
        }

        $accuracy = $attemptedQuestionsCount > 0
            ? (int) round(($correctAnswersCount / $attemptedQuestionsCount) * 100)
            : 0;

        $resultTitle = $correctAnswersCount === $totalQuestions ? 'Perfect!' : 'Results';

        $actions = [
            [
                'key' => 'review_answers',
                'label' => 'Review Answers',
                'style' => 'primary',
                'api_endpoint' => '/quiz/review/'.$attempt->attempt_id,
                'method' => 'GET',
            ],
            [
                'key' => 'lesson_summary',
                'label' => 'Lesson Summary',
                'style' => 'outline',
                'api_endpoint' => '/learnings/summary/'.$learningId,
                'method' => 'GET',
            ],
        ];

        if ($user) {
            $actions[] = [
                'key' => 'retry_quiz',
                'label' => 'Retry Quiz',
                'style' => 'outline',
                'api_endpoint' => '/quiz/retry/'.$learningId,
                'method' => 'POST',
            ];

            $actions[] = [
                'key' => 'continue',
                'label' => 'Continue ->',
                'style' => 'outline',
                'api_endpoint' => '/learnings/next',
                'method' => 'POST',
            ];
        }

        return [
            'result' => [
                'title' => $resultTitle,
                'attempt_id' => $attempt->attempt_id,
                'score' => $score,
                'accuracy' => $accuracy,
                'correct_answers' => $correctAnswersCount,
                'total_questions' => $totalQuestions,
                'attempted_questions' => $attemptedQuestionsCount,
                'summary_text' => 'You answered '.$correctAnswersCount.' out of '.$totalQuestions.' correctly',
                'is_guest' => $user === null,
            ],
            'actions' => $actions,
        ];
    }

    /**
     * Check whether a single answer is correct (non-persistent, used for real-time feedback).
     *
     * @return array{question_id: int, is_correct: bool, correct_answer: mixed, explanation: string|null}
     */
    public function checkAnswer(int $questionId, $answer): array
    {
        $question = $this->questionRepository->findOrFail($questionId);
        $userAnswer = $this->normalizeAnswer($answer);
        $isCorrect = $this->compareAnswer($question, $userAnswer);

        return [
            'question_id' => $question->id,
            'is_correct' => $isCorrect,
            'correct_answer' => $question->correct_answer,
            'explanation' => $question->explanation,
        ];
    }

    /**
     * Retrieve a full review of a previous quiz attempt, including per-question
     * results and correct-answer explanations.
     *
     * @return array{title: string, learning_id: int, learning_title: string|null, score: int, correct_answers: int, total_questions: int, items: array, actions: array}
     */
    public function getReview(int $attemptId, $user, $guestDevice): array
    {
        $attempt = $this->findAttempt($attemptId, $user, $guestDevice);

        $quiz = $this->quizRepository->findWithQuestions($attempt->quiz_id);

        $answersData = collect($attempt->answers_data ?? [])->keyBy('question_id');

        $reviewItems = $quiz->questions->values()->map(function ($question, $index) use ($answersData) {
            $answerEntry = $answersData->get($question->id);
            $isCorrect = $answerEntry['is_correct'] ?? false;
            $userAnswer = $answerEntry['user_answer'] ?? null;

            $correctAnswerDisplay = $this->formatAnswerForDisplay($question);

            return [
                'number' => $index + 1,
                'question_id' => $question->id,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'image_url' => $question->image_url,
                'image_size' => $question->image_size ?? ($question->image_url ? 'medium' : null),
                'is_correct' => $isCorrect,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'correct_answer_display' => $correctAnswerDisplay,
                'explanation' => $question->explanation,
                'options' => $question->options,
            ];
        })->all();

        $actions = [
            [
                'key' => 'retry_quiz',
                'label' => 'Retry Quiz',
                'style' => 'outline',
                'api_endpoint' => '/quiz/retry/'.$quiz->learning_id,
                'method' => 'POST',
            ],
            [
                'key' => 'lesson_summary',
                'label' => 'Lesson Summary',
                'style' => 'primary',
                'api_endpoint' => '/learnings/summary/'.$quiz->learning_id,
                'method' => 'GET',
            ],
        ];

        return [
            'title' => 'Review Answers',
            'learning_id' => $quiz->learning_id,
            'learning_title' => $quiz->learning?->title,
            'score' => $attempt->score,
            'correct_answers' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'items' => $reviewItems,
            'actions' => $actions,
        ];
    }

    /**
     * Retrieve the latest review for a given learning item's quiz.
     *
     * @throws NotFoundHttpException if no quiz or no attempt is found.
     */
    public function getReviewByLearning(int $learningId, $user, $guestDevice): array
    {
        $quiz = $this->quizRepository->getPublishedByLearning($learningId);
        if (! $quiz) {
            throw new NotFoundHttpException('No quiz found for this learning.');
        }

        $attempt = null;
        if ($user) {
            $attempt = $this->attemptRepository->findLatestByQuizForUser($quiz->id, $user->id);
        } elseif ($guestDevice) {
            $attempt = $this->attemptRepository->findLatestByQuizForGuest($quiz->id, $guestDevice->id);
        }

        if (! $attempt) {
            throw new NotFoundHttpException('No quiz attempt found. Please take the quiz first.');
        }

        return $this->getReview($attempt->attempt_id, $user, $guestDevice);
    }

    /**
     * Delete all previous attempts for a quiz and return fresh quiz data for retrying.
     *
     * @return array{quiz: array, questions: array}
     *
     * @throws NotFoundHttpException if no quiz is published.
     */
    public function retryQuiz(int $learningId, $user, $guestDevice): array
    {
        $quiz = $this->quizRepository->getPublishedByLearningWithQuestions($learningId);
        if (! $quiz) {
            throw new NotFoundHttpException('No quiz found for this learning.');
        }

        if ($user) {
            $this->attemptRepository->deleteForUser($quiz->id, $user->id);
        } elseif ($guestDevice) {
            $this->attemptRepository->deleteForGuest($quiz->id, $guestDevice->id);
        }

        return $this->buildQuizData($learningId, $user, $guestDevice, $quiz);
    }

    /**
     * Retrieve raw attempt details for a specific quiz attempt.
     *
     * @return array{attempt_id: int, quiz_id: int, score: int, correct_answers: int, total_questions: int, attempted_questions: int, answers_data: array}
     */
    public function getAttemptDetails(int $attemptId, $user, $guestDevice): array
    {
        $attempt = $this->findAttempt($attemptId, $user, $guestDevice);

        return [
            'attempt_id' => $attempt->attempt_id,
            'quiz_id' => $attempt->quiz_id,
            'score' => $attempt->score,
            'correct_answers' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'attempted_questions' => $attempt->attempted_questions,
            'answers_data' => $attempt->answers_data,
        ];
    }

    /**
     * Retrieve all published quiz questions across all quizzes.
     *
     * @return array<int, Quiz>
     */
    public function getAllQuestions(): array
    {
        return $this->quizRepository->getAllPublishedWithQuestions()->all();
    }

    /* ------------------------------------------------------------------
     * Private Helpers
     * ----------------------------------------------------------------*/

    /**
     * Build the full quiz-data payload including questions and previous attempt stats.
     *
     * @param  User|null  $user
     * @param  GuestDevice|null  $guestDevice
     * @param  Quiz|null  $prefetchedQuiz  Optional pre-loaded quiz model to avoid duplicate queries.
     * @return array{quiz: array, questions: array}
     */
    private function buildQuizData(int $learningId, $user, $guestDevice, $prefetchedQuiz = null): array
    {
        $learning = $this->learningRepository->findOrFail($learningId);

        $quiz = $prefetchedQuiz ?? $this->quizRepository->getPublishedByLearningWithQuestions($learningId);

        if (! $quiz) {
            throw new NotFoundHttpException('No quiz found for this learning.');
        }

        $allAttempts = $this->getAttemptsForQuiz($quiz->id, $user, $guestDevice);

        $bestScore = $allAttempts->max('score');
        $bestAttempt = $allAttempts->sortByDesc('score')->first();
        $attemptsCount = $allAttempts->count();
        $latestAttempt = $allAttempts->sortByDesc('attempt_id')->first();

        $bestAccuracy = 0;
        if ($bestAttempt && $bestAttempt->attempted_questions > 0) {
            $bestAccuracy = (int) round(($bestAttempt->correct_answers / $bestAttempt->attempted_questions) * 100);
        }

        $questions = $quiz->questions->values()->map(function ($question, $index) {
            return [
                'id' => $question->id,
                'number' => $index + 1,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'image_url' => $question->image_url,
                'image_size' => $question->image_size ?? ($question->image_url ? 'medium' : null),
                'options' => $question->options,
            ];
        });

        return [
            'quiz' => [
                'id' => $quiz->id,
                'learning_id' => $quiz->learning_id,
                'learning_title' => $learning->title,
                'status' => $quiz->status,
                'total_questions' => $questions->count(),
                'has_previous_attempt' => $attemptsCount > 0,
                'attempts_count' => $attemptsCount,
                'best_score' => $bestScore !== null ? (int) round($bestScore) : null,
                'best_accuracy' => $bestAccuracy,
                'previous_score' => $latestAttempt?->score ? (int) round($latestAttempt->score) : null,
                'is_guest' => $user === null,
            ],
            'questions' => $questions->all(),
        ];
    }

    /**
     * Retrieve all attempts for a quiz belonging to the given user or guest.
     *
     * @return Collection<int, QuizAttempt>
     */
    private function getAttemptsForQuiz(int $quizId, $user, $guestDevice): Collection
    {
        if ($user) {
            return $this->attemptRepository->findByQuizForUser($quizId, $user->id);
        }

        if ($guestDevice) {
            return $this->attemptRepository->findByQuizForGuest($quizId, $guestDevice->id);
        }

        return collect();
    }

    /**
     * Locate an attempt by ID, scoped to the authenticated user or guest device.
     *
     * @throws NotFoundHttpException
     */
    private function findAttempt(int $attemptId, $user, $guestDevice): QuizAttempt
    {
        $attempt = null;

        if ($user) {
            $attempt = $this->attemptRepository->findAttemptByIdForUser($attemptId, $user->id);
        } elseif ($guestDevice) {
            $attempt = $this->attemptRepository->findAttemptByIdForGuest($attemptId, $guestDevice->id);
        }

        if (! $attempt) {
            throw new NotFoundHttpException('Attempt not found.');
        }

        return $attempt;
    }

    /**
     * Extract the guest device from the request input (injected by middleware).
     */
    private function getGuestDevice(Request $request): ?GuestDevice
    {
        return $request->input('_guest_device');
    }

    /**
     * Normalize a raw answer value: decode JSON strings to arrays.
     *
     * @param  mixed  $answer
     * @return mixed
     */
    private function normalizeAnswer($answer)
    {
        if (is_null($answer)) {
            return null;
        }

        if (is_string($answer)) {
            $decoded = json_decode($answer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $answer;
    }

    /**
     * Compare a user answer against the correct answer, handling MCQ and tap_order types.
     *
     * @param  mixed  $userAnswer
     */
    private function compareAnswer(QuizQuestion $question, $userAnswer): bool
    {
        $correctAnswer = $question->correct_answer;

        switch ($question->type) {
            case 'mcq':
                $u = is_array($userAnswer) && count($userAnswer) === 1 ? reset($userAnswer) : $userAnswer;
                $c = is_array($correctAnswer) && count($correctAnswer) === 1 ? reset($correctAnswer) : $correctAnswer;

                if (is_array($u) || is_array($c)) {
                    return json_encode($u) === json_encode($c);
                }

                return (string) $u === (string) $c;

            case 'tap_order':
                if (! is_array($userAnswer) || ! is_array($correctAnswer)) {
                    return false;
                }

                return array_values(array_map('strval', $userAnswer))
                    === array_values(array_map('strval', $correctAnswer));

            default:
                return false;
        }
    }

    /**
     * Format the correct answer into a human-readable display string based on question type.
     */
    private function formatAnswerForDisplay(QuizQuestion $question): string
    {
        $correct = $question->correct_answer;
        $options = $question->options;

        switch ($question->type) {
            case 'mcq':
                if (is_numeric($correct) && is_array($options) && isset($options[(int) $correct])) {
                    return (string) $options[(int) $correct];
                }
                if (is_array($correct) && count($correct) === 1) {
                    $idx = reset($correct);
                    if (is_numeric($idx) && is_array($options) && isset($options[(int) $idx])) {
                        return (string) $options[(int) $idx];
                    }
                }

                return is_array($correct) ? implode(', ', $correct) : (string) $correct;

            case 'tap_order':
                if (is_array($correct) && is_array($options)) {
                    $ordered = array_map(function ($idx) use ($options) {
                        return $options[(int) $idx] ?? $idx;
                    }, $correct);

                    return implode(' -> ', $ordered);
                }

                return is_array($correct) ? implode(' -> ', $correct) : (string) $correct;

            default:
                return is_array($correct) ? json_encode($correct) : (string) $correct;
        }
    }
}
