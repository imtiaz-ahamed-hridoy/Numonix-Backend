<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="NumoniX Founder Activation - Your 30-day pass is active." />
    <title>Founder Pass Activated - NumoniX</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 40px 16px 60px;
            background: linear-gradient(135deg, #091e3a 0%, #0d2b4f 50%, #004b6e 100%);
            min-height: 100vh;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            max-width: 560px;
            width: 100%;
            background: white;
            padding: 48px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0284c7;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 6px 16px;
            border-radius: 999px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 12px;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .trial-box {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: left;
        }
        .trial-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .trial-row:last-child { margin-bottom: 0; }
        .trial-label { color: #64748b; font-weight: 500; }
        .trial-value { color: #0f172a; font-weight: 700; }
        .tier-badge {
            background: #0ea5e9;
            color: white;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-sizing: border-box;
            border: none;
            margin-bottom: 12px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.5);
        }
        .footer-note {
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">NumoniX Founder Programme</div>
        <h1>Welcome, {{ $user->name ?: 'Founder' }}!</h1>
        <p class="subtitle">
            Your 30-day Founder Pass has been activated successfully. You now have full access to all Tier 2 features across the app.
        </p>

        <div class="trial-box">
            <div class="trial-row">
                <span class="trial-label">Membership Tier:</span>
                <span class="trial-value"><span class="tier-badge">Tier 2 Pass (Top Access)</span></span>
            </div>
            <div class="trial-row">
                <span class="trial-label">Registered Email:</span>
                <span class="trial-value">{{ $user->email }}</span>
            </div>
            <div class="trial-row">
                <span class="trial-label">Trial Duration:</span>
                <span class="trial-value">30 Days</span>
            </div>
            <div class="trial-row">
                <span class="trial-label">Active Until:</span>
                <span class="trial-value">{{ $trialEndsAt ? $trialEndsAt->format('F j, Y') : '30 days from now' }}</span>
            </div>
        </div>

        <a href="numonix://app" class="btn btn-primary">Open NumoniX App</a>

        <p class="footer-note">
            Log in on the NumoniX mobile app using <strong>{{ $user->email }}</strong>. If you haven't set a password yet, use "Forgot Password" on the app login screen.
        </p>
    </div>
</body>
</html>