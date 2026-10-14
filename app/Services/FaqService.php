<?php

namespace App\Services;

use App\Repositories\Interfaces\FaqRepositoryInterface;

/**
 * Service for retrieving and grouping FAQ entries.
 */
class FaqService
{
    protected FaqRepositoryInterface $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    /**
     * Get all active FAQs grouped by section.
     *
     * @return array Groups of FAQs with section labels
     */
    public function getGroupedFaqs(): array
    {
        $faqs = $this->faqRepository->findActiveSorted();

        if ($faqs->isEmpty()) {
            return [];
        }

        return $faqs->groupBy(function ($item) {
            return $item->section ?: 'General';
        })->map(function ($items, $section) {
            return [
                'section' => $section,
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'question' => $item->question,
                        'answer' => $item->answer,
                    ];
                })->values(),
            ];
        })->values()->all();
    }
}
