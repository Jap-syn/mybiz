@extends('layouts.app')
@section('title', '店舗管理')

@section('head')
<link href="{{ mix('css/location.css') }}" rel="stylesheet">
<script>
  TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
</script>
<script type="text/javascript" src="{{ mix('js/location_index.js') }}" defer></script>
<script>
  let edit_url = "{{ action('LocationController@edit', ['locationId' => '||location_id||']) }}";
</script>
@endsection

@section('content')
<div class="m-3">
  <div>現在、店舗情報管理機能はβ版ではご利用頂けません。</div>
  <div class="mb-3">個別サポートプランのご契約をご検討される場合は下記までお問い合わせください。</div>
  <div>（株）ParaWorks</div>
  <div>マイビジチェーンサポート</div>
  <div><a href="mailto:info@paraworks.jp">info@paraworks.jp</a></div>
</div>
{{--<h3>店舗管理</h3>--}}
{{--<form action="{{url('/location')}}" method="get" name="searchform">--}}
{{--  <div class="row m-3">--}}
{{--      <div class="col-5 m-3">--}}
{{--      <label for="location_st_date">投稿日(始)</label>--}}
{{--      <input id="locationStDate" type="text"--}}
{{--        class="mb-3 form-control flatpickr{{ $errors->has('stDate') ? ' is-invalid' : ''}}" name="stDate"--}}
{{--  value="{{ request()->input('stDate') }}" data-default-date="{{ request()->input('stDate') }}" >--}}
{{--  @if ($errors->has('stDate'))--}}
{{--  <span class="invalid-feedback" role="alert">--}}
{{--    <strong>{{ $errors->first('stDate') }}</strong>--}}
{{--  </span>--}}
{{--  @endif--}}
{{--  <label for="location_end_date">投稿日(終)</label>--}}
{{--  <input id="locationEndDate" type="text"--}}
{{--    class="form-control flatpickr{{ $errors->has('endDate') ? ' is-invalid' : ''}}" name="endDate"--}}
{{--    value="{{ request()->input('endDate') }}" data-default-date="{{ request()->input('endDate') }}">--}}
{{--  @if ($errors->has('endDate'))--}}
{{--  <span class="invalid-feedback" role="alert">--}}
{{--    <strong>{{ $errors->first('endDate') }}</strong>--}}
{{--  </span>--}}
{{--  @endif--}}
{{--  </div>--}}
{{--  <div class="row m-3">--}}
{{--    <div class="col-5 mx-3">--}}
{{--      <label for="account_id">アカウント</label>--}}
{{--      {{Form::select('account_id', $accounts, request()->input('account_id'), ['id' => 'location_account_id', 'class' => 'mb-3 form-control','placeholder' => '選択してください'])}}--}}
{{--    </div>--}}
{{--    <div class="col-5 mx-3">--}}
{{--      <label for="gmb_main_category">カテゴリー</label>--}}
{{--      {{Form::select('gmb_main_category', config('formConst.GMB_ACTION_TYPE'), request()->input('gmb_action_type'), ['id' => 'location_gmb_main_category', 'class' => 'form-control','placeholder' => '選択してください'])}}--}}
{{--    </div>--}}
{{--  </div>--}}
{{--  <div class="row mx-3">--}}
{{--    <div class="col-5 mx-3">--}}
{{--      <label for="attribute_id">属性</label>--}}
{{--      {{Form::select('attribute_id', $attributes, request()->input('attribute_id'), ['id' => 'location_gmb_attribute', 'class' => 'form-control','placeholder' => '選択してください'])}}--}}
{{--    </div>--}}
{{--    <div class="col-5 mx-3">--}}
{{--      <label for="keyword">キーワード</label>--}}
{{--      {{Form::text('keyword', old('keyword', request()->keyword), ['id' => 'keyword', 'class' => ['form-control']])}}--}}
{{--    </div>--}}
{{--  </div>--}}
{{--</form>--}}
{{--<div class="row m-3">--}}
{{--  <div class="col-3 m-3 ml-auto">--}}
{{--    <form action="{{url('/location/export')}}" method="post" onSubmit="exportPrepare()">--}}
{{--    @csrf--}}
{{--    <input type="hidden" id="exportStDate" name="stDate" value="">--}}
{{--    <input type="hidden" id="exportEndDate" name="endDate" value="">--}}
{{--    <input type="hidden" id="exportGmbTopicType" name="account_id" value="">--}}
{{--    <input type="hidden" id="exportGmbActionType" name="gmb_action_type" value="">--}}
{{--    <button class="btn btn-primary btn-block" type="submit">エクスポート</button>--}}
{{--    </form>--}}
{{--  </div>--}}
{{--  <div class="col-3 m-3">--}}
{{--    <button class="btn btn-success btn-block" type="button" onclick="document.searchform.submit();">検索</button>--}}
{{--  </div>--}}
{{--</div>--}}
{{--<div class="table-responsive">--}}
{{--  <table class="table table-striped table-sm index-table">--}}
{{--    <thead>--}}
{{--      <tr>--}}
{{--        <th class='col-id'>ID</th>--}}
{{--        <th class='col-location-name'>店舗名</th>--}}
{{--        <th class='col-main-category'>メインカテゴリー</th>--}}
{{--        <th class='col-date'>最終投稿日</th>--}}
{{--        <th class='col-control'></th>--}}
{{--      </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--      @forelse($locations as $location)--}}
{{--      <tr>--}}
{{--        <td class='col-id'><a href="{{url('location/edit/'.$location->location_id)}}">{{$location->location_id}}</a>--}}
{{--        </td>--}}
{{--        <td class='col-location-name'>{{ $location->gmb_location_name }}</td>--}}
{{--        <td class='col-main-category'>{{ $location->gmb_primary_category_id }}</td>--}}
{{--        <td class='col-date'>--}}
{{--          {{optional(optional($location->localPosts()->latest('update_time')->first())->update_time)->format('Y/m/d H:i')}}--}}
{{--        </td>--}}
{{--        <td class='col-control'>--}}
{{--          <button class="btn btn-primary btn-open-edit" data-locationid="{{$location->location_id}}">編集</button>--}}
{{--          <form action="{{ action('LocationController@destroy', ['locationId' => $location->location_id]) }}" method='POST'>--}}
{{--            @csrf--}}
{{--            @method('DELETE')--}}
{{--            <button type='submit' class="btn btn-danger btn-open-delete" onclick="return confirm('削除しますか？')">削除</button>--}}
{{--          </form>--}}
{{--        </td>--}}
{{--      </tr>--}}
{{--      @empty--}}
{{--      @endforelse--}}
{{--    </tbody>--}}
{{--  </table>--}}
{{--  <div class='col-md-4 mx-auto'>--}}
{{--    {{ $locations->links() }}<span>{{ $locations->firstItem() }} ～ {{ $locations->lastItem() }} 件 / 全--}}
{{--      {{ $locations->total() }} 件</span>--}}
{{--  </div>--}}
{{--  <div class='col-12'>--}}
{{--    <div class='float-right'>--}}
{{--      <button class=" btn btn-primary btn-block" onclick="location.href='/location/create'">　追加　</button>--}}
{{--    </div>--}}
{{--  </div>--}}
{{--</div>--}}
@endsection