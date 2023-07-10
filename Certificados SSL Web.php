<?php
//======================================================================
// Desenvolvido por Levi Lucena - linkedin.com/in/levilucena 
//======================================================================
$urls = [
    "https://apimedcovid.saude.sp.gov.br",
    "https://apimedcovidhom.saude.sp.gov.br",
    "https://ariadne.saude.sp.gov.br",
    "https://autoestima.sp.gov.br",
    "https://homologacao-telemedicina.saude.sp.gov.br",
    "https://telemedicina.saude.sp.gov.br",
    "https://vpn.sdr.sp.gov.br",
    "https://avaesus.saudeemacao.saude.sp.gov.br",
    "https://homologacaosivisa.saude.sp.gov.br",
    "https://honiara.saude.sp.gov.br",
    "https://horamarcada.saude.sp.gov.br",
    "https://hsistema.saude.sp.gov.br",
    "https://humanizases.saude.sp.gov.br",
    "https://imunouvses.saude.sp.gov.br",
    "https://jenkins.saude.sp.gov.br",
    "https://jupiter.saude.sp.gov.br",
    "https://kanboard.saude.sp.gov.br",
    "https://kumo.saude.sp.gov.br",
    "https://mail.saude.sp.gov.br",
    "https://medcovid.saude.sp.gov.br",
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

// Paginação
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$perPage = 20;
$totalItems = count($certificates);
$totalPages = ceil($totalItems / $perPage);
$start = ($page - 1) * $perPage;
$end = $start + $perPage;
$certificatesPage = array_slice($certificates, $start, $perPage);

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

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            color: #000;
            border: 1px solid #ddd;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="conteudo text-center">
        <img src="https://www.zarphost.com.br/wp-content/uploads/2020/01/certificado-ssl.png" class="imagem">
        <h1>Gerenciamento de Certificados SSL</h1>
    </div>

    <?php foreach ($certificatesPage as $certificate) : ?>
        <div class="container <?php echo isset($certificate["validityClass"]) ? $certificate["validityClass"] : ""; ?>">
            <table>
                <tr>
                    <th>URL</th>
                    <td><?php echo isset($certificate["url"]) ? $certificate["url"] : ""; ?></td>
                </tr>
                <?php if (is_array($certificate) && !$certificate["valid"]) : ?>
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
                    <?php $certificateInfo = is_array($certificate) ? openssl_x509_parse($certificate["certificate"]) : null; ?>
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

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <?php if ($i == $page) : ?>
                <a href="?page=<?php echo $i; ?>" class="active"><?php echo $i; ?></a>
            <?php else : ?>
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div> 
</body>
</html>

           
