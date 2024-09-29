<?php

require_once(dirname(__FILE__) . '/../../config.php');
	require_once($CFG->dirroot . '/blocks/im_report/lib.php');
	require_once($CFG->libdir . '/grouplib.php');
	require_once($CFG->libdir . '/filelib.php' );
	require_once($CFG->dirroot . '/blocks/im_report/PHPExcel.php');

    date_default_timezone_set('America/Sao_Paulo');
  

    require_login();
    global $USER, $COURSE, $OUTPUT, $CFG, $DB;
	/*ini_set('display_errors',1);
	ini_set('display_startup_erros',1);
	error_reporting(E_ALL);*/
	error_reporting(0);
	date_default_timezone_set("America/Sao_Paulo");
        #set_time_limit(0);
 
$nome_arquivo='frequencia_alunos';

function excelColumnRange($start, $end) {
    $columns = [];
    
    // Gerar as colunas
    $current = $start;
    
    while (strcmp($current, $end) <= 0) {
        $columns[] = $current;
        $current = getNextExcelColumn($current);
    }

    return $columns;
}

function getNextExcelColumn($column) {
    $length = strlen($column);
    $index = $length - 1;

    while ($index >= 0) {
        if ($column[$index] !== 'Z') {
            $column[$index] = chr(ord($column[$index]) + 1);
            return $column;
        }
        $column[$index] = 'A';
        $index--;
    }

    // Caso a coluna seja toda Z (como "Z", "ZZ"), adiciona mais uma letra
    return 'A' . $column;
}

// Criar um novo objecto PHPExcel
$objPHPExcel = new PHPExcel();

function cellColor($cells,$color){
    global $objPHPExcel;
    
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
            'rgb' => $color
        )
    ));
}

function obter_aulas($year){
	global $DB;
    
   
	$sql_aulas ="SELECT mas.id,mas.description,mc.fullname  nome_curso , c.name as turma,
	mc.id as id_curso,
	to_char( to_timestamp(mas.sessdate ) , 'DD/MM/YY ') as data_aula ,
	to_char( to_timestamp(mas.sessdate ) , 'DD') as dia,
	to_char( to_timestamp(mas.sessdate ) , 'MM') as mes
	FROM mdl_attendance ma
	left join mdl_attendance_sessions mas on ma.id =mas.attendanceid 
	left join mdl_course mc on mc.id =ma.course 
	left join mdl_course_categories c on c.id = mc.category
	where  mc.id='55'
	order by mc.fullname ,mas.sessdate";

	
   $aulas = $DB->get_records_sql( $sql_aulas, array('year'=>$year));


   return $aulas;

}
function obter_aulas_curso($curso){
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
	order by mc.fullname,mas.sessdate";

	
   $aulas = $DB->get_records_sql( $sql_aulas, array('courseid'=>$curso));

   return $aulas;
}
try {
    
	// -----------------------
	// Selecionar os alunos matriculados / trancados no curso 
	$sql = "SELECT u.id,to_char(to_timestamp(mue.timestart), 'DD/MM/YYYY') as data_matricula ,
	 u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as aluno , mc.fullname as nome_curso,c.name as turma
   FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid 
	   INNER JOIN mdl_context e ON rs.contextid=e.id 
	   INNER  JOIN  mdl_enrol me  ON me.courseid = e.instanceid  
	   INNER  JOIN mdl_user_enrolments mue ON mue.userid =rs .userid  AND mue.enrolid =me.id 
	   INNER JOIN  mdl_course mc  on mc.id  =e.instanceid 
	   	left join mdl_course_categories c on c.id = mc.category
       --INNER JOIN mdl_groups mg  on mg.courseid = e.instanceid 
	 --  INNER  JOIN mdl_groups_members mgm  on mgm.groupid =mg.id  and mgm.userid =u.id 
	   WHERE e.contextlevel=50 AND rs.roleid=5
	 --  GROUP BY u.id
   order by c.name,mc.fullname,u.firstname";
	
	$alunos = $DB->get_records_sql( $sql);
     

       
} catch (Exception $e) {
  
    $mensagem = $e->getMessage();
    echo $mensagem;

    exit;
}

$aba = 0; $indice = 1;
//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'. $indice.':AA'. $indice);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'. $indice,' Número');
#cellColor('A'. $indice.':AA'. $indice,'FFFF00');
//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AB'. $indice.':AG'. $indice);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'. $indice,'Nome do Aluno');
#cellColor('AB'. $indice.':AG'. $indice,'4682B4');

//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AH'. $indice.':AR'. $indice);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'. $indice,'Curso');
#cellColor('AH'. $indice.':AR'. $indice,'F4A460');


