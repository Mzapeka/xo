<?php
/**
 * Created by PhpStorm.
 * User: nikolay
 * Date: 04.12.18
 * Time: 15:05
 */

namespace app\models;

use yii\base\Model;

class Step extends Model
{
    public $x;
    public $y;

    public function rules()
    {
        return [
            [['x', 'y'], 'required'],
            ['x', 'integer', 'min' => 0, 'max' => Game::SIZE_X - 1],
            ['y', 'integer', 'min' => 0, 'max' => Game::SIZE_Y - 1]
        ];
    }
}