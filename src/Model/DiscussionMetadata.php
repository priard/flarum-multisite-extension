<?php

namespace PriArd\FlarumMultisite\Model;

use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;

class DiscussionMetadata extends AbstractModel
{
    protected $table = 'discussion_metadata';
    
    protected $fillable = [
        'discussion_id',
        'source_domain',
        'source_post_id',
        'source_post_slug',
        'source_post_url',
        'site_tag',
        'post_status'
    ];
    
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * Get the discussion this metadata belongs to.
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
    
    /**
     * Create or update metadata for a discussion.
     */
    public static function updateForDiscussion($discussionId, array $data)
    {
        return static::updateOrCreate(
            ['discussion_id' => $discussionId],
            $data
        );
    }
    
    /**
     * Get metadata for multiple discussions.
     */
    public static function getForDiscussions(array $discussionIds)
    {
        return static::whereIn('discussion_id', $discussionIds)
            ->get()
            ->keyBy('discussion_id');
    }
}
