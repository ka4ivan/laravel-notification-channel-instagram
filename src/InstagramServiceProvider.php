<?php

namespace NotificationChannels\Instagram;

use Illuminate\Support\ServiceProvider;

class InstagramServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(InstagramChannel::class)
            ->needs(Instagram::class)
            ->give(static function () {

                return new Instagram([
                    'apiVersion' => config('services.instagram.version', '22.0'),
                    'accessToken' => config('services.instagram.access_token'),
                    'username' => config('services.instagram.username'),
                    'profileId' => config('services.instagram.profile_id'),
                ]);
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
