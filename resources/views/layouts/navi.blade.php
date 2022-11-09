<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*home*')) active @endif" href="{{url('home')}}">ダッシュボード</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*localpost*')) active @endif @if (!Gate::allows('allow-localpost')) disabled invalid-link @endif" href="{{url('localpost')}}">投稿管理</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*photo*')) active @endif @if (!Gate::allows('allow-localpost')) disabled invalid-link @endif" href="{{url('photo')}}">写真管理</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*review*')) active @endif @if (!Gate::allows('allow-review')) disabled invalid-link @endif" href="{{url('review')}}">クチコミ管理</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*template*')) active @endif @if (!Gate::allows('allow-review')) disabled invalid-link @endif" href="{{url('template')}}">テンプレート管理</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*location*')) active @endif" href="{{url('location')}}">店舗情報管理</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->is('*contact*')) active @endif" href="{{url('contact')}}">サービスについてのお問い合わせ</a>
    </li>
    @if (Gate::allows('admin'))
        <li class="nav-item">&nbsp;</li>
        <li class="nav-item">
            <div class='nav-link'><strong>管理者用メニュー</strong></div>
            <ul>
                <li class="nav-item">
                    <a class="nav-link @if (request()->is('*users*')) active @endif" href="{{url('user')}}">ユーザー管理</a>
                </li>
            </ul>
        </li>
    @endif
</ul>

<div style="padding-top:15px;">
    <a href="https://forms.gle/nRaxW2Z1VAzpwAWF9" target="_blank" rel="noopener noreferrer"><img src="{{ asset('img/02_banner-03.png') }}" style="padding-left:1rem; width:95%; height:auto;"></a>
</div>
<div style="padding-top:15px;">
    <a href="https://forms.gle/SN1SWUqMux1kFS7K8" target="_blank" rel="noopener noreferrer"><img src="{{ asset('img/03_banner-02.png') }}" style="padding-left:1rem; width:95%; height:auto;"></a>
</div>