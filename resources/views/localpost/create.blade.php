@extends('layouts.app')
@section('title', '投稿作成')

@section('head')
    <link href="{{ mix('css/localpost.css') }}" rel="stylesheet">
    <script>
        TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
    </script>
    <script type="text/javascript" src="{{ mix('js/localpost_create.js') }}" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js"></script>
    <script>
        const isCreate = {{ $isCreate ?'true': 'false' }};
        const isReadOnly = {{ $isReadOnly ?'true': 'false' }};
    </script>
    @if(!$isReadOnly)
        @include('components.include-emoji')
    @endif
@endsection

@section('content')
    <div class="col-md-8 order-md-1">
        <h4 class="mb-4">投稿{{ $isCreate ? '作成' : '編集' }}</h4>
        <form action="{{ $isCreate ? url('/localpost/store') : url('/localpost/update') }}" method="post"
              enctype="multipart/form-data">
            @csrf
            <input type='hidden' name='local_post_group_id' value="{{$localpost->local_post_group_id}}"/>
            <input type='hidden' name='local_post_id' value="{{$localpost->local_post_id}}"/>
            <div class='container'>
                {{-- 写真・動画 ※API経由では動画に対応していない--}}
                <div class='row py-2' id='gmb_media_format_row'>
                    <div class="col-md-4 text-right">
                        <label for="" class="d-block font-weight-bold">写真</label>
                    </div>
                    <div class='col-md-8 text-center'>
                        {{Form::select('gmb_media_format', config('formConst.GMB_MEDIA_FORMAT'), old('gmb_media_format', $localpost->gmb_media_format) ?: 'PHOTO', ['id' => 'gmb_media_format', 'class' => ['d-none', 'form-control',($errors->has('gmb_media_format')) ? 'is-invalid':''], 'disabled'])}}
                        <input type='hidden' name='gmb_source_url'
                               value="{{old('gmb_source_url', optional($localpost->mediaItems()->first())->gmb_source_url)}}">
                        <div class="image-wrapper">
                            @if(!$isCreate && $localpost->mediaItems()->first())
                                <img class='saved-image' src="{{$localpost->mediaItems()->first()->getImageUrl()}}"/>
                            @else
                                <img class='saved-image' src="{{ asset('img/no_image.png') }}"/>
                            @endif
                            <img id="upload-preview"/>
                        </div>
                        {{--
                        【投稿画面では Dropzone の利用を一旦中止】
                        <div class="dropzone" id="gmb-manager-dropzone">
                            <div class='dz-message'>
                                <button type="button"
                                    class="dz-button">ファイルを選択</button>
                            </div>
                        </div>
                        --}}
                        {{-- 実際にコントローラー側の request()->file() で使用する file エレメント。 --}}
                        @if(!$isReadOnly)
                            <div class='upload-files-wrapper'>
                                <input id="upload-files" type='file' name='upload_files[]' multiple="multiple">
                            </div>
                        @endif

                        <div style="padding-top:10px; text-align: center;">
                            <span style="color:red;">幅：400px - 3500px、高さ：300px - 3500px</span>
                        </div>
                    </div>
                </div>
                {{-- 投稿タイプ　パネル --}}
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        @php
                            $selected_key = old('gmb_topic_type', $localpost->gmb_topic_type) ?? 'STANDARD';
                        @endphp
                        <input type='hidden' name='gmb_topic_type' id='gmb_topic_type' value='{{$selected_key}}'>
                        {{-- @forelse(config('formConst.GMB_TOPIC_TYPE_WITHOUT_UNSPECIFIED') as $key => $value) --}}
                        @php
                            $gmb_topic_type_temp = [
                                'STANDARD' => '最新情報',
                                'EVENT' => 'イベント',
                                // 'OFFER' => '特典'
                            ];
                        @endphp
                        @forelse($gmb_topic_type_temp as $key => $value)
                            @if($isReadOnly)
                                <span class="nav-item nav-link {{$key == $selected_key ? 'active' : ''}}">{{$value}}</span>
                            @else<a class="nav-item nav-link {{$key == $selected_key ? 'active' : ''}}"
                                    id="nav-tab-gmb-topic-type-{{$key}}" data-toggle="tab" href="#nav-{{$key}}"
                                    role="tab"
                                    aria-controls="nav-{{$key}}" onclick="setTopicType('{{$key}}')"
                                    aria-selected="{{$key == $selected_key ? 'true' : 'false'}}">{{$value}}</a>
                            @endif
                        @empty
                        @endforelse
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        {{-- 現在のステータス --}}
                        @if(!$isCreate)
                            <div class='row py-2' id='sync_status'>
                                <div class="col-md-4 text-right">
                                    <label for="gmb_event_title" class="d-block font-weight-bold">現在のステータス</label>
                                </div>
                                <div class='col-md-8'>
                                    <div>
                                        <h5>
                                    <span
                                            class="badge {{$localpost->sync_status == 'FAILED' ? 'badge-danger' : 'badge-secondary'}} p-2">
                                        {{\App\Enums\SyncStatusType::getString($localpost->sync_status)}}
                                        @if($localpost->sync_status == 'QUEUED')
                                            [ {{$localpost->scheduled_sync_time}} ]
                                        @endif
                                    </span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- タイトル --}}
                        <div class='row p-2' id='gmb_event_title_row'>
                            <div class="col-md-4 text-right">
                                <label for="gmb_event_title" class="d-block font-weight-bold">タイトル</label>
                            </div>
                            <div class='col-md-8 pr-1'>
                                {{Form::text('gmb_event_title', old('gmb_event_title', $localpost->gmb_event_title), [
                                    'id' => 'gmb_event_title',
                                    'class' => ['form-control',($errors->has('gmb_event_title')) ? 'is-invalid':''],
                                    'data-emojiable' => "true",
                                    'data-emoji-input' => "unicode",
                                    'readonly' => $isReadOnly
                                ])}}
                                @if ($errors->has('gmb_event_title'))
                                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('gmb_event_title') }}</strong>
                            </span>
                                @endif
                            </div>
                        </div>
                        {{-- 詳細 --}}
                        <div class='row p-2' id='gmb_summary_row'>
                            <div class="col-md-4 text-right">
                                <label for="" class="d-block font-weight-bold">詳細</label>
                            </div>
                            <div class='col-md-8 pr-1 resizable'>
                                {{Form::textarea('gmb_summary', old('gmb_summary', $localpost->gmb_summary), [
                                        'rows' => 4,
                                        'class' => ['form-control',($errors->has('gmb_summary')) ? 'is-invalid':''],
                                        'data-emojiable' => "true",
                                        'data-emoji-input' => "unicode",
                                        'readonly' => $isReadOnly,
                                    ])}}
                                @if ($errors->has('gmb_summary'))
                                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('gmb_summary') }}</strong>
                            </span>
                                @endif
                            </div>
                        </div>
                        {{-- 時間を追加 --}}
                        <div class='row py-2' id='has_event_time_row'>
                            <div class='col-md-4 text-right'>
                                <label for="" class="d-block font-weight-bold">時間の追加</label>
                            </div>
                            <div class='col-md-8'>
                                <div class="custom-control custom-switch">
                                    {{-- checkbox が外されたときに request でフィールド自体が渡らないため hidden で対応 --}}
                                    <input name="gmb_has_event_time" type="hidden" value="0">
                                    {{Form::checkbox(
                                            'gmb_has_event_time', 1,
                                            old('gmb_has_event_time',
                                            $localpost->gmb_has_event_time ? 1 : ''),
                                            [
                                                'id' => 'gmb_has_event_time',
                                                'class' => ['custom-control-input'],
                                                'data-toggle' => 'toggle',
                                                'onChange' => 'setEventFlatpickr()',
                                                'readonly' => $isReadOnly,
                                                'disabled' => $isReadOnly,
                                            ]
                                        )}}
                                    <label class="custom-control-label" for="gmb_has_event_time"></label>
                                </div>
                            </div>
                        </div>
                        {{-- 開始日／終了日 --}}
                        <div class='row py-2' id='start_end_wrapper'>
                            <div class='col-md-4 text-right'>
                                <label for="" class="d-block font-weight-bold">期間</label>
                            </div>
                            <div class='col-md-8'>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        開始日:{{Form::text('gmb_event_start_time', old('gmb_event_start_time', $localpost->gmb_event_start_time), ['id' => 'gmb_event_start_time', 'class' => ['form-control', ($errors->has('gmb_event_start_time')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                                    </div>
                                    <div class='col-md-6'>
                                        終了日:{{Form::text('gmb_event_end_time', old('gmb_event_end_time', $localpost->gmb_event_end_time), ['id' => 'gmb_event_end_time', 'class' => ['form-control', ($errors->has('gmb_event_end_time')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id='gmb_action_type_wrapper'>
                            <div class='row py-2' id='gmb_action_type_row'>
                                <div class='col-md-4 text-right'>
                                    <label for="" class="d-block font-weight-bold">ボタンの追加(任意)</label>
                                </div>
                                <div class='col-md-8'>
                                    {{Form::select('gmb_action_type', config('formConst.GMB_ACTION_TYPE'), old('gmb_action_type', $localpost->gmb_action_type) ?: 'LOCAL_POST_ACTION_TYPE_UNSPECIFIED' , ['id' => 'gmb_action_type', 'class' => ['form-control',($errors->has('gmb_action_type')) ? 'is-invalid':''], 'disabled' => $isReadOnly])}}
                                    @if ($errors->has('gmb_action_type'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_action_type') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class='row py-2' id='gmb_action_type_url_row'>
                                <div class='col-md-4 text-right'>
                                    <label for="" class="d-block font-weight-bold">URL</label>
                                </div>
                                <div class='col-md-8'>
                                    {{Form::text('gmb_action_type_url', old('gmb_action_type_url', $localpost->gmb_action_type_url), ['id' => 'gmb_action_type_url', 'class' => ['form-control',($errors->has('gmb_action_type_url')) ? 'is-invalid':''], 'placeholder' => '例） https://example.jp/campaing', 'readonly' => $isReadOnly])}}
                                    @if ($errors->has('gmb_action_type_url'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_action_type_url') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div id='gmb_offer_detail_wrapper'>
                            <div class='row py-2' id='gmb_offer_coupon_code'>
                                <div class='col-md-4 text-right'>
                                    <label for="" class="d-block font-weight-bold">クーポンコード</label>
                                </div>
                                <div class='col-md-8'>
                                    {{Form::text('gmb_offer_coupon_code', old('gmb_offer_coupon_code', $localpost->gmb_offer_coupon_code), ['id' => 'gmb_offer_coupon_code', 'class' => ['form-control',($errors->has('gmb_offer_coupon_code')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                                    @if ($errors->has('gmb_offer_coupon_code'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_offer_coupon_code') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class='row py-2' id='gmb_offer_redeem_online_url'>
                                <div class='col-md-4 text-right'>
                                    <label for="" class="d-block font-weight-bold">クーポンURL</label>
                                </div>
                                <div class='col-md-8'>
                                    {{Form::text('gmb_offer_redeem_online_url', old('gmb_offer_redeem_online_url', $localpost->gmb_offer_redeem_online_url), ['id' => 'gmb_offer_redeem_online_url', 'class' => ['form-control',($errors->has('gmb_offer_redeem_online_url')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                                    @if ($errors->has('gmb_offer_redeem_online_url'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_offer_redeem_online_url') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class='row py-2' id='gmb_offer_terms_conditions'>
                                <div class='col-md-4 text-right'>
                                    <label for="" class="d-block font-weight-bold">クーポン利用規約</label>
                                </div>
                                <div class='col-md-8'>
                                    {{Form::text('gmb_offer_terms_conditions', old('gmb_offer_terms_conditions', $localpost->gmb_offer_terms_conditions), ['id' => 'gmb_offer_terms_conditions', 'class' => ['form-control',($errors->has('gmb_offer_terms_conditions')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                                    @if ($errors->has('gmb_offer_terms_conditions'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_offer_terms_conditions') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    ...
                </div>
                <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    ...
                </div>
            </div>
            {{-- 投稿先 --}}
            <div class='row py-0' id='post_locations_title_row'>
                <div class='col-md-4 text-right'>
                    <label for="" class="d-block font-weight-bold">投稿先</label>
                </div>
                <div class='col-md-8'>
                    &nbsp;
                </div>
            </div>
            <div class='row py-0' id='post_locations_row'>
                <div class='col'>
                    <div class='row py-2'>
                        <div class='col-4 text-right'>
                            ブランド：
                        </div>
                        <div class='col-8'>
                            <select name="account_id" id='account_id'
                                    class="form-control {{ !$isCreate ? 'form-control-disabled disabled-wrapper' : '' }} {{ $errors->has('account_id') ? 'is-invalid':'' }}">
                                <option value='' disabled
                                        {{ old('account_id',  $local_post_group->account_id) || session(config('formConst.SESSION_MY_BRAND')) ? '' : 'selected' }}
                                        data-locations="">
                                    ブランドを選択してください
                                </option>
                                @forelse($accounts as $account)
                                    <option value="{{ $account->account_id }}"
                                            data-locations="{{ $account->locations->implode('location_id', ',') }}"
                                            {{ ( $account->account_id == ( old('account_id', $local_post_group->account_id) ?: session(config('formConst.SESSION_MY_BRAND')) ) ) ? 'selected' : '' }}>
                                        {{ $account->gmb_account_name }}
                                    </option>
                                @empty
                                    <option>参照できるブランドがありません</option>
                                @endforelse
                            </select>
                            @if ($errors->has('account_id'))
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('account_id') }}</strong>
                        </span>
                            @endif
                        </div>
                    </div>
                    <div class='row py-2'>
                        <div class='col-4 text-right'>
                            都道府県：
                        </div>
                        <div class='col-8'>
                            <select name="pref_name" id='pref_name'
                                    class="form-control {{ !$isCreate ? 'form-control-disabled disabled-wrapper' : '' }} {{ $errors->has('pref_name') ? 'is-invalid':'' }}">
                                <option value=''
                                        {{ old('pref_name') ? '' : 'selected' }}
                                        data-locations="">
                                    都道府県を選択してください
                                </option>
                                @foreach($prefs as $prefName)
                                    <option value="{{ $prefName }}">
                                        {{ $prefName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class='row py-2'>
                        <div class='col-4 text-right'>
                            キーワード：
                        </div>
                        <div class='col-8'>
                            {{Form::text('account_keyword', '', [
                                    'id' => 'account_keyword',
                                    'class' => ['form-control', (!$isCreate ? 'form-control-disabled disabled-wrapper' : '')],
                                    'readonly' => $isReadOnly
                                ])}}
                        </div>
                    </div>
                    <div class='row py-2'>
                        <div class='col-4 text-right'>
                            @if($isCreate)
                                <div>
                                    <button type='button' id='btn-select-all' class='btn btn-primary p-1 m-1'>全選択
                                    </button>
                                </div>
                                <div>
                                    <button type='button' id='btn-de-select-all' class='btn btn-secondary p-1 m-1'>全解除
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class='col-4'>
                            <div>検索結果</div>
                            <div
                                    id="location_checkboxes"
                                    class="location-checkboxes-wrapper form-control {{ $errors->has('location_id') ? 'is-invalid':'' }} {{ !$isCreate ? 'disabled-wrapper' : '' }}">
                                @forelse($locations as $location)
                                    <div class='location-row'>
                                        {{Form::checkbox(
                                            'location_id[]',
                                            $location->location_id,
                                            collect( old('location_id[]', $local_post_group->localPosts->pluck('location_id')) )->contains($location->location_id) ,
                                            ['id' => 'lo_' . $location->location_id, 'class' => ['location-checkboxes', !$isCreate ? 'form-control-disabled' : ''], 'data-pref' => $location->gmb_postaladdr_admin_area, 'data-name' => $location->gmb_location_name, ]
                                        )}}
                                        <label for="lo_{{$location->location_id}}"
                                               class="{{ !$isCreate ? 'form-control-disabled' : '' }}">{{ $location->gmb_location_name }}</label>
                                    </div>
                                @empty
                                    <span>参照できる店舗がありません</span>
                                @endforelse
                            </div>
                        </div>
                        <div class='col-4'>
                            <div>選択中の店舗</div>
                            <div id="location_preview"
                                 class="location-checkboxes-wrapper form-control {{ $errors->has('location_id') ? 'is-invalid':'' }} {{ !$isCreate ? 'disabled-wrapper' : '' }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 投稿日時 --}}
            <div class='row py-2' id='schedule_row'>
                <div class='col-md-4 text-right'>
                    <label for="" class="d-block font-weight-bold">投稿日時</label>
                </div>
                <div class='col-md-8'>
                    <div class='row py-2'>
                        <div class='col-lg-4'>
                            投稿日:{{Form::text('scheduled_sync_time', old('scheduled_sync_time', $localpost->scheduled_sync_time ? \Carbon\Carbon::parse($localpost->scheduled_sync_time)->format(config('formConst.FORMAT_DATE_YMD')) : ''), ['id' => 'scheduled_sync_time', 'class' => ['form-control', ($errors->has('scheduled_sync_time')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                        </div>
                        <div class='col-lg-5'>
                            時間:{{Form::select('scheduled_range', config('formConst.POST_TIME_RANGE'), old('scheduled_range', $localpost->scheduled_sync_time ? \Carbon\Carbon::parse($localpost->scheduled_sync_time)->format(config('formConst.FORMAT_TIME_HI')) : ''), ['id' => 'scheduled_range', 'class' => ['form-control', ($errors->has('scheduled_range')) ? 'is-invalid':''], 'placeholder' => '選択してください', 'disabled' => $isReadOnly])}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 追加／更新ボタン類 --}}
            <div class="d-flex justify-content-between mb-4">
                <div class='m-3'>
                    <a href="{{action('LocalPostGroupController@index')}}">
                        <button class="btn btn-primary" type="button">一覧に戻る</button>
                    </a>
                </div>
                {{-- 「下書き」以外は修正不可 --}}
                @if(!$isReadOnly)
                    <div class='m-3 d-flex'>
                        <button id="btn-submit"
                                class="btn btn-primary mr-3{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}"
                                {{!Gate::allows('edit-localpost') ? ' disabled' : ''}}
                                type="submit" name="request_type" value="1" onclick="return confirm('下書き保存してよろしいですか？')">
                            下書き
                        </button>
                        <button id="btn-submit"
                                class="btn btn-primary mr-3{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}"
                                {{!Gate::allows('edit-localpost') ? ' disabled' : ''}}
                                type="submit" name="request_type" value="2" onclick="return postConfirm()">最短投稿
                        </button>
                        <button id="btn-submit"
                                class="btn btn-primary{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}"
                                {{!Gate::allows('edit-localpost') ? ' disabled' : ''}}
                                type="submit" name="request_type" value="3" onclick="return postConfirm()">予約投稿
                        </button>

                    </div>
                @endif
            </div>
        </form>
    </div>
@endsection