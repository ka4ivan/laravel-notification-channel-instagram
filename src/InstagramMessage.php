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
    public $hasText = false;

    /** @var string Attachment Type. Defaults to File */
    public $attachmentType = AttachmentType::IMAGE;

    /** @var string Attachment URL */
    public $attachmentUrl;

    /** @var array Attachment URL`s */
    public array $attachments = [];

    /** @var bool */
    public $hasAttachment = false;

    /** @var array Call to Action Buttons */
    public $buttons = [];

    /**
     * Access token for authenticating with the Instagram API.
     *
     * @var string
     */
    protected ?string $accessToken = null;

    /**
     * Instagram profile ID associated with the authenticated user.
     *
     * curl -X GET "https://graph.instagram.com/me?fields=id,username&access_token=ACCESS_TOKEN"
     *
     * @var string
     */
    protected ?string $profileId = null;

    /**
     * Instagram API version (e.g., '22.0')
     *
     * @var string
     */
    protected ?string $apiVersion = null;

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
     * Set the access token used for authenticating API requests.
     *
     * @param string $accessToken Instagram access token.
     * @return $this
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the Instagram profile ID for API requests.
     *
     * @param string $profileId Instagram profile ID.
     * @return $this
     */
    public function setProfileId(string $profileId): self
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * Set Default Graph API Version.
     *
     * @param string $apiVersion
     * @return $this
     */
    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return string|null
     */
    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    /**
     * @return string|null
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
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
     * Add Attachments.
     *
     * @return $this
     */
    public function attachMany(string $type, array $urls): self
    {
        foreach ($urls as $url) {
            $this->attachments[] = [
                'type' => $type,
                'url' => $url,
            ];
        }

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
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
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
            // check if it has buttons
            if (count($this->buttons) > 0) {
                return $this->buttonMessageToArray();
            }

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

    /**
     * Returns message for Button Template message.
     */
    protected function buttonMessageToArray(): array
    {
        $message = [];
        $message['recipient']['id'] = $this->recipientId;
        $message['message']['attachment']['type'] = 'template';
        $message['message']['attachment']['payload']['template_type'] = 'button';
        $message['message']['attachment']['payload']['text'] = $this->text;
        $message['message']['attachment']['payload']['buttons'] = $this->buttons;

        return $message;
    }
}
