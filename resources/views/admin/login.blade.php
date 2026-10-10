<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Pemilu</title>
</head>
<body style="background-color: #f9f9f9; font-family: sans-serif; padding: 20px;">
    <div style="max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="text-align: center;">Login Admin Panel</h2>

        @if($errors->any())
            <div style="color: red; margin-bottom: 15px; text-align: center;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/admin/login" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="username">Username:</label><br>
                <input type="text" name="username" id="username" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="password">Password:</label><br>
                <input type="password" name="password" id="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            
            <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Login Admin</button>
        </form>
    </div>
</body>
</html>