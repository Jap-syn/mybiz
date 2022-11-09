<?php
// 修正
namespace App\Console\Commands;

require_once (dirname(__FILE__) .'/../../../vendor/autoload.php');
require_once (dirname(__FILE__) .'/MyBusiness.php');

use Illuminate\Console\Command;
use App;
use App\Account;
use App\Services\GmbApiService;
use App\Services\GmbApiAccountRegistService;
use App\Services\GmbApiLocationRegistService;
use App\Services\GmbApiLocalpostRegistService;
use App\Services\GmbApiReviewRegistService;
use App\Services\GmbApiMediaItem2RegistService;
use Google_Service_V1_MyBusinessLocation;
use Google_Client;
use Google_Service_MyBusiness;

class GmbRegistCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmb:regist {ver=v1.0} {api=all} {key?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GMBのデータ更新';

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
        $key = $this->argument('key');
        $this->_debug('GmbRegistCommand ver=' .$ver .' api=' .$api .' key=' .$key);

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

              //  $client->setApiFormatV2(2);
                $gmbService = new Google_Service_MyBusiness($client);

                /*
                // 店舗情報を更新
                if ($api == 'putAll' || $api == 'putLocations') {
                    $gmbApiLocationRegistService = new GmbApiLocationRegistService();
                    $gmbApiLocationRegistService->registLocations($gmbService, $gmbApiService);
                }
                */
                // 
                if ($api == 'putLocationSpecialHour') {
                    $gmbApiLocationRegistService = new GmbApiLocationRegistService();
                    $gmbService_v1 = new Google_Service_V1_MyBusinessLocation($client);

                    $gmbApiLocationRegistService->registSpecialHour($gmbService_v1, $gmbApiService);
                }
                
                // 投稿を更新
                if ($api == 'putAll' || $api == 'putLocalposts') {
                    $gmbApiLocalpostRegistService = new GmbApiLocalpostRegistService();
                    $gmbApiLocalpostRegistService->registLocalposts($gmbService, $gmbApiService);
                }
                
                // クチコミ返信を同期（新規作成、変更、削除）
                if ($api == 'putAll' || $api == 'putReviewReplies') {
                    $gmbApiReviewRegistService = new GmbApiReviewRegistService();
                    $gmbApiReviewRegistService->registReviewReplies($gmbService, $gmbApiService);
                }
                
                // 写真
                if ($api == 'putAll' || $api == 'putMediaItems2') {
                    $gmbApiMediaItem2RegistService = new GmbApiMediaItem2RegistService();
                    $gmbApiMediaItem2RegistService->registMediaItems2($gmbService, $gmbApiService);
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
