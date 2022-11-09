<?php
// 修正
namespace App\Console\Commands;

require_once (dirname(__FILE__) .'/../../../vendor/autoload.php');
require_once (dirname(__FILE__) .'/MyBusiness.php');

use Illuminate\Console\Command;
use App;
use App\Account;
use App\Services\GmbApiService;
use Google_Client;
use Google_Service_MyBusiness;

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
        $api = $this->argument('api');
        $key = $this->argument('key');
        $this->_debug('GmbQueryCommand api=' .$api .' key=' .$key);

        $authFile = dirname(__FILE__) .'/' .config('command.client_secret.json');
        $tokenFile = dirname(__FILE__) .'/my_bussiness_token.json';

        $gmbApiService = new GmbApiService();
        $client = $gmbApiService->newGoogleClient($authFile);
        $result = $gmbApiService->setAccessToken($client, $tokenFile);
        if($result) 
        {
            if (! $client->isAccessTokenExpired()) 
            {
                $this->_debug('トークン有効');

                $gmbService = new Google_Service_MyBusiness($client);
                //すべてのアカウント情報を取得
                if ($api == 'all' || $api == 'allAccounts') {
                    $gmbApiService->allAccounts($gmbService);
                }

                //特定のアカウント情報を取得
                if ($api == 'all' || $api == 'getAccount') {
                    $gmbApiService->getAccount($gmbService, $key);
                }

                // アカウントのすべての店舗情報を取得 key=accountId
                if ($api == 'getLocations') {
                    if ($key == '') {
                        $this->_allAccounts($gmbService, $gmbApiService);
                    } else {
                        $gmbApiService->getLocations($gmbService, $key);
                    }
                }
                // 特定の店舗情報を取得　key=accountId/locationId
                if ($api == 'getLocation') {
                    if ($key != '') {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 2) {
                            $gmbApiService->getLocation($gmbService, $keyAry[0], $keyAry[1]);
                        }
                    }
                }

                // 店舗のすべての投稿を取得
                if ($api == 'getLocalPosts') {
                    if ($key != '') {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 2) {
                            $gmbApiService->getLocalPosts($gmbService, $keyAry[0], $keyAry[1]);
                        }
                    }
                }              

                // 特定の投稿を取得
                if ($api == 'getLocalPost') {
                    if ($key != '') {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 3) {
                            $gmbApiService->getLocalPost($gmbService, $keyAry[0], $keyAry[1], $keyAry[2]);
                        }
                    }
                } 
                /*
                // 店舗のすべてのクチコミを取得
                if ($api == 'getReviews') {
                    if ($key != '') {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 2) {
                            $gmbApiService->getReviews($gmbService, $keyAry[0], $keyAry[1]);
                        }
                    }
                } 
                */
                // 特定のクチコミを取得
                if ($api == 'getReview') {
                    if ($key != '') {
                        $keyAry = explode("/", $key);
                        if (count($keyAry) == 3) {
                            $gmbApiService->getReview($gmbService, $keyAry[0], $keyAry[1], $keyAry[2]);
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

    // アカウントを抽出
    private function _allAccounts($gmbService, $gmbApiService) {
        $accounts = Account::active()->get();
        foreach ($accounts as $account) {
            $gmbApiService->getLocations($gmbService, $account->gmb_account_id);
        }
    }

    private function _debug($msg) {
        //$this->comment($msg);
        var_dump($msg);
    }
}
