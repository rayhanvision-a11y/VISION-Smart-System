<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VISION Technologies Limited') }} - Service Portal</title>
        @php 
            $siteFavicon = \App\Models\Setting::get('favicon_path'); 
            $favUrl = $siteFavicon ? asset('storage/' . $siteFavicon) : asset('images/favicon.png');
            $appName = config('app.name', 'VISION Technologies Limited');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $favUrl }}">
        <link rel="apple-touch-icon" href="{{ $favUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            [x-cloak] { display: none !important; }

            *, *::before, *::after {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
                background-color: #060b17;
                color: #f1f5f9;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                overflow-x: hidden;
                position: relative;
            }

            /* Ambient background gradients */
            .ambient-glow-1 {
                position: fixed;
                top: -100px;
                left: -100px;
                width: 450px;
                height: 450px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.28) 0%, rgba(37, 99, 235, 0) 70%);
                filter: blur(50px);
                pointer-events: none;
                z-index: 0;
            }
            .ambient-glow-2 {
                position: fixed;
                top: 30%;
                right: -120px;
                width: 500px;
                height: 500px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(99, 102, 241, 0) 70%);
                filter: blur(60px);
                pointer-events: none;
                z-index: 0;
            }
            .ambient-glow-3 {
                position: fixed;
                bottom: -100px;
                left: 25%;
                width: 450px;
                height: 450px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(14, 165, 233, 0) 70%);
                filter: blur(50px);
                pointer-events: none;
                z-index: 0;
            }
            .grid-overlay {
                position: fixed;
                inset: 0;
                background-image: 
                    linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
                background-size: 40px 40px;
                mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, #000 50%, transparent 100%);
                -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, #000 50%, transparent 100%);
                pointer-events: none;
                z-index: 0;
            }

            /* Header */
            .guest-header {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: relative;
                z-index: 10;
            }
            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(15, 23, 42, 0.85);
                border: 1px solid rgba(255, 255, 255, 0.12);
                padding: 6px 14px;
                border-radius: 9999px;
                font-size: 12px;
                color: #e2e8f0;
                backdrop-filter: blur(10px);
            }
            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #10b981;
                box-shadow: 0 0 10px #10b981;
                flex-shrink: 0;
            }
            .support-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                color: #94a3b8;
                text-decoration: none;
                transition: color 0.15s ease;
            }
            .support-link:hover {
                color: #60a5fa;
            }
            .support-link svg {
                width: 15px !important;
                height: 15px !important;
                max-width: 15px !important;
                max-height: 15px !important;
                flex-shrink: 0;
            }

            /* Center Stage */
            .login-stage {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 24px 16px;
                position: relative;
                z-index: 10;
                width: 100%;
            }
            .login-card {
                width: 100%;
                max-width: 440px;
                background: rgba(15, 23, 42, 0.88);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 24px;
                padding: 36px 32px;
                box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 30px rgba(37, 99, 235, 0.12);
                position: relative;
                overflow: hidden;
            }
            .card-accent-bar {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(90deg, #3b82f6 0%, #6366f1 50%, #06b6d4 100%);
            }

            /* Logo Box */
            .brand-logo-wrap {
                display: flex;
                justify-content: center;
                margin-bottom: 24px;
            }
            .brand-logo-box {
                background: #ffffff;
                border-radius: 16px;
                padding: 10px 22px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
                transition: transform 0.2s ease;
                text-decoration: none;
            }
            .brand-logo-box:hover {
                transform: scale(1.02);
            }
            .brand-logo-box img {
                height: 44px !important;
                max-width: 200px !important;
                width: auto !important;
                object-fit: contain !important;
                display: block !important;
            }

            /* Typography */
            .auth-heading {
                font-size: 22px;
                font-weight: 700;
                color: #ffffff;
                margin: 0 0 6px 0;
                text-align: center;
                letter-spacing: -0.02em;
            }
            .auth-subheading {
                font-size: 13px;
                color: #94a3b8;
                margin: 0 0 26px 0;
                text-align: center;
                line-height: 1.5;
            }

            /* Form Elements */
            .form-field {
                margin-bottom: 20px;
                display: block;
                width: 100%;
            }
            .form-field:last-of-type {
                margin-bottom: 0;
            }
            .field-label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #cbd5e1;
                margin-bottom: 8px;
            }
            .field-label-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 8px;
            }
            .field-label-row .field-label {
                margin-bottom: 0;
            }
            .field-link {
                font-size: 12px;
                color: #60a5fa;
                text-decoration: none;
                font-weight: 500;
                transition: color 0.15s ease;
            }
            .field-link:hover {
                color: #93c5fd;
                text-decoration: underline;
            }

            .field-input-box {
                position: relative;
                width: 100%;
                display: flex;
                align-items: center;
            }
            .field-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                width: 18px !important;
                height: 18px !important;
                max-width: 18px !important;
                max-height: 18px !important;
                color: #64748b;
                pointer-events: none;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2;
                flex-shrink: 0;
            }
            .field-icon svg {
                width: 18px !important;
                height: 18px !important;
                max-width: 18px !important;
                max-height: 18px !important;
                flex-shrink: 0;
            }

            .input-control {
                width: 100% !important;
                height: 48px !important;
                background-color: #1e293b !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                border-radius: 12px !important;
                padding: 0 16px 0 44px !important;
                font-size: 14px !important;
                color: #ffffff !important;
                outline: none !important;
                transition: all 0.2s ease !important;
                font-family: inherit !important;
            }
            .input-control.has-toggle {
                padding-right: 44px !important;
            }
            .input-control:focus {
                background-color: #0f172a !important;
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
            }
            .input-control::placeholder {
                color: #64748b !important;
            }

            .eye-toggle-btn {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                width: 32px !important;
                height: 32px !important;
                background: transparent;
                border: none;
                color: #94a3b8;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                padding: 0;
                transition: all 0.15s ease;
                z-index: 3;
            }
            .eye-toggle-btn:hover {
                color: #ffffff;
                background: rgba(255, 255, 255, 0.08);
            }
            .eye-toggle-btn svg {
                width: 18px !important;
                height: 18px !important;
                max-width: 18px !important;
                max-height: 18px !important;
                flex-shrink: 0;
            }

            /* Error Text */
            .field-error {
                margin: 6px 0 0 0;
                font-size: 12px;
                color: #fb7185;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .field-error svg {
                width: 14px !important;
                height: 14px !important;
                max-width: 14px !important;
                max-height: 14px !important;
                flex-shrink: 0;
            }

            /* Remember row */
            .remember-row {
                display: flex;
                align-items: center;
                margin: 16px 0 22px 0;
            }
            .checkbox-label {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: #cbd5e1;
                cursor: pointer;
                user-select: none;
            }
            .custom-checkbox {
                width: 16px !important;
                height: 16px !important;
                accent-color: #2563eb !important;
                cursor: pointer;
                margin: 0;
            }

            /* Submit Button */
            .btn-primary-action {
                width: 100% !important;
                height: 48px !important;
                background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
                border: none !important;
                border-radius: 12px !important;
                color: #ffffff !important;
                font-size: 15px !important;
                font-weight: 600 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                cursor: pointer !important;
                box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4) !important;
                transition: all 0.2s ease !important;
                font-family: inherit !important;
                text-decoration: none !important;
            }
            .btn-primary-action:hover {
                background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%) !important;
                box-shadow: 0 6px 22px rgba(37, 99, 235, 0.6) !important;
                transform: translateY(-1px);
            }
            .btn-primary-action:active {
                transform: translateY(0);
            }
            .btn-primary-action svg {
                width: 18px !important;
                height: 18px !important;
                max-width: 18px !important;
                max-height: 18px !important;
                flex-shrink: 0;
                transition: transform 0.2s ease;
            }
            .btn-primary-action:hover svg {
                transform: translateX(3px);
            }

            /* Security assurance badge */
            .security-foot {
                margin-top: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 16px;
                font-size: 12px;
                color: #64748b;
            }
            .security-foot span {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .security-foot svg {
                width: 14px !important;
                height: 14px !important;
                max-width: 14px !important;
                max-height: 14px !important;
                flex-shrink: 0;
            }

            /* Footer */
            .guest-footer {
                width: 100%;
                text-align: center;
                padding: 20px 16px;
                font-size: 12px;
                color: #475569;
                position: relative;
                z-index: 10;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        
        <!-- Ambient Glowing Background Elements -->
        <div class="ambient-glow-1"></div>
        <div class="ambient-glow-2"></div>
        <div class="ambient-glow-3"></div>
        <div class="grid-overlay"></div>

        <!-- Top Header Bar -->
        <header class="guest-header">
            <!-- Left: Operational Status -->
            <div class="status-pill">
                <span class="status-dot"></span>
                <span style="font-weight: 500;">System Operational</span>
                <span style="color: #475569;">|</span>
                <span style="color: #94a3b8;">ISP Core Gateway</span>
            </div>

            <!-- Right: Support Helpline -->
            <div>
                <a href="tel:09613828828" class="support-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #3b82f6;">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>Helpline: <strong style="color: #cbd5e1;">09613828828</strong></span>
                </a>
            </div>
        </header>

        <!-- Main Card Section -->
        <main class="login-stage">
            <div class="login-card">
                <div class="card-accent-bar"></div>

                <!-- Company Logo -->
                <div class="brand-logo-wrap">
                    <a href="/" class="brand-logo-box">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Card Body (Slot) -->
                {{ $slot }}

            </div>

            <!-- Trust & Security Assurance Footnote -->
            <div class="security-foot">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    256-Bit SSL Encrypted
                </span>
                <span style="color: #334155;">•</span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #3b82f6;">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    High Availability Core
                </span>
            </div>
        </main>

        <!-- Footer -->
        <footer class="guest-footer">
            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </footer>

    </body>
</html>
