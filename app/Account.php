<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:12:18
 */

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`account` (
 * `account_id`    BIGINT(20)  NOT NULL,
 * `user_key`      VARCHAR(45) NULL,
 * `password`      VARCHAR(45) NULL,
 * `account_type`  TINYINT(5)  NULL,
 * `union_user_id` BIGINT(20)  NULL,
 * `status`        TINYINT(5)  NULL,
 * PRIMARY KEY (`account_id`)
 * );
 *
 * ALTER TABLE `user_center`.`account`
 * CHANGE COLUMN `account_id` `account_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;
 **/
class Account extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $table = 'account';

    public $primaryKey = 'account_id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = false;

    public $dateFormat = 'U';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    // account login type
    const TEMP_LOGIN = -1;
    const NORMAL_LOGIN = 0;
    const QQ_LOGIN = 1;
    const WECHAT_LOGIN = 2;
    const WEIBO_LOGIN = 3;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['account_id', 'user_key', 'password', 'account_type', 'union_user_id', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}