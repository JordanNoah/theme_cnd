<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// We will add callbacks here as we add features to our theme.

// Function to return the SCSS to prepend to our main SCSS for this theme.
// Note the function name starts with the component name because this is a global function and we don't want namespace clashes.
function theme_cnd_get_pre_scss($theme) {
    // Load the settings from the parent.
    $theme = theme_config::load('boost');
    // Call the parent themes get_pre_scss function.
    return theme_boost_get_pre_scss($theme);
}


/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_cnd_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage' ||
            $filearea === 'loginbackgroundimage')) {
        $theme = theme_config::load('cnd');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}


// Function to return the SCSS to append to our main SCSS for this theme.
// Note the function name starts with the component name because this is a global function and we don't want namespace clashes.
function theme_cnd_get_extra_scss($theme) {
    // Load the settings from the parent.
    $theme = theme_config::load('boost');
    // Call the parent themes get_extra_scss function.
    return theme_boost_get_extra_scss($theme);
}

function theme_cnd_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_photo', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_photo and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/cnd/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/cnd/scss/post.scss');

    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post;
}

function set_default_primarynav_sections()
{
    global $PAGE;
    // *** Default sections Managed ***
    $sections = [
        'myhome' => 'i/home',
        'mycourses' => 'i/moodle_host',
        'siteadminnode' => 'i/settings',
    ];
    if (is_student()) { //cuando es estudiante se remueve la opcion mis cursos
        $PAGE->primarynav->get('mycourses')->remove();
    }

    foreach ($sections as $section => $icon) {
        /**
         * @var $node navigation_node
         */
        $node = $PAGE->primarynav->get($section);
        if (!empty($node))
            $node->icon = new pix_icon($icon, '');
    }
}

function is_student()
{
    global $USER, $DB;
    $role = $DB->get_record('role', ['shortname' => 'student']);
    return !empty($role) && user_has_role_assignment($USER->id, $role->id);
}

function set_aditional_primarynav_sections()
{
    global $PAGE;

    if (isloggedin() && !isguestuser()) {
        $payload = get_sections_details();
        foreach ($payload as $key => $content) {
            if (!empty($content['link'])) {
                $stringForTitle = $content['label'];
                $redirectionUrl = new moodle_url($content['link']);
                $nodeKey = $content['node_key'];
                $pixIcon = new pix_icon($content['icon'], '');
                $beforeKey = $content['before_key'];
                $node = create_avigation_node($stringForTitle, $redirectionUrl, $nodeKey, $pixIcon);
                if ($content['exclusive_for_student'] && is_student()) {
                    $PAGE->primarynav->add_node($node, $beforeKey);
                } elseif (!$content['exclusive_for_student']) {
                    $PAGE->primarynav->add_node($node, $beforeKey);
                }
            }
        }
    }
}

function get_sections_details()
{
    global $PAGE;
    $pluginName = 'theme_cnd';
    customize_urls($pluginName);

    $tutorialCourseName = get_filtered_course_title(get_config($pluginName, 'tutorial_url'));
    $discussionCourseName = get_filtered_course_title(get_config($pluginName, 'discussions_url'));
    $newsCourseName = get_filtered_course_title(get_config($pluginName, 'news_url'));

    return [
        'tutorials' => [
            'label' => $tutorialCourseName ?: get_string('tutorial', $pluginName),
            'icon' => 'book',
            'node_key' => 'tutorials',
            'link' => get_config($pluginName, 'tutorial_url'),
            'before_key' => 'myhome',
            'exclusive_for_student' => false,
        ],
        'library' => [
            'label' => get_string('library', $pluginName),
            'icon' => 'i/db',
            'node_key' => 'library',
            'link' => get_config($pluginName, 'library_url'),
            'before_key' => null,
            'exclusive_for_student' => false,
        ],
        'academic_record' => [
            'label' => get_string('academic_record', $pluginName),
            'icon' => 'i/course',
            'node_key' => 'academic_record',
            'link' => get_config($pluginName, 'academic_record_url'),
            'before_key' => null,
            'exclusive_for_student' => true,
        ],
        'news' => [
            'label' => $newsCourseName ?: get_string('news', $pluginName),
            'icon' => 'i/news',
            'node_key' => 'news',
            'link' => get_config($pluginName, 'news_url'),
            'before_key' => 'library',
            'exclusive_for_student' => false,
        ],
        'discussions' => [
            'label' => $discussionCourseName ?: get_string('discussions', $pluginName),
            'icon' => 't/messages',
            'node_key' => 'discussions',
            'link' => get_config($pluginName, 'discussions_url'),
            'before_key' => 'news',
            'exclusive_for_student' => false,
        ],
    ];
}


function customize_urls($pluginName) {
    global $PAGE;

    $PAGE->requires->js_call_amd(
        'theme_cnd/utils/redirects',
        'redirects',
        [
            [
                ['page' => 'userMenu', 'url' => get_config($pluginName, 'academic_record_url'), 'key' => 'gradesStudent'],
                ['page' => 'profile', 'url' => get_config($pluginName, 'academic_record_url'), 'key' => 'reportsGrades']
            ]
        ]
    );
}

/**
 * @param moodle_page $page
 * @return void
 * @throws coding_exception
 */
function theme_cnd_page_init(moodle_page $page){

    global $USER, $PAGE, $COURSE, $DB, $CFG;

    if(user_has_role_assignment($USER->id,5)){

        $page->add_body_class('role-student');

    }
    if (user_has_role_assignment($USER->id,1)) {
        $page->add_body_class('role-manager');
    }
    if(is_siteadmin($USER->id)){
        $page->add_body_class('role-admin');
    }
    if (strpos($page->pagetype, 'course-view') === 0) {
        $page->add_body_class('course-custom');
    }
}


function get_filtered_course_title($tutorialUrl)
{
    global $DB;

    if (strpos($tutorialUrl, 'name=')) {
        $queryString = parse_url($tutorialUrl, PHP_URL_QUERY);
        parse_str($queryString, $params);

        $shortname = $params['name'];
        $tutorialCourse = $DB->get_record('course', array('shortname' => $shortname));

        return format_string($tutorialCourse->fullname);
    }
}
