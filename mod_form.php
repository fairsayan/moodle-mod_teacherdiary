<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This file defines the main teacherdiary configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             teacherdiary type (index.php) and in the header
 *             of the teacherdiary main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_teacherdiary_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('teacherdiaryname', 'teacherdiary'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    /// Adding owner of the diary
        $owners = teacherdiary_get_potential_owners($COURSE);
        if (empty($owners)) notice (get_string('noteacherdefinedincourse', 'teacherdiary'));
        $mform->addElement('select', 'userid', get_string('diaryowner', 'teacherdiary'), $owners);
        $mform->setType('userid', PARAM_INT);
        $mform->addRule('userid', null, 'required', null, 'client');

        $mform->addElement('text', 'plannedhours', get_string('plannedhours', 'teacherdiary'), array('size'=>'64'));
        $mform->setType('plannedhours', PARAM_INT);
        $mform->addRule('plannedhours', null, 'required', null, 'client');

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>
