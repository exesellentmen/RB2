<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';?>

<div class="site-index">
    <?if(isset($mes)):?>
        Новости успешно обновлены
    <?endif;?>

    <div class="jumbotron">
        <h1>Новости</h1>
    </div>
    <div class="body-content">
        <div class="row">
            <?if(!isset($news)):?>
                <h2>Новости отсутствуют</h2>
            <?else:?>
            <?foreach ($news as $newsItew):?>
                <div class="col-lg-3">
                    <h2><?=$newsItew["TITLE"]?></h2>
                    <p><?=$newsItew["ANNOUNCEMENT"]?></p>
                    <p><a class="btn btn-default" href="/index.php?r=site%2Fnewsdetail&idnews=<?=$newsItew["ID"]?>">Подробнее</a></p>
                </div>
            <?endforeach;?>
            <?endif;?>
        </div>
        <?// Pagination
        if(isset($pages)) {
            echo \yii\widgets\LinkPager::widget([
                'pagination' => $pages,
            ]);
        }
        ?>
    </div>

</div>
