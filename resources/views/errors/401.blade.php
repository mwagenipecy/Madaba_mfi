@extends('errors.layout')

@section('title', 'Unauthorized')
@section('code', '401')
@section('accent-bar', 'bg-amber-500')
@section('code-color', 'text-amber-600')
@section('heading', 'Sign In Required')
@section('message', $message ?? 'You must be signed in to access this page.')

@section('actions')
    @include('errors.partials.actions', ['showLogin' => true])
@endsection
