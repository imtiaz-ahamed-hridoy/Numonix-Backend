<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // -- Getting Started ----------------------------------------------
            [
                'section'  => 'Getting Started',
                'question' => 'What is NumoniX and how does it work?',
                'answer'   => 'NumoniX is a music theory learning app that uses a unique color-coded system (Tonic, Subdominant, Dominant) to help you understand harmony, chord progressions, and ear training. You learn by associating colors with musical functions.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Getting Started',
                'question' => 'How do I create an account?',
                'answer'   => 'Tap "Sign Up" on the login screen, enter your email and a password, and verify your email via the OTP sent to your inbox. You can then start your learning journey immediately.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Getting Started',
                'question' => 'Is NumoniX suitable for beginners?',
                'answer'   => 'Absolutely! The app is designed for all levels - from complete beginners to advanced musicians. The learning path starts from the very basics and gradually builds up to complex harmonic concepts.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Getting Started',
                'question' => 'How much time should I spend daily?',
                'answer'   => 'We recommend 10-15 minutes per day for optimal progress. Each lesson is short and focused, making it easy to fit into your daily routine.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Getting Started',
                'question' => 'Can I use NumoniX offline?',
                'answer'   => 'Some downloaded lessons and quizzes are available offline once you\'ve completed them online. However, an internet connection is required to access new content and sync your progress.',
                'status'   => 'Active',
            ],

            // -- Account ------------------------------------------------------
            [
                'section'  => 'Account',
                'question' => 'How do I reset my password?',
                'answer'   => 'Tap "Forgot Password" on the login screen. Enter your registered email, check your inbox for an OTP, and follow the instructions to create a new password.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Account',
                'question' => 'Can I change my email address?',
                'answer'   => 'Yes. Go to Profile -> Manage Account -> Update Details. Enter your new email and verify it via the OTP sent to your new address.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Account',
                'question' => 'How do I delete my account?',
                'answer'   => 'Go to Profile -> Manage Account -> Delete Account. Please note that this action is permanent and all your progress data will be removed.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Account',
                'question' => 'Can I use the same account on multiple devices?',
                'answer'   => 'Yes. Simply log in with your credentials on any device. Your progress and data will sync automatically.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Account',
                'question' => 'I didn\'t receive the verification email. What should I do?',
                'answer'   => 'Check your spam/junk folder first. If it\'s not there, tap "Resend OTP" on the verification screen. Still having issues? Contact our support team through the app.',
                'status'   => 'Active',
            ],

            // -- Billing -----------------------------------------------------
            [
                'section'  => 'Billing',
                'question' => 'Is NumoniX free to use?',
                'answer'   => 'NumoniX offers a Tier 1 access with introductory lessons and quizzes. Premium subscription unlocks the full course library, advanced ear training, and detailed progress analytics.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Billing',
                'question' => 'How much does the Premium subscription cost?',
                'answer'   => 'Pricing varies by region and plan. You can choose between monthly and yearly subscriptions. Check the Subscription page in the app for current pricing.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Billing',
                'question' => 'How do I cancel my subscription?',
                'answer'   => 'Go to Profile -> Subscription -> Cancel Subscription. Your access will continue until the end of the current billing period. You won\'t be charged again.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Billing',
                'question' => 'Can I get a refund?',
                'answer'   => 'Refunds are handled on a case-by-case basis. Please contact our support team via the app or email us at support@example.com with your purchase details.',
                'status'   => 'Active',
            ],
            [
                'section'  => 'Billing',
                'question' => 'Will my progress be saved if my subscription expires?',
                'answer'   => 'Yes, your progress data is never deleted. However, you will lose access to premium lessons and features until you renew your subscription.',
                'status'   => 'Active',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }

        $this->command->info('Seeded ' . count($faqs) . ' FAQ entries across sections.');
    }
}
