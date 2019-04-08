<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vul;
use App\Models\Company;
use App\Models\Msg;
use App\Models\CashFlow;
use Log;

class ClaimVul extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claim:vul';

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
        $nowTime = date('Y-m-d H:i:s', strtotime('-7 days'));
        $vulList = Vul::select(
            'id',
            'company_state',
            'company_id',
            'price',
            'com',
            'points',
            'uid',
            'write_time'
        )
        ->where([
            ['id', '>=', 226],
            ['state', '=', Vul::COMPANY_STATE_PASS],
            ['company_state', '=', Vul::COMPANY_STATE_INIT],
            ['update_time', '<=', $nowTime]
        ])
        ->orWhere([
            ['id', '>=', 226],
            ['state', '=', Vul::COMPANY_STATE_INIT],
            ['company_hide', '=', Vul::COMPANY_HIDE_TRUE],
            ['company_state', '=', Vul::COMPANY_STATE_INIT],
            ['write_time', '<=', $nowTime]
        ])
        ->get();

        if($vulList){
            foreach ($vulList as $key => $value) {
                if($value->company_id != 0){
                    $info = Company::select('publish','balance','company_name')->where('id', $value->company_id)->first();
                    if($info['publish'] == 1){
                        $totalPrice = $value->price + $value->price*0.1;
                        if($info['balance'] >= $totalPrice){
                            $status = Vul::companyVulClaim($value->company_id, $value->id);
                            if($status){
                                $vulNum = vulNumber($value->id,$value->write_time);
                                $title   = "漏洞认领状态通知";
                                $content = "恭喜！您编号为".$vulNum."的漏洞已经被厂商".$info['company_name']."认领。快到漏洞详情里查看吧~";
                                Msg::add($title, $content, $value->uid);
                                if($value->state == 0){
                                    Vul::where('id', $value->id)->update(['state' => Vul::STATE_SUCCESS,'update_time' => date('Y-m-d H:i:s', strtotime('-20 seconds'))]);
                                }
                                self::writeCashFlow($value);
                            }
                        }else{
                            Vul::where('id', $value->id)->update(['company_state' => Vul::COMPANY_STATE_COM_UNINIT,'company_time' => date('Y-m-d H:i:s')]);
                        }
                    }else{
                        Vul::where('id', $value->id)->update(['company_state' => Vul::COMPANY_STATE_com,'company_time' => date('Y-m-d H:i:s')]);
                        if($value->state == 0){
                            Vul::where('id', $value->id)->update(['state' => Vul::STATE_SUCCESS,'update_time' => date('Y-m-d H:i:s', strtotime('-20 seconds'))]);
                        }
                        self::writeCashFlow($value);
                    }
                }else{
                    Vul::where('id', $value->id)->update(['company_state' => Vul::COMPANY_STATE_com,'company_time' => date('Y-m-d H:i:s')]);
                    if($value->state == 0){
                        Vul::where('id', $value->id)->update(['state' => Vul::STATE_SUCCESS,'update_time' => date('Y-m-d H:i:s', strtotime('-20 seconds'))]);
                    }
                    self::writeCashFlow($value);
                }
            }
        }
    }

    protected function writeCashFlow($value)
    {
        if(!$value){
            return false;
        }
        $ethCashFlow = CashFlow::getCash($value->uid, CashFlow::TYPE_ZERO, $value->id);
        if(!empty($ethCashFlow)){
            CashFlow::where('id',$ethCashFlow->id)->update([
                'price' => $value->price
            ]);
        }else{
            CashFlow::create([
                'price'     => $value->price,
                'type'      => CashFlow::TYPE_ZERO,
                'uid'       => $value->uid,
                'field_id'  => $value->id,
                'writetime' => date('Y-m-d H:i:s', time())
            ]);
        }

        $comCashFlow = CashFlow::getCash($value->uid, CashFlow::TYPE_ONE, $value->id);
        if(!empty($comCashFlow)){
            CashFlow::where('id',$comCashFlow->id)->update([
                'price' => $value->com
            ]);
        }else{
            CashFlow::create([
                'price'     => $value->com,
                'type'      => CashFlow::TYPE_ONE,
                'uid'       => $value->uid,
                'field_id'  => $value->id,
                'writetime' => date('Y-m-d H:i:s', time())
            ]);
        }

        $pointCashFlow = CashFlow::getCash($value->uid, CashFlow::TYPE_FOUR, $value->id);
        if(!empty($pointCashFlow)){
            CashFlow::where('id',$pointCashFlow->id)->update([
                'price' => $value->points
            ]);
        }else{
            CashFlow::create([
                'price'     => $value->points,
                'type'      => CashFlow::TYPE_FOUR,
                'uid'       => $value->uid,
                'field_id'  => $value->id,
                'writetime' => date('Y-m-d H:i:s', time())
            ]);
        }
    }
}
