<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 03.12.18
 * Time: 23:18
 */

namespace app\controllers;

use app\models\Game;
use Yii;
use yii\base\Module;
use yii\rest\Controller;
use yii\web\HttpException;

class StatusController extends Controller
{
    private $userId;

    /**
     * StatusController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     * @throws HttpException
     */
    public function __construct(string $id, Module $module, array $config = [])
    {
        $this->userId = Yii::$app->session->get('userId');
        if ($this->userId === false) {
            throw new HttpException(404, 'Player not found');
        }
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet(): array
    {
        if ($this->userId !== Yii::$app->request->post('userId')){
            throw new HttpException(403, 'Access forbidden');
        }
        $gameData = Yii::$app->cache->get('game_data_' . $this->userId);
        $answer = [
            'status' => Yii::$app->cache->get('game_status_' . $this->userId),
            'data' => $gameData ? json_decode($gameData) : [],
        ];
        return $answer;
    }

    /**
     * @return array
     */
    public function actionConfirm(): array
    {
        Yii::$app->cache->set('game_status_' . $this->userId, Game::GAME_STATUS_NO_ACTION, 60 * 3);
        return ['result' => 'OK'];
    }
}
