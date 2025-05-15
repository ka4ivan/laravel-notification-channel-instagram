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

    /**
     * Thrown when invalid attachment type provided.
     *
     * @return static
     */
    public static function invalidAttachmentType(): self
    {
        return new static('Attachment Type provided is invalid.');
    }

    /**
     * Thrown when a URl should be provided for an attachment.
     *
     * @return static
     */
    public static function urlNotProvided(): self
    {
        return new static('You have not provided a Url for an attachment');
    }

    /**
     * Thrown when number of buttons in message exceeds.
     *
     * @return static
     */
    public static function messageButtonsLimitExceeded(): self
    {
        return new static('You cannot add more than 3 buttons in 1 notification message.');
    }
}
