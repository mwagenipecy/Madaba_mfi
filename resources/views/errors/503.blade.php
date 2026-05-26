@extends('errors.layout')

@section('title', 'Service Unavailable')
@section('code', '503')
@section('accent-bar', 'bg-violet-500')
@section('code-color', 'text-violet-600')
@section('heading', 'Service Unavailable')
@section('message', $message ?? 'The system is temporarily unavailable due to maintenance or high load. Please try again soon.')

@section('extra')
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 text-left">
        <strong>Maintenance:</strong> If this continues, contact your system administrator.
    </div>
@endsection

@section('actions')
    @include('errors.partials.actions', ['showRefresh' => true])
@endsection
