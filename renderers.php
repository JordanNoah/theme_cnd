<?php

class theme_cnd_core_renderer extends \core_renderer {

    public function navbar(): string {
        $newnav = new \theme_boost\boostnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string {
        global $DB, $USER, $CFG, $SITE;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 'message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                        $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                        $contactimage = $iscontact ? 'removecontact' : 'addcontact';
                        $userbuttons['togglecontact'] = array(
                            'buttontype' => 'togglecontact',
                            'title' => get_string($contacttitle, 'message'),
                            'url' => new moodle_url('/message/index.php', array(
                                    'user1' => $USER->id,
                                    'user2' => $user->id,
                                    $contacturlaction => $user->id,
                                    'sesskey' => sesskey())
                            ),
                            'image' => $contactimage,
                            'linkattributes' => \core_message\helper::togglecontact_link_params($user, $iscontact),
                            'page' => $this->page
                        );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                $heading = $this->page->course->fullname;
            } else {
                $heading = $this->page->cm->get_formatted_name();
                $imagedata = $this->pix_icon('monologo', '', $this->page->activityname, ['class' => 'activityicon']);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $imagedata = html_writer::tag('div', $imagedata, ['class' => $purposeclass]);
                $prefix = get_string('modulename', $this->page->activityname);
            }
        }


        $contextheader = new \context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix);
        return $this->render_context_header($contextheader);
    }


    protected function render_context_header(\context_header $contextheader) {
        global $COURSE,$USER,$PAGE;

        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel, 'h2 m-0');
        } else {
            if($PAGE->pagetype == 'course-view-topics' && $contextheader->headinglevel == 1){
                $contextheader->heading .= "<span class='page-course-shortname'>(".$COURSE->shortname.")</span>";
                $heading = html_writer::tag('p',$contextheader->heading,array('class'=>'h2 m-0'));
            }else{
                $heading = $this->heading($contextheader->heading, $contextheader->headinglevel, 'h2 m-0');
            }
        }

        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header d-flex flex-column');

        if (is_student()) {
            // Logic to redirect student from mod to previous section.
            $courseMods = new course_modinfo($COURSE, $USER->id);
            $sectionnum = is_object($PAGE->cm) && property_exists($PAGE->cm, "sectionnum") ? $PAGE->cm->sectionnum : 0;
            $sectioninfo = $courseMods->get_section_info($sectionnum);
            $sectionname = is_object($sectioninfo) && property_exists($sectioninfo, "_name") ? $sectioninfo->name : "";


            $icon = html_writer::tag('i','&nbsp;&nbsp;', array('class'=> 'd-inline align-middle fa fa-arrow-left font-weight-normal my-0', 'aria-hidden'=> 'true'));
            $coursename = html_writer::tag('span', $COURSE->fullname, array('class' => 'align-middle d-inline'));
            $linkcontent = html_writer::tag('p', $icon . $coursename, array('class' => 'my-0', 'title' => $sectionname));
            $linkUrl = new moodle_url('/course/view.php', ["id" => $COURSE->id]) . '#section-' . ($sectionnum);
            $link = html_writer::link($linkUrl, $linkcontent, array('class' => 'course-previous-section-link'));
            $html .= html_writer::div($link, 'course-title-back h2 text--secondary');
        }

        $html .= html_writer::start_div('course-section-view');
        $html .= html_writer::span('','');
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('d-flex');
        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image mr-2 d-flex align-items-center');
        }

        // Headings.
        if (isset($contextheader->prefix)) {
            $prefix = html_writer::div($contextheader->prefix, 'text-muted text-uppercase small line-height-3 type-topic');
            $heading = $prefix . $heading;
        }
        $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));

        $html .= html_writer::end_div();




        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }

/**
     * See if this is the first view of the current cm in the session if it has fake blocks.
     *
     * (We track up to 100 cms so as not to overflow the session.)
     * This is done for drawer regions containing fake blocks so we can show blocks automatically.
     *
     * @return boolean true if the page has fakeblocks and this is the first visit.
     */
    public function firstview_fakeblocks(): bool {
        global $SESSION;

        $firstview = false;
        if ($this->page->cm) {
            if (!$this->page->blocks->region_has_fakeblocks('side-pre')) {
                return false;
            }
            if (!property_exists($SESSION, 'firstview_fakeblocks')) {
                $SESSION->firstview_fakeblocks = [];
            }
            if (array_key_exists($this->page->cm->id, $SESSION->firstview_fakeblocks)) {
                $firstview = false;
            } else {
                $SESSION->firstview_fakeblocks[$this->page->cm->id] = true;
                $firstview = true;
                if (count($SESSION->firstview_fakeblocks) > 100) {
                    array_shift($SESSION->firstview_fakeblocks);
                }
            }
        }
        return $firstview;
    }

}