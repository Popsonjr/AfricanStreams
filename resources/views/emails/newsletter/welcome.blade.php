<!DOCTYPE html>
<html>
<head>
    <title>Welcome to AfricanStreams Newsletter</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #1a73e8;">Welcome to AfricanStreams!</h2>
        <p>Thank you for subscribing to our newsletter! We're excited to keep you updated with the latest movies, exclusive offers, and news from AfricanStreams.</p>
        <p>Stay tuned for our weekly updates!</p>
        <p style="margin-top: 20px;">Best regards,<br>The AfricanStreams Team</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777;">
            You received this email because you subscribed to our newsletter.
            To unsubscribe, click <a href="{{ url('/newsletter/unsubscribe?email=' . urlencode($email)) }}" style="color: #1a73e8;">here</a>.
        </p>
    </div>
</body>
</html>