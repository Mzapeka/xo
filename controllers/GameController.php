<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 03.12.18
 * Time: 23:18
 */

namespace app\controllers;

use app\models\Engine;
use app\models\Game;
use app\models\Step;
use Yii;
use yii\base\Module;
use yii\rest\Controller;
use yii\web\HttpException;

class GameController extends Controller
{
    private $engine;
    private $userId;

    public function __construct(string $id, Module $module, Engine $engine, array $config = [])
    {
        $this->userId = Yii::$app->session->get('userId');
        if ($this->userId === false) {
            throw new HttpException(404, 'Player not found');
        }
        $this->engine = $engine;
        parent::__construct($id, $module, $config);
    }

    public function actionStatus()
    {
        $gameData = Yii::$app->cache->get('game_data_' . $this->userId);
        $answer = [
            'status' => Yii::$app->cache->get('game_status_' . $this->userId),
            'data' => $gameData ? json_decode($gameData) : [],
        ];
        return $answer;
    }

    public function actionGetStatusConfirm(): array
    {
        Yii::$app->cache->set('game_status_' . $this->userId, Game::GAME_STATUS_NO_ACTION, 60 * 3);
        return ['result' => 'OK'];
    }

    /**
     * @throws HttpException
     */
    public function actionStep(): array
    {
        $step = new Step();
        if (!$step->load(Yii::$app->request->post())) {
            throw new HttpException(403, 'Wrong data format passed');
        }

        $gameObj = $this->engine->getGameByUserId($this->userId);

        if ($step->validate()) {
            $gameObj->go($step, $this->userId);
            $this->sendMessage($gameObj);
            return ['result' => 'OK'];
        }
        throw new HttpException(403, 'Given wrong parameters: ' . print_r($step->getErrors(), true));
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionEndGame(): array
    {
        $gameObj = $this->engine->getGameByUserId($this->userId);
        $gameObj->winner = $gameObj->getOpponentId($this->userId);
        $this->sendMessage($gameObj);
        return ['result' => 'OK'];
    }

    /**
     * @param Game $game
     * @param string $userId
     * @return false|string
     */
    private function prepareGameData(Game $game, string $userId)
    {
        return json_encode(
            [
                'gameId' => $game->id,
                'board' => $game->board,
                'currentTurn' => $game->getCurrentTurn(),
                'activeUser' => $game->activeUser,
                'yourName' => $game->getYourName($userId),
                'opponentName' => $game->getOpponentName($userId),
                'winner' => $game->winner
            ]
        );
    }

    /**
     * @param Game $game
     */
    private function sendMessage(Game $game)
    {
        if (!$game->winner) {
            $this->engine->games[$game->id] = serialize($game);
        } else {
            $this->engine->end($this->userId);
        }
        $opponentId = $game->getOpponentId($this->userId);
        Yii::$app->cache->set(
            'game_status_' . $this->userId,
            Game::GAME_STATUS_UPDATE,
            3 * 60
        );
        Yii::$app->cache->set(
            'game_status_' . $opponentId,
            Game::GAME_STATUS_UPDATE,
            3 * 60
        );
        Yii::$app->cache->set(
            'game_data_' . $this->userId,
            $this->prepareGameData($game, $this->userId),
            3 * 60
        );
        Yii::$app->cache->set(
            'game_data_' . $opponentId,
            $this->prepareGameData($game, $opponentId),
            3 * 60
        );
    }
}
