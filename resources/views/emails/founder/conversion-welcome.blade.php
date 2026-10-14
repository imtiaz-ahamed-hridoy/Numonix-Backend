@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>🎉 <strong>Welcome to the NumoniX family - officially!</strong></p>

    <p>Your payment was successful and your <strong>£24.99/year Founder's membership</strong> is now active. You're one of a very select group of people who helped shape NumoniX from the very beginning - thank you.</p>

    <p><strong>What your Founder's membership includes:</strong></p>
    <ul>
        <li>✓ Full Tier 2 access - all premium lessons, Core Theory, Ear Training stages, and quizzes</li>
        <li>✓ Your Founder's rate of <strong>£24.99/year, locked in for life</strong> as long as you stay subscribed</li>
        <li>✓ Priority access to new features as they launch</li>
        <li>✓ Your voice in shaping the product</li>
    </ul>

    <p>Your subscription renews annually at the same locked-in £24.99 rate. You can manage or cancel your subscription at any time from your account settings.</p>

    <hr class="divider">

    <p>Open the NumoniX app on your device and sign in - your Founder membership is active immediately.</p>

    <p>If you have any questions, feedback, or just want to say hi - reply to this email. We read every message.</p>

    <p>With gratitude,<br>The NumoniX Team</p>
@endsection
