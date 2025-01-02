<!DOCTYPE html>
<html>
<head>
    <title>Leave Application</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #eef2f7;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Container Styles */
        .container {
            width: 90%;
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header Styles */
        h1 {
            font-size: 28px;
            color: #2b7a78;
            background-color: #def2f1;
            text-align: center;
            margin: 0;
            padding: 20px 10px;
            border-bottom: 2px solid #2b7a78;
        }

        /* Text Styles */
        p {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
        }

        p strong {
            color: #333;
        }

        /* Detail Box Styles */
        .detail {
            background-color: #f8f9fa;
            border-left: 5px solid #2b7a78;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Footer Styles */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
            padding: 10px 0;
            background-color: #f0f0f0;
            border-top: 1px solid #ddd;
        }

        /* Button Styles (optional, if buttons are added later) */
        button {
            display: inline-block;
            background-color: #2b7a78;
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #21625e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Leave Application</h1>
        <p><strong>{{ $leaveDetails['user_name'] }}</strong> applied for <strong>{{ $leaveDetails['type_of_leave'] }}</strong>.</p>
        <div class="detail">
            <p><strong>Date:</strong> {{ $leaveDetails['start_date'] }} to {{ $leaveDetails['end_date'] ?? 'N/A' }}</p>
            <p><strong>Time:</strong> 
                {{ $leaveDetails['start_time'] ?? 'N/A' }} to {{ $leaveDetails['end_time'] ?? 'N/A' }}
            </p>
        </div>
        <p><strong>Reason:</strong> {{ $leaveDetails['reason'] }}</p>
        <p><strong>Contact During Leave:</strong> {{ $leaveDetails['contact_during_leave'] }}</p>
        <p><strong>Status:</strong> Pending</p>
        <div class="footer">
            <p>Thank you,</p>
            <p>HR Department, Contriwhiz Technologies</p>
        </div>
    </div>
</body>
</html>
