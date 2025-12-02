<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
</head>
<body>
    <h1>Hello, {{ $user->name ?? 'User' }}</h1>
    <p>Please click the button below to verify your email address.</p>
    <a href="{{ $url }}">Verify Email</a>
    <p>If you did not create an account, no further action is required.</p>
</body>
</html>
