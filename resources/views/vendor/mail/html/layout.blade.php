<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ config('app.name') }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .outer-padding {
                padding: 0 !important;
            }
            .inner-body {
                width: 100% !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .footer {
                width: 100% !important;
            }
            .content-cell {
                padding: 28px 20px !important;
            }
            .header-cell {
                padding: 20px !important;
            }
            .footer-cell {
                padding: 20px !important;
            }
            .button {
                width: 100% !important;
                display: block !important;
                padding: 16px 24px !important;
                font-size: 16px !important;
                box-sizing: border-box !important;
            }
            .action-table {
                width: 100% !important;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            body,
            .body {
                background-color: #111111 !important;
            }
            .inner-body {
                background-color: #1a1a1a !important;
            }
            .header-cell {
                background-color: #1a1a1a !important;
                border-bottom-color: #333333 !important;
            }
            .logo-text {
                color: #ffffff !important;
            }
            .content-cell,
            .content-cell p,
            .content-cell td,
            .content-cell li {
                color: #e4e4e7 !important;
            }
            .content-cell h1,
            .content-cell h2,
            .content-cell h3 {
                color: #ffffff !important;
            }
            .footer-cell {
                background-color: #111111 !important;
                border-top-color: #333333 !important;
            }
            .footer-cell p,
            .footer-cell a {
                color: #888888 !important;
            }
            .panel-content {
                background-color: #222222 !important;
                border-color: #333333 !important;
            }
        }
    </style>
</head>
<body style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; position: relative; -webkit-text-size-adjust: none; background-color: #f5f5f5; color: #1a1a1a; height: 100%; line-height: 1.6; margin: 0; padding: 0; width: 100% !important;">
    <table class="body" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td class="outer-padding" align="center" style="padding: 40px 20px;">
                <table class="inner-body" width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border-radius: 12px; margin: 0 auto; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                    <!-- Header -->
                    <tr>
                        <td class="header-cell" style="background-color: #ffffff; padding: 28px 45px; text-align: center; border-bottom: 1px solid #eaeaea;">
                            <span class="logo-text" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 26px; font-weight: 700; color: #1a1a1a; text-decoration: none; letter-spacing: -0.5px;">&#10024; Stylely</span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="content-cell" style="padding: 40px 45px; color: #1a1a1a;">
                            {!! Illuminate\Mail\Markdown::parse($slot) !!}

                            {{ $subcopy ?? '' }}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer-cell" style="background-color: #fafafa; border-top: 1px solid #eaeaea; padding: 25px 45px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <p style="color: #666666; font-size: 13px; line-height: 1.5; margin: 0; text-align: center;">
                                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                        </p>
                                        <p style="color: #888888; font-size: 12px; margin: 10px 0 0; text-align: center;">
                                            <a href="{{ config('app.url') }}/privacy" style="color: #1a1a1a; text-decoration: underline;">Privacy Policy</a>
                                            &nbsp;&middot;&nbsp;
                                            <a href="{{ config('app.url') }}/terms" style="color: #1a1a1a; text-decoration: underline;">Terms of Service</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
