<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* -- Reset ------------------------------------------- */
        * { box-sizing: border-box; }
        body, html {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                         Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.65;
            -webkit-text-size-adjust: 100%;
        }
        /* -- Outer wrapper ------------------------------------ */
        .wrapper {
            width: 100%;
            padding: 48px 16px;
            background-color: #f1f5f9;
        }
        /* -- Card --------------------------------------------- */
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.10);
        }
        /* -- Brand header ------------------------------------- */
        .brand-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 60%, #3b82f6 100%);
            text-align: center;
            padding: 32px 40px 28px;
        }
        .brand-header .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-header .brand-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
            margin: 4px 0 0;
            letter-spacing: 0.02em;
        }
        /* -- Body copy ---------------------------------------- */
        .body-wrap {
            padding: 36px 40px 28px;
        }
        p {
            margin: 0 0 16px;
            font-size: 15px;
            color: #334155;
        }
        ul {
            padding-left: 20px;
            margin: 0 0 16px;
        }
        ul li {
            font-size: 15px;
            color: #334155;
            margin-bottom: 6px;
        }
        strong {
            color: #1e293b;
        }
        a {
            color: #2563eb;
            text-decoration: underline;
        }
        /* -- Primary CTA button ------------------------------- */
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            margin: 8px 0 20px;
            letter-spacing: 0.01em;
        }
        .button:hover {
            background: #1d4ed8;
        }
        /* -- Divider ------------------------------------------ */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 8px 0 24px;
        }
        /* -- Footer ------------------------------------------- */
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            padding: 20px 40px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.7;
        }
        .footer a {
            color: #64748b;
            text-decoration: none;
        }
        /* -- Mobile ------------------------------------------- */
        @media only screen and (max-width: 620px) {
            .body-wrap { padding: 28px 24px 20px; }
            .brand-header { padding: 24px 24px 20px; }
            .footer { padding: 16px 24px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">

            {{-- Brand header --}}
            <div class="brand-header">
                <p class="brand-name">NumoniX</p>
                <p class="brand-tagline">Functional Ear Training &amp; Music Theory</p>
            </div>

            {{-- Email body --}}
            <div class="body-wrap">
                @yield('content')
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p>&copy; {{ date('Y') }} NumoniX. All rights reserved.</p>
                <p>You received this email because you are a NumoniX user or Founder Programme member.</p>
                <p><a href="mailto:support@example.com">support@example.com</a></p>
            </div>

        </div>
    </div>
</body>
</html>
