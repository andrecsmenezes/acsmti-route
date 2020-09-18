# acsmti-route

para usar:

$padrao = '/path1/{id:\d+}[/{title}[/{length}]]';
$rota   = gerarRegex( $padrao );

//vai gerar: ^\/path1\/((?'id'(\d+)))(\/((?'title'[^\/]+))(\/((?'length'[^\/]+)))?)?(\/)?$

depois:

$url        = '/path1/123/teste/456/';
$parametros = pegarParametrosDaRota( $url, $rota );

//vai gerar:
//$parametros->id     -> 123
//$parametros->title  -> teste
//$parametros->length -> 456