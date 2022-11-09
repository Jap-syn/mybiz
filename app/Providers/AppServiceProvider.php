<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\ReviewReply;
use App\Observers\ReviewReplyObserver;
use App\ReviewReplyTemplate;
use App\Observers\ReviewReplyTemplateObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //create_user_id,update_user_id
        ReviewReply::observe(ReviewReplyObserver::class);
        ReviewReplyTemplate::observe(ReviewReplyTemplateObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
