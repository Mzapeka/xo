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
use yii\helpers\Html;
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

    public function actionStart($name)
    {
        Yii::$app->session->remove('userId');
        Yii::$app->session->remove('name');

        Yii::$app->session->set('name', Html::encode($name));
        $userId = Yii::$app->security->generateRandomString();
        Yii::$app->session->set('userId', $userId);
        $game = $this->engine->startGame($userId, Html::encode($name));
        if ($game instanceof Game) {
            $this->sendMessage($game);
        }

        return $this->redirect('/site/start');
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
            $this->engine->addGame([$game->id => serialize($game)]);
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
