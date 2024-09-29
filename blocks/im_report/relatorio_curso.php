<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	require_once($CFG->dirroot . '/blocks/im_report/lib.php');
	require_once($CFG->libdir . '/grouplib.php');
	require_once($CFG->libdir . '/filelib.php' );
	require_once($CFG->dirroot . '/blocks/im_report/mpdf/vendor/autoload.php');
	
	//defined('MOODLE_INTERNAL') || die();
	require_login();
	
	global $USER, $COURSE, $OUTPUT, $CFG;
error_reporting(0);
	use  \Mpdf\Mpdf;
	//require_once($_SERVER['DOCUMENT_ROOT'] . "/class/Helper.class.php");
//	require_once($_SERVER['DOCUMENT_ROOT'] . "/class/Util.class.php");


	//$course =61;

	$courseid = required_param('courseid', PARAM_INT);
	$month=required_param('month', PARAM_INT);

	//$sql= "select  instance as trimestre from mdl_course_modules where id=:sessionid";
	
	//$session = $DB->get_records_sql( $sql, array(sessionid=>$sessionid));


///foreach ($session as $id => $record) {
	//$attendeceid = $record->trimestre;
//}

	$html ='<body style="font-family: serif; font-size: 10pt;">';
	
	
	
	//SET lc_time_names = 'pt_BR';
	$sql_aulas= "SELECT mas.id,mas.description,
	from_unixtime( mas.sessdate  , '%Y-%m-%d ') as data_aula ,
	from_unixtime( mas.sessdate  , '%d') as dia,
	from_unixtime( mas.sessdate  , '%m') as mes
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid 
	where MONTH(FROM_UNIXTIME(mas.sessdate)) =:month and course =:course
	group by DAY(FROM_UNIXTIME(mas.sessdate))
	ORDER BY mas.sessdate ";

   $aulas = $DB->get_records_sql( $sql_aulas, array(month=>$month,course=>$courseid));
	
	// Selecionar os alunos matriculados / trancados no curso 
	$sql = "SELECT u.id, mue.status ,	from_unixtime( mue.timeend  , '%d/%m/%Y ') as data_transferencia ,
	 u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as aluno , mc.fullname as nome_curso
   FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid 
	   INNER JOIN mdl_context e ON rs.contextid=e.id 
	   INNER  JOIN  mdl_enrol me  ON me.courseid = e.instanceid 
	   INNER  JOIN mdl_user_enrolments mue ON mue.userid =rs .userid  AND mue.enrolid =me.id 
	   INNER JOIN  mdl_course mc  on mc.id  =e.instanceid and e.instanceid =:course
	   WHERE e.contextlevel=50 AND rs.roleid=5
	   GROUP BY u.id
   order by mc.fullname,u.firstname";
	
	$alunos = $DB->get_records_sql( $sql, array(course=>$courseid));

	   
	
	    $html .= '<table  class="alunos" style="overflow: wrap;border:1px solid #000000;" cellPadding="1">';
	    $html .= '<tbody><tr>';
		$html .= '<th style="text-align:right;" colspan="2"></th>';
	    $html .= '<th style="text-align:right;">MÊS</th>';
		$dias_aulas =obterDiasDaSemana($month);
                $mes =converterMesPorExtenso($month);
		for ($i=0;$i <count($dias_aulas);$i++) {
		    	$html .= '<th style="text-rotate:90deg;">'.strtoupper(substr($mes,0,3)).'</th>';
	
				
		}
		$html .= '<th rowspan="2" style="text-align:center;">%</th>';
	    $html .= '</tr>';
	    $html .= '<tr>';
	    $html .= '<th style="text-align:center;">Nº</th>';
	    $html .= '<th  width="50%"style="text-align:center;">NOME DO ALUNO</th>';
		$html .= '<th width="1%" style="text-align:right;">DIA</th>';
	    $cont=0;
		
	
		for ($i=0;$i <count($dias_aulas);$i++) {
			$dia = date('d', strtotime($dias_aulas[$i]));
				$html .= '<th style="text-align:center;">'.$dia.'</th>';
				
		}
	

		$html .= '</tr>';
		

		foreach ($alunos as $id => $record) {
			$nome= $record->aluno;
			$data_trasferencia= $record->data_transferencia;
			$status_matricula= $record->status;
			$nome_curso= $record->nome_curso;
			 
                $cont++;
				$html .= '<tr>';
				$html .= '<td width="2%" style="text-align:center;">'.$cont.'</td>';
				if($status_matricula == 1){
					$html .= '<td width="40%" colspan="2" style="text-align:left;">'.$nome.'<i> (Transferido em:'.$data_trasferencia.')</i></td>';
				}else{
					$html .= '<td width="40%" colspan="2" style="text-align:left;">'.$nome.'</td>';
				}
				
				$html .= get_frequencia_aluno($month,$record->id);
				$html .= '</tr>';

		}
		$html .= '</tbody></table>';
	   




	$mpdfConfig = array(
	    'mode' => 'utf-8',
	    'format' => 'A4',
	   // 'margin_header' => 30,     // 30mm not pixel
	  //  'margin_footer' => 10,     // 10mm
	    'orientation' => 'L',
	    margin_top =>52,
		margin_left=>5,
		margin_right=>5,
	    'default_font_size' => 10



	);
	$mpdf = new Mpdf($mpdfConfig);
	
