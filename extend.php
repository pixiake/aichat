<?php

/*
 * This file is part of pixiake/aichat.
 *
 * Copyright (c) 2024 pixiake.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Pixiake\AiChat;

use Pixiake\AiChat\Listener\AiChatForPost;
use Flarum\Post\Event\Posted;
use Flarum\Extend;
use Flarum\Discussion\Discussion;
use Pixiake\AiChat\Api\Controller\MarkDiscussionController;
use Pixiake\AiChat\Api\Controller\MarkPostController;
use Pixiake\AiChat\Access\DiscussionPolicy;
use Pixiake\AiChat\Access\PostPolicy;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Post\Post;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),
    
    (new Extend\Routes('api'))
        ->post(
            '/ai-chat/v1alpha1/mark-discussion', 
            'ai-chat.v1alpha1.mark.discussion', 
            MarkDiscussionController::class
        )
        ->post(
            '/ai-chat/v1alpha1/mark-post',
            'ai-chat.v1alpha1.mark.post',
            MarkPostController::class
        ),
        
    (new Extend\Policy())
        ->modelPolicy(Discussion::class, DiscussionPolicy::class)
        ->modelPolicy(Post::class, PostPolicy::class),

    (new Extend\Settings())
        ->serializeToForum('botUserId', 'pixiake-aichat.user_for_answer')
        ->serializeToForum('needLearnTags', 'pixiake-aichat.need-to-learn-tags')
        ->serializeToForum('learnedTags', 'pixiake-aichat.already-learned-tags'),

    // 注册API序列化器
    (new Extend\ApiSerializer(PostSerializer::class))
        ->attributes(function (PostSerializer $serializer, Post $post, array $attributes) {
            // $attributes['canMarkAnswer'] = $serializer->getActor()->can('markAnswer', $post);
            $attributes['isMarkedCorrect'] = (bool) $post->is_marked_correct;
            $attributes['isMarkedWrong'] = (bool) $post->is_marked_wrong;
            return $attributes;
        }),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(function ($serializer, $model, $attributes) {
            // 解析标签ID为数组
            if (isset($attributes['needLearnTags'])) {
                $attributes['needLearnTags'] = json_decode($attributes['needLearnTags']) ?: [];
            }
            if (isset($attributes['learnedTags'])) {
                $attributes['learnedTags'] = json_decode($attributes['learnedTags']) ?: [];
            }
            
            return $attributes;
        }),


    (new Extend\Event())
        ->listen(Posted::class, AiChatForPost::class),
];
