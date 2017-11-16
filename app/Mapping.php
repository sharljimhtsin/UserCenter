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
 **/
class Mapping extends Model
{
    public $table = 'mapping';

    public $primaryKey = 'mapping_id';

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