$stylesheet = file_get_contents($CFG->wwwroot . '/blocks/cb_report/styles.css');
	
  $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
	
// Selecionar professor do curso
$sql = "SELECT u.id, u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as regente 
FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid 
INNER JOIN mdl_context e ON rs.contextid=e.id 
WHERE e.contextlevel=50 AND rs.roleid=3 AND e.instanceid=:course

order by u.firstname";

$regente = $DB->get_records_sql( $sql, array(course=>$courseid));

foreach ($regente as $id => $record) {
	$professor_regente=$record->regente;
}

// Selecionar os dados do curso
$sql = "SELECT  shortname as turma,
fullname  as componente,
mcc.name as curso ,
mcc.parent ,
mcc2.name  as escola,
from_unixtime( mc .startdate  , '%Y') as ano
from mdl_course mc 
left join mdl_course_categories mcc  on mcc.id =mc.category
left join mdl_course_categories mcc2  on mcc2.id =mcc.parent 
where mc.id=:course";

$dados_curso = $DB->get_records_sql( $sql, array(course=>$courseid));

foreach ($dados_curso as $id => $record) {
	$turma=$record->turma;
	$componente =$record->componente;
	$nome_curso =$record->curso;
	$ano = $record->ano;
	$escola =$record->escola;
}
$count_aulas= count($aulas);

    $mpdf->SetHTMLHeader('<table  cellspacing=2 width="100%" style="border: 1px solid;vertical-align: bottom; font-family: serif; font-size:10pt; color: #000;"><tr>
<td width="10%" style="border-color:#FFF;"><img  width="20%" src="'.	$CFG->wwwroot.'/blocks/im_report/pix/instituto-logotipo-orange.png"/></td>
<td width="33%" style="border-color:#FFF;text-align:left;vertical-align:top !important;"><br/>Instituto Manager<br/>
 <br/>
 <span>'.$escola.'</span><br/>
 R. Colocar endereço <br/>
  Fone: colocar telefone

 </td>
<td width="33%" style="border-left: 1px solid #000;padding-left:8px; border-color:#000;vertical-align:top !important;"> <br/>
Calendário:'.$ano.'<br/>
Curso:'.$componente.'<br/>
Mês:'.$mes.'<br/>
Professor:'.strtoupper($professor_regente).'<br/>
</td>
<td width="33%" style="border-color:#FFF;text-align:right;vertical-align:bottom !important;margin-right:5px;"><span style="font-weight: bold;">Diário de Classe de Frequência</span><br/>
<br/><br/><br/><br/><br/><br/>
 '.$turma.'<br/>
Aulas dadas: '.$count_aulas.'<br/>
</td>
</tr></table>','O');
    $mpdf->SetHTMLHeader('<div style="float:left;border-bottom: 1px solid #000000;">Left</div>','E');
    

	$mpdf->SetHTMLFooter('
<table width="100%" cellspacing=2 width="100%" style="border: 1px solid;vertical-align: bottom; font-family: serif; font-size:10pt; color: #000;">
    <tr>
        <td width="25%">Entregue em :____ /_____/______           </td>
		<td width="25%"> POR:_____________________________ </td>
        <td width="25%" align="center"> Revisado em: ____ /_____/______</td> 
        <td  style="text-align: right;">POR:_____________________________ </td>
    </tr>
	
</table><tr>

');
//Data retirada do relatório

//<div width="100%" style="text-align: right;">{DATE j/m/Y H:i} </div>
$mpdf->SetDisplayMode('fullpage');
	$mpdf->WriteHTML($html);
	$mpdf->Output('relatorio_frequencia.pdf','I');

	

	echo $html;

   
?>