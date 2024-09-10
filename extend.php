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
use Pixiake\AiChat\Listener\AiChatForTag;
use Flarum\Tags\Event\DiscussionWasTagged;
use Flarum\Post\Event\Posted;
use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Event())
        ->listen(DiscussionWasTagged::class, AiChatForTag::class)
        ->listen(Posted::class, AiChatForPost::class),
];
