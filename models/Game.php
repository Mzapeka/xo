<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 02.12.18
 * Time: 18:40
 */

namespace app\models;

use yii\helpers\ArrayHelper;
use yii\web\HttpException;


class Game
{
    const SIZE_X = 3;
    const SIZE_Y = 3;

    const LENGTH_TO_WIN = 3;

    const NO_WINNER_STATUS = 'none';

    const GAME_STATUS_NO_ACTION = 'no_action';
    const GAME_STATUS_UPDATE = 'update';

    public $board = [];
    public $id;
    public $user;
    public $opponent;
    public $steps = 0;

    public $userName;
    public $opponentName;

    public $userTurn;
    public $opponentTurn;

    public $activeUser;

    public $winner;

    /**
     * Game constructor.
     * @param string $user
     * @param string $oponent
     * @param string $userName
     * @param string $opponentName
     * @throws \yii\base\Exception
     */
    public function __construct(string $user, string $oponent, string $userName, string $opponentName)
    {
        $this->user = $user;
        $this->opponent = $oponent;
        $this->userName = $userName;
        $this->opponentName = $opponentName;
        $this->id = \Yii::$app->security->generateRandomString(32);
        $this->generateTurn();
    }

    /**
     * @param Step $step
     * @param string $user
     * @return bool|null
     * @throws HttpException
     */
    public function go(Step $step, string $user): ?bool
    {
        if (ArrayHelper::getValue($this->board, [$step->x, $step->y], false)) {
            throw new HttpException(422, 'This cell already hold.');
        }
        ArrayHelper::setValue($this->board, [$step->x, $step->y], $this->getTurn($user));
        $this->steps++;
        $this->switchTurn();
        $winner = $this->checkWinner($this->getTurn($user));
        switch ($winner) {
            case true:
                $this->winner = $user;
                break;
            case self::NO_WINNER_STATUS:
                $this->winner = self::NO_WINNER_STATUS;
                break;
        }
        return $winner;
    }

    private function getTurn(string $user): string
    {
        return ($user === $this->user ? $this->userTurn : $this->opponentTurn);
    }

    /**
     * @param string $turn
     * @return bool|string
     */
    private function checkWinner(string $turn)
    {
        if ((count($this->board, COUNT_RECURSIVE) - count($this->board)) === self::SIZE_X * self::SIZE_Y) {
            return self::NO_WINNER_STATUS;
        }

        // check X axes
        foreach ($this->board as $rowNumber => $row) {
            if (isset($this[$rowNumber]) && count($this[$rowNumber]) === self::SIZE_Y) {
                $countTurnChain = 0;
                foreach ($this[$rowNumber] as $column) {
                    if ($column !== $turn) {
                        break;
                    }
                    if (++$countTurnChain === self::LENGTH_TO_WIN) {
                        return true;
                    }
                }
            }
        }
        if (count($this->board) < self::SIZE_Y) {
            return false;
        }
        // check Y axes
        for ($col = 0; $col < self::SIZE_X; $col++) {
            $countTurnChain = 0;
            for ($row = 0; $row < self::SIZE_Y; $row++) {
                if (ArrayHelper::getValue($this->board, [$row, $col], false) !== $turn) {
                    break;
                }
                if (++$countTurnChain === self::LENGTH_TO_WIN) {
                    return true;
                }
            }
        }

        // check diagonal
        $countTurnChain = 0;
        for ($i = 0; $i < self::SIZE_X; $i++) {
            if (ArrayHelper::getValue($this->board, [$i, $i], false) !== $turn) {
                break;
            }
            if (++$countTurnChain === self::LENGTH_TO_WIN) {
                return true;
            }
        }

        // check diagonal2
        $countTurnChain = 0;
        $col = self::SIZE_Y - 1;
        for ($i = 0; $i < self::SIZE_X; $i++) {
            if (ArrayHelper::getValue($this->board, [$i, $col - $i], false) !== $turn) {
                break;
            }
            if (++$countTurnChain === self::LENGTH_TO_WIN) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $userId
     * @return mixed
     */
    public function getOpponentId(string $userId)
    {
        return $userId === $this->user ? $this->opponent : $this->user;
    }

    /**
     *
     */
    private function generateTurn()
    {
        $turns = ['X', 'O'];
        shuffle($turns);
        $this->userTurn = array_shift($turns);
        $this->opponentTurn = array_shift($turns);
        $this->activeUser = $this->user;
    }

    /**
     *
     */
    private function switchTurn(): void
    {
        $this->activeUser = $this->activeUser === $this->user ? $this->opponent : $this->user;
    }

    /**
     * @param string $userId
     * @return string
     */
    public function getYourName(string $userId): string
    {
        return $userId === $this->user ? $this->userName : $this->opponentName;
    }

    /**
     * @param string $userId
     * @return string
     */
    public function getOpponentName(string $userId): string
    {
        return $userId === $this->user ? $this->opponentName : $this->userName;
    }

    /**
     * @return string
     */
    public function getCurrentTurn(): string
    {
        return $this->activeUser === $this->user ? $this->userTurn : $this->opponentTurn;
    }
}