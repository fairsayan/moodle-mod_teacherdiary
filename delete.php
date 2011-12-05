<?php

/**
 * This page prints a particular instance of teacherdiary
 *
 * @author  Domenico Pontari <fairsayan@gmail.com>
 * @package mod/teacherdiary
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('pageid', PARAM_INT); // page diary ID
$confirmed = optional_param('confirmed', false, PARAM_BOOL); // confirmed deletion

if (! $teacherdiary_page = get_record('teacherdiary_pages', 'id', $id)) {
  error('Page diary ID is incorrect');
}
$teacherdiaryid = $teacherdiary_page->teacherdiaryid;
if (! $teacherdiary = get_record('teacherdiary', 'id', $teacherdiaryid)) {
    error('Teacher diary ID is incorrect');
}
if (! $course = get_record('course', 'id', $teacherdiary->course)) {
    error('Course is misconfigured');
}
if (! $cm = get_coursemodule_from_instance('teacherdiary', $teacherdiary->id, $course->id)) {
    error('Course Module ID was incorrect');
}

require_login($course, true, $cm);

/// Print the page header
$strteacherdiaries = get_string('modulenameplural', 'teacherdiary');
$strteacherdiary  = get_string('modulename', 'teacherdiary');
$strnavleaf = get_string('delete_page_diary', 'teacherdiary');

$navlinks = array();
$navlinks[] = array('name' => $strteacherdiaries, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($teacherdiary->name), 'link' => 'view.php?id=' . $cm->id, 'type' => 'activityinstance');
$navlinks[] = array('name' => format_string($strnavleaf), 'link' => '', 'type' => 'activityaction');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($teacherdiary->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strteacherdiary), navmenu($course, $cm));

/// Print the main part of the page

$modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);


$can_modify = has_capability('mod/teacherdiary:editanydiary', $modcontext) ||
    (has_capability('mod/teacherdiary:editowndiary', $modcontext) && ($teacherdiary->userid == $USER->id));

if (!$can_modify) notice(get_string("deletingpagedenied", 'teacherdiary'));

if ($confirmed) {
  if (!delete_records('teacherdiary_pages', 'id', $id))
    notice (get_string("unabletodeleteteacherdiarypage", 'teacherdiary'));
  redirect($CFG->wwwroot.'/mod/teacherdiary/view.php?id='.$cm->id, get_string("teacherdiarypagedeleted", 'teacherdiary'));
}

$msg_data->from = userdate($teacherdiary_page->starttime);
$msg_data->to = userdate($teacherdiary_page->endtime);
$msg_data->summary = $teacherdiary_page->summary;
notice_yesno (
  get_string("confirmteacherdiarypagetobedeleted", 'teacherdiary', $msg_data),
  "delete.php?confirmed=true&pageid=$id",
  $CFG->wwwroot.'/mod/teacherdiary/view.php?id='.$cm->id
);

/// Finish the page
print_footer($course);

?>
