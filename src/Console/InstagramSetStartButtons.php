<?php

namespace NotificationChannels\Instagram\Console;

use Illuminate\Console\Command;
use NotificationChannels\Instagram\Instagram;

class InstagramSetStartButtons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:set-start-buttons
        {--access_token= : Instagram access token}
        {--profile_id= : Instagram profile ID}
        {--api_version= : Instagram API version (default from config)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Instagram starting buttons';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $accessToken = $this->option('access_token') ?: config('services.instagram.access_token');
        $profileId = $this->option('profile_id') ?: config('services.instagram.profile_id');
        $version = $this->option('api_version') ?: config('services.instagram.version');
        $buttons = config('services.instagram.start_buttons');

        if (empty($accessToken) || empty($profileId)) {
            $this->error('Access token and profile ID must be provided (via options or config).');
            return;
        }

        $instagram = new Instagram();
        $instagram->setAccessToken($accessToken)
            ->setProfileId($profileId)
            ->setApiVersion($version);

        try {
            $instagram->post("{$profileId}/messenger_profile", [
                'platform' => 'instagram',
                'ice_breakers' => [
                    [
                        'call_to_actions' => $buttons,
                        'locale' => 'default',
                    ],
                ],
            ]);

            $this->info('Instagram start buttons were set successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to set Instagram start buttons: ' . $e->getMessage());
        }
    }
}
