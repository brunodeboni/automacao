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
$conn = mysql_connect("mysql.centralsigma.com.br","centralsigma02","S4k813042012");
mysql_select_db("centralsigma02",$conn);
mysql_set_charset("utf8");

//Verifica cada Turma com inscrições Abertas, com menos de 5 inscritos e que iniciam em menos de 6 dias e não foram postergadas mais que 2 vezes
$sql = "select turmas.id as id_turma,
turmas.data_inicio,
cursos.carga_horaria,
cursos.id as id_curso,
(select count(*) from cw_cursos_inscritos as i where i.id_turma=turmas.id) as total,
		
day(date_add(turmas.data_inicio, interval 7 day)) as novo_dia,		
month(date_add(turmas.data_inicio, interval 7 day)) as novo_mes,
		
day(date_add(turmas.data_inicio, interval 8 day)) as novo_dia1,
month(date_add(turmas.data_inicio, interval 8 day)) as novo_mes1,
		
day(date_add(turmas.data_inicio, interval 9 day)) as novo_dia2,
month(date_add(turmas.data_inicio, interval 9 day)) as novo_mes2

from cw_cursos_turmas as turmas
		
left join cw_cursos as cursos
on turmas.id_curso=cursos.id
		
where turmas.data_inicio > current_date 
and turmas.data_inicio < date_add(current_date, interval 6 day)
and turmas.status=0
and turmas.vezes_postergado < 2
having total < 5";
$query = mysql_query($sql, $conn);

while ($res = mysql_fetch_array($query)) {
	$id_turma = $res['id_turma'];
	$data_inicio = $res['data_inicio'];
	$id_curso = $res['id_curso'];
	
	//Cria novo período
	$carga_horaria = $res['carga_horaria'];
	$novo_dia = $res['novo_dia'];
	$novo_dia1 = $res['novo_dia1'];
	$novo_dia2 = $res['novo_dia2'];
	$novo_mes = $res['novo_mes'];
	$novo_mes1 = $res['novo_mes1'];
	$novo_mes2 = $res['novo_mes2'];
	
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
	if ($carga_horaria < 9) {
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
	
	//Altera período da turma para data atual + 7d
	$sql2 = "update cw_cursos_turmas 
	set data_inicio = date_add(data_inicio, interval 7 day),
	periodo='".$periodo."',
	vezes_postergado = vezes_postergado + 1
	where id=".$id_turma;
	$query2 = mysql_query($sql2, $conn); 
	
	//Envia SMS e E-mail para inscritos da turma avisando sobre a postergação.
	//Pesquisa os dados
	$sql3 = "select usuarios.email, usuarios.celular, turmas.id as id,
		day(turmas.data_inicio) as dia, month(turmas.data_inicio) as mes, 
		turmas.horarios, cursos.nome as curso

		from cwk_users as usuarios
		
		left join cw_cursos_inscritos as inscritos
		on inscritos.id_user=usuarios.id
		
		left join cw_cursos_turmas as turmas
		on inscritos.id_turma=turmas.id

		left join cw_cursos as cursos
		on turmas.id_curso=cursos.id
		
		where turmas.id=".$id_turma;
	$query3 = mysql_query($sql3, $conn);
		
		
	$a=0;
	while ($res3 = mysql_fetch_assoc($query3)) {
		// verifica se já enviou 10 emails e dá uma espera, para não configurar spam
		if($a==10){
			sleep(3);
			$a=0;
		}
		
		$destinatario = $res3['email'];
		$turma = $res3['id'];
		$dia = $res3['dia'];
		$mes = $res3['mes'];
		$horario = $res3['horarios'];
		
		switch ($mes) {
			case "1": $mes = "janeiro"; break;
			case "2": $mes = "fevereiro"; break;
			case "3": $mes = "março"; break;
			case "4": $mes = "abril"; break;
			case "5": $mes = "maio"; break;
			case "6": $mes = "junho"; break;
			case "7": $mes = "julho"; break;
			case "8": $mes = "agosto"; break;
			case "9": $mes = "setembro"; break;
			case "10": $mes = "outubro"; break;
			case "11": $mes = "novembro"; break;
			case "12": $mes = "dezembro"; break;
		}
		
		$headers = 'MIME-Version: 1.0'."\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
		$headers .= 'From: CPCM Rede Industrial <cpcm@redeindustrial.com.br>'."\r\n";
		
		$assunto = 'Turma prorrogada';
		$mensagem = '
			<!doctype html>
			<html>
			<head>
				<meta charset="utf-8">
			</head>
			<body style="width: 550px;">
				<p style="font-family:Arial, Tahoma, sans-serif; font-size:16px;">
				Prezado Aluno,<br>
				Informamos que sua aula de '.$curso.' foi prorrogada e sua primeira Aula irá começar em '.$dia.' de '.$mes.' às '.$horario.'. Link direto da Sala de aula:<br>
				http://centralsigma.com.br/cpcm/cursos/saladeaula/'.$turma.' <br>
				<br>
				Ao menos 1 dia antes de sua aula, acesse o site CPCM e confira suas atividades que devem ser feitas antes da primeira aula: <br>
				http://www.centralsigma.com.br/cpcm <br>
				<br>
				Para sanar suas dúvidas em relação ao acesso, leia o Manual do Aluno, disponível em pdf no canto superior do site, ou neste link: <br>
				http://centralsigma.com.br/cpcm/resources/pdf/PortalCPCM-ManualdoAluno.pdf <br>
				<br>
				Em caso de Dúvida, responda este e-mail!<br>
				<br>
				Equipe CPCM Rede Industrial<br>
				www.centralsigma.com.br
				</p>
			</body>
			</html>';
			
		
		//Divide o destinatario em partes quando houver espa�os ou sinais
		//(mais de um email foi cadastrado)
		$parte = preg_split("/[\s,]+/", $destinatario);

		//se não houver mais de uma parte
		if (!isset ($parte[1])) {
			//Envia e-mail
			$email_enviado = mail($destinatario, $assunto, $mensagem, $headers);
		
			if ($email_enviado) {
				$a++;//soma contador de envios
			}
		
		}else { 	//se houver mais de uma parte
			foreach ($parte as $destinatario) {
				//Envia e-mail
				$email_enviado = mail($destinatario, $assunto, $mensagem, $headers);
		
				if ($email_enviado) {
					$a++;//soma contador de envios
				}
			}
		}
		//dá uma espera de 20s a cada envio para não configurar spam
		sleep(20);
		
		
		//Envia SMS
		$celular = $res3['celular'];
		$celular = decode_telefone($celular);
		$curso = $res3['curso'];
		$mensagem_cel = "Seu curso de ".$curso." foi prorrogado e inicia em ".$dia." de ".$mes.". Confira seu e-mail. CPCM";
			
			
		$sql4 = "INSERT INTO `SMS` (`CELULAR_REMETENTE`, `CELULAR_DESTINO`, `MENSAGEM`, `STATUS`, `USUARIO`)
			 VALUES ('9999999999', '$celular', '$mensagem_cel', '1', '151')";
		$query4 = mysql_query($sql4, $conn);
			

	}
}

?>