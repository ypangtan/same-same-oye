<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSO</title>
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
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo" class="logo">
    </div>

    <p>Dear {{ $data['name'] }},</p>
    
    <div>
        <p>You have been invited by {{ $data['sender_name'] }} to join the {{ $data['plan_name'] }} subscription package.</p>

        <p>By accepting this invitation, you will gain access to the features and benefits included in this plan.</p>

        <p>To proceed, please click the button below:</p>

        <button class="btn btn-primary" style="background-color: #4a056b; color: white; padding: 10px 20px; border: none; border-radius: 5px;"><a href="{{ $data['invitation_link'] }}" style="color: white; text-decoration: none;">Accept Invitation</a></button>

        <p>If the button above does not work, you may copy and paste the following link into your browser:</p>

        <p>{{ $data['invitation_link'] }}</p>

        <p>If you were not expecting this invitation, you may safely ignore this email.</p>
    </div>

    <div>
        <p>This is an automated message. Please do not reply to this email.</p>

        <p>
            Best regards, <br>
            Sama Sama Oye! Team <br>
            https://samasamaoye.com/
        </p>
    </div>
</body>
</html>
