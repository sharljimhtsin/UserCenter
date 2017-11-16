<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`user` (
 * `user_id`     BIGINT(20)  NOT NULL,
 * `telephone`   VARCHAR(45) NULL,
 * `nickname`    VARCHAR(45) NULL,
 * `avatar`      VARCHAR(45) NULL,
 * `birthday`    DATE        NULL,
 * `sex`         TINYINT(5)  NULL,
 * `signature`   VARCHAR(45) NULL,
 * `user_source` VARCHAR(45) NULL,
 * `role`        TINYINT(5)  NULL,
 * `status`      TINYINT(5)  NULL,
 * `create_time` DATETIME    NULL,
 * `modify_time` DATETIME    NULL,
 * PRIMARY KEY (`user_id`)
 * );
 **/
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $table = 'user';

    public $primaryKey = 'user_id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = true;

    public $dateFormat = 'U';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'avatar', 'signature'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
