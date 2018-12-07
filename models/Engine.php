<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 02.12.18
 * Time: 19:41
 */

namespace app\models;

use RuntimeException;
use yii\caching\Cache;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * @property array $games
 * @property array $users
 * @property array $waitingUsers
 */
class Engine
{
    const MAX_ATTEMPTS_TO_CASH_ACCESS = 50;

    const GAMES = 'games_key';
    const USERS = 'users_key';
    const WAITING_USERS = 'waiting_users_key';
    /**
     * @var Cache $cash
     */
    protected $cash;

    protected $games = [];
    protected $users = [];
    protected $waitingUsers = [];

    /**
     * Engine constructor.
     */
    public function __construct()
    {
        $this->cash = \Yii::$app->cache;
        if (!$this->blockEngine()) {
            throw new RuntimeException('Max attempts of Engine access achieved');
        }
        $this->init();
    }

    /**
     * load game data from store
     */
    private function init(): void
    {
        $this->games = $this->getDataFromStore(self::GAMES);
        $this->users = $this->getDataFromStore(self::USERS);
        $this->waitingUsers = $this->getDataFromStore(self::WAITING_USERS);
    }

    /**
     * Start new game
     * @param string $user
     * @param string|null $userName
     * @return Game|bool
     * @throws \yii\base\Exception
     */
    public function startGame(string $user, string $userName = null)
    {
        if (count($this->waitingUsers) === 0) {
            $this->waitingUsers[] = ['id' => $user, 'name' => $userName];
            return false;
        }

        $opponent = array_shift($this->waitingUsers);

        if ($opponent === $user) {
            $this->waitingUsers[] = ['id' => $user, 'name' => $userName];
            return false;
        }

        $game = new Game($user, $opponent['id'], $userName, $opponent['name']);
        $id = $game->id;
        $this->games[$id] = serialize($game);
        $this->users[$user] = $id;
        $this->users[$opponent['id']] = $id;
        return $game;
    }

    /**
     * @param $user
     * @return bool
     */
    public function end($user)
    {
        ArrayHelper::removeValue($this->waitingUsers, $user);
        if (isset($this->users[$user])) {
            return true;
        }
        $gameId = $this->users[$user];
        if (isset($this->games[$gameId])) {
            return true;
        }
        /**
         * @var Game $game
         */
        $game = unserialize($this->games[$gameId], [Game::class]);
        $opponent = $game->user === $user ? $game->user : $game->opponent;
        ArrayHelper::remove($this->games, $gameId);
        ArrayHelper::remove($this->users, $user);
        ArrayHelper::remove($this->users, $opponent);
        return true;
    }

    /**
     * @param Step $step
     * @param string $user
     * @return Game
     * @throws HttpException
     */
    public function step(Step $step, string $user): Game
    {
        $game = $this->getGameByUserId($user);
        $game->go($step, $user);
        $this->addGame($game);
        return $game;
    }

    /**
     * @param string $userId
     * @return Game
     * @throws HttpException
     */
    public function getGameByUserId(string $userId): Game
    {
        $gameId = $this->users[$userId] ?? false;
        if (!$gameId) {
            throw new HttpException(404, 'Game not found');
        }
        $game = $this->games[$gameId] ?? false;

        if (!$game) {
            throw new HttpException(404, 'Game not found');
        }
        /**
         * @var Game $gameObj
         */
        $gameObj = unserialize($game, [Game::class]);
        return $gameObj;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    private function getDataFromStore(string $key)
    {
        if ($data = $this->cash->get($key)) {
            return json_decode($data, true) ?? [];
        }
        return [];
    }

    /**
     * saving data to DB
     */
    public function save(): void
    {
        $this->cash->set(self::GAMES, json_encode($this->games), 24 * 60 * 60);
        $this->cash->set(self::USERS, json_encode($this->users), 24 * 60 * 60);
        $this->cash->set(self::WAITING_USERS, json_encode($this->waitingUsers), 24 * 60 * 60);
    }

    public function __destruct()
    {
        $this->save();
        $this->unblockEngine();
    }

    /**
     * Блокируем объект в базе
     * @return bool
     */
    private function blockEngine(): bool
    {
        $accessAttemptCounter = 0;
        while ($this->cash->get('blockFlag')) {
            usleep(100000);
            if ($accessAttemptCounter++ > self::MAX_ATTEMPTS_TO_CASH_ACCESS) {
                return false;
            }
        }
        $this->cash->set('blockFlag', true, 1);
        return true;
    }

    /**
     * @return bool
     */
    private function unblockEngine(): bool
    {
        $this->cash->delete('blockFlag');
        return true;
    }

    /**
     * @param Game $game
     */
    public function addGame(Game $game)
    {
        $this->games[$game->id] = serialize($game);
    }

    /**
     * @param string $userId
     * @return Game
     * @throws HttpException
     */
    public function setOpponentWin(string $userId): Game
    {
        $game = $this->getGameByUserId($userId);
        $game->winner = $game->getOpponentId($userId);
        $this->addGame($game);
        return $game;
    }
}
