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

//Para enviar as informações ao banco de dados em UTF-8
mysql_set_charset("utf8");

//Seleciona os inscritos das turmas que iniciam em 7 dias, 4 dias e 1 dia
$sql = "select usuarios.id as id_user, usuarios.email, usuarios.celular, turmas.id as id_turma, 
day(turmas.data_inicio) as dia, month(turmas.data_inicio) as mes, turmas.horarios,
cursos.nome as curso, cursos.id as id_curso,
datediff(turmas.data_inicio, current_date) as faltam		
from cwk_users as usuarios

left join cw_cursos_inscritos as inscritos
on inscritos.id_user=usuarios.id

left join cw_cursos_turmas as turmas
on inscritos.id_turma=turmas.id	

left join cw_cursos as cursos
on turmas.id_curso=cursos.id
	
where turmas.data_inicio = date_add(current_date, interval 7 day)
or turmas.data_inicio = date_add(current_date, interval 4 day)
or turmas.data_inicio = date_add(current_date, interval 1 day)";
$query = mysql_query($sql, $conn);


$a=0;//inicia contador de envios
while ($res = mysql_fetch_assoc($query)) {
	// verifica se já enviou 10 emails e dá uma espera, para não configurar spam
	if($a==10){
		sleep(3);
		$a=0;
	}
	
	$id_user = $res['id_user'];
	$id_curso = $res['id_curso'];
	
	//Procura o usuário e curso na tabela de unsubscribe
	$sql1 = "select id_user from cw_notificacao_alunos_desativados where id_user=".$id_user." and id_curso=".$id_curso;
	$query1 = mysql_query($sql1, $conn);
	
	//se está lá, finaliza e volta ao loop
	if (mysql_num_rows($query1) > 0) continue;
	
	
	$destinatario = $res['email'];
	$turma = $res['id_turma'];
	$dia = $res['dia'];
	$mes = $res['mes'];
	$horario = $res['horarios'];
	$curso = $res['curso'];
	$faltam = $res['faltam'];
	
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
	$headers .= 'CC: CPCM Rede Industrial <cpcm@redeindustrial.com.br>'."\r\n";
	
	$assunto = 'Curso '.utf8_encode($curso).' - Início das aulas em '.$faltam.' dias';
	$mensagem = '
		<!doctype html>
		<html>
		<head>
			<meta charset="utf-8">
		</head>
		<body style="width: 550px;">
		<p style="font-family:Arial, Tahoma, sans-serif; font-size:16px;">
		Prezado Aluno,<br>
		Sua primeira Aula do curso '.$curso.' irá começar em '.$dia.' de '.$mes.' às '.$horario.'. Link direto da Sala de aula:<br> 
		http://centralsigma.com.br/cpcm/cursos/saladeaula/'.$turma.' <br>
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
		<br>
		<p style="font-family:Arial, Tahoma, sans-serif; font-size:16px;">
		Se deseja não continuar sendo lembrado sobre esta aula, clique no link abaixo:</br>
		<a href="http://centralsigma.com.br/arquivos/notificacoes_cpcm/unsubscribe_7dias.php?user='.$id_user.'&curso='.$id_curso.'">Não ser notificado</a>	
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

	
	//Envia SMS
	$celular = $res['celular'];
	$celular = decode_telefone($celular);
	$mensagem_cel = "Seu curso de ".$curso." inicia em ".$dia." de ".$mes.". Confira seu e-mail. CPCM";
	
	$sql2 = "INSERT INTO `SMS` (`CELULAR_REMETENTE`, `CELULAR_DESTINO`, `MENSAGEM`, `STATUS`, `USUARIO`)
	VALUES ('9999999999', '$celular', '$mensagem_cel', '1', '151')";
	$query2 = mysql_query($sql2, $conn);
	
	
	//dá uma espera de 20s a cada envio para não configurar spam
	sleep(20);
}
?>