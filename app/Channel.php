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
 **/
class Channel extends Model
{
    public $table = 'channel';

    public $primaryKey = 'channel_id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = false;

    public $dateFormat = 'U';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}