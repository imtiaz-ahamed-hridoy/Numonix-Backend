<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningPageResource extends JsonResource
{
    private $checklistProgressMap;

    public function __construct($resource, $checklistProgressMap = [])
    {
        parent::__construct($resource);
        $this->checklistProgressMap = $checklistProgressMap;
    }

    public function toArray(Request $request): array
    {
        $page = $this->resource;

        $elements = ($page->relationLoaded('elements') ? $page->elements : $page->elements()->get())
            ->values()
            ->map(fn ($element) => (new LearningElementResource($element, $this->checklistProgressMap))
                ->toArray($request))
            ->all();

        return [
            'id'            => $page->id,
            'page_number'   => $page->page_number,
            'title'         => $page->title,
            'section_title' => $page->section_title,
            'elements'      => $elements,
        ];
    }
}
