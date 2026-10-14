<?php

namespace Tests\Feature\Learning;

use App\Models\GuestDevice;
use App\Models\Learning;
use App\Models\LearningPage;
use App\Models\LearningPageElement;
use App\Models\User;
use App\Services\GuestFingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningElementPositioningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private array $guestHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $admin */
        $this->admin = User::factory()->create(['role' => 'Admin']);

        $fingerprintService = app(GuestFingerprintService::class);
        $deviceId = 'test_device_' . uniqid();
        $token = 'test_token_' . uniqid();
        $fingerprint = $fingerprintService->compute($deviceId, $token);

        GuestDevice::create([
            'device_id' => $deviceId,
            'token' => $token,
            'guest_fingerprint' => $fingerprint,
        ]);

        $this->guestHeaders = [
            'X-Guest-Device-Id' => $deviceId,
            'X-Guest-Token' => $token,
            'X-Guest-Fingerprint' => $fingerprint,
        ];
    }

    /** @test */
    public function creating_learning_saves_elements_with_sequential_positions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('learning.store'), [
            'category' => 'lesson',
            'title' => 'Position Test Lesson',
            'slug' => 'position-test-lesson',
            'status' => 'active',
            'is_published' => '1',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'title' => 'Page 1',
                    'elements' => [
                        [
                            'type' => 'section_label',
                            'position' => 1,
                            'data' => ['text' => 'First Section Label'],
                        ],
                        [
                            'type' => 'key_insight',
                            'position' => 2,
                            'data' => ['title' => 'Insight Title', 'text' => 'Insight Body'],
                        ],
                        [
                            'type' => 'text',
                            'position' => 3,
                            'data' => ['text' => 'Third Text Paragraph'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $learning = Learning::where('slug', 'position-test-lesson')->with('pages.elements')->firstOrFail();
        $page = $learning->pages->first();
        $this->assertCount(3, $page->elements);

        $this->assertSame('section_label', $page->elements[0]->type);
        $this->assertSame(1, $page->elements[0]->position);

        $this->assertSame('key_insight', $page->elements[1]->type);
        $this->assertSame(2, $page->elements[1]->position);

        $this->assertSame('text', $page->elements[2]->type);
        $this->assertSame(3, $page->elements[2]->position);
    }

    /** @test */
    public function updating_learning_reorders_elements_and_updates_database_positions(): void
    {
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Reorder Test Lesson',
            'slug' => 'reorder-test-lesson',
            'status' => 'active',
            'is_published' => true,
        ]);

        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Page 1',
        ]);

        $el1 = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'position' => 1,
            'type' => 'section_label',
            'data' => ['text' => 'Section Label 1'],
        ]);

        $el2 = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'position' => 2,
            'type' => 'key_insight',
            'data' => ['title' => 'Insight 2', 'text' => 'Body 2'],
        ]);

        $el3 = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'position' => 3,
            'type' => 'text',
            'data' => ['text' => 'Text 3'],
        ]);

        // Reorder: el2 (Key Insight) moves to top (position 1), el3 (Text) to position 2, el1 (Section Label) to position 3
        $response = $this->actingAs($this->admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Reorder Test Lesson',
            'slug' => 'reorder-test-lesson',
            'status' => 'active',
            'is_published' => '1',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Page 1',
                    'elements' => [
                        [
                            'id' => $el2->id,
                            'type' => 'key_insight',
                            'position' => 1,
                            'data' => ['title' => 'Insight 2', 'text' => 'Body 2'],
                        ],
                        [
                            'id' => $el3->id,
                            'type' => 'text',
                            'position' => 2,
                            'data' => ['text' => 'Text 3'],
                        ],
                        [
                            'id' => $el1->id,
                            'type' => 'section_label',
                            'position' => 3,
                            'data' => ['text' => 'Section Label 1'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        // Refresh and check model ordering
        $page->refresh();
        $page->load('elements');

        $this->assertSame($el2->id, $page->elements[0]->id);
        $this->assertSame('key_insight', $page->elements[0]->type);
        $this->assertSame(1, $page->elements[0]->position);

        $this->assertSame($el3->id, $page->elements[1]->id);
        $this->assertSame('text', $page->elements[1]->type);
        $this->assertSame(2, $page->elements[1]->position);

        $this->assertSame($el1->id, $page->elements[2]->id);
        $this->assertSame('section_label', $page->elements[2]->type);
        $this->assertSame(3, $page->elements[2]->position);
    }

    /** @test */
    public function api_response_returns_elements_in_exact_position_order_with_position_field(): void
    {
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'API Position Test',
            'slug' => 'api-position-test',
            'status' => 'active',
            'is_published' => true,
        ]);

        $page = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Page 1',
            'section_title' => 'Section 1',
        ]);

        // Insert in DB with intentional non-sequential ID ordering vs position
        // Element with ID 1 has position 2, Element with ID 2 has position 1
        $elA = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'position' => 2,
            'type' => 'section_label',
            'data' => ['text' => 'Should Be Second'],
        ]);

        $elB = LearningPageElement::create([
            'learning_page_id' => $page->id,
            'position' => 1,
            'type' => 'key_insight',
            'data' => ['title' => 'Key Point', 'text' => 'Should Be First'],
        ]);

        $response = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$learning->id}");

        $response->assertStatus(200);

        $elements = $response->json('data.learning.pages.0.elements');
        $this->assertCount(2, $elements);

        // First element in API array should be elB (position 1)
        $this->assertSame($elB->id, $elements[0]['id']);
        $this->assertSame('key_insight', $elements[0]['type']);
        $this->assertSame(1, $elements[0]['position']);

        // Second element in API array should be elA (position 2)
        $this->assertSame($elA->id, $elements[1]['id']);
        $this->assertSame('section_label', $elements[1]['type']);
        $this->assertSame(2, $elements[1]['position']);
    }

    /** @test */
    public function reordering_pages_updates_page_number_and_api_tabs(): void
    {
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Page Reorder Lesson',
            'slug' => 'page-reorder-lesson',
            'status' => 'active',
            'is_published' => true,
        ]);

        $pageA = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Original Page 1',
        ]);

        $pageB = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 2,
            'title' => 'Original Page 2',
        ]);

        // Reorder pages in update request: Page B becomes first, Page A becomes second
        $response = $this->actingAs($this->admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Page Reorder Lesson',
            'slug' => 'page-reorder-lesson',
            'status' => 'active',
            'is_published' => '1',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $pageB->id,
                    'title' => 'Original Page 2 (Now First)',
                ],
                [
                    'id' => $pageA->id,
                    'title' => 'Original Page 1 (Now Second)',
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $pageA->refresh();
        $pageB->refresh();

        $this->assertSame(1, $pageB->page_number);
        $this->assertSame(2, $pageA->page_number);

        // Check API show
        $apiResponse = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$learning->id}");

        $apiResponse->assertStatus(200);
        $pages = $apiResponse->json('data.learning.pages');
        $tabs = $apiResponse->json('data.learning.tabs');

        $this->assertSame($pageB->id, $pages[0]['id']);
        $this->assertSame(1, $pages[0]['page_number']);
        $this->assertSame($pageA->id, $pages[1]['id']);
        $this->assertSame(2, $pages[1]['page_number']);

        $this->assertSame($pageB->id, $tabs[0]['id']);
        $this->assertSame($pageA->id, $tabs[1]['id']);
    }

    /** @test */
    public function moving_element_to_different_page_updates_page_and_position_in_db_and_api(): void
    {
        $learning = Learning::create([
            'category' => 'lesson',
            'title' => 'Cross Page Element Move',
            'slug' => 'cross-page-element-move',
            'status' => 'active',
            'is_published' => true,
        ]);

        $page1 = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 1,
            'title' => 'Page 1',
        ]);

        $page2 = LearningPage::create([
            'learning_id' => $learning->id,
            'page_number' => 2,
            'title' => 'Page 2',
        ]);

        // Element 1 is on Page 1
        $el1 = LearningPageElement::create([
            'learning_page_id' => $page1->id,
            'position' => 1,
            'type' => 'section_label',
            'data' => ['text' => 'Moved to Page 2'],
        ]);

        // Element 2 is on Page 2
        $el2 = LearningPageElement::create([
            'learning_page_id' => $page2->id,
            'position' => 1,
            'type' => 'key_insight',
            'data' => ['title' => 'Page 2 Insight', 'text' => 'Already on Page 2'],
        ]);

        // Move Element 1 from Page 1 to Page 2 (as position 1), so Element 2 becomes position 2 on Page 2
        $response = $this->actingAs($this->admin)->post(route('learning.update', $learning->id), [
            'category' => 'lesson',
            'title' => 'Cross Page Element Move',
            'slug' => 'cross-page-element-move',
            'status' => 'active',
            'is_published' => '1',
            'is_premium' => '0',
            'guest_access_override' => 'null',
            'pages' => [
                [
                    'id' => $page1->id,
                    'title' => 'Page 1',
                    'elements' => [],
                ],
                [
                    'id' => $page2->id,
                    'title' => 'Page 2',
                    'elements' => [
                        [
                            'id' => $el1->id,
                            'type' => 'section_label',
                            'position' => 1,
                            'data' => ['text' => 'Moved to Page 2'],
                        ],
                        [
                            'id' => $el2->id,
                            'type' => 'key_insight',
                            'position' => 2,
                            'data' => ['title' => 'Page 2 Insight', 'text' => 'Already on Page 2'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('learning.index'));

        $el1->refresh();
        $el2->refresh();

        // el1 should now belong to page2 and have position 1
        $this->assertSame($page2->id, $el1->learning_page_id);
        $this->assertSame(1, $el1->position);

        // el2 should have position 2
        $this->assertSame($page2->id, $el2->learning_page_id);
        $this->assertSame(2, $el2->position);

        // API response test
        $apiResponse = $this->withHeaders($this->guestHeaders)
            ->getJson("/api/learnings/show/{$learning->id}");

        $apiResponse->assertStatus(200);
        $page1Elements = $apiResponse->json('data.learning.pages.0.elements');
        $page2Elements = $apiResponse->json('data.learning.pages.1.elements');

        $this->assertEmpty($page1Elements);
        $this->assertCount(2, $page2Elements);
        $this->assertSame($el1->id, $page2Elements[0]['id']);
        $this->assertSame(1, $page2Elements[0]['position']);
        $this->assertSame($el2->id, $page2Elements[1]['id']);
        $this->assertSame(2, $page2Elements[1]['position']);
    }
}
