<?php
// 修正
namespace App\Console\Commands;

require_once (dirname(__FILE__) .'/../../../vendor/autoload.php');
require_once (dirname(__FILE__) .'/MyBusiness.php');
require_once (dirname(__FILE__) .'/MyBusinessV1Account.php');
require_once (dirname(__FILE__) .'/MyBusinessV1Location.php');

use Illuminate\Console\Command;
use App;
use App\Account;
use App\Services\GmbApiService;
use App\Services\GmbApiAccountQueryService;
use App\Services\GmbApiAccountV1QueryService;
use App\Services\GmbApiLocationQueryService;
use App\Services\GmbApiLocationV1QueryService;

use App\Services\GmbApiLocalpostQueryService;
use App\Services\GmbApiReviewQueryService;

use Google_Client;
use Google_Service_MyBusiness;
use Google_Service_V1_MyBusinessAccount;
use Google_Service_V1_MyBusinessLocation;

use Google_Service_MyBusiness_Account;
use Google_Service_V1_MyBusiness;
use Google_Service_V1_MyBusiness_Account;

use Google_Service_V1_MyBusiness_Location;

class GmbQueryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmb:query {ver=v1.0} {api=all} {key?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GMBのデータ取得';

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
        $this->_debug('GmbQueryCommand ver=' .$ver .' api=' .$api .' key=' .$key);

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
                /*
                // 一時的
                // DEMOデータ作成
                if ($api == 'all' || $api == 'createDemodata') {
                    $gmbApiAccountQueryService = new GmbApiAccountQueryService();
                 //   $gmbApiAccountQueryService->createDemodata($gmbService, $gmbApiService);
                    $gmbApiAccountQueryService->updateReviewStar($gmbService, $gmbApiService);
                }
                */

                //アカウント情報を取得

                if ($api == 'all' || $api == 'getAccounts') {
                    $gmbApiAccountQueryService = new GmbApiAccountV1QueryService();
                    $gmbService_v1 = new Google_Service_V1_MyBusinessAccount($client);

                    if ($key == '') {
                        //すべてのアカウント情報を取得
                        $gmbApiAccountQueryService->getAccounts($gmbService_v1, $gmbApiService);
                    } else {
                        //特定のアカウント情報を取得
                        $gmbAccountId = $key;
                        $gmbApiAccountQueryService->getAccount($gmbService_v1, $gmbApiService, $gmbAccountId);
                    }
                }

                // 店舗情報を取得
                if ($api == 'all' || $api == 'getLocations') {
                    $gmbApiLocationQueryService = new GmbApiLocationV1QueryService();
                    $gmbService_v1 = new Google_Service_V1_MyBusinessLocation($client);

                    if ($key == '') {
                        // すべてのアカウントの全店舗情報
                        $gmbApiLocationQueryService->getLocationsForAllAccounts($gmbService_v1, $gmbApiService);

                    } else {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 1) {
                            // 特定のアカウントの全店舗情報

                            $gmbAccountId = $keyAry[0];
                            $gmbApiLocationQueryService->getLocations($gmbService_v1, $gmbApiService, $gmbAccountId);
                        } else if (count($keyAry) == 2) {
                            // 特定の店舗情報
                            $gmbAccountId = $keyAry[0];
                            $gmbLocationId = $keyAry[1];
                            $gmbApiLocationQueryService->getLocation($gmbService_v1, $gmbApiService, $gmbAccountId, $gmbLocationId);
                        }
                    }
                }

                // クチコミを取得
                if ($api == 'all' || $api == 'getReviews') {
                    $gmbApiReviewQueryService = new GmbApiReviewQueryService();

                    if ($key == '') {
                        // 契約企業全てのブランド・全店舗のクチコミ
                        $gmbApiReviewQueryService->getReviews($gmbService_v1, $gmbApiService, null, null);

                    } else {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 1) {
                            // 指定されたブランド配下の全店舗のクチコミ
                            $gmbAccountId = $keyAry[0];
                            $gmbApiReviewQueryService->getReviews($gmbService_v1, $gmbApiService, $gmbAccountId, null);
                        } else if (count($keyAry) == 2) {
                            // 指定された店舗のクチコミ
                            $gmbAccountId = $keyAry[0];
                            $gmbLocationId = $keyAry[1];
                            $gmbApiReviewQueryService->getReviews($gmbService_v1, $gmbApiService, $gmbAccountId, $gmbLocationId);
                        }
                    }
                }  

                if ($api == 'all' || $api == 'getLocalPosts') {
                    $gmbApiLocalpostQueryService = new GmbApiLocalpostQueryService();

                    if ($key == '') {
                        // 契約企業全てのブランド・全店舗の投稿
                        $gmbApiLocalpostQueryService->getLocalPosts($gmbService_v1, $gmbApiService, null, null);

                    } else {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 1) {
                            // 指定されたブランド配下の全店舗の投稿
                            $gmbAccountId = $keyAry[0];
                            $gmbApiLocalpostQueryService->getLocalPosts($gmbService_v1, $gmbApiService, $gmbAccountId, null);
                        } else if (count($keyAry) == 2) {
                            // 指定された店舗の投稿
                            $gmbAccountId = $keyAry[0];
                            $gmbLocationId = $keyAry[1];
                            $gmbApiLocalpostQueryService->getLocalPosts($gmbService_v1, $gmbApiService, $gmbAccountId, $gmbLocationId);
                        }
                    }
                }
            }
        }

        // トークンが無いか、有効期限切れ
        $gmbApiService->refreshToken($client, $tokenFile);


        /*
        $tokenFile = dirname(__FILE__) .'/my_bussiness_token.json';
        if (file_exists($tokenFile)) {
            $this->info('トークン有効');

            $accessToken = json_decode(file_get_contents($tokenFile), true);
            $client->setAccessToken($accessToken);
  
            //アカウントリストを取得
            $gmbService = new Google_Service_MyBusiness($client);
            $results = $gmbService->accounts->listAccounts();
            //echo var_dump($results);


            return config('command.exit_code.SUCCESS');
        }


        if ($client->isAccessTokenExpired()) {
            $this->comment('トークンが無いか、有効期限切れ');

            // リフレッシュするか新しいトークンを取得
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            } else {

                // 認証コードをトークンに交換
                //$client->setRedirectUri("urn:ietf:wg:oauth:2.0:oob");
                //$authUrl = $client->createAuthUrl();   
                //echo "authUrl=".$authUrl."\n\n"; <-- このURLをブラウザで表示して、高松さんのGoogleアカウントでログインして認証すると、リダイレクトされずに認証コードが表示されるので、その認証コードを使う

                //$code = "4/yQGgt-NlF9tiiQhun2u0RHUw2tYoKR-z6NlgKABHAxXf-UcwTG3gXX8";
                $code = config('command.auth.code');
                $accessToken = $client->fetchAccessTokenWithAuthCode($code);
                $client->setAccessToken($accessToken);

                if (array_key_exists('error', $accessToken)) {
                    //throw new Exception(join(', ', $accessToken));
                    $this->error('Exception:setAccessToken()');
                    return config('command.exit_code.ERROR');
                }
            }

            // トークンの保存
            if (!file_exists($tokenFile)) {
                touch($tokenFile);
                chmod($tokenFile, 0777);
            }

            file_put_contents($tokenFile, json_encode($client->getAccessToken()));
        }
        */
    }

    private function _debug($msg) {
        var_dump($msg);
    }
}
