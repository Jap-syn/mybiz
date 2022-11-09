@extends('layouts.app')
@section('title', 'ユーザー一覧')
@section('head')
<link href="{{mix('css/user.css')}}" rel="stylesheet">
<script type="text/javascript" src="{{mix('js/user_index.js')}}" defer></script>
@endsection
@section('content')
<h3>ユーザー一覧</h3>
<div class="row">
    <div class="col-12">
        <div class="row m-3">
            <a href="{{route('user.register')}}">
                <button class="btn btn-primary btn-block">ユーザーの作成</button>
            </a>
        </div>
    </div>
</div>
<form action="{{action('UserController@index')}}" method="get" name="template">
    <div class="row m-3">
        <div class="col-4">
            <label for="accounts">所属企業</label>
            {{Form::select('enterprise', $enterprises, request()->input('enterprise'), ['class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
        </div>
        <div class="col-4">
        </div>
    </div>
    <div class="row justify-content-end">
        <div class="col-3 m-3">
            <button class="btn btn-success btn-block" type="submit">検索</button>
        </div>
    </div>
</form>
<form action="#" method="post" name="destroy_user_form">
    @csrf
    @method('DELETE')
    <input type="hidden" name="destroy_user_id" value="" />
</form>
<div class="row m-3">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">ユーザー名</th>
                    <th class="col-name">email</th>
                    <th class="col-name">所属企業名</th>
                    <th class="col-button"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="col-id">
                        <a href="{{action('UserController@edit', ['user' => $user->user_id])}}">{{$user->user_id}}</a>
                    </td>
                    <td class="col-name">{{$user->name}}</td>
                    <td class="col-email">{{$user->email}}</td>
                    <td class="col-name">{{$user->enterprise->name}}</td>
                    <td class="col-button">
                        <a href="{{action('UserController@edit', ['user' => $user->user_id])}}">
                            <button class="btn btn-primary">編集</button>
                        </a>
                        <button class="btn btn-danger" onClick="destroyUser({{$user->user_id}})">削除</button>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
        <div class="col-md-4 mx-auto">
            {{$users->appends(request()->input())->links()}}
            <span>
                @if(is_null($users->firstItem())) 0 @else {{$users->firstItem()}} @endif ～
                @if(is_null($users->lastItem())) 0 @else {{$users->lastItem()}} @endif 件&nbsp;/&nbsp;全
                {{$users->total()}} 件
            </span>
        </div>
    </div>
</div>
<div class="row m-3">
    <div class="col-12">
        <div class="row justify-content-end float-right">
            <a href="{{route('user.register')}}">
                <button class="btn btn-primary btn-block">ユーザーの作成</button>
            </a>
        </div>
    </div>
</div>
@endsection