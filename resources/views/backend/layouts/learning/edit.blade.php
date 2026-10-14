@extends('backend.app')
@section('title', 'Edit Learning')

@section('content')
    @include('backend.layouts.learning._form', [
        'learning' => $learning,
        'elementTypes' => $elementTypes,
        'formTitle' => 'Edit Learning',
        'submitLabel' => 'Update Learning',
        'action' => route('learning.update', $learning->id),
    ])
@endsection
