<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\LogApi;
use App\Services\GmbApiService;
use \Illuminate\Database\QueryException;

use Google_Client;
use Google_Service_MyBusiness_Location;
use Google_Service_MyBusiness_Profile;
use Google_Service_MyBusiness_LocalPost;
use Google_Service_MyBusiness_CallToAction;
use Google_Service_MyBusiness_LocalPostEvent;
use Google_Service_MyBusiness_Date;
use Google_Service_MyBusiness_TimeOfDay;
use Google_Service_MyBusiness_TimeInterval;
use Google_Service_MyBusiness_LocalPostOffer;
use Google_Service_MyBusiness_LocalPostProduct;
use Google_Service_MyBusiness_MediaItem;
use Google_Service_MyBusiness_LocationAssociation;
use Google_Service_MyBusiness_Review;
use Google_Service_MyBusiness_ReviewReply;

use Google_Service_MyBusiness_TimePeriod;
use Google_Service_MyBusiness_BusinessHours;
use Google_Service_MyBusiness_SpecialHours;
use Google_Service_MyBusiness_SpecialHourPeriod;

use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiLocationRegistService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_location_count;
    private $_started_at;
    private $_ended_at;
    
    public function __construct()
    {

    }

        
    public function registSpecialHour($gmbService, $gmbApiService)
    { 
        $csv_dir = "/var/www/phplaravel/storage/csv";
        $csv_psth = $csv_dir ."/*.csv";
        echo $csv_psth ."\n";

        $conut = 0;
        $first = true;
        foreach(glob($csv_psth) as $file) {
            $gmb_account_id = basename($file, '.csv');
            echo $file ."\n";
            echo "gmb_account_id=".$gmb_account_id ."\n";

            $f = fopen($file, "r");
            while ($line = fgets($f)) {
                $data_ary = explode(",", $line);

                if ($data_ary[0] == "0") {
                    echo "全店舗 ーーー\n";
                    $locations = Location::select(['gmb_location_id'])
                                        ->active()
                                        ->where('gmb_account_id', '=', $gmb_account_id)
                                        ->get();

                    foreach ($locations as $location) {
                        $this->_patchSpecialHour($gmbService, $gmbApiService, $gmb_account_id, $location['gmb_location_id'], $line);
                        unset($location);
                        $conut ++;

                        break;
                    }
                    unset($locations);
                    sleep(5);
                } else {
                    if ($first) echo "個別店舗 ーーー\n";
                    $first = false;

                    $gmb_location_id = $data_ary[0];
                    $this->_patchSpecialHour($gmbService, $gmbApiService, $gmb_account_id, $gmb_location_id, $line);
                    $conut++;
                }
                break;
            }
        }

        echo $conut."件、連携完了\n";
    }

    public function _patchSpecialHour($gmbService, $gmbApiService, $gmb_account_id, $gmb_location_id, $line){
        $specialPeriodAry = array();
        $name = sprintf('accounts/%s/locations/%s', $gmb_account_id, $gmb_location_id);
        $data_ary = explode(",", $line);
        $start_date = $data_ary[1];
        $start_year = substr($start_date, 0,4);
        $start_month = substr($start_date, 4,2);
        $start_day = substr($start_date, -2);
        $end_year = $start_year;
        $end_month = $start_month;
        $end_day = $start_day;
        $start_time = $data_ary[2];
        $end_time = $data_ary[3];
        $start_time2 = $data_ary[4];
        $end_time2 = $data_ary[5];
        $holiday = str_replace("\r\n", '', $data_ary[6]);

        try {

            if ($start_time != "" && $end_time != "" && $start_time2 == "" && $end_time2 == "") {
                $start_time = substr($start_time,0,2) .":" .substr($start_time,-2);
                $end_time = substr($end_time,0,2) .":" .substr($end_time,-2);
                echo $gmb_location_id ." " .$start_year ."/" .$start_month ."/" .$start_day ." ".$start_time ." - " .$end_time ." 休日=" .$holiday ."\n";
                $specialPeriodAry = array();
                $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$start_year);
                $datePeriod->setMonth((int)$start_month);
                $datePeriod->setDay((int)$start_day);
                $specialHourPeriod->setStartDate($datePeriod);
                $specialHourPeriod->setOpenTime($start_time);
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$end_year);
                $datePeriod->setMonth((int)$end_month);
                $datePeriod->setDay((int)$end_day);
                $specialHourPeriod->setEndDate($datePeriod);
                $specialHourPeriod->setCloseTime($end_time);
                if ($holiday == "1") {
                    $specialHourPeriod->setIsClosed(true);
                } else {
                    $specialHourPeriod->setIsClosed(false);
                }
                $specialPeriodAry[] = $specialHourPeriod;
                $specialHours = new Google_Service_MyBusiness_SpecialHours();
                $specialHours->setSpecialHourPeriods($specialPeriodAry); 
                $location = new Google_Service_MyBusiness_Location(); 
                $location->setName($name); 
                $location->setSpecialHours($specialHours);  
                $params=['updateMask'=>"specialHours"];
                $name = sprintf('accounts/%s/locations/%s', $gmb_account_id, $gmb_location_id);
                $gmb = $gmbService->accounts_locations->patch($name,$location,$params);

            } else if ($start_time != "" && $end_time != "" && $start_time2 != "" && $end_time2 != "") {
                $start_time = substr($start_time,0,2) .":" .substr($start_time,-2);
                $end_time = substr($end_time,0,2) .":" .substr($end_time,-2);
                $start_time2 = substr($start_time2,0,2) .":" .substr($start_time2,-2);
                $end_time2 = substr($end_time2,0,2) .":" .substr($end_time2,-2);
                echo $gmb_location_id ." " .$start_year ."/" .$start_month ."/" .$start_day ." ".$start_time ." - " .$end_time ." " .$start_time2 ." - " .$end_time2 ." 休日=" .$holiday ."\n";
                $specialPeriodAry = array();
                $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$start_year);
                $datePeriod->setMonth((int)$start_month);
                $datePeriod->setDay((int)$start_day);
                $specialHourPeriod->setStartDate($datePeriod);
                $specialHourPeriod->setOpenTime("13:00");
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$end_year);
                $datePeriod->setMonth((int)$end_month);
                $datePeriod->setDay((int)$end_day);
                $specialHourPeriod->setEndDate($datePeriod);
                $specialHourPeriod->setCloseTime("17:00");
        
                if ($holiday == "1") {
                    $specialHourPeriod->setIsClosed(true);
                } else {
                    $specialHourPeriod->setIsClosed(false);
                }
                $specialPeriodAry[] = $specialHourPeriod;
                $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$start_year);
                $datePeriod->setMonth((int)$start_month);
                $datePeriod->setDay((int)$start_day);
                $specialHourPeriod->setStartDate($datePeriod);
                $specialHourPeriod->setOpenTime("18:00");
                $datePeriod = new Google_Service_MyBusiness_Date();
                $datePeriod->setYear((int)$end_year);
                $datePeriod->setMonth((int)$end_month);
                $datePeriod->setDay((int)$end_day);
                $specialHourPeriod->setEndDate($datePeriod);
                $specialHourPeriod->setCloseTime("20:00");
        
                if ($holiday == "1") {
                    $specialHourPeriod->setIsClosed(true);
                } else {
                    $specialHourPeriod->setIsClosed(false);
                }
                $specialPeriodAry[] = $specialHourPeriod;
                $specialHours = new Google_Service_MyBusiness_SpecialHours();
                $specialHours->setSpecialHourPeriods($specialPeriodAry); 
                $location = new Google_Service_MyBusiness_Location(); 
                $location->setName($name); 
                $location->setSpecialHours($specialHours);  
                $params=['updateMask'=>"specialHours"];
                $name = sprintf('accounts/%s/locations/%s', $gmb_account_id, $gmb_location_id);
                $gmb = $gmbService->accounts_locations->patch($name,$location,$params);
            }

        }catch(Google_Service_Exception $e){
            echo "Exception: gmb_location_id=" .$gmb_location_id ." " .$line . " Error Message: ".$e->getMessage();
        }

    }

    /*
    // 営業時間の更新　OK
    public function registLocations($gmbService, $gmbApiService)
    { 
        $gmbAccountId = "114708824399692680522";
        $gmbLocationId = "4217405982519266446";
        $name = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        echo $name;
        
        $timePeriodAry = array();
        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("MONDAY");
        $timePeriod->setOpenTime("10:00");
        $timePeriod->setCloseDay("MONDAY");
        $timePeriod->setCloseTime("18:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("TUESDAY");
        $timePeriod->setOpenTime("10:00");
        $timePeriod->setCloseDay("TUESDAY");
        $timePeriod->setCloseTime("18:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("WEDNESDAY");
        $timePeriod->setOpenTime("10:00");
        $timePeriod->setCloseDay("WEDNESDAY");
        $timePeriod->setCloseTime("18:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("THURSDAY");
        $timePeriod->setOpenTime("10:00");
        $timePeriod->setCloseDay("THURSDAY");
        $timePeriod->setCloseTime("18:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("FRIDAY");
        $timePeriod->setOpenTime("10:00");
        $timePeriod->setCloseDay("FRIDAY");
        $timePeriod->setCloseTime("18:00");
        $timePeriodAry[] = $timePeriod;

        $businessHours = new Google_Service_MyBusiness_BusinessHours();
        $businessHours->setPeriods($timePeriodAry); 

        $location = new Google_Service_MyBusiness_Location(); 
        $location->setName($name); 
        $location->setRegularHours($businessHours);  
        $params=['updateMask'=>"regularHours"];

        $gmb = $gmbService->accounts_locations->patch($name,$location,$params);
        $this->_debug($gmb);
    }
    */

    /*
    // 営業時間の更新　OK
    public function registLocations($gmbService, $gmbApiService)
    { 
        $gmbAccountId = "114708824399692680522";
        $gmbLocationId = "4217405982519266446";
        $name = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        echo $name;
        
        $timePeriodAry = array();
        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("MONDAY");
        $timePeriod->setOpenTime("13:00");
        $timePeriod->setCloseDay("MONDAY");
        $timePeriod->setCloseTime("17:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("TUESDAY");
        $timePeriod->setOpenTime("13:00");
        $timePeriod->setCloseDay("TUESDAY");
        $timePeriod->setCloseTime("17:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("WEDNESDAY");
        $timePeriod->setOpenTime("13:00");
        $timePeriod->setCloseDay("WEDNESDAY");
        $timePeriod->setCloseTime("17:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("THURSDAY");
        $timePeriod->setOpenTime("13:00");
        $timePeriod->setCloseDay("THURSDAY");
        $timePeriod->setCloseTime("17:00");
        $timePeriodAry[] = $timePeriod;

        $timePeriod = new Google_Service_MyBusiness_TimePeriod();
        $timePeriod->setOpenDay("FRIDAY");
        $timePeriod->setOpenTime("13:00");
        $timePeriod->setCloseDay("FRIDAY");
        $timePeriod->setCloseTime("17:00");
        $timePeriodAry[] = $timePeriod;

        $businessHours = new Google_Service_MyBusiness_BusinessHours();
        $businessHours->setPeriods($timePeriodAry); 

        $location = new Google_Service_MyBusiness_Location(); 
        $location->setName($name); 
        $location->setRegularHours($businessHours);  
        $params=['updateMask'=>"regularHours"];

        $gmb = $gmbService->accounts_locations->patch($name,$location,$params);
        $this->_debug($gmb);
    }
    */
    /*
    // 営業時間の更新
    public function registLocations($gmbService, $gmbApiService)
    { 
        $gmbAccountId = "114708824399692680522";
        $gmbLocationId = "4217405982519266446";
        $name = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        echo $name;
        
        $specialPeriodAry = array();
        $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(20);
        $specialHourPeriod->setStartDate($datePeriod);
        $specialHourPeriod->setOpenTime("13:00");

        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(20);
        $specialHourPeriod->setEndDate($datePeriod);
        $specialHourPeriod->setCloseTime("17:00");

        $specialHourPeriod->setIsClosed(false);
        #$specialHourPeriod->setIsClosed(true);  # true:定休日

        $specialPeriodAry[] = $specialHourPeriod;


        $specialHours = new Google_Service_MyBusiness_SpecialHours();
        $specialHours->setSpecialHourPeriods($specialPeriodAry); 

        $location = new Google_Service_MyBusiness_Location(); 
        $location->setName($name); 
        $location->setSpecialHours($specialHours);  
        $params=['updateMask'=>"specialHours"];

        $gmb = $gmbService->accounts_locations->patch($name,$location,$params);
        $this->_debug($gmb);

    }
    */

    // 営業時間の更新
    /*
    public function registLocations($gmbService, $gmbApiService)
    { 
    //    $file = fopen("/var/www/phplaravel/app/Services/焼肉きんぐ2.csv", "r");
    //    if($file){
    //     while ($line = fgets($file)) {
    //        echo $line;
    //      }
    //    }
    //    fclose($file);

        $gmbAccountId = "114708824399692680522";
        $gmbLocationId = "4217405982519266446";
        $name = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        echo $name;
        
        $specialPeriodAry = array();

        # 2021.01.21 13:00 - 17:00
        $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(21);
        $specialHourPeriod->setStartDate($datePeriod);
        $specialHourPeriod->setOpenTime("13:00");

        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(21);
        $specialHourPeriod->setEndDate($datePeriod);
        $specialHourPeriod->setCloseTime("17:00");

        #$specialHourPeriod->setIsClosed(false);
        $specialHourPeriod->setIsClosed(true);  # true:定休日
        $specialPeriodAry[] = $specialHourPeriod;

        # 2021.01.21 18:00 - 20:00
        $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(21);
        $specialHourPeriod->setStartDate($datePeriod);
        $specialHourPeriod->setOpenTime("18:00");

        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(21);
        $specialHourPeriod->setEndDate($datePeriod);
        $specialHourPeriod->setCloseTime("20:00");

        #$specialHourPeriod->setIsClosed(false);
        $specialHourPeriod->setIsClosed(true);  # true:定休日
        $specialPeriodAry[] = $specialHourPeriod;



        # 2021.01.22 13:00 - 17:00
        $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(22);
        $specialHourPeriod->setStartDate($datePeriod);
        $specialHourPeriod->setOpenTime("20:00");

        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(22);
        $specialHourPeriod->setEndDate($datePeriod);
        $specialHourPeriod->setCloseTime("23:00");

        #$specialHourPeriod->setIsClosed(false);
        $specialHourPeriod->setIsClosed(true);  # true:定休日
        $specialPeriodAry[] = $specialHourPeriod;

        # 2021.01.22 18:00 - 20:00
        $specialHourPeriod = new Google_Service_MyBusiness_SpecialHourPeriod();
        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(22);
        $specialHourPeriod->setStartDate($datePeriod);
        $specialHourPeriod->setOpenTime("08:00");

        $datePeriod = new Google_Service_MyBusiness_Date();
        $datePeriod->setYear(2021);
        $datePeriod->setMonth(1);
        $datePeriod->setDay(22);
        $specialHourPeriod->setEndDate($datePeriod);
        $specialHourPeriod->setCloseTime("12:00");

        #$specialHourPeriod->setIsClosed(false);
        $specialHourPeriod->setIsClosed(true);  # true:定休日
        $specialPeriodAry[] = $specialHourPeriod;




        $specialHours = new Google_Service_MyBusiness_SpecialHours();
        $specialHours->setSpecialHourPeriods($specialPeriodAry); 
        $location = new Google_Service_MyBusiness_Location(); 
        $location->setName($name); 
        $location->setSpecialHours($specialHours);  
        $params=['updateMask'=>"specialHours"];

        $gmb = $gmbService->accounts_locations->patch($name,$location,$params);
        $this->_debug($gmb);

    }
    */
    
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
