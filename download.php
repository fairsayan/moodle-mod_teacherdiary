<?php

/**
 * This page prints a particular instance of teacherdiary
 *
 * @author  Domenico Pontari <fairsayan@gmail.com>
 * @package mod/teacherdiary
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/lib_page_diary.php');
require_once($CFG->dirroot . '/lib/filelib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // teacherdiary instance ID
$editable = optional_param ('editable', 0, PARAM_BOOL);

if ($id) {
    if (! $cm = get_coursemodule_from_id('teacherdiary', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $teacherdiary = get_record('teacherdiary', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $teacherdiary = get_record('teacherdiary', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $teacherdiary->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('teacherdiary', $teacherdiary->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "teacherdiary", "download", "download.php?id=$cm->id", "$teacherdiary->id");

/// Print the main part of the page
$modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);

if (!has_capability('mod/teacherdiary:viewdiary', $modcontext)) {
  echo get_string("diaryviewdenied", 'teacherdiary');
  die;
}

if (!make_upload_directory('teacherdiary'))
  error("The site administrator needs to fix the file permissions");
teacherdiary_create_excel ($teacherdiary->id, $editable);
send_temp_file ($CFG->dataroot."/teacherdiary/teacherdiary$teacherdiary->id.csv", "teacherdiary$teacherdiary->id.csv");

?>
