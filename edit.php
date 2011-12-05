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
require_once(dirname(__FILE__).'/edit_form.php');

$id = optional_param('pageid', 0, PARAM_INT); // page diary ID if doesn't exists add a new page
$teacherdiaryid = optional_param('teacherdiaryid', 0, PARAM_INT); // teacherdiary instance ID

if ($id) {
    if (! $teacherdiary_page = get_record('teacherdiary_pages', 'id', $id)) {
        error('Page diary ID is incorrect');
    }
    $teacherdiaryid = $teacherdiary_page->teacherdiaryid;
} else if (!$teacherdiaryid) {
    error('You must specify a page diary ID or a teacher diary ID');
}
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
$strnavleaf = ($id==0)?get_string('add_page_diary', 'teacherdiary'):get_string('edit_page_diary', 'teacherdiary');

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

if (!$can_modify) notice(get_string("editingpagedenied", 'teacherdiary'));

$editform = new teacherdiary_edit_form('edit.php', null, 'post', '', 'class="teacherdiaryeditform"');
$displayform = true;
if ($editform->is_cancelled()) redirect($CFG->wwwroot.'/mod/teacherdiary/view.php?id='.$cm->id);
elseif ($data = $editform->get_data()) {
  if (!$id) {
    $teacherdiary_page = get_record_select('teacherdiary_pages');
    $teacherdiary_page->id = 0;
  }
  $teacherdiary_page->starttime = mktime ($data->starthour, $data->startminute, 0, $data->month, $data->day, $data->year);
  $teacherdiary_page->endtime = mktime ($data->endhour, $data->endminute, 0, $data->month, $data->day, $data->year);
  $teacherdiary_page->timemodified = time();
  $teacherdiary_page->summary = $data->summary;
  $teacherdiary_page->teachername = $data->teachername;
  $teacherdiary_page->teacherdiaryid = $teacherdiaryid;
  if (!$id) {
    $teacherdiary_page->timecreated = time();
    $teacherdiary_page->id = insert_record('teacherdiary_pages', $teacherdiary_page);
    if (!$teacherdiary_page->id) notice(get_string("unabletoaddteacherdiarypage", 'teacherdiary'));
  } elseif (!update_record ('teacherdiary_pages', $teacherdiary_page))
    notice(get_string("unabletoupdateteacherdiarypage", 'teacherdiary'));

  redirect($CFG->wwwroot.'/mod/teacherdiary/view.php?id='.$cm->id, get_string("teacherdiarypageupdated", 'teacherdiary'));
  $displayform = false;
}
$preinline_template = "\n\t\t".'<div class="fitem fpreinline {advanced}<!-- BEGIN required --> required<!-- END required -->"><div class="fitemtitle"><label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label></div><div class="felement {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->{element}</div></div>';
$inline_template = "\n\t\t".'<div class="felement finline <!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->{element}</div>';
$renderer = new MoodleTeacherDiaryEditForm_Renderer();

//$renderer->setElementTemplate($preinline_template, 'starthour');
//$renderer->setElementTemplate($inline_template, 'startminute');
$editform->_form->accept($renderer);
if ($displayform) echo $renderer->toHtml();

/// Finish the page
print_footer($course);

?>
