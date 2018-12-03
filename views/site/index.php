<?php

/* @var $this yii\web\View */

use app\assets\XOAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

$this->title = 'XO Battle';
XOAsset::register($this);
?>
<div class="notification"></div>

<div class="site-index">
    <div class="jumbotron">
        <p><button id="enter-name" class="btn btn-lg btn-success">Enter Name</button></p>
        <p><button id="start-game" class="btn btn-warning">Start Game</button></p>
    </div>
</div>

<?php Modal::begin([
    'header' => 'Enter Name',
    'id' => 'add-name-modal',
])?>

<?= Html::input('text', null, null, ['id' => 'input-name'])?>
<?= Html::button('Ok', ['id' => 'submit-name'])?>

<?php Modal::end();