@extends('backend.app')
@section('title', 'Create Learning')

@section('content')
    @include('backend.layouts.learning._form', [
        'learning' => null,
        'elementTypes' => $elementTypes,
        'formTitle' => 'Create Learning',
        'submitLabel' => 'Save Learning',
        'action' => route('learning.store'),
    ])
@endsection
