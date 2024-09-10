<?php

namespace Pixiake\AiChat\Listener;

use Pixiake\AiChat\Models\DiscussionChatId;
use Pixiake\AiChat\AiChatClient;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;


class AiChatForPost
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

        // Check if the user for answer is @mention
        $content = $event -> post -> content;
        $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');

        $is_mention = false;

        $this->logger->info('content: ' . $content);
        if ((preg_match_all('/#(\d+)/', $content, $matches))) {
            $userIds = $matches[1];
            foreach ($userIds as $userId) {
                if ($userId == $userId_for_answer) {
                    $is_mention = true;
                    break;
                }
            }
        }

        // Check if the tag is enabled on discussion
        $enabledTagIds = $this->settings->get('pixiake-aichat.enabled-tags', '[]');

        $discussion = $event-> post ->discussion;

        if ($is_mention == false) {
            if ($enabledTagIds = json_decode($enabledTagIds, true)) {

                $tagIds = Arr::pluck($discussion->tags, 'id');

                if (! array_intersect($enabledTagIds, $tagIds)) {
                    return;
                }
            }
        }

        // Chek if the userId for post is the userId for answer
        $actorId = $event -> post -> user_id;
        if ( $actorId == $userId_for_answer ) {
            return;
        }

        // Get the post data
        $discussionId = $event -> post -> discussion_id;
        $title = $event -> post -> discussion -> title;
        $posts = $event -> post -> discussion -> posts;

        // The user for chatbot
        $user_server = $this -> settings -> get('pixiake-aichat.user_for_chatbot');

        // check if disscussion has been exited
        $discussionChatIdRecord = DiscussionChatId::where('discussion_id', $discussionId)->first();

        $conversation_id = "";

        if (! $discussionChatIdRecord) {
            $conversation = $this -> client -> newConversation($user_server);
                if ( $conversation == null ) {
                    return;
                }
            DiscussionChatId::create([
                'discussion_id' => $discussionId,
                'chat_id' => $conversation['conversation_id'],
            ]);
            $conversation_id = $conversation['conversation_id'];
        } else {
            $conversation_id = $discussionChatIdRecord -> chat_id;
        }


        $questions = [];

        if ( $actorId != $userId_for_answer ) {
            if ($is_mention == false) {
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
            } else {
                $questions[] = [
                    'content' => $content,
                    'role' => 'user'
                ];
            }
        } else {
           return;
        }

        $prefix = "";
        if (count($posts) == 1) {
           $prefix = $conversation['content'] . "\n\n";
        }

        if (count($posts) > 15) {
           return;
        } else {
            $this -> client -> completion($questions, $conversation_id, $discussionId, $prefix);
        }
    }
}
