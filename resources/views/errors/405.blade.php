@extends('errors.layout')

@section('title', 'Method Not Allowed')
@section('code', '405')
@section('accent-bar', 'bg-gray-500')
@section('code-color', 'text-gray-600')
@section('heading', 'Method Not Allowed')
@section('message', $message ?? 'This action is not supported for the requested URL.')

@section('actions')
    @include('errors.partials.actions', ['showBack' => true])
@endsection
