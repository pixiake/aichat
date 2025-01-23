<?php

namespace Pixiake\AiChat\Listener;

use Pixiake\AiChat\Models\DiscussionChatId;
use Pixiake\AiChat\AiChatClient;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;

class AiChatForTag
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
       
        $discussion = $event->discussion;
        $oldTags = $event->oldTags;
        $newTags = $discussion->tags;
        // 获取新增的标签
        $addedTags = $newTags->diff($oldTags);

        $helpRequestTagIds = $this->settings->get('pixiake-aichat.help-request-tags', '[]');

        if ($helpRequestTagIds = json_decode($helpRequestTagIds, true)) {
            $tagIds = Arr::pluck($addedTags, 'id');
            if (array_intersect($helpRequestTagIds, $tagIds)) {
                // Get the discussion data
                $discussionId = $discussion -> id;
                $title = $discussion -> title;
                $posts = $discussion -> posts;
                $postNumber = count($posts);
        
                $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');
                $user_server = $this -> settings -> get('pixiake-aichat.user_for_chatbot');
        
                $conversation_id = "";
        
                $questions = [];
                foreach ($posts as $post) {
                    $role = $post->user_id == $userId_for_answer ? 'assistant' : 'user';
        
                    $content = "";
                    if (is_string($post -> content)) {
                        $content = $post->content;
                    } else {
                        continue;
                    }
        
                    if ( $post->postNumber == 0 ) {
                        $content = $title . "\n" . $post -> content;
                    }
        
                    $questions[] = [
                        'content' => $content,
                        'role' => $role
                    ];
                }
        
                $prefix = "";
        
                $help_request_tag = json_decode($this -> settings -> get('pixiake-aichat.help-request-tags'), true)[0] ?? null;
        
                $tech_share_tag = json_decode($this -> settings -> get('pixiake-aichat.technical-share-tags'), true)[0] ?? null;
        
                $this -> client -> completion($questions, $conversation_id, $discussionId, $prefix, "support", $help_request_tag, $tech_share_tag);
           }
        }

        $needTolearnTagIds = $this->settings->get('pixiake-aichat.need-to-learn-tags', '[]');
        if ($needTolearnTagIds = json_decode($needTolearnTagIds, true)) {
            $tagIds = Arr::pluck($addedTags, 'id');
            if (array_intersect($needTolearnTagIds, $tagIds)) {
                $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');

                $questions[] = [
                    'content' => $discussion -> title,
                    'role' => 'user'
                ];
        
                foreach ($discussion -> posts as $post) {
                    $role = $post->user_id == $userId_for_answer ? 'assistant' : 'user';
                    $content = "";

                    if (is_string($post -> content) && $post->user_id != $userId_for_answer) {
                        $content = $post->content;
                    } else {
                        continue;
                    }
                    $questions[] = [
                        'content' => $content,
                        'role' => $role
                    ];
                }
                
                $this->client->self_learning($discussion -> id, $questions, "upload");
            }
        }
    }
}
