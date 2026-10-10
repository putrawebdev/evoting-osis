<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilu</title>
</head>
<body>
    <h2>Login Pemilu Ketua & Wakil Ketua OSIS</h2>

    <!-- Menampilkan pesan error dari backend -->
    @if($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Menampilkan pesan sukses (misal: setelah logout otomatis) -->
    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="/login" method="POST">
        @csrf
        <label for="nis">Masukkan NIS Anda:</label><br><br>
        <input type="text" name="nis" id="nis" required placeholder="Contoh: 12345"><br><br>
        <button type="submit">Masuk</button>
    </form>
</body>
</html>