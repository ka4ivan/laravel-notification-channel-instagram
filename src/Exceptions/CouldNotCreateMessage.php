<?php

namespace NotificationChannels\Instagram\Exceptions;

class CouldNotCreateMessage extends \Exception
{
    /**
     * Thrown when the message text is not provided.
     *
     * @return static
     */
    public static function textTooLong(): self
    {
        return new static('Message text is too long, A 1000 character limited string should be provided.');
    }

    /**
     * Thrown when there is no user id or phone number provided.
     *
     * @return static
     */
    public static function recipientNotProvided(): self
    {
        return new static('Instagram notification recipient ID or Phone Number was not provided. Please refer usage docs.');
    }

    /**
     * Thrown when enough data is not provided.
     *
     * @return static
     */
    public static function dataNotProvided(): self
    {
        return new static('Your message was missing critical information');
    }
}
