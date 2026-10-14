<?php

namespace App\Services\Cms;

use App\Models\Cms\AboutNumoniX;
use App\Models\Cms\CompleteGlossary;
use App\Models\Cms\Glossary;
use App\Models\Cms\PrivacyPolicy;
use App\Models\Cms\TermsConditions;
use App\Models\Cms\UserGuide;

/**
 * Service layer for CMS content retrieval.
 *
 * Provides formatted data for user guides, terms & conditions,
 * privacy policies, glossaries, and About NumoniX content.
 */
class CmsService
{
    /**
     * Get the User Guide with formatted tabs.
     */
    public function getUserGuide(): array
    {
        $tabs = UserGuide::with('sections.items')->get();

        $formattedTabs = $tabs->map(function ($tab) {
            if ($tab->tab === 'harmonic_builder') {
                $tiles = [];
                foreach ($tab->sections as $section) {
                    foreach ($section->items as $item) {
                        $imagesFormatted = [];
                        if (is_array($item->images)) {
                            foreach ($item->images as $img) {
                                $imagesFormatted[] = [
                                    'src' => isset($img['src']) ? asset('storage/' . $img['src']) : null,
                                ];
                            }
                        }

                        $tiles[] = [
                            'id' => (string) $item->id,
                            'section' => $section->heading,
                            'title' => $item->title,
                            'body' => $item->body,
                            'images' => $imagesFormatted,
                            'keys' => is_array($item->keys) ? $item->keys : [],
                        ];
                    }
                }

                return [
                    'tab' => $tab->tab,
                    'label' => 'Harmonic Builder',
                    'heading' => $tab->heading,
                    'intro_text' => $tab->intro_text,
                    'tiles' => $tiles,
                ];
            }

            return [
                'tab' => $tab->tab,
                'label' => 'Getting Started',
                'heading' => $tab->heading,
                'intro_text' => $tab->intro_text,
                'sections' => $tab->sections->map(function ($section) {
                    return [
                        'heading' => $section->heading,
                        'items' => $section->items->map(function ($item) {
                            return [
                                'title' => $item->title,
                                'body' => $item->body,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return [
            'tabs' => $formattedTabs,
        ];
    }

    /**
     * Get the Terms & Conditions.
     */
    public function getTermsConditions(): array
    {
        $terms = TermsConditions::with('sections')->first();

        if (! $terms) {
            return [
                'last_updated' => null,
                'sections' => [],
            ];
        }

        return [
            'title' => $terms->title,
            'last_updated' => $terms->last_updated_label,
            'sections' => $terms->sections->map(function ($section) {
                return [
                    'section_number' => $section->section_number,
                    'title' => $section->title,
                    'body' => $section->body,
                ];
            }),
        ];
    }

    /**
     * Get the Privacy Policy.
     */
    public function getPrivacyPolicy(): array
    {
        $privacy = PrivacyPolicy::with('sections')->first();

        if (! $privacy) {
            return [
                'last_updated' => null,
                'sections' => [],
            ];
        }

        return [
            'title' => $privacy->title,
            'last_updated' => $privacy->last_updated_label,
            'sections' => $privacy->sections->map(function ($section) {
                return [
                    'section_number' => $section->section_number,
                    'title' => $section->title,
                    'body' => $section->body,
                ];
            }),
        ];
    }

    /**
     * Get the Glossary.
     */
    public function getGlossary(): array
    {
        $glossary = Glossary::with('terms')->first();

        if (! $glossary) {
            return [
                'terms' => [],
            ];
        }

        return [
            'title' => $glossary->title,
            'terms' => $glossary->terms->map(function ($term) {
                return [
                    'term' => $term->term,
                    'definition' => $term->definition,
                ];
            }),
        ];
    }

    /**
     * Get the Complete Glossary.
     */
    public function getCompleteGlossary(): array
    {
        $glossary = CompleteGlossary::with('sections.items')->first();

        if (! $glossary) {
            return [
                'sections' => [],
            ];
        }

        return [
            'title' => $glossary->title,
            'sections' => $glossary->sections->map(function ($section) {
                return [
                    'label' => $section->label,
                    'items' => $section->items->map(function ($item) {
                        return [
                            'title' => $item->title,
                            'body' => $item->body,
                        ];
                    }),
                ];
            }),
        ];
    }

    /**
     * Get the About NumoniX content.
     */
    public function getAboutNumoniX(): array
    {
        $about = AboutNumoniX::with('sections.items')->first();

        if (! $about) {
            return [
                'heading' => '',
                'sections' => [],
            ];
        }

        return [
            'title' => $about->title,
            'heading' => $about->heading,
            'sections' => $about->sections->map(function ($section) {
                return [
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'points' => $section->items->pluck('body'),
                ];
            }),
        ];
    }
}
