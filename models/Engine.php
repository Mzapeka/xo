<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 02.12.18
 * Time: 19:41
 */

namespace app\models;

use http\Exception\RuntimeException;
use phpDocumentor\Reflection\Types\Array_;
use yii\caching\Cache;
use yii\helpers\ArrayHelper;

/**
 * @property array $games
 * @property array $users
 * @property array $waitingUsers
 */
class Engine
{
    const MAX_ATTEMPTS_TO_CASH_ACCESS = 10;

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
     * @return Game|bool
     */
    public function startGame(string $user)
    {
        if (count($this->waitingUsers) > 0) {
            $oponent = array_shift($this->waitingUsers);
            $game = new Game($user, $oponent);
            $id = $game->id;
            $this->games[$id] = serialize($game);
            $this->users[$user] = $id;
            $this->users[$oponent] = $id;
            return $game;
        }
        $this->waitingUsers[] = $user;
        return false;
    }

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
        return true;
    }

    /**
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
     *
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
     * @return bool
     */
    private function blockEngine(): bool
    {
        $accessAttemptCounter = 0;
        while (!$this->cash->get('blockFlag')) {
            usleep(1000);
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
    private function unblockEngine()
    {
        $this->cash->delete('blockFlag');
        return true;
    }

}