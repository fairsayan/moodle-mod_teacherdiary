#diary_pages_box{
    width: 85%;
    margin: auto;
    text-align: center;
}

#diary_pages_table {
    width: 90%;
    margin: auto;
    font-size: 0.8em;
    margin-top: 15px;
    border-color: gray;
    text-align: left;
}

#diary_pages_table tr {
    border-color: gray;
}

.teacherdiary_summary {
    width: 60%;
    margin: auto;
    text-align: left;
    font-size: 0.9em;
    margin-bottom: 7.5em;
    clear: both;
}

.teacherdiary_summary th {
    text-align: right;
    width: 50%;
}


.editing_img {
    margin-left: 3px;
    margin-right: 3px;
}

.teacherdiaryeditform.mform {
    width: 600px;
    margin:auto;
}

.teacherdiaryeditform.mform div.fdescription {
    padding-top: 30px;
}



/** CSS definition for edit form **/
/* this piece of code is adapted for IE7, there are some dirty things
 * this is the beginning of the optimized IE7 code: */

.teacherdiaryeditform.mform div.fitem.finlinefirst {
  width: 270px;
  float: left;
  clear: left;
}

.teacherdiaryeditform.mform div.fitem.finlinefirst div.felement {
  width: auto;
}

.teacherdiaryeditform.mform div.felement.finline,
.teacherdiaryeditform.mform div.felement.finlinelast {
  width: auto;
  float: left;
}

.teacherdiaryeditform.mform div.felement.finlinelast {
  clear: right;
  width: auto;
}

.teacherdiaryeditform.mform .fitemtitle {
    width: 200px;
}

.pulldownIE7 {
  width: 100%;
  clear: both;
  float: none;
}


/* here finish of the optimized IE7 code. Here in the comment I leave
   a cleaner code that doesn't work for IE7

.teacherdiaryeditform.mform div.fitem {
    width: auto;
    float: left;
}

.teacherdiaryeditform.mform .fitemtitle {
    width: 200px;
}

.teacherdiaryeditform.mform .finline,
.teacherdiaryeditform.mform .finlinelast,
.teacherdiaryeditform.mform .finlinefirst .felement
 {
    float:left;
    width:auto;
}

.teacherdiaryeditform.mform div.felement,
.teacherdiaryeditform.mform fieldset.felement {
    float:left;
    width:auto;
}

*/

#teacherdiary_bulk_insert_form {
    width: 80%;
    margin: auto;
    margin-top: 30px;
}

.teacherdiary_tools {
    font-size: 0.8em;
    text-align: left;
    width: auto;
    margin-bottom: 1em;
}

.teacherdiary_tools ul {
    margin:0;
    padding-left: 1em;
}
