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

        // Check if the tag is enabled on discussion
        $enabledTagIds = $this->settings->get('pixiake-aichat.enabled-tags', '[]');

        $discussion = $event->discussion;
        $this->logger -> info('discussion_id: ' .$event->discussion->id);
        if ($enabledTagIds = json_decode($enabledTagIds, true)) {

            $tagIds = Arr::pluck($discussion->tags, 'id');

            if (! array_intersect($enabledTagIds, $tagIds)) {
                return;
            }
        }

        $this->logger -> info('discussion_id: ' .$event->discussion->id);

        // Get the discussion data
        $discussionId = $event -> discussion -> id;
        $title = $event -> discussion -> title;
        $posts = $event -> discussion -> posts;
        $postNumber = count($posts);

        $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');
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

        $this->logger -> info('discussion_id: ' . $discussionId);

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

        $this->logger -> info('questions: ' . count($posts));
        $this->logger -> info($questions);

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
