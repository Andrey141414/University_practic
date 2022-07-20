<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p>To recover your password follow the link</p>
<a href="<?php echo route('showPasswordResetForm')?>"><?php echo 'password-reset?token='. $token ?></a>

</body>
</html>