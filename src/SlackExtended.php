<?php

namespace Marshmallow\SlackExtended;

use GuzzleHttp\Client;
use Laravel\Envoy\ConfigurationParser;

class SlackExtended
{
    use ConfigurationParser;

    /**
     * The webhook URL.
     *
     * @var string
     */
    public $hook;

    /**
     * The Slack channel.
     *
     * @var mixed
     */
    public $channel;

    /**
     * The options.
     *
     * @var array
     */
    public $options;

    public $success = false;

    /**
     * The task name.
     *
     * @var string
     */
    protected $task;

    /**
     * Create a new Slack instance.
     *
     * @param  string  $hook
     * @param  mixed  $channel
     * @param  string  $message
     * @param  array  $options
     * @return void
     */
    public function __construct($hook, $channel = '', $options = [], $success = false)
    {
        $this->hook = $hook;
        $this->channel = $channel;
        $this->options = $options;
        $this->success = $success;
    }

    /**
     * Create a new Slack message instance.
     *
     * @param  string  $hook
     * @param  mixed  $channel
     * @param  string  $message
     * @param  array  $options
     * @return \Laravel\Envoy\Slack
     */
    public static function make($hook, $channel = '', $options = [], $success = false)
    {
        return new static($hook, $channel, $options, $success);
    }

    /**
     * Send the Slack message.
     *
     * @return void
     */
    public function send()
    {
        $attachments = $this->createAttachments($this->options, $this->success);

        $payload = array_merge(['attachments' => $attachments, 'channel' => $this->channel]);
        $attachments['channel'] = $this->channel;
        $payload = $attachments;
        $payload =
            (new Client())->post($this->hook, [
                'json' => $payload,
            ]);
    }

    public function createAttachments($options, $success = true)
    {
        if (!$options) {
            $options = [
                'success' => false,
                'host' => 'host',
                'release' => 'release',
                'user' => 'user',
                'branch' => 'branch',
                'php_version' => 'php version',
                'github_url' => 'https://github.com',
            ];
        }

        $default_text = "Deployment for {$options['host']} ({$options['branch']})";
        $task = $this->task ?? null;
        $status = [
            'success' =>  [
                'text' => "✅ {$default_text} is successful",
                'color' => '#00c100',
                'image_url' => 'https://marshmallow.dev/storage/slack/success.png',
            ],
            'failed' => [
                'text' => "⛔️ {$default_text} failed on task '{$task}'",
                'color' => '#ff0909',
                'image_url' => 'https://marshmallow.dev/storage/slack/failed.png',
            ],
        ];

        $data = $success ? $status['success'] : $status['failed'];

        $attachment = [
            "color" => $data['color'],
            "text" => $data['text'],
            "blocks" => [
                [
                    "type" => "header",
                    "text" => [
                        "type" => "plain_text",
                        "text" => $data['text'],
                        "emoji" => true
                    ]
                ],
                [
                    "type" => "section",
                    "text" => [
                        "type" => "plain_text",
                        "text" => "With release number #{$options['release']}",
                        "emoji" => true
                    ]
                ],
                [
                    "type" => "divider"
                ],
                [
                    "type" => "section",
                    "accessory" => [
                        "type" => "image",
                        "image_url" => $data['image_url'],
                        "alt_text" => $data['image_url']
                    ],
                    "fields" => [
                        [
                            "type" => "mrkdwn",
                            "text" => "*Host:*\n" . $options['host']
                        ],
                        [
                            "type" => "mrkdwn",
                            "text" => "*Branch:*\n" . $options['branch']
                        ],
                        [
                            "type" => "mrkdwn",
                            "text" => "*PHP version:*\n" . $options['php_version']
                        ],
                        [
                            "type" => "mrkdwn",
                            "text" => "*Created by:*\n" . $options['user']
                        ]
                    ]
                ],
                [
                    "type" => "divider"
                ],
                [
                    "type" => "section",
                    "text" => [
                        "type" => "mrkdwn",
                        "text" => "View the action on GitHub"
                    ],
                    "accessory" => [
                        "type" => "button",
                        "text" => [
                            "type" => "plain_text",
                            "text" => "View",
                            "emoji" => true
                        ],
                        "url" => $options['github_url'],
                        "action_id" => "button-action"
                    ]
                ]
            ]
        ];

        return $attachment;
    }

    /**
     * Set the task for the message.
     *
     * @param  string  $task
     * @return $this
     */
    public function task($task)
    {
        $this->task = $task;

        return $this;
    }
}
