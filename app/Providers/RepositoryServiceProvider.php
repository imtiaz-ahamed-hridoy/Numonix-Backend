<?php

namespace App\Providers;

use App\Repositories\ActivityLogRepository;
use App\Repositories\GuestDeviceRepository;
use App\Repositories\CmsAboutNumoniXRepository;
use App\Repositories\CmsCompleteGlossaryRepository;
use App\Repositories\CmsGlossaryRepository;
use App\Repositories\CmsPrivacyPolicyRepository;
use App\Repositories\CmsTermsConditionsRepository;
use App\Repositories\CmsUserGuideRepository;
use App\Repositories\EarTrainingAttemptRepository;
use App\Repositories\EarTrainingLevelRepository;
use App\Repositories\EarTrainingQuestionRepository;
use App\Repositories\EarTrainingQuizRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\FaqRepository;
use App\Repositories\GuestAccessSettingRepository;
use App\Repositories\GuestActivityLogRepository;
use App\Repositories\HomeModuleRepository;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Interfaces\GuestDeviceRepositoryInterface;
use App\Repositories\Interfaces\CmsAboutNumoniXRepositoryInterface;
use App\Repositories\Interfaces\CmsCompleteGlossaryRepositoryInterface;
use App\Repositories\Interfaces\CmsGlossaryRepositoryInterface;
use App\Repositories\Interfaces\CmsPrivacyPolicyRepositoryInterface;
use App\Repositories\Interfaces\CmsTermsConditionsRepositoryInterface;
use App\Repositories\Interfaces\CmsUserGuideRepositoryInterface;
use App\Repositories\Interfaces\EarTrainingAttemptRepositoryInterface;
use App\Repositories\Interfaces\EarTrainingLevelRepositoryInterface;
use App\Repositories\Interfaces\EarTrainingQuestionRepositoryInterface;
use App\Repositories\Interfaces\EarTrainingQuizRepositoryInterface;
use App\Repositories\Interfaces\EmailLogRepositoryInterface;
use App\Repositories\Interfaces\EmailTemplateRepositoryInterface;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Repositories\Interfaces\GuestAccessSettingRepositoryInterface;
use App\Repositories\Interfaces\GuestActivityLogRepositoryInterface;
use App\Repositories\Interfaces\HomeModuleRepositoryInterface;
use App\Repositories\Interfaces\LearningGroupRepositoryInterface;
use App\Repositories\Interfaces\LearningProgressRepositoryInterface;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use App\Repositories\Interfaces\MailSettingRepositoryInterface;
use App\Repositories\Interfaces\PreferencesRepositoryInterface;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use App\Repositories\Interfaces\QuizQuestionRepositoryInterface;
use App\Repositories\Interfaces\QuizRepositoryInterface;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\Interfaces\SystemSettingRepositoryInterface;
use App\Repositories\Interfaces\ContactSupportRepositoryInterface;
use App\Repositories\Interfaces\BugReportRepositoryInterface;
use App\Repositories\Interfaces\FeatureSuggestionRepositoryInterface;
use App\Repositories\Interfaces\FirebaseTokenRepositoryInterface;
use App\Repositories\Interfaces\UserProgressRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\LearningGroupRepository;
use App\Repositories\LearningProgressRepository;
use App\Repositories\LearningRepository;
use App\Repositories\MailSettingRepository;
use App\Repositories\PreferencesRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizQuestionRepository;
use App\Repositories\QuizRepository;

use App\Repositories\ReviewRepository;
use App\Repositories\SystemSettingRepository;
use App\Repositories\ContactSupportRepository;
use App\Repositories\BugReportRepository;
use App\Repositories\FeatureSuggestionRepository;
use App\Repositories\FirebaseTokenRepository;
use App\Repositories\UserProgressRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Registers all repository interface-to-implementation bindings
 * in the service container. Centralizes the DI wiring so individual
 * ServiceProviders are not cluttered with repository bindings.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings: interface => concrete implementation.
     *
     * @var array<class-string, class-string>
     */
    private array $repositoryBindings = [
        // Auth / User
        UserRepositoryInterface::class            => UserRepository::class,

        // Learning
        LearningRepositoryInterface::class         => LearningRepository::class,
        LearningGroupRepositoryInterface::class    => LearningGroupRepository::class,
        LearningProgressRepositoryInterface::class => LearningProgressRepository::class,

        // Quiz
        QuizRepositoryInterface::class             => QuizRepository::class,
        QuizQuestionRepositoryInterface::class     => QuizQuestionRepository::class,
        QuizAttemptRepositoryInterface::class      => QuizAttemptRepository::class,

        // Ear Training
        EarTrainingLevelRepositoryInterface::class    => EarTrainingLevelRepository::class,
        EarTrainingQuizRepositoryInterface::class     => EarTrainingQuizRepository::class,
        EarTrainingQuestionRepositoryInterface::class => EarTrainingQuestionRepository::class,
        EarTrainingAttemptRepositoryInterface::class  => EarTrainingAttemptRepository::class,

        // CMS
        CmsUserGuideRepositoryInterface::class           => CmsUserGuideRepository::class,
        CmsTermsConditionsRepositoryInterface::class      => CmsTermsConditionsRepository::class,
        CmsPrivacyPolicyRepositoryInterface::class        => CmsPrivacyPolicyRepository::class,
        CmsGlossaryRepositoryInterface::class             => CmsGlossaryRepository::class,
        CmsCompleteGlossaryRepositoryInterface::class     => CmsCompleteGlossaryRepository::class,
        CmsAboutNumoniXRepositoryInterface::class         => CmsAboutNumoniXRepository::class,

        // Support
        FaqRepositoryInterface::class                 => FaqRepository::class,
        ReviewRepositoryInterface::class              => ReviewRepository::class,

        // Email
        EmailTemplateRepositoryInterface::class   => EmailTemplateRepository::class,
        EmailLogRepositoryInterface::class        => EmailLogRepository::class,
        MailSettingRepositoryInterface::class     => MailSettingRepository::class,

        // System / Settings
        SystemSettingRepositoryInterface::class       => SystemSettingRepository::class,
        GuestAccessSettingRepositoryInterface::class  => GuestAccessSettingRepository::class,

        // Activity
        ActivityLogRepositoryInterface::class     => ActivityLogRepository::class,
        GuestActivityLogRepositoryInterface::class => GuestActivityLogRepository::class,

        // Founder
        \App\Repositories\Interfaces\FounderRepositoryInterface::class => \App\Repositories\FounderRepository::class,

        // User Progress / Preferences
        UserProgressRepositoryInterface::class       => UserProgressRepository::class,
        PreferencesRepositoryInterface::class        => PreferencesRepository::class,
        HomeModuleRepositoryInterface::class         => HomeModuleRepository::class,


        // Support
        ContactSupportRepositoryInterface::class    => ContactSupportRepository::class,
        BugReportRepositoryInterface::class         => BugReportRepository::class,
        FeatureSuggestionRepositoryInterface::class => FeatureSuggestionRepository::class,

        // Push Notifications
        FirebaseTokenRepositoryInterface::class     => FirebaseTokenRepository::class,

        // Guest
        GuestDeviceRepositoryInterface::class       => GuestDeviceRepository::class,
    ];


    public function register(): void
    {
        foreach ($this->repositoryBindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
