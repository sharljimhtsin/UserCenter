<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8
 * Time: 11:12:06
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class MaimengAccount extends Model implements MultiDB
{
    public $table = 'account';

    public $primaryKey = 'id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = false;

    public $dateFormat = 'Y-m-d H:i:s';

    public $connection = 'mysql';

    const CREATED_AT = 'createTime';
    const UPDATED_AT = 'modifyTime';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'password', 'qudao', 'registerType', 'avatar', 'nickname', 'sex', 'signature', 'ip', 'loginTime', 'unionId', 'status'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "maimeng")
    {
        if (array_key_exists($db, MaimengAccount::$dbPool)) {
            return MaimengAccount::$dbPool[$db];
        } else {
            $model = new MaimengAccount();
            $model->setConnection($db);
            $builder = $model->newQuery();
            MaimengAccount::$dbPool[$db] = $builder;
            return $builder;
        }
    }

}