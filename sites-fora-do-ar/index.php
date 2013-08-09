<?php
header('Content-Type: text/html; charset=utf-8');

//verifica se site existe / está no ar

$sites["centralsigma"] = "http://www.centralsigma.com.br";
$sites["cpcm"] = "http://www.centralsigma.com.br/cpcm";
$sites["hdutil"] = "http://www.hdutil.com.br";
$sites["tecchat"] = "http://www.tecchat.com.br";
$sites["loja-centralsigma"] = "http://loja.centralsigma.com.br";
$sites["fllecha"] = "http://www.fllecha.com.br";
$sites["teste-de-conhecimento"] = "http://www.testedeconhecimento.com.br";
$sites["rede-industrial"] = "http://www.redeindustrial.com.br";
$sites["paghoje"] = "http://www.paghoje.com.br";
$sites["aulasweb"] = "http://www.aulasweb.com.br";

$msg = "";
foreach ($sites as $nome => $url) {
	$resposta = visit($url);
		
	if ($resposta == 'OK') {
		$msg .= 'Site '.$nome.' OK.<br>';
	}else {
		$msg .= 'Site '.$nome.' fora do ar. Erro '.$resposta.'<br>';
	}
}

//Envia e-mail para Danna
$headers = 'MIME-Version: 1.0'."\r\n";
$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
$headers .= 'From: <comercial@redeindustrial.com.br>'."\r\n";
//$headers .= 'CC: <cpcm@redeindustrial.com.br>'."\r\n";

$assunto = "Status dos sites";
$mensagem = '<!doctype html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
Verificação automática dos status dos seguintes sites:<br><br>
'.$msg.'
<body>
</html>';

$email_enviado = mail('monitoramentosites@live.com', $assunto, $mensagem, $headers);

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
		case 0: return '0: Site não existe.'; break;
		case 304: return '304 Não modificado: A página solicitada não foi modificada desde a última solicitação.'; break;
		case 400: return '400 Solicitação inválida: O servidor não entendeu a sintaxe da solicitação.'; break;
        case 403: return '403 Proibido: O servidor está recusando a solicitação.'; break;
        case 404: return '404 Não encontrado: O servidor não encontrou a página solicitada.'; break;
        case 405: return '405 Método não permitido: O método especificado na solicitação não é permitido.'; break;
        case 408: return '408 Tempo limite da solicitação: O servidor atingiu o tempo limite ao aguardar a solicitação.'; break;
        case 500: return '500 Erro interno do servidor: O servidor encontrou um erro e não pode completar a solicitação.'; break;
        case 502: return '502 Gateway inválido: O servidor estava operando como gateway ou proxy e recebeu uma resposta inválida do servidor superior.'; break;
        case 503: return '503 Serviço indisponível: O servidor está indisponível no momento (por sobrecarga ou inatividade para manutenção). Geralmente, esse status é temporário.'; break;
        case 504: return '504 Tempo limite do gateway: O servidor estava operando como gateway ou proxy e não recebeu uma solicitação do servidor superior a tempo.'; break;
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