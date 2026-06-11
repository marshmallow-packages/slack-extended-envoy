![alt text](https://marshmallow.dev/cdn/media/logo-red-237x46.png "marshmallow.")

# Slack Extended for Laravel Envoy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/slack-extended-envoy.svg?style=flat-square)](https://packagist.org/packages/marshmallow/slack-extended-envoy)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/slack-extended-envoy.svg?style=flat-square)](https://packagist.org/packages/marshmallow/slack-extended-envoy)

Rich Text notification for Laravel Envoy.

This package sends a richly formatted Slack message from your [Laravel Envoy](https://laravel.com/docs/envoy) deployment scripts. Instead of a plain line of text, it posts a Slack [Block Kit](https://api.slack.com/block-kit) attachment with a colored success/failure header, the release number, deployment details (host, branch, PHP version, the user who triggered the deploy) and a button linking back to GitHub.

## Installation

Install the package via Composer:

```bash
composer require marshmallow/slack-extended-envoy
```

## Usage

The entry point is the `Marshmallow\SlackExtended\SlackExtended` class. Build a message with `make()`, optionally set the failing task with `task()`, and call `send()` to post it to your Slack Incoming Webhook.

```php
use Marshmallow\SlackExtended\SlackExtended;

SlackExtended::make(
    hook: 'https://hooks.slack.com/services/XXX/YYY/ZZZ',
    channel: '#deployments',
    options: [
        'host'        => 'production',
        'release'     => '20260611120000',
        'user'        => 'stefvanesch',
        'branch'      => 'main',
        'php_version' => '8.5',
        'github_url'  => 'https://github.com/marshmallow-packages/slack-extended-envoy',
    ],
    success: true,
)->send();
```

### Constructor / `make()` arguments

| Argument  | Type     | Default | Description |
| --------- | -------- | ------- | ----------- |
| `hook`    | `string` | —       | The Slack Incoming Webhook URL to post the message to. |
| `channel` | `mixed`  | `''`    | The Slack channel the message is posted to. |
| `options` | `array`  | `[]`    | The deployment details rendered in the message (see below). When empty, a set of placeholder values is used. |
| `success` | `bool`   | `false` | Whether the deployment succeeded. Controls the header text, color (green/red) and image. |

The `options` array is rendered into the message body and recognises these keys:

| Key           | Description |
| ------------- | ----------- |
| `host`        | The host the deployment ran on. |
| `release`     | The release number, shown as `#{release}`. |
| `user`        | The user that triggered the deployment. Known handles (`LTKort`, `stefvanesch`, `lucienversendaal`) are mapped to a Slack mention; others render as "Unknown user". |
| `branch`      | The branch that was deployed. |
| `php_version` | The PHP version used for the deployment. |
| `github_url`  | The URL the "View" button links to. |

### Reporting the failing task

When a deployment fails, use `task()` to include the name of the task that failed in the message. `task()` is chainable and returns the instance:

```php
use Marshmallow\SlackExtended\SlackExtended;

SlackExtended::make($hook, $channel, $options, success: false)
    ->task('deploy')
    ->send();
```

### Using it from Envoy

Call the package from your `Envoy.blade.php` deployment hooks, for example in `@error` and `@finished`:

```blade
@finished
    @php
        \Marshmallow\SlackExtended\SlackExtended::make(
            $slackHook,
            '#deployments',
            $options,
            success: true,
        )->send();
    @endphp
@endfinished
```

## Credits

- [LTKort](https://github.com/marshmallow-packages)
- [All Contributors](https://github.com/marshmallow-packages/slack-extended-envoy/contributors)

## Security Vulnerabilities

Please report security vulnerabilities by email rather than via the public issue tracker.

## License

The MIT License. Please see the [License File](LICENSE.md) for more information.
