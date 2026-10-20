<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wisatawan - SIMTicketingKRM</title>
</head>
<body>

    <h1>Dashboard Wisatawan - SIMTicketingKRM</h1>

    <p>Selamat datang, {{ auth()->user()->name }}!</p>

    <p>Role: {{ auth()->user()->role }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit">
            Logout
        </button>
    </form>

</body>
</html>
