<?php
//======================================================================
// Desenvolvido por Levi Lucena - linkedin.com/in/levilucena
//======================================================================
$urls = [
    "https://www.google.com.br",
    "https://expired.badssl.com/",
    "https://wrong.host.badssl.com",
    "https://untrusted-root.badssl.com",
    "https://revoked.badssl.com",
    "https://gursutapu.gursu.bel.tr/Login.aspx?mesaj=",
    "https://www.ssllabs.com/ssltest/analyze.html?d=elearn.nutifood.com.vn",
    "https://www.gov.br",
    "https://www.bb.com.br",
    "https://www.itau.com.br",
    "https://www.bradesco.com.br",
    "https://crt.sh",
    "https://censys.io",
    "https://securitytrails.com",
    "https://www.caixa.gov.br",
    "https://www.uol.com.br",
    "https://www.globo.com",
    "https://www.mercadolivre.com.br",
    "https://www.github.com",
    "https://www.microsoft.com",
    "https://www.amazon.com",
    "https://www.cloudflare.com",
    "https://www.stackoverflow.com",
    // Adicione mais URLs aqui...
];

function formatDate($ts)   { return $ts ? date("d/m/Y", $ts) : "—"; }
function daysRemaining($t) { return max(0, (int) floor(($t - time()) / 86400)); }

function getValidityClass($v) {
    if ($v > 0) { $d = floor(($v - time()) / 86400); if ($d <= 0) return "expired"; if ($d <= 40) return "expiring"; }
    return "valid";
}

function getCertificate($url) {
    $p = parse_url($url);
    $ctx = stream_context_create(["ssl" => [
        "capture_peer_cert" => true, "verify_peer" => true, "verify_peer_name" => true,
        "allow_self_signed" => false, "crypto_method" => STREAM_CRYPTO_METHOD_TLS_CLIENT, "ciphers" => "HIGH:MEDIUM",
    ]]);
    $port = $p['port'] ?? 443;
    $s = @stream_socket_client("ssl://{$p['host']}:{$port}", $e, $err, 8, STREAM_CLIENT_CONNECT, $ctx);
    if (!$s) return null;
    $pm = stream_context_get_params($s);
    return $pm["options"]["ssl"]["peer_certificate"] ?? null;
}

$certificates = [];
foreach ($urls as $url) {
    $cert = getCertificate($url);
    if (!$cert) { $certificates[] = ["url"=>$url,"valid"=>false,"validityClass"=>"expired","validTo"=>0]; continue; }
    $info = openssl_x509_parse($cert);
    if (!$info) continue;
    $vt = $info["validTo_time_t"] ?? 0;

    // Chave pública
    $pubkey     = openssl_pkey_get_public($cert);
    $keyDetails = $pubkey ? openssl_pkey_get_details($pubkey) : [];
    $keyType    = match($keyDetails['type'] ?? -1) {
        OPENSSL_KEYTYPE_RSA => 'RSA', OPENSSL_KEYTYPE_EC => 'EC',
        OPENSSL_KEYTYPE_DSA => 'DSA', default => '?'
    };
    $keyBits    = $keyDetails['bits'] ?? 0;

    // Algoritmo de assinatura
    $sigAlgo = $info['signatureTypeLN'] ?? $info['signatureTypeSN'] ?? 'N/A';
    $sigAlgo = str_replace(['WithRSAEncryption','withRSAEncryption','Encryption'], ['withRSA','withRSA',''], $sigAlgo);

    // Nomes alternativos (SANs)
    $sanRaw = $info['extensions']['subjectAltName'] ?? '';
    $sans   = implode(', ', array_map(
        fn($s) => trim(preg_replace('/^DNS:/', '', $s)),
        array_filter(explode(',', $sanRaw), fn($s) => str_contains($s, 'DNS:'))
    ));

    // Número de série
    $serial = strtoupper($info['serialNumberHex'] ?? ($info['serialNumber'] ?? 'N/A'));

    // Fingerprint SHA256
    $fp  = openssl_x509_fingerprint($cert, 'sha256') ?: '';
    $fingerprint = implode(':', str_split(strtoupper($fp), 2));

    // OCSP
    $aia = $info['extensions']['authorityInfoAccess'] ?? '';
    preg_match('/OCSP\s*-\s*URI:(\S+)/i', $aia, $ocspM);
    $ocsp = $ocspM[1] ?? '—';

    $certificates[] = [
        "url"=>$url, "valid"=>true, "validTo"=>$vt, "validFrom"=>$info["validFrom_time_t"]??0,
        "validityClass"=>getValidityClass($vt), "subject"=>$info["subject"]["CN"]??"N/A",
        "issuer"=>$info["issuer"]["CN"]??"N/A", "daysRemaining"=>daysRemaining($vt),
        "keyType"=>$keyType, "keyBits"=>$keyBits, "sigAlgo"=>$sigAlgo,
        "sans"=>$sans, "serial"=>$serial, "fingerprint"=>$fingerprint, "ocsp"=>$ocsp,
    ];
}
usort($certificates, fn($a,$b) => ($a["validTo"]??0)-($b["validTo"]??0));

