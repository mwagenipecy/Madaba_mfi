@extends('errors.layout')

@section('title', 'Page Expired')
@section('code', '419')
@section('accent-bar', 'bg-sky-500')
@section('code-color', 'text-sky-600')
@section('heading', 'Session Expired')
@section('message', $message ?? 'Your session has expired. Refresh the page and submit the form again.')

@section('actions')
    @include('errors.partials.actions', ['showRefresh' => true])
@endsection
