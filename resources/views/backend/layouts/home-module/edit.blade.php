@extends('backend.app')
@section('title', 'Edit Home Module')

@section('content')
    @include('backend.layouts.home-module._form', [
        'module'      => $module,
        'formTitle'   => 'Edit Home Module',
        'submitLabel' => 'Update Module',
        'action'      => route('home-module.update', $module->id),
    ])
@endsection
