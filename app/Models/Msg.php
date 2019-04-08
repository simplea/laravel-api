<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Msg extends Model
{
    //
    protected $table   = 'com_msg';
    protected $guarded = [];

    const CREATED_AT   = 'create_at';
    const UPDATED_AT   = 'update_at';

    const STATS_UNREAD  = 0;       // 未读
    const STATS_READ    = 1;       // 已读
    const TYPE_USER     = 0;       // 用户
    const TYPE_COMPANY  = 1;       // 商户
    const BUBBLE_TRUE   = 1;       // 已冒泡
    const BUBBLE_FALSE  = 0;       // 未冒泡

    /**
     * 消息列表
     *
     * @param [type] $where
     * @return void
     */
    protected function msgList($where, $num = 20)
    {
        return self::where($where)->orderBy('create_at','desc')->paginate(20);
    }

    /**
     * 未读状态修改
     *
     * @return void
     */
    protected function unreadUpdate($type,$uid)
    {
        return self::where([
            'type' => $type,
            'obj_id' => $uid
        ])->update([
            'status' => self::STATS_READ,
            'is_bubble' => self::BUBBLE_TRUE,
            'update_at' => date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * 发送消息
     *
     * @param string $title     标题
     * @param string $content   内容
     * @param integer $objId    发送id
     * @param integer $type     0 用户，1 厂商
     * @return void
     */
    protected function add($title = '', $content = '', $objId = 0, $type = 0)
    {
        return self::create([
            'title' => $title,
            'content' => $content,
            'obj_id' => $objId,
            'type' => $type
        ]);
    }
}
