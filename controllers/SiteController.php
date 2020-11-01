<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\News;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        $request = Yii::$app->request;
        $mes = $request->get('mes');

        $query = News::find()->orderBy(['ID' => SORT_DESC]);
        $countQuery = clone $query;
        $pages = new \yii\data\Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return $this->render('index', [
            'news' => $models,
            'pages' => $pages,
            'mes' => $mes,
        ]);
    }


    /**
     * Update News.
     *
     * @return string
     */
    public function actionNewsupdate()
    {
        //Parse News
        $arNews = \app\components\Parser::CollectList();

        //Update News
        News::UpdateDBNews($arNews);

        $query = News::find()->orderBy(['ID' => SORT_DESC]);
        $countQuery = clone $query;
        $pages = new \yii\data\Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $this->redirect('/?mes=yes');
        return $this->render('index', [
            'news' => $models,
            'pages' => $pages,
        ]);
    }


    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionNewsdetail()
    {
        $request = Yii::$app->request;
        $id = $request->get('idnews');
        $news = News::find()->where(['ID'=>$id])->orderBy(['ID' => SORT_DESC])->asArray()->all();
        return $this->render('detail',
            ['news' => $news]
        );
    }


    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
