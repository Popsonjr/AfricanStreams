<!DOCTYPE html>
<html>

    <head>
        <title>New Contact Message</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #1a73e8;">New Contact Message</h2>
            <p><strong>Full Name:</strong> {{ $fullname }}</p>
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Subject:</strong> {{ $subject }}</p>
            @if($company)
            <p><strong>Company:</strong> {{ $company }}</p>
            @endif
            <p><strong>Message:</strong></p>
            <p style="background: #f9f9f9; padding: 10px; border-radius: 4px;">{{ $message }}</p>
            <p style="margin-top: 20px;">Please respond to this inquiry promptly.</p>
            <p>Best regards,<br>AfricanStreams System</p>
        </div>
    </body>

</html>