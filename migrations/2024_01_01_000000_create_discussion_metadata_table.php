<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (!$schema->hasTable('discussion_metadata')) {
            $schema->create('discussion_metadata', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('discussion_id')->unique();
                $table->string('source_domain', 255)->nullable();
                $table->string('source_post_id', 100)->nullable();
                $table->string('source_post_slug', 255)->nullable();
                $table->text('source_post_url')->nullable();
                $table->string('site_tag', 100)->nullable();
                $table->timestamps();
                
                // Foreign key to discussions table
                $table->foreign('discussion_id')
                    ->references('id')
                    ->on('discussions')
                    ->onDelete('cascade');
                
                // Indexes for faster lookups
                $table->index('source_domain');
                $table->index('site_tag');
                $table->index(['source_domain', 'source_post_id']);
            });
        }
    },
    
    'down' => function (Builder $schema) {
        $schema->dropIfExists('discussion_metadata');
    }
];