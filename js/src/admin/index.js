import app from 'flarum/admin/app';

app.initializers.add('priard-multisite', () => {
  app.extensionData
    .for('priard-multisite')
    .registerSetting({
      setting: 'priard_multisite.default_character_limit',
      label: app.translator.trans('priard-multisite.admin.settings.default_character_limit_label'),
      help: app.translator.trans('priard-multisite.admin.settings.default_character_limit_help'),
      type: 'number',
      default: 5000
    })
    .registerSetting({
      setting: 'priard_multisite.character_limits',
      label: app.translator.trans('priard-multisite.admin.settings.character_limits_label'),
      help: app.translator.trans('priard-multisite.admin.settings.character_limits_help'),
      type: 'textarea',
      default: JSON.stringify({
        'site1': 5000,
        'site2': 3000,
        'site3': 4000
      }, null, 2),
      placeholder: '{\n  "site1": 5000,\n  "site2": 3000\n}'
    })
    .registerSetting({
      setting: 'priard_multisite.site_tags',
      label: app.translator.trans('priard-multisite.admin.settings.site_tags_label'),
      help: app.translator.trans('priard-multisite.admin.settings.site_tags_help'),
      type: 'textarea',
      default: JSON.stringify([
        'site1',
        'site2',
        'site3'
      ], null, 2),
      placeholder: '["site1", "site2", "site3"]'
    });
});