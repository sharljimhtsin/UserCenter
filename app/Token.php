<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:40:46
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`token` (
 * `user_id` BIGINT(20)  NULL,
 * `token`   VARCHAR(45) NULL,
 * `ttl`     BIGINT(20)  NULL
 * );
 *
 * ALTER TABLE `user_center`.`token`
 * CHANGE COLUMN `user_id` `user_id` BIGINT(20) NOT NULL ,
 * ADD PRIMARY KEY (`user_id`);
 *
 * ALTER TABLE `user_center`.`token`
 * CHANGE COLUMN `ttl` `expire_time` BIGINT(20) NULL DEFAULT NULL ;
 **/
class Token extends Model implements MultiDB
{
    public $table = 'token';

    public $primaryKey = 'user_id';

    public $incrementing = false;

    public $keyType = 'string';

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
    protected $fillable = ["user_id", "token", "expire_time"];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, Token::$dbPool)) {
            return Token::$dbPool[$db];
        } else {
            $model = new Token();
            $model->setConnection($db);
            $builder = $model->newQuery();
            Token::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}