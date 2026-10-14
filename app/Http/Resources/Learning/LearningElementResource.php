<?php

namespace App\Http\Resources\Learning;

use App\Services\LearningElementFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningElementResource extends JsonResource
{
    private $checklistProgressMap;

    public function __construct($resource, $checklistProgressMap = [])
    {
        parent::__construct($resource);
        $this->checklistProgressMap = $checklistProgressMap;
    }

    public function toArray(Request $request): array
    {
        $element = $this->resource;

        $formattedData = LearningElementFormatter::format(
            $element->type,
            $element->data ?? new \stdClass(),
            $this->checklistProgressMap,
            $element->id
        );

        $payload = [
            'id'       => $element->id,
            'type'     => $element->type,
            'position' => (int) ($element->position ?? 1),
        ];

        // sentence_visual: promote mode/title/description to top level
        // title and description are ALWAYS present (defaults to empty string)
        if ($element->type === 'sentence_visual') {
            if (array_key_exists('mode', $formattedData)) {
                $payload['mode'] = $formattedData['mode'];
                unset($formattedData['mode']);
            }
            $payload['title'] = $formattedData['title'] ?? '';
            unset($formattedData['title']);
            $payload['description'] = $formattedData['description'] ?? '';
            unset($formattedData['description']);
        }

        $payload['data'] = $formattedData;

        // Only file-based element types (image, transparent_image, audio) carry a
        // file_url. For every other type the key is intentionally omitted from the
        // response to keep the payload clean and avoid confusing consumers with
        // a null value that has no meaning for non-file elements.
        if (in_array($element->type, \App\Models\LearningPageElement::FILE_TYPES, true)) {
            $payload['file_url'] = $element->file_url;
        }

        return $payload;
    }
}
