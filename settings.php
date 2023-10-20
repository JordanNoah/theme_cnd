<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {

    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingcnd', get_string('configtitle', 'theme_cnd'));

    // Each page is a tab - the first is the "General" tab.
    $page = new admin_settingpage('theme_cnd_general', get_string('generalsettings', 'theme_cnd'));

    // Replicate the preset setting from boost.
    $name = 'theme_cnd/preset';
    $title = get_string('preset', 'theme_cnd');
    $description = get_string('preset_desc', 'theme_cnd');
    $default = 'default.scss';

    // We list files in our own file area to add to the drop down. We will provide our own function to
    // load all the presets from the correct paths.
    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_cnd', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets from Boost.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_cnd/presetfiles';
    $title = get_string('presetfiles','theme_cnd');
    $description = get_string('presetfiles_desc', 'theme_cnd');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Variable $brand-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_cnd/brandcolor';
    $title = get_string('brandcolor', 'theme_cnd');
    $description = get_string('brandcolor_desc', 'theme_cnd');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_cnd_advanced', get_string('advancedsettings', 'theme_cnd'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_configtextarea('theme_cnd/scsspre',
        get_string('rawscsspre', 'theme_cnd'), get_string('rawscsspre_desc', 'theme_cnd'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_configtextarea('theme_cnd/scss', get_string('rawscss', 'theme_cnd'),
        get_string('rawscss_desc', 'theme_cnd'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    //enable welcome block

    $setting = new admin_setting_configcheckbox('theme_cnd/welcomeblock',
        get_string('welcome_block','theme_cnd'),
        get_string('welcome_block_desc','theme_cnd'),'','1','0');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    $settings->add($page);
}