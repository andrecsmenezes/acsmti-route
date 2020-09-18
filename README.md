# acsmti-route

### Para usar:

```php
$padrao = '/path1/{id:\d+}[/{title}[/{length}]]';
$rota   = gerarRegex( $padrao );
```

### Vai gerar:

```
^\/path1\/((?'id'(\d+)))(\/((?'title'[^\/]+))(\/((?'length'[^\/]+)))?)?(\/)?$
```

### Depois:

```php
$url        = '/path1/123/teste/456/';
$parametros = pegarParametrosDaRota( $url, $rota );
```

### Vai gerar:

```php
echo $parametros->id     // 123
echo $parametros->title  // teste
echo $parametros->length // 456
```

### Limitação encontrada até o momento:

- Quando escrevemos uma parte da rota que necessita de regex para poder tratar não poderão ser usados os caracteres `{` e `}`, já que esses são utilizados para criação de blocos.