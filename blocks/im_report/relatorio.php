  <?php
	require_once(dirname(__FILE__) . '/../../config.php');
	require_once($CFG->dirroot . '/blocks/im_report/lib.php');
	require_once($CFG->libdir . '/grouplib.php');
	require_once($CFG->libdir . '/filelib.php' );
	require_once($CFG->dirroot . '/blocks/im_report/mpdf/vendor/autoload.php');

	date_default_timezone_set('America/Sao_Paulo');


	//defined('MOODLE_INTERNAL') || die();
	require_login();
	global $USER, $COURSE, $OUTPUT, $CFG;
error_reporting(0);
	use  \Mpdf\Mpdf;
	//require_once($_SERVER['DOCUMENT_ROOT'] . "/class/Helper.class.php");
//	require_once($_SERVER['DOCUMENT_ROOT'] . "/class/Util.class.php");


$instanceid =$_POST['instanceid'];

$sql ="select course from mdl_course_modules where id =:instance";
$dados_curso = $DB->get_records_sql( $sql, array(instance=>$instanceid));

foreach ($dados_curso as $id => $record) {
	$courseid =$record->course;
}

	#$courseid = required_param('courseid', PARAM_INT);
	#$month=required_param('month', PARAM_INT);


	$mpdfConfig = array(
	    'mode' => 'utf-8',
	    'format' => 'A4',
	   // 'margin_header' => 30,     // 30mm not pixel
	  //  'margin_footer' => 10,     // 10mm
	    'orientation' => 'L',
	    margin_top =>45,
		margin_left=>5,
		margin_right=>5,
	    'default_font_size' => 10



	);
	$mpdf = new Mpdf($mpdfConfig);

$stylesheet = file_get_contents($CFG->wwwroot . '/blocks/cb_report/styles.css');

  $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

	//$sql= "select  instance as trimestre from mdl_course_modules where id=:sessionid";

	//$session = $DB->get_records_sql( $sql, array(sessionid=>$sessionid));


///foreach ($session as $id => $record) {
	//$attendeceid = $record->trimestre;
