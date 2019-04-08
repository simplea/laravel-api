<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyTmp;
use App\Models\Vul;
use App\Models\VulToken;
use Log;

class GetCompanyTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getToken:company {company}';

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
        $companyName = $this->argument('company');
        $companyInfo = CompanyTmp::infoByName($companyName);
        if($companyInfo){
            $vulIds = Vul::select(
                'id'
            )->where([
                'state'      => Vul::STATE_SUCCESS,
                'is_mail'    => 0,
                'company_id' => Vul::COMPANY_STATE_INIT,
                'company'    => $companyName
            ])->get();
            foreach ($vulIds as $key => $value) {
                if($value->id){
                    $token = '已经删除';
                    VulToken::create([
                        'vid'   => $value->id,
                        'token' => $token
                    ]);
                    $tokenList[] = "https://test.com?token=".$token;
                }
            }
            $tokenLog[$companyName] = $tokenList;
            Log::info($tokenLog);
        }
    }
}
