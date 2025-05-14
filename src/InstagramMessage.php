<?php

namespace NotificationChannels\Instagram;

use NotificationChannels\Instagram\Exceptions\CouldNotCreateMessage;

class InstagramMessage
{
    /** @var string Recipient's ID. */
    public $recipientId;

    /** @var string Notification Text. */
    public $text;

    /** @var bool */
    protected $hasText = false;

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

    public function to($recipientId): self
    {
        $this->recipientId = $recipientId;

        return $this;
    }

    /**
     * Returns message payload for JSON conversion.
     *
     * @throws CouldNotCreateMessage
     */
    public function toArray(): array
    {
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

}
