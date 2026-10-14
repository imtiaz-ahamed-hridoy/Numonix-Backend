@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>🎉 <strong>Welcome to the NumoniX Founder's Programme.</strong> You're in - and we're glad you're here.</p>

    <p>Your <strong>30-day all-access Founder's Trial</strong> is now active from today. That means full Tier 2 access to everything NumoniX has to offer:</p>

    <ul>
        <li>✓ All premium lessons - Chapters 1-23 plus decimals and Core Theory</li>
        <li>✓ Full Functional Ear Training - all stages and levels</li>
        <li>✓ Case studies, quizzes, and progress tracking</li>
    </ul>

    <p>As a Founder, you also have an exclusive opportunity to lock in our <strong>lifetime rate of £24.99/year</strong> - a rate that will never be available to the general public. Towards day 30 of your trial, your personal checkout link will be delivered directly to your inbox automatically.</p>

    <p>To get started, set your password and access your account:</p>

    @if(!empty($activationUrl))
    <a href="{{ $activationUrl }}" class="button" style="display:inline-block;padding:12px 24px;background-color:#1e3a8a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">Activate Your 30-Day Founder Trial -></a>
    @else
    <a href="{{ $setupUrl ?? route('password.request') }}" class="button">Access Your Founder Account -></a>
    @endif

    <hr class="divider">

    <p><strong>Your trial timeline:</strong></p>
    <ul>
        <li><strong>Now:</strong> Full Tier 2 access begins immediately</li>
        <li><strong>Day 25:</strong> You'll receive a reminder that your trial is ending soon</li>
        <li><strong>Day 30:</strong> Your exclusive £24.99/year checkout link arrives automatically</li>
    </ul>

    <p>If you have questions at any point, just reply to this email. We read every message.</p>

    <p>Thanks for being a Founder,<br>The NumoniX Team</p>
@endsection
