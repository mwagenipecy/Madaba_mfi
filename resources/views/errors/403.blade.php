@extends('errors.layout')

@section('title', 'Access Forbidden')
@section('code', '403')
@section('accent-bar', 'bg-amber-500')
@section('code-color', 'text-amber-600')
@section('heading', 'Access Forbidden')
@section('message', $message ?? 'You do not have permission to view this page. Contact your administrator if you need access.')

@section('actions')
    @include('errors.partials.actions', ['showLogin' => true, 'showBack' => true])
@endsection
