<?php

namespace PriArd\FlarumMultisite;

use Flarum\Extend;
use Flarum\Api\Serializer\DiscussionSerializer;
use PriArd\FlarumMultisite\Api\Controller\GetDiscussionMetadataController;
use PriArd\FlarumMultisite\Api\Controller\UpdateDiscussionMetadataController;
use PriArd\FlarumMultisite\Api\Controller\GetCommentSettingsController;
use PriArd\FlarumMultisite\Api\Controller\GetBulkMetadataController;
use PriArd\FlarumMultisite\Api\Controller\UpdateDiscussionStatusController;

return [
    // Frontend assets
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),
    
    // Locales
    (new Extend\Locales(__DIR__ . '/resources/locale')),
    
    // Database migrations

    // API routes
    (new Extend\Routes('api'))
        ->get('/discussions/{id}/metadata', 'discussions.metadata.show', GetDiscussionMetadataController::class)
        ->post('/discussions/{id}/metadata', 'discussions.metadata.update', UpdateDiscussionMetadataController::class)
        ->post('/discussions/{id}/status', 'discussions.status.update', UpdateDiscussionStatusController::class)
        ->get('/comment-settings', 'comments.settings', GetCommentSettingsController::class)
        ->post('/discussions/metadata/bulk', 'discussions.metadata.bulk', GetBulkMetadataController::class),

    // Add metadata to discussion serializer
    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(function (DiscussionSerializer $serializer, $discussion, $attributes) {
            // Try to get metadata safely
            try {
                $metadata = \PriArd\FlarumMultisite\Model\DiscussionMetadata::where('discussion_id', $discussion->id)->first();
                
                if ($metadata) {
                    $attributes['sourceDomain'] = $metadata->source_domain;
                    $attributes['sourcePostId'] = $metadata->source_post_id;
                    $attributes['sourcePostSlug'] = $metadata->source_post_slug;
                    $attributes['sourcePostUrl'] = $metadata->source_post_url;
                    $attributes['siteTag'] = $metadata->site_tag;
                }
            } catch (\Exception $e) {
                // Silently fail if metadata table doesn't exist or other error
            }
            
            return $attributes;
        }),

    // Settings with default values
    (new Extend\Settings())
        ->default('priard_multisite.default_character_limit', 5000)
        ->default('priard_multisite.character_limits', json_encode([
            'site1' => 5000,
            'site2' => 3000,
            'site3' => 4000
        ]))
        ->default('priard_multisite.site_tags', json_encode([
            'site1',
            'site2', 
            'site3'
        ]))
];
