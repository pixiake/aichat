<?php

namespace Pixiake\AiChat\Models;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Foundation\EventGeneratorTrait;

class DiscussionChatId extends AbstractModel
{
    // See https://docs.flarum.org/extend/models.html#backend-models for more information.

    protected $table = 'discussion_chat_ids';
    protected $fillable = ['discussion_id', 'chat_id'];
}
