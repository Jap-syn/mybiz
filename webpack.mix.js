const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .scripts('resources/js/dashboard.js', 'public/js/dashboard.js')
    .scripts('resources/js/review_reply.js', 'public/js/review_reply.js')
    .scripts('resources/js/review_template.js', 'public/js/review_template.js')
    .scripts('resources/js/review.js', 'public/js/review.js')
    .scripts('resources/js/localpost_index.js', 'public/js/localpost_index.js')
    .scripts('resources/js/localpost_create.js', 'public/js/localpost_create.js')
    .scripts('resources/js/localpost_reserve.js', 'public/js/localpost_reserve.js')
    .scripts('resources/js/location_index.js', 'public/js/location_index.js')
    .scripts('resources/js/location_create.js', 'public/js/location_create.js')
    .scripts('resources/js/photo_create.js', 'public/js/photo_create.js')
    .scripts('resources/js/photo.js', 'public/js/photo.js')
    .scripts('resources/js/user_create.js', 'public/js/user_create.js')
    .scripts('resources/js/user_index.js', 'public/js/user_index.js')
    .scripts('resources/js/emoji/config.js', 'public/js/emoji/config.js')
    .scripts('resources/js/emoji/util.js', 'public/js/emoji/util.js')
    .scripts('resources/js/emoji/jquery.emojiarea.js', 'public/js/emoji/jquery.emojiarea.js')
    .scripts('resources/js/emoji/emoji-picker.js', 'public/js/emoji/emoji-picker.js')
    .scripts('resources/js/chart/chartjs.min.js', 'public/js/chart/chartjs.min.js')
    .scripts('resources/js/chart/chart.js', 'public/js/chart/chart.js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/layouts.scss', 'public/css').version()
    .sass('resources/sass/dashboard.scss', 'public/css').version()
    .sass('resources/sass/localpost.scss', 'public/css').version()
    .sass('resources/sass/location.scss', 'public/css').version()
    .sass('resources/sass/photo.scss', 'public/css').version()
    .sass('resources/sass/template.scss', 'public/css').version()
    .sass('resources/sass/review.scss', 'public/css').version()
    .sass('resources/sass/user.scss', 'public/css').version()
    .copy('resources/sass/emoji.css', 'public/css')
    .copyDirectory('resources/img', 'public/img')
    .options({
        // ライブラリ内のimgパスが相対指定されていると書き換えられてしまうので false にしておく。
        processCssUrls: false
     });
