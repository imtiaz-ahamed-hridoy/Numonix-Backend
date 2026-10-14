<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningResource extends JsonResource
{
    private array $pageTabs;
    private array $formattedPages;
    private ?array $pagination;
    private bool $isGuestAccessible;

    public function __construct(
        $resource,
        array $pageTabs = [],
        array $formattedPages = [],
        bool $isGuestAccessible = false,
        ?array $pagination = null
    ) {
        parent::__construct($resource);
        $this->pageTabs = $pageTabs;
        $this->formattedPages = $formattedPages;
        $this->isGuestAccessible = $isGuestAccessible;
        $this->pagination = $pagination;
    }

    public function toArray(Request $request): array
    {
        $learning = $this->resource;

        $result = [
            'id'                   => $learning->id,
            'title'                => $learning->title,
            'subtitle'             => $learning->subtitle,
            'read_time'            => $learning->read_time,
            'status'               => $learning->status,
            'is_guest_accessible'  => $this->isGuestAccessible,
            'tabs'                 => $this->pageTabs,
            'pages'                => $this->formattedPages,
        ];

        // Conditionally add pagination metadata - must match exact key position
        if ($this->pagination !== null) {
            $result['pagination'] = $this->pagination;
        }

        return $result;
    }
}
