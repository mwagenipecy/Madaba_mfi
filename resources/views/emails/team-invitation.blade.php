@extends('emails.layout')

@section('title', 'Team Invitation')
@section('subtitle', 'You have been invited')

@section('content')
<div style="background:#ffffff;border-radius:10px;">
  <p style="font-size:15px;color:#374151;margin:0 0 4px;">
    Hello,
  </p>
  <h2 style="font-size:22px;color:#111827;margin:0 0 8px;">
    Join {{ $invitation->team->name }}
  </h2>
  <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 24px;">
    {{ __('You have been invited to join the :team team!', ['team' => $invitation->team->name]) }}
  </p>

  @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
    <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 16px;">
      {{ __('If you do not have an account, create one first, then accept the invitation below.') }}
    </p>
    <div style="text-align:center;margin-bottom:20px;">
      @include('emails.partials.button', ['url' => route('register'), 'label' => __('Create Account')])
    </div>
    <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 16px;">
      {{ __('If you already have an account, accept the invitation below:') }}
    </p>
  @else
    <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 16px;">
      {{ __('You may accept this invitation by clicking the button below:') }}
    </p>
  @endif

  <div style="text-align:center;margin:8px 0 24px;">
    @include('emails.partials.button', ['url' => $acceptUrl, 'label' => __('Accept Invitation')])
  </div>

  <div style="height:1px;background:#E5E7EB;margin:0 0 20px;"></div>

  @include('emails.partials.alert', [
      'text' => __('If you did not expect to receive an invitation to this team, you may discard this email.'),
  ])
</div>
@endsection
