@extends('layouts.app')

@section('content')
    @if (isset($header))
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </div>
    @endif

    {{ $slot }}
@endsection
