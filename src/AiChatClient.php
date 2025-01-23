<?php

namespace Pixiake\AiChat;

use Exception;
use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

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
        $this -> webhook_client = new HttpClient([
            'base_uri' => $this -> settings -> get('pixiake-aichat.we_webhook_url'),
            'headers' => [
               'Content-Type' => 'application/json',
               'User-Agent' => 'FoxBot/1.0.0 (https://foxbot.ai)',
            ],
        ]);
        $this -> async_client = new HttpClient([
            'base_uri' => $this -> settings -> get('pixiake-aichat.apiserver_url_for_chatbot'),
            'headers' => [
               'Authorization' => 'Bearer ' . $apiKey,
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
        

        if ($result['code'] !== 0) {
            $this -> logger -> error('Failed to start new conversation: ' . $result['retcode'] . '' . $result['retmsg'] . '' . $response->getBody()->getContents());
            return null;
        }

        return [
            'conversation_id' => $result['data']['id'],
            'content' => $result['data']['message'][0]['content'],
        ];
    }

    public function completion(array $messages, string $conversation_id, string $discussionId, string $prefix, string $action, string $help_request_tag, string $tech_share_tag)
    {
        try {
            
            $response = $this->async_client->post('completion', [
                'json' => [
                    'action' => $action,
                    'prefix' => $prefix,
                    "discussion_id" => $discussionId,
                    'req_for_aichat' => [
                        'conversation_id' => $conversation_id,
                        'messages' => $messages,
                        'stream' => false,
                    ],
                    "tag_id_for_tech_share" => $tech_share_tag,
                    "tag_id_for_question" => $help_request_tag,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $this->logger->info('Completion result: ' . (string)$response->getBody());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function self_learning(string $discussionId, array $messages, string $action)
    {
        try {

            $response = $this->async_client->post('self-learning', [
                'json' => [
                    'action' => $action,
                    'knowledge_id' => $this->settings->get('pixiake-aichat.knowledge_base'),
                    "discussion_id" => $discussionId,
                    'req_for_aichat' => [
                        'conversation_id' => "",
                        'messages' => $messages,
                        'stream' => false,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $this->logger->info('self learning result: ' . (string)$response->getBody());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function webhook(array $content)
    {
        try {
            $response = $this -> webhook_client -> post('', [
                'json' => $content,
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            $this->logger->info('webhook result: ' . (string)$response->getBody());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
}
