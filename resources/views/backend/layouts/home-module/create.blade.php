@extends('backend.app')
@section('title', 'Create Home Module')

@section('content')
    @include('backend.layouts.home-module._form', [
        'module'      => null,
        'formTitle'   => 'Create Home Module',
        'submitLabel' => 'Save Module',
        'action'      => route('home-module.store'),
    ])
@endsection
