<?php

namespace Tests\Feature\EarTraining;

use App\Models\EarTrainingStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarTrainingValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private EarTrainingStage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Admin']);
        $this->stage = EarTrainingStage::create([
            'title' => 'Beginner',
            'subtitle' => 'Core chord movements',
            'description' => 'Recognise core movements',
            'accent_colour' => '#3B82F6',
            'patterns_label' => 'T-S-T',
            'is_premium' => false,
            'is_locked_by_default' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /** @test */
    public function it_accepts_valid_fnh_tokens_on_level_creation()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.ear-training.levels.create', $this->stage))
            ->post(route('admin.ear-training.levels.store', $this->stage), [
                'level_number' => 1,
                'title' => 'Valid Level',
                'exercise_type' => 'identify_function',
                'function_patterns' => [
                    ['T', 'S', 'D', 'T#'],
                    ['d#', 'Db', 'd#T', null],
                    ['T', 'S', 'D', 'T'],
                    ['T', null, 'S', 'D']
                ],
                'tempo_bpm' => 80,
                'midi_program' => 0,
                'chord_voicing' => 'block',
                'chord_size' => '3',
                'chord_purity' => 'pure',
                'answer_display' => 'leadership_only',
                'chord_regularity' => 'regular',
                'bar_length' => 4,
                'repetitions' => 1,
                'exercise_count' => 10,
            ]);

        $response->assertRedirect(route('admin.ear-training.levels.index', $this->stage));
        $this->assertDatabaseHas('ear_training_levels', [
            'title' => 'Valid Level',
        ]);
    }

    /** @test */
    public function it_rejects_invalid_fnh_tokens_such_as_absolute_note_names()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.ear-training.levels.create', $this->stage))
            ->post(route('admin.ear-training.levels.store', $this->stage), [
                'level_number' => 1,
                'title' => 'Invalid Level',
                'exercise_type' => 'identify_function',
                'function_patterns' => [
                    ['F', 'S', 'D', 'T'], // 'F' is invalid!
                    ['T', 'S', 'D', 'T'],
                    ['T', 'S', 'D', 'T'],
                    ['T', 'S', 'D', 'T'],
                ],
                'tempo_bpm' => 80,
                'midi_program' => 0,
                'chord_voicing' => 'block',
                'chord_size' => '3',
                'chord_purity' => 'pure',
                'answer_display' => 'leadership_only',
                'chord_regularity' => 'regular',
                'bar_length' => 4,
                'repetitions' => 1,
                'exercise_count' => 10,
            ]);

        $response->assertRedirect(route('admin.ear-training.levels.create', $this->stage));
        $response->assertSessionHasErrors(['function_patterns.0.0']);
    }
}
