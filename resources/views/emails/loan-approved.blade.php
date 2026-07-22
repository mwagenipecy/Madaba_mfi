@extends('emails.layout')

@section('title', 'Mkopo umeidhinishwa')
@section('subtitle', 'Loan approval notification')

@section('content')
<div style="background:#ffffff;border-radius:10px;">
  <h2 style="font-size:22px;color:#111827;margin:0 0 16px;">
    Mkopo umeidhinishwa
  </h2>
  <div style="font-size:15px;color:#374151;line-height:1.7;white-space:pre-line;">{{ $messageBody }}</div>
  <div style="height:1px;background:#E5E7EB;margin:24px 0 16px;"></div>
  <p style="font-size:13px;color:#6B7280;margin:0;">
    Loan number: <strong style="color:#111827;">{{ $loan->loan_number }}</strong><br>
    Amount: <strong style="color:#111827;">TZS {{ $amount }}</strong>
  </p>
</div>
@endsection
