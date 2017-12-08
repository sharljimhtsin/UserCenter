<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8
 * Time: 12:38:39
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Variable
 * @package App
 *
 * CREATE TABLE `user_center`.`variable` (
 * `variable_id` BIGINT(20) NOT NULL,
 * `name` VARCHAR(30) NULL,
 * `value` VARCHAR(100) NULL,
 * `create_time` DATETIME NULL,
 * `modify_time` DATETIME NULL,
 * PRIMARY KEY (`variable_id`));
 *
 * ALTER TABLE `user_center`.`variable`
 * CHANGE COLUMN `variable_id` `variable_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;
 *
 */
class Variable extends Model implements MultiDB
{
    public $table = 'variable';

    public $primaryKey = 'variable_id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = true;

    public $dateFormat = 'Y-m-d H:i:s';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'value'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, Variable::$dbPool)) {
            return Variable::$dbPool[$db];
        } else {
            $model = new Variable();
            $model->setConnection($db);
            $builder = $model->newQuery();
            Variable::$dbPool[$db] = $builder;
            return $builder;
        }
    }

}