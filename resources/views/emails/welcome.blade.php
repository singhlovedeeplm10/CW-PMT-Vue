<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Company</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
            line-height: 1.6;
        }
        .email-body p {
            margin: 0 0 15px;
        }
        .email-body strong {
            color: #007bff;
        }
        .email-footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #777;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Welcome to Contriwhiz Technologies</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p>Dear <strong>{{ $name }}</strong>,</p>
            <p>We are thrilled to have you on board as a part of the <strong>Contriwhiz Technologies</strong> team!</p>
            <p>We are committed to providing you with the tools, resources, and support you need to succeed in your role.</p>
            <p>If you have any questions or need assistance, please don't hesitate to reach out. We are here to help!</p>
            <p>Welcome aboard, and we look forward to achieving great things together.</p>
            <p>Best regards,</p>
            <p><strong>The Team at Contriwhiz Technologies</strong></p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }} Contriwhiz Technologies. All rights reserved.
        </div>
    </div>
</body>
</html>
