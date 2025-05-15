# Instagram Notifications Channel for Laravel

[![License](https://img.shields.io/packagist/l/ka4ivan/laravel-notification-channel-instagram.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-notification-channel-instagram)
[![Build Status](https://img.shields.io/github/stars/ka4ivan/laravel-notification-channel-instagram.svg?style=for-the-badge)](https://github.com/ka4ivan/laravel-notification-channel-instagram)
[![Latest Stable Version](https://img.shields.io/packagist/v/ka4ivan/laravel-notification-channel-instagram.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-notification-channel-instagram)
[![Total Downloads](https://img.shields.io/packagist/dt/ka4ivan/laravel-notification-channel-instagram.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-notification-channel-instagram)

This package makes it easy to send notifications using the [Instagram Messenger](https://developers.facebook.com/docs/instagram-platform) with Laravel.
 
## Contents

- [Installation](#installation)
    - [Setting up your Instagram Bot](#setting-up-your-instagram-bot)
      - [Get Profile ID](#get-profile-id)
      - [Set config](#set-config)
      - [Set start buttons](#set-start-buttons)
- [Usage](#usage)
    - [Available Message methods](#available-message-methods)
- [Contributing](#contributing)
- [License](#license)


## Installation

You can install the package via composer:

``` bash
composer require ka4ivan/laravel-notification-channel-instagram
```

## Setting up your Instagram Bot

### Get Profile ID
``` bash
curl -X GET "https://graph.instagram.com/me?fields=id,username&access_token=ACCESS_TOKEN"
```

### Set config
Next we need to add tokens to our Laravel configurations. Create a new Instagram section inside `config/services.php` and place the page token there:

```php
// config/services.php
'instagram' => [
    'version' => env('INSTAGRAM_VERSION', '22.0'),
    'access_token' => env('INSTAGRAM_ACCESS_TOKEN', ''),
    'profile_id' => env('INSTAGRAM_PROFILE_ID', ''),
    'start_buttons' => [
        [
            'question' => 'Start',
            'payload' => 'start',
        ],
    ],
],
```

### Set start buttons

Run the command to set the start buttons
``` bash
php artisan instagram:set-start-buttons
```

Possible options for the command
```php
{--access_token= : Instagram access token}
{--profile_id= : Instagram profile ID}
{--api_version= : Instagram API version (default from config)}'
```

This command will add the start buttons that appear when entering the chat for the first time

![image](https://github.com/user-attachments/assets/c4a1af25-df9f-4170-973c-c099678955a0)

## Usage

Let's take an invoice-paid-notification as an example.
You can now use the Instagram channel in your `via()` method, inside the InvoicePaid class. The `to($userId)` method defines the Instagram user, you want to send the notification to.

```php
use NotificationChannels\Instagram\InstagramChannel;
use NotificationChannels\Instagram\InstagramMessage;

use Illuminate\Notifications\Notification;

class ChannelConnected extends Notification
{
    public function via($notifiable)
    {
        return [InstagramChannel::class];
    }

    public function toInstagram($notifiable)
    {

        return InstagramMessage::create()
            ->to($notifiable->instagram_id) // Optional
            ->text('Congratulations, the communication channel is connected');
    }
}
```

The notification will be sent from your Instagram page, whose page token you have configured earlier. Here's a screenshot preview of the notification inside the chat window.

![image](https://github.com/user-attachments/assets/30cfd446-fd5f-4dd4-9705-82a820bf7295)

#### Message Examples

##### Basic Text Message

```php
return InstagramMessage::create('You have just paid your monthly fee! Thanks');
```

##### Message With Attachment

```php
return InstagramMessage::create()
    ->attach(AttachmentType::IMAGE, url('images/'.$this->product->id))
    ->to($notifiable->instagram_id);
```

##### Message With Buttons

```php
return InstagramMessage::create()
    ->to($notifiable->instagram_id)
    ->text('How can we help?')
    ->buttons([
        Button::create('View Website', $url)->isTypeWebUrl(),
        Button::create('Start Chatting', ['user' => $this->user->id])->isTypePostback() // Custom payload sent back to your server
    ]);
```

### Routing a message

You can either send the notification by providing with the page-scoped user id of the recipient to the `to($recipientId)` (IGSID) method like shown in the above example or add a `routeNotificationForInstagram()` method in your notifiable model:

```php
/**
 * Route notifications for the Instagram channel.
 *
 * @return int
 */
public function routeNotificationForInstagram()
{
    return $this->instagram_id;
}
```

### Available Message methods
- `to($recipientId)`: (string) User (recipient) Instagram ID (IGSID).
- `text('')`: (string) Notification message.
- `attach($attachmentType, $url)`: (AttachmentType, string) An attachment type (IMAGE, AUDIO, VIDEO) and the url of this attachment.
- `buttons($buttons = [])`: (array) An array of "Call to Action" buttons (Created using NotificationChannels\Instagram\Components\Button::create()). You can add up to 3 buttons of one of the following types: web_url or postback. See Button methods below for more details.


### Available Button methods
- `title('')`: (string) Button Title.
- `data('')`: (string) Button Data - It can be a web url or postback data.
- `type('')`: (string) Button Type - `web_url` or `postback`. Use `ButtonType` enumerator for guaranteeing valid values
- `isTypeWebUrl()`: Helper method to create a `web_url` type button.
- `isTypePostback()`: Helper method to create a `postback` type button.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
