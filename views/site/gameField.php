<?php

/**
 * Created by PhpStorm.
 * User: mz
 * Date: 04.12.18
 * Time: 0:27
 */

/* @var $this \yii\web\View */
/* @var $id mixed */
/* @var $name mixed */

\app\assets\XOGameAsset::register($this);

?>
<div id="preloaderbg">
    <div class="preloader-message">Finding opponent...</div>
    <div class="preloader">
        <div class="loader"></div>
    </div>
</div>
<div id="preloader-start">
    <div class="ploader">
        <span class="ploader-inner-1"></span>
        <span class="ploader-inner-2"></span>
        <span class="ploader-inner-3"></span>
        <span class="ploader-inner-4"></span>
    </div>
</div>


    <div class="panel state-data text-center panel-default">
        <div class="panel-body">
            Finding opponent...
        </div>
    </div>

<section class="wrapper">
    <div class="informer"></div>
    <section class="xo">
        <div class="xo__field">
            <div class="xo__row">
                <div class="xo__cells xo__cells-o" data-x="0" data-y="0"></div>
                <div class="xo__cells xo__cells-o xo__cells--border" data-x="1" data-y="0"></div>
                <div class="xo__cells xo__cells-x" data-x="2" data-y="0"></div>
            </div>
            <div class="xo__row  ttoe__row--border">
                <div class="xo__cells xo__cells-o" data-x="0" data-y="1"></div>
                <div class="xo__cells xo__cells-x xo__cells--border" data-x="1" data-y="1"></div>
                <div class="xo__cells xo__cells-o " data-x="2" data-y="1"></div>
            </div>
            <div class="xo__row">
                <div class="xo__cells" data-x="0" data-y="2"></div>
                <div class="xo__cells xo__cells-x xo__cells--border" data-x="1" data-y="2"></div>
                <div class="xo__cells xo__cells-x" data-x="2" data-y="2"></div>
            </div>
        </div>
    </section>
</section>
<div class="jumbotron">
    <p>
        <button id="end-game" class="btn btn-lg btn-success">Abandon game</button>
    </p>
</div>


<script>
    var USER_ID = '<?=$id?>';
    var USER_NAME = '<?=$name?>';
</script>