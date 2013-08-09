<?php

/**
 * 1 - Pegar lista de aulas acontecendo agora
 * 2 - Pegar lista de aulas acontecendo agora que já foram avisadas
 * 3 - Deixar apenas as aulas que ainda vão acontecer e não foram avisadas
 *     4 - Pegar lista de alunos desses cursos
 *     5 - Pegar lista de alunos que NÃO querem receber notificações
 *     6 - Deixar apenas os alunos que ainda querem receber notificações
 *         7 - Enviar SMS para estes alunos
 * 8 - Gravar no banco de dados
 */

/**
 * - tabela de aulas avisadas hoje
 * - tabela de alunos que não querem receber notificações
 */


// Conexão com mysql
//$conn = mysql_connect("mysql.centralsigma.com.br","centralsigma02","S4k813042012");
$conn = mysql_connect("186.202.121.119","webadmin","webADMIN") or die('Falhou a conexão com o mysql');
mysql_select_db("cpcm_teste",$conn);
mysql_set_charset("utf8");



// 1 - Pegar lista de aulas acontecendo agora
$sql_recursos_agora = "select
							rec.id as id_recurso,
							rec.id_curso,
							rec.id_tipo,
							rec.turma_acontecendo_agora,
							rec.titulo,
							rec.texto_descricao,
							rec.link,
							cur.nome,
							cur.carga_horaria,
							tur.horarios
						from cw_cursos_recursos as rec
						join cw_cursos as cur on rec.id_curso = cur.id
						join cw_cursos_turmas as tur on tur.id = rec.turma_acontecendo_agora
						where rec.turma_acontecendo_agora is not null";
$qry_recursos_agora = mysql_query($sql_recursos_agora,$conn);

$recursos_agora = array();

while($res_recursos_agora = mysql_fetch_assoc($qry_recursos_agora)){
	$recursos_agora[] = $res_recursos_agora;
}





// 2 - Pegar lista de aulas acontecendo agora que já foram avisadas
$sql_recursos_avisados = "select
								*
							from cw_notificacao
							where date(datahora) = curdate()";
$qry_recursos_avisados = mysql_query($sql_recursos_avisados,$conn) or die(mysql_error());

$recursos_avisados = array();

while($res_recursos_avisados = mysql_fetch_assoc($qry_recursos_avisados)){
	$recursos_avisados[] = $res_recursos_avisados;
}

//var_dump($recursos_avisados);die();


// 3 - Deixar apenas as aulas que ainda vão acontecer e não foram avisadas
function checar_avisado($avisado, $todos){
	$idx_avisado = false;
	
	foreach($todos as $krec => $recurso){
		if($avisado['id_curso'] == $recurso['id_curso']
		&&$avisado['id_turma'] == $recurso['turma_acontecendo_agora']){
			$idx_avisado = $krec;
			break;
		}
	}
	
	return $idx_avisado;
}

foreach($recursos_avisados as &$avisado){
	$avisado_idx = checar_avisado($avisado, $recursos_agora);
	
	if($avisado_idx!==false){
		unset($recursos_agora[$avisado_idx]);
	}
}




// 4 - Pegar lista de alunos desses cursos
function get_lista_turmas($recursos){
	$turmas = array();
	foreach($recursos as $recurso){
		$turmas[] = (int)$recurso['turma_acontecendo_agora'];
	}
	
	return $turmas;
}

$turmas = get_lista_turmas($recursos_agora);
$turmas_where_sql = '';
foreach($turmas as $turma){
	$turmas_where_sql = "i.id_turma = $turma and ";
}
if(strlen($turmas_where_sql)){
	$turmas_where_sql = substr($turmas_where_sql,0,-4);
	$sql_alunos = "select
						i.id as id_inscrito,
						i.id_turma,
						i.id_user,
						us.celular,
						us.email
					from cw_cursos_inscritos as i
					join cwk_users as us on i.id_user = us.id
					
					where $turmas_where_sql";
	$qry_alunos = mysql_query($sql_alunos,$conn) or die(mysql_error() . ' ----- '.$sql_alunos);


	while($res_alunos = mysql_fetch_assoc($qry_alunos)){
		foreach($recursos_agora as &$recurso){
			if($recurso['turma_acontecendo_agora'] == $res_alunos['id_turma']){
				if(isset($recurso['alunos'])){
					$recurso['alunos'][] = $res_alunos;
				}else{
					$recurso['alunos'] = array($res_alunos);
				}
				break;
			}
		}
	}
}


