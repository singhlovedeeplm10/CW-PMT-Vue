<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Status Update</title>
    <style>
        /* General Body Styling */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #eef2f7;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Email Container Styling */
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: 1px solid #ddd;
        }

        /* Email Header Styling */
        .email-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #ffffff;
            padding: 25px;
            text-align: center;
            border-bottom: 3px solid #0056b3;
        }

        .email-header h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: 1px;
        }

        /* Email Body Styling */
        .email-body {
            padding: 25px;
        }

        .email-body p {
            margin: 0 0 20px;
            line-height: 1.6;
            font-size: 16px;
            color: #555;
        }

        /* Leave Details Styling */
        .email-details {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }

        .email-details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .email-details ul li {
            padding: 10px 0;
            border-bottom: 1px dashed #ccc;
            font-size: 15px;
            color: #444;
        }

        .email-details ul li:last-child {
            border-bottom: none;
        }

        .email-details ul li strong {
            color: #333;
        }

        /* Email Footer Styling */
        .email-footer {
            text-align: center;
            padding: 20px;
            background: #f4f4f4;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #ddd;
        }

        .email-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }

        /* Responsiveness */
        @media (max-width: 600px) {
            .email-container {
                width: 95%;
            }

            .email-header h1 {
                font-size: 22px;
            }

            .email-body p, 
            .email-details ul li {
                font-size: 14px;
            }
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
                    <li><strong>Type of Leave:</strong> {{ $type_of_leave }}</li>
                    <li><strong>Status:</strong> {{ $status }}</li>
                    @if($type_of_leave !== 'Short Leave')
                        <li><strong>Start Date:</strong> {{ $start_date }}</li>
                        <li><strong>End Date:</strong> {{ $end_date }}</li>
                    @endif
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
