<?php

namespace App\Services\SentenceVisual;

/**
 * Orchestrates sentence visual formatting.
 *
 * Maps raw input data into the canonical sentence_visual format
 * via SentenceVisualMapper, then validates the result through
 * SentenceVisualValidator to ensure schema compliance.
 */
class SentenceVisualService
{
    public function __construct(
        private readonly SentenceVisualMapper $mapper,
        private readonly SentenceVisualValidator $validator,
    ) {}

    /**
     * Format raw data into a validated canonical sentence visual block.
     *
     * @param  array  $data  Raw sentence visual data
     * @param  int|string|null  $id  Optional block identifier
     * @return array The validated canonical sentence visual payload
     */
    public function format(array $data, int|string|null $id = null): array
    {
        $block = $this->mapper->toCanonical($data, $id);
        $this->validator->validate($block);

        return $block;
    }
}
