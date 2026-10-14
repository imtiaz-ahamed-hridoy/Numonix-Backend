<?php

namespace App\Services;

use App\Repositories\Interfaces\BugReportRepositoryInterface;
use App\Repositories\Interfaces\ContactSupportRepositoryInterface;
use App\Repositories\Interfaces\FeatureSuggestionRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Service layer for user support requests.
 *
 * Handles contact form submissions, bug reports (with optional
 * screenshot upload), and feature suggestions.
 */
class SupportService
{
    protected ContactSupportRepositoryInterface $contactSupportRepository;
    protected BugReportRepositoryInterface $bugReportRepository;
    protected FeatureSuggestionRepositoryInterface $featureSuggestionRepository;

    public function __construct(
        ContactSupportRepositoryInterface $contactSupportRepository,
        BugReportRepositoryInterface $bugReportRepository,
        FeatureSuggestionRepositoryInterface $featureSuggestionRepository,
    ) {
        $this->contactSupportRepository = $contactSupportRepository;
        $this->bugReportRepository = $bugReportRepository;
        $this->featureSuggestionRepository = $featureSuggestionRepository;
    }

    /**
     * Persist a contact-support message.
     *
     * @return array{id: int, name: string, email: string, message: string}
     */
    public function submitContact(array $validated): array
    {
        $support = $this->contactSupportRepository->create($validated);

        return [
            'id' => $support->id,
            'name' => $support->name,
            'email' => $support->email,
            'message' => $support->message,
        ];
    }

    /**
     * Submit a bug report with an optional screenshot attachment.
     *
     * @param  UploadedFile|null  $screenshotFile
     * @return array{id: int, title: string, description: string, email: string|null, screenshot: string|null, created_at: string|null}
     */
    public function reportBug(array $validated, $screenshotFile = null): array
    {
        $screenshotUrl = null;

        if ($screenshotFile) {
            $path = $screenshotFile->store('bug-reports', 'public');
            $screenshotUrl = Storage::disk('public')->url($path);
        }

        $bug = $this->bugReportRepository->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'email' => $validated['email'] ?? null,
            'screenshot' => $screenshotUrl,
        ]);

        return [
            'id' => $bug->id,
            'title' => $bug->title,
            'description' => $bug->description,
            'email' => $bug->email,
            'screenshot' => $bug->screenshot,
            'created_at' => optional($bug->created_at)->toDateTimeString(),
        ];
    }

    /**
     * Submit a feature suggestion.
     *
     * @return array{id: int, title: string, description: string, email: string|null, created_at: string|null}
     */
    public function suggestFeature(array $validated): array
    {
        $feature = $this->featureSuggestionRepository->create($validated);

        return [
            'id' => $feature->id,
            'title' => $feature->title,
            'description' => $feature->description,
            'email' => $feature->email,
            'created_at' => optional($feature->created_at)->toDateTimeString(),
        ];
    }
}
