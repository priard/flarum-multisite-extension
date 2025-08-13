# Flarum Multisite Extension

[![Version](https://img.shields.io/badge/version-0.3.1-blue.svg)](https://github.com/priard/flarum-multisite-extension/releases)
[![License](https://img.shields.io/badge/license-Apache%202.0-green.svg)](LICENSE)

This extension adds multi-site support to Flarum for WordPress integration, allowing multiple WordPress sites to share a single Flarum instance for comments.

## Version History

### v0.3.1 (2025-01-13)
- Fixed JavaScript admin panel error
- Added pre-built admin.js to avoid build requirements
- Simplified admin settings implementation

### v0.3.0 (2025-01-13)
- Added admin panel for configuring settings
- Added UI for managing character limits per site
- Added localization support (English)
- Added JavaScript build configuration
- Enhanced installation and update documentation

### v0.2.2 (2025-01-13)
- Fixed migration error with hasColumn check scope
- Corrected column existence verification in migration

### v0.2.1 (2025-01-13)
- Fixed migration error by removing deprecated Extend\Database usage
- Added separate migration file for post_status field
- Improved compatibility with Flarum's automatic migration detection

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
- Configure character limits per site/tag via admin panel
- Bulk metadata retrieval for efficient notification handling
- API endpoints for WordPress integration
- Discussion status management (lock/unlock, hide/restore)
- Automatic discussion state sync with WordPress post status
- Admin panel for managing settings and character limits

## Installation

### Production Installation

```bash
cd /path/to/flarum
composer require priard/flarum-multisite
php flarum migrate
php flarum cache:clear
```

Then enable the extension in the admin panel.

### Local Development Installation

For development and testing:

```bash
# Clone the extension repository
git clone https://github.com/priard/flarum-multisite-extension.git
cd flarum-multisite-extension
composer install

# Link to your local Flarum installation
cd /path/to/flarum
composer config repositories.multisite path /path/to/flarum-multisite-extension
composer require priard/flarum-multisite:*

# Run migrations and clear cache
php flarum migrate
php flarum cache:clear
```

Enable the extension in the admin panel.

### Updating the Extension

#### Production Update

```bash
cd /path/to/flarum
composer update priard/flarum-multisite
php flarum migrate
php flarum cache:clear
```

#### Development Update

```bash
# Pull latest changes
cd /path/to/flarum-multisite-extension
git pull origin main
composer install

# Clear Flarum cache
cd /path/to/flarum
php flarum cache:clear
```

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

### Admin Settings

Configure the extension in the Flarum admin panel:

1. Navigate to **Admin Panel → Extensions**
2. Find **Multisite Comment System** and click settings
3. Configure the following:

#### Character Limits

Set maximum character limits for comments per site:

- **Default limit**: 5000 characters (applies to all sites without specific limits)
- **Per-site limits**: Configure different limits for each site tag:
  ```json
  {
    "site1": 5000,
    "site2": 3000,
    "site3": 4000
  }
  ```

#### Site Tags

Each WordPress site should use a unique tag to identify its discussions:
- Configured in WordPress plugin settings
- Used to apply site-specific character limits
- Helps organize discussions by source site

### Settings Storage

Settings are stored with the following keys:
- `priard_multisite.default_character_limit` - Default character limit
- `priard_multisite.character_limits` - JSON object with per-site limits

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

### Setup Development Environment

```bash
# Clone the repository
git clone https://github.com/priard/flarum-multisite-extension.git
cd flarum-multisite-extension

# Install PHP dependencies
composer install

# Install JavaScript dependencies (if modifying admin panel)
npm install
```

### Building JavaScript Assets

**Note:** The extension comes with pre-built JavaScript assets in `js/dist/admin.js`, so building is not required unless you modify the source files.

#### How Flarum Assets Work

1. **Source files** are in `js/src/admin/index.js` (ES6+ modern JavaScript)
2. **Compiled files** go to `js/dist/admin.js` (browser-compatible JavaScript)
3. **Flarum loads** the compiled file specified in `extend.php`

#### Building Assets Step-by-Step

If you modify the admin panel JavaScript:

```bash
# 1. Install dependencies (first time only)
npm install

# 2. Build for development (with source maps)
npm run dev

# 3. Build for production (minified)
npm run build

# 4. Watch mode (auto-rebuild on changes)
npm run dev -- --watch
```

#### When to Build

- **NOT needed:** When installing the extension (pre-built files included)
- **NEEDED:** When modifying files in `js/src/`
- **NEEDED:** When adding new JavaScript functionality

#### Build Output

After building, you'll see:
- `js/dist/admin.js` - Compiled admin panel code
- `js/dist/admin.js.map` - Source map for debugging (dev build only)

#### Troubleshooting Build Issues

```bash
# Clear npm cache if build fails
npm cache clean --force
rm -rf node_modules
npm install

# Verify webpack config
npm run build -- --display-error-details
```

### Testing Changes

1. Link extension to your local Flarum
2. Build assets if modified: `npm run build`
3. Clear cache after changes: `php flarum cache:clear`
4. Test in browser (force refresh with Ctrl+F5)
5. Check logs for errors: `tail -f storage/logs/flarum.log`
6. Check browser console for JavaScript errors (F12)

### Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes
4. Run `npm run build` if you modified JS
5. Commit changes: `git commit -am 'Add new feature'`
6. Push to branch: `git push origin feature/your-feature`
7. Submit a pull request

## License

Apache License 2.0
