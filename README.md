# acsmti-route

### Para usar:

```php
$padrao = '/path1/{{id:\d+}}[/{{title}}[/{{length}}/]/]';
$rota   = gerarRegex( $padrao );
```

### Explicando

- busca pela rota `path1`
- depois busca um id numérico
- para gerar um parâmetro enclousuramos com `{{` e `}}`, para abrir e fechar o parâmetro
- setamos um nome de variável, nesse caso `id`
- utilizamos de `regex` para dizer as diretrizes de busca, no caso `\d+` para dizer que aceitamos diversos valores numéricos
- para separar o parâmetro do `regex` utilizamos `:`
- fechando o bloco no formato `{{id:\d+}}`
- caso queiramos que um bloco não obrigatório seja utilizado enclousuramos com `[/` e `/]`
- todo bloco a partir desse se também for não obrigatório também será enclousurado da mesma forma de forma encadeada.
- no caso estamos buscando um `title` e em seguida um `length`
- nesses não foi utilizado nenhum parâmetro para o regex então nesse caso é aceito qualquer caracter que não seja `/`
- o formato de cada parametro ficará então `{{title}}` e `{{length}}`
- quanto ao enclousuramento ficará com o formato `[/{{title}}[/{{length}}/]/]`
- iniciamos com `[/` seguimos com o primeiro parâmetro abrindo em seguida o segundo com `[\` e depois de colocar o segundo parâmetro fechamos os dois `/]/]`
- para facilitar a visualização será algo mais ou menos assim `( primeiro ( segundo (...continua )... ) )`

### Vai gerar:

```
^\/path1\/((?'id'(\d+)))(\/((?'title'[^\/]+))(\/((?'length'[^\/]+)))?)?(\/)?$
```

O regex gerado está dentro dos padrões de uso do `PHP` de forma que o uso de funções da própria linguagem terão acesso a seus parâmetros

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

- Quando escrevemos uma parte da rota que necessita de regex para poder tratar não poderão ser usados os caracteres `{` e `}`, já que esses são utilizados para criação de blocos. (resolvido)