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

                        $resultado[ $parametro[0] ] = $this->setParam( $parametro[1], $v );
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

        private function setParam( $param, $val ){
            if( preg_match( "/(int|float|mobject|object|array|boolean)/", $param, $match ) ){
                $method    = 'set' . ucfirst( $match[0] );
                $new_param = preg_replace( "/^" . $match[0] . "/", "", $param );

                if( is_array( $val ) ){
                    foreach( $val as $k => $v ){
                        $val[ $k ] = $this->setParam(
                                        $new_param,
                                        $this->$method( $v )
                                    );
                    }

                    return $val;
                }
                else if( is_object( $val ) ){
                    foreach( $val as $k => $v ){
                        $val->{$k} = $this->setParam(
                                        $new_param,
                                        $this->$method( $v )
                                    );
                    }

                    return $val;
                }
                else {
                    return $this->setParam(
                                        $new_param,
                                        $this->$method( $val )
                                    );
                }
            }

            return $val;
        }

        private function setInt( $val ){
            return intval( $val );
        }

        private function setFloat( $val ){
            return floatval( $val );
        }

        private function setObject( $val ){
            $array  = explode( "|", $val );
            $object = [];

            foreach( $array as $a ){
                $key   = preg_replace( "/\[(.*)\]/", "", $a );
                $value = preg_replace( ["/^[^\[]+\[/", "/\]/"], "", $a );

                $object[ $key ] = $value;
            }

            return (object)$object;
        }

        private function setMobject( $val ){
            $array  = explode( "|", $val );
            $object = [];

            foreach( $array as $a ){
                $key   = preg_replace( "/\[(.*)\]/", "", $a );
                $value = preg_replace( ["/^[^\[]+\[/", "/\]/"], "", $a );

                $object[ $key ][] = $value;
            }

            return (object)$object;
        }

        private function setArray( $val ){
            if( preg_match( "/(\[|\]|\|)/", $val ) ){
                $itens  = [];

                foreach( explode( "|", $val ) as $item ){
                    $key   = preg_replace( "/\[(.*)\]/", "", $item );
                    $value = preg_replace( ["/^[^\[]+\[/", "/\]/"], "", $item );

                    $itens[ $key ] = explode( ',', $value );
                }

                return $itens;
            }
            else {
                return explode( ",", $val );
            }
        }

        private function setBoolean( $val ){
            return boolval( $val );
        }
    }

}


$teste = new acsmtiRoute();
$regex = $teste->gerarRegex( '/pasta1/{{pesquisa,array}}' );

echo "<pre>";
var_dump( $teste->pegarParametrosDaRota( '/pasta1/ids[10,20,30]|variaveis[aaa,bbb]|blablabla[1,2,3]', $regex ) );
echo "</pre>";
exit;
