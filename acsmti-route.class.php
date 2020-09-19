<?php

if( !class_exists( 'ascmtiRoute' ) ){

    class acsmtiRoute{

        function gerarRegex( $rota ){
            $rota             = str_replace( ["{{","}}"], ["©", "®"], $rota );

            $regex_parametros = "/©(?'chamada'((((((?'parametro'([a-z0-9\_]+))\:)?(?'valor'([^©®]+))))|(?R))*))®/";
            $regex_final      = '';

            $regex_final      = preg_replace_callback( $regex_parametros, array( $this, 'trataParametros' ), $rota );

            while( preg_match( "/\[\/(.*)\/\]/", $regex_final, $match ) ){
                $novo        = preg_replace( ["/^\[\//","/\/\]$/"], ["(\/",")?"], $match[0] );
                $regex_final = str_replace( $match[0], $novo, $regex_final );
            }

            $regex_final =  str_replace( "__ENCLOUSURADO__", "[^\/]", $regex_final );
            $regex_final = preg_replace( "/^\//"           , "\/"   , $regex_final );
            $regex_final = preg_replace( "/([^\\\])\//"    , "$1\/" , $regex_final );
            $regex_final = '/^' . $regex_final . '(\/)?$/';

            return $regex_final;
        }

        function pegarParametrosDaRota( $rota, $padrao ){
            preg_match( $padrao, $rota, $resultado );

            foreach( $resultado as $k => $v )
                if( is_numeric( $k ) )
                    unset( $resultado[ $k ] );

            return (object)$resultado;
        }

        private function trataParametros( $match ){
            $novo = $match[0];
            $novo = str_replace( ["©","®"], ["(",")"], $novo );

            if( isset( $match['parametro'] ) && !empty( $match['parametro'] ) ){
                $novo = str_replace( $match['chamada'], "(?'" . $match['parametro'] . "'(" . $match['valor'] . "))", $novo );
            } else {
                $novo = str_replace( $match['chamada'], "(?'" . $match['valor'] . "'__ENCLOUSURADO__+)", $novo );
            }

            return $novo;
        }
    }

}

/*
$teste = new acsmtiRoute();
$regex = $teste->gerarRegex( '/pasta1/{{id:([0-9]{2,4})}}[/{{titulo}}[/{{quantidade}}/]/]' );

echo "<pre>";
var_dump( $teste->pegarParametrosDaRota( '/pasta1/123/teste/456', $regex ) );
echo "</pre>";
exit;
*/