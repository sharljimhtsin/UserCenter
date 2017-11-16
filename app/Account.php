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
    protected $hidden = [
        'password',
    ];
}