<?php

namespace NotificationChannels\Instagram\Exceptions;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

class CouldNotSendNotification extends \Exception
{
    public static function instagramRespondedWithAnExceptionError($response)
    {
        $error = Arr::get($response, 'error');

        return new static("The communication with endpoint failed. Reason: {$error}");
    }

    public static function instagramRespondedWithAnError(ClientException $exception): self
    {
        if ($exception->hasResponse()) {
            $result = json_decode($exception->getResponse()->getBody(), false);

            return new static("Facebook responded with an error `{$result->error->code} - {$result->error->type} {$result->error->message}`");
        }

        return new static('Facebook responded with an error');
    }

    /**
     * Thrown when there's no page token provided.
     *
     * @return static
     */
    public static function instagramPageTokenNotProvided(string $message): self
    {
        return new static($message);
    }

    /**
     * Thrown when we're unable to communicate with Instagram.
     *
     * @return static
     */
    public static function couldNotCommunicateWithInstagram(\Exception $exception): self
    {
        return new static('The communication with Instagram failed. Reason: '.$exception->getMessage());
    }
}
