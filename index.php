<?php
//======================================================================
// Desenvolvido por Levi Lucena - linkedin.com/in/levilucena 
//======================================================================
$urls = [
    "https://www.google.com",

        // Adicione mais URLs aqui...
];

// Função para formatar a data do certificado
function formatDate($timestamp) {
    return date("d/m/Y", $timestamp);
}

// Função para obter o certificado SSL de uma URL
function getCertificate($url) {
    $host = parse_url($url, PHP_URL_HOST);
    $port = parse_url($url, PHP_URL_PORT) ?: 443;

    $streamContext = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => true,
            "verify_peer_name" => true,
            "allow_self_signed" => false
        ]
    ]);

    $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $streamContext);

    if (!$stream) {
        // Erro ao estabelecer conexão SSL
        return null;
    }

    $params = stream_context_get_params($stream);

    if (empty($params["options"]["ssl"]["peer_certificate"])) {
        // Certificado inválido ou expirado
        return null;
    }

    return $params["options"]["ssl"]["peer_certificate"];
}

// Obter certificados para cada URL e classificá-los por tempo de validade
$certificates = [];
foreach ($urls as $url) {
    $certificate = getCertificate($url);

    if ($certificate === null) {
        $certificates[] = [
            "url" => $url,
            "valid" => false,
            "validityClass" => "expired"
        ];
        continue; // Ignorar URLs inválidas ou com certificado expirado
    }

    $certificateInfo = openssl_x509_parse($certificate);

    if ($certificateInfo === false) {
        continue; // Ignorar certificados inválidos
    }

    $validTo = $certificateInfo["validTo_time_t"] ?? 0;

    $certificates[] = [
        "url" => $url,
        "valid" => true,
        "certificate" => $certificate,
        "validTo" => $validTo,
        "validityClass" => getValidityClass($validTo)
    ];
}

// Função para classificar os certificados por tempo de validade
function compareByValidity($certificateA, $certificateB) {
    $validToA = $certificateA["validTo"] ?? 0;
    $validToB = $certificateB["validTo"] ?? 0;

    return $validToA - $validToB;
}

// Classificar os certificados por tempo de validade
usort($certificates, 'compareByValidity');

// Função para obter a classe de validade com base na data de validade
function getValidityClass($validTo) {
    if ($validTo > 0) {
        $difference = $validTo - time();
        $daysToExpiration = floor($difference / (60 * 60 * 24));

        if ($daysToExpiration <= 30) {
            return "expired";
        } elseif ($daysToExpiration <= 90) {
            return "expiring";
        }
    }

    return "valid";
}
?>

<!DOCTYPE html>
<html>
<!-- ====================================================================== -->
<!-- Desenvolvido por Levi Lucena - linkedin.com/in/levilucena -->
<!-- ====================================================================== -->
<head>
    <title>Verificação de Certificado SSL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            zoom: 0.9;
        }

        .imagem {
            width: 200px;
            height: auto;
            margin-right: 10px;
        }

        .conteudo {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 500px;
            height: 140px; /* Tamanho fixo em pixels */
            display: inline-block;
            margin: 5px;
            padding: 5px;
        }

        h1 {
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        .expired {
            background-color: red;
            color: white;
        }

        .expiring {
            background-color: orange;
        }

        .valid {
            background-color: green;
            color: white;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px; /* Defina o valor do espaçamento vertical desejado */
            margin-right: 10px;
        }

        .legend-color {
            width: 10px;
            height: 10px;
            margin-right: 5px;
        }

        .button-style {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .button-style:hover {
            background-color: #0056b3;
        }

        .button-style:active {
            background-color: #003380;
        }

    </style>
</head>
<body>
    <div class="conteudo text-center">
        <img src="https://www.zarphost.com.br/wp-content/uploads/2020/01/certificado-ssl.png" class="imagem">
        <h1>Gerenciamento de Certificados SSL</h1>
    </div>

    <div class="chart-legend conteudo text-center">
    <div class="legend-item">
        <div class="legend-color" style="background-color: red;"></div>
        <div class="legend-title">Expirados ou inválidos</div>
    </div>

    <div class="legend-item">
        <div class="legend-color" style="background-color: orange;"></div>
        <div class="legend-title">Expirando</div>
    </div>

    <div class="legend-item">
        <div class="legend-color" style="background-color: green;"></div>
        <div class="legend-title">Válidos</div>
    </div> 

    <div class="legend-item">
  <a href="grafico.php" target="_blank" onclick="openWindow(event)" class="button-style">
    <div class="legend-title">Gráfico</div>
  </a>
</div>
</div>


    <?php foreach ($certificates as $certificate) : ?>
        <div class="container <?php echo $certificate["validityClass"]; ?>">
            <table>
                <tr>
                    <th>URL</th>
                    <td><?php echo $certificate["url"]; ?></td>
                </tr>
                <?php if (!$certificate["valid"]) : ?>
                    <tr>
                        <th>Domínio</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Emissor</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Validade</th>
                        <td></td>
                    </tr>
                <?php else : ?>
                    <?php $certificateInfo = openssl_x509_parse($certificate["certificate"]); ?>
                    <tr>
                        <th>Domínio</th>
                        <td><?php echo $certificateInfo["subject"]["CN"] ?? "N/A"; ?></td>
                    </tr>
                    <tr>
                        <th>Emissor</th>
                        <td><?php echo $certificateInfo["issuer"]["CN"] ?? "N/A"; ?></td>
                    </tr>
                    <tr>
                        <th>Validade</th>
                        <td><?php echo formatDate($certificateInfo["validFrom_time_t"] ?? 0); ?> até <?php echo formatDate($certificateInfo["validTo_time_t"] ?? 0); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    <?php endforeach; ?>

    <script>
        setTimeout(function() {
            location.reload();
        }, 300000); // Recarregar a página após 5 minuto (300000 milissegundos)
    </script>


<script>
    function openWindow(event) {
        event.preventDefault(); // Impede o comportamento padrão do link
        var width = 1000; // Largura da janela em pixels
        var height = 700; // Altura da janela em pixels
        var left = (window.screen.width - width) / 2; // Posição horizontal da janela
        var top = (window.screen.height - height) / 2; // Posição vertical da janela
        var features = `width=${width},height=${height},left=${left},top=${top}`;
        window.open(event.target.href, "_blank", features);
    }
</script>


</body>
</html>
