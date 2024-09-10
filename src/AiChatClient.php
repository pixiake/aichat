<?php

namespace Pixiake\AiChat;

use Exception;
use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;
use GuzzleHttp\RequestOptions as HttpOptins;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Middleware\DebugMiddleware;
use GuzzleHttp\HandlerStack;

class AiChatClient
{
    public function __construct(SettingsRepositoryInterface $settings, LoggerInterface $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;

        $apiKey = $this->settings->get('pixiake-aichat.api_key_for_chatbot');

        if (empty($apiKey)) {
            $this->logger->error('API key is not set.');
            return;
        }

        $this -> client = new HttpClient([
            'base_uri' => $this -> settings -> get('pixiake-aichat.apiserver_url_for_chatbot'),
            'headers' => [
               'Authorization' => 'Bearer ' . $apiKey,
               'Accept' => '*/*',
               'User-Agent' => 'FoxBot/1.0.0 (https://foxbot.ai)',
            ],
        ]);

        $this -> async_client = new HttpClient([
            'base_uri' => $this -> settings -> get('pixiake-aichat.async_server_url'),
            'headers' => [
               'Accept' => '*/*',
               'User-Agent' => 'FoxBot/1.0.0 (https://foxbot.ai)',
            ],
        ]);
    }

    public function newConversation(string $userId): ?array
    {
        try {
            $response = $this -> client -> get('new_conversation', [
              'query' => ['user_id' => $userId],
              ]);

            $result = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        if ($result['retcode'] !== 0) {
            $this -> logger -> error('Failed to start new conversation: ' . $result['retcode'] . '' . $result['retmsg'] . '' . $response->getBody()->getContents());
            return null;
        }

        return [
            'conversation_id' => $result['data']['id'],
            'content' => $result['data']['message'][0]['content'],
        ];
    }

    public function completion(array $messages, string $conversation_id, string $discussionId, string $prefix)
    {
        try {

            $response = $this->async_client->post('completion', [
                'json' => [
                    'prefix' => $prefix,
                    'url_for_chatbot' => $this->settings->get('pixiake-aichat.apiserver_url_for_chatbot'),
                    'api_key_for_chatbot' => $this->settings->get('pixiake-aichat.api_key_for_chatbot'),
                    "url_for_flarum" => $this->settings->get('pixiake-aichat.url_for_flarum'),
                    'api_key_for_flarum' => $this->settings->get('pixiake-aichat.api_key_for_flarum'),
                    "discussion_id" => $discussionId,
                    'req_for_aichat' => [
                        'conversation_id' => $conversation_id,
                        'messages' => $messages,
                        'stream' => false,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $this->logger->info('Completion result: ' . (string)$response->getBody());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
