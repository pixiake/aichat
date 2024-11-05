<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('posts', function (Blueprint $table) {
            $table->boolean('is_marked_correct')->default(false);
            $table->boolean('is_marked_wrong')->default(false);
        });
    },
    'down' => function (Builder $schema) {
        $schema->table('posts', function (Blueprint $table) {
            $table->dropColumn('is_marked_correct');
            $table->dropColumn('is_marked_wrong');
        });
    }
];