//}

	$html ='<body style="font-family: serif; font-size: 10pt;">';


	$curso_anterior= null;
		        // Buscar as aulas de acordo com o ano ministrado
				$dias_aulas =obter_aulas($courseid);

				$count_aulas= count($dias_aulas);

						foreach ($dias_aulas as $id => $record) {
							$nome_curso= $record->nome_curso;
							$data_aula= $record->data_aula;
							$mes =converterMesPorExtenso($record->mes);
							$dia =$record->dia;
							$courseid =$record->id_curso;

							$dados_curso = cabecalho_curso($courseid);
							$dias_aulas_curso =obter_aulas_curso($courseid);



							if ($curso_anterior != $nome_curso){


								$professor =$dados_curso['professor'];
							 #$carga_horaria = $count_aulas * 4;

							 $carga_horaria = $count_aulas;

								$html_header ='<table  cellspacing=2 width="100%" style="border: 1px solid;vertical-align: bottom; font-family: serif; font-size:10pt; color: #000;"><tr>
							<td width="25%" style="border-color:#FFF;"><img  width="25%" src="'.	$CFG->wwwroot.'/blocks/im_report/pix/cabecalho_diario.png"/></td>
							<td width="33%" style="border-left: 1px solid #000;padding-left:8px; border-color:#000;vertical-align:top !important;"> <br/>
							<b>Nome do curso:</b> '.strtoupper($dados_curso['nome_curso']).'<br/>
							Unidade Temática:<b>'.$nome_curso.'</b><br/>
							Professor(a): '.strtoupper($dados_curso['professor']).'<br/>
							Cidade / Turma: '.strtoupper($dados_curso['cidade']).'
							</td>
							<td width="33%" style="border-color:#FFF;text-align:left;vertical-align:bottom !important;margin-right:5px;"><span style="font-weight: bold;">Diário de Classe de Frequência</span><br/><br/>
							Carga horária : '.$carga_horaria.'<br/>
							Perí­odo : '.strtoupper($dados_curso['periodo']).'<br/>
							Turno : '.strtoupper($dados_curso['turno']).'      Hora:'.$dados_curso['horario'].'
							</td>
							</tr></table>';

							$html = '<table  border="1" class="alunos" style="overflow: wrap;border:1px solid #000000;" cellPadding="1">';
							$html .= '<thead>';

							$html .= '<tr>';
										$html .= '<th  width="2%" style="text-align:center;">N°</th>';
										$html .= '<th  width="15%"style="text-align:center;">NOME DO ALUNO</th>';
										$cont=0;

									    #$dias_aulas_curso =obter_aulas_curso($courseid);

										//for ($i=0;$i <count($dias_aula_curso);$i++) {
										foreach ($dias_aulas_curso as $id => $record) {
											$dia = $record->data_aula;
											$html .= '<th style="text-rotate:90deg;">'.$dia.'</th>';

										}

										$html .= '<td width="2%" style="text-align:center;"><b>Faltas</b></td>';
										$html .= '<td width="2%" style="text-align:left;"><b>Conceito</b></td>';

										$html .= '</tr></thead>';

							// Selecionar os alunos matriculados / trancados no curso
							$sql = "SELECT u.id, mue.status ,	to_char( to_timestamp(mue.timeend)  , 'DD/MM/YYYY ') as data_transferencia ,mc.id as id_curso,
							to_char( to_timestamp(mue.timestart)  , 'DD/MM/YYYY ') as data_matricula,
						  to_timestamp( mue.timestart)::DATE - to_timestamp( mc.startdate ) ::DATE dias,
							u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as aluno , mc.fullname as nome_curso
						FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid
							INNER JOIN mdl_context e ON rs.contextid=e.id
							INNER  JOIN  mdl_enrol me  ON me.courseid = e.instanceid
							INNER  JOIN mdl_user_enrolments mue ON mue.userid =rs .userid  AND mue.enrolid =me.id
							INNER JOIN  mdl_course mc  on mc.id  =e.instanceid
							WHERE e.contextlevel=50 AND rs.roleid=5  AND e.instanceid=:course
						order by mc.fullname,u.firstname";

							$alunos = $DB->get_records_sql( $sql,array('course'=>$courseid));


								foreach ($alunos as $id => $record) {
									$nome= $record->aluno;
									$data_trasferencia= $record->data_transferencia;
									$status_matricula= $record->status;
									//$nome_curso= $record->nome_curso;
									$nome_turma= $record->turma;
									$courseid =$record->id_curso;
									$data_matricula = $record->data_matricula;
									$dias  = $record->dias;

									$cont++;
									$html .= '<tr>';
									$html .= '<td width="2%" style="text-align:center;">'.$cont.'</td>';
									if($status_matricula == 1){
										$html .= '<td width="10%"  style="text-align:left;">'.$nome.'<i> ( Desligado em:'.$data_trasferencia.')</i></td>';
									}else{
										if($dias > 1){

											$html .= '<td width="10%"  style="text-align:left;">'.$nome.'<i> (Matrículado em: '.$data_matricula.')</i></td>';
										}else {
											$html .= '<td width="15%"  style="text-align:left;">'.$nome.'</td>';
										}
									}


									$html .= get_frequencia_aluno($courseid,$record->id,$status_matricula);
									$html .= '</tr>';


									}

									$html .= '</table>';

									$html .= '<div>CONCEITOS:  DESISTENTE: D     EVADIDO: EV     REGULAR: R     BOM: B     MUITO BOM: MB     ÓTIMO: OT	</div>';
									$mpdf->SetHTMLHeader($html_header);

									$mpdf->WriteHTML($html);
									$curso_anterior = $record->nome_curso;
								}else{

								}






		}


		// Escrever a tabela na tela




