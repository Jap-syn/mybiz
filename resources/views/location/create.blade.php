@extends('layouts.app')
@section('title', '店舗作成')

@section('head')
<link href="{{ mix('css/location.css') }}" rel="stylesheet">
<script>
    TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
</script>
<script type="text/javascript" src="{{ mix('js/location_create.js') }}" defer></script>
<script>
    const isCreate = {{ $isCreate ?'true': 'false' }};
</script>
@endsection

@section('content')
<div class="col-md-8 order-md-1">
    <h4 class="mb-4">店舗{{ $isCreate ? '作成' : '編集' }}</h4>
    <form action="{{ $isCreate ? url('/location/store') : url('/location/update') }}" method="post">
        @csrf
        <input type='hidden' name='location_id' value="{{$location->location_id}}" />
        <div class='container'>
            {{-- 店舗名 --}}
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_location_name" class="d-block font-weight-bold">店舗名</label>
                </div>
                <div class='col-md-8'>
                    {{Form::text('gmb_location_name', old('gmb_location_name', $location->gmb_location_name) , ['id' => 'gmb_location_name', 'class' => ['form-control',($errors->has('gmb_location_name')) ? 'is-invalid':'']])}}
                </div>
            </div>
            {{-- メインカテゴリー --}}
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_primary_category_id" class="d-block font-weight-bold">メインカテゴリー</label>
                </div>
                <div class='col-md-8'>
                    {{Form::select('gmb_primary_category_id', $categories->pluck('gmb_display_name', 'category_id'), old('gmb_primary_category_id', $location->gmb_primary_category_id) , ['id' => 'gmb_primary_category_id', 'class' => ['form-control',($errors->has('gmb_primary_category_id')) ? 'is-invalid':'']])}}
                    @include('errors.error_span', ['element_name' => 'gmb_primary_category_id'])
                </div>
            </div>
            {{-- 追加カテゴリー --}}
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_sub_category_ids" class="d-block font-weight-bold">追加カテゴリー</label>
                </div>
                <div class='col-md-8'>
                    {{Form::select('gmb_sub_category_ids[]', $categories->pluck('gmb_display_name', 'category_id'), old('gmb_sub_category_ids[]', $location->categories()->pluck('location_categories.category_id')->all()) , ['multiple' => 'multiple', 'id' => 'gmb_sub_category_ids', 'class' => ['form-control',($errors->has('gmb_sub_category_ids')) ? 'is-invalid':'']])}}
                    @include('errors.error_span', ['element_name' => 'gmb_sub_category_ids'])
                </div>
            </div>

            {{-- 住所 --}}
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_postaladdr_region_code" class="d-block font-weight-bold">住所</label>
                </div>
                <div class='col-md-8'>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            国・地域
                        </div>
                        <div class='col-md-9'>
                            {{Form::select('gmb_postaladdr_region_code', config('formConst.POSTAL_REGEONS'), old('gmb_postaladdr_region_code', $location->gmb_postaladdr_region_code) ?? 'JA', ['id' => 'gmb_postaladdr_region_code', 'class' => ['form-control',($errors->has('gmb_postaladdr_region_code')) ? 'is-invalid':''], 'readonly' => true])}}
                        </div>
                    </div>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            郵便番号
                        </div>
                        <div class='col-md-4'>
                            {{Form::text('gmb_postaladdr_postal_code', old('gmb_postaladdr_postal_code', $location->gmb_postaladdr_postal_code), ['id' => 'gmb_postaladdr_postal_code', 'class' => ['form-control',($errors->has('gmb_postaladdr_postal_code')) ? 'is-invalid':'']])}}
                            @include('errors.error_span', ['element_name' => 'gmb_postaladdr_postal_code'])
                        </div>
                    </div>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            都道府県
                        </div>
                        <div class='col-md-9'>
                            {{Form::select('gmb_postaladdr_admin_area', array_combine(config('formConst.POSTAL_PREFS'), config('formConst.POSTAL_PREFS')), old('gmb_postaladdr_admin_area', $location->gmb_postaladdr_admin_area), ['id' => 'gmb_postaladdr_admin_area', 'class' => ['form-control',($errors->has('gmb_postaladdr_admin_area')) ? 'is-invalid':'']])}}
                        </div>
                    </div>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            住所１
                        </div>
                        <div class='col-md-9'>
                            {{Form::text('gmb_postaladdr_locality', old('gmb_postaladdr_locality', $location->gmb_postaladdr_locality), ['id' => 'gmb_postaladdr_locality', 'class' => ['form-control',($errors->has('gmb_postaladdr_locality')) ? 'is-invalid':'']])}}
                            @include('errors.error_span', ['element_name' => 'gmb_postaladdr_locality'])
                        </div>
                    </div>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            住所２
                        </div>
                        <div class='col-md-9'>
                            {{Form::text('gmb_postaladdr_sublocality', old('gmb_postaladdr_sublocality', $location->gmb_postaladdr_sublocality), ['id' => 'gmb_postaladdr_sublocality', 'class' => ['form-control',($errors->has('gmb_postaladdr_sublocality')) ? 'is-invalid':'']])}}
                            @include('errors.error_span', ['element_name' => 'gmb_postaladdr_sublocality'])
                        </div>
                    </div>
                    <div class='row py-1 align-items-center'>
                        <div class='col-md-3 text-right'>
                            住所３
                        </div>
                        <div class='col-md-9'>
                            {{Form::textarea('gmb_postaladdr_address_lines', old('gmb_postaladdr_address_lines', $location->gmb_postaladdr_address_lines), ['rows' => '1', 'id' => 'gmb_postaladdr_address_lines', 'class' => ['form-control',($errors->has('gmb_postaladdr_sublocality')) ? 'is-invalid':'']])}}
                            @include('errors.error_span', ['element_name' => 'gmb_postaladdr_address_lines'])
                        </div>
                    </div>
                </div>
            </div>
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_primary_phone" class="d-block font-weight-bold">電話</label>
                </div>
                <div class='col-md-8'>
                    <div class='row'>
                        {{Form::text('gmb_primary_phone', old('gmb_primary_phone', $location->gmb_primary_phone), ['id' => 'gmb_primary_phone', 'class' => ['form-control',($errors->has('gmb_primary_phone')) ? 'is-invalid':'']])}}
                        @include('errors.error_span', ['element_name' => 'gmb_primary_phone'])
                    </div>
                </div>
            </div>
            <div class='row py-2'>
                <div class="col-md-4 text-right">
                    <label for="gmb_website_url" class="d-block font-weight-bold">Webサイト URL
                    </label>
                </div>
                <div class='col-md-8'>
                    <div class='row'>
                        {{Form::text('gmb_website_url', old('gmb_website_url', $location->gmb_website_url), ['id' => 'gmb_website_url', 'class' => ['form-control',($errors->has('gmb_website_url')) ? 'is-invalid':'']])}}
                        @include('errors.error_span', ['element_name' => 'gmb_website_url'])
                    </div>
                </div>
            </div>
        </div>
        {{-- ボタン類 --}}
        <div class="row p-3">
            <div class="col-md-3">
                <div class='btn btn-primary btn-block'>営業日編集</div>
            </div>
            <div class="col-md-3">
                <div class='btn btn-primary btn-block'>特別営業日編集</div>
            </div>
            <div class="col-md-3">
            </div>
            <div class="col-md-3">
                <button id="btn-submit" class="btn btn-primary btn-block"
                    type="submit">{{ $isCreate ? '追加する' : '更新する' }}</button>
            </div>
        </div>
        <div class="row p-3 mb-4">

        </div>
    </form>
</div>
@endsection