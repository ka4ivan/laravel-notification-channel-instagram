<?php

namespace NotificationChannels\Instagram;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use NotificationChannels\Instagram\Exceptions\CouldNotCreateMessage;
use NotificationChannels\Instagram\Exceptions\CouldNotSendNotification;

class InstagramChannel
{
    /** @var Instagram */
    private $instagram;

    /**
     * InstagramChannel constructor.
     */
    public function __construct(Instagram $instagram)
    {
        $this->instagram = $instagram;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return array
     * @throws CouldNotCreateMessage
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification): array
    {
        $message = $notification->toInstagram($notifiable);

        if (is_string($message)) {
            $message = InstagramMessage::create($message);
        }

        if ($message->toNotGiven()) {
            if (!$to = $notifiable->routeNotificationFor('instagram')) {
                throw CouldNotCreateMessage::recipientNotProvided();
            }

            $message->to($to);
        }

        $response = $this->instagram->send($message->toArray());

        if (Arr::get($response, 'error')) {
            throw CouldNotSendNotification::instagramRespondedWithAnExceptionError($response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
