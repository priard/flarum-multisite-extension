<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if ($schema->hasTable('discussion_metadata')) {
            $schema->table('discussion_metadata', function (Blueprint $table) {
                if (!$schema->hasColumn('discussion_metadata', 'post_status')) {
                    $table->string('post_status', 50)->nullable()->default('publish')->after('site_tag');
                }
            });
        }
    },
    
    'down' => function (Builder $schema) {
        if ($schema->hasTable('discussion_metadata')) {
            $schema->table('discussion_metadata', function (Blueprint $table) {
                $table->dropColumn('post_status');
            });
        }
    }
];