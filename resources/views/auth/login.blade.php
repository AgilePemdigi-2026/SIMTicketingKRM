<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Wisatawan - SIMTicketingKRM</title>
</head>
<body>

    <h1>Login Wisatawan</h1>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div>
            <label for="login">Email atau Nomor Telepon</label>
            <input
                type="text"
                id="login"
                name="login"
                value="{{ old('login') }}"
                required
            >
        </div>

        <div>
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit">Masuk</button>
    </form>

    <p>
        Belum punya akun?
        <a href="{{ route('register') }}">Daftar sekarang</a>
    </p>

</body>
</html>