$indice++;
   


         $dias_aulas =obter_aulas($year);
         $alfabeto = excelColumnRange('E', 'ZZ');
         $contador =0;
         $curso_anterior=null;
        // $mes =converterMesPorExtenso($month);
 /*for ($i=0;$i <count($dias_aulas);$i++) {
         //$html .= '<th style="text-rotate:90deg;">'.strtoupper(substr($mes,0,3)).'</th>';
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alfabeto[$contador]. $indice,$dias_aulas[$i]);
         $contador++;     
 }*/

 foreach ($dias_aulas as $id => $record) {
							$nome_curso= $record->nome_curso;
                            // Pega a ultima palavra do nome do curso que é a cidade
                            $turma= $record->turma;
                            $cidades =explode(" ", $turma);
                            $cidade =reset($cidades);
							$data_aula= $record->data_aula;
							$mes =converterMesPorExtenso($record->mes);
							$dia =$record->dia;
							$courseid =$record->id_curso;

                            $indice=1;
                            
							if ($curso_anterior != $nome_curso){
	
                               

                                $objPHPExcel->createSheet();
                                $objPHPExcel->setActiveSheetIndex($contador);
                                //Nome da aba
                                $objPHPExcel->getActiveSheet()->setTitle($nome_curso);
                                #cellColor('A'. $indice.':AA'. $indice,'FFFF00');

                                $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');

                             
                                #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E1:E8');
                          
// Adicionar uma imagem que ocupará 4 células
$drawing = new PHPExcel_Worksheet_Drawing();
$drawing->setName('Imagem');
$drawing->setDescription('Imagem do Relatório');
$drawing->setPath('pix/logo_aprendiz.png'); // Substitua pelo caminho da sua imagem
$drawing->setHeight(150); // Ajuste a altura da imagem
$drawing->setCoordinates('A1'); // Começa na célula A2
$drawing->setWorksheet($objPHPExcel->getActiveSheet());
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'. $indice,'Turma');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','Nome do curso');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','Unidade Temática');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I4','Cidade /Turma');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2',$nome_curso);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J3',$nome_curso);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J4', $cidade);


