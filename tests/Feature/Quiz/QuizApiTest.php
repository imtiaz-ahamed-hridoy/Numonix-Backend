<?php

namespace Tests\Feature\Quiz;

use App\Models\GuestDevice;
use App\Models\Learning;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\GuestFingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Comprehensive end-to-end test suite for Quiz APIs.
 *
 * Covers:
 *   1. Guest flow: byLearning → submit → verify best_score, best_accuracy, attempts_count
 *   2. Authenticated user flow (same sequence via Sanctum)
 *   3. Accuracy consistency in submit response (correct/attempted * 100)
 *   4. Best-score tracking across multiple attempts (higher replaces, lower doesn't)
 *   5. Attempts_count increments correctly
 *   6. Review endpoint verification (answers match submitted data)
 *   7. Attempt-details endpoint verification
 *   8. Retry flow (deletes old attempts, returns fresh state)
 *   9. Edge cases: missing headers, invalid IDs, empty answers
 *  10. Guest / authenticated user data isolation
 */
class QuizApiTest extends TestCase
{
    use RefreshDatabase;

    private GuestFingerprintService $fingerprintService;
    private User $user;
    private Learning $learning;
    private Quiz $quiz;
    private int $totalQuestions;
    private array $questions;

    // Guest fixtures
    private string $guestDeviceId;
    private string $guestToken;
    private string $guestFingerprint;
    private array $guestHeaders;
    private GuestDevice $guestDevice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fingerprintService = app(GuestFingerprintService::class);

        // ── Seed quiz data ───────────────────────────────────────────────
        $this->learning = Learning::create([
            'title'        => 'Test Learning',
            'subtitle'     => 'Test subtitle',
            'slug'         => Str::slug('Test Learning Quiz'),
            'category'     => 'lesson',
            'read_time'    => '5 min read',
            'summary'      => 'Test summary',
            'is_published' => true,
            'status'       => 'active',
        ]);

        $this->quiz = Quiz::create([
            'learning_id' => $this->learning->id,
            'status'      => 'published',
        ]);

        // Create 5 MCQ questions with known correct answers
        $this->totalQuestions = 5;
        $this->questions = [];
        for ($i = 1; $i <= $this->totalQuestions; $i++) {
            $correctIndex = ($i - 1) % 4;
            $q = QuizQuestion::create([
                'quiz_id'        => $this->quiz->id,
                'type'           => 'mcq',
                'question_text'  => "Quiz question {$i}.",
                'options'        => ['Option A', 'Option B', 'Option C', 'Option D'],
                'correct_answer' => $correctIndex,
                'explanation'    => "The correct answer was option {$correctIndex}.",
            ]);
            $this->questions[] = $q;
        }

        // ── Create a reusable authenticated user ─────────────────────────
        $this->user = User::factory()->create(['subscription_tier' => 'tier_1']);

        // ── Create a reusable guest device ───────────────────────────────
        $this->guestDeviceId        = 'test_quiz_device_' . uniqid();
        $this->guestToken           = 'test_quiz_token_' . uniqid();
        $this->guestFingerprint     = $this->fingerprintService->compute($this->guestDeviceId, $this->guestToken);

        $this->guestDevice = GuestDevice::create([
            'device_id'         => $this->guestDeviceId,
            'token'             => $this->guestToken,
            'guest_fingerprint' => $this->guestFingerprint,
        ]);

        $this->guestHeaders = [
            'X-Guest-Device-Id'   => $this->guestDeviceId,
            'X-Guest-Token'       => $this->guestToken,
            'X-Guest-Fingerprint' => $this->guestFingerprint,
        ];
    }

    /* ==================================================================
     * HELPERS
     * ================================================================*/

    /**
     * Build correct answers for all questions in the seeded quiz.
     */
    private function correctAnswers(): array
    {
        $answers = [];
        foreach ($this->questions as $q) {
            $answers[] = [
                'question_id' => $q->id,
                'answer'      => $q->correct_answer,
            ];
        }
        return $answers;
    }

    /**
     * Build answers where N are correct and the rest are wrong.
     */
    private function partialAnswers(int $correctCount): array
    {
        $answers = [];
        foreach ($this->questions as $i => $q) {
            if ($i < $correctCount) {
                $answers[] = ['question_id' => $q->id, 'answer' => $q->correct_answer];
            } else {
                $wrongOption = ((int) $q->correct_answer + 1) % 4;
                $answers[] = ['question_id' => $q->id, 'answer' => $wrongOption];
            }
        }
        return $answers;
    }

    /* ==================================================================
     * GROUP 1: GUEST FLOW — byLearning
     * ================================================================*/

    /** @test */
    public function guest_by_learning_returns_quiz_with_initial_zero_stats(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");

        $response->assertStatus(200);
        $quizData = $response->json('data.quiz');

        $this->assertNotNull($quizData, 'Quiz data should not be null');
        $this->assertEquals($this->quiz->id, $quizData['id']);
        $this->assertEquals($this->learning->title, $quizData['learning_title']);
        $this->assertEquals($this->totalQuestions, $quizData['total_questions']);

        // Initial state: no attempts
        $this->assertFalse($quizData['has_previous_attempt'], 'No previous attempt initially');
        $this->assertEquals(0, $quizData['attempts_count'], 'Attempts count should be 0 initially');
        $this->assertNull($quizData['best_score'], 'Best score should be null initially');
        $this->assertEquals(0, $quizData['best_accuracy'], 'Best accuracy should be 0 initially');
        $this->assertNull($quizData['previous_score'], 'Previous score should be null initially');
        $this->assertTrue($quizData['is_guest'], 'Should be marked as guest');

        // Questions should not include correct_answer
        $questions = $response->json('data.questions');
        $this->assertCount($this->totalQuestions, $questions);
        $this->assertArrayNotHasKey('correct_answer', $questions[0],
            'Questions should NOT expose correct_answer in byLearning response');
    }

    /** @test */
    public function guest_by_learning_returns_deduped_question_fields(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");

        $response->assertStatus(200);
        $question = $response->json('data.questions.0');

        // Verify no duplicate 'id' key (the controller was fixed to remove duplicate)
        $keys = array_keys($question);
        $idKeys = array_filter($keys, fn($k) => $k === 'id');
        $this->assertCount(1, $idKeys, 'Should only have one "id" key (was duplicated before fix)');
    }

    /** @test */
    public function guest_by_learning_returns_question_image_fields(): void
    {
        $this->questions[0]->update([
            'image_url' => 'https://cdn.example.test/quizzes/question-1.webp',
            'image_size' => 'small',
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");

        $response->assertStatus(200);
        $question = $response->json('data.questions.0');

        $this->assertSame('https://cdn.example.test/quizzes/question-1.webp', $question['image_url']);
        $this->assertSame('small', $question['image_size']);
    }

    /* ==================================================================
     * GROUP 2: GUEST FLOW — submit accuracy & score
     * ================================================================*/

    /** @test */
    public function guest_submit_correct_returns_100_accuracy(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->correctAnswers(),
            ]);

        $response->assertStatus(200);
        $result = $response->json('data.result');

        $this->assertEquals(100, $result['accuracy'], 'Accuracy should be 100% for all correct');
        $this->assertEquals(100, $result['score'], 'Score should be 100');
        $this->assertEquals($this->totalQuestions, $result['correct_answers'], 'All answers correct');
        $this->assertEquals($this->totalQuestions, $result['attempted_questions'], 'All questions attempted');
        $this->assertEquals($this->totalQuestions, $result['total_questions'], 'Total questions match');
        $this->assertTrue($result['is_guest'], 'Should be marked as guest');
    }

    /** @test */
    public function guest_submit_partial_returns_correct_accuracy(): void
    {
        // 3/5 correct = 60% score, 60% accuracy
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        $response->assertStatus(200);
        $result = $response->json('data.result');

        $this->assertEquals(60, $result['accuracy'], 'Accuracy should be 60% (3/5)');
        $this->assertEquals(60, $result['score'], 'Score should be 60');
        $this->assertEquals(3, $result['correct_answers'], '3 correct answers');
    }

    /** @test */
    public function guest_submit_zero_correct_returns_zero_accuracy(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(0),
            ]);

        $response->assertStatus(200);
        $result = $response->json('data.result');

        $this->assertEquals(0, $result['accuracy'], 'Accuracy should be 0%');
        $this->assertEquals(0, $result['score'], 'Score should be 0');
        $this->assertEquals(0, $result['correct_answers'], '0 correct answers');
    }

    /** @test */
    public function guest_submit_displays_perfect_title(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->correctAnswers(),
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Perfect!', $response->json('data.result.title'));
    }

    /** @test */
    public function guest_submit_displays_results_title_when_not_perfect(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Results', $response->json('data.result.title'));
    }

    /* ==================================================================
     * GROUP 3: BEST SCORE & ATTEMPTS COUNT TRACKING
     * ================================================================*/

    /** @test */
    public function best_score_tracks_highest_across_multiple_attempts(): void
    {
        // Attempt 1: 60%
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        // Attempt 2: 100% — becomes best
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->correctAnswers(),
            ]);

        // Attempt 3: 40% — should NOT replace best
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(2),
            ]);

        // Verify byLearning shows best score = 100
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");

        $response->assertStatus(200);
        $quizData = $response->json('data.quiz');

        $this->assertEquals(100, $quizData['best_score'], 'Best score should be 100 (max of 60, 100, 40)');
        $this->assertEquals(100, $quizData['best_accuracy'], 'Best accuracy should use best attempt (100%)');
        $this->assertEquals(3, $quizData['attempts_count'], 'Attempts count should be 3');
        $this->assertTrue($quizData['has_previous_attempt'], 'Should have previous attempt');
        $this->assertNotNull($quizData['previous_score'], 'Previous score should not be null');
    }

    /** @test */
    public function higher_score_replaces_lower_best_score(): void
    {
        // Attempt 1: 40%
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(2),
            ]);

        // Verify best = 40
        $resp1 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(40, $resp1->json('data.quiz.best_score'), 'Best should be 40 after first attempt');

        // Attempt 2: 80%
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(4),
            ]);

        // Verify best = 80 (higher)
        $resp2 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(80, $resp2->json('data.quiz.best_score'), 'Best should update to 80');
    }

    /** @test */
    public function lower_score_does_not_replace_higher_best_score(): void
    {
        // Attempt 1: 100%
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->correctAnswers(),
            ]);

        // Attempt 2: 60% (lower)
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        // Verify best remains 100
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");

        $this->assertEquals(100, $response->json('data.quiz.best_score'), 'Best should NOT decrease');
        $this->assertEquals(100, $response->json('data.quiz.best_accuracy'), 'Accuracy should use best attempt');
    }

    /** @test */
    public function attempts_count_increments_with_each_submit(): void
    {
        // Initial: 0 attempts
        $resp0 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(0, $resp0->json('data.quiz.attempts_count'));

        // Attempt 1
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        $resp1 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(1, $resp1->json('data.quiz.attempts_count'));

        // Attempt 2
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(4),
            ]);

        $resp2 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(2, $resp2->json('data.quiz.attempts_count'));

        // Attempt 3
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->correctAnswers(),
            ]);

        $resp3 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(3, $resp3->json('data.quiz.attempts_count'));
    }

    /* ==================================================================
     * GROUP 4: AUTHENTICATED USER FLOW
     * ================================================================*/

    /** @test */
    public function authenticated_user_can_submit_and_see_stats(): void
    {
        Sanctum::actingAs($this->user);

        // Submit with correct answers
        $submitRes = $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);

        $submitRes->assertStatus(200);
        $result = $submitRes->json('data.result');
        $this->assertEquals(100, $result['accuracy']);
        $this->assertEquals(100, $result['score']);
        $this->assertFalse($result['is_guest'], 'Authenticated user should not be guest');

        // Verify byLearning
        $byLearningRes = $this->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $byLearningRes->assertStatus(200);
        $quizData = $byLearningRes->json('data.quiz');
        $this->assertEquals(100, $quizData['best_score']);
        $this->assertEquals(100, $quizData['best_accuracy']);
        $this->assertEquals(1, $quizData['attempts_count']);
        $this->assertFalse($quizData['is_guest'], 'Should not be marked as guest');
    }

    /** @test */
    public function authenticated_user_multiple_attempts_tracked_independently(): void
    {
        Sanctum::actingAs($this->user);

        // Attempt 1: 60%
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(3),
        ]);

        // Attempt 2: 100%
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);

        // Best should be 100
        $response = $this->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(100, $response->json('data.quiz.best_score'));
        $this->assertEquals(2, $response->json('data.quiz.attempts_count'));
    }

    /* ==================================================================
     * GROUP 5: REVIEW ENDPOINT
     * ================================================================*/

    /** @test */
    public function review_returns_submitted_answers_correctly(): void
    {
        Sanctum::actingAs($this->user);

        $submitRes = $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(3),
        ]);

        $attemptId = $submitRes->json('data.result.attempt_id');

        // Verify review endpoint
        $reviewRes = $this->getJson("/api/quiz/review/{$attemptId}");
        $reviewRes->assertStatus(200);

        $this->assertEquals(60, $reviewRes->json('data.score'));
        $this->assertEquals(3, $reviewRes->json('data.correct_answers'));
        $this->assertEquals($this->totalQuestions, $reviewRes->json('data.total_questions'));

        // Each question should have is_correct field
        $items = $reviewRes->json('data.items');
        $this->assertCount($this->totalQuestions, $items);
        $this->assertArrayHasKey('is_correct', $items[0]);
        $this->assertArrayHasKey('user_answer', $items[0]);
        $this->assertArrayHasKey('correct_answer', $items[0]);
        $this->assertArrayHasKey('explanation', $items[0]);
    }

    /** @test */
    public function review_by_learning_returns_latest_attempt(): void
    {
        Sanctum::actingAs($this->user);

        // First attempt
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(2),
        ]);

        // Second attempt (latest)
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(4),
        ]);

        // review-by-learning should return the latest attempt
        $response = $this->getJson("/api/quiz/review-by-learning/{$this->learning->id}");
        $response->assertStatus(200);
        $this->assertEquals(80, $response->json('data.score'), 'Should return latest attempt (80%)');
        $this->assertEquals(4, $response->json('data.correct_answers'));
    }

    /** @test */
    public function review_without_attempt_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/quiz/review-by-learning/{$this->learning->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function review_blocks_other_users_attempts(): void
    {
        Sanctum::actingAs($this->user);

        $submitRes = $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);
        $attemptId = $submitRes->json('data.result.attempt_id');

        // Different user should not be able to access this attempt
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/quiz/review/{$attemptId}");
        $response->assertStatus(404);
    }

    /* ==================================================================
     * GROUP 6: ATTEMPT DETAILS
     * ================================================================*/

    /** @test */
    public function attempt_details_returns_stored_data(): void
    {
        Sanctum::actingAs($this->user);

        $submitRes = $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(4),
        ]);

        $attemptId = $submitRes->json('data.result.attempt_id');

        $response = $this->getJson("/api/quiz/attempt-details/{$attemptId}");
        $response->assertStatus(200);

        $this->assertEquals($attemptId, $response->json('data.attempt_id'));
        $this->assertEquals($this->quiz->id, $response->json('data.quiz_id'));
        $this->assertEquals(80, $response->json('data.score'));
        $this->assertEquals(4, $response->json('data.correct_answers'));
        $this->assertEquals($this->totalQuestions, $response->json('data.total_questions'));
        $this->assertEquals($this->totalQuestions, $response->json('data.attempted_questions'));
        $this->assertNotNull($response->json('data.answers_data'));
        $this->assertCount($this->totalQuestions, $response->json('data.answers_data'));
    }

    /* ==================================================================
     * GROUP 7: RETRY FLOW
     * ================================================================*/

    /** @test */
    public function retry_deletes_previous_attempts_and_returns_fresh_state(): void
    {
        Sanctum::actingAs($this->user);

        // Make 2 attempts
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(3),
        ]);
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(4),
        ]);

        // Verify 2 attempts exist
        $this->assertEquals(2, QuizAttempt::where('user_id', $this->user->id)->count());

        // Retry
        $retryRes = $this->postJson("/api/quiz/retry/{$this->learning->id}");
        $retryRes->assertStatus(200);

        // Verify old attempts deleted
        $this->assertEquals(0, QuizAttempt::where('user_id', $this->user->id)->count());

        // Verify byLearning returns fresh state (no previous attempts)
        $quizData = $retryRes->json('data.quiz');
        $this->assertFalse($quizData['has_previous_attempt'], 'After retry, should have no previous attempt');
        $this->assertNull($quizData['best_score'], 'Best score should be null after retry');
        $this->assertEquals(0, $quizData['attempts_count'], 'Attempts should be 0 after retry');
    }

    /** @test */
    public function retry_without_attempts_returns_fresh_data(): void
    {
        Sanctum::actingAs($this->user);

        // Retry with no prior attempts should still work
        $response = $this->postJson("/api/quiz/retry/{$this->learning->id}");
        $response->assertStatus(200);

        $this->assertFalse($response->json('data.quiz.has_previous_attempt'));
        $this->assertNull($response->json('data.quiz.best_score'));
    }

    /* ==================================================================
     * GROUP 8: GUEST / AUTH USER DATA ISOLATION
     * ================================================================*/

    /** @test */
    public function guest_and_authenticated_attempts_are_separate(): void
    {
        // Guest submits (60%)
        $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => $this->partialAnswers(3),
            ]);

        // Guest should see 60% best
        $guestRes = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(60, $guestRes->json('data.quiz.best_score'));

        // Authenticated user submits (100%) — should NOT see guest's attempts
        Sanctum::actingAs($this->user);
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);

        $userRes = $this->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(100, $userRes->json('data.quiz.best_score'), 'User should see their own 100%');
        $this->assertEquals(1, $userRes->json('data.quiz.attempts_count'), 'User should have 1 attempt');

        // Clear auth so the next request treats guest headers as a real guest
        $this->app['auth']->forgetGuards();

        // Guest should still see 60%
        $guestRes2 = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $this->assertEquals(60, $guestRes2->json('data.quiz.best_score'), 'Guest should still see their own 60%');
        $this->assertEquals(1, $guestRes2->json('data.quiz.attempts_count'), 'Guest should have 1 attempt');
    }

    /* ==================================================================
     * GROUP 9: EDGE CASES & ERROR HANDLING
     * ================================================================*/

    /** @test */
    public function submit_without_guest_headers_returns_422(): void
    {
        $response = $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function by_learning_without_guest_headers_returns_422(): void
    {
        $response = $this->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $response->assertStatus(422);
    }

    /** @test */
    public function submit_with_invalid_learning_id_returns_404(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson('/api/quiz/submit/99999', [
                'answers' => [['question_id' => 1, 'answer' => 0]],
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function submit_with_empty_answers_returns_422(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson("/api/quiz/submit/{$this->learning->id}", [
                'answers' => [],
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function by_learning_with_invalid_id_returns_404(): void
    {
        $response = $this->withHeaders($this->guestHeaders)
            ->getJson('/api/quiz/by-learning/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function check_answer_validates_input(): void
    {
        // Missing question_id
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson('/api/quiz/check-answer', [
                'answer' => 0,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function check_answer_returns_correctness(): void
    {
        $question = $this->questions[0];
        $response = $this->withHeaders($this->guestHeaders)
            ->postJson('/api/quiz/check-answer', [
                'question_id' => $question->id,
                'answer'      => $question->correct_answer,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_correct'));
        $this->assertEquals($question->id, $response->json('data.question_id'));
        $this->assertNotNull($response->json('data.correct_answer'));
    }

    /** @test */
    public function check_answer_returns_incorrect_for_wrong_answer(): void
    {
        $question = $this->questions[0];
        $wrongAnswer = ((int) $question->correct_answer + 1) % 4;

        $response = $this->withHeaders($this->guestHeaders)
            ->postJson('/api/quiz/check-answer', [
                'question_id' => $question->id,
                'answer'      => $wrongAnswer,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.is_correct'));
    }

    /** @test */
    public function attempt_is_appended_not_overwritten(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->partialAnswers(3),
        ]);
        $this->postJson("/api/quiz/submit/{$this->learning->id}", [
            'answers' => $this->correctAnswers(),
        ]);

        // 2 attempts in DB
        $count = QuizAttempt::where('user_id', $this->user->id)
            ->where('quiz_id', $this->quiz->id)
            ->count();

        $this->assertEquals(2, $count, 'Attempts should append, not overwrite');
    }

    /** @test */
    public function authenticated_user_bypasses_guest_headers(): void
    {
        Sanctum::actingAs($this->user);

        // Without any guest headers, authenticated user should still access byLearning
        $response = $this->getJson("/api/quiz/by-learning/{$this->learning->id}");
        $response->assertStatus(200);
        $this->assertFalse($response->json('data.quiz.is_guest'));
    }
}
