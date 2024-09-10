<?php

use Illuminate\Database\Schema\Blueprint;

use Flarum\Database\Migration;

return Migration::createTable(
    'discussion_chat_ids',
    function (Blueprint $table) {
        $table->increments('id');
        $table->integer('discussion_id')->unsigned();
        $table->string('chat_id', 255);
        // created_at & updated_at
        $table->timestamps();
    }
);

