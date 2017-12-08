<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8
 * Time: 11:12:17
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class MaimengUser extends Model implements MultiDB
{
    public $table = 'user';

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
    protected $fillable = ['telephone', 'nickname', 'avatar', 'birthday', 'sex', 'signature', 'qudao', 'registerType', 'level', 'enableEmChat', 'status', 'qq', 'email'];

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
        if (array_key_exists($db, MaimengUser::$dbPool)) {
            return MaimengUser::$dbPool[$db];
        } else {
            $model = new MaimengUser();
            $model->setConnection($db);
            $builder = $model->newQuery();
            MaimengUser::$dbPool[$db] = $builder;
            return $builder;
        }
    }

}