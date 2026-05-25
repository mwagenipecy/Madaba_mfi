<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#374151;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
          <tr>
            <td align="center" style="background:#ffffff;border-bottom:1px solid #E5E7EB;padding:28px 24px;">
              <img src="{{ rtrim(config('app.url'), '/') }}/logo/wibook.png" alt="{{ config('app.name', 'Wibook Financing') }}" style="max-width:180px;height:auto;display:block;background:#ffffff;padding:10px 16px;border-radius:8px;">
              @hasSection('subtitle')
                <p style="font-size:14px;color:#008000;margin:14px 0 0;">
                  @yield('subtitle')
                </p>
              @endif
            </td>
          </tr>
          <tr>
            <td style="padding:30px 28px;">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:22px 20px;background:#F9FAFB;border-top:1px solid #E5E7EB;">
              <p style="font-size:12px;color:#6B7280;margin:0 0 6px;">
                © {{ date('Y') }} {{ config('app.name', 'Wibook Financing') }} · All rights reserved
              </p>
              @hasSection('footer_links')
                <p style="margin:0;">
                  @yield('footer_links')
                </p>
              @else
                <p style="font-size:11.5px;color:#9CA3AF;margin:0;">
                  This is an automated message. Please do not reply to this email.
                </p>
              @endif
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
