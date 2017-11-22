<?php

namespace App;

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
class User extends Model implements MultiDB
{
    public $table = 'user';

    public $primaryKey = 'user_id';

    public $incrementing = false;

    public $keyType = 'int';

    public $timestamps = true;

    public $dateFormat = 'Y-m-d H:i:s';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    // role type
    const NORMAL_ROLE = 1;
    const ADMIN_ROLE = 2;
    const PARTNER_ROLE = 3;
    // account status type
    const NORMAL_STATUS = 1;
    const BAN_STATUS = 2;
    const DISABLE_STATUS = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'telephone', 'nickname', 'avatar', 'birthday', 'sex', 'signature', 'user_source', 'role', 'status'
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
        if (array_key_exists($db, User::$dbPool)) {
            return User::$dbPool[$db];
        } else {
            $model = new User();
            $model->setConnection($db);
            $builder = $model->newQuery();
            User::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}
