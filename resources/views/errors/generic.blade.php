@extends('errors.layout')

@section('title', 'Error')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div style="text-align: center; background: white; padding: 3rem; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); max-width: 500px; width: 90%;">
        <div style="font-size: 6rem; font-weight: 700; color: #667eea; margin-bottom: 1rem; line-height: 1;">
            @if(isset($exception) && $exception->getCode())
                {{ $exception->getCode() }}
            @else
                Error
            @endif
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 600; color: #374151; margin-bottom: 1rem;">
            @if(isset($exception) && $exception->getMessage())
                {{ $exception->getMessage() }}
            @else
                Something went wrong
            @endif
        </h1>
        <p style="color: #6b7280; margin-bottom: 2rem; line-height: 1.6;">
            An unexpected error occurred. Please try again or contact support if the problem persists.
        </p>
        <div>
            <a href="{{ url('/') }}" style="background: #667eea; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-block;">
                Go Home
            </a>
            <a href="javascript:history.back()" style="background: transparent; color: #667eea; padding: 0.75rem 2rem; border: 2px solid #667eea; border-radius: 0.5rem; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-block; margin-left: 1rem;">
                Go Back
            </a>
        </div>
    </div>
</div>
@endsection
