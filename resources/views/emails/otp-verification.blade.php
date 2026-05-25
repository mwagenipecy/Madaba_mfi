@extends('emails.layout')

@section('title', 'Your Verification Code')
@section('subtitle', 'Verification code')

@section('content')
@php
    $otpDigits = str_split(str_pad((string) $otpCode, 6, '0', STR_PAD_LEFT));
@endphp
<div style="background:#ffffff;border-radius:10px;">
  <p style="font-size:15px;color:#374151;margin:0 0 4px;">
    Hello{{ $userName ? ', ' . $userName : '' }},
  </p>
  <h2 style="font-size:22px;color:#111827;margin:0 0 8px;">
    Your verification code
  </h2>
  <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 28px;">
    Use this one-time password to complete your sign-in. This code expires in
    <strong style="color:#008000;">{{ $expiryMinutes }} minutes</strong>.
  </p>
  <div style="text-align:center;margin-bottom:28px;">
    @foreach($otpDigits as $digit)
      <span style="display:inline-block;width:52px;height:64px;background:#ffffff;border-radius:12px;border:1.5px solid #E5E7EB;font-size:28px;font-weight:700;color:#008000;line-height:64px;text-align:center;margin:0 4px;">{{ $digit }}</span>
    @endforeach
  </div>
  <div style="height:1px;background:#E5E7EB;margin:0 0 20px;"></div>
  @include('emails.partials.alert', [
      'text' => 'If you did not request this code, please ignore this email or contact support. Never share this code.',
  ])
</div>
@endsection
