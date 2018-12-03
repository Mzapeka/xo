<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 02.12.18
 * Time: 18:40
 */

namespace app\models;

use yii\helpers\ArrayHelper;


class Game
{
    const SIZE_X = 3;
    const SIZE_Y = 3;

    const LENGTH_TO_WIN = 3;
bosch2018                                                               

    public $board = [];
    public $id;
    public $user;
    public $opponent;
    public $steps = 0;

    public function __construct($user, $oponent)
    {
        $this->user = $user;
        $this->opponent = $oponent;
        $this->id = \Yii::$app->security->generateRandomString(32);
    }

    /**
     * @param int $x
     * @param int $y
     * @param string $user
     * @return bool|null
     */
    public function go(int $x, int $y, string $user): ?bool
    {
        if (!ArrayHelper::getValue($this->board, [$x, $y], false)) {
            return false;
        }
        ArrayHelper::setValue($this->board, [$x, $y], $this->getTurn($user));
        $this->steps++;
        return $this->checkWinner($this->getTurn($user));
    }

    private function getTurn(string $user): string
    {
        return ($user === $this->user ? 'X' : 'O');
    }

    /**
     * @param string $turn
     * @return bool|string
     */
    private function checkWinner(string $turn)
    {
        if ((count($this->board, COUNT_RECURSIVE) - count($this->board)) === self::SIZE_X * self::SIZE_Y) {
            return 'none';
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
}