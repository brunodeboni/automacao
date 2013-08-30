<?php
header('Content-Type: text/html; charset=utf-8');

//verifica se site existe / está no ar

$sites["centralsigma"] = "http://www.centralsigma.com.br";
$sites["cpcm"] = "http://www.centralsigma.com.br/cpcm";
$sites["mobileprovider"] = "http://www.mobileprovider.com.br";
$sites["sigmaandroid"] = "http://www.sigmaandroid.com.br";
$sites["hdutil"] = "http://www.hdutil.com.br";
$sites["tecchat"] = "http://www.tecchat.com.br";
$sites["loja-centralsigma"] = "http://loja.centralsigma.com.br";
$sites["paghoje"] = "http://www.paghoje.com.br";
$sites["fllecha"] = "http://www.fllecha.com.br";
$sites["teste-de-conhecimento"] = "http://www.testedeconhecimento.com.br";
$sites["rede-industrial"] = "http://www.redeindustrial.com.br";
$sites["paghoje"] = "http://www.paghoje.com.br";
$sites["aulasweb"] = "http://www.aulasweb.com.br";
$sites["kolonistenhaus"] = "http://www.kolonistenhaus.com.br";
$sites["horimetro"] = "http://www.aulasweb.com.br";
//$sites["dheinhaus"] = "http://www.dheinhaus.com.br";
$sites["estudostecnicos"] = "http://www.estudostecnicos.com.br";
$sites["sigmatube"] = "http://www.sigmatube.com.br";
$sites["sigmatv"] = "http://www.sigmatv.com.br";
$sites["tvsigma"] = "http://www.tvsigma.com.br";
//$sites["cfc-ivoti"] = "http://www.cfcivoti.com.br";
$sites["produtosweb"] = "http://produtosweb.com.br/";
//$sites["showroomdeempresas"] = "http://www.showroomdeempresas.com.br/";
$sites["classemundial"] = "http://www.classemundial.com.br/";
$sites["universosigma"] = "http://www.universosigma.com.br/";
//$sites["canoeletrico"] = "http://canoeletrico.com.br/";

$msg = "";
$msg_cel = "Site(s) fora do ar. Verifique seu e-mail para mais detalhes.";
foreach ($sites as $nome => $url) {
    $resposta = visit($url);

    if ($resposta == 'OK') {
		//Nada acontece.
    }else {
		$msg .= "Site $nome fora do ar. Erro $resposta<br>";
		//$msg_cel .= "$nome ";
    }
	//$msg_cel .= "fora do ar.";

}
if ($msg !== "") {
	
	//Envia e-mail para Henrique
	$headers = 'MIME-Version: 1.0'."\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
	$headers .= 'From: <comercial@redeindustrial.com.br>'."\r\n";
	$headers .= 'CC: <brunodeboni@gmail.com.br>'."\r\n";

	$assunto = "Status dos sites";
	$mensagem = '<!doctype html>
	<html>
	<head>
		<meta charset="utf-8">
	</head>
	<body>
	A verificação automática de sites gerou um aviso:<br><br>
	'.$msg.'
	<body>
	</html>';

	mail('henriquesschmitt@gmail.com', $assunto, $mensagem, $headers);

	//Envia SMS para Henrique
	
	
	try {
		$conn = new PDO('mysql:host=mysql.centralsigma.com.br;dbname=centralsigma02', 
				'webadmin', 'webADMIN', 
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	catch(Exception $e) {
		echo 'Exception -> ';
		var_dump($e->getMessage());
	}
	
	$sql = "insert into sms 
		(CELULAR_REMETENTE, CELULAR_DESTINO, MENSAGEM, STATUS, USUARIO)
		values ('9999999999', '5491315407', :mensagem, '1', '151')"; //Para Henrique
	$query = $conn->prepare($sql);
	$query->execute(array(':mensagem' => $msg_cel));
	print_r($conn->errorInfo());
	
	$sql1 = "insert into sms 
		(CELULAR_REMETENTE, CELULAR_DESTINO, MENSAGEM, STATUS, USUARIO)
		values ('9999999999', '5492163539', :mensagem, '1', '151')"; //Para Bruno
	$query1 = $conn->prepare($sql1);
	$query1->execute(array(':mensagem' => $msg_cel));
	print_r($conn->errorInfo());
}


//Lista de status HTTP:
//https://support.google.com/webmasters/answer/40132?hl=pt-br

function visit($url) {
	
    //verifica se a URL informada é válida
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
    	return 'URL inválida!';
    }
	
    //Verifica se há erros
    $agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $page = curl_exec($ch);

    //echo curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    switch($httpcode) {
		//case 0: return '0: Site não existe.'; break;
		case 304: return '304 Não modificado: A página solicitada não foi modificada desde a última solicitação.'; break;
		case 400: return '400 Solicitação inválida: O servidor não entendeu a sintaxe da solicitação.'; break;
		case 401: return '401 Não Autorizado: Falha na autenticação. Uma autenticação é requerida e não foi provida.'; break;
		case 403: return '403 Proibido: O servidor está recusando a solicitação.'; break;
		case 404: return '404 Não encontrado: O servidor não encontrou a página solicitada.'; break;
		case 405: return '405 Método não permitido: O método especificado na solicitação não é permitido.'; break;
		case 408: return '408 Tempo limite da solicitação: O servidor atingiu o tempo limite ao aguardar a solicitação.'; break;
		case 410: return '410 Gone: O recurso solicitado não está mais disponível.'; break;
		case 420: return '420 Falha de método.'; break;
		case 424: return '424 Falha de método: Falha na execução do método em algum recurso.'; break;
		case 451: return '451 Indisponível por motivos legais: Acesso negado por motivos legais, como censura ou bloqueio governamental.'; break;
		case 500: return '500 Erro interno do servidor: O servidor encontrou um erro e não pode completar a solicitação.'; break;
		case 502: return '502 Gateway inválido: O servidor estava operando como gateway ou proxy e recebeu uma resposta inválida do servidor superior.'; break;
		case 503: return '503 Serviço indisponível: O servidor está indisponível no momento (por sobrecarga ou inatividade para manutenção). Geralmente, esse status é temporário.'; break;
		case 504: return '504 Tempo limite do gateway: O servidor estava operando como gateway ou proxy e não recebeu uma solicitação do servidor superior a tempo.'; break;
		case 507: return '507 Armazenamento insuficiente: O servidor não possui espaço suficiente para completar a solicitação.'; break;
		case 508: return '507 Loop detectado: O servidor detectou um loop infinito enquanto processava a solicitação.'; break;
		case 509: return '509 Limite de largura de banda excedido.'; break;
		default: return 'OK'; break;
    }
	
	/*
		if($httpcode >= 200 && $httpcode < 300) return true;
		else return false;
	*/
	//return $httpcode;
}

/*
function isSiteAvailable($url) {

	//verifica se a URL informada é válida
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
    	return 'URL invalida!';
    }
 
    //Conexão com CURL
    $cl = curl_init($url);
    curl_setopt($cl,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($cl,CURLOPT_HEADER,true);
    curl_setopt($cl,CURLOPT_NOBODY,true);
    curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
 
    //Pega resposta
    $response = curl_exec($cl);
    curl_close($cl);
 
    if ($response) return 'Site no ar!';
	
    return 'Site nao existe ou esta fora do ar.';
}
*/

?>