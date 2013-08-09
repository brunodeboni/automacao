<?php
// Conexão com mysql
$conn = mysql_connect("186.202.121.119","webadmin","webADMIN") or die("Sem conex&atilde;o com o Banco de Dados");
mysql_select_db("cpcm_teste",$conn) or die("N&atilde;o foi possivel selecionar o Banco de Dados");

//Para enviar as informações ao banco de dados em UTF-8
mysql_set_charset("utf8");

//Verifica as turmas cheias que estão abertas e pega os dados necessários
$sql = "select
turmas.id as id_turma,
cursos.id as id_curso,
cursos.max_inscritos,
cursos.nome as curso,
(select count(*) from cw_cursos_inscritos as i where i.id_turma = turmas.id) as total_inscritos,
turmas.data_inicio,
turmas.horarios,
turmas.turno,
date_add(turmas.data_inicio, interval 7 day) as nova_data_inicio,
		
day(date_add(turmas.data_inicio, interval 7 day)) as novo_dia,		
month(date_add(turmas.data_inicio, interval 7 day)) as novo_mes,
		
day(date_add(turmas.data_inicio, interval 8 day)) as novo_dia1,
month(date_add(turmas.data_inicio, interval 8 day)) as novo_mes1,
		
day(date_add(turmas.data_inicio, interval 9 day)) as novo_dia2,
month(date_add(turmas.data_inicio, interval 9 day)) as novo_mes2,

current_date as hoje

from cw_cursos_turmas as turmas

join cw_cursos as cursos
on turmas.id_curso = cursos.id

where turmas.data_inicio > current_date
and turmas.status=0 
having total_inscritos = 38";
$query = mysql_query($sql, $conn);

while ($res = mysql_fetch_assoc($query)) {
	$turma = $res['id_turma'];
	$id_curso = $res['id_curso'];
	$curso = $res['curso'];
	$hoje = $res['hoje'];
	$horario = $res['horarios'];
	$turno = $res['turno'];
	$nova_data_inicio = $res['nova_data_inicio'];
	$novo_dia = $res['novo_dia'];
	$novo_dia1 = $res['novo_dia1'];
	$novo_dia2 = $res['novo_dia2'];
	$novo_mes = $res['novo_mes'];
	$novo_mes1 = $res['novo_mes1'];
	$novo_mes2 = $res['novo_mes2'];
	$max_inscritos = $res['max_inscritos'];
	$total_inscritos = $res['total_inscritos'];
	
	switch ($novo_mes) {
		case "1": $novo_mes = "janeiro"; break;
		case "2": $novo_mes = "fevereiro"; break;
		case "3": $novo_mes = "março"; break;
		case "4": $novo_mes = "abril"; break;
		case "5": $novo_mes = "maio"; break;
		case "6": $novo_mes = "junho"; break;
		case "7": $novo_mes = "julho"; break;
		case "8": $novo_mes = "agosto"; break;
		case "9": $novo_mes = "setembro"; break;
		case "10": $novo_mes = "outubro"; break;
		case "11": $novo_mes = "novembro"; break;
		case "12": $novo_mes = "dezembro"; break;
	}
	
	switch ($novo_mes1) {
		case "1": $novo_mes1 = "janeiro"; break;
		case "2": $novo_mes1 = "fevereiro"; break;
		case "3": $novo_mes1 = "março"; break;
		case "4": $novo_mes1 = "abril"; break;
		case "5": $novo_mes1 = "maio"; break;
		case "6": $novo_mes1 = "junho"; break;
		case "7": $novo_mes1 = "julho"; break;
		case "8": $novo_mes1 = "agosto"; break;
		case "9": $novo_mes1 = "setembro"; break;
		case "10": $novo_mes1 = "outubro"; break;
		case "11": $novo_mes1 = "novembro"; break;
		case "12": $novo_mes1 = "dezembro"; break;
	}
	
	switch ($novo_mes2) {
		case "1": $novo_mes2 = "janeiro"; break;
		case "2": $novo_mes2 = "fevereiro"; break;
		case "3": $novo_mes2 = "março"; break;
		case "4": $novo_mes2 = "abril"; break;
		case "5": $novo_mes2 = "maio"; break;
		case "6": $novo_mes2 = "junho"; break;
		case "7": $novo_mes2 = "julho"; break;
		case "8": $novo_mes2 = "agosto"; break;
		case "9": $novo_mes2 = "setembro"; break;
		case "10": $novo_mes2 = "outubro"; break;
		case "11": $novo_mes2 = "novembro"; break;
		case "12": $novo_mes2 = "dezembro"; break;
	}
	
	
	//Se são 2 dias de aula
	if ( ($id_curso == 4) || ($id_curso == 5) ) {
		//se o primeiro e segundo dias forem no mesmo mês
		if ($novo_mes == $novo_mes1) {
			$periodo = 'Dias '.$novo_dia.' e '.$novo_dia1.' de '.$novo_mes.'';
		}else { 
			$periodo = 'Dias '.$novo_dia.' de '.$novo_mes.' e '.$novo_dia1.' de '.$novo_mes1.'';
		}
	}else { //ou 3 dias de aula
		//se os 3 dias forem no mesmo mês
		if( ($novo_mes == $novo_mes1) && ($novo_mes == $novo_mes2) ) {
			$periodo = 'Dias '.$novo_dia.', '.$novo_dia1.' e '.$novo_dia2.' de '.$novo_mes.'';
		}else if ( ($novo_mes == $novo_mes1) && ($novo_mes != $novo_mes2) ) {
			$periodo = 'Dias '.$novo_dia.' e '.$novo_dia1.' de '.$novo_mes.' e '.$novo_dia2.' de '.$novo_mes2.'';
		}else if ($novo_mes != $novo_mes1) {
			$periodo = 'Dias '.$novo_dia.' de '.$novo_mes.', '.$novo_dia1.' e '.$novo_dia2.' de '.$novo_mes2.'';
		}
	}
	
	//Cria outra turma
	$sql2 = "insert into cw_cursos_turmas 
	(id_curso, data_publicacao, data_inicio, periodo, status, vezes_postergado, turno, horarios) 
	values ('".$id_curso."', '".$hoje."', '".$nova_data_inicio."', '".$periodo."', '0', '0', '".$turno."', '".$horario."')";
	$query2 = mysql_query($sql2, $conn);

}
?>