<?php

namespace PriArd\FlarumMultisite;

use Flarum\Extend;
use Flarum\Api\Serializer\DiscussionSerializer;
use PriArd\FlarumMultisite\Api\Controller\GetDiscussionMetadataController;
use PriArd\FlarumMultisite\Api\Controller\UpdateDiscussionMetadataController;
use PriArd\FlarumMultisite\Api\Controller\GetCommentSettingsController;
use PriArd\FlarumMultisite\Api\Controller\GetBulkMetadataController;
use PriArd\FlarumMultisite\Listener\SaveDiscussionMetadata;

return [
    // Database migrations
    (new Extend\Database())
        ->migration('default', __DIR__ . '/migrations/2024_01_01_000000_create_discussion_metadata_table.php'),

    // API routes
    (new Extend\Routes('api'))
        ->get('/discussions/{id}/metadata', 'discussions.metadata.show', GetDiscussionMetadataController::class)
        ->post('/discussions/{id}/metadata', 'discussions.metadata.update', UpdateDiscussionMetadataController::class)
        ->get('/comment-settings', 'comments.settings', GetCommentSettingsController::class)
        ->post('/discussions/metadata/bulk', 'discussions.metadata.bulk', GetBulkMetadataController::class),

    // Add metadata to discussion serializer
    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(function (DiscussionSerializer $serializer, $discussion, $attributes) {
            $metadata = $discussion->metadata;
            
            if ($metadata) {
                $attributes['sourceDomain'] = $metadata->source_domain;
                $attributes['sourcePostId'] = $metadata->source_post_id;
                $attributes['sourcePostSlug'] = $metadata->source_post_slug;
                $attributes['sourcePostUrl'] = $metadata->source_post_url;
                $attributes['siteTag'] = $metadata->site_tag;
            }
            
            return $attributes;
        }),

    // Settings for character limits
    (new Extend\Settings())
        ->default('ittechblog_multisite.default_character_limit', 5000)
        ->default('ittechblog_multisite.character_limits', json_encode([
            'ittechblog' => 5000,
            'focus' => 3000,
            'chip' => 4000
        ])),

    // Event listeners
    (new Extend\Event())
        ->listen(\Flarum\Discussion\Event\Saving::class, SaveDiscussionMetadata::class)
];
