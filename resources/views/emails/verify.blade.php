<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Welcome to AfricanStreams</title>
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
            background-color: #1a73e8;
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
            <h2>Welcome to AfricanStreams! 🎉</h2>

            <p>Hi there,</p>

            <p>Thank you for signing up. Please verify your email address to complete your registration and get started:
            </p>

            <a href="{{ $frontendUrl }}/#/verify-email?token={{ $token }}" class="button">Verify Email</a>

            <p>If the button above doesn't work, you can also copy and paste this link into your browser:</p>
            <p><a
                    href="{{ $frontendUrl }}/#/verify-email?token={{ $token }}">{{ $frontendUrl }}/verify?token={{ $token }}</a>
            </p>

            <p>If you did not create an account with us, you can safely ignore this email.</p>

            <div class="footer">
                &copy; AfricanStreams. All rights reserved.
            </div>
        </div>
    </body>

</html>