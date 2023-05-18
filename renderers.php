<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/renderer.php');

class theme_max_core_course_renderer extends core_course_renderer {
    /**
     * Outputs contents for frontpage as configured in $CFG->frontpage or $CFG->frontpageloggedin
     *
     * @return string
     */
    public function frontpage() {
        global $CFG, $SITE;

        $output = '';

        if (isloggedin() and !isguestuser() and isset($CFG->frontpageloggedin)) {
            $frontpagelayout = $CFG->frontpageloggedin;
        } else {
            $frontpagelayout = $CFG->frontpage;
        }

        foreach (explode(',', $frontpagelayout) as $v) {
            switch ($v) {
                // Display the main part of the front page.
                case FRONTPAGENEWS:
                    if ($SITE->newsitems) {
                        // Print forums only when needed.
                        require_once($CFG->dirroot .'/mod/forum/lib.php');
                        if (($newsforum = forum_get_course_forum($SITE->id, 'news')) &&
                            ($forumcontents = $this->frontpage_news($newsforum))) {
                            $newsforumcm = get_fast_modinfo($SITE)->instances['forum'][$newsforum->id];
                            $output .= $this->frontpage_part('skipsitenews', 'site-news-forum',
                                $newsforumcm->get_formatted_name(), $forumcontents);
                        }
                    }
                    break;

                case FRONTPAGEENROLLEDCOURSELIST:
                    $mycourseshtml = $this->frontpage_my_courses();
                    if (!empty($mycourseshtml)) {
                        $output .= $this->frontpage_part('skipmycourses', 'frontpage-course-list',
                            get_string('mycourses'), $mycourseshtml);
                    }
                    break;

                case FRONTPAGEALLCOURSELIST:
                    $availablecourseslist = $this->get_html($CFG); //Custom design function: return customized list of courses
                    $output .= $availablecourseslist;
                    break;

                case FRONTPAGECATEGORYNAMES:
                    $output .= $this->frontpage_part('skipcategories', 'frontpage-category-names',
                        get_string('categories'), $this->frontpage_categories_list());
                    break;

                case FRONTPAGECATEGORYCOMBO:
                    $output .= $this->frontpage_part('skipcourses', 'frontpage-category-combo',
                        get_string('courses'), $this->frontpage_combo_list());
                    break;

                case FRONTPAGECOURSESEARCH:
                    $output .= $this->box($this->course_search_form(''), 'd-flex justify-content-center');
                    break;

            }
            $output .= '<br />';
        }

        return $output;
    }

    protected function course_contacts_with_div(core_course_list_element $course) {
        $content = '';
        if ($course->has_course_contacts()) {
            $content .= html_writer::start_tag('ul', ['class' => 'teachers']);
            foreach ($course->get_course_contacts() as $coursecontact) {
                $rolenames = array_map(function ($role) {
                    return $role->displayname;
                }, $coursecontact['roles']);
                $name = html_writer::tag('div', implode(", ", $rolenames).': ', ['class' => 'font-weight-bold']);
                $name .= html_writer::link(new moodle_url('/user/view.php',
                    ['id' => $coursecontact['user']->id, 'course' => SITEID]),
                    $coursecontact['username']);
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul');
        }
        return $content;
    }

    protected function coursecat_coursebox_content_with_div(coursecat_helper $chelper, $course) {
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $content = \html_writer::start_tag('div', ['class' => 'd-flex']);
        $content .= $this->course_overview_files($course);
        $content .= \html_writer::start_tag('div', ['class' => 'flex-grow-1']);
        $content .= $this->course_summary($chelper, $course);
        $content .= $this->course_contacts_with_div($course);
        $content .= $this->course_category_name($chelper, $course);
        $content .= $this->course_custom_fields($course);
        $content .= \html_writer::end_tag('div');
        $content .= \html_writer::end_tag('div');
        return $content;
    }

    public function get_html($CFG)
    {
        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
        set_courses_display_options(array(
            'recursive' => true,
            'limit' => $CFG->frontpagecourselimit,
            'viewmoreurl' => new moodle_url('/course/index.php'),
            'viewmoretext' => new lang_string('fulllistofcourses')));
        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));

        $courses = core_course_category::top()->get_courses($chelper->get_courses_display_options());

        $text = '';

        //#0A4259

        $text .= html_writer::tag('h2', 'Все курсы', ['class' => 'all-courses']);

        $text .= html_writer::start_tag('div', ['class' => 'mine-wrapper']);

        foreach ($courses as $course) {
            $text .= html_writer::start_tag('div', ['class' => 'mine']);

            $coursename = $this->course_name($chelper, $course).$this->course_enrolment_icons($course);
            $text .= html_writer::tag('div', $coursename, array('class' => 'info'));

            $contacts = $this->coursecat_coursebox_content_with_div($chelper, $course);

            $text .= html_writer::tag('div', $contacts, array('class' => 'content'));

            $text .= html_writer::end_tag('div');
        }

        $text .= html_writer::end_tag('div');

        return $text;
    }

}