<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Owner Dashboard</h2>

    <p>Welcome, {{ auth()->user()->name }}</p>

    <p>Role: Owner</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

</body>
</html>