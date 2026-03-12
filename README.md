# 🔒 SSL Monitor

Painel de monitoramento de certificados SSL em tempo real, desenvolvido em PHP puro com interface moderna e responsiva. Verifica, classifica e exibe informações técnicas detalhadas dos certificados de uma lista de URLs configurável.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chart.js&logoColor=white)](https://chartjs.org)
[![License: MIT](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Autor](https://img.shields.io/badge/-LinkedIn-blue?style=flat-square&logo=Linkedin&logoColor=white)](https://www.linkedin.com/in/levilucena/)

---

<img width="1868" height="910" alt="image" src="https://github.com/user-attachments/assets/e5b8fddf-3536-409a-8040-943bdc79a59c" />

## Funcionalidades

### Monitor Principal (`index.php`)
- **Verificação em tempo real** de certificados SSL via conexão TLS direta
- **Gauge de saúde** — percentual geral do parque de certificados no header
- **Cards informativos** com dados completos de cada certificado:
  - Domínio (CN), Emissor, Datas de emissão e expiração
  - Tipo e tamanho da chave (ex: RSA 2048 bits)
  - Algoritmo de assinatura (ex: SHA256withRSA)
  - Painel colapsável com: SANs, Número de série, Fingerprint SHA256 e URL OCSP
- **Barra de progresso** de validade com codificação por cor
- **Classificação automática** por status:
  - 🔴 **Expirado** — certificado vencido ou servidor inacessível
  - 🟡 **Expirando** — vence em até **40 dias**
  - 🟢 **Válido** — dentro do prazo
- **Filtros por status** e **busca por URL** em tempo real
- **Atualização automática** a cada 5 minutos com contador regressivo

### Dashboard Analítico (`grafico.php`)
- **Gráfico de barras** — distribuição por status
- **Gráfico de rosca** — proporção visual
- **Barras horizontais** — dias restantes por certificado (ordenado)
- **Calendário de expirações** — quantos certificados vencem em cada um dos próximos 12 meses
- **Legendas detalhadas** com favicon, URL e dias restantes por grupo

### Screenshots
| Screenshot 1 | Screenshot 2 | Screenshot 3 |
|--------------|--------------|--------------|
| <img src="https://github.com/user-attachments/assets/e5b8fddf-3536-409a-8040-943bdc79a59c" width="100%"> | <img src="https://github.com/user-attachments/assets/5a6f88d1-c483-476f-a1d8-e368ae64f509" width="100%"> | <img src="https://github.com/user-attachments/assets/6c4f830c-7ae6-408a-bc05-1d3dff6da7f1" width="100%"> |

| Screenshot 4 | Screenshot 5 | Screenshot 6 |
|--------------|--------------|--------------|
| <img src="https://github.com/user-attachments/assets/718df896-6ab2-46f0-b48a-ac398b221808" width="100%"> | <img src="https://github.com/user-attachments/assets/9b24753e-6ccc-4170-9e91-ea3e39724b97" width="100%"> | <img src="https://github.com/user-attachments/assets/acc7cfa1-a755-4202-afed-11d33c38b0db" width="100%"> |

---

## Interface

> Tema dark moderno com glassmorphism, gradientes e animações suaves

- Fundo com blobs de gradiente radial animados
- Cards com borda superior colorida e glow no hover
- Animação de entrada em cascata (fade-up staggered)
- Cards expirados com borda pulsante em vermelho
- Layout responsivo (mobile, tablet e desktop)

---

## Tecnologias

| Tecnologia | Uso |
|---|---|
| **PHP 8.x** | Lógica de verificação SSL, parsing de certificados |
| **OpenSSL (ext)** | `openssl_x509_parse`, `openssl_pkey_get_details`, `openssl_x509_fingerprint` |
| **Bootstrap 5.3** | Grid responsivo e componentes |
| **Bootstrap Icons** | Iconografia |
| **Chart.js 4** | Gráficos do dashboard |
| **Google Fonts (Inter)** | Tipografia |

---

## Instalação

### Pré-requisitos
- PHP 8.x com extensão OpenSSL habilitada
- Arquivo `cacert.pem` para validação de certificados (ex: [curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem))

### Usando o servidor embutido do PHP

```bash
# Clone o repositório
git clone https://github.com/LeviLucena/Certificados-SSL-Web.git
cd Certificados-SSL-Web

# Inicie o servidor (ajuste o caminho do PHP e do php.ini)
php -c php.ini -S localhost:8080
```

Acesse **http://localhost:8080** no navegador.

### Configuração do `php.ini`

Certifique-se de que o arquivo `php.ini` contém:

```ini
extension=openssl
openssl.cafile="C:\caminho\para\cacert.pem"
```

---

## Configuração

### Adicionar URLs para monitorar

Edite o array `$urls` no início do arquivo `index.php`:

```php
$urls = [
    "https://www.meusite.com.br",
    "https://api.minhaempresa.com",
    "https://painel.sistema.io",
    // Adicione mais URLs aqui...
];
```

### Ajustar o limiar de alerta "Expirando"

Por padrão, certificados com **40 dias ou menos** de validade são marcados como "Expirando". Para alterar:

```php
// index.php — função getValidityClass()
if ($d <= 40) return "expiring"; // altere 40 para o valor desejado
```

### Ajustar timeout de conexão

```php
// index.php — função getCertificate()
$s = @stream_socket_client("ssl://{$p['host']}:{$port}", $e, $err, 8, ...);
//                                                                   ↑
//                                                         segundos por URL
```

---

## Estrutura do projeto

```
Certificados-SSL-Web/
├── index.php      # Monitor principal — listagem e detalhes dos certificados
├── grafico.php    # Dashboard analítico — gráficos e estatísticas
├── php.ini        # Configuração do PHP (OpenSSL, cacert)
└── README.md
```

---

## Informações exibidas por certificado

| Campo | Descrição |
|---|---|
| Domínio | Common Name (CN) do Subject |
| Emissor | Common Name (CN) do Issuer |
| Emitido em | Data de início da validade |
| Expira em | Data de expiração |
| Chave | Tipo e tamanho (ex: RSA 2048 bits) |
| Algoritmo | Algoritmo de assinatura (ex: SHA256withRSA) |
| SANs | Nomes alternativos do certificado |
| Série (hex) | Número de série em hexadecimal |
| Fingerprint | SHA256 do certificado |
| OCSP | URL do servidor de revogação |

---

## Licença

Distribuído sob a [MIT License](LICENSE).

---

## Autor

Desenvolvido por **Levi Lucena**

[![LinkedIn](https://img.shields.io/badge/-LinkedIn-blue?style=flat-square&logo=Linkedin&logoColor=white)](https://www.linkedin.com/in/levilucena/)
[![GitHub](https://img.shields.io/badge/-GitHub-181717?style=flat-square&logo=github)](https://github.com/LeviLucena)
