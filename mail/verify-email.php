<?php


/**
 * @var string $name
 * @var string $otp
 */

?>

<h2>Hello <?= \yii\helpers\Html::encode($name) ?></h2>

<p>Your verification code is:</p>

<h1><?= $otp ?></h1>

<p>This code expires in 10 minutes.</p>
