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
 * Game block language strings
 *
 * @package    block_cb_report
 * @copyright  2022  Vanessa Cristine Silva
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\session\manager;
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->libdir . '/filelib.php' );
defined('MOODLE_INTERNAL') || die();

function block_trail_get_config_block($courseid) {
    global $DB;
    $coursecontext = \context_course::instance($courseid);
    $blockrecords = $DB->get_records('block_instances', array('blockname' => 'im_report', 'parentcontextid' => $coursecontext->id));
    foreach ($blockrecords as $b) {
        $blockinstance = \block_instance('im_report', $b);
    }
    if (isset($blockinstance->config)) {
        return $blockinstance->config;
    }
    return false;
}
/*

*/
function course_trail ($course_id,$position,$padding,$margin){

		global $DB, $USER,$CFG;
		/*Comentado para implentar array com vários cursos*/
		/*foreach ($courses as $course_id) {
            if (!$DB->record_exists('course', array('id' => $course_id))) {
				continue;
			}/*/

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

					$lock= $CFG->wwwroot.'/blocks/cb_report/pix/lock.png';
					$margin_lock= $margin +4;
					$position_lock='right:25px;';
				// Verificar se o usuário logado está matriculado
				if(empty($enrol)){

					/*$img ='<div  class="img-container" data-toggle="tooltip" data-placement="'.$position.'"  data-html="true" title="' .$course_full_name . '<br><i class=\'fa fa-calendar fa-fw icon\'></i>'.userdate($course_start,get_string('strftimedatefullshort', 'langconfig')).'<br>">
					<div  style="position:relative;">
						<img src="'.$course_image_url.'" style="width:25%;float:'.$position.';margin-left:'.$margin.'em" /></div>
						<div style="position:absolute">
							<img src="'.$lock.'" style="width:20%;float:'.$position.';margin-left:'.$margin_lock.'em";" />
						</div>
					</div>';*/
                         if($position=='left'){
							 $position_lock='right:25px;';
						 }else{
							$position_lock='left:310px;';
						 }
					$img ='<div   class="img-container" data-toggle="tooltip" data-placement="'.$position.'"  data-html="true" title="' .$course_full_name . '<br><i class=\'fa fa-calendar fa-fw icon\'></i>'.userdate($course_start,get_string('strftimedatefullshort', 'langconfig')).'<br>">


					   <figure>
						    <img src="'.$course_image_url.'" style="width:25%;float:'.$position.';margin-left:'.$margin.'em" />

							<img src="'.$lock.'" style="width:10%;position:relative;'.$position_lock.'"/>

							</figure>

						</div>';

					$title = $img;
				}else{
					$img ='<div class="img-container" data-toggle="tooltip" data-placement="right"  data-html="true" title ="<i>' .$course_full_name . '</i><br><i class=\'fa fa-calendar fa-fw icon\'></i>'.userdate($course_start,get_string('strftimedatefullshort', 'langconfig')). '">
					<img   src="'.$course_image_url.'" style="width:25%;float:'.$position.';margin-left:'.$margin.'em;" /></div>';
					$title = '<a href="' . $course_url . '">' .$img. '</a>';

				}


			// Course Items

				$item = '<div class="col-4" style="display:inline-block;padding-top:'.$padding.'em;">' . $title . '</div>';

				return $item;
//	}

}


