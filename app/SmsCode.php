<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:42:38
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * CREATE TABLE `user_center`.`sms_code` (
 * `telephone` BIGINT(20) NOT NULL,
 * `code`      INT        NULL,
 * `ttl`       BIGINT(20) NULL,
 * PRIMARY KEY (`telephone`)
 * );
 *
 * ALTER TABLE `user_center`.`sms_code`
 * CHANGE COLUMN `ttl` `expire_time` BIGINT(20) NULL DEFAULT NULL ;
 **/
class SmsCode extends Model implements MultiDB
{
    public $table = 'sms_code';

    public $primaryKey = 'telephone';

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
    protected $fillable = ["telephone", "code", "expire_time"];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, SmsCode::$dbPool)) {
            return SmsCode::$dbPool[$db];
        } else {
            $model = new SmsCode();
            $model->setConnection($db);
            $builder = $model->newQuery();
            SmsCode::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}