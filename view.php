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

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // teacherdiary instance ID

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

add_to_log($course->id, "teacherdiary", "view", "view.php?id=$cm->id", "$teacherdiary->id");

/// Print the page header
$strteacherdiaries = get_string('modulenameplural', 'teacherdiary');
$strteacherdiary  = get_string('modulename', 'teacherdiary');

$navlinks = array();
$navlinks[] = array('name' => $strteacherdiaries, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($teacherdiary->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($teacherdiary->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strteacherdiary), navmenu($course, $cm));

/// Print the main part of the page

$modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);

if (!has_capability('mod/teacherdiary:viewdiary', $modcontext))
    notice(get_string("diaryviewdenied", 'teacherdiary'));

$can_modify = has_capability('mod/teacherdiary:editanydiary', $modcontext) ||
    (has_capability('mod/teacherdiary:editowndiary', $modcontext) && ($teacherdiary->userid == $USER->id));
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$bulk_insert_allowed = has_capability('mod/teacherdiary:bulkinsertteacherdiary', $systemcontext);
$excel_management_allowed = has_capability('mod/teacherdiary:useanyexceltool', $modcontext);
$downloadall_allowed = has_capability('mod/teacherdiary:downloadallteacherdiaries', $systemcontext);


$view_content = '';

//-------------------------------------------------------------------------------
//TOOLS
$tools = array();
if ($can_modify) $tools[] = '<a href="edit.php?teacherdiaryid=' . $teacherdiary->id . '">' . get_string('add_page_diary', 'teacherdiary') . "</a>\n";
if ($bulk_insert_allowed) $tools[] = '<a href="bulk_insert_teacherdiary.php">' . get_string('bulkinsertteacherdiarytool', 'teacherdiary') . "</a>\n";
if ($excel_management_allowed) $tools[] = '<a href="excel_management.php?a=' . $teacherdiary->id . '">' . get_string('teacherdiarypagesexceltool', 'teacherdiary') . "</a>\n";
if ($downloadall_allowed) $tools[] = '<a href="download_all.php">' . get_string('downloadallexcel', 'teacherdiary') . "</a>\n";
if ($downloadall_allowed) $tools[] = '<a href="download_all.php?only_report=1">' . get_string('downloadreportinexcel', 'teacherdiary') . "</a>\n";
$tools[] ='<a href="download.php?a=' . $teacherdiary->id . '">' . get_string('downloadexcelformat', 'teacherdiary') . "</a>\n";
//-------------------------------------------------------------------------------

$view_content .= teacherdiary_get_tools_rendered ($tools);
$view_content .= teacherdiary_get_page_diary_summary ($teacherdiary);
$view_content .= teacherdiary_get_page_diary_list($teacherdiary, $can_modify);

print_box($view_content, 'generalbox', 'diary_pages_box');

/// Finish the page
print_footer($course);

?>
