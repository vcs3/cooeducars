<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Purity Courses PRO block main class.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/renderer.php');
include_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/im_report/lib.php');

class block_im_report extends block_base {

    /**
     * Adds title to block instance and initializes it.
     */
    public function init() {
        $this->title = get_string('im_report', 'block_im_report');
    }

    /**
     * Overrides block instance content.
     * Called immediately after init().
     */
	public function specialization() {

		// Set defaults
		if (empty($this->config)) {
			$this->config->show_as_card = '0';
			$this->config->show_header = '1';
			$this->config->custom_title = 'Relatórios Instituto Manager';
			$this->config->behaviour = 'slider';
			$this->config->items_per_row = '3';
			$this->config->style = '0';
			$this->config->title_limit = '32';
			$this->config->summary_limit = '100';
		}

		if (isset($this->config)) {
			if (empty($this->config->custom_title)) {
				$this->title = get_string('im_report', 'block_im_report');            
			} else {
				$this->title = format_text($this->config->custom_title, FORMAT_HTML, array('filter' => true));
			}
		}
	}

    /**
     * Gets block instance content.
     */
	public function get_content() {
		global $CFG, $DB, $PAGE;

		if ($this->content !== null) {
			return $this->content;
		}

		// Set defaults
		if (!isset($this->config->autoplay)) { $this->config->autoplay = 'true'; }
		if (!isset($this->config->autoplay_interval)) { $this->config->autoplay_interval = '6000'; }
		if (!isset($this->config->pause_hover)) { $this->config->pause_hover = 'true'; }
		if (!isset($this->config->show_image)) { $this->config->show_image = '1'; }
		if (!isset($this->config->image_height)) { $this->config->image_height = '200px'; }
		if (!isset($this->config->show_title)) { $this->config->show_title = '1'; }
		if (!isset($this->config->title_limit)) { $this->config->title_limit = '33'; }
		if (!isset($this->config->show_summary)) { $this->config->show_summary = '1'; }
		if (!isset($this->config->summary_limit)) { $this->config->summary_limit = '100'; }
		if (!isset($this->config->show_teacher)) { $this->config->show_teacher = '0'; }
		if (!isset($this->config->show_date)) { $this->config->show_date = '2'; }
		if (!isset($this->config->show_category)) { $this->config->show_category = '1'; }
		if (!isset($this->config->show_enrolments)) { $this->config->show_enrolments = '1'; }
	 
		$this->content = new stdClass;

		// Block Intro
		if ($this->config->show_header && $this->config->show_as_card == '0') {
			$block_intro = '
				<div class="purity-block-intro">';

			$block_intro .= '</div>';
		} else {
			$block_intro = '';
		}

	   

		//$courses = $this->config->courses;
	
		$chelper = new coursecat_helper();
	    $this->content->text = '
	    	<div  style="margin-top:100px;width:100%;">
			
			<a href="'.$CFG->wwwroot.'/blocks/im_report/relatorio.php?courseid=25&month=04">Relatório de Frequência (PDF)</a>
				 <a href="'.$CFG->wwwroot.'/blocks/im_report/relatorioFrequenciaXls.php">Relatório de Frequência (Excel)</a>

	    	</div>';
            
			$this->content->text .='<br><form method="get" action="'.$CFG->wwwroot.'/blocks/im_report/relatorio.php" class="selector m-1">
			<div class="form-inline text-xs-right">';
 
			$select = '<select  name="month" class="form-control custom-select mr-1">';
			$select  .= '<option> Selecione o mês...</option>';
			$meses = array(
				'Janeiro', 'Fevereiro', 'Março', 'Abril',
				'Maio', 'Junho', 'Julho', 'Agosto',
				'Setembro', 'Outubro', 'Novembro', 'Dezembro'
			  );
			// Loop pelos meses de janeiro a dezembro
			for ($mes = 1; $mes <= 12; $mes++) {
			  // Cria um objeto DateTime para o mês atual
			  $data = new DateTime("2023-$mes-01");
			  // Imprime o nome do mês no option do select
			  $select .= '<option value="' . $mes . '">' . $meses[$mes-1]. '</option>';
			}
	$select  .= '</select>';
	$select .='<input type="hidden" name="courseid" value="25">';
	$select.= '<button type="submit" class="btn btn-secondary">Gerar Relatório (PDF</button><div>';
	$this->content->text .= $select;
    // $this->content->footer = '';
	 
	    return $this->content;
	}

    /**
     * Modifies the HTML attributes of the block.
     */
	public function html_attributes() {
	    $attributes = parent::html_attributes(); // Get default values

		if ($this->config->show_header == '0') {
			$attributes['class'] .= ' block_hide_header';
		}

		if ($this->config->show_as_card == '0') {
			$attributes['class'] .= ' block_not_card';
		}

		if ($this->config->style == '0') {
			$attributes['class'] .= ' style1';
		} else if ($this->config->style == '1') {
			$attributes['class'] .= ' style2';
		}

	    return $attributes;
	}

