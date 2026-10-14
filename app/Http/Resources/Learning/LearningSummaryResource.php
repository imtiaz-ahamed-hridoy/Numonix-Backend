<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningSummaryResource extends JsonResource
{
    private array $keyTakeaways;
    private string $categoryTitle;

    public function __construct($resource, array $keyTakeaways = [], string $categoryTitle = 'Summary')
    {
        parent::__construct($resource);
        $this->keyTakeaways = $keyTakeaways;
        $this->categoryTitle = $categoryTitle;
    }

    public function toArray(Request $request): array
    {
        $learning = $this->resource;

        return [
            'learning_id'    => $learning->id,
            'title'          => $this->categoryTitle,
            'learning_title' => $learning->title,
            'section_label'  => 'KEY TAKEAWAYS',
            'key_takeaways'  => $this->keyTakeaways,
            'summary'        => $learning->summary,
        ];
    }
}
