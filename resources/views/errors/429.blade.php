@extends('errors.layout')

@section('title', 'Too Many Requests')
@section('code', '429')
@section('accent-bar', 'bg-orange-500')
@section('code-color', 'text-orange-600')
@section('heading', 'Too Many Requests')
@section('message', $message ?? 'You have made too many requests. Please wait a moment and try again.')

@section('actions')
    @include('errors.partials.actions', ['showRefresh' => true])
@endsection
