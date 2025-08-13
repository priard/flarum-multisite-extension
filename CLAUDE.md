# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Flarum extension** that provides multi-site support with WordPress integration. It allows multiple WordPress sites to share a single Flarum instance for comments, creating a centralized commenting system.

## Key Commands

### Installation & Setup
```bash
# Install dependencies
composer install

# Link extension to local Flarum installation for development
composer config repositories.multisite path /path/to/flarum-multisite-extension
composer require ittechblog/flarum-multisite:*

# Run database migrations after installation
php flarum migrate

# Clear Flarum cache after changes
php flarum cache:clear
```

### Development
```bash
# No build process required - pure PHP extension
# Make changes directly to PHP files
# Test changes in linked Flarum installation
```

## Architecture & Key Components

### Extension Entry Point
- `extend.php` - Configures routes, models, and controllers for the extension

### Database Model
- `src/Model/DiscussionMetadata.php` - Links Flarum discussions to WordPress posts
- Table: `discussion_metadata` - Stores source_domain, post_id, post_slug, post_url, site_tag

### API Controllers (src/Api/Controller/)
1. **GetCommentSettingsController** - Returns character limits per site tag
2. **GetDiscussionMetadataController** - Gets metadata for specific discussion
3. **UpdateDiscussionMetadataController** - Updates discussion metadata (requires API token)
4. **GetBulkMetadataController** - Bulk retrieval of metadata for multiple discussions

### API Endpoints
- `GET /api/comment-settings?tag={site_tag}` - Character limits
- `GET /api/discussions/{id}/metadata` - Single discussion metadata
- `POST /api/discussions/{id}/metadata` - Update metadata (requires auth)
- `POST /api/discussions/metadata/bulk` - Bulk metadata retrieval

### Site Configuration
Character limits are configured per site tag in Flarum admin:
- ittechblog: 5000 characters
- focus: 3000 characters  
- chip: 4000 characters
- default: 5000 characters

## Key Implementation Details

### Authentication
- Write operations require Flarum API token passed as `Authorization: Token {token}`
- Read operations are public

### Multi-site Support
- Each WordPress site uses a unique `site_tag` to identify its discussions
- Metadata links discussions to original WordPress posts via post_id and post_url

### Database Migration
- Migration file: `migrations/2024_01_01_000000_create_discussion_metadata_table.php`
- Creates `discussion_metadata` table with proper foreign keys and indexes

## Testing Approach

No automated tests are included. Test manually by:
1. Installing extension in a local Flarum instance
2. Creating discussions via API with metadata
3. Verifying metadata storage and retrieval
4. Testing character limit enforcement per site tag