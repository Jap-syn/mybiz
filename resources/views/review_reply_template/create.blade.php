@extends('layouts.app')
@section('title', '返信テンプレート' . ($isCreate ? '作成' : '編集'))
@section('head')
    @include('components.include-emoji')
@endsection
@section('content')
    <div class="col-md-8 order-md-1">
        <h4 class="mb-4">返信テンプレート{{$isCreate ? '作成' : '編集'}}</h4>
        <form method="post" action="{{$isCreate ? action('ReviewReplyTemplateController@store') : action('ReviewReplyTemplateController@update')}}">
            @csrf
            <input type="hidden" name="review_reply_template_id" value="{{$template->review_reply_template_id}}" />
            <div class="mb-4">
                <label for="account_id" class="d-block font-weight-bold">ブランド</label>
                {{Form::select('account_id', $accounts, old('account_id', $template->account_id), ['class' => ['mb-3 form-control', ($errors->has('account_id')) ? 'is-invalid' : ''], 'placeholder' => '選択してください'])}}
                @if ($errors->has('account_id'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{$errors->first('account_id')}}</strong>
                    </span>
                @endif
            </div>
            <div class="mb-4">
                <label for="template_name" class="d-block font-weight-bold">テンプレート名</label>
                {{Form::text('template_name', old('template_name', $template->template_name), ['class' => ['mb-3 form-control', ($errors->has('template_name')) ? 'is-invalid' : '']])}}
                @if ($errors->has('template_name'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{$errors->first('template_name')}}</strong>
                    </span>
                @endif
            </div>
            <div class="mb-4">
                <label for="target_star_rating" class="d-block font-weight-bold">自動返信クチコミ評点</label>
                {{Form::select('target_star_rating', config('formConst.rate'), old('target_star_rating', $template->target_star_rating), ['class' => ['mb-3 form-control', ($errors->has('target_star_rating')) ? 'is-invalid' : ''], 'placeholder' => '選択してください'])}}
                @if ($errors->has('target_star_rating'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{$errors->first('target_star_rating')}}</strong>
                    </span>
                @endif
            </div>
            <div class="row">
                <div class="col mb-4">
                    <label for="template" class="font-weight-bold">返信内容</label>
                    <div class="emoji-picker-container">
                        {{Form::textarea('template', old('template', $template->template),
                        ['rows' => 8, 'class' => ['form-control', ($errors->has('template')) ? 'is-invalid' : ''], 'data-emojiable' => 'true', 'data-emoji-input' => 'unicode'])}}
                        @if ($errors->has('template'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{$errors->first('template')}}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="row justify-content-end">
                    <div class="m-3">
                        <button type="submit" class="btn btn-primary btn-block{{!Gate::allows('edit-review') ? ' disabled' : ''}}" {{!Gate::allows('edit-review') ? 'aria-distabled="true" disabled' : ''}}>{{$isCreate ? 'テンプレートを追加する' : 'テンプレートを更新する'}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection