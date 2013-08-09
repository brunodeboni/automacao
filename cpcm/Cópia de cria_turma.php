<?php
function decode_telefone($telefone){
	$telefone = trim($telefone);
	if($telefone=="") return "";
	$nums = "0123456789";

	$numsarr = str_split($nums);
	$telsarr = str_split($telefone);

	$novo_telefone = "";

	foreach($telsarr as $tel){
		$ex = false;
		foreach($numsarr as $num){
			if($tel == $num){
				$ex = true;
				break;
			}
		}

		if($ex) $novo_telefone .= $tel;
	}

	return $novo_telefone;
}

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
turmas.status=0
having total_inscritos > cursos.max_inscritos";
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
			echo $periodo."<br>";
		}else { 
			$periodo = 'Dias '.$novo_dia.' de '.$novo_mes.' e '.$novo_dia1.' de '.$novo_mes1.'';
			echo $periodo."<br>";
		}
	}else { //ou 3 dias de aula
		//se os 3 dias forem no mesmo mês
		if( ($novo_mes == $novo_mes1) && ($novo_mes == $novo_mes2) ) {
			$periodo = 'Dias '.$novo_dia.', '.$novo_dia1.' e '.$novo_dia2.' de '.$novo_mes.'';
			echo $periodo."<br>";
		}else if ( ($novo_mes == $novo_mes1) && ($novo_mes != $novo_mes2) ) {
			$periodo = 'Dias '.$novo_dia.' e '.$novo_dia1.' de '.$novo_mes.' e '.$novo_dia2.' de '.$novo_mes2.'';
			echo $periodo."<br>";
		}else if ($novo_mes != $novo_mes1) {
			$periodo = 'Dias '.$novo_dia.' de '.$novo_mes.', '.$novo_dia1.' e '.$novo_dia2.' de '.$novo_mes2.'';
			echo $periodo."<br>";
		}
	}
	
	
	
	//Muda status da turma para encerrada
	$update = "update cw_cursos_turmas set status=1 where id=".$turma;
	$qry = mysql_query($update, $conn);
	
	
	//Cria outra turma
	$sql2 = "insert into cw_cursos_turmas 
	(id_curso, data_publicacao, data_inicio, periodo, status, vezes_postergado, turno, horarios) 
	values ('".$id_curso."', '".$hoje."', '".$nova_data_inicio."', '".$periodo."', '0', '0', '".$turno."', '".$horario."')";
	$query2 = mysql_query($sql2, $conn);
		
		
	//Seleciona nova turma
	$sql3 = "select id as id_turma 
	from cw_cursos_turmas 
	where id_curso='".$id_curso."'
	and data_publicacao='".$hoje."'
	and data_inicio='".$nova_data_inicio."'
	and periodo='".$periodo."'
	and turno='".$turno."'
	and horarios='".$horario."'";
	$query3= mysql_query($sql3, $conn);
		
	$res3 = mysql_fetch_assoc($query3);
	$id_nova_turma = $res3['id_turma'];
	
	//Conta quantos inscritos ultrapassaram o limite da turma
	$excesso = $total_inscritos - $max_inscritos; 
	
	//Muda id da turma dos inscritos que excederam o limite da turma
	$sql4 = "select id, email, celular from cw_cursos_inscritos
	where id_turma=".$turma."
	order by dh_inscrito desc
	limit ".$excesso;
	$query4 = mysql_query($sql4, $conn);
	
	while ($res4 = mysql_fetch_assoc($query4)) {
		$id4 = $res4['id'];
		$destinatario = $res4['email'];
		
		$up = "update cw_cursos_inscritos 
		set id_turma=".$id_nova_turma."
		where id=".$id4;
		$qryup = mysql_query($up);
		
		//Envia e-mail avisando
		$headers = 'MIME-Version: 1.0'."\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
		$headers .= 'From: CPCM Rede Industrial <cpcm@redeindustrial.com.br>'."\r\n";
		$headers .= 'CC: CPCM Rede Industrial <cpcm@redeindustrial.com.br>'."\r\n";
				
		$assunto = 'Seu curso mudou de data.';
		$mensagem = '
				<!doctype html>
				<html>
				<head>
					<meta charset="utf-8">
				</head>
				<body style="width: 550px;">
				<p style="font-family:Arial, Tahoma, sans-serif; font-size:16px;">
				Prezado Aluno,<br>
				Devido ao grande número de inscrições e para o melhor aproveitamento de sua aula, criamos uma nova turma para seu curso de '.$curso.'.<br>
				Sua nova turma terá início em '.$novo_dia.' de '.$novo_mes.' às '.$horario.'. Link direto da Sala de aula:<br>
				http://centralsigma.com.br/cpcm/cursos/saladeaula/'.$id_nova_turma.' <br>
				<br>
				Ao menos 1 dia antes de sua aula, acesse o site CPCM e confira suas atividades que devem ser feitas antes da primeira aula:<br>
				http://www.centralsigma.com.br/cpcm <br>
				<br>
				Para sanar suas dúvidas em relação ao acesso, leia o Manual do Aluno, disponível em pdf no canto superior do site, ou nesse link: <br>
				http://centralsigma.com.br/cpcm/resources/pdf/PortalCPCM-ManualdoAluno.pdf <br>
				<br>
				Em caso de Dúvida, responda este e-mail!<br>
				<br>
				Equipe CPCM Rede Industrial<br>
				www.centralsigma.com.br
				</p>
				</body>
				</html>';
				
				
			//Divide o destinatario em partes quando houver espaços ou sinais
			//(mais de um email foi cadastrado)
			$parte = preg_split("/[\s,]+/", $destinatario);
				
			//se não houver mais de uma parte
			if (!isset ($parte[1])) {
				//Envia e-mail
				$email_enviado = mail($destinatario, $assunto, $mensagem, $headers);
			}else { 	//se houver mais de uma parte
				foreach ($parte as $destinatario) {
					//Envia e-mail
					$email_enviado = mail($destinatario, $assunto, $mensagem, $headers);
				}
			}
				
			//Envia SMS avisando
			$celular = $res4['celular'];
			$celular = decode_telefone($celular);
			$mensagem_cel = "Sua turma de ".$curso." foi alterada e inicia em ".$novo_dia." de ".$novo_mes.". Confira seu e-mail para mais informações. CPCM";
				
			$insert = "INSERT INTO `SMS` (`CELULAR_REMETENTE`, `CELULAR_DESTINO`, `MENSAGEM`, `STATUS`, `USUARIO`)
			VALUES ('9999999999', '$celular', '$mensagem_cel', '1', '151')";
			$q_insert = mysql_query($insert, $conn);
					
	}
	

}
?>