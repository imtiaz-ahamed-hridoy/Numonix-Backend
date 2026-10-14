<?php

namespace App\Http\Requests\Web\Backend\Learning;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;
use Illuminate\Validation\Rule;

/**
 * Validate and persist a new learning.
 *
 * @property array|null $pages
 * @property string $category
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $slug
 * @property string|null $read_time
 * @property string|null $summary
 * @property string $status
 * @property bool|null $is_published
 * @property bool|null $is_premium
 * @property string|null $guest_access_override
 * @property int|null $group_id
 */
class StoreLearningRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $learningId = $this->route('id');

        return [
            'category' => 'required|in:lesson,core_theory,case_study,fnh_architecture,advanced_insight',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('learnings', 'slug')->ignore($learningId),
            ],
            'read_time' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'status' => 'required|in:active,coming_soon,inactive',
            'is_published' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'guest_access_override' => 'nullable|in:null,true,false',
            'group_id' => 'nullable|integer|exists:learning_groups,id',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'pages' => 'nullable|array',
            'pages.*.id' => 'nullable|integer|exists:learning_pages,id',
            'pages.*.title' => 'nullable|string|max:255',
            'pages.*.section_title' => 'nullable|string|max:255',
            'pages.*.elements' => 'nullable|array',
            'pages.*.elements.*.id' => 'nullable|integer|exists:learning_page_elements,id',
            'pages.*.elements.*.position' => 'nullable|integer',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $category = $this->input('category');
            $groupId = $this->input('group_id');

            if (in_array($category, ['case_study', 'fnh_architecture'], true) && ! $this->filled('group_id')) {
                $validator->errors()->add('category', 'A group is required for '.str_replace('_', ' ', $category).' learnings.');
            }
        });
    }
}
