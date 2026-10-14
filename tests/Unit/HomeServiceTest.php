<?php

namespace Tests\Unit;

use App\Models\Learning;
use App\Models\LearningUserProgress;
use App\Repositories\Interfaces\HomeModuleRepositoryInterface;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use App\Repositories\Interfaces\UserProgressRepositoryInterface;
use App\Services\ContentAccessConfig;
use App\Services\HomeService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HomeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_resumes_an_unfinished_learning_record(): void
    {
        $first = $this->makeLearning('First lesson');
        $second = $this->makeLearning('Second lesson');

        $progress = new Collection([
            $second->id => new LearningUserProgress([
                'learning_id' => $second->id,
                'is_completed' => false,
            ]),
        ]);

        $homeModules = Mockery::mock(HomeModuleRepositoryInterface::class);
        $homeModules->shouldReceive('getActiveOrdered')->once()->andReturn(new EloquentCollection());

        $learnings = Mockery::mock(LearningRepositoryInterface::class);
        $learnings->shouldReceive('getPublishedByCategory')
            ->with('lesson')
            ->once()
            ->andReturn(new EloquentCollection([$first, $second]));

        $progressRepository = Mockery::mock(UserProgressRepositoryInterface::class);
        $progressRepository->shouldReceive('getLearningProgressForUser')
            ->with(123)
            ->once()
            ->andReturn($progress);

        $service = new HomeService(
            app(ContentAccessConfig::class),
            $homeModules,
            $learnings,
            $progressRepository,
        );

        $payload = $service->buildHomePayload((object) ['id' => 123], null);

        $this->assertSame('CONTINUE LEARNING', $payload['continue_learning']['label']);
        $this->assertSame($second->id, $payload['continue_learning']['learning_id']);
    }

    private function makeLearning(string $title): Learning
    {
        return Learning::create([
            'title' => $title,
            'subtitle' => $title,
            'slug' => strtolower(str_replace(' ', '-', $title)),
            'category' => 'lesson',
            'read_time' => '5 min read',
            'summary' => $title,
            'is_published' => true,
            'status' => 'active',
        ]);
    }
}
