<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Sama Sama Oye! OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 5px 0 10px 0;
        }
        ul {
            margin: 5px 0 10px 0;
            padding-left: 20px;
        }
        a {
            color: #1a73e8;
        }
        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }

        .text-center{
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Sama Sama Oye! Logo" class="logo">
    </div>

    <p>Hello,</p>
    <p>Thank you for signing up with Sama Sama Oye!</p>
    <p>Your OTP code is:</p>
    <div class="otp">{{ $data['otp_code'] }}</div>
    <p>This code will expire in 10 minutes.</p>
    <p>For your security:</p>

    <ul>
        <li>Do not share this code with anyone.</li>
        <li>Sama Sama Oye! staff will never ask for your verification code.</li>
        <li>If you did not request for this code, please ignore this email.</li>
    </ul>

    <p>Need help? Contact our support team at <a href="mailto:contact@samasamaoye.com">contact@samasamaoye.com</a>.</p>

    <p>Thank you,<br>
    Sama Sama Oye! Support Team</p>
    <p><a href="https://www.samasamaoye.com/en">https://www.samasamaoye.com/en</a></p>

    <div class="footer">
        <p class="text-center">This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
