<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body style="
    text-align: center;
    font-size: larger;">
    <img src="https://in-good-hands.dev.mind4.me/logo.jpg">

    @if (!isset($rejectionReason))
        <p>Ваше объявление "<?php echo ($name); ?></a>" опубликованно</p>
    @else
        <p>Ваше объявление "<?php echo ($name); ?></a>" не опубликованно</p>
        <p>Причина: "<?php echo ($rejectionReason); ?></a>"</p>
    @endif
</body>

</html>