// 5 - Pegar lista de alunos que NÃO querem receber notificações
foreach($recursos_agora as &$recurso){
	if(!isset($recurso['alunos'])) continue;
	
	$sql_desistentes = "select
							id_user
						from cw_notificacao_alunos_desativados
						where id_curso = $recurso[id_curso]";
	$qry_desistentes = mysql_query($sql_desistentes,$conn);
	
	$desistentes = array();
	while($res_desistentes = mysql_fetch_assoc($qry_desistentes)){
		$desistentes[] = $res_desistentes['id_user'];
	}
	
	// 6 - Deixar apenas os alunos que ainda querem receber notificações
	foreach($recurso['alunos'] as $rekey => $aluno){
		$dkey = array_search($aluno['id_user'],$desistentes);
		if($dkey!==false){
			unset($recurso['alunos'][$rekey]);
			unset($desistentes[$dkey]);
		}
	}
}


// Imprimir a lista completa de recursos e alunos
//echo'<pre>';print_r($recursos_agora);die();


// 7 - Enviar SMS para estes alunos
$sql_notificados = '';
$mensagem_cel    = '';
foreach($recursos_agora as &$recurso){
	$mensagem_cel = mysql_real_escape_string('A '.$recurso['titulo'].' do curso de '.$recurso['nome'].' está em vigor, entre no site do CPCM para conferir.');
	$sql_sms = "INSERT INTO `SMS` (`CELULAR_REMETENTE`, `CELULAR_DESTINO`, `MENSAGEM`, `STATUS`, `USUARIO`) VALUES ";
	
	$count_alunos = 0;
	
	if(isset($recurso['alunos'])){
		foreach($recurso['alunos'] as &$aluno){
			$count_alunos++;
			$celular = $aluno['celular'];
			$sql_sms .= "('9999999999', '$celular', '$mensagem_cel', '1', '151'),";
		}
	}
	
	$sql_sms = substr($sql_sms,0,-1);
	
	// DESATIVADA POR MOTIVO DE TESTES
	//mysql_query($sql_sms,$conn);
	
	$sql_notificados .= "($recurso[id_curso], $recurso[turma_acontecendo_agora], '$mensagem_cel', $count_alunos),";
}

// 8 - Gravar no banco de dados
if(strlen($sql_notificados)){
	$sql_notificados = 'insert into cw_notificacao (id_curso, id_turma, mensagem, contador) values '.substr($sql_notificados,0,-1);
	mysql_query($sql_notificados,$conn);
}



die('*** FIM DO SCRIPT ***');

/**
 * Script antigo criado pelo Bruno
 **

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



//Verifica turmas com aula agora e seleciona os usuários
$consulta = "select usuarios.id as usu, cursos.nome as cur

from cwk_users as usuarios

left join cw_cursos_inscritos as inscritos
on inscritos.id_user=usuarios.id

left join cw_cursos_turmas as turmas
on inscritos.id_turma=turmas.id

left join cw_cursos as cursos
on cursos.id=turmas.id_curso

left join cw_cursos_recursos as recursos
on recursos.id_curso=cursos.id

where turmas.id=recursos.turma_acontecendo_agora
and recursos.turma_acontecendo_agora is not null";
$qry = mysql_query($consulta, $conn);

while ($result = mysql_fetch_assoc($qry)) {
	$id_user = $result['usu'];
	$curso = $result['cur'];


	//Verifica alunos que acessaram no dia da aula
	$sql = "select usuarios.celular

	from cwk_users as usuarios
		
	left join cw_users_online as online
	on usuarios.id=online.id_user
		
	where date(online.dh)=current_date and usuarios.id=".$id_user;
	$query = mysql_query($sql, $conn);
	
	//se não acessaram, envia a mensagem
	if(mysql_num_rows($query)==0) {
			
		while ($res = mysql_fetch_assoc($query)) {
			
			//Envia SMS
			$celular = $res['celular'];
			$celular = decode_telefone($celular);
			$mensagem_cel = "Sua aula de ".$curso." já começou. Confira seu e-mail com o link para a sala de aula. CPCM";
			
			$sql2 = "INSERT INTO `SMS` (`CELULAR_REMETENTE`, `CELULAR_DESTINO`, `MENSAGEM`, `STATUS`, `USUARIO`)
			VALUES ('9999999999', '$celular', '$mensagem_cel', '1', '151')";
			
			// QUERY DESABILITADA PARA EVITAR ENVIO DE SMS NOS TESTES
			//$query2 = mysql_query($sql2, $conn);
		}
	}
}
?>

*/
