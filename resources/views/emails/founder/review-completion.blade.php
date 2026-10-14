@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>Thank you for taking the time to share your thoughts on NumoniX. Your feedback is the most valuable thing you can give us at this stage - it directly shapes how the product develops.</p>

    <p>We read every single response, and yours helps us understand what's working, what needs improving, and where to focus next.</p>

    <hr class="divider">

    <p><strong>What happens next with your trial?</strong></p>

    <ul>
        <li>You are still in your active 30-day Founder's Trial - nothing changes.</li>
        <li>On <strong>day 25</strong>, you'll receive a reminder that your trial is ending soon.</li>
        <li>On <strong>day 30</strong>, your exclusive <strong>£24.99/year checkout link</strong> will land directly in your inbox - no action needed on your part.</li>
    </ul>

    <p>If you have anything to add or want to share more, just reply to this email. We read every message.</p>

    <p>With gratitude,<br>The NumoniX Team</p>
@endsection
