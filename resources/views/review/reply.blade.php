@extends('layouts.app')
@section('title', 'クチコミ対応 返信' . (isset($reply->review_reply_id) ? '編集・削除' : ''))
@section('head')
    @include('components.include-emoji')
    <script type="text/javascript" src="{{mix('js/review_reply.js')}}" defer></script>
    <script>window.Promise || document.write('<script src="https://www.promisejs.org/polyfills/promise-7.0.4.min.js"><\/script>');</script>
@endsection
@section('content')
    <div class="col-md-8 order-md-1">
        <h4 class="mb-4">クチコミ対応 返信{{isset($reply->review_reply_id) ? '編集・削除' : ''}}</h4>
        <form method="post" action="">
            @csrf
            <input type="hidden" name="review_reply_id" value="{{$reply->review_reply_id}}">
            <input type="hidden" name="review_id" value="{{$review->review_id}}">
            <input type="hidden" name="gmb_review_reply_comment" value="{{$review->gmb_review_reply_comment}}">
            <input type="hidden" id="is_deleted" name="is_deleted" value="{{config('const.FLG_OFF')}}">
            <input type="hidden" name="sync_type" value="{{$review->sync_type}}">
            <input type="hidden" name="sync_status" value="{{$review->sync_status}}">
            <input type="hidden" name="account_id" value="{{$review->location->account_id}}">
            <input type="hidden" name="is_scheduled" value="">
            <div class="mb-4">
                <label for="sync_status" class="d-block font-weight-bold">現在のステータス</label>
                @if (is_null($review->gmb_review_reply_comment) || $review->gmb_review_reply_comment == '') 未返信 @else 返信済み @endif・
                @if (isset($reply->review_reply_id))
                    {{App\Enums\SyncStatusType::getString($reply->sync_status)}}
                    @if (App\Enums\SyncStatusType::getValue($reply->sync_status) == 2)
                        ({{$reply->scheduled_sync_time}})
                    @endif
                @else
                    {{App\Enums\SyncStatusType::getString($review->sync_status)}}
                    @if (App\Enums\SyncStatusType::getValue($review->sync_status) == 2)
                        ({{$review->scheduled_sync_time}})
                    @endif
                @endif
            </div>
            <div class="mb-4">
                <label for="gmb_create_time" class="d-block font-weight-bold">クチコミ日時</label>
                {{$review->gmb_create_time}}
            </div>
            <div class="mb-4">
                <label for="gmb_reviewer_display_name" class="d-block font-weight-bold">クチコミユーザ名</label>
                {{$review->gmb_reviewer_display_name}}
            </div>
            <div class="mb-4">
                <label for="gmb_star_rating" class="d-block font-weight-bold">クチコミ評点</label>
                {{mb_str_pad(intval(App\Enums\StarRatingType::getValue($review->gmb_star_rating)), config('const.ACTIVE_RATE_STRING'), config('const.RATE_LIMIT'), config('const.INACTIVE_RATE_STRING'))}}
            </div>
            <div class="mb-4">
                <label for="gmb_comment_review" class="d-block font-weight-bold">クチコミ</label>
                {!! nl2br(e($review->gmb_comment)) !!}
            </div>
            <div class="mb-4">
                <label for="template" class="font-weight-bold">テンプレート選択</label>
                {{Form::select('template', $templates, old('template'), ['id' => 'template', 'onChange' => 'getTemplate()', 'class' => 'form-control', 'placeholder' => '選択してください'])}}
            </div>
            <div class="mb-4">
                <label for="gmb_comment_reply" class="font-weight-bold">返信内容</label>
                <div class="emoji-picker-container resizable">
                    {{Form::textarea('gmb_comment', old('gmb_comment', (!empty($reply->gmb_comment)) ? $reply->gmb_comment : ''),
                    ['rows' => 8, 'id' => 'gmb_comment', 'class' => ['form-control', ($errors->has('gmb_comment')) ? 'is-invalid' : ''], 'data-emojiable' => 'true', 'data-emoji-input' => 'unicode'])}}
                    @if ($errors->has('gmb_comment'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{$errors->first('gmb_comment')}}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="mb-4">
                <label for="scheduled" class="font-weight-bold">返信日時</label>
                <div class="row">
                    <div class="col-3">
                        <span>返信日:</span>
                        {{Form::text('scheduled_sync_time', old('scheduled_sync_time', $reply->scheduled_sync_time ? Carbon\Carbon::parse($reply->scheduled_sync_time)->format('Y-m-d') : ''), ['id' => 'scheduled_sync_time', 'class' => ['form-control', ($errors->has('scheduled_sync_time')) ? 'is-invalid' : ''], 'maxlength' => 10])}}
                        @if ($errors->has('scheduled_sync_time'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{$errors->first('scheduled_sync_time')}}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-4">
                        <span>時間:</span>
                        {{Form::select('scheduled_range', config('formConst.REPLY_TIME_RANGE'), old('scheduled_range', $reply->scheduled_sync_time ? Carbon\Carbon::parse($reply->scheduled_sync_time)->format('H:i') : ''), ['id' => 'scheduled_range', 'class' => ['form-control', ($errors->has('scheduled_range')) ? 'is-invalid' : ''], 'placeholder' => '選択してください'])}}
                        @if ($errors->has('scheduled_range'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{$errors->first('scheduled_range')}}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="row justify-content-end">
                    <div class="m-3">
                        <button type="submit" name="request_type" class="btn btn-primary{{!Gate::allows('edit-review') ? ' disabled' : ''}}" formaction="@if (isset($reply->review_reply_id)) {{action('ReviewController@update')}} @else {{action('ReviewController@store')}} @endif" value="1" onclick="return confirm('下書き保存してよろしいですか？')" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>
                            下書き
                        </button>
                    </div>
                    <div class="m-3">
                        <button type="submit" name="request_type" class="btn btn-primary{{!Gate::allows('edit-review') ? ' disabled' : ''}}" formaction="@if (isset($reply->review_reply_id)) {{action('ReviewController@update')}} @else {{action('ReviewController@store')}} @endif" value="2" onClick="return postConfirm();" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>
                            最短返信
                        </button>
                    </div>
                    <div class="m-3">
                        <button type="submit" name="request_type" class="btn btn-primary{{!Gate::allows('edit-review') ? ' disabled' : ''}}" formaction="@if (isset($reply->review_reply_id)) {{action('ReviewController@update')}} @else {{action('ReviewController@store')}} @endif" value="3" onClick="return postConfirm();" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>
                            予約返信
                        </button>
                    </div>
                    @if (isset($reply->review_reply_id))
                        <div class="m-3">
                            <button type="submit" name="request_type" class="btn btn-danger{{!Gate::allows('edit-review') ? ' disabled' : ''}}" formaction="{{action('ReviewController@delete')}}" value="4" onClick="return deleteConfirm();" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>
                                @if (App\Enums\SyncStatusType::getValue($reply->sync_status) == 1) 下書き削除 @else 返信削除 @endif
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection