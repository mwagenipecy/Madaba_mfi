@component('mail::message')
# Welcome to MicroFin Pro!

Hello **{{ $user->full_name }}**,

Congratulations! Your organization **{{ $organization->name }}** has been successfully registered with MicroFin Pro.

## Registration Details
- **Organization ID:** #ORG{{ str_pad($organization->id, 4, '0', STR_PAD_LEFT) }}
- **Admin User ID:** #USR{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
- **Status:** Pending Approval

## What's Next?

1. **Account Review:** Our team will review your organization within 1-2 business days
2. **Approval Notification:** You'll receive an email once your account is approved
3. **System Access:** After approval, you can access your dashboard using the login button below

@component('mail::button', ['url' => $loginUrl])
Access Dashboard
@endcomponent

## Your Account Details
- **Email:** {{ $user->email }}
- **Role:** Administrator
- **Organization:** {{ $organization->name }}

## Need Help?

If you have any questions or need assistance, please don't hesitate to contact our support team.

@component('mail::button', ['url' => 'mailto:support@microfin.com'])
Contact Support
@endcomponent

Thank you for choosing MicroFin Pro for your microfinance management needs!

Best regards,<br>
{{ config('app.name') }} Team
@endcomponent