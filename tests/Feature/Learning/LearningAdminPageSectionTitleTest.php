<?php

namespace Tests\Feature\Learning;

use App\Models\Learning;
use App\Models\LearningPage;
use App\Models\LearningPageElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAdminPageSectionTitleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_save_page_section_title_without_elements(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->post(route('learning.store'), [
            'category' => 'lesson',
            'title' => 'Section Title Lesson',
            'slug' => 'section-title-lesson',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'title' => 'Page Heading',
                    'section_title' => 'New Section Heading',
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $learning = Learning::where('slug', '=', 'section-title-lesson')
            ->with('pages')
            ->firstOrFail();

        $this->assertSame('Page Heading', $learning->pages->first()->title);
        $this->assertSame('New Section Heading', $learning->pages->first()->section_title);
    }

    /** @test */
    public function admin_can_update_page_section_title(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Existing Lesson',
            'slug' => 'existing-section-title-lesson',
            'status' => 'active',
            'is_published' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Existing Lesson',
            'slug' => 'existing-section-title-lesson',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'title' => 'Updated Page Heading',
                    'section_title' => 'Updated Section Heading',
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $learning->refresh();
        $learning->load('pages');

        $this->assertSame('Updated Page Heading', $learning->pages->first()->title);
        $this->assertSame('Updated Section Heading', $learning->pages->first()->section_title);
    }

    /** @test */
    public function admin_update_preserves_sentence_visual_custom_function_superscript_and_notes(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Sentence Visual Lesson',
            'slug' => 'sentence-visual-lesson',
            'status' => 'active',
            'is_published' => false,
        ]);
        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Study View',
        ]);
        $element = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'type' => 'sentence_visual',
            'data' => [
                'type' => 'sentence_visual',
                'mode' => 'cells',
                'beats' => [
                    ['label' => '1', 'strong' => true],
                    ['label' => '2'],
                    ['label' => '3'],
                    ['label' => '4'],
                ],
                'rows' => [[
                    'label' => 'BASS',
                    'cells' => [
                        ['type' => 'empty', 'beat' => 1, 'functions' => []],
                        ['type' => 'empty', 'beat' => 2, 'functions' => []],
                        ['type' => 'empty', 'beat' => 3, 'functions' => []],
                        [
                            'type' => 'normal',
                            'beat' => 4,
                            'functions' => [[
                                'world' => 'TS#',
                                'superscript' => '3',
                                'notes' => [
                                    ['number' => '1'],
                                    ['number' => '3'],
                                    ['number' => '5'],
                                    ['number' => '6#'],
                                ],
                            ]],
                        ],
                    ],
                ]],
            ],
            'file_url' => '/storage/learnings/elements/audio/obsolete.mp3',
        ]);

        $sentenceJson = json_encode($element->data, JSON_THROW_ON_ERROR);

        $response = $this->actingAs($admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Sentence Visual Lesson Updated',
            'slug' => 'sentence-visual-lesson',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Study View',
                    'elements' => [
                        [
                            'id' => $element->id,
                            'type' => 'sentence_visual',
                            'data' => [
                                'sentence_json' => $sentenceJson,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $element->refresh();
        $function = $element->data['rows'][0]['cells'][3]['functions'][0];

        $this->assertSame('TS#', $function['world']);
        $this->assertSame('3', $function['superscript']);
        $this->assertSame('6#', $function['notes'][3]['number']);
        $this->assertArrayNotHasKey('strong', $element->data['beats'][0]);
        $this->assertNull($element->getRawOriginal('file_url'));
    }

    /** @test */
    public function admin_update_keeps_existing_sentence_visual_data_when_json_payload_is_missing(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Protected Sentence Visual',
            'slug' => 'protected-sentence-visual',
            'status' => 'active',
            'is_published' => false,
        ]);
        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Study View',
        ]);
        $element = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'type' => 'sentence_visual',
            'data' => [
                'type' => 'sentence_visual',
                'mode' => 'cells',
                'beats' => [['label' => '1']],
                'rows' => [[
                    'label' => 'BASS',
                    'cells' => [[
                        'type' => 'normal',
                        'beat' => 1,
                        'functions' => [[
                            'world' => 'TS#',
                            'superscript' => '3',
                            'notes' => [['number' => '6#']],
                        ]],
                    ]],
                ]],
            ],
        ]);

        $response = $this->actingAs($admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Protected Sentence Visual Updated',
            'slug' => 'protected-sentence-visual',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Study View',
                    'elements' => [
                        [
                            'id' => $element->id,
                            'type' => 'sentence_visual',
                            'data' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $element->refresh();
        $function = $element->data['rows'][0]['cells'][0]['functions'][0];

        $this->assertSame('TS#', $function['world']);
        $this->assertSame('3', $function['superscript']);
        $this->assertSame('6#', $function['notes'][0]['number']);
    }

    /** @test */
    public function admin_update_preserves_sentence_visual_top_annotation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Top Annotation Test',
            'slug' => 'top-annotation-test',
            'status' => 'active',
            'is_published' => false,
        ]);
        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Study View',
        ]);
        $element = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'type' => 'sentence_visual',
            'data' => [
                'type' => 'sentence_visual',
                'mode' => 'cells',
                'beats' => [['label' => '1']],
                'rows' => [[
                    'label' => 'BASS',
                    'cells' => [[
                        'type' => 'normal',
                        'beat' => 1,
                        'annotation' => 'PIVOT CHORD',
                        'functions' => [[
                            'world' => 'T',
                            'notes' => [['number' => '1']],
                            'annotation' => 'PIVOT CHORD',
                        ]],
                    ]],
                ]],
            ],
        ]);

        $sentenceJson = json_encode($element->data, JSON_THROW_ON_ERROR);

        $response = $this->actingAs($admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Top Annotation Test Updated',
            'slug' => 'top-annotation-test',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Study View',
                    'elements' => [
                        [
                            'id' => $element->id,
                            'type' => 'sentence_visual',
                            'data' => [
                                'sentence_json' => $sentenceJson,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $element->refresh();
        $cell = $element->data['rows'][0]['cells'][0];

        $this->assertSame('PIVOT CHORD', $cell['annotation']);
        $this->assertSame('PIVOT CHORD', $cell['functions'][0]['annotation']);
    }

    /** @test */
    public function admin_update_preserves_table_key_map_data(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Table Test Lesson',
            'slug' => 'table-test-lesson',
            'status' => 'active',
            'is_published' => false,
        ]);
        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Table Page',
        ]);
        $element = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'type' => 'table',
            'data' => [
                'title' => 'Key Map Matrix',
                'rounded' => true,
                'columns' => [
                    ['key' => 'interval', 'label' => 'Interval'],
                    ['key' => 'quality', 'label' => 'Quality'],
                ],
                'rows' => [
                    [
                        'interval' => ['text' => 'Unison', 'bold' => true],
                        'quality' => ['text' => 'Perfect', 'bg_color' => '#DCFCE7'],
                    ],
                ],
            ],
        ]);

        $tableJson = json_encode($element->data, JSON_THROW_ON_ERROR);

        $response = $this->actingAs($admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Table Test Lesson Updated',
            'slug' => 'table-test-lesson',
            'status' => 'active',
            'is_published' => '0',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Table Page',
                    'elements' => [
                        [
                            'id' => $element->id,
                            'type' => 'table',
                            'data' => [
                                'table_json' => $tableJson,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $element->refresh();
        $this->assertSame('Key Map Matrix', $element->data['title']);
        $this->assertTrue($element->data['rounded']);
        $this->assertSame('Unison', $element->data['rows'][0]['interval']['text']);
        $this->assertTrue($element->data['rows'][0]['interval']['bold']);
    }
}
