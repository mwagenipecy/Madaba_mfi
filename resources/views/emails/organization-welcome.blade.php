@extends('emails.layout')

@section('title', 'Welcome to ' . config('app.name'))
@section('subtitle', 'Registration successful')

@section('content')
<div style="background:#ffffff;border-radius:10px;">
  <p style="font-size:15px;color:#374151;margin:0 0 4px;">
    Hello,
  </p>
  <h2 style="font-size:22px;color:#111827;margin:0 0 8px;">
    Welcome, {{ $user->full_name }}
  </h2>
  <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 24px;">
    Your organization <strong style="color:#008000;">{{ $organization->name }}</strong> has been registered successfully.
  </p>

  <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;padding:18px 20px;margin-bottom:24px;">
    <p style="font-size:13px;font-weight:600;color:#111827;margin:0 0 12px;">Registration details</p>
    <p style="font-size:14px;color:#4B5563;line-height:1.8;margin:0;">
      Organization ID: <strong>#ORG{{ str_pad($organization->id, 4, '0', STR_PAD_LEFT) }}</strong><br>
      Admin ID: <strong>#USR{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</strong><br>
      Email: <strong>{{ $user->email }}</strong><br>
      Status: <strong style="color:#008000;">Pending approval</strong>
    </p>
  </div>

  <p style="font-size:14px;color:#4B5563;line-height:1.6;margin:0 0 8px;">
    Our team will review your account within 1–2 business days. You will receive another email once approved.
  </p>

  <div style="text-align:center;margin:28px 0 8px;">
    @include('emails.partials.button', ['url' => $loginUrl, 'label' => 'Access Dashboard'])
  </div>

  <div style="height:1px;background:#E5E7EB;margin:24px 0 20px;"></div>

  @include('emails.partials.alert', [
      'text' => 'If you did not register this organization, please contact support immediately.',
  ])
</div>
@endsection

@section('footer_links')
  <a href="{{ url('/login') }}" style="font-size:11.5px;color:#008000;text-decoration:none;margin:0 9px;">Sign in</a>
  <a href="mailto:support@microfin.com" style="font-size:11.5px;color:#008000;text-decoration:none;margin:0 9px;">Contact support</a>
@endsection
