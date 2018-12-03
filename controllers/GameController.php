<?php
/**
 * Created by PhpStorm.
 * User: mz
 * Date: 03.12.18
 * Time: 23:18
 */

namespace app\controllers;

use app\models\Engine;
use yii\base\Module;
use yii\rest\Controller;

class GameController extends Controller
{
    private $engine;

    public function __construct(string $id, Module $module, Engine $engine, array $config = [])
    {
        $this->engine = $engine;
        parent::__construct($id, $module, $config);
    }

    public function actionStart()
    {
        
    }

}