function get_frequencia_aluno ($course,$studentid,$status_matricula){
	global $DB, $CFG;
// Buscar as aulas por semestre  com as frequencias do estudante selecionado
	$sql = "SELECT mas.id,mal.studentid, mas2.acronym as presenca , mal.statusid  ,
	CONCAT(mu.firstname,' ', mu.lastname) as aluno ,name,
	 to_char( to_timestamp(mas.sessdate) , 'DD-MM-YYYY ') as data_aula,finalgrade as nota_final
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid
	inner join mdl_attendance_log mal  on mas.id =mal.sessionid
	left join mdl_user mu  on mu.id =mal.studentid
	left join mdl_attendance_statuses mas2 on mas2.id = mal.statusid
	inner join mdl_grade_items i  on i.courseid  =ma.course  and itemtype='course'
	INNER JOIN mdl_grade_grades g ON i.id=g.itemid and g.userid = mal.studentid
	where    mal.studentid =:studentid and ma.course = :course
	order by mas.sessdate";

    $frequencia = $DB->get_records_sql( $sql, array('studentid'=>$studentid,'course'=> $course));




	$sql_aulas ="SELECT mas.id,
	 to_char( to_timestamp(mas.sessdate) , 'DD-MM-YYYY ') as data_aula,
	 to_char( to_timestamp(mas.sessdate)  , 'DD') as dia,
	 to_char( to_timestamp(mas.sessdate) , 'MM') as mes
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid
	left join mdl_course mc on mc.id =ma.course
	where  mc.id=:course
	order by mas.sessdate ";


   $dias_aulas_assoc = $DB->get_records_sql( $sql_aulas, array('course'=>$course));

	$tabela_frequencia = "";
	$count_falta=0;
	$count_presenca=0;
    // $dias_aulas= obterDiasDaSemana($month);
   $aulas_aluno=array();
	 $presenca=array();
	 $cont=0;
	 $i=0;
	foreach ($frequencia as $id => $record) {
		$aulas_aluno[$cont]= strtotime( $record->data_aula);
		#$presenca[$cont][$record->id]= $record->id;
		$presenca[$record->id]= $record->presenca;
		$conceito =gerarLetraNumero(round($record->nota_final,0));
		$nota_final =$conceito;
		$cont++;

		}


		foreach ($dias_aulas_assoc as $id => $record) {
			$dias_aulas[$i]['id'] =$record->id;
			$dias_aulas[$i]['data_aula'] =$record->data_aula;
			$i++;
		}

	for ($i=0;$i <count($dias_aulas);$i++) {
			#$data_procurada = strtotime($dias_aulas[$i]['data_aula']);
			// Buscar a posição do array das aulas lançadas pelo aluno
			#print_r($dias_aulas[$i]);
			#$p = array_search($data_procurada,$aulas_aluno);

			$aula_procurada = $dias_aulas[$i]['id'];
			// Buscar a posição do array das aulas lançadas pelo aluno
			#print_r($dias_aulas[$i]);
			$p = array_search($aula_procurada,$presenca);

			if(array_key_exists($aula_procurada,$presenca)){
				#echo $presenca[$aula_procurada];
				if($presenca[$aula_procurada]=='Pr' || $presenca[$aula_procurada]=='P'){
					$tabela_frequencia .= '<td style="width:1%;text-align:center;"> P </td>';
					$count_presenca++;
				}else{
						$tabela_frequencia .= '<td style="width:1%;text-align:center;"> F </td>';
						$count_falta++;

			}
			}else{
				#echo 'n�o tem lan�amento para essa aula';
				$tabela_frequencia .= '<td style="width:1%;text-align:center;">-- </td>';
			}


   }
      $total_aulas=count($aulas_aluno);
	  if($total_aulas >0){

       			$porcentagem= round((((float)$count_presenca)/(float)$total_aulas )*100,2);

	  }else{
		$porcentagem='--';
	  }
		$numero_faltas = count($aulas_aluno) - $count_presenca ;
	$tabela_frequencia .= '<td  style="width:1%;text-align:center;">'.$numero_faltas.' </td>';

	if($status_matricula == 1){
		$tabela_frequencia .= '<td  style="width:1%;text-align:center;">D</td>';
	}else {
		$tabela_frequencia .= '<td  style="width:1%;text-align:center;">'.$nota_final.' </td>';
	}



	return $tabela_frequencia;

}




// Get Course image url by Course ID
 function course_image_url($courseid) {
	global $DB, $CFG;

	$courserecord = $DB->get_record('course', array('id' => $courseid));
	$course = new core_course_list_element($courserecord);

	foreach ($course->get_course_overviewfiles() as $file) {
		$isimage = $file->is_valid_image();
		$url = file_encode_url("$CFG->wwwroot/pluginfile.php",
			'/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
			$file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
		if ($isimage) {
			return $url;
		}
	}
}
 function converterMesPorExtenso($mes) {
	switch ($mes) {

		case 1: $mes = "JANEIRO"; break;
		case 2: $mes = "FEVEREIRO"; break;
		case 3: $mes = "MARÇO"; break;
		case 4: $mes = "ABRIL"; break;
		case 5: $mes = "MAIO"; break;
		case 6: $mes = "JUNHO"; break;
		case 7: $mes = "JULHO"; break;
		case 8: $mes = "AGOSTO"; break;
		case 9: $mes = "SETEMBRO"; break;
		case 10: $mes = "OUTUBRO"; break;
		case 11: $mes = "NOVEMBRO"; break;
		case 12: $mes = "DEZEMBRO"; break;

	}
	return $mes;
}

function obterDiasDaSemana($mes) {
    $diasDaSemana = array();
    $dataAtual = date('Y-' . $mes . '-01'); // primeiro dia do mês
    $ultimoDia = date('t', strtotime($dataAtual)); // último dia do mês

    // iterar sobre cada dia do mês e adicionar à lista se for um dia da semana
    for ($dia = 1; $dia <= $ultimoDia; $dia++) {
        $data = date('Y-' . $mes . '-' . $dia);
        $diaDaSemana = date('N', strtotime($data)); // 1 para segunda-feira, 7 para domingo
        if ($diaDaSemana <= 5) { // apenas dias úteis (segunda a sexta)
            $diasDaSemana[] = $data;
        }
    }

    return $diasDaSemana;
}

function gerarLetraNumero($numero) {

 $nota = intval($numero);
	$letra = '';

	if ($nota >= 90) {
		$letra = 'OT';
} elseif ($nota > 70 && $nota <=89) {
	$letra = 'MB';
} elseif ($nota > 60 && $nota <=70) {
	$letra = 'B';
} else {
	$letra = 'R';
}


			return  "$letra";

}