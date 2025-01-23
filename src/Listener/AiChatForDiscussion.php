<?php

namespace Pixiake\AiChat\Listener;

use Pixiake\AiChat\AiChatClient;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Psr\Log\LoggerInterface;

class AiChatForDiscussion
{
    public function __construct(
        Dispatcher $events,
        SettingsRepositoryInterface $settings,
        AiChatClient $client,
        LoggerInterface $logger
    ) {
        $this->events = $events;
        $this->settings = $settings;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function handle($event): void
    {
        // Check if the extension is enabled on discussion started
        if (! $this->settings->get('pixiake-aichat.enable_on_discussion_started', true)) {
            return;
        }
        
        $this->logger->info('Discussion started');

        // Check if the user for answer is @mention  @username #userid
        $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');

        // Chek if the userId for post is the userId for answer
        $actorId = $event -> discussion -> user_id;
        if ( $actorId == $userId_for_answer ) {
            return;
        }

        // Get the discussion data
        $discussion = $event -> discussion;
        $discussionId = $discussion -> id;
        $title = $discussion -> title;

        $content = $discussion -> posts[0] -> content;
        
         
        $help_request_tag = json_decode($this -> settings -> get('pixiake-aichat.help-request-tags'), true)[0] ?? null;
        // $this->logger->info('help_request_tag: ' . $help_request_tag);

        $tech_share_tag = json_decode($this -> settings -> get('pixiake-aichat.technical-share-tags'), true)[0] ?? null;
        // $this->logger->info('tech_share_tag: ' . $tech_share_tag);

        $conversation_id = "";
        $questions[] = [
            'content' => $title . "\n\n" . $content,
            'role' => 'user'
        ];

        $prefix = "";
 
        $this -> client -> completion($questions, $conversation_id, $discussionId, $prefix, "category", $help_request_tag, $tech_share_tag);

    }
}
