<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    //
    protected $table   = 'com_news';
    protected $guarded = [];
    public $timestamps = false;

    const STATUS_FALSE = 0; // 下架
    const STATUS_TRUE  = 1; // 上架
    const TYPE_NEW  = 1; // 新闻
    const TYPE_PUB  = 2; // 公告

    protected function list($num = 10, $type = self::TYPE_NEW)
    {
        return self::where([
            'status' => self::STATUS_TRUE,
            'type' => $type
        ])
        ->orderBy('created_at', 'desc')
        ->paginate($num);
    }
}