<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vul;
use Log;

class updateVulPicture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:vulpic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // for($i=0;$i<10;$i++){
            file_put_contents('./updatePic.sh', '#!/bin/bash'.PHP_EOL, FILE_APPEND);
            $vuls = Vul::select('id','description')->skip(0)->take(10)->get();

            foreach($vuls as $val){
                // 抓取图片地址
                preg_match_all('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i',$val->description, $match);

                if(!empty($match[1])){
                    // 原始内容
                    // Log::info($val->description);
                    foreach($match[1] as $k => $v){
                        $dirName = pathinfo($v)['dirname']; 

                        if($dirName != '/upload'){
                            continue;
                        }
                        // 初始替换内容
                        $replace[0] = $val->description;

                        // 图片新地址内容
                        $newUrl = '已经删除';

                        // 替换的地址内容
                        $replaceUrl = pathinfo($v)['filename'];
                        // 后缀
                        $extension = pathinfo($v)['extension'] ?? '';

                        // 赋值新替换内容
                        $replace[$k+1] = str_replace($replaceUrl,$newUrl,$replace[$k]);

                        // 数据库新内容
                        $newDesc = $replace[$k+1];

                        $root = '/var/www';
                        // 编写shell脚本代码
                        $shell = 'mv '.$root.$v.' '.$root.'/upload/'.$newUrl.'.'.$extension;
                        file_put_contents('./updatePic.sh', $shell.PHP_EOL, FILE_APPEND);
                    }
                    Vul::where('id',$val->id)->update(['description' => $newDesc]);
                }
            }
            // }
    }
}
