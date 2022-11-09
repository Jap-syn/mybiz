@extends('layouts.app')
@section('title', 'Dashboard')
@section('head')
    <link href="{{ mix('css/dashboard.css') }}" rel="stylesheet">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="{{ mix('js/chart/chartjs.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/chart/chart.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/dashboard.js') }}" defer></script>
    <script>window.Promise || document.write('<script src="https://www.promisejs.org/polyfills/promise-7.0.4.min.js"><\/script>');</script>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="float-left">
                <div class="form-inline">
                    <div class="form-group" id="titles">
                        <h3>Dashboard</h3>
                    </div>
                </div>
            </div>
            <div class="float-right">
                <div class="form-inline">
                    <div class="form-group" id="scheduled_times">
                        <label for="update_scheduled_time">毎日{{config('const.DASHBOARD_UPDATE_SCHEDULED_TIME')}}時更新（{{config('const.DASHBOARD_DATE_RANGE_INTERVAL')}}日前データから取得可）</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12 clearfix">
            <div class="float-left">
                <div class="form-inline">
                    <div class="form-group" id="accounts">
                        <label for="account">ブランド：</label>
                        {{Form::select('account', $accounts, request()->input('account'), ['id'=>'account', 'onChange'=>'getReports()', 'class'=>'form-control'])}}
                    </div>
                </div>
            </div>
            <div class="float-right">
                <div class="form-inline">
                    <div class="form-group" id="dateRanges">
                        <label for="date_range">期間指定：</label>
                        {{Form::select('dateRange', config('formConst.DASHBOARD_DATE_RANGE'), request()->input('dateRange'), ['id' => 'dateRange', 'class' => 'form-control'])}}
                        {{Form::hidden('period', request()->input('period'), ['id' => 'period'])}}
                        &nbsp;&nbsp;
                        <input type="text" class="form-control" id="startDate" name="startDate" value="{{request()->input('startDate')}}">
                        <span>&nbsp;&nbsp;～&nbsp;&nbsp;</span>
                        <input type="text" class="form-control" id="endDate" name="endDate" value="{{request()->input('endDate')}}">
                        {{Form::hidden('dateRangeInterval', config('const.DASHBOARD_DATE_RANGE_INTERVAL'), ['id' => 'dateRangeInterval'])}}
                        {{Form::hidden('dateRangeMinDate', config('const.DASHBOARD_DATE_RANGE_MINDATE'), ['id' => 'dateRangeMinDate'])}}
                        {{Form::hidden('dateRangeMaxDate', config('const.DASHBOARD_DATE_RANGE_MAXDATE'), ['id' => 'dateRangeMaxDate'])}}
                        &nbsp;&nbsp;
                        <button type="button" class="btn btn-secondary form-control" onClick="getReports()">更新</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="float-right">
                <div class="form-inline">
                    <div class="form-group" id="displayDateRanges">
                        <p><label for="display_date_range">表示期間：</label></p>
                        <p id="dateRangeStartDate">{{$tiles['dateRangeStartDate']}}</p>
                        <p>&nbsp;～&nbsp;</p>
                        <p id="dateRangeEndDate">{{$tiles['dateRangeEndDate']}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 col-md-4">
                            <i class="fa fa-search fa-4x text-primary"></i>
                        </div>
                        <div class="col-8 col-md-8">
                            <div class="numbers">
                                <p class="card-category">合計検索数<br>(全店舗)</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="numbers">
                                <p class="card-title" id="totalSearchCount">{{$tiles['totalSearchCount']}}<p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i>&nbsp;Updated&nbsp;<span id="totalSearchCountUpdatedDate">{{$tiles['locationReportUpdatedDate']}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 col-md-4">
                            <i class="fa fa-area-chart fa-4x text-warning"></i>
                        </div>
                        <div class="col-8 col-md-8">
                            <div class="numbers">
                                <p class="card-category">合計検索数<br>(全店舗平均)</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="numbers">
                                <p class="card-title" id="totalSearchCountAverage">{{$tiles['totalSearchCountAverage']}}<p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i>&nbsp;Updated&nbsp;<span id="totalSearchCountAverageUpdatedDate">{{$tiles['locationReportUpdatedDate']}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 col-md-4">
                            <i class="fa fa-send fa-4x text-success"></i>
                        </div>
                        <div class="col-8 col-md-8">
                            <div class="numbers">
                                <p class="card-category">口コミ数<br>(全店舗)</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="numbers">
                                <p class="card-title" id="totalReviewCount">{{$tiles['totalReviewCount']}}<p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i>&nbsp;Updated&nbsp;<span id="totalReviewCountUpdatedDate">{{$tiles['reviewUpdatedDate']}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 col-md-4">
                            <i class="fa fa-reply fa-4x text-danger"></i>
                        </div>
                        <div class="col-8 col-md-8">
                            <div class="numbers">
                                <p class="card-category">未返信数<br>(全店舗)</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="numbers">
                                <p class="card-title" id="totalReviewCountUnreplied">{{$tiles['totalReviewCountUnreplied']}}<p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i>&nbsp;Updated&nbsp;<span id="totalReviewCountUnrepliedUpdatedDate">{{$tiles['replyUpdatedDate']}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">検索数推移</h5>
                    <div class="float-left">
                        <div class="form-inline">
                            <div class="form-group" id="chartTypes">
                                <label for="chartType">グラフ種類：</label>
                                {{Form::select('chartType', config('formConst.DASHBOARD_CHART_TYPE'), request()->input('chartType'), ['id'=>'chartType', 'onChange'=>'getCharts()', 'class'=>'form-control'])}}
                            </div>
                        </div>
                    </div>
                    <div class="float-right">
                        <div class="form-inline">
                            <div class="form-group" id="export">
                                <form action="{{action('HomeController@export')}}" method="post" onSubmit="exportPrepare()">
                                    @csrf
                                    <input type="hidden" id="exportAccount" name="account" value="">
                                    <input type="hidden" id="exportDateRange" name="dateRange" value="">
                                    <input type="hidden" id="exportStartDate" name="startDate" value="">
                                    <input type="hidden" id="exportEndDate" name="endDate" value="">
                                    <input type="hidden" id="exportChartType" name="chartType" value="">
                                    <button type="submit" class="btn btn-primary form-control">エクスポート</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartOfReport"></canvas>
                </div>
                <div class="card-footer">
                    <div class="chart-legend" id="chart-legend"></div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i>&nbsp;Updated&nbsp;<span id="locationReportUpdatedDate">{{$tiles['locationReportUpdatedDate']}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card strpied-tabled-with-hover">
                <div class="card-header">
                    <h5 class="card-title">店舗別一覧</h5>
                </div>
                <div class="card-body table-full-width table-responsive">
                    <table id="locationList" class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th class="col-name">店舗名&nbsp;<span class="icon-sort"></span></th>
                                <th>合計検索数&nbsp;<span class="icon-sort"></span></th>
                                <th>直接検索数&nbsp;<span class="icon-sort"></span></th>
                                <th>間接検索数&nbsp;<span class="icon-sort"></span></th>
                                <th>ブランド検索数&nbsp;<span class="icon-sort"></span></th>
                                <th>アクション数&nbsp;<span class="icon-sort"></span></th>
                                <th class="col-rating">口コミ総合評価&nbsp;※&nbsp;<span class="icon-sort"></span></th>
                                <th>口コミ数&nbsp;<span class="icon-sort"></span></th>
                                <th>未返信数&nbsp;<span class="icon-sort"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lists as $list)
                                <tr>
                                    <td class="col-name" data-value="{{$list['location_id']}}">{{$list['location_name']}}</td>
                                    <td>{{$list['location_search_count_total']}}</td>
                                    <td>{{$list['location_search_count_direct']}}</td>
                                    <td>{{$list['location_search_count_indirect']}}</td>
                                    <td>{{$list['location_search_count_chain']}}</td>
                                    <td>{{$list['location_actions_count']}}</td>
                                    <td class="col-rating">{{$list['location_average_rating']}}</td>
                                    <td>{{$list['location_review_count']}}</td>
                                    <td>{{$list['location_review_count_unreplied']}}</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">※口コミ総合評価は期間指定に関わらず、過去の全ての口コミレビューの平均値です。</div>
            </div>
        </div>
    </div>
@endsection