<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
</head>

<body>

    <h2>Hello {{ $user->first_name }}</h2>

    <p>
        Thank you for registration.
    </p>

    <p>
        Use the following OTP:
    </p>

    <h1>{{ $otp }}</h1>

    <p>
        This OTP will expire in 10 minutes.
    </p>

    <p>
        Ignore this email if you did not request it.
    </p>

</body>

</html>
