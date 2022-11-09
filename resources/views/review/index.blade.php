@extends('layouts.app')
@section('title', 'クチコミ管理')
@section('head')
    <link href="{{mix('css/review.css')}}" rel="stylesheet">
    <script type="text/javascript" src="{{mix('js/review.js')}}" defer></script>
    <script>window.Promise || document.write('<script src="https://www.promisejs.org/polyfills/promise-7.0.4.min.js"><\/script>');</script>
@endsection
@section('content')
    <h3>クチコミ管理</h3>
    <form method="get" name="review" action="{{action('ReviewController@index')}}">
        <div class="row m-3">
            <div class="col-5">
                <label for="review_account">ブランド</label>
                {{Form::select('account', $accounts, request()->input('account'), ['id' => 'reviewAccount', 'onChange' => 'getLocations()', 'class' => 'mb-3 form-control', 'placeholder' => 'すべて'])}}
                <label for="review_location">店舗</label>
                <?php $locations = array_flip($locations); ?>
                {{Form::select('location', $locations, request()->input('location'), ['id' => 'reviewLocation', 'class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
                <label for="review_st_date">クチコミ登録日(始)</label>
                <input type="text" id="reviewStDate" class="mb-3 form-control flatpickr{{$errors->has('stDate') ? ' is-invalid' : ''}}" name="stDate" value="{{request()->input('stDate')}}" data-default-date="{{request()->input('stDate')}}">
                @if ($errors->has('stDate'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{$errors->first('stDate')}}</strong>
                    </span>
                @endif
                <label for="review_end_date">クチコミ登録日(終)</label>
                <input type="text" id="reviewEndDate" class="mb-3 form-control flatpickr{{$errors->has('endDate') ? ' is-invalid' : ''}}" name="endDate" value="{{request()->input('endDate')}}" data-default-date="{{request()->input('endDate')}}">
                @if ($errors->has('endDate'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{$errors->first('endDate')}}</strong>
                    </span>
                @endif
                {{Form::hidden('period', request()->input('period'), ['id' => 'period'])}}
            </div>
            <div class="col-5">
                <label for="rate">クチコミ評点</label>
                {{Form::select('rate', config('formConst.rate'), request()->input('rate'), ['id' => 'reviewRate', 'class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
                <label for="reply_status">返信ステータス</label>
                {{Form::select('replyStatus', config('formConst.replyStatus'), request()->input('replyStatus'), ['id' => 'reviewReplyStatus', 'class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
                <label for="sync_status">同期ステータス</label>
                {{Form::select('syncStatus', config('formConst.syncStatus'), request()->input('syncStatus'), ['id' => 'reviewSyncStatus', 'class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
            </div>
        </div>
    </form>
    <div class="row justify-content-end">
        <div class="col-3 m-3">
            <button type="button" class="btn btn-success btn-block" onClick="document.review.submit();">検索</button>
        </div>
        <div class="col-3 m-3">
            <form method="post" action="{{action('ReviewController@export')}}" onSubmit="exportPrepare();">
                @csrf
                <input type="hidden" id="exportAccount" name="account"  value="">
                <input type="hidden" id="exportLocation" name="location"  value="">
                <input type="hidden" id="exportStDate" name="stDate"  value="">
                <input type="hidden" id="exportEndDate" name="endDate" value="">
                <input type="hidden" id="exportRate" name="rate"  value="">
                <input type="hidden" id="exportReplyStatus" name="replyStatus" value="">
                <input type="hidden" id="exportSyncStatus" name="syncStatus" value="">
                <button type="submit" class="btn btn-primary btn-block">エクスポート</button>
            </form>
        </div>
        <div class="col-3 m-3">
        {{Form::select('reviewAutoReplied', config('formConst.reviewAutoReplied'), $is_autoreplied, ['id' => 'reviewAutoReplied', 'onChange' => 'setReviewAutoreplied()', 'class' => 'mb-3 form-control'])}}
        </div>
    </div>
    <div class="row m-3">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-date">日付</th>
                        <th class="col-name">店舗名</th>
                        <th class="col-rating">クチコミ評点</th>
                        <th class="col-name">ユーザ名</th>
                        <th class="col-comment">タイトル</th>
                        <th class="col-status">返信ステータス</th>
                        <th class="col-status-date">同期ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td class="col-id"><a href="@if (isset($review->review_reply_id)) {{action('ReviewController@edit', ['reviewId' => $review->review_id])}} @else {{action('ReviewController@create', ['reviewId' => $review->review_id])}} @endif">{{$review->review_id}}</a></td>
                            <td class="col-date">{{Carbon\Carbon::parse($review->gmb_create_time)->format('Y-m-d')}}</td>
                            <td class="col-name">{{trim(mb_convert_kana($review->gmb_location_name, 's', 'UTF-8'))}}</td>
                            <td class="col-rating">{{mb_str_pad(intval(App\Enums\StarRatingType::getValue($review->gmb_star_rating)), config('const.ACTIVE_RATE_STRING'), config('const.RATE_LIMIT'), config('const.INACTIVE_RATE_STRING'))}}</td>
                            <td class="col-name">{{$review->gmb_reviewer_display_name}}</td>
                            <td class="col-comment">{{Str::limit($review->gmb_comment, config('const.REVIEW_COMMENT_LIMIT'), "...")}}</td>
                            <td class="col-status">@if (is_null($review->gmb_review_reply_comment) || $review->gmb_review_reply_comment == '') 未 @else 済 @endif</td>
                            <td class="col-status-date">
                                @if (isset($review->review_reply_id))
                                    {{App\Enums\SyncStatusType::getString($review->reply_sync_status)}}
                                    @if (App\Enums\SyncStatusType::getValue($review->reply_sync_status) == 2) <br>({{$review->reply_scheduled_sync_time}}) @endif
                                @else
                                    {{App\Enums\SyncStatusType::getString($review->review_sync_status)}}
                                    @if (App\Enums\SyncStatusType::getValue($review->review_sync_status) == 2) <br>({{$review->review_scheduled_sync_time}}) @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
            <div class="col-md-4 mx-auto">
                {{$reviews->appends(request()->input())->links()}}
                <span>
                    @if (is_null($reviews->firstItem())) 0 @else {{$reviews->firstItem()}} @endif ～
                    @if (is_null($reviews->lastItem())) 0 @else {{$reviews->lastItem()}} @endif 件&nbsp;/&nbsp;全
                    {{$reviews->total()}} 件
                </span>
            </div>
        </div>
    </div>
@endsection