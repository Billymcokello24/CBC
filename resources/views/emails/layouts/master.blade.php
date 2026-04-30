<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        /* Base Styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
            width: 100%;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        /* Container */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
        }

        .main {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Header */
        .header {
            padding: 40px 0;
            text-align: center;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }

        .header img {
            max-width: 180px;
        }

        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
        }

        /* Content Area */
        .content {
            padding: 48px 40px;
            color: #334155;
            line-height: 1.6;
        }

        .content h1 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 24px;
            letter-spacing: -0.025em;
        }

        .content p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        /* Buttons */
        .button-wrapper {
            margin: 32px 0;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        /* Footer */
        .footer {
            padding: 40px;
            text-align: center;
            background-color: #f1f5f9;
            color: #64748b;
            font-size: 14px;
        }

        .footer p {
            margin: 8px 0;
        }

        .social-links {
            margin-bottom: 16px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #94a3b8;
            text-decoration: none;
        }

        /* Utilities */
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 32px 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #e0e7ff;
            color: #4338ca;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Mobile */
        @media screen and (max-width: 600px) {
            .content {
                padding: 32px 24px;
            }
            .header {
                padding: 32px 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div style="height: 40px;"></div>
        <div class="main">
            <!-- Header -->
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <!-- Content -->
            <div class="content">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="social-links">
                    <a href="#">Official Website</a> &bull;
                    <a href="#">Staff Portal</a> &bull;
                    <a href="#">Support</a>
                </div>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div style="margin-top: 16px; font-size: 11px; color: #94a3b8; line-height: 1.4;">
                    <p><strong>{{ config('app.name') }} HQ</strong><br>
                    Institutional Administration Complex, Kenya<br>
                    This is a transactional notification regarding your professional account.</p>
                    <p>To manage your notification preferences, please visit your account settings.</p>
                </div>
            </div>
        </div>
        <div style="height: 40px;"></div>
    </div>
</body>
</html>
