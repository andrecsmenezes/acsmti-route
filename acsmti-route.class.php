<?php

if( !class_exists( 'ascmtiRoute' ) ){

    class acsmtiRoute{

        function gerarRegex( $rota ){
            $rota             = str_replace( ["{{","}}"], ["©", "®"], $rota );

            $regex_parametros = "/©(?'chamada'((((((?'parametro'([a-z0-9\_,]+))\:)?(?'valor'([^©®]+))))|(?R))*))®/";
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

            foreach( $resultado as $k => $v ){
                if( is_numeric( $k ) ){
                    unset( $resultado[ $k ] );
                }
                else {
                    if( preg_match( "/___/", $k ) ){
                        $parametro = explode( "___", $k );
                        unset( $resultado[ $k ] );

                        if( $parametro[1] == 'int' )
                            $resultado[ $parametro[0] ] = intval( $v );
                        else if(
                               $parametro[1] == 'array'
                            || $parametro[1] == 'arrayint'
                            || $parametro[1] == 'arrayfloat'
                        ){
                            $array = explode( ",", $v );

                            if( $parametro[1] == 'arrayint' || $parametro[1] == 'arrayfloat' ){
                                if( $parametro[1] == 'arrayint' ){
                                    foreach( $array as $k => $v ){
                                        $array[ $k ] = intval( $v );
                                    }
                                }
                                else {
                                    foreach( $array as $k => $v ){
                                        $array[ $k ] = floatval( $v );
                                    }
                                }
                            }

                            $resultado[ $parametro[0] ] = $array;
                        }
                        else if(
                               $parametro[1] == 'object'
                            || $parametro[1] == 'objectint'
                            || $parametro[1] == 'objectfloat'
                        ){
                            $object_array = [];

                            foreach( explode( ",", $v ) as $object ){
                                $key                  = preg_replace( "/\[(.*)\]/", "", $object );
                                $value                = preg_replace( ["/^[^\[]+\[/", "/\]/"], "", $object );

                                if( $parametro[1] == 'objectint' )
                                    $value = intval( $value );
                                else if( $parametro[1] == 'objectfloat' )
                                    $value = floatval( $value );

                                $object_array[ $key ] = $value;
                            }

                            $resultado[ $parametro[0] ] = (object)$object_array;
                        }
                    }
                }
            }

            return (object)$resultado;
        }

        private function trataParametros( $match ){
            $novo = $match[0];
            $novo = str_replace( ["©","®"], ["(",")"], $novo );

            if( isset( $match['parametro'] ) && !empty( $match['parametro'] ) ){
                $novo = str_replace( $match['chamada'], "(?'" . str_replace( ",", "___", $match['parametro'] ) . "'(" . $match['valor'] . "))", $novo );
            } else {
                $novo = str_replace( $match['chamada'], "(?'" . str_replace( ",", "___", $match['valor'] ) . "'__ENCLOUSURADO__+)", $novo );
            }

            return $novo;
        }
    }

}


$teste = new acsmtiRoute();
$regex = $teste->gerarRegex( '/pasta1/{{id,int:([0-9]{2,4})}}[/{{parametros,arrayfloat}}[/{{quantidade,int}}/]/]' );

echo "<pre>";
var_dump( $teste->pegarParametrosDaRota( '/pasta1/123/2.9847,5/456', $regex ) );
echo "</pre>";
exit;
