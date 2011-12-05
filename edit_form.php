<?php

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

require_once($CFG->dirroot.'/lib/formslib.php');

class teacherdiary_edit_form extends moodleform {

    function definition() {
        global $teacherdiary;
        global $id;
        $mform =& $this->_form;

        $now = getdate();
        $curryear = (int) $now['year'];
        for ($i = 1; $i <= 31; $days["$i"] = $i++);
        for ($i = 1; $i <= 12; $months["$i"] = $i++);
        for ($i = $curryear - 1; $i <= $curryear + 1; $years["$i"] = $i++);
        for ($i = 8; $i <= 20; $hours["$i"] = $i++);
        for ($i = 0; $i < 60; $i+= 5) $minutes["$i"] = sprintf("%02d", $i);

        if ($id) {
          global $teacherdiary_page;
          $starttime_obj = getdate($teacherdiary_page->starttime);
          $endtime_obj = getdate($teacherdiary_page->endtime);

          $default_teachername = $teacherdiary_page->teachername;
          $default_day   =  $starttime_obj['mday'];
          $default_month =  $starttime_obj['mon'];
          $default_year  =  $starttime_obj['year'];
          $default_starthour    = $starttime_obj['hours'];
          $default_startminute  = $starttime_obj['minutes'];
          $default_endhour      = $endtime_obj['hours'];
          $default_endminute    = $endtime_obj['minutes'];
          $default_summary      = $teacherdiary_page->summary;
        } else {
          $default_teachername = fullname(get_record('user','id',$teacherdiary->userid));
          $default_day   =  $now['mday'];
          $default_month =  $now['mon'];
          $default_year  =  $curryear;
          $default_starthour    = '8';
          $default_startminute  = '00';
          $default_endhour      = '8';
          $default_endminute    = '00';
          $default_summary      = '';
        }

//-------------------------------------------------------------------------------
        $mform->addElement('text', 'teachername', get_string('teachername', 'teacherdiary'), array('size'=>'20'));
        $mform->setType('teachername', PARAM_TEXT);
        $mform->setDefault('teachername', $default_teachername);
        $mform->addRule('teachername', null, 'required', null, 'client');
        $mform->addRule('teachername', get_string('maximumchars', '', 50), 'maxlength', 50, 'client');

        $mform->addElement('select', 'day', get_string('date'), $days);
        $mform->setType('day', PARAM_INT);
        $mform->addRule('day', null, 'required', null, 'client');
        $mform->setDefault('day', $default_day);

        $mform->addElement('select', 'month', '', $months);
        $mform->setType('month', PARAM_INT);
        $mform->setDefault('month', $default_month);

        $mform->addElement('select', 'year', '', $years);
        $mform->setType('year', PARAM_INT);
        $mform->setDefault('year', $default_year);

        $mform->addElement('select', 'starthour', get_string('starttime', 'teacherdiary'), $hours);
        $mform->setType('starthour', PARAM_INT);
        $mform->addRule('starthour', null, 'required', null, 'client');
        $mform->setDefault('starthour', $default_starthour);

        $mform->addElement('select', 'startminute', '', $minutes);
        $mform->setType('startminute', PARAM_INT);
        $mform->setDefault('startminute', $default_startminute);

        $mform->addElement('select', 'endhour', get_string('endtime', 'teacherdiary'), $hours);
        $mform->setType('endhour', PARAM_INT);
        $mform->addRule('endhour', null, 'required', null, 'client');
        $mform->setDefault('endhour', $default_endhour);

        $mform->addElement('select', 'endminute', '', $minutes);
        $mform->setType('endminute', PARAM_INT);
        $mform->setDefault('endminute', $default_endminute);

        $mform->addElement('htmleditor', 'summary', get_string('summary', 'teacherdiary'));
        $mform->setType('summary', PARAM_RAW);
        $mform->addRule('summary', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('summary', array('writing', 'richtext'), false, 'editorhelpbutton');
        $mform->setDefault('summary', $default_summary);

        if ($id) $mform->addElement('hidden', 'pageid', $id);
          else $mform->addElement('hidden', 'teacherdiaryid', $teacherdiary->id);

        $this->add_action_buttons();
    }
    
    function validation ($data) {
      $errors = array();

      return empty($errors)?true:$errors;
    }
}

class MoodleTeacherDiaryEditForm_Renderer extends MoodleQuickForm_Renderer {
    function renderElement(&$element, $required, $error){
        //manipulate id of all elements before rendering
        if (!is_null($element->getAttribute('id'))) {
            $id = $element->getAttribute('id');
        } else {
            $id = $element->getName();
        }
        //strip qf_ prefix and replace '[' with '_' and strip ']'
        $id = preg_replace(array('/^qf_|\]/', '/\[/'), array('', '_'), $id);
        if (strpos($id, 'id_') !== 0){
            $element->updateAttributes(array('id'=>'id_'.$id));
        }

        //adding stuff to place holders in template
        switch ($element->getName()) {
          case 'day':
          case 'starthour':
          case 'endhour':
              $html = $this->_elementTemplates['inlinefirst'];
            break;
          case 'month':
              $html = $this->_elementTemplates['inline'];
            break;
          case 'startminute':
          case 'endminute':
          case 'year':
              $html = $this->_elementTemplates['inlinelast'];
            break;
          default:
          if (method_exists($element, 'getElementTemplateType')){
              $html = $this->_elementTemplates[$element->getElementTemplateType()];
          }else{
              $html = $this->_elementTemplates['default'];
          }
        }

        if ($this->_showAdvanced){
            $advclass = ' advanced';
        } else {
            $advclass = ' advanced hide';
        }
        if (isset($this->_advancedElements[$element->getName()])){
            $html =str_replace(' {advanced}', $advclass, $html);
        } else {
            $html =str_replace(' {advanced}', '', $html);
        }
        if (isset($this->_advancedElements[$element->getName()])||$element->getName() == 'mform_showadvanced'){
            $html =str_replace('{advancedimg}', $this->_advancedHTML, $html);
        } else {
            $html =str_replace('{advancedimg}', '', $html);
        }
        $html =str_replace('{type}', 'f'.$element->getType(), $html);
        $html =str_replace('{name}', $element->getName(), $html);
        if (method_exists($element, 'getHelpButton')){
            $html = str_replace('{help}', $element->getHelpButton(), $html);
        }else{
            $html = str_replace('{help}', '', $html);

        }
        if (!isset($this->_templates[$element->getName()])) {
            $this->_templates[$element->getName()] = $html;
        }

        parent::renderElement($element, $required, $error);
    }
    
    function __construct () {
      parent::__construct();
      $this->_elementTemplates['inline'] = "\n\t\t".'<div class="felement finline {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->{element}</div>';
      $this->_elementTemplates['inlinelast'] = "\n\t\t".'<div class="felement finlinelast {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->{element}</div>';
      $this->_elementTemplates['inlinefirst'] = "\n\t\t".'<div class="pulldownIE7"></div><div class="fitem finlinefirst {advanced}<!-- BEGIN required --> required<!-- END required -->"><div class="fitemtitle"><label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label></div><div class="felement {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->{element}</div></div>';
      parent::HTML_QuickForm_Renderer_Tableless();
    }
}