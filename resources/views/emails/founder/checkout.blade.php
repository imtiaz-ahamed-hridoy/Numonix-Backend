@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>🎉 <strong>Today is the final day of your NumoniX Founder's Trial.</strong></p>

    <p>As part of the Founder's Programme, your exclusive checkout link is now ready. Click below to lock in your <strong>£24.99/year lifetime Founder's rate</strong> before your trial access expires:</p>

    <a href="{{ $checkoutLink }}" class="button">Claim Your Founder's Rate - £24.99/yr -></a>

    <p>This is the Founder's exclusive rate - it's permanently locked in at <strong>£24.99/year</strong> for as long as you stay subscribed. It will never be offered to the general public.</p>

    <hr class="divider">

    <p><strong>After clicking:</strong></p>
    <ul>
        <li>Complete the secure Paddle checkout (takes under 2 minutes)</li>
        <li>Your account upgrades immediately - no disruption to your access</li>
        <li>Your Founder's rate of £24.99/year is locked in forever</li>
    </ul>

    @if (!$user->founder_review_completed)
    <p>If you haven't already, we'd love to hear your thoughts before you go: <a href="{{ route('founder.review.show') }}">Share feedback (optional, 2 minutes)</a></p>
    @else
    <p>✓ Thank you for sharing your feedback - it means a lot to us.</p>
    @endif

    <p>Questions about your subscription? Reply to this email and we'll help straight away.</p>

    <p>Welcome to the family,<br>The NumoniX Team</p>
@endsection
