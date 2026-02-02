<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiry Received</title>
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
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .transaction-details {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .transaction-details p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
        .thank-you {
            font-size: 18px;
            font-weight: bold;
            color: #4a056b;
            text-align: center;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo" class="logo">
    </div>
    <h1>Enquiry Received</h1>

    <p class="thank-you">New Enquiry Received!</p>

    <div class="order-details">
        <h2>Enquiry Details</h2>
        <table>
            <tr>
                <th>Fullname</th>
                <td>{{ $data['name'] }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $data['email'] }}</td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td>{{ $data['phone_number'] }}</td>
            </tr>
            <tr>
                <th>Message</th>
                <td>{{ $data['message'] }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply directly to this message.</p>
        <p>If you have any inquiries, please visit our help center or contact support for further assistance.</p>
    </div>
</body>
</html>
