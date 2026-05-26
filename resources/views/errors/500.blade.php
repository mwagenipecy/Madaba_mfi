@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('accent-bar', 'bg-red-500')
@section('code-color', 'text-red-600')
@section('heading', 'Something Went Wrong')
@section('message', $message ?? 'An unexpected error occurred on our servers. Our team has been notified. Please try again shortly.')

@section('actions')
    @include('errors.partials.actions', ['showRefresh' => true])
@endsection
