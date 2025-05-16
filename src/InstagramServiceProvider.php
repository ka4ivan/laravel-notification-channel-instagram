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
                    'apiVersion' => config('services.instagram.api_version', '22.0'),
                    'accessToken' => config('services.instagram.access_token'),
                    'profileId' => config('services.instagram.profile_id'),
                ]);
            });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstagramSetStartButtons::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
