<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Wibook Financing</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #008000, #006600);
            border-radius: 12px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
        .title {
            color: #008000;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            color: #666;
            font-size: 16px;
            margin: 10px 0 0 0;
        }
        .otp-container {
            background: #f8f9fa;
            border: 2px dashed #008000;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #008000;
            letter-spacing: 8px;
            margin: 0;
            font-family: 'Courier New', monospace;
        }
        .otp-label {
            color: #666;
            font-size: 14px;
            margin: 10px 0 0 0;
        }
        .expiry-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .expiry-text {
            color: #856404;
            font-size: 14px;
            margin: 0;
        }
        .instructions {
            background: #e7f3ff;
            border-left: 4px solid #008000;
            padding: 20px;
            margin: 20px 0;
        }
        .instructions h3 {
            color: #008000;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 5px 0;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .security-note {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .security-text {
            color: #721c24;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">W</div>
            <h1 class="title">Wibook Financing</h1>
            <p class="subtitle">Secure Login Verification</p>
        </div>

        <p>Hello <strong>{{ $userName }}</strong>,</p>
        
        <p>You have successfully logged into your Wibook Financing account. To complete the login process, please use the OTP (One-Time Password) code below:</p>

        <div class="otp-container">
            <p class="otp-code">{{ $otpCode }}</p>
            <p class="otp-label">Your OTP Code</p>
        </div>

        <div class="expiry-info">
            <p class="expiry-text">
                <strong>⏰ This code will expire at {{ $expiresAt->format('h:i A') }} on {{ $expiresAt->format('M d, Y') }}</strong>
            </p>
        </div>

        <div class="instructions">
            <h3>📋 Instructions:</h3>
            <ul>
                <li>Enter the 6-digit code above in the verification form</li>
                <li>Do not share this code with anyone</li>
                <li>If you didn't request this login, please contact support immediately</li>
                <li>The code is valid for 10 minutes only</li>
            </ul>
        </div>

        <div class="security-note">
            <p class="security-text">
                <strong>🔒 Security Notice:</strong> Wibook Financing will never ask for your OTP code via phone, email, or any other method. Only enter this code in the official login verification page.
            </p>
        </div>

        <p>If you have any questions or need assistance, please contact our support team.</p>

        <div class="footer">
            <p>© {{ date('Y') }} Wibook Financing. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
