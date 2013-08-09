<?php
if (isset ($_GET['user']) && ($_GET['curso'])) {
	$id_user = $_GET['user'];
	$id_curso = $_GET['curso'];
}
else echo 'Usu&aacute;rio ou curso n&atilde;o encontrados.';

// Conexão com mysql
$conn = mysql_connect("mysql.centralsigma.com.br","centralsigma02","S4k813042012");
mysql_select_db("centralsigma02",$conn);

//Para enviar as informações ao banco de dados em UTF-8
mysql_set_charset("utf8");

$sql = "insert into cw_notificacao_alunos_desativados (id_user, id_curso, tipo, datahora) values ('$id_user', '$id_curso', 'email', now())"
		or die('N&atilde;o foi poss&iacute;vel executar seu pedido. Por favor, tente novamente em alguns instantes.');
$query = mysql_query($sql, $conn);


if ($query) echo 'Notifica&ccedil;&otilde;es canceladas com sucesso!';
?>

