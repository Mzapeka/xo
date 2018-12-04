<?php

namespace app\controllers;

use app\models\Engine;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
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
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTik()
    {
        return $this->render(
            'gameField'
        );
    }


    /**
     * @param $name
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionStart($name)
    {
        if (!Yii::$app->session->isActive) {
            Yii::$app->session->open();
            Yii::$app->session->set('name', Html::encode($name));
            $userId = Yii::$app->security->generateRandomString();
            Yii::$app->session->set('userId', $userId);
            /**
             * @var Engine $engin
             */
            $engin = Yii::$container->get(Engine::class);
            $game = $engin->startGame($userId);
            if ($game) {
                $gameData = json_encode([
                    'gameId' => $game->id,
                    'board' => $game->board,
                    'currentTurn' => $game->currentTurn,
                    'winner' => $game->winner
                ]);
                Yii::$app->cache->set('message' . $game->opponent, $gameData, 60);
                Yii::$app->cache->set('message' . $game->user, $gameData, 60);
            }
        }
        return $this->render(
            'gameField',
            ['id' => Yii::$app->session->get('userId'), 'name' => Yii::$app->session->get('name')]
        );
    }
}
