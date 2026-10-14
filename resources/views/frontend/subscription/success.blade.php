<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <title>Subscription Confirmed - NumoniX</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 40px 20px; background-color: #f8fafc; text-align: center; color: #1e293b; margin: 0; }
        .card { max-width: 540px; margin: 40px auto; background: white; padding: 48px 40px; border-radius: 12px; box-shadow: 0 4px 16px -2px rgba(0,0,0,0.08); }
        .icon { font-size: 56px; margin-bottom: 16px; }
        h1 { margin: 0 0 8px; font-size: 26px; color: #0f172a; }
        .badge { display: inline-block; background: #ecfdf5; color: #059669; font-weight: 700; padding: 6px 18px; border-radius: 9999px; margin-bottom: 24px; font-size: 13px; letter-spacing: 0.02em; text-transform: uppercase; }
        p { line-height: 1.65; color: #475569; margin-bottom: 16px; font-size: 15px; }
        .features { text-align: left; background: #f8fafc; border-radius: 8px; padding: 20px 24px; margin: 24px 0; list-style: none; }
        .features li { padding: 6px 0; color: #334155; font-size: 15px; }
        .features li::before { content: "✓ "; color: #059669; font-weight: 700; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
        .note { font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🎉</div>
        <div class="badge">Payment Successful</div>
        <h1>You're in - welcome to NumoniX!</h1>
        <p>Your subscription is now active and your account has been upgraded. A confirmation email is on its way.</p>

        <ul class="features">
            <li>All premium lessons &amp; Core Theory</li>
            <li>Full Functional Ear Training access</li>
            <li>Quizzes and progress tracking</li>
            @auth
                @if(auth()->user()->is_founder)
                <li>Your £24.99/year Founder's rate - locked in for life</li>
                @endif
            @endauth
        </ul>

        <hr class="divider">

        <p><strong>What's next?</strong><br>
        Open the NumoniX app on your device and sign in - your premium access will be active immediately.</p>

        <p class="note">Questions? Email us at <a href="mailto:support@example.com" style="color:#2563eb;">support@example.com</a></p>
    </div>
</body>
</html>

