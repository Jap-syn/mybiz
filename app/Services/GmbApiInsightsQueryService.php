<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\LocationReport;
use App\LogApi;
use App\Services\GmbApiService;
use \Illuminate\Database\QueryException;

use Google_Client;
use Google_Service_MyBusiness_ReportLocationInsightsRequest;
use Google_Service_MyBusiness_BasicMetricsRequest;
use Google_Service_MyBusiness_MetricRequest;
use Google_Service_MyBusiness_TimeRange;
use Google_Service_MyBusiness_DrivingDirectionMetricsRequest;
use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiInsightsQueryService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_account_count;
    private $_started_at;
    private $_ended_at;
    // APIで1度にデータ取得できる店舗数
    private $_max_Locatins = 10;

    public function __construct()
    {

    }

    // Insights情報を取得
    public function getReportLocationInsights($gmbService, $gmbApiService, $day, $gmbAccountId=null, $gmbLocationId=null)
    { 

        $this->_kubun = 1;
        $this->_class_function = "GmbApiInsightsQueryService.getReportLocationInsights";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_location_reports_new_count = 0;
        $this->_location_reports_update_count = 0;
        $this->_location_reports_exception_count = 0;

        $paramAry = array();
        $paramAry['startDate'] = date("Y-m-d",strtotime("-$day day"));
        $paramAry['startTime'] = date("Y-m-d",strtotime("-$day day")) ."T00:00:00Z";    // 2021.12 修正
        $day--;
        $paramAry['endDate'] = date("Y-m-d",strtotime("-$day day"));                    // 2021.12 修正
        $paramAry['endTime'] = date("Y-m-d",strtotime("-$day day")) ."T00:00:00Z";      // 2021.12 修正

        $locationAry = array();
        $locationNameAry = array();
        if ($gmbAccountId == null && $gmbLocationId == null) {
            // 契約企業全てのブランド・店舗のInsights情報を取得
            $accounts = Account::select(['gmb_account_id'])
                                ->active()
                                ->where('gmb_account_id', '<>', '102356320813998189642')
                                ->get();

            foreach ($accounts as $account) {
                $locations = Location::select(['account_id', 'location_id', 'gmb_account_id', 'gmb_location_id', 'gmb_location_name'])
                                    ->active()
                                    ->where('gmb_account_id', '=', $account['gmb_account_id'])
                                    ->get();

                $locationAry = array();
                foreach ($locations as $location) {
                    $name = 'accounts/' .$account['gmb_account_id'] .'/locations/' .$location['gmb_location_id'];
                    $locationNameAry[] = $name;
    
                    $ary = array();
                    $ary['account_id'] = $location->account_id;
                    $ary['location_id'] = $location->location_id;
                    $ary['gmb_location_name'] = $location->gmb_location_name;
                    $ary['gmb_account_id'] = $location->gmb_account_id;
                    $ary['gmb_location_id'] = $location->gmb_location_id;
                    $locationAry[$name] = $ary;

                    if (count($locationNameAry) >= $this->_max_Locatins) {
                        $this->_getReportLocationInsights($gmbService, $gmbApiService, $account['gmb_account_id'], $locationNameAry, $locationAry, $paramAry);
                        $locationAry = array();
                        unset($locationNameAry);
                    }
                    unset($location);
                }

                if (isset($locationNameAry)) {
                    if (count($locationNameAry) > 0) {
                        $this->_getReportLocationInsights($gmbService, $gmbApiService, $account['gmb_account_id'], $locationNameAry, $locationAry, $paramAry);
                    }
                }

                unset($locationAry);
                unset($locationNameAry);
                unset($locations);
            }
            unset($accounts);

        } else if ($gmbLocationId == null) {
            // 指定されたブランド配下の全店舗のInsights情報を取得
            $locations = Location::select(['account_id', 'location_id', 'gmb_account_id', 'gmb_location_id', 'gmb_location_name'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->get();

            $locationAry = array();
            foreach ($locations as $location) {
                $name = 'accounts/' .$gmbAccountId .'/locations/' .$location['gmb_location_id'];
                $locationNameAry[] = $name;

                $ary = array();
                $ary['account_id'] = $location->account_id;
                $ary['location_id'] = $location->location_id;
                $ary['gmb_location_name'] = $location->gmb_location_name;
                $ary['gmb_account_id'] = $location->gmb_account_id;
                $ary['gmb_location_id'] = $location->gmb_location_id;
                $locationAry[$name] = $ary;

                if (count($locationNameAry) >= $this->_max_Locatins) {
                    $this->_getReportLocationInsights($gmbService, $gmbApiService, $gmbAccountId, $locationNameAry, $locationAry, $paramAry);
                    $locationAry = array();
                    unset($locationNameAry);
                }
                unset($location);
            }

            if (isset($locationNameAry)) {
                if (count($locationNameAry) > 0) {
                    $this->_getReportLocationInsights($gmbService, $gmbApiService, $gmbAccountId, $locationNameAry, $locationAry, $paramAry);
                }
            }

            unset($locationAry);
            unset($locationNameAry);
            unset($locations);

        } else {
            // 指定された店舗のInsights情報を取得
            $location = Location::select(['account_id', 'location_id', 'gmb_account_id', 'gmb_location_id', 'gmb_location_name'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->where('gmb_location_id', '=', $gmbLocationId)
                                ->first();

            if ($location != null) {
                $name = 'accounts/' .$location['gmb_account_id'] .'/locations/' .$location['gmb_location_id'];
                $locationNameAry[] = $name;

                $locationAry = array();
                $ary = array();
                $ary['account_id'] = $location->account_id;
                $ary['location_id'] = $location->location_id;
                $ary['gmb_location_name'] = $location->gmb_location_name;
                $ary['gmb_account_id'] = $location->gmb_account_id;
                $ary['gmb_location_id'] = $location->gmb_location_id;
                $locationAry[$name] = $ary;

                $this->_getReportLocationInsights($gmbService, $gmbApiService, $gmbAccountId, $locationNameAry, $locationAry, $paramAry);
                unset($locationAry);
                unset($locationNameAry);
            }
            $location = null;
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("date=%s new_count=%d, update_count=%d, exception_count=%d", 
                        $paramAry['startDate'],$this->_location_reports_new_count, $this->_location_reports_update_count, $this->_location_reports_exception_count);
        $this->_logging($gmbApiService);

     //   logger()->error(DB::getQueryLog());
    }

    // 店舗のInsights情報を取得
    private function _getReportLocationInsights($gmbService, $gmbApiService, $gmbAccountId, $locationNameAry, $locationAry, $paramAry)
    { 
        
        //$this->_debug($paramAry);

        try {

            $postBody = new Google_Service_MyBusiness_ReportLocationInsightsRequest();
            $basic = new Google_Service_MyBusiness_BasicMetricsRequest();
            $metrics = new Google_Service_MyBusiness_MetricRequest();
            $timeRange = new Google_Service_MyBusiness_TimeRange();
            //$driving = new Google_Service_MyBusiness_DrivingDirectionMetricsRequest();

            $postBody->setLocationNames($locationNameAry);

            $metrics->setMetric("ALL"); 
            $basic->setMetricRequests($metrics); 
            //$basic->setMetricRequests(['metric' => 'ALL', 'options' => 'AGGREGATED_DAILY']);

            $timeRange->setStartTime($paramAry['startTime']); 
            $timeRange->setEndTime($paramAry['endTime']); 
            $basic->setTimeRange($timeRange);
            $postBody->setBasicRequest($basic);

            //$driving->setNumDays(90);
            //$driving->setNumDays("SEVEN");
            //$driving->setLanguageCode("ja");
            //$postBody->setDrivingDirectionsRequest($driving);

            $name = 'accounts/'.$gmbAccountId;
            $gmb = $gmbService->accounts_locations->reportInsights($name, $postBody);
            if ($gmb) {
                //$this->_debug('locationAry=' .count($locationAry) .'  locationMetrics=' .count($gmb['locationMetrics']));

                foreach($gmb['locationMetrics'] as $locationMetrics){

                    $name = $locationMetrics['locationName'];
                    //$this->_debug($name);

                    if (array_key_exists($name, $locationAry)) {
                        $hasValues = False;
                        $responseAry = array();
                        foreach($locationMetrics['metricValues'] as $metricValue){

                            if ($paramAry['startTime']==$metricValue['totalValue']['timeDimension']['timeRange']['startTime'] 
                            && $paramAry['endTime']==$metricValue['totalValue']['timeDimension']['timeRange']['endTime']) 
                            {
                                $responseAry['metric'][$metricValue['metric']] = $metricValue['totalValue']['value'];
                                if ($hasValues == False) {
                                    $responseAry['aggregate_date'] = explode('T', $paramAry['endDate'])[0] ." 00:00:00";
                                }
                                $hasValues = True;
                            }
                        }

                        if ($hasValues) {
                            $this->_saveLocationReports($gmbService, $gmbApiService, $paramAry, $locationAry[$name], $responseAry);
                        }
                        unset($responseAry);
                    }
                }
            }
            unset($gmb);

        }catch(Google_Service_Exception $e){
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiInsightsQueryService._getLocal_getReportLocationInsightsPosts";
            $this->_detail = implode(",", $locationNameAry);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            // 開放
            $location = null;
        }
    }

    // ダッシュボードのメトリクス値を登録
    private function _saveLocationReports($gmbService, $gmbApiService, $paramAry, $locationAry, $responseAry)
    {
        //$this->_debug("_saveLocationReports " .$locationAry['gmb_location_name']);
        //$this->_debug($responseAry);

        try {

            $metricAry = array();
            $metricAry[config('const.METRIC.QUERIES_DIRECT')] = 0;
            $metricAry[config('const.METRIC.QUERIES_INDIRECT')] = 0;
            $metricAry[config('const.METRIC.QUERIES_CHAIN')] = 0;
            $metricAry[config('const.METRIC.VIEWS_MAPS')] = 0;
            $metricAry[config('const.METRIC.VIEWS_SEARCH')] = 0;
            $metricAry[config('const.METRIC.ACTIONS_WEBSITE')] = 0;
            $metricAry[config('const.METRIC.ACTIONS_PHONE')] = 0;
            $metricAry[config('const.METRIC.ACTIONS_DRIVING_DIRECTIONS')] = 0;
            $metricAry[config('const.METRIC.PHOTOS_VIEWS_MERCHANT')] = 0;
            $metricAry[config('const.METRIC.PHOTOS_VIEWS_CUSTOMERS')] = 0;
            $metricAry[config('const.METRIC.PHOTOS_COUNT_MERCHANT')] = 0;
            $metricAry[config('const.METRIC.PHOTOS_COUNT_CUSTOMERS')] = 0;
            $metricAry[config('const.METRIC.LOCAL_POST_VIEWS_SEARCH')] = 0;
            $metricAry[config('const.METRIC.LOCAL_POST_ACTIONS_CALL_TO_ACTION')] = 0;

            foreach($responseAry['metric'] as $key => $value){
                if (is_numeric($value)) {
                    $metricAry[$key] = $value;
                }
            }

            $locationReport = LocationReport::where('location_id', '=', $locationAry['location_id'])
                                ->whereDate('aggregate_date', '=', $paramAry['endDate'])
                                ->first();

            if ($locationReport == null) {

                $locationReport = new LocationReport;
                $locationReport->location_id = $locationAry['location_id'];
                $locationReport->aggregate_date = $responseAry['aggregate_date'];
                $locationReport->gmb_account_id = $locationAry['gmb_account_id'];
                $locationReport->gmb_location_id = $locationAry['gmb_location_id'];
                $locationReport->gmb_location_name = $locationAry['gmb_location_name'];

                $locationReport->gmb_queries_direct = $metricAry[config('const.METRIC.QUERIES_DIRECT')];
                $locationReport->gmb_queries_indirect = $metricAry[config('const.METRIC.QUERIES_INDIRECT')];
                $locationReport->gmb_queries_chain = $metricAry[config('const.METRIC.QUERIES_CHAIN')];
                $locationReport->gmb_views_maps = $metricAry[config('const.METRIC.VIEWS_MAPS')];
                $locationReport->gmb_views_search = $metricAry[config('const.METRIC.VIEWS_SEARCH')];
                $locationReport->gmb_actions_website = $metricAry[config('const.METRIC.ACTIONS_WEBSITE')];
                $locationReport->gmb_actions_phone = $metricAry[config('const.METRIC.ACTIONS_PHONE')];
                $locationReport->gmb_actions_driving_directions = $metricAry[config('const.METRIC.ACTIONS_DRIVING_DIRECTIONS')];
                $locationReport->gmb_photos_views_merchant = $metricAry[config('const.METRIC.PHOTOS_VIEWS_MERCHANT')];
                $locationReport->gmb_photos_views_customers = $metricAry[config('const.METRIC.PHOTOS_VIEWS_CUSTOMERS')];
                $locationReport->gmb_photos_count_merchant = $metricAry[config('const.METRIC.PHOTOS_COUNT_MERCHANT')];
                $locationReport->gmb_photos_count_customers = $metricAry[config('const.METRIC.PHOTOS_COUNT_CUSTOMERS')];
                $locationReport->gmb_local_post_views_search = $metricAry[config('const.METRIC.LOCAL_POST_VIEWS_SEARCH')];
                $locationReport->gmb_local_post_actions_call_to_action = $metricAry[config('const.METRIC.LOCAL_POST_ACTIONS_CALL_TO_ACTION')];

                $locationReport->create_user_id  = 0;
                $locationReport->save();
                $this->_location_reports_new_count ++;

                /*
                // 0件になる原因調査
                if ($metricAry[config('const.METRIC.QUERIES_DIRECT')] == 0 || $metricAry[config('const.METRIC.QUERIES_INDIRECT')] == 0 || $metricAry[config('const.METRIC.QUERIES_CHAIN')] == 0) {

                    $this->_proc_exit = -1;
                    $this->_class_function = "GmbApiInsightsQueryService._saveLocationReports";
                    $this->_detail = sprintf("location_id=%s aggregate_date=%s gmb_account_id=%s gmb_location_id=%s gmb_location_name=%s", $locationAry['location_id'], $responseAry['aggregate_date'], $locationAry['gmb_account_id'], $locationAry['gmb_location_id'], $locationAry['gmb_location_name']);

                    $this->_exception = sprintf("insert QUERIES_DIRECT=%d QUERIES_INDIRECT=%d QUERIES_CHAIN=%d", $metricAry[config('const.METRIC.QUERIES_DIRECT')], $metricAry[config('const.METRIC.QUERIES_INDIRECT')], $metricAry[config('const.METRIC.QUERIES_CHAIN')]);
                    $this->_logging($gmbApiService);
                }
                */

            } else {

                if ($metricAry[config('const.METRIC.QUERIES_DIRECT')] > 0) {
                    $locationReport->gmb_queries_direct = $metricAry[config('const.METRIC.QUERIES_DIRECT')];
                }
                if ($metricAry[config('const.METRIC.QUERIES_INDIRECT')] > 0) {
                    $locationReport->gmb_queries_indirect = $metricAry[config('const.METRIC.QUERIES_INDIRECT')];
                }
                if ($metricAry[config('const.METRIC.QUERIES_CHAIN')] > 0) {
                    $locationReport->gmb_queries_chain = $metricAry[config('const.METRIC.QUERIES_CHAIN')];
                }
                if ($metricAry[config('const.METRIC.VIEWS_MAPS')] > 0) {
                    $locationReport->gmb_views_maps = $metricAry[config('const.METRIC.VIEWS_MAPS')];
                }
                if ($metricAry[config('const.METRIC.VIEWS_SEARCH')] > 0) {
                    $locationReport->gmb_views_search = $metricAry[config('const.METRIC.VIEWS_SEARCH')];
                }
                if ($metricAry[config('const.METRIC.ACTIONS_WEBSITE')] > 0) {
                    $locationReport->gmb_actions_website = $metricAry[config('const.METRIC.ACTIONS_WEBSITE')];
                }
                if ($metricAry[config('const.METRIC.ACTIONS_PHONE')] > 0) {
                    $locationReport->gmb_actions_phone = $metricAry[config('const.METRIC.ACTIONS_PHONE')];
                }
                if ($metricAry[config('const.METRIC.ACTIONS_DRIVING_DIRECTIONS')] > 0) {
                    $locationReport->gmb_actions_driving_directions = $metricAry[config('const.METRIC.ACTIONS_DRIVING_DIRECTIONS')];
                }
                if ($metricAry[config('const.METRIC.PHOTOS_VIEWS_MERCHANT')] > 0) {
                    $locationReport->gmb_photos_views_merchant = $metricAry[config('const.METRIC.PHOTOS_VIEWS_MERCHANT')];
                }
                if ($metricAry[config('const.METRIC.PHOTOS_VIEWS_CUSTOMERS')] > 0) {
                    $locationReport->gmb_photos_views_customers = $metricAry[config('const.METRIC.PHOTOS_VIEWS_CUSTOMERS')];
                }
                if ($metricAry[config('const.METRIC.PHOTOS_COUNT_MERCHANT')] > 0) {
                    $locationReport->gmb_photos_count_merchant = $metricAry[config('const.METRIC.PHOTOS_COUNT_MERCHANT')];
                }
                if ($metricAry[config('const.METRIC.PHOTOS_COUNT_CUSTOMERS')] > 0) {
                    $locationReport->gmb_photos_count_customers = $metricAry[config('const.METRIC.PHOTOS_COUNT_CUSTOMERS')];
                }
                if ($metricAry[config('const.METRIC.LOCAL_POST_VIEWS_SEARCH')] > 0) {
                    $locationReport->gmb_local_post_views_search = $metricAry[config('const.METRIC.LOCAL_POST_VIEWS_SEARCH')];
                }
                if ($metricAry[config('const.METRIC.LOCAL_POST_ACTIONS_CALL_TO_ACTION')] > 0) {
                    $locationReport->gmb_local_post_actions_call_to_action = $metricAry[config('const.METRIC.LOCAL_POST_ACTIONS_CALL_TO_ACTION')];
                }

                $locationReport->update_user_id  = 0;
                $locationReport->save();
                $this->_location_reports_update_count ++;

                /*
                // 0件になる原因調査
                if ($metricAry[config('const.METRIC.QUERIES_DIRECT')] == 0 || $metricAry[config('const.METRIC.QUERIES_INDIRECT')] == 0 || $metricAry[config('const.METRIC.QUERIES_CHAIN')] == 0) {

                    $this->_proc_exit = -1;
                    $this->_class_function = "GmbApiInsightsQueryService._saveLocationReports";
                    $this->_detail = sprintf("location_id=%s aggregate_date=%s gmb_account_id=%s gmb_location_id=%s gmb_location_name=%s", $locationAry['location_id'], $responseAry['aggregate_date'], $locationAry['gmb_account_id'], $locationAry['gmb_location_id'], $locationAry['gmb_location_name']);

                    $this->_exception = sprintf("update QUERIES_DIRECT=%d QUERIES_INDIRECT=%d QUERIES_CHAIN=%d", $metricAry[config('const.METRIC.QUERIES_DIRECT')], $metricAry[config('const.METRIC.QUERIES_INDIRECT')], $metricAry[config('const.METRIC.QUERIES_CHAIN')]);
                    $this->_logging($gmbApiService);
                }
                */
            }

        }catch(Google_Service_Exception $e){
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiInsightsQueryService._saveLocationReports";

            $this->_detail = sprintf("location_id=%s aggregate_date=%s gmb_account_id=%s gmb_location_id=%s gmb_location_name=%s", $locationAry['location_id'], $responseAry['aggregate_date'], $locationAry['gmb_account_id'], $locationAry['gmb_location_id'], $locationAry['gmb_location_name']);

            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);
            $this->_location_reports_exception_count ++;

        } finally {
            // 開放
            $locationReport = null;
        }

    }

    // ログ出力
    private function _logging($gmbApiService) {
      $this->_ended_at = Carbon::now();
      $gmbApiService->logApiBatch($this->_kubun, 
                                  $this->_proc_exit,
                                  $this->_class_function,
                                  $this->_detail,
                                  $this->_exception,
                                  $this->_started_at,
                                  $this->_ended_at);
    }

    private function _debug($msg) {
        var_dump($msg);
    }
}