$total    = count($certificates);
$expired  = count(array_filter($certificates, fn($c) => $c["validityClass"]==="expired"));
$expiring = count(array_filter($certificates, fn($c) => $c["validityClass"]==="expiring"));
$valid    = count(array_filter($certificates, fn($c) => $c["validityClass"]==="valid"));
$healthScore = $total > 0 ? max(0, round((($valid*100+$expiring*40)/($total*100))*100)) : 100;
$gaugeDash   = round($healthScore/100*175.9);
$gaugeColor  = $healthScore >= 80 ? '#22c55e' : ($healthScore >= 50 ? '#f59e0b' : '#ef4444');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSL Monitor</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',system-ui,sans-serif;background:#050b18;color:#e2e8f0;min-height:100vh;overflow-x:hidden}

    /* ─── Ambient blobs ─── */
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:
        radial-gradient(ellipse 55% 45% at 5% 15%,rgba(99,102,241,.13) 0%,transparent 65%),
        radial-gradient(ellipse 45% 40% at 95% 5%,rgba(139,92,246,.09) 0%,transparent 65%),
        radial-gradient(ellipse 40% 50% at 65% 95%,rgba(34,197,94,.06) 0%,transparent 65%),
        radial-gradient(ellipse 30% 30% at 50% 50%,rgba(56,189,248,.04) 0%,transparent 60%)}

    .z1{position:relative;z-index:1}

    /* ─── Header ─── */
    header{padding:18px 40px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(5,11,24,.85);
      backdrop-filter:blur(16px);position:sticky;top:0;z-index:200;display:flex;align-items:center;gap:18px;flex-wrap:wrap}

    .brand-logo{width:44px;height:44px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;
      display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;
      box-shadow:0 0 24px rgba(99,102,241,.45)}
    .brand-name{font-size:1.25rem;font-weight:900;letter-spacing:-.3px;color:#e2e8f0}
    .brand-sub{font-size:.72rem;color:#475569;margin-top:1px}

    /* Gauge */
    .gauge-box{display:flex;align-items:center;gap:12px}
    .gauge-ring{position:relative;width:68px;height:68px;flex-shrink:0}
    .gauge-ring svg{transform:rotate(-90deg)}
    .gauge-label{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
    .gauge-pct{font-size:.95rem;font-weight:900;line-height:1}
    .gauge-word{font-size:.52rem;color:#475569;text-transform:uppercase;letter-spacing:.6px;margin-top:1px}
    .gauge-stats{font-size:.74rem;color:#64748b;line-height:2}
    .gauge-stats b{color:#94a3b8}

    /* Buttons */
    .btn-glass{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#cbd5e1;
      border-radius:10px;font-size:.83rem;font-weight:500;padding:8px 16px;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
    .btn-glass:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:#fff}
    .btn-primary2{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff;
      border-radius:10px;font-size:.83rem;font-weight:600;padding:8px 18px;cursor:pointer;transition:all .2s;
      box-shadow:0 4px 16px rgba(99,102,241,.35);display:inline-flex;align-items:center;gap:5px}
    .btn-primary2:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.45);color:#fff}

    #countdown{font-weight:800;color:#818cf8;font-variant-numeric:tabular-nums}

    /* ─── Stat tiles ─── */
    .stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:26px 40px 0}
    .stat-tile{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:16px;
      padding:18px 20px;display:flex;align-items:center;gap:14px;transition:all .25s;cursor:default;
      position:relative;overflow:hidden}
    .stat-tile::after{content:'';position:absolute;inset:0;border-radius:16px;opacity:0;transition:opacity .3s;
      background:linear-gradient(135deg,rgba(255,255,255,.04),transparent)}
    .stat-tile:hover{transform:translateY(-3px);border-color:rgba(255,255,255,.12)}
    .stat-tile:hover::after{opacity:1}
    .s-icon{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0}
    .s-num{font-size:2rem;font-weight:900;line-height:1}
    .s-lbl{font-size:.73rem;color:#64748b;margin-top:3px}

    .ic-total{background:transparent} .n-total{color:#818cf8}
    .ic-exp{background:transparent}   .n-exp{color:#f87171}
    .ic-warn{background:transparent}  .n-warn{color:#fbbf24}
    .ic-ok{background:transparent}    .n-ok{color:#4ade80}

    /* ─── Toolbar ─── */
    .toolbar{padding:18px 40px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .pill{padding:6px 16px;border-radius:20px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
      color:#64748b;font-size:.81rem;font-weight:500;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:5px}
    .pill:hover{color:#e2e8f0;border-color:rgba(255,255,255,.2)}
    .p-all{background:linear-gradient(135deg,#6366f1,#8b5cf6)!important;border-color:transparent!important;color:#fff!important}
    .p-exp{background:linear-gradient(135deg,#ef4444,#dc2626)!important;border-color:transparent!important;color:#fff!important}
    .p-warn{background:linear-gradient(135deg,#f59e0b,#d97706)!important;border-color:transparent!important;color:#fff!important}
    .p-ok{background:linear-gradient(135deg,#22c55e,#16a34a)!important;border-color:transparent!important;color:#fff!important}

    .srch-wrap{position:relative;margin-left:auto}
    .srch-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#475569;font-size:.9rem;pointer-events:none}
    .srch{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:#e2e8f0;
      border-radius:10px;font-size:.83rem;padding:7px 14px 7px 36px;width:230px;transition:all .2s}
    .srch:focus{outline:none;border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.06);
      box-shadow:0 0 0 3px rgba(99,102,241,.12);color:#e2e8f0}

    /* ─── Cert cards ─── */
    .cards-grid{padding:0 40px 44px}

    .cert-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:18px;
      overflow:hidden;height:100%;transition:all .28s cubic-bezier(.4,0,.2,1);position:relative}
    .cert-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;z-index:1}
    .cert-card.valid::before{background:linear-gradient(90deg,#16a34a,#22c55e,#4ade80,#22c55e)}
    .cert-card.expiring::before{background:linear-gradient(90deg,#b45309,#f59e0b,#fbbf24,#f59e0b)}
    .cert-card.expired::before{background:linear-gradient(90deg,#dc2626,#ef4444,#f87171,#ef4444)}

    .cert-card.expired::before{animation:pulseBorder 2s ease-in-out infinite}
    @keyframes pulseBorder{0%,100%{opacity:1}50%{opacity:.3}}

    .cert-card:hover{transform:translateY(-6px);border-color:rgba(255,255,255,.13)}
    .cert-card.valid:hover{box-shadow:0 24px 60px rgba(34,197,94,.12)}
    .cert-card.expiring:hover{box-shadow:0 24px 60px rgba(245,158,11,.12)}
    .cert-card.expired:hover{box-shadow:0 24px 60px rgba(239,68,68,.12)}

    /* Card header */
    .c-head{padding:15px 16px 11px;display:flex;align-items:flex-start;gap:9px}
    .c-favicon{width:22px;height:22px;border-radius:5px;flex-shrink:0;margin-top:2px}
    .c-url{font-size:.83rem;font-weight:600;color:#7dd3fc;word-break:break-all;flex:1;line-height:1.45}

    /* Badge */
    .c-badge{flex-shrink:0;padding:3px 9px;border-radius:20px;font-size:.66rem;font-weight:700;
      text-transform:uppercase;letter-spacing:.6px;display:inline-flex;align-items:center;gap:4px}
    .c-badge.valid{background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.28)}
    .c-badge.expiring{background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.28)}
    .c-badge.expired{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.28)}

    /* Info grid */
    .c-info{display:grid;grid-template-columns:1fr 1fr;border-top:1px solid rgba(255,255,255,.05)}
    .c-cell{padding:9px 16px;border-right:1px solid rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.05)}
    .c-cell:nth-child(even){border-right:none}
    .c-cell:nth-last-child(-n+2){border-bottom:none}
    .c-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:#475569;margin-bottom:3px}
    .c-val{font-size:.79rem;color:#cbd5e1;font-weight:500;word-break:break-word}

    /* Progress */
    .c-prog{padding:11px 16px 15px;border-top:1px solid rgba(255,255,255,.05)}
    .c-pmeta{display:flex;justify-content:space-between;font-size:.72rem;margin-bottom:7px;color:#475569}
    .c-pdays{font-weight:700}
    .c-pdays.valid{color:#4ade80} .c-pdays.expiring{color:#fbbf24} .c-pdays.expired{color:#f87171}
    .c-track{height:5px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden}
    .c-fill{height:100%;border-radius:3px;transition:width 1s ease}
    .c-fill.valid{background:linear-gradient(90deg,#16a34a,#4ade80)}
    .c-fill.expiring{background:linear-gradient(90deg,#b45309,#fbbf24)}
    .c-fill.expired{background:linear-gradient(90deg,#991b1b,#ef4444)}

    /* Error state */
    .c-err{padding:14px 16px;display:flex;align-items:center;gap:9px;
      font-size:.81rem;color:#f87171;border-top:1px solid rgba(255,255,255,.05)}

    /* Collapsible details */
    .c-toggle{width:100%;background:none;border:none;border-top:1px solid rgba(255,255,255,.05);
      padding:9px 16px;color:#475569;font-size:.74rem;font-weight:600;text-align:left;cursor:pointer;
      display:flex;align-items:center;gap:6px;transition:color .2s}
    .c-toggle:hover{color:#94a3b8}
    .c-toggle i{transition:transform .25s;margin-left:auto}
    .c-toggle.open{color:#94a3b8}
    .c-toggle.open i{transform:rotate(180deg)}
    .c-details{display:none;border-top:1px solid rgba(255,255,255,.04);padding:10px 16px 14px}
    .c-details.show{display:block}
    .c-drow{display:flex;gap:8px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.76rem;align-items:baseline}
    .c-drow:last-child{border-bottom:none}
    .c-dlbl{color:#475569;white-space:nowrap;min-width:90px;flex-shrink:0}
    .c-dval{color:#94a3b8;word-break:break-all;font-family:monospace;font-size:.72rem}
    .c-dval.mono{letter-spacing:.3px}

    /* Fade-in stagger */
    @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
    .cert-col{animation:fadeUp .45s ease both}

    /* Empty */
    .empty-hint{text-align:center;padding:64px 20px;color:#334155}
    .empty-hint i{font-size:3rem}

    /* Footer */
    footer{border-top:1px solid rgba(255,255,255,.05);padding:16px 40px;font-size:.76rem;
      color:#334155;text-align:center}
    footer a{color:#818cf8;text-decoration:none}
    footer a:hover{color:#a5b4fc}

    /* Responsive */
    @media(max-width:900px){
      header,.stat-row,.toolbar,.cards-grid,footer{padding-left:16px;padding-right:16px}
      .stat-row{grid-template-columns:repeat(2,1fr)}
      .gauge-box{display:none}
    }
  </style>
</head>
<body>
<div class="z1">

<!-- ╔══════════════════════ HEADER ══════════════════════╗ -->
<header>
  <div class="brand-logo">🔒</div>
  <div>
    <div class="brand-name">SSL Monitor</div>
    <div class="brand-sub"><?= date("d/m/Y \· H:i") ?> &nbsp;·&nbsp; <?= $total ?> sites</div>
  </div>

  <!-- Health gauge -->
  <div class="gauge-box ms-3 d-none d-lg-flex">
    <div class="gauge-ring">
      <svg viewBox="0 0 68 68" width="68" height="68">
        <circle cx="34" cy="34" r="28" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="6"/>
        <circle cx="34" cy="34" r="28" fill="none"
          stroke="<?= $gaugeColor ?>" stroke-width="6" stroke-linecap="round"
          stroke-dasharray="<?= $gaugeDash ?> 175.9"
          style="filter:drop-shadow(0 0 8px <?= $gaugeColor ?>99);transition:stroke-dasharray 1s ease"/>
      </svg>
      <div class="gauge-label">
        <span class="gauge-pct" style="color:<?= $gaugeColor ?>"><?= $healthScore ?>%</span>
        <span class="gauge-word">saúde</span>
      </div>
    </div>
    <div class="gauge-stats">
      <div><b><?= $valid ?></b> válidos</div>
      <div><b><?= $expiring ?></b> expirando</div>
      <div><b><?= $expired ?></b> expirados</div>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
    <span style="font-size:.76rem;color:#334155">Atualiza em <span id="countdown">5:00</span></span>
    <a href="grafico.php" class="btn-glass" onclick="openGraph(event)">
      <i class="bi bi-bar-chart-line-fill"></i>Dashboard
    </a>
    <button class="btn-primary2" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise"></i>Atualizar
    </button>
  </div>
</header>

<!-- ╔══════════════════════ STATS ══════════════════════╗ -->
<div class="stat-row">
  <div class="stat-tile">
    <div class="s-icon ic-total"><i class="bi bi-globe2" style="color:#818cf8"></i></div>
    <div><div class="s-num n-total"><?= $total ?></div><div class="s-lbl">Sites monitorados</div></div>
  </div>
  <div class="stat-tile">
    <div class="s-icon ic-exp"><i class="bi bi-shield-x" style="color:#f87171"></i></div>
    <div><div class="s-num n-exp"><?= $expired ?></div><div class="s-lbl">Expirados / Inválidos</div></div>
  </div>
  <div class="stat-tile">
    <div class="s-icon ic-warn"><i class="bi bi-exclamation-triangle" style="color:#fbbf24"></i></div>
    <div><div class="s-num n-warn"><?= $expiring ?></div><div class="s-lbl">Expirando em 40 dias</div></div>
  </div>
  <div class="stat-tile">
    <div class="s-icon ic-ok"><i class="bi bi-shield-check" style="color:#4ade80"></i></div>
    <div><div class="s-num n-ok"><?= $valid ?></div><div class="s-lbl">Certificados válidos</div></div>
  </div>
</div>

<!-- ╔══════════════════════ TOOLBAR ══════════════════════╗ -->
<div class="toolbar">
  <button class="pill p-all" data-f="all"><i class="bi bi-grid-3x3-gap"></i>Todos (<?= $total ?>)</button>
  <button class="pill" data-f="expired"><i class="bi bi-x-circle"></i>Expirados (<?= $expired ?>)</button>
  <button class="pill" data-f="expiring"><i class="bi bi-clock-history"></i>Expirando (<?= $expiring ?>)</button>
  <button class="pill" data-f="valid"><i class="bi bi-shield-check"></i>Válidos (<?= $valid ?>)</button>
  <div class="srch-wrap">
    <i class="bi bi-search"></i>
    <input class="srch" id="srch" type="text" placeholder="Buscar URL...">
  </div>
</div>

<!-- ╔══════════════════════ CARDS ══════════════════════╗ -->
<div class="cards-grid">
  <div class="row g-3" id="grid">
    <?php foreach ($certificates as $i => $c):
      $cls   = htmlspecialchars($c["validityClass"]);
      $url   = htmlspecialchars($c["url"]);
      $host  = parse_url($c["url"], PHP_URL_HOST);
      $lbl   = match($cls){"expired"=>"Expirado","expiring"=>"Expirando",default=>"Válido"};
      $ico   = match($cls){"expired"=>"bi-shield-x","expiring"=>"bi-hourglass-split",default=>"bi-shield-check"};
      $delay = min($i*.045,.65);
    ?>
    <div class="col-12 col-sm-6 col-xl-4 cert-col"
         data-s="<?= $cls ?>" data-u="<?= $url ?>" style="animation-delay:<?= $delay ?>s">
      <div class="cert-card <?= $cls ?>">
        <div class="c-head">
          <img class="c-favicon" src="https://www.google.com/s2/favicons?domain=<?= $host ?>&sz=32"
               alt="" onerror="this.style.display='none'">
          <span class="c-url"><?= $url ?></span>
          <span class="c-badge <?= $cls ?>"><i class="bi <?= $ico ?>"></i><?= $lbl ?></span>
        </div>

        <?php if (!$c["valid"]): ?>
          <div class="c-err">
            <i class="bi bi-wifi-off fs-5"></i>
            <span>Servidor inacessível ou certificado inválido</span>
          </div>
        <?php else:
          $days = $c["daysRemaining"];
          $pct  = $cls==="valid" ? min(100,round($days/365*100)) : ($cls==="expiring" ? round($days/40*100) : 0);
          $dLbl = $cls==="expired" ? "Expirado" : "{$days} dias restantes";
        ?>
          <div class="c-info">
            <div class="c-cell"><div class="c-lbl">Domínio</div><div class="c-val"><?= htmlspecialchars($c["subject"]) ?></div></div>
            <div class="c-cell"><div class="c-lbl">Emissor</div><div class="c-val"><?= htmlspecialchars($c["issuer"]) ?></div></div>
            <div class="c-cell"><div class="c-lbl">Emitido em</div><div class="c-val"><?= formatDate($c["validFrom"]) ?></div></div>
            <div class="c-cell"><div class="c-lbl">Expira em</div><div class="c-val"><?= formatDate($c["validTo"]) ?></div></div>
            <div class="c-cell"><div class="c-lbl">Chave</div><div class="c-val"><?= htmlspecialchars($c["keyType"]) ?> <?= $c["keyBits"] ?> bits</div></div>
            <div class="c-cell"><div class="c-lbl">Algoritmo</div><div class="c-val"><?= htmlspecialchars($c["sigAlgo"]) ?></div></div>
          </div>
          <div class="c-prog">
            <div class="c-pmeta">
              <span><i class="bi bi-clock me-1"></i>Validade</span>
              <span class="c-pdays <?= $cls ?>"><?= $dLbl ?></span>
            </div>
            <div class="c-track"><div class="c-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div></div>
          </div>

          <?php $uid = 'd' . $i; ?>
          <button class="c-toggle" onclick="toggleDetails(this,'<?= $uid ?>')">
            <i class="bi bi-cpu me-1"></i>Detalhes técnicos
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="c-details" id="<?= $uid ?>">
            <div class="c-drow"><span class="c-dlbl">SANs</span><span class="c-dval"><?= htmlspecialchars($c["sans"] ?: '—') ?></span></div>
            <div class="c-drow"><span class="c-dlbl">Série (hex)</span><span class="c-dval mono"><?= htmlspecialchars($c["serial"]) ?></span></div>
            <div class="c-drow"><span class="c-dlbl">Fingerprint</span><span class="c-dval mono"><?= htmlspecialchars(substr($c["fingerprint"],0,47)) ?>…</span></div>
            <div class="c-drow"><span class="c-dlbl">OCSP</span><span class="c-dval"><?= htmlspecialchars($c["ocsp"]) ?></span></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="empty-hint d-none" id="empt">
    <i class="bi bi-binoculars"></i>
    <p class="mt-3 fs-6">Nenhum certificado encontrado.</p>
  </div>
</div>

<footer>
  <i class="bi bi-code-slash me-1" style="color:#818cf8"></i>
  Desenvolvido por <a href="https://linkedin.com/in/levilucena" target="_blank">Levi Lucena</a>
  &nbsp;·&nbsp; <?= $total ?> certificados verificados às <?= date("H:i:s") ?>
</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Countdown
  let s=300, el=document.getElementById('countdown');
  setInterval(()=>{
    s--;
    const display = Math.max(s, 0);
    el.textContent=`${Math.floor(display/60)}:${String(display%60).padStart(2,'0')}`;
    if(s<=0) location.reload();
  },1000);

  // Filter + search
  const cols=document.querySelectorAll('.cert-col');
  const pills=document.querySelectorAll('.pill');
  const srch=document.getElementById('srch');
  const empt=document.getElementById('empt');

  function applyFilter(){
    const f=document.querySelector('.pill[class*=" p-"]')?.dataset.f||'all';
    const q=srch.value.toLowerCase();
    let n=0;
    cols.forEach(c=>{
      const ok=(f==='all'||c.dataset.s===f)&&c.dataset.u.includes(q);
      c.style.display=ok?'':'none'; if(ok)n++;
    });
    empt.classList.toggle('d-none',n>0);
  }
  pills.forEach(p=>p.addEventListener('click',()=>{
    pills.forEach(x=>x.className='pill');
    const map={all:'p-all',expired:'p-exp',expiring:'p-warn',valid:'p-ok'};
    p.classList.add(map[p.dataset.f]);
    applyFilter();
  }));
  srch.addEventListener('input',applyFilter);

  function toggleDetails(btn, id) {
    const panel = document.getElementById(id);
    const open  = panel.classList.toggle('show');
    btn.classList.toggle('open', open);
  }

  function openGraph(e){
    e.preventDefault();
    const w=1200,h=820;
    window.open(e.currentTarget.href,'_blank',`width=${w},height=${h},left=${(screen.width-w)/2},top=${(screen.height-h)/2}`);
  }
</script>
</body>
</html>
