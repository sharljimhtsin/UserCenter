<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:39:11
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`mapping` (
 * `mapping_id`  BIGINT(20)  NOT NULL,
 * `channel_id`  BIGINT(20)  NULL,
 * `channel_uid` VARCHAR(45) NULL,
 * `user_id`     BIGINT(20)  NULL,
 * `create_time` DATETIME    NULL,
 * PRIMARY KEY (`mapping_id`)
 * );
 *
 * ALTER TABLE `user_center`.`mapping`
 * CHANGE COLUMN `mapping_id` `mapping_id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
 * ADD COLUMN `modify_time` DATETIME NULL AFTER `create_time`;
 **/
class Mapping extends Model implements MultiDB
{
    public $table = 'mapping';

    public $primaryKey = 'mapping_id';

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
    protected $fillable = ['channel_id', 'channel_uid', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, Mapping::$dbPool)) {
            return Mapping::$dbPool[$db];
        } else {
            $model = new Mapping();
            $model->setConnection($db);
            $builder = $model->newQuery();
            Mapping::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}