<?php

namespace Pixiake\AiChat\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\Post\Post;
use Flarum\User\User;

class PostPolicy extends AbstractPolicy
{
    public function canMarkAnswer(User $actor, Post $post)
    {
        // 管理员始终可以标记
        if ($actor->isAdmin()) {
            return true;
        }

        // 讨论作者可以标记
        if ($actor->id === $post -> discussion -> user_id) {
            return true;
        }

        // 有特定权限的用户可以标记
        if ($actor->hasPermission('discussion.moderate')) {
            return true;
        }

        return false;
    }
}