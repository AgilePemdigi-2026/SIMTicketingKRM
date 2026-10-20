<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Wisatawan - SIMTicketingKRM</title>
</head>
<body>
    <h1>Daftar Akun Wisatawan</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <input type="text" name="name"
            value="{{ old('name') }}" placeholder="Nama Lengkap" required>

        <input type="text" name="identity_number"
            value="{{ old('identity_number') }}" placeholder="Nomor Identitas" required>

        <input type="tel" name="phone"
            value="{{ old('phone') }}" placeholder="Nomor Telepon" required>

        <input type="email" name="email"
            value="{{ old('email') }}" placeholder="Email" required>

        <input type="password" name="password"
            placeholder="Password" required>

        <input type="password" name="password_confirmation"
            placeholder="Konfirmasi Password" required>

        <select name="visitor_type" required>
            <option value="">Pilih Jenis Wisatawan</option>
            <option value="domestik">Domestik</option>
            <option value="mancanegara">Mancanegara</option>
        </select>

        <label>
            <input type="checkbox" name="terms" value="1" required>
            Saya menyetujui syarat dan ketentuan.
        </label>

        <button type="submit">Daftar</button>
    </form>
</body>
</html>
