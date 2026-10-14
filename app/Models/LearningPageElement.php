<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LearningPageElement extends Model
{
    /**
     * Standard element types - available for lesson, core_theory, advanced_insight.
     */
    /**
     * Element types that store a file on disk (image, transparent_image, audio).
     * Only these types will have a non-null file_url returned by the API.
     */
    public const FILE_TYPES = ['image', 'transparent_image', 'audio'];

    public const STANDARD_TYPES = [
        'title',
        'subtitle',
        'section_label',
        'text',
        'bullet_list',
        'checklist',
        'table',
        'info_box',
        'key_insight',
        'image',
        'transparent_image',
        'audio',
        'sentence_visual',
    ];

    /**
     * FNH Architecture & Case Study element types - exclusively for fnh_architecture and case_study.
     */
    public const FNH_CASE_STUDY_TYPES = [
        'what_fnh_reveals',          // Screenshot #1: "WHAT FNH REVEALS" callout box
        'template_form_table',       // Screenshot #2, #4: TEMPLATE / T FORM / T# FORM table
        'defining_behaviours_card',  // Screenshot #3: "6.3 Tension Climb" - DEFINING BEHAVIOURS card
        'movement_quality_table',    // Screenshot #5: MOVEMENT / EMOTIONAL QUALITY / FROM T / FROM T#
        'centre_character_table',    // Screenshot #6: CENTRE / CHARACTER / WHAT HOME FEELS LIKE
        'functional_flow',           // Screenshot #7: FUNCTIONAL FLOW - chain (T# > S# > D > ...)
        'section_heading',           // Screenshot #9: "Developer Reference - All Element Types"
        'exploring_section',         // Screenshot #10: "Exploring Verse" with T/T# formula tags
        'centre_identity_card',      // Screenshot #11, #12: Centre identity card(s) - single or grid
        'form_identity_card',        // Screenshot #13, #14: Form identity card(s) - single or pair
    ];

    /**
     * Standard element types that should ALSO be available in FNH/Case Study categories.
     */
    public const FNH_SHARED_STANDARD_TYPES = [
        'title',
        'text',
        'table',
        'info_box',
        'key_insight',
        'image',
        'transparent_image',
        'sentence_visual',
    ];

    /**
     * Categories that use FNH-specific element types.
     */
    public const FNH_CATEGORIES = ['fnh_architecture', 'case_study'];

    /**
     * All valid element types (union of standard + FNH).
     */
    public const TYPES = [
        // Standard
        'title',
        'subtitle',
        'section_label',
        'text',
        'bullet_list',
        'checklist',
        'table',
        'info_box',
        'key_insight',
        'image',
        'transparent_image',
        'audio',
        'sentence_visual',
        // FNH Architecture & Case Study
        'what_fnh_reveals',
        'template_form_table',
        'defining_behaviours_card',
        'movement_quality_table',
        'centre_character_table',
        'functional_flow',
        'developer_note',
        'section_heading',
        'exploring_section',
        'centre_identity_card',
        'form_identity_card',
    ];

    use LogsActivity;

    protected $fillable = [
        'learning_page_id',
        'position',
        'type',
        'data',
        'file_url',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'position' => 'integer',
        'data'     => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(LearningPage::class, 'learning_page_id');
    }

    public function getFileUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return url($value);
        }
        return $value;
    }
}