    /**
     * Allows multiple instances of the block.
     */
	public function instance_allow_multiple() {
		return true;
	}

    /**
     * Enables block global configuration.
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where the block can be displayed.
     */
	public function applicable_formats() {
		return array('all' => true);
	}


	/**
 * Retornar os cursos da categoria
 *
 * @param string $string_categories
 * @return mixed
 */

 public function block_eixo_get_courses($string_categories) {
    global $DB;

    if (!empty($string_categories)) {
        $courses_record = $DB->get_records_sql('SELECT id FROM {course} WHERE category in ('.$string_categories.')');
    
		
	     $i =0;

		 foreach ($courses_record as $id => $record) {
			$courses[$i]= $record->id;
			$i++;

		 }
	
		
		return $courses;
    }
    return false;
}
	

	public function course_trail ($courses,$position,$top){

		global $DB,$USER;
		if($position == null){
			$position ='right';
		}

		foreach ($courses as $course_id) {
            if (!$DB->record_exists('course', array('id' => $course_id))) {
				continue;
			}

				// Course Image
				$course_image_url = course_image_url($course_id);

				// Course Data
				$course_record = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
				$course_element = new core_course_list_element($course_record);
				$course_context = context_course::instance($course_id);
	
				$course_short_name = format_text($course_record->shortname, FORMAT_HTML, array('filter' => true));
				$course_full_name = format_text($course_record->fullname, FORMAT_HTML, array('filter' => true));
				$course_created = $course_record->timecreated;
				$course_updated = $course_record->timemodified;
				$course_start = $course_record->startdate;
				$course_end = $course_record->enddate;
				$course_url = new moodle_url('/course/view.php', array('id' => $course_id));

				$enrol = $DB->get_records_sql('SELECT u.id, u.firstname,u.lastname, u.email 
				FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid 
				INNER JOIN mdl_context e ON rs.contextid=e.id
				WHERE e.contextlevel=50 AND rs.roleid=5 AND e.instanceid=?
				and u.id= ?',array($course_id,$USER->id));

				


		   
			// Pin do curso
			if ($this->config->show_image == '1') {
				$image = '
					<a href="' . $course_url . '">
						<div class="course-image card-img-top" style="background-image: url(' . $course_image_url . '); height: ' . $this->config->image_height .';border-radius: 25px ;">
						</div>
					</a>';
			} else {
				$image = '';
			}

				// Title
			if ($this->config->show_title == '0') {
				$title = '';
			} else if ($this->config->show_title == '1') {
				if (!$this->config->title_limit) {
					$title .= '<h3 class="course-title"><a href="' . $course_url . '">' . $course_full_name . '</a></h3>';
				} else {
					if(empty($enrol)){
						$img ='<div class="course-date img-container" data-toggle="tooltip" data-placement="'.$position.'"  data-html="true" title="' .$course_full_name . '">
						<img class="img-cinza"  src="'.$course_image_url.'" style="max-width:30%;float:'.$position.';" /></div>';
						$dots_string = $this->config->title_limit > strlen($course_full_name) ? '' : '...';
						$title = '<a href="' . $course_url . '">' .$img. '</a>';
					}else{
						$img ='<div class="course-date img-container" data-toggle="tooltip" data-placement="right"  data-html="true" title ="<i>' .$course_full_name . '</i>">
						<img   src="'.$course_image_url.'" style="max-width:30%;float:'.$position.';" /></div>';
						$dots_string = $this->config->title_limit > strlen($course_full_name) ? '' : '...';
						$title = '<a href="' . $course_url . '">' .$img. '</a>';

					}
				}
			} else if ($this->config->show_title == '2') {
				if (!$this->config->title_limit) {
					$title .= '<h3 class="course-title"><a href="' . $course_url . '">' . $course_short_name . '</a></h3>';
				} else {
					$dots_string = $this->config->title_limit > strlen($course_short_name) ? '' : '...';
					$title .= '<h3 class="course-title"><a href="' . $course_url . '">' . trim(substr($course_short_name, 0, $this->config->title_limit)) . '' . $dots_string . '</a></h3>';
			
				}
			}
		// Date
			if ($this->config->show_date == '0') {
				$date = '';
			} else{
				$date = '
					<div class="course-date" data-toggle="tooltip" title="' .$course_short_name . '">
						<i class="fa fa-calendar fa-fw icon" aria-hidden="true"></i>
						' . userdate($course_start, get_string('strftimedatefullshort', 'langconfig')) . '
					</div>';
			} 

			// Container que mostra a data
			if ($date ) {
				$td_container = '
					<div class="course-td-container">
					<img  style="max-width: 30%;float:right;" src="'.$course_image_url.'" size="20%"/>
						' . $date . '
					</div>';
			} else {
				$td_container = '';
			}


				$style_item = '
					<div>
						<div class="card-body"> 
							' . $title . '
							' . $td_container . '
						</div>
					</div>';
	
			// Course Items
			
				$item = '<div style="border:red;width:33%;display:inline-block;margin-top:'.$top.'";>' . $style_item . '</div>';
			
				return $item;
	}


	}

}




