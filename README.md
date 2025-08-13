# Flarum Multisite Extension

[![Version](https://img.shields.io/badge/version-0.2.0-blue.svg)](https://github.com/priard/flarum-multisite-extension/releases)
[![License](https://img.shields.io/badge/license-Apache%202.0-green.svg)](LICENSE)

This extension adds multi-site support to Flarum for WordPress integration, allowing multiple WordPress sites to share a single Flarum instance for comments.

## Version History

### v0.2.0 (2025-01-13)
- Added discussion status management API endpoint
- Added post_status field to track WordPress post status
- Implemented automatic discussion locking/hiding based on post status
- Support for lock, unlock, hide, and restore operations
- Enhanced WordPress integration for draft/trash post handling

### v0.1.1 (2025-01-13)
- Changed namespace from ITTechBlog to PriArd
- Updated package name to priard/flarum-multisite
- Replaced example domains with generic placeholders
- Updated settings keys to use priard prefix

### v0.1.0 (Initial Release)
- Store metadata for discussions linking to WordPress posts
- Configure character limits per site/tag
- Bulk metadata retrieval API for efficient operations
- API endpoints for WordPress integration
- Support for multiple WordPress sites with unique tags

## Features

- Store metadata for each discussion (source domain, post ID, post URL, post status)
- Configure character limits per site/tag
- Bulk metadata retrieval for efficient notification handling
- API endpoints for WordPress integration
- Discussion status management (lock/unlock, hide/restore)
- Automatic discussion state sync with WordPress post status

## Installation

1. Clone this repository to a separate location (it will have its own Git repository)
2. Navigate to your Flarum installation directory
3. Install via Composer:

```bash
composer require priard/flarum-multisite
```

Or for local development:

```bash
composer config repositories.multisite path /path/to/flarum-multisite-extension
composer require priard/flarum-multisite:*
```

4. Run migrations:

```bash
php flarum migrate
```

5. Enable the extension in the admin panel

## API Endpoints

### Get Comment Settings

```
GET /api/comment-settings?tag=site1
```

Returns character limits and other comment settings.

### Get Discussion Metadata

```
GET /api/discussions/{id}/metadata
```

Returns metadata for a specific discussion.

### Update Discussion Metadata

```
POST /api/discussions/{id}/metadata
Authorization: Token YOUR_API_TOKEN

{
    "domain": "example-blog.com",
    "postId": "123",
    "postSlug": "example-post",
    "postUrl": "https://example-blog.com/posts/example-post",
    "siteTag": "site1"
}
```

### Bulk Get Metadata

```
POST /api/discussions/metadata/bulk

{
    "discussionIds": [1, 2, 3, 4, 5]
}
```

Returns metadata for multiple discussions at once.

### Update Discussion Status

```
POST /api/discussions/{id}/status
Authorization: Token YOUR_API_TOKEN

{
    "action": "lock|unlock|hide|restore|auto",
    "postStatus": "publish|draft|pending|trash|private"
}
```

Manage discussion visibility and commenting status. Actions:
- `lock`: Prevent new comments
- `unlock`: Allow new comments
- `hide`: Hide discussion from public view
- `restore`: Make hidden discussion visible
- `auto`: Automatically set based on WordPress post status

## Configuration

### Character Limits

Set in Flarum admin panel under extension settings:

- Default character limit: 5000
- Per-tag limits:
  - site1: 5000
  - site2: 3000
  - site3: 4000

## WordPress Plugin Integration

### Creating Discussions

The WordPress plugin should send metadata when creating discussions:

```php
// After creating discussion
$metadata_url = $flarum_url . '/api/discussions/' . $discussion_id . '/metadata';
wp_remote_post($metadata_url, [
    'headers' => [
        'Authorization' => 'Token ' . $api_token,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode([
        'domain' => parse_url(get_site_url(), PHP_URL_HOST),
        'postId' => $post_id,
        'postSlug' => $post->post_name,
        'postUrl' => get_permalink($post_id),
        'siteTag' => get_option('flarum_sync_site_tag', 'site1')
    ])
]);

### Syncing Post Status

When WordPress post status changes, update the discussion:

```php
// When post status changes
$status_url = $flarum_url . '/api/discussions/' . $discussion_id . '/status';
wp_remote_post($status_url, [
    'headers' => [
        'Authorization' => 'Token ' . $api_token,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode([
        'action' => 'auto',
        'postStatus' => $post->post_status
    ])
]);
```

## Development

To work on this extension:

1. Clone the repository
2. Run `composer install`
3. Link to your Flarum installation for testing
4. Make changes and test
5. Commit and push to the separate repository

## License

Apache License 2.0
