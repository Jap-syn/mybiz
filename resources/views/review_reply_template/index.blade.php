@extends('layouts.app')
@section('title', '返信テンプレート一覧')
@section('head')
    <link href="{{mix('css/template.css')}}" rel="stylesheet">
    <script type="text/javascript" src="{{mix('js/review_template.js')}}" defer></script>
@endsection
@section('content')
    <h3>返信テンプレート一覧</h3>
    <div class="row">
        <div class="col-12">
            <div class="row m-3">
                <a class="@if (!Gate::allows('edit-review')) disabled @endif" href="{{action('ReviewReplyTemplateController@create')}}">
                    <button class="btn btn-primary btn-block{{!Gate::allows('edit-review') ? ' disabled' : ''}}" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>テンプレートの作成</button>
                </a>
            </div>
        </div>
    </div>
    <form method="get" name="template" action="{{action('ReviewReplyTemplateController@index')}}">
        <div class="row m-3">
            <div class="col-4">
                <label for="accounts">ブランド</label>
                {{Form::select('account', $accounts, request()->input('account'), ['class' => 'mb-3 form-control'])}}
            </div>
            <div class="col-4">
                <label for="rate">自動返信クチコミ評点</label>
                {{Form::select('target_star_rating', config('formConst.rate'), request()->input('target_star_rating'), ['class' => 'mb-3 form-control', 'placeholder' => '選択してください'])}}
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-3 m-3">
                <button type="submit" class="btn btn-success btn-block">検索</button>
            </div>
        </div>
    </form>
    <form method="post" name="delete_template" action="{{action('ReviewReplyTemplateController@delete')}}">
        @csrf
        <input type="hidden" name="review_reply_template_id" value="" />
    </form>
    <div class="row m-3">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-name">ブランド</th>
                        <th class="col-name">テンプレート名</th>
                        <th class="col-rating">クチコミ評点</th>
                        <th class="col-template">返信内容</th>
                        <th class="col-button"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td class="col-id">
                                <a href="{{action('ReviewReplyTemplateController@edit', ['reviewReplyTemplateId' => $template->review_reply_template_id])}}">{{$template->review_reply_template_id}}</a>
                            </td>
                            <td class="col-name">{{$template->account->gmb_account_name}}</td>
                            <td class="col-name">{{$template->template_name}}</td>
                            <td class="col-rating">{{mb_str_pad($template->target_star_rating, config('const.ACTIVE_RATE_STRING'), config('const.RATE_LIMIT'), config('const.INACTIVE_RATE_STRING'))}}</td>
                            <td class="col-template">{{$template->template}}</td>
                            <td class="col-button">
                                <a href="{{action('ReviewReplyTemplateController@edit', ['reviewReplyTemplateId' => $template->review_reply_template_id])}}">
                                    <button class="btn btn-primary">編集</button>
                                </a>
                                <button class="btn btn-danger{{!Gate::allows('edit-review') ? ' disabled' : ''}}" onClick="deleteTemplate({{$template->review_reply_template_id}})" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>削除</button>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
            <div class="col-md-4 mx-auto">
                {{$templates->appends(request()->input())->links()}}
                <span>
                    @if (is_null($templates->firstItem())) 0 @else {{$templates->firstItem()}} @endif ～
                    @if (is_null($templates->lastItem())) 0 @else {{$templates->lastItem()}} @endif 件&nbsp;/&nbsp;全
                    {{$templates->total()}} 件
                </span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-12">
            <div class="row justify-content-end float-right">
                <a class="@if (!Gate::allows('edit-review')) disabled @endif" href="{{action('ReviewReplyTemplateController@create')}}">
                    <button class="btn btn-primary btn-block{{!Gate::allows('edit-review') ? ' disabled' : ''}}" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>テンプレートの作成</button>
                </a>
            </div>
        </div>
    </div>
@endsection