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

        if ($accessToken = $message->getAccessToken()) {
            $this->instagram->setAccessToken($accessToken);
        }

        if ($profileId = $message->getProfileId()) {
            $this->instagram->setProfileId($profileId);
        }

        if ($apiVersion = $message->getApiVersion()) {
            $this->instagram->setApiVersion($apiVersion);
        }

        if (!empty($message->getAttachments())) {
            return $this->sendAttachmentsMessage($message);
        }

        if ($message->hasAttachment) {
           return $this->sendAttachmentMessage($message);
        }

        $response = $this->instagram->send($message->toArray());
        if (Arr::get($response, 'error')) {
            throw CouldNotSendNotification::instagramRespondedWithAnExceptionError($response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws CouldNotCreateMessage
     * @throws CouldNotSendNotification
     */
    private function sendAttachmentMessage(InstagramMessage $message): array
    {
        $responses = [];

        $responses[] = $this->instagram->send($message->toArray());

        if ($message->hasText) {
            $tempMessage = clone $message;
            $tempMessage->hasAttachment = false;
            $responses[] = $this->instagram->send($tempMessage->toArray());
        }

        return array_map(fn($r) => json_decode($r->getBody()->getContents(), true), $responses);
    }

    /**
     * @throws CouldNotCreateMessage
     * @throws CouldNotSendNotification
     */
    private function sendAttachmentsMessage(InstagramMessage $message): array
    {
        $responses = [];

        foreach ($message->getAttachments() as $attachment) {
            $tempMessage = clone $message;
            $tempMessage->attach($attachment['type'], $attachment['url']);
            $responses[] = $this->instagram->send($tempMessage->toArray());
        }

        if ($message->hasText) {
            $tempMessage = clone $message;
            $tempMessage->hasAttachment = false;
            $responses[] = $this->instagram->send($tempMessage->toArray());
        }

        return array_map(fn($r) => json_decode($r->getBody()->getContents(), true), $responses);
    }
}
