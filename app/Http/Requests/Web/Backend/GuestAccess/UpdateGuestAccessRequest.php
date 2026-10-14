<?php

namespace App\Http\Requests\Web\Backend\GuestAccess;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesFailed;

/**
 * Validate an update request for a guest access.
 */
class UpdateGuestAccessRequest extends FormRequest
{
    use ValidatesFailed;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tier1_limit_lesson' => 'required|integer|min:0|max:100',
            'tier1_limit_core_theory' => 'required|integer|min:0|max:100',
            'tier1_limit_case_study' => 'required|integer|min:0|max:100',
            'tier1_limit_fnh_architecture' => 'required|integer|min:0|max:100',
            'tier1_limit_advanced_insight' => 'required|integer|min:0|max:100',
            'tier2_limit_lesson' => 'required|integer|min:0|max:100',
            'tier2_limit_core_theory' => 'required|integer|min:0|max:100',
            'tier2_limit_case_study' => 'required|integer|min:0|max:100',
            'tier2_limit_fnh_architecture' => 'required|integer|min:0|max:100',
            'tier2_limit_advanced_insight' => 'required|integer|min:0|max:100',
            'tier3_limit_lesson' => 'required|integer|min:0|max:100',
            'tier3_limit_core_theory' => 'required|integer|min:0|max:100',
            'tier3_limit_case_study' => 'required|integer|min:0|max:100',
            'tier3_limit_fnh_architecture' => 'required|integer|min:0|max:100',
            'tier3_limit_advanced_insight' => 'required|integer|min:0|max:100',
            'hidden_categories' => 'nullable|array',
            'hidden_categories.*' => 'string|in:lesson,core_theory,case_study,fnh_architecture,advanced_insight',
            'tier2_hidden_categories' => 'nullable|array',
            'tier2_hidden_categories.*' => 'string|in:lesson,core_theory,case_study,fnh_architecture,advanced_insight',
            'tier3_hidden_categories' => 'nullable|array',
            'tier3_hidden_categories.*' => 'string|in:lesson,core_theory,case_study,fnh_architecture,advanced_insight',
            'group_premium' => 'nullable|array',
            'group_premium.*' => 'boolean',
            'group_tier2_premium' => 'nullable|array',
            'group_tier2_premium.*' => 'boolean',
            'group_tier3_premium' => 'nullable|array',
            'group_tier3_premium.*' => 'boolean',
            'group_premium_fnh' => 'nullable|array',
            'group_premium_fnh.*' => 'boolean',
            'group_tier2_premium_fnh' => 'nullable|array',
            'group_tier2_premium_fnh.*' => 'boolean',
            'group_tier3_premium_fnh' => 'nullable|array',
            'group_tier3_premium_fnh.*' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tier1_limit_lesson.required' => 'Please enter your Tier1 Limit Lesson.',
            'tier1_limit_lesson.integer' => 'The Tier1 Limit Lesson must be a whole number.',
            'tier1_limit_lesson.min' => 'The Tier1 Limit Lesson must be at least 0 characters.',
            'tier1_limit_lesson.max' => 'The Tier1 Limit Lesson must not exceed 100 characters.',
            'tier1_limit_core_theory.required' => 'Please enter your Tier1 Limit Core Theory.',
            'tier1_limit_core_theory.integer' => 'The Tier1 Limit Core Theory must be a whole number.',
            'tier1_limit_core_theory.min' => 'The Tier1 Limit Core Theory must be at least 0 characters.',
            'tier1_limit_core_theory.max' => 'The Tier1 Limit Core Theory must not exceed 100 characters.',
            'tier1_limit_case_study.required' => 'Please enter your Tier1 Limit Case Study.',
            'tier1_limit_case_study.integer' => 'The Tier1 Limit Case Study must be a whole number.',
            'tier1_limit_case_study.min' => 'The Tier1 Limit Case Study must be at least 0 characters.',
            'tier1_limit_case_study.max' => 'The Tier1 Limit Case Study must not exceed 100 characters.',
            'tier1_limit_fnh_architecture.required' => 'Please enter your Tier1 Limit Fnh Architecture.',
            'tier1_limit_fnh_architecture.integer' => 'The Tier1 Limit Fnh Architecture must be a whole number.',
            'tier1_limit_fnh_architecture.min' => 'The Tier1 Limit Fnh Architecture must be at least 0 characters.',
            'tier1_limit_fnh_architecture.max' => 'The Tier1 Limit Fnh Architecture must not exceed 100 characters.',
            'tier1_limit_advanced_insight.required' => 'Please enter your Tier1 Limit Advanced Insight.',
            'tier1_limit_advanced_insight.integer' => 'The Tier1 Limit Advanced Insight must be a whole number.',
            'tier1_limit_advanced_insight.min' => 'The Tier1 Limit Advanced Insight must be at least 0 characters.',
            'tier1_limit_advanced_insight.max' => 'The Tier1 Limit Advanced Insight must not exceed 100 characters.',
            'tier2_limit_lesson.required' => 'Please enter your Tier2 Limit Lesson.',
            'tier2_limit_lesson.integer' => 'The Tier2 Limit Lesson must be a whole number.',
            'tier2_limit_lesson.min' => 'The Tier2 Limit Lesson must be at least 0 characters.',
            'tier2_limit_lesson.max' => 'The Tier2 Limit Lesson must not exceed 100 characters.',
            'tier2_limit_core_theory.required' => 'Please enter your Tier2 Limit Core Theory.',
            'tier2_limit_core_theory.integer' => 'The Tier2 Limit Core Theory must be a whole number.',
            'tier2_limit_core_theory.min' => 'The Tier2 Limit Core Theory must be at least 0 characters.',
            'tier2_limit_core_theory.max' => 'The Tier2 Limit Core Theory must not exceed 100 characters.',
            'tier2_limit_case_study.required' => 'Please enter your Tier2 Limit Case Study.',
            'tier2_limit_case_study.integer' => 'The Tier2 Limit Case Study must be a whole number.',
            'tier2_limit_case_study.min' => 'The Tier2 Limit Case Study must be at least 0 characters.',
            'tier2_limit_case_study.max' => 'The Tier2 Limit Case Study must not exceed 100 characters.',
            'tier2_limit_fnh_architecture.required' => 'Please enter your Tier2 Limit Fnh Architecture.',
            'tier2_limit_fnh_architecture.integer' => 'The Tier2 Limit Fnh Architecture must be a whole number.',
            'tier2_limit_fnh_architecture.min' => 'The Tier2 Limit Fnh Architecture must be at least 0 characters.',
            'tier2_limit_fnh_architecture.max' => 'The Tier2 Limit Fnh Architecture must not exceed 100 characters.',
            'tier2_limit_advanced_insight.required' => 'Please enter your Tier2 Limit Advanced Insight.',
            'tier2_limit_advanced_insight.integer' => 'The Tier2 Limit Advanced Insight must be a whole number.',
            'tier2_limit_advanced_insight.min' => 'The Tier2 Limit Advanced Insight must be at least 0 characters.',
            'tier2_limit_advanced_insight.max' => 'The Tier2 Limit Advanced Insight must not exceed 100 characters.',
            'tier3_limit_lesson.required' => 'Please enter your Tier3 Limit Lesson.',
            'tier3_limit_lesson.integer' => 'The Tier3 Limit Lesson must be a whole number.',
            'tier3_limit_lesson.min' => 'The Tier3 Limit Lesson must be at least 0 characters.',
            'tier3_limit_lesson.max' => 'The Tier3 Limit Lesson must not exceed 100 characters.',
            'tier3_limit_core_theory.required' => 'Please enter your Tier3 Limit Core Theory.',
            'tier3_limit_core_theory.integer' => 'The Tier3 Limit Core Theory must be a whole number.',
            'tier3_limit_core_theory.min' => 'The Tier3 Limit Core Theory must be at least 0 characters.',
            'tier3_limit_core_theory.max' => 'The Tier3 Limit Core Theory must not exceed 100 characters.',
            'tier3_limit_case_study.required' => 'Please enter your Tier3 Limit Case Study.',
            'tier3_limit_case_study.integer' => 'The Tier3 Limit Case Study must be a whole number.',
            'tier3_limit_case_study.min' => 'The Tier3 Limit Case Study must be at least 0 characters.',
            'tier3_limit_case_study.max' => 'The Tier3 Limit Case Study must not exceed 100 characters.',
            'tier3_limit_fnh_architecture.required' => 'Please enter your Tier3 Limit Fnh Architecture.',
            'tier3_limit_fnh_architecture.integer' => 'The Tier3 Limit Fnh Architecture must be a whole number.',
            'tier3_limit_fnh_architecture.min' => 'The Tier3 Limit Fnh Architecture must be at least 0 characters.',
            'tier3_limit_fnh_architecture.max' => 'The Tier3 Limit Fnh Architecture must not exceed 100 characters.',
            'tier3_limit_advanced_insight.required' => 'Please enter your Tier3 Limit Advanced Insight.',
            'tier3_limit_advanced_insight.integer' => 'The Tier3 Limit Advanced Insight must be a whole number.',
            'tier3_limit_advanced_insight.min' => 'The Tier3 Limit Advanced Insight must be at least 0 characters.',
            'tier3_limit_advanced_insight.max' => 'The Tier3 Limit Advanced Insight must not exceed 100 characters.',
            'hidden_categories.*.in' => 'The Hidden Categories * must be one of: lesson, core_theory, case_study, fnh_architecture, advanced_insight.',
            'tier2_hidden_categories.*.in' => 'The Tier2 Hidden Categories * must be one of: lesson, core_theory, case_study, fnh_architecture, advanced_insight.',
            'tier3_hidden_categories.*.in' => 'The Tier3 Hidden Categories * must be one of: lesson, core_theory, case_study, fnh_architecture, advanced_insight.',
        ];
    }

}
