<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form method="post">
@csrf <!-- {{ csrf_field() }} -->

    <h1>Подтвердите email</h1>
    <a href="{{route (verification.send)}}">Повторная отправка</a>
</form>
</body>
</html>