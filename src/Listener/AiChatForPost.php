<?php

namespace Pixiake\AiChat\Listener;

use Pixiake\AiChat\Models\DiscussionChatId;
use Pixiake\AiChat\AiChatClient;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Flarum\Post\Post;
use Illuminate\Contracts\Events\Dispatcher;
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

        // Check if the user for answer is @mention  @username #userid
        $content = $event -> post -> content;
        $userId_for_answer = $this->settings->get('pixiake-aichat.user_for_answer');

        $is_mention = false;

        if ((preg_match_all('/#(\d+)/', $content, $matches))) {
            $userIds = $matches[1];
            foreach ($userIds as $userId) {
                if ($userId == $userId_for_answer) {
                    $is_mention = true;
                    break;
                }
            }
        }


        if (!$is_mention) {
            // 先获取目标用户信息
            $target_user = User::find($userId_for_answer);
            if (!$target_user) {
                return;
            }

            $target_mention = '@' . $target_user->display_name . ' '; // 加空格确保完整匹配
            if ( strpos($content . ' ', $target_mention) !== false ) {
                $is_mention = true;
            }
        }


        if (!$is_mention && preg_match_all('/@"[^"]+?"#p(\d+)/', $content, $matches)) {
            foreach ($matches[1] as $postId) {
                $post = Post::find($postId);
                if ($post && $post->user_id == $userId_for_answer) {
                    $is_mention = true;
                    break;
                }
            }
        }


        if (!$is_mention) {
            return;
        }

        $discussion = $event -> post -> discussion;
        // Check if the tag is enabled on discussion
        // $enabledTagIds = $this->settings->get('pixiake-aichat.enabled-tags', '[]');

        // if ($enabledTagIds = json_decode($enabledTagIds, true)) {
        //     $tagIds = Arr::pluck($discussion->tags, 'id');
        //     foreach ($enabledTagIds as $enabledTagId) {
        //         if ( !$discussion->tags || ! in_array($enabledTagId, $tagIds)) {
        //             $discussion -> tags() -> attach($enabledTagId);
        //         }
        //     }
        // }

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

        $questions[] = [
            'content' => $title,
            'role' => 'user'
        ];

        if ( $actorId != $userId_for_answer ) {
            foreach ($posts as $post) {
                $role = $post->user_id == $userId_for_answer ? 'assistant' : 'user';
                $content = "";
                if (is_string($post -> content) && ! $post -> is_marked_wrong) {
                    $content = $post->content;
                } else {
                    continue;
                }
                $questions[] = [
                    'content' => $content,
                    'role' => $role
                ];
            }
        } else {
           return;
        }

        $prefix = "";
        if (count($posts) == 1) {
           $prefix = $conversation['content'] . "\n\n";
        }

        if (count($posts) > 20) {
           return;
        } else {
           $this -> client -> completion($questions, $conversation_id, $discussionId, $prefix);
        }
    }
}
