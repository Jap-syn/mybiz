<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;

class HelperTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testGetNextSyncDateTime()
    {
        Carbon::setTestNow('2020/08/08 07:50:04');

        $this->assertEquals(new Carbon('2020/08/08 09:00:00'), getNextSyncDateTime(new Carbon()));

        $current_datetime = new Carbon('2020/08/08 08:46:51');
        $this->assertEquals(new Carbon('2020/08/08 09:00:00'), getNextSyncDateTime($current_datetime));

        // 日付をまたぐ
        $current_datetime = new Carbon('2020/08/08 23:46:51');
        $this->assertEquals(new Carbon('2020/08/09 00:00:00'), getNextSyncDateTime($current_datetime));

        // 過去日
        $current_datetime = new Carbon('2020/08/07 23:46:51');
        $this->assertEquals(null, getNextSyncDateTime($current_datetime));

        // 境界
        $this->assertEquals(new Carbon('2020/08/08 12:00:00'), getNextSyncDateTime(new Carbon('2020/08/08 09:00:00')));
        $this->assertEquals(new Carbon('2020/08/08 15:00:00'), getNextSyncDateTime(new Carbon('2020/08/08 12:00:00')));
        $this->assertEquals(new Carbon('2020/08/09 00:00:00'), getNextSyncDateTime(new Carbon('2020/08/08 21:00:00')));
        $this->assertEquals(new Carbon('2020/08/09 03:00:00'), getNextSyncDateTime(new Carbon('2020/08/09 00:00:00')));
        $this->assertEquals(new Carbon('2020/08/10 03:00:00'), getNextSyncDateTime(new Carbon('2020/08/09 24:00:00')));

        // 現在ちょうど
        Carbon::setTestNow('2020/08/08 09:00:00');
        $this->assertEquals(new Carbon('2020/08/08 12:00:00'), getNextSyncDateTime(new Carbon()));
    }
}
