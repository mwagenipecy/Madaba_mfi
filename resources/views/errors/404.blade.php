@extends('errors.layout')

@section('title', 'Page Not Found')
@section('code', '404')
@section('heading', 'Page Not Found')
@section('message', $message ?? 'The page you requested does not exist or may have been moved. Check the URL or return to the dashboard.')

@section('actions')
    @include('errors.partials.actions', ['showBack' => true])
@endsection
