@extends('layouts.app')
@section('title', 'サービスについての問い合わせ')

@section('head')
<script>
  TOPIC_TYPE = JSON.parse('{!! json_encode(config("formConst.GMB_TOPIC_TYPE")) !!}');
</script>
@endsection

@section('content')
<div class="m-3">
  <div>（株）ParaWorks</div>
  <div>マイビジチェーンサポート</div>
  <div><a href="mailto:info@paraworks.jp">info@paraworks.jp</a></div>
</div>
@endsection