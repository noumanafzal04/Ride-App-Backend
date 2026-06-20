@php
    $r = $inspectionRequest;
    $car = trim(collect([$r->car_year, $r->car_make, $r->car_model])->filter()->implode(' '));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZRide Car Inspection</title>
</head>
<body style="margin:0;padding:0;background:#F5F5F7;font-family:Arial,Helvetica,sans-serif;color:#202223;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F5F7;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #EAEDEE;">
                    <!-- Header -->
                    <tr>
                        <td style="background:#07163B;padding:20px 28px;">
                            <span style="color:#FFD400;font-size:20px;font-weight:bold;">EZRide</span>
                            <span style="color:#FFFFFF;font-size:13px;"> · Car Inspection</span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 8px;font-size:20px;color:#07163B;">{{ $heading }}</h1>
                            <p style="margin:0 0 20px;font-size:14px;line-height:22px;color:#5D5F62;">
                                Hi {{ $r->name ?: 'there' }},<br>{{ $body }}
                            </p>

                            <!-- Car / request summary -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F9FAFB;border:1px solid #EAEDEE;border-radius:10px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0 0 6px;font-size:13px;color:#9AA0A6;">Request #{{ $r->id }}</p>
                                        <p style="margin:0 0 4px;font-size:16px;font-weight:bold;color:#07163B;">{{ $car ?: 'Car inspection' }}</p>
                                        @if($r->registration_no)
                                            <p style="margin:0 0 4px;font-size:13px;color:#5D5F62;">Reg: {{ $r->registration_no }}</p>
                                        @endif
                                        <p style="margin:8px 0 0;font-size:13px;color:#5D5F62;">
                                            Status: <strong style="color:#07163B;text-transform:capitalize;">{{ str_replace('_',' ',$r->status) }}</strong>
                                        </p>
                                        @if($r->scheduled_at)
                                            <p style="margin:4px 0 0;font-size:13px;color:#5D5F62;">Scheduled: {{ $r->scheduled_at->format('d M Y, h:i A') }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if($r->status === 'completed' && $r->overall_grade)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;background:#E8F8EE;border-radius:10px;">
                                    <tr>
                                        <td style="padding:16px;text-align:center;">
                                            <span style="font-size:13px;color:#5D9C6B;">Overall result</span><br>
                                            <span style="font-size:28px;font-weight:bold;color:#109F2A;">Grade {{ $r->overall_grade }}</span>
                                            @if($r->overall_score !== null)
                                                <span style="font-size:16px;color:#109F2A;"> · {{ rtrim(rtrim((string) $r->overall_score, '0'), '.') }}%</span>
                                            @endif
                                            @if($r->inspector_comments)
                                                <p style="margin:10px 0 0;font-size:13px;color:#3B3E40;">{{ $r->inspector_comments }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if($r->tracking_token)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;border:1px dashed #D7DBDE;border-radius:10px;">
                                    <tr>
                                        <td style="padding:14px 16px;text-align:center;">
                                            <span style="font-size:12px;color:#9AA0A6;">Track your request anytime with code</span><br>
                                            <span style="font-size:20px;font-weight:bold;letter-spacing:2px;color:#07163B;">{{ $r->tracking_token }}</span>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:24px 0 0;font-size:13px;line-height:20px;color:#9AA0A6;">
                                Questions? Reply to this email or call our team. We’ll keep you posted at each step.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#F9FAFB;padding:16px 28px;border-top:1px solid #EAEDEE;">
                            <p style="margin:0;font-size:12px;color:#9AA0A6;">© {{ date('Y') }} EZRide. This is an automated message.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
