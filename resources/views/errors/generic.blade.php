@extends('errors.layout')

@section('title', 'Error')
@section('code', $statusCode ?? 500)
@section('accent-bar', 'bg-gray-500')
@section('code-color', 'text-gray-600')
@section('heading', 'Unexpected Error')
@section('message', $message ?? 'Something went wrong. Please try again or contact support if the problem continues.')

@section('actions')
    @include('errors.partials.actions', ['showBack' => true, 'showRefresh' => true])
@endsection
