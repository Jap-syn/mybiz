@extends('layouts.app')
@section('title', '投稿管理')

@section('head')
<link href="{{ mix('css/localpost.css') }}" rel="stylesheet">
<script>
  TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
</script>
<script type="text/javascript" src="{{ mix('js/localpost_index.js') }}" defer></script>
@endsection

@section('content')
<h3>投稿管理</h3>
<div class="row">
    <div class="col-3">
        <div class="row m-3">
            <button class="btn btn-primary btn-block{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}" onclick="location.href='/localpost/create'" {{!Gate::allows('edit-localpost') ? 'aria-distabled="true" disabled' : ''}}>　新規投稿の作成　</button>
        </div>
    </div>
</div>
<form action="{{url('/localpost')}}" method="get" name="localpost">
  <div class="row m-3">
    <div class="col-4">
      <label for="account">ブランド</label>
      {{Form::select('account', $params['accounts'], request()->input('account'), ['id'=>'account', 'class'=>'mb-3 form-control'])}}
    </div>
    <div class="col-4">
      <label for="localpost_st_date">投稿反映日(始)</label>
      <input id="localpostStDate" type="text"
        class="mb-3 form-control flatpickr{{ $errors->has('stDate') ? ' is-invalid' : ''}}" name="stDate"
        value="{{ request()->input('stDate') }}" data-default-date="{{ request()->input('stDate') }}">
      @if ($errors->has('stDate'))
      <span class="invalid-feedback" role="alert">
        <strong>{{ $errors->first('stDate') }}</strong>
      </span>
      @endif
    </div>
    <div class="col-4">
      <label for="localpost_end_date">投稿反映日(終)</label>
      <input id="localpostEndDate" type="text"
        class="form-control flatpickr{{ $errors->has('endDate') ? ' is-invalid' : ''}}" name="endDate"
        value="{{ request()->input('endDate') }}" data-default-date="{{ request()->input('endDate') }}">
      @if ($errors->has('endDate'))
      <span class="invalid-feedback" role="alert">
        <strong>{{ $errors->first('endDate') }}</strong>
      </span>
      @endif
    </div>
    <div class="col-4"></div>
  </div>
  <div class="row m-3">
    <div class="col-4">
      <label for="gmb_topic_type">投稿種類</label>
      {{Form::select('gmb_topic_type', config('formConst.GMB_TOPIC_TYPE_WITHOUT_UNSPECIFIED'), request()->input('gmb_topic_type'), ['id' => 'localpost_gmb_topic_type', 'class' => 'mb-3 form-control','placeholder' => 'すべて'])}}
    </div>
    <div class="col-4">
      <label for="gmb_action_type">アクション</label>
      {{Form::select('gmb_action_type', config('formConst.GMB_ACTION_TYPE'), request()->input('gmb_action_type'), ['id' => 'localpost_gmb_action_type', 'class' => 'form-control','placeholder' => '選択してください'])}}
    </div>
    <div class="col-4">
      <label for="sync_status">ステータス</label>
      {{Form::select('sync_status', config('formConst.LOCAL_POST_SYNC_STATUS'), request()->input('sync_status'), ['id' => 'localpost_sync_status', 'class' => 'form-control','placeholder' => '選択してください'])}}
    </div>
  </div>
</form>
<div class="row m-3">
  <div class="col-3 m-3 ml-auto">
    <button class="btn btn-success btn-block" type="button" onclick="document.localpost.submit();">検索</button>
  </div>
  <div class="col-3 m-3">
    <form action="{{url('/localpost/export')}}" method="post" onSubmit="exportPrepare()">
      @csrf
      <input type="hidden" id="exportAccount" name="account" value="">
      <input type="hidden" id="exportStDate" name="stDate" value="">
      <input type="hidden" id="exportEndDate" name="endDate" value="">
      <input type="hidden" id="exportGmbTopicType" name="gmb_topic_type" value="">
      <input type="hidden" id="exportGmbActionType" name="gmb_action_type" value="">
      <input type="hidden" id="exportSyncStatus" name="sync_status" value="">
      <button class="btn btn-primary btn-block" type="submit">エクスポート</button>
    </form>
  </div>
