<!DOCTYPE html>
<html>
<head>
    <title>Leave Application</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #444;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            font-size: 16px;
        }

        /* Container Styles */
        .container {
            width: 80%;
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Header Styles */
        h1 {
            font-size: 26px;
            color: #004d7f;
            background-color: #e9f1f9;
            text-align: center;
            margin: 0;
            padding: 18px;
            border-bottom: 3px solid #004d7f;
        }

        /* Text Styles */
        p {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
            line-height: 1.7;
        }

        p strong {
            color: #333;
        }

        /* Detail Box Styles */
        .detail {
            background-color: #f9f9f9;
            border-left: 5px solid #004d7f;
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Footer Styles */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
            padding: 15px 0;
            background-color: #f1f1f1;
            border-top: 2px solid #ddd;
        }

        /* Button Styles (optional, if buttons are added later) */
        button {
            display: inline-block;
            background-color: #004d7f;
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #00375f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Leave Application Notification</h1>
        <p>Hello, <strong>{{ $leaveDetails['admin_name'] }}</strong></p>
        <p><strong>{{ $leaveDetails['user_name'] }}</strong> has applied for a <strong>{{ $leaveDetails['type_of_leave'] }}</strong>.</p>
        
        <div class="detail">
            <p><strong>Date of Leave:</strong> {{ $leaveDetails['start_date'] }} to {{ $leaveDetails['end_date'] ?? 'N/A' }}</p>
            <p><strong>Time of Leave:</strong> {{ $leaveDetails['start_time'] ?? 'N/A' }} to {{ $leaveDetails['end_time'] ?? 'N/A' }}</p>
        </div>
        
        <p><strong>Reason for Leave:</strong> {{ $leaveDetails['reason'] }}</p>
        <p><strong>Contact During Leave:</strong> {{ $leaveDetails['contact_during_leave'] }}</p>
        <p><strong>Status:</strong> Pending</p>
        
        <div class="footer">
            <p>Thank you for your attention,</p>
            <p>HR Department<br>Contriwhiz Technologies</p>
        </div>
    </div>
</body>
</html>
