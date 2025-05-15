<?php

namespace NotificationChannels\Instagram;

use NotificationChannels\Instagram\Enums\AttachmentType;
use NotificationChannels\Instagram\Exceptions\CouldNotCreateMessage;

class InstagramMessage implements \JsonSerializable
{
    /** @var string Recipient's ID. */
    public $recipientId;

    /** @var string Notification Text. */
    public $text;

    /** @var bool */
    protected $hasText = false;

    /** @var string Attachment Type. Defaults to File */
    public $attachmentType = AttachmentType::IMAGE;

    /** @var string Attachment URL */
    public $attachmentUrl;

    /** @var bool */
    protected $hasAttachment = false;

    /** @var array Call to Action Buttons */
    protected $buttons = [];

    /**
     * @throws CouldNotCreateMessage
     */
    public function __construct(string $text = '')
    {
        if ('' !== $text) {
            $this->text($text);
        }
    }

    /**
     * @return static
     * @throws CouldNotCreateMessage
     */
    public static function create(string $text = ''): self
    {
        return new static($text);
    }

    /**
     * Notification text.
     *
     * @return $this
     * @throws CouldNotCreateMessage
     */
    public function text(string $text): self
    {
        if (mb_strlen($text) > 1000) {
            throw CouldNotCreateMessage::textTooLong();
        }

        $this->text = $text;
        $this->hasText = true;

        return $this;
    }

    /**
     * Recipient's Instagram ID.
     *
     * @return $this
     */
    public function to($recipientId): self
    {
        $this->recipientId = $recipientId;

        return $this;
    }

    /**
     * Determine if user id is not given.
     */
    public function toNotGiven(): bool
    {
        return !isset($this->recipientId);
    }

    /**
     * Add Attachment.
     *
     * @return $this
     *
     * @throws CouldNotCreateMessage
     */
    public function attach(string $attachmentType, string $url): self
    {
        $attachmentTypes = [
            AttachmentType::IMAGE,
            AttachmentType::VIDEO,
            AttachmentType::AUDIO,
        ];

        if (!in_array($attachmentType, $attachmentTypes)) {
            throw CouldNotCreateMessage::invalidAttachmentType();
        }

        if (blank($url)) {
            throw CouldNotCreateMessage::urlNotProvided();
        }

        $this->attachmentType = $attachmentType;
        $this->attachmentUrl = $url;
        $this->hasAttachment = true;

        return $this;
    }

    /**
     * Add up to 3 call to action buttons.
     *
     * @return $this
     *
     * @throws CouldNotCreateMessage
     */
    public function buttons(array $buttons = []): self
    {
        if (count($buttons) > 3) {
            throw CouldNotCreateMessage::messageButtonsLimitExceeded();
        }

        $this->buttons = $buttons;

        return $this;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     *
     * @throws CouldNotCreateMessage
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns message payload for JSON conversion.
     *
     * @throws CouldNotCreateMessage
     */
    public function toArray(): array
    {
        if ($this->hasAttachment) {
            return $this->attachmentMessageToArray();
        }

        if ($this->hasText) {
            return $this->textMessageToArray();
        }

        throw CouldNotCreateMessage::dataNotProvided();
    }

    /**
     * Returns message for simple text message.
     */
    protected function textMessageToArray(): array
    {
        $message = [];
        $message['recipient']['id'] = $this->recipientId;
        $message['message']['text'] = $this->text;

        return $message;
    }

    /**
     * Returns message for attachment message.
     */
    protected function attachmentMessageToArray(): array
    {
        $message = [];
        $message['recipient']['id'] = $this->recipientId;
        $message['message']['attachment']['type'] = $this->attachmentType;
        $message['message']['attachment']['payload']['url'] = $this->attachmentUrl;

        return $message;
    }
}