</div>
<div class="table-responsive">
  <table class="table table-striped table-sm index-table">
    <thead>
      <tr>
        <th class='col-id'>ID</th>
        <th class='col-image'>イメージ</th>
        <th class='col-title'>ブランド</th>
        <th class='col-date'>投稿反映日</th>
        <th class='col-topic-type'>投稿種類</th>
        <th class='col-title'>タイトル</th>
        <th class='col-summary'>詳細</th>
        <th class='col-date'>イベント開始</th>
        <th class='col-date'>イベント終了</th>
        <th class='col-date'>ステータス</th>
        <th class='col-control'></th>
      </tr>
    </thead>
    <tbody>
      @forelse($localPostGroups as $localPostGroup)
      <tr>
        <td class='col-id'><a
            href="{{action('LocalPostGroupController@edit', ['localPostGroupId' => $localPostGroup->id])}}">
            {{$localPostGroup->id}}</a>
        </td>
        <td class='col-image'>
          @if($localPostGroup->mediaItems()->first())
            <img class='w-100' src="{{$localPostGroup->mediaItems()->first()->getThumbnailImageUrl()}}"/>
          @else
            <img class='w-100' src="{{ asset('img/no_image.png') }}"/>
          @endif
        </td>
        <td class='col-title'>
          {{Str::limit(optional($localPostGroup->account)->gmb_account_name, config('const.LOCAL_POST_TITLE_LIMIT'), "...") }}
        </td>
        <td class='col-date'>
          {{ optional($localPostGroup->localPosts()->first())->sync_time ? \Carbon\Carbon::parse(optional($localPostGroup->localPosts()->first())->sync_time)->tz(config('app.timezone'))->format(config('formConst.FORMAT_DATETIME_YMDHI')) : '-' }}
        </td>
        <td class='col-topic-type'>{{ config('formConst.GMB_TOPIC_TYPE')[$localPostGroup->topic_type] ?? ""}}</td>
        <td class='col-title'>
          {{Str::limit($localPostGroup->event_title, config('const.LOCAL_POST_TITLE_LIMIT'), "...") }}</td>
        <td class='col-summary'>
          {{Str::limit(optional($localPostGroup->localPosts()->first())->gmb_summary, config('const.LOCAL_POST_SUMMARY_LIMIT'), "...") }}
        </td>
        <td class='col-date'>
          {{ $localPostGroup->getEventStartDateTimeString('-') }}
        </td>
        <td class='col-date'>
          {{ $localPostGroup->getEventEndDateTimeString('-') }}
        </td>
        <td class='col-topic-type'>
          {{ config('formConst.LOCAL_POST_SYNC_STATUS')[optional($localPostGroup->localPosts()->first())->sync_status] ?? ""}}
        </td>
        <td class='col-control'>
          <a href="{{action('LocalPostGroupController@edit', ['localPostGroupId' => $localPostGroup->id])}}">
            <button class="btn btn-{{ $localPostGroup->isEditable() ? 'primary' : 'secondary' }}">
              {{ $localPostGroup->isEditable() ? '修正' : '表示' }}</button>
          </a>
          <form action="{{action('LocalPostGroupController@destroy', ['localPostGroupId' => $localPostGroup->id])}}"
            method="post" name="delete-localpost" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger delete-localpost{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}" {{!Gate::allows('edit-localpost') ? 'aria-distabled="true" disabled' : ''}}>削除</button>
          </form>
          </a>
        </td>
      </tr>
      @empty
      @endforelse
    </tbody>
  </table>
  <div class='col-md-4 mx-auto'>
    {{ $localPostGroups->appends($params)->links() }}<span>{{ $localPostGroups->firstItem() }} ～
      {{ $localPostGroups->lastItem() }} 件 / 全
      {{ $localPostGroups->total() }} 件</span>
  </div>
</div>
@endsection