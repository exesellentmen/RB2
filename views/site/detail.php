<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';?>

<?if(!isset($news)):?>
    <h2>Новости отсутствуют</h2>
<?else:?>
    <main class="bd-masthead" id="content" role="main">
        <div class="container">
            <div class="row">
                <?if(isset(current($news)["PICTURE"])):?>
                    <div class="col-6 mx-auto col-md-4 order-md-2">
                        <img src="<?=current($news)["PICTURE"]?>" alt="<?=current($news)["TITLE"]?>" style="width: 300px" class="Responsive image">
                    </div>
                    <div class="col-md-8 order-md-1 text-center text-md-left pr-md-5">
                        <h1 class="mb-3"><?=current($news)["TITLE"]?></h1>
                            <p class="lead mb-4">
                                <?=current($news)["DESCRIPTION"]?>
                            </p>
                    </div>
                <?else:?>
                    <h1 class="mb-3"><?=current($news)["TITLE"]?></h1>
                    <?=current($news)["DESCRIPTION"]?>
                <?endif;?>
            </div>
        </div>
    </main>
<?endif;?>