// Ajustar a largura das colunas, se necessário
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$indice=9;
$cont_aluno = 1;
                                $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('A'. $indice,'Número');
                                $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('B'. $indice ,'Nome do aluno');
                                $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('C' . $indice,'Nome da Turma');


                                $dias_aulas_curso =obter_aulas_curso($courseid);

                                // 1 linha da tabela : Datas da aula
                               $alfabeto = excelColumnRange('D', 'ZZ');
                               $cont_dias=0;
                                    foreach ($dias_aulas_curso as $id => $record) {
                                        $data_aula = $record->data_aula;
                                         if($data_aula_anterior != $data_aula) {

                                            $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$cont_dias]. $indice,$data_aula);
                                            $data_aula_anterior = $data_aula;
                                            $mesclar_data = $cont_dias +3;

                                           
                                            $objPHPExcel->setActiveSheetIndex(0)->mergeCells($alfabeto[$cont_dias]. $indice.':'.$alfabeto[$mesclar_data].$indice);
                                            

                                         }
                                            
                                         $cont_dias++;

                                    }

                                    $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$cont_dias]. $indice,'T');
                                    $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$cont_dias+1]. $indice,'%');

                                 
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E1:'.$alfabeto[$cont_dias+1].'1');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E2:'.$alfabeto[$cont_dias+1].'2');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E3:'.$alfabeto[$cont_dias+1].'3');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E4:'.$alfabeto[$cont_dias+1].'4');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E5:'.$alfabeto[$cont_dias+1].'5');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E6:'.$alfabeto[$cont_dias+1].'6');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E7:'.$alfabeto[$cont_dias+1].'7');
                                    #$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E8:'.$alfabeto[$cont_dias+1].'8');



                                    $indice++;
                                    // Selecionar os alunos matriculados / trancados no curso 
                                    $sql = "SELECT u.id, mue.status  ,mc.id as id_curso,
                                    u.firstname,u.lastname ,CONCAT(u.firstname,' ', u.lastname) as aluno , mc.fullname as nome_curso, mg.name  as turma
                                FROM mdl_role_assignments rs INNER JOIN mdl_user u ON u.id=rs.userid 
                                    INNER JOIN mdl_context e ON rs.contextid=e.id 
                                    INNER  JOIN  mdl_enrol me  ON me.courseid = e.instanceid 
                                    INNER  JOIN mdl_user_enrolments mue ON mue.userid =rs .userid  AND mue.enrolid =me.id 
                                    INNER JOIN  mdl_course mc  on mc.id  =e.instanceid
                                    INNER JOIN mdl_groups mg  on mg.courseid = e.instanceid  and  e.instanceid=:course
                                    INNER  JOIN mdl_groups_members mgm  on mgm.groupid =mg.id  and mgm.userid =u.id 
                                    WHERE e.contextlevel=50 AND rs.roleid=5
                                order by mc.fullname,mg.name,u.firstname";
                                    
                                    $alunos = $DB->get_records_sql( $sql,array('course'=>$courseid));

                                    #print_r($alunos);
                                    #die;
                                      

                                    if(!empty($alunos)){
        
                                        foreach ($alunos as $id => $record) {
                                            $nome= $record->aluno;
                                            $data_trasferencia= $record->data_transferencia;
                                            $status_matricula= $record->status;
                                            $nome_curso= $record->nome_curso;
                                            $nome_turma= $record->turma;
                                            $courseid =$record->id_curso;
                                            $studentid=$record->id;
        
                                               $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('A' . $indice,$cont_aluno);
                                               $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('B' . $indice, $nome);
                                               $objPHPExcel->setActiveSheetIndex($contador)->setCellValue('C' . $indice, $nome_turma);
                                                
                                                $sql = "SELECT mas.id,mal.studentid, mas2.acronym as presenca , mal.statusid  , 
                                                            CONCAT(mu.firstname,' ', mu.lastname) as aluno ,name,
                                                            to_char( to_timestamp(mas.sessdate ) , 'DD/MM/YY ') as data_aula ,
                                                            to_char( to_timestamp(mas.sessdate ) , 'DD') as dia,
                                                            to_char( to_timestamp(mas.sessdate ) , 'MM') as mes
                                                            FROM mdl_attendance ma
                                                            left join mdl_attendance_sessions mas on ma.id =mas.attendanceid 
                                                            inner join mdl_attendance_log mal  on mas.id =mal.sessionid  and mal.studentid =:studentid 
                                                            left join mdl_user mu  on mu.id =mal.studentid 
                                                            left join mdl_attendance_statuses mas2 on mas2.id = mal.statusid 
                                                            order by mas.sessdate";

                                                            $frequencia = $DB->get_records_sql( $sql, array('studentid'=>$studentid));

                          
	
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

	
                                                 $dias_aulas_assoc = $DB->get_records_sql( $sql_aulas, array('year'=>$year,'courseid'=>$courseid));

                                                 
                                                            $count_falta=0;
                                                            $count_presenca=0;
                                                             $aulas_ministradas= array();
                                                            $aulas_aluno=array();
                                                            $presenca=array();
                                                            $cont=0;
                                                            $i=0;
                                                            foreach ($frequencia as $id => $record) {
                                                                $aulas_aluno[$cont]= strtotime( $record->data_aula);
                                                                $presenca[$cont]= $record->presenca;
                                                                $cont++;	
                                                                    
                                                                } 
                                                                
                                                            
                                                                foreach ($dias_aulas_assoc as $id => $record) {
                                                                    $aulas_ministradas[$i]['data_aula'] =$record->data_aula;
                                                                    $i++;
                                                                }

                                                            for ($i=0;$i <count($aulas_ministradas);$i++) {
                                                                    $data_procurada = strtotime($aulas_ministradas[$i]['data_aula']);
                                                                    // Buscar a posição do array das aulas lançadas pelo aluno
                                                                    $p = array_search($data_procurada,$aulas_aluno);

                                                                    if ($p !== false) {
                                                                        if($presenca[$p]=='Pr' || $presenca[$p]=='P'){
                                                                            $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$i] . $indice,'P');
                                            
                                                                            $count_presenca++;
                                                                        }else{
                                                                           $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$i] . $indice,'F');
                                                                                $count_falta++;
                                                                        
                                                                    } 
                                                                    
                                                                }else{
                                                                   $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$i] . $indice,'-----');
                                                                    
                                                                
                                                                }
                                                                
                                                        }
                                                            $total_aulas=count($aulas_aluno);
                                                            if($total_aulas >0){

                                                                        $porcentagem= round((((float)$count_presenca)/(float)$total_aulas )*100,2);

                                                            }else{
                                                                $porcentagem='--';
                                                            }
                                                            $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$i] . $indice,$count_presenca.'/'. count($aulas_aluno));
                                                            $objPHPExcel->setActiveSheetIndex($contador)->setCellValue($alfabeto[$i +1] . $indice,$porcentagem);
                                                                    
                                                
                                                
                                            
                                            
                                            
                                                $indice++;
                                                $cont_aluno++;
                 
                                                        }
                                        }
                                $contador++;
                                $curso_anterior = $nome_curso;


                            



                                     // Primeira linha em negrito
         
         $from = "A1"; // or any value
         $to = "IE1"; // or any value
         $objPHPExcel->getActiveSheet($contador)->getStyle("$from:$to")->getFont()->setBold( true );
                                      // Alinhar o tamanho da  coluna de acordo com tamanho do texto
         foreach(range('A',$objPHPExcel->getActiveSheet($contador)->getHighestDataColumn()) as $columnID) {
            $objPHPExcel->getActiveSheet($contador)->getColumnDimension($columnID)->setAutoSize(true);
        }
                            }
                          
							//$dados_curso = cabecalho_curso($courseid);
						

                          

       
 }
        
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$nome_arquivo.'.xls"');
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

// Encaminhar o ficheiro resultante para abrir no browser ou fazer download

?>