# Certificado SSL Checker - Projeto Final

Este projeto é um Certificado SSL Checker, que verifica e exibe informações sobre os certificados SSL de uma lista de URLs. Ele permite visualizar o status de validade dos certificados SSL, o domínio, o emissor e a data de validade.

![pagina](https://github.com/LeviLucena/Certificados-SSL-Web/assets/34045910/2cf1e710-656d-4c22-b02d-c14048b29600)


## Funcionalidades

- Verificação de certificados SSL de uma lista de URLs.
- Exibição do status de validade dos certificados (válido, expirando ou expirado).
- Exibição do domínio e do emissor do certificado.
- Exibição da data de validade do certificado.
- Exibição de gráfico com distinção entre as URLs
- Paginação dos resultados para melhor organização e visualização.
- Classificação dos certificados por tempo de validade (expirados, expirando e válidos).
- Interface amigável e responsiva.

## Tecnologias utilizadas

- PHP: linguagem de programação utilizada para a lógica do servidor e manipulação dos certificados SSL.
- HTML: linguagem de marcação utilizada para a estrutura da página web.
- CSS: linguagem de estilo utilizada para a aparência e o design da página web.
- OpenSSL: biblioteca utilizada para obter informações dos certificados SSL.
- GitHub: plataforma utilizada para hospedar o código-fonte do projeto.

## Instruções de uso

1. Clone ou faça o download do repositório para o seu ambiente local.
2. Certifique-se de ter o PHP instalado em seu sistema.
3. Configure o servidor web (por exemplo, Apache) para executar o código PHP.
4. Edite o arquivo `index.php` e adicione as URLs que você deseja verificar na variável `$urls`.
5. Acesse a página `index.php` em seu navegador para visualizar os resultados.

## Personalização

- Caso deseje adicionar ou remover URLs a serem verificadas, edite o array `$urls` no arquivo `index.php`.
- É possível personalizar a aparência e o estilo da página por meio do arquivo CSS (`style.css`).
- Para alterar a quantidade de resultados exibidos por página, ajuste o valor da variável `$perPage` no arquivo `index.php`.

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para enviar sugestões, correções de bugs ou melhorias por meio de pull requests.

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE). Sinta-se à vontade para utilizá-lo e modificá-lo de acordo com suas necessidades.

---

Espero que esse README seja útil para apresentar o projeto final em seu repositório do GitHub. Sinta-se à vontade para personalizá-lo de acordo com suas preferências e necessidades.
