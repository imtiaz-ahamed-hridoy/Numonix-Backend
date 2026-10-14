@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>A quick heads up - your NumoniX Founder's Trial ends in <strong>5 days</strong>.</p>

    <p>As a Founder, you have an exclusive opportunity to lock in your <strong>lifetime rate of £24.99/year</strong> - a rate we will never offer to the general public. At the end of your 30-day trial, your personal checkout link will be emailed to you automatically.</p>

    <p><strong>What happens next:</strong></p>
    <ul>
        <li><strong>Day 30 (in 5 days):</strong> Your personal £24.99/year checkout link arrives directly in your inbox.</li>
        <li><strong>After your trial:</strong> If you don't subscribe, your access reverts to the free Tier 1 plan.</li>
    </ul>

    @if ($user->founder_checkout_link)
    <p>Your exclusive checkout link is already ready. Lock in your Founder's rate right now:</p>
    <a href="{{ $user->founder_checkout_link }}" class="button">Claim My Founder's Rate - £24.99/yr -></a>
    @endif

    <hr class="divider">

    <p>We'd also love to hear what you think of NumoniX before your trial ends. Your feedback directly shapes how we build the product - and it takes less than 2 minutes.</p>

    @if (!$user->founder_review_completed)
    <p><a href="{{ route('founder.review.show') }}">Share your feedback (optional - 2 minutes)</a></p>
    @else
    <p>✓ Thank you - we've already received your feedback. We appreciate it.</p>
    @endif

    <p>Questions? Just reply to this email.<br>The NumoniX Team</p>
@endsection
