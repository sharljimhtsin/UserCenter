<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:34:44
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`channel` (
 * `channel_id`       BIGINT(20)   NOT NULL,
 * `channel_name`     VARCHAR(45)  NULL,
 * `channel_key`      VARCHAR(45)  NULL,
 * `channel_secret`   VARCHAR(45)  NULL,
 * `create_time`      DATETIME     NULL,
 * `pay_callback_url` VARCHAR(100) NULL,
 * `is_test`          TINYINT(5)   NULL,
 * PRIMARY KEY (`channel_id`)
 * );
 *
 * ALTER TABLE `user_center`.`channel`
 * ADD COLUMN `owner` BIGINT(20) NULL AFTER `is_test`;
 *
 * ALTER TABLE `user_center`.`channel`
 * CHANGE COLUMN `channel_id` `channel_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;
 *
 * ALTER TABLE `user_center`.`channel`
 * ADD COLUMN `modify_time` DATETIME NULL AFTER `owner`;
 **/
class Channel extends Model implements MultiDB
{
    public $table = 'channel';

    public $primaryKey = 'channel_id';

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
    protected $fillable = ['channel_name', 'channel_key', 'channel_secret', 'pay_callback_url', 'is_test', 'owner'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, Channel::$dbPool)) {
            return Channel::$dbPool[$db];
        } else {
            $model = new Channel();
            $model->setConnection($db);
            $builder = $model->newQuery();
            Channel::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}