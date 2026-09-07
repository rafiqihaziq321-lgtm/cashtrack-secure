<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Dashboard - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Cashier Dashboard</h2>

    <p>Welcome, {{ auth()->user()->name }}</p>

    <p>Role: Cashier</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

</body>
</html>