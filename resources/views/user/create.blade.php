@extends('layouts.app')
@section('title', 'ユーザー編集')

@section('head')
<link href="{{ mix('css/user.css') }}" rel="stylesheet">
<script>
    TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
</script>
<script type="text/javascript" src="{{ mix('js/user_create.js') }}" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js"></script>
<script>
    const isCreate = {{ $isCreate ?'true': 'false' }};
    const isReadOnly = {{ $isReadOnly ?'true': 'false' }};
</script>
@endsection

@section('content')
<div class="col-md-8 order-md-1">
    <h4 class="mb-4">ユーザー{{ $isCreate ? '作成' : '編集' }}</h4>
    <form action="{{ $isCreate ? route('user.store') : route('user.update', $user->user_id) }}" method="post"
        enctype="multipart/form-data">
        @csrf
        @if(!$isCreate)
        @method('PATCH')
        @endif
        <input type='hidden' name='user_id' value="{{$user->user_id}}" />
        <div class='container'>
            {{-- ユーザー名 --}}
            <div class='row p-2' id='name_row'>
                <div class="col-md-4 text-right">
                    <label for="users_name" class="d-block font-weight-bold">ユーザー名</label>
                </div>
                <div class='col-md-8 pr-1'>
                    {{Form::text('users_name', old('users_name', $user->name), [
                                    'id' => 'users_name',
                                    'class' => ['form-control',($errors->has('users_name')) ? 'is-invalid':''],
                                    'readonly' => $isReadOnly
                                ])}}
                    @if ($errors->has('users_name'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('users_name') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            {{-- email --}}
            <div class='row p-2' id='email_row'>
                <div class="col-md-4 text-right">
                    <label for="users_email" class="d-block font-weight-bold">emailアドレス</label>
                </div>
                <div class='col-md-8 pr-1'>
                    {{Form::text('users_email', old('users_email', $user->email), [
                                    'id' => 'users_email',
                                    'class' => ['form-control',($errors->has('users_email')) ? 'is-invalid':''],
                                    'readonly' => $isReadOnly
                                ])}}
                    @if ($errors->has('users_email'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('users_email') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            {{-- Password --}}
            @if(!$isCreate)
            <div class='row pt-4 pb-0'>
                <div class="col-md-4 text-right">
                </div>
                <div class='col-md-8 pr-1'>
                    <strong>※パスワードを変更する場合のみ入力してください</strong>
                </div>
            </div>
            @endif
            <div class='row p-2' id='password_row'>
                <div class="col-md-4 text-right">
                    <label for="users_password" class="d-block font-weight-bold">パスワード</label>
                </div>
                <div class='col-md-8 pr-1'>
                    {{Form::password('users_password', [
                                    'id' => 'users_password',
                                    'class' => ['form-control',($errors->has('users_password')) ? 'is-invalid':''],
                                    'readonly' => $isReadOnly
                                ])}}
                    @if ($errors->has('users_password'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('users_password') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            {{-- Password（確認用） --}}
            <div class='row p-2' id='password_cnf_row'>
                <div class="col-md-4 text-right">
                    <label for="users_password_confirmation" class="d-block font-weight-bold">パスワード（確認用）</label>
                </div>
                <div class='col-md-8 pr-1'>
                    {{Form::password('users_password_confirmation', [
                                    'id' => 'users_password_confirmation',
                                    'class' => ['form-control',($errors->has('users_password_confirmation')) ? 'is-invalid':''],
                                    'readonly' => $isReadOnly
                                ])}}
                    @if ($errors->has('users_password_confirmation'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('users_password_confirmation') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            {{-- 所属企業 --}}
            <div class='row py-2'>
                <div class='col-md-4 text-right'>
                    <label for="" class="d-block font-weight-bold">所属企業</label>
                </div>
                <div class='col-8'>
                    <select name="enterprise_id" id='enterprise_id'
                        class="form-control {{ $isReadOnly ? 'form-control-disabled disabled-wrapper' : '' }} {{ $errors->has('enterprise_id') ? 'is-invalid':'' }}">
                        <option value='' disabled {{ old('enterprise_id',  $user->enterprise_id) ? '' : 'selected' }}
                            data-locations="">
                            所属企業を選択してください
                        </option>
                        @forelse($enterprises as $enterprise)
                        <option value="{{ $enterprise->enterprise_id }}"
                            data-accounts="{{ optional($accounts->where('enterprise_id', '=', $enterprise->enterprise_id))->    implode('account_id', ',') }}"
                            {{ $enterprise->enterprise_id == old('enterprise_id', $user->enterprise_id) ? 'selected' : '' }}>
                            {{ $enterprise->name }}
                        </option>
                        @empty
                        <option>参照できる所属企業がありません</option>
                        @endforelse
                    </select>
                    @if ($errors->has('enterprise_id'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('enterprise_id') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class='row py-2'>
                <div class='col-4 text-right'>
                    <label for="account" class="d-block font-weight-bold">ブランド</label>
                    <div>
                        <button type='button' id='btn-select-all' class='btn btn-primary p-1 m-1'>全選択
                        </button>
                    </div>
                    <div>
                        <button type='button' id='btn-de-select-all' class='btn btn-secondary p-1 m-1'>全解除
                        </button>
                    </div>
                </div>
                <div class='col-8'>
                    <div
                        class="account-checkboxes-wrapper form-control {{ $errors->has('location_id') ? 'is-invalid':'' }} {{ $isReadOnly? 'disabled-wrapper' : '' }}">
                        @forelse($accounts as $account)
                        <div class='location-row'>
                            {{Form::checkbox(
                                            'account_ids[]',
                                            $account->account_id,
                                            collect( old('account_ids[]', optional($user_roles)->pluck('account_id')) )->contains($account->account_id) ,
                                            ['id' => 'ac_' . $account->account_id, 'class' => ['location-checkboxes', $isReadOnly ? 'form-control-disabled' : '']]
                                        )}}
                            <label for="lo_{{$account->account_id}}"
                                class="{{ $isReadOnly ? 'form-control-disabled' : '' }}">{{ $account->gmb_account_name }}</label>
                        </div>
                        @empty
                        <span>参照できるブランドがありません</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="row py-2">
                <div class="col-md-4 text-right">
                    <label for="access_control" class="d-block font-weight-bold">アクセス制御設定</label>
                </div>
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col-4">
                            <label for=""></label>
                        </div>
                        <div class="col-2">
                            <label for="allow">表示</label>
                        </div>
                        <div class="col-2">
                            <label for="edit">編集</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 flexbox">
                            <label for="local_post">投稿管理<br>写真管理</label>
                        </div>
                        <div class="col-2">
                            {{Form::checkbox(
                                'allow_local_post', config('const.FLG_ON'),
                                old('allow_local_post', $isCreate ? config('const.FLG_ON') : ($access_control['allow_localpost'] ? config('const.FLG_ON') : '')),
                                ['id' => 'allow_local_post', 'class' => ['mb-3', $isReadOnly ? 'form-control-disabled' : 'form-control']]
                            )}}
                        </div>
                        <div class="col-2">
                            {{Form::checkbox(
                                'edit_local_post', config('const.FLG_ON'),
                                old('edit_local_post', $isCreate ? config('const.FLG_ON') : ($access_control['edit_localpost'] ? config('const.FLG_ON') : '')),
                                ['id' => 'edit_local_post', 'class' => ['mb-3', $isReadOnly ? 'form-control-disabled' : 'form-control']]
                            )}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 flexbox">
                            <label for="allow_review">クチコミ管理</label>
                        </div>
                        <div class="col-2">
                            {{Form::checkbox(
                                'allow_review', config('const.FLG_ON'),
                                old('allow_review', $isCreate ? config('const.FLG_ON') : ($access_control['allow_review'] ? config('const.FLG_ON') : '')),
                                ['id' => 'allow_review', 'class' => ['mb-3', $isReadOnly ? 'form-control-disabled' : 'form-control']]
                            )}}
                        </div>
                        <div class="col-2">
                            {{Form::checkbox(
                                'edit_review', config('const.FLG_ON'),
                                old('edit_review', $isCreate ? config('const.FLG_ON') : ($access_control['edit_review'] ? config('const.FLG_ON') : '')),
                                ['id' => 'edit_review', 'class' => ['mb-3', $isReadOnly ? 'form-control-disabled' : 'form-control']]
                            )}}
                        </div>
                    </div>
                </div>
            </div>
            {{-- 追加／更新ボタン類 --}}
            <div class="d-flex justify-content-between mb-4">
                <div class='m-3'>
                    <a href="{{route('user.index')}}">
                        <button class="btn btn-primary" type="button">一覧に戻る</button>
                    </a>
                </div>
                {{-- 「下書き」以外は修正不可 --}}
                @if(!$isReadOnly)
                <div class='m-3 d-flex'>
                    <button id="btn-submit" class="btn btn-primary" type="submit">保存
                    </button>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection