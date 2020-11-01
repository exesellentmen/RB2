<?php


namespace app\models;


use yii\db\ActiveRecord;
use Yii;

class News extends ActiveRecord
{
    public function UpdateDBNews($RowsFromParser){
        //Getting duplicate records from the database
        $newsDBArray = self::find()->where(['GUID' => array_column($RowsFromParser, "GUID")])->asArray()->all();
        $newsDBArray = array_column($newsDBArray, "GUID");

        //Preparing an array of records
        $RowsForDB = [];
        foreach ($RowsFromParser as $ItemNews) {
            if(!in_array($ItemNews["GUID"],$newsDBArray)) {
                $RowsForDB[] = array_values($ItemNews);
            }
        }

        //Sending a request to add records to the database
        Yii::$app->db->createCommand()->batchInsert('news', ['GUID', 'TITLE', 'ANNOUNCEMENT', 'DATETIME', 'PICTURE', 'DESCRIPTION'], $RowsForDB)->execute();

        return true;
    }
}