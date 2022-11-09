@extends('layouts.app')
@section('title', '投稿作成')

@section('head')
    <link href="{{ mix('css/photo.css') }}" rel="stylesheet">
    <script>
        TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
    </script>
    <script type="text/javascript" src="{{ mix('js/photo_create.js') }}" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js"></script>
    <script>
        const isCreate = {{ $isCreate ?'true': 'false' }};
        const isReadOnly = {{ $isReadOnly ?'true': 'false' }};
        const maxFileLength = '{{$maxFileLength}}'
    </script>
    @if(!$isReadOnly)
        @include('components.include-emoji')
    @endif
@endsection

@section('content')
    <div class="col-md-8 order-md-1">
        <h4 class="mb-4">写真{{ $isCreate ? '投稿' : '編集' }}</h4>
        <form action="{{ $isCreate ? url('/photo/store') : url('/photo/update') }}" method="post"
              enctype="multipart/form-data">
            @csrf
            <input type='hidden' name='media_item2_group_id' value="{{$media_item2_group->media_item2_group_id}}"/>
            <input type='hidden' name='all_delete_media_item2' id="all_delete_media_item2" value=""/>
            <div id="delete_media_item2"></div>

            <h5>写真や動画の投稿</h5>
            <div class='container'>
                {{-- 写真・動画 ※API経由では動画に対応していない--}}
                @if (!$isReadOnly)
                    <div id="drop-zone" style="width: 100%;" class="drop-zone pt-5 pb-5 text-center w-100">
                        <p class="title-label text-center">ここに写真やファイルをドロップ</p>
                        <button type="button" class="btn btn-primary" onClick="$('#file-input').click();">パソコンから写真や動画を選択
                        </button>
                    </div>
                @endif
                <input class="d-none" type="file" name='upload_files[]' multiple="multiple" id="file-input">
                <h5 class="mt-3">アップロードしたファイル</h5>
                <div id="register_media_items">
                    @foreach($mediaItem2 as $index => $mediaItem)
                        <span id="media_item2_{{$index}}" class="d-flex justify-content-between mb-1">
                            <div class="col-2 mb-1 d-flex file_box">
                                @if ($mediaItem['mediaFormat'] == 'PHOTO')
                                    <img class="thumbnail" src="{{$mediaItem['thumbnailUrl']}}"
                                         alt="{{$mediaItem['fileName']}}"/>
                                @else
                                    <a href="{{$mediaItem['fileUrl']}}" target="_blank">
                                        <img class="thumbnail" src="{{ asset('img/movie_icon.jpg') }}"/>
                                    </a>
                                @endif
                                    <p class="mr-1 text-nowrap">{{ $mediaItem['fileName'] }}</p>
                            </div>
                            @if (!$isReadOnly)
                                <button type="button" class="btn btn-primary delete-button"
                                        onclick="deleteRegisteredFile({{$index}}, '{{ $mediaItem['mediaItem2Ids']}}')">
                                    削除
                                </button>
                            @endif
                        </span>
                    @endforeach
                </div>
                <div id="preview" class="mb-5"></div>
            </div>
            {{-- ファイルのカテゴリ --}}
            <div class='row py-0 mb-4' id='post_locations_title_row'>
                <div class='col-md-4 text-right'>
                    <label for="" class="d-block font-weight-bold">カテゴリ</label>
                </div>
                <div class='col-md-8'>
                    {{Form::select('gmb_location_association_category', config('formConst.PHOTO_CATEGORY'), old('gmb_location_association_category', $media_item2_group->gmb_location_association_category) ?: 'CATEGORY_UNSPECIFIED' , ['id' => 'gmb_location_association_category', 'class' => ['form-control',($errors->has('gmb_location_association_category')) ? 'is-invalid':'', !$isCreate ? 'form-control-disabled disabled-wrapper' : '']])}}
                    @if ($errors->has('gmb_location_association_category'))
                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('gmb_location_association_category') }}</strong>
                        </span>
                    @endif
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
                                        {{ old('account_id',  $media_item2_group->account_id) || session(config('formConst.SESSION_MY_BRAND')) ? '' : 'selected' }}
                                        data-locations="">
                                    ブランドを選択してください
                                </option>
                                @forelse($accounts as $account)
                                    <option value="{{ $account->account_id }}"
                                            data-locations="{{ $account->locations->implode('location_id', ',') }}"
                                            {{ ( $account->account_id == ( old('account_id', $media_item2_group->account_id) ?: session(config('formConst.SESSION_MY_BRAND')) ) ) ? 'selected' : '' }}>
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
                                            collect( old('location_id[]', $media_item2_group->mediaItems()->pluck('location_id')->unique()) )->contains($location->location_id) ,
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
                            投稿日:{{Form::text('scheduled_sync_time', old('scheduled_sync_time', $media_item2_group->mediaItems()->first() && $media_item2_group->mediaItems()->first()->scheduled_sync_time ? \Carbon\Carbon::parse($media_item2_group->mediaItems()->first()->scheduled_sync_time)->format(config('formConst.FORMAT_DATE_YMD')) : ''), ['id' => 'scheduled_sync_time', 'class' => ['form-control', ($errors->has('scheduled_sync_time')) ? 'is-invalid':''], 'readonly' => $isReadOnly])}}
                        </div>
                        <div class='col-lg-5'>
                            時間:{{Form::select('scheduled_range', config('formConst.POST_TIME_RANGE'), old('scheduled_range', $media_item2_group->mediaItems()->first() && $media_item2_group->mediaItems()->first()->scheduled_sync_time ? \Carbon\Carbon::parse($media_item2_group->mediaItems()->first()->scheduled_sync_time)->format(config('formConst.FORMAT_TIME_HI')) : ''), ['id' => 'scheduled_range', 'class' => ['form-control', ($errors->has('scheduled_range')) ? 'is-invalid':''], 'placeholder' => '選択してください', 'disabled' => $isReadOnly])}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 追加／更新ボタン類 --}}
            <div class="d-flex justify-content-between mb-4">
                <div class='m-3'>
                    <a href="{{action('PhotoController@index')}}">
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