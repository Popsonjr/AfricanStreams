<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Reset Your Password - AfricanStreams</title>
        <style>
        .container {
            max-width: 600px;
            margin: auto;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 40px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .button {
            display: inline-block;
            background-color: #e53935;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
        </style>
    </head>

    <body>
        <div class="container">
            <h2>Password Reset Request</h2>

            <p>Hello there,</p>

            <p>We received a request to reset your password. Click the button below to continue:</p>

            <a href="{{ $frontendUrl }}/reset-password?token={{ $token }}" class="button">Reset
                Password</a>

            <p>If the button doesn’t work, copy and paste the following link into your browser:</p>
            <p><a
                    href="{{ $frontendUrl }}/reset-password?token={{ $token }}">{{ $frontendUrl }}/reset-password?token={{ $token }}</a>
            </p>

            <p>If you didn’t request a password reset, no action is needed. Your account is safe.</p>

            <div class="footer">
                &copy; AfricanStreams. All rights reserved.
            </div>
        </div>
    </body>

</html>