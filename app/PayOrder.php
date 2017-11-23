<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 10:38:36
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PayOrder
 * @package App
 *
 * CREATE TABLE `pay_order` (
 * `order_id` bigint(20) NOT NULL,
 * `channel_id` bigint(20) NOT NULL,
 * `channel_order_id` varchar(255) DEFAULT NULL,
 * `currency` varchar(255) DEFAULT NULL,
 * `extension` varchar(255) DEFAULT NULL,
 * `money` int(11) NOT NULL,
 * `status` int(11) NOT NULL,
 * `user_id` bigint(20) NOT NULL,
 * `role_id` varchar(255) DEFAULT NULL,
 * `role_name` varchar(255) DEFAULT NULL,
 * `server_id` varchar(255) DEFAULT NULL,
 * `server_name` varchar(255) DEFAULT NULL,
 * `product_id` varchar(255) DEFAULT NULL,
 * `product_name` varchar(255) DEFAULT NULL,
 * `product_desc` varchar(255) DEFAULT NULL,
 * `notify_url` varchar(2048) DEFAULT NULL,
 * `created_time` datetime DEFAULT NULL,
 * `modify_time` datetime DEFAULT NULL,
 * `complete_time` datetime DEFAULT NULL,
 * PRIMARY KEY (`order_id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * ALTER TABLE `user_center`.`pay_order`
 * CHANGE COLUMN `order_id` `order_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;
 */
class PayOrder extends Model
{
    public $table = 'pay_order';

    public $primaryKey = 'order_id';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = true;

    public $dateFormat = 'Y-m-d H:i:s';

    public $connection = 'mysql';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['channel_id', 'channel_order_id', 'currency', 'extension', 'money', 'status', 'user_id', 'role_id', 'role_name', 'server_id', 'server_name', 'product_id', 'product_name', 'product_desc', 'notify_url', 'complete_time'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    static $dbPool = array();

    static function getQuery($db = "mysql")
    {
        if (array_key_exists($db, PayOrder::$dbPool)) {
            return PayOrder::$dbPool[$db];
        } else {
            $model = new PayOrder();
            $model->setConnection($db);
            $builder = $model->newQuery();
            PayOrder::$dbPool[$db] = $builder;
            return $builder;
        }
    }
}