<?php
// 修正
namespace App\Console\Commands;

require_once (dirname(__FILE__) .'/../../../vendor/autoload.php');
require_once (dirname(__FILE__) .'/MyBusiness.php');

use Illuminate\Console\Command;
use App;
use App\Account;
use App\Services\GmbApiService;
use App\Services\GmbApiInsightsQueryService;

use Google_Client;
use Google_Service_MyBusiness;

class GmbInsightsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmb:insights {ver=v1.0} {api=all} {from=3} {to=3} {key?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GMBのInsightsデータ取得';

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
        $ver = $this->argument('ver');
        $api = $this->argument('api');
        $from = $this->argument('from');
        $to = $this->argument('to');
        $key = $this->argument('key');
        $this->_debug('GmbQueryCommand ver=' .$ver .' api=' .$api .' from=' .$from .' to=' .$to .' key=' .$key);

        $authFile = dirname(__FILE__) .'/' .config('command.client_secret.json');
        $tokenFile = dirname(__FILE__) .'/my_bussiness_token.json';

        $gmbApiService = new GmbApiService();
        $client = $gmbApiService->newGoogleClient($authFile);
        $result = $gmbApiService->setAccessToken($client, $tokenFile);
        if ($client->isAccessTokenExpired()) 
        {
            // トークンが無いか、有効期限切れ
            $this->_debug('トークン有効期限切れ');
            $gmbApiService->refreshToken($client, $tokenFile);
        }

        if($result) 
        {
            if (! $client->isAccessTokenExpired()) 
            {
                $this->_debug('トークン有効');
                $gmbService = new Google_Service_MyBusiness($client);

                // Insights情報を取得
                if ($api == 'getReportLocationInsights') {
                    $gmbApiInsightsQueryService = new GmbApiInsightsQueryService();

                    for ($day = $from; $day <= $to; $day++) {
                        if ($key == '') {
                            // すべてのアカウントの全店舗のInsights情報
                            $gmbApiInsightsQueryService->getReportLocationInsights($gmbService, $gmbApiService, $day);
    
                        } else {
                            $keyAry = explode("/", $key);
                            if (count($keyAry) == 1) {
                                // 特定のアカウントの全店舗のInsights情報
                                $gmbAccountId = $keyAry[0];
                                $gmbApiInsightsQueryService->getReportLocationInsights($gmbService, $gmbApiService, $day, $gmbAccountId);
                            } else if (count($keyAry) == 2) {
                                // 特定の店舗のInsights情報
                                $gmbAccountId = $keyAry[0];
                                $gmbLocationId = $keyAry[1];
                                $gmbApiInsightsQueryService->getReportLocationInsights($gmbService, $gmbApiService, $day, $gmbAccountId, $gmbLocationId);
                            }
                        }
                    }
                } 
            }
        }

        // トークンが無いか、有効期限切れ
        $gmbApiService->refreshToken($client, $tokenFile);

    }

    private function _debug($msg) {
        var_dump($msg);
    }
}
