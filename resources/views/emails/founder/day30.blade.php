@extends('emails.layouts.master')

@section('content')
    <p>Hi {{ $user->name }},</p>

    <p>Today is the final day of your NumoniX Founder's Trial.</p>
    
    <p>Don't miss out on securing your exclusive £24.99/year lifetime rate before your trial concludes.</p>

    <p>If you have a moment, we'd also love to hear your thoughts to help improve NumoniX:</p>

    <a href="{{ url('/founder/review') }}" class="button">Share Founder Feedback</a>

    <p>Thanks,<br>The NumoniX Team</p>
@endsection
