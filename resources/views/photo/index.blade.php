@extends('layouts.app')
@section('title', '写真管理')
@section('head')
    <link href="{{mix('css/photo.css')}}" rel="stylesheet">
    <script type="text/javascript" src="{{mix('js/photo.js')}}" defer></script>
@endsection
@section('content')
    <h3>写真管理</h3>
    <div class="row">
        <div class="col-12">
            <div class="row m-3">
                <a class="@if (!Gate::allows('edit-localpost')) disabled @endif" href="{{action('PhotoController@create')}}">
                    <button class="btn btn-primary btn-block{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}" {{!Gate::allows('edit-localpost') ? 'aria-distabled="true" disabled' : ''}}>
                        写真のアップロード
                    </button>
                </a>
            </div>
        </div>
    </div>
    <form method="get" name="photo" action="{{action('PhotoController@index')}}">
        <div class="row m-3">
            <div class="col-4">
                <label for="account">ブランド</label>
                {{Form::select('account', $accounts, request()->input('account'), ['id' => 'account', 'class' => 'mb-3 form-control'])}}
            </div>
            <div class="col-4">
                <label for="start_date">投稿反映日(始)</label>
                <input type="text" id="startDate" name="startDate" class="mb-3 form-control flatpickr" value="{{request()->input('startDate')}}" data-default-date="{{request()->input('startDate')}}">
            </div>
            <div class="col-4">
                <label for="end_date">投稿反映日(終)</label>
                <input type="text" id="endDate" name="endDate" class="mb-3 form-control flatpickr" value="{{request()->input('endDate')}}" data-default-date="{{request()->input('endDate')}}">
            </div>
            {{Form::hidden('period', request()->input('period'), ['id' => 'period'])}}
        </div>
        <div class="row m-3">
            <div class="col-4">
                <label for="category">カテゴリー</label>
                {{Form::select('category', config('formConst.PHOTO_CATEGORY'), request()->input('category'), ['id' => 'category', 'class' => 'mb-3 form-control', 'placeholder' => 'すべて'])}}
            </div>
            <div class="col-4">
                <label for="sync_status">ステータス</label>
                {{Form::select('syncStatus', config('formConst.PHOTO_SYNC_STATUS'), request()->input('syncStatus'), ['id' => 'syncStatus', 'class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
            </div>
        </div>
    </form>
    <div class="row justify-content-end">
        <div class="col-3 m-3">
            <button type="button" class="btn btn-success btn-block" onClick="document.photo.submit();">
                検索
            </button>
        </div>
        <div class="col-3 m-3">
            <form method="post" action="{{action('PhotoController@delete')}}">
                @csrf
                <input type="hidden" id="deleteAccount" name="account" value="">
                <input type="hidden" id="deleteItemIds" name="media_item2_group_ids" value="">
                <input type="hidden" id="is_deleted" name="is_deleted" value="">
                <input type="hidden" id="sync_type" name="sync_type" value="">
                <input type="hidden" id="sync_status" name="sync_status" value="">
                <input type="hidden" id="scheduled_sync_time" name="scheduled_sync_time" value="">
                <button type="submit" class="btn btn-danger form-control{{(!Gate::allows('edit-localpost')) ? ' disabled' : ''}}" onClick="return bulkDeleteConfirm();" {{!Gate::allows('edit-localpost') ? 'aria-distabled="true" disabled' : ''}}>
                    写真の一括削除
                </button>
            </form>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-4">
            <button type='button' id='btn-select-all' class='btn btn-primary'>
                全選択
            </button>
            <button type='button' id='btn-de-select-all' class='btn btn-secondary'>
                全解除
            </button>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <ul class="image_list">
                @forelse($photos as $photo)
                    <li>
                        <div class="mb-1 image_box">
                            <div class="btn_area">
                                <a href="{{action('PhotoController@edit', ['mediaItem2GroupId' => $photo->media_item2_group_id])}}">
                                    <button type="button" class="btn btn-{{$photo->isEditable() ? 'primary' : 'secondary'}} btn-sm">
                                        {{$photo->isEditable() ? '編集' : '表示'}}
                                    </button>
                                </a>
                                <form method="post" class="d-inline" action="{{action('PhotoController@delete')}}">
                                    @csrf
                                    <input type="hidden" id="deleteAccount_{{$photo->media_item2_group_id}}" name="account" value="{{$photo->account_id}}">
                                    <input type="hidden" id="deleteItemId_{{$photo->media_item2_group_id}}" name="media_item2_group_ids" value="{{$photo->media_item2_group_id}}">
                                    <input type="hidden" id="is_deleted_{{$photo->media_item2_group_id}}" name="is_deleted" value="">
                                    <input type="hidden" id="sync_type_{{$photo->media_item2_group_id}}" name="sync_type" value="">
                                    <input type="hidden" id="sync_status_{{$photo->media_item2_group_id}}" name="sync_status" value="">
                                    <input type="hidden" id="scheduled_sync_time_{{$photo->media_item2_group_id}}" name="scheduled_sync_time" value="">
                                    <button type="submit" class="btn btn-danger btn-sm{{!Gate::allows('edit-localpost') ? ' disabled' : ''}}" onClick="return deleteConfirm();" {{!Gate::allows('edit-localpost') ? 'aria-distabled="true" disabled' : ''}}>
                                        削除
                                    </button>
                                </form>
                            </div>
                            <img class="thumbnail" src="{{$photo->getThumbnailImageUrl()}}" alt="{{$photo->gmb_description}}" />
                            <input type="checkbox" name="media_item2_group_id" class="image_checkbox" value="{{$photo->media_item2_group_id}}" />
                            @if (is_null($photo->update_time)) {{Carbon\Carbon::parse($photo->create_time)->format('Y-m-d')}} @else {{Carbon\Carbon::parse($photo->update_time)->format('Y-m-d')}} @endif
                        </div>
                    </li>
                @empty
                    <li>
                        <div class="mb-1">
                            <p>写真がありません</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection