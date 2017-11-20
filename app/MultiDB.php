<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/20
 * Time: 16:38:49
 */

namespace App;


interface MultiDB
{
    static function getQuery($db);
}