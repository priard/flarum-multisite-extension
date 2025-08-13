flarum.core.compat['admin/app'].initializers.add('priard-multisite', function() {
  flarum.core.compat['admin/app'].extensionData
    .for('priard-multisite')
    .registerSetting({
      setting: 'priard_multisite.default_character_limit',
      label: 'Default Character Limit',
      help: 'Maximum number of characters allowed in comments (default for all sites)',
      type: 'number'
    })
    .registerSetting({
      setting: 'priard_multisite.character_limits', 
      label: 'Per-Site Character Limits',
      help: 'JSON object with site-specific limits. Example: {"site1": 5000, "site2": 3000}',
      type: 'textarea'
    })
    .registerSetting({
      setting: 'priard_multisite.site_tags',
      label: 'Site Tags',
      help: 'JSON array of site tags. Example: ["site1", "site2", "site3"]',
      type: 'textarea'
    });
});