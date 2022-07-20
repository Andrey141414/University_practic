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
<a href="<?php echo ('http://46.50.152.115:3000/reset-password'.'?token='. $token)?>"><?php echo 'password-reset?token='. $token ?></a>

</body>
</html>