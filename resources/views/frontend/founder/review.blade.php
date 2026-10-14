<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="NumoniX Founder Feedback - Share your thoughts and help shape the product." />
    <title>Founder Feedback - NumoniX</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 40px 16px 60px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #1e293b;
            margin: 0;
        }
        .card {
            max-width: 580px;
            margin: 0 auto;
            background: white;
            padding: 44px 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px -4px rgba(0,0,0,0.12);
        }
        /* Header */
        .brand { font-size: 13px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
        h1 { font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
        .subtitle { color: #64748b; font-size: 15px; margin-bottom: 28px; line-height: 1.5; }
        /* Info box */
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 28px;
            color: #1e40af;
            font-size: 14px;
            line-height: 1.55;
        }
        /* Star rating */
        .form-group { margin-bottom: 22px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            gap: 6px;
            margin-bottom: 4px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 36px;
            cursor: pointer;
            color: #d1d5db;
            transition: color 0.15s;
            margin: 0;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #f59e0b;
        }
        /* Fallback select for JS-disabled */
        .star-fallback { display: none; }
        /* Textarea */
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s;
            color: #1e293b;
        }
        textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .optional-badge { font-weight: 400; color: #94a3b8; font-size: 12px; }
        /* Submit button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 4px;
        }
        .btn-submit:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        /* Error messages */
        .error { color: #dc2626; font-size: 13px; margin-top: 4px; }
        /* Mobile */
        @media (max-width: 620px) {
            .card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">NumoniX</div>
        <h1>Founder Feedback</h1>
        <p class="subtitle">Your thoughts help shape the future of NumoniX. This is completely optional and takes less than 2 minutes.</p>

        <div class="info-box">
            🔒 Your exclusive <strong>£24.99/year lifetime checkout link</strong> will be emailed to you automatically at the end of your 30-day trial - no action required and this form has no effect on when or whether you receive it.
        </div>

        @if ($errors->any())
            <div class="info-box" style="background:#fef2f2; border-color:#fecaca; border-left-color:#dc2626; color:#991b1b;">
                <strong>Please fix the following:</strong>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('founder.review.submit') }}">
            @csrf

            {{-- Star Rating --}}
            <div class="form-group">
                <label>How would you rate NumoniX overall? <span style="color:#ef4444;">*</span></label>

                <div class="star-rating">
                    @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                        <label for="star{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">&#9733;</label>
                    @endfor
                </div>

                {{-- Accessible fallback --}}
                <select name="rating_fallback" class="star-fallback">
                    <option value="">- Select a rating -</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>

                @error('rating') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- Written feedback --}}
            <div class="form-group">
                <label for="feedback">
                    Any other thoughts?
                    <span class="optional-badge">(Optional)</span>
                </label>
                <textarea
                    id="feedback"
                    name="feedback"
                    rows="5"
                    maxlength="2000"
                    placeholder="Tell us what you love, what you'd improve, or anything else on your mind...">{{ old('feedback') }}</textarea>
                @error('feedback') <div class="error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-submit">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