$mpdf->SetHTMLHeader($html_header);

	$mpdf->SetHTMLFooter('
	<hr>
	<div width="100%" style="text-align: right;"> Diário de frenquêcia gerado em : {DATE j/m/Y H:i} por '.$professor. '</div>

');
//Data retirada do relatório

//
$mpdf->SetDisplayMode('fullpage');


// Adicionar uma página
$mpdf->AddPage();


// Definir o conteudo HTML usando o HTMLWriter
$html = '
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Data</th>
        <th>Tópico</th>
    </tr>';

foreach ($dias_aulas as $id => $record) {
	$descricao= $record->description;
	$data_aula= $record->data_aula;

	$html .= '
    <tr>
        <td>'.$data_aula.'</td>
        <td>'.$descricao.'</td>
    </tr>
';

	}
	$html .= '</table>';
// Escrever o conteÃÂÃÂÃÂÃÂºdo HTML no PDF usando o HTMLWriter
$mpdf->writeHTML($html);


$mpdf->Output('diario_frequencia.pdf','I');





function cabecalho_curso($courseid){
	global $DB;

					// Selecionar professor do curso
			$sql = "SELECT u.id, u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as regente ,
			mcc.name as cidade ,
      mcc2.name  as nome_curso,
			to_char( to_timestamp(c .startdate) AT TIME ZONE 'Europe/Lisbon','DD/MM/YYYY') as inicio,
			to_char( to_timestamp(c .enddate) AT TIME ZONE 'Europe/Lisbon', 'DD/MM/YYYY') as fim
			FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid
			INNER JOIN mdl_context e ON rs.contextid=e.id
			INNER JOIN mdl_course c ON c.id = e.instanceid
			left join mdl_course_categories mcc  on mcc.id =c.category
      left join mdl_course_categories mcc2  on mcc2.id =mcc.parent
			WHERE e.contextlevel=50 AND rs.roleid=3 AND e.instanceid=:course

			order by u.firstname";

			$dados_curso = $DB->get_records_sql( $sql, array(course=>$courseid));

			foreach ($dados_curso as $id => $record) {
				$curso['professor']=$record->regente;
				$curso['ano'] =$record->ano;
				$curso['cidade']=$record->cidade;
				$curso['nome_curso']=$record->nome_curso;
				$curso['periodo']=$record->inicio.' à '.$record->fim;
}
 $sql ="SELECT mc.fullname as nome_curso,
 mc.id as id_curso,
 mcd.value as hora_inicial,mcd2.value as hora_final
	 FROM mdl_customfield_data mcd
	 INNER join mdl_customfield_field mcf on mcf.id =mcd.fieldid
	 inner join mdl_course mc on mc.id =mcd.instanceid
		 INNER JOIN mdl_customfield_data mcd2 ON mcd2.instanceid =mc.id
		 INNER JOIN mdl_customfield_field  mcf2 on mcf2.id =mcd2.fieldid
	 where
	 mc.id = :course and mcf.shortname ='hora_inicial' and mcf2.shortname ='hora_final'";
$dados_curso = $DB->get_records_sql( $sql, array(course=>$courseid));
foreach ($dados_curso as $id => $record) {
	$curso['horario']=$record->hora_inicial.' às '.$record->hora_final;
	$curso['turno'] = determinarPeriodo($record->hora_inicial);
}


return $curso;

}

function obter_aulas($courseid){
	global $DB;

	$sql_aulas ="SELECT mas.id,mas.description,mc.fullname  nome_curso ,
	mc.id as id_curso,
	(mas.duration/3600 ) as horas,
	to_char( to_timestamp(mas.sessdate ) , 'DD/MM/YY ') as data_aula ,
	to_char( to_timestamp(mas.sessdate ) , 'DD') as dia,
	to_char( to_timestamp(mas.sessdate ) , 'MM') as mes
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid
	left join mdl_course mc on mc.id = ma.course
	where mc.id =:courseid
	order by mc.fullname,mas.sessdate ";


   $aulas = $DB->get_records_sql( $sql_aulas, array('courseid'=>$courseid));

   return $aulas;

}
function obter_aulas_curso($curso){
	global $DB;


	$sql_aulas ="SELECT mas.id,
	 to_char( to_timestamp(mas.sessdate) , 'DD-MM-YY ') as data_aula,
	 to_char( to_timestamp(mas.sessdate)  , 'DD') as dia,
	 to_char( to_timestamp(mas.sessdate) , 'MM') as mes
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid
	left join mdl_course mc on mc.id =ma.course
	where  mc.id=:course
	order by mas.sessdate  ";


   $aulas = $DB->get_records_sql( $sql_aulas, array('course'=>$curso));




   return $aulas;

}

function determinarPeriodo($hora) {
    // Extrair a parte da hora (antes dos dois pontos)
    $partesHora = explode(':', $hora);
    $hora = intval($partesHora[0]);

    if ($hora >= 6 && $hora < 12) {
        return "manhã";
    } elseif ($hora >= 12 && $hora < 18) {
        return "tarde";
    } else {
        return "noturno";
    }
}

?>