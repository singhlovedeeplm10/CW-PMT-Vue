<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
        }
        .email-body p {
            margin: 0 0 15px;
            line-height: 1.5;
        }
        .email-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .email-details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .email-details ul li {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .email-details ul li:last-child {
            border-bottom: none;
        }
        .email-footer {
            text-align: center;
            padding: 15px;
            background: #f4f4f4;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Leave Status Update</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p>Hello, <strong>{{ $name }}</strong>,</p>
            <p>Your leave request has been updated. Here are the details:</p>

            <!-- Leave Details -->
            <div class="email-details">
                <ul>
                    {{-- <li><strong>Type of Leave:</strong> {{ $type_of_leave }}</li> --}}
                    <li><strong>Status:</strong> {{ $status }}</li>
                    {{-- @if($type_of_leave !== 'Short Leave')
                        <li><strong>Start Date:</strong> {{ $start_date }}</li>
                        <li><strong>End Date:</strong> {{ $end_date }}</li>
                    @endif --}}
                </ul>
            </div>

            <p>If you have any questions or concerns, feel free to contact the HR department.</p>
            <p>Thank you!</p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }} Your Company Name. All rights reserved.
        </div>
    </div>
</body>
</html>
