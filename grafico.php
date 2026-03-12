<!DOCTYPE html>
<!-- ====================================================================== -->
<!-- Desenvolvido por Levi Lucena - linkedin.com/in/levilucena -->
<!-- ====================================================================== -->
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard SSL</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',system-ui,sans-serif;background:#050b18;color:#e2e8f0;overflow-x:hidden}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:
        radial-gradient(ellipse 55% 45% at 5% 15%,rgba(99,102,241,.12) 0%,transparent 65%),
        radial-gradient(ellipse 40% 35% at 90% 5%,rgba(139,92,246,.09) 0%,transparent 65%),
        radial-gradient(ellipse 35% 45% at 60% 90%,rgba(34,197,94,.06) 0%,transparent 65%)}
    .z1{position:relative;z-index:1}

    /* Header */
    header{padding:16px 36px;border-bottom:1px solid rgba(255,255,255,.06);
      background:rgba(5,11,24,.85);backdrop-filter:blur(16px);display:flex;align-items:center;gap:16px;flex-wrap:wrap}
    .brand-logo{width:42px;height:42px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;
      display:flex;align-items:center;justify-content:center;font-size:1.2rem;box-shadow:0 0 22px rgba(99,102,241,.4)}
    .brand-name{font-size:1.2rem;font-weight:900;color:#e2e8f0}
    .brand-sub{font-size:.72rem;color:#475569;margin-top:1px}
    .btn-glass{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#cbd5e1;
      border-radius:10px;font-size:.82rem;font-weight:500;padding:8px 16px;cursor:pointer;transition:all .2s;
      text-decoration:none;display:inline-flex;align-items:center;gap:5px}
    .btn-glass:hover{background:rgba(255,255,255,.1);color:#fff}
    .btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff;border-radius:10px;
      font-size:.82rem;font-weight:600;padding:8px 18px;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3);
      display:inline-flex;align-items:center;gap:5px;transition:all .2s}
    .btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.4);color:#fff}

    /* Mini stats */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:24px 36px 0}
    .mini-stat{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:14px;
      padding:16px 18px;display:flex;align-items:center;gap:12px;transition:all .2s}
    .mini-stat:hover{transform:translateY(-2px);border-color:rgba(255,255,255,.12)}
    .mi-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
    .mi-num{font-size:1.7rem;font-weight:900;line-height:1}
    .mi-lbl{font-size:.71rem;color:#64748b;margin-top:2px}

    /* Chart cards */
    .chart-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:22px 24px;height:100%}
    .chart-ttl{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#475569;
      margin-bottom:16px;display:flex;align-items:center;gap:7px}
    .chart-ttl i{font-size:1rem}

    /* Divider section label */
    .sec-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#334155;
      display:flex;align-items:center;gap:10px;margin:24px 36px 0}
    .sec-label::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06)}

    /* Legend cards */
    .leg-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:18px 20px;height:100%}
    .leg-title{font-size:.8rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .leg-dot{width:10px;height:10px;border-radius:50%}
    .leg-count{padding:1px 8px;border-radius:10px;font-size:.7rem;font-weight:700;margin-left:auto}
    .leg-url{font-size:.76rem;color:#64748b;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.05);
      display:flex;align-items:center;gap:6px;word-break:break-all}
    .leg-url:last-child{border-bottom:none}
    .leg-empty{font-size:.76rem;color:#22c55e;padding:5px 0}

    footer{border-top:1px solid rgba(255,255,255,.05);padding:14px 36px;font-size:.75rem;color:#334155;text-align:center;margin-top:8px}
    footer a{color:#818cf8;text-decoration:none}

    @media(max-width:900px){
      header,.stats-row,.sec-label,footer{padding-left:16px;padding-right:16px}
      .stats-row{grid-template-columns:repeat(2,1fr)}
      .px-chart{padding:0 16px}
    }
  </style>
</head>
<body>
<?php
ob_start();
require_once('index.php');
ob_end_clean();

// Expiry by next 12 months
$monthLabels = [];
$monthData   = array_fill(0, 12, 0);
for ($i = 0; $i < 12; $i++) {
    $monthLabels[] = iconv('UTF-8','UTF-8//IGNORE', date('M/y', mktime(0,0,0, date('n')+$i, 1)));
}
foreach ($certificates as $c) {
    if ($c['valid'] && $c['validTo'] > time()) {
        $diff = round(($c['validTo'] - time()) / (30.44*86400));
        if ($diff >= 0 && $diff < 12) $monthData[(int)$diff]++;
    }
}
?>
<div class="z1">

<!-- Header -->
<header>
  <div class="brand-logo">📊</div>
  <div>
    <div class="brand-name">Dashboard SSL</div>
    <div class="brand-sub">Análise de certificados · <?= date("d/m/Y H:i") ?> · <?= $total ?> sites</div>
  </div>
  <div class="d-flex gap-2 ms-auto flex-wrap">
    <a href="index.php" class="btn-glass"><i class="bi bi-arrow-left"></i>Voltar</a>
    <button class="btn-p" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i>Atualizar</button>
  </div>
</header>

<!-- Mini stats -->
<div class="stats-row">
  <div class="mini-stat">
    <div class="mi-icon"><i class="bi bi-globe2" style="color:#818cf8"></i></div>
    <div><div class="mi-num" style="color:#818cf8"><?= $total ?></div><div class="mi-lbl">Total</div></div>
  </div>
  <div class="mini-stat">
    <div class="mi-icon"><i class="bi bi-shield-x" style="color:#f87171"></i></div>
    <div><div class="mi-num" style="color:#f87171"><?= $expired ?></div><div class="mi-lbl">Expirados</div></div>
  </div>
  <div class="mini-stat">
    <div class="mi-icon"><i class="bi bi-exclamation-triangle" style="color:#fbbf24"></i></div>
    <div><div class="mi-num" style="color:#fbbf24"><?= $expiring ?></div><div class="mi-lbl">Expirando</div></div>
  </div>
  <div class="mini-stat">
    <div class="mi-icon"><i class="bi bi-shield-check" style="color:#4ade80"></i></div>
    <div><div class="mi-num" style="color:#4ade80"><?= $valid ?></div><div class="mi-lbl">Válidos</div></div>
  </div>
</div>

<!-- Charts row 1 -->
<div class="sec-label"><i class="bi bi-graph-up me-1"></i>Visão Geral</div>
<div class="row g-3 px-chart mt-0 px-3" style="padding:0 36px;margin-top:16px!important">
  <div class="col-md-7">
    <div class="chart-card">
      <div class="chart-ttl"><i class="bi bi-bar-chart-fill" style="color:#818cf8"></i>Distribuição por Status</div>
      <div style="position:relative;height:280px"><canvas id="barChart"></canvas></div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="chart-card">
      <div class="chart-ttl"><i class="bi bi-pie-chart-fill" style="color:#fbbf24"></i>Proporção</div>
      <div style="position:relative;height:280px"><canvas id="donutChart"></canvas></div>
    </div>
  </div>
</div>

<!-- Charts row 2 -->
<div class="sec-label mt-3"><i class="bi bi-clock-history me-1"></i>Validade</div>
<div class="row g-3 px-3 mt-0" style="padding:0 36px;margin-top:12px!important">
  <div class="col-md-8">
    <div class="chart-card">
      <div class="chart-ttl"><i class="bi bi-bar-chart-steps" style="color:#4ade80"></i>Dias restantes por certificado</div>
      <?php $vc = array_values(array_filter($certificates, fn($c)=>$c['valid'])); ?>
      <div style="position:relative;height:<?= max(200, count($vc)*34) ?>px"><canvas id="daysChart"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="chart-card">
      <div class="chart-ttl"><i class="bi bi-calendar3" style="color:#38bdf8"></i>Expirações nos próximos 12 meses</div>
      <div style="position:relative;height:<?= max(200, count($vc)*34) ?>px"><canvas id="monthChart"></canvas></div>
    </div>
  </div>
</div>

<!-- Legend row -->
<div class="sec-label mt-3"><i class="bi bi-list-ul me-1"></i>Detalhamento</div>
<div class="row g-3 px-3 pb-4" style="padding:0 36px;padding-bottom:36px!important;margin-top:12px!important">
  <?php
  $groups = [
    ["expired",  "#ef4444","#f87171","rgba(239,68,68,.12)","bi-x-circle","Expirados / Inválidos",  $expired],
    ["expiring", "#f59e0b","#fbbf24","rgba(245,158,11,.12)","bi-hourglass-split","Expirando em breve",$expiring],
    ["valid",    "#22c55e","#4ade80","rgba(34,197,94,.12)","bi-shield-check","Válidos",             $valid],
  ];
  foreach ($groups as [$cls, $dotColor, $txtColor, $bgCount, $icon, $label, $count]):
  ?>
  <div class="col-md-4">
    <div class="leg-card">
      <div class="leg-title">
        <span class="leg-dot" style="background:<?= $dotColor ?>;box-shadow:0 0 8px <?= $dotColor ?>88"></span>
        <i class="bi <?= $icon ?>" style="color:<?= $txtColor ?>"></i>
        <span style="color:<?= $txtColor ?>"><?= $label ?></span>
        <span class="leg-count" style="background:<?= $bgCount ?>;color:<?= $txtColor ?>"><?= $count ?></span>
      </div>
      <?php
      $items = array_filter($certificates, fn($c)=>$c['validityClass']===$cls);
      if (empty($items)): ?>
        <div class="leg-empty"><i class="bi bi-check2-circle me-1"></i>Nenhum nesta categoria</div>
      <?php else: foreach ($items as $c): ?>
        <div class="leg-url">
          <img src="https://www.google.com/s2/favicons?domain=<?= parse_url($c['url'],PHP_URL_HOST) ?>&sz=16"
               width="14" height="14" style="border-radius:3px;flex-shrink:0" alt="" onerror="this.style.display='none'">
          <?= htmlspecialchars($c['url']) ?>
          <?php if($c['valid'] && isset($c['daysRemaining'])): ?>
            <span style="margin-left:auto;white-space:nowrap;font-size:.7rem;color:<?= $txtColor ?>;flex-shrink:0"><?= $c['daysRemaining'] ?>d</span>
          <?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<footer>
  <i class="bi bi-code-slash me-1" style="color:#818cf8"></i>
  Desenvolvido por <a href="https://linkedin.com/in/levilucena" target="_blank">Levi Lucena</a>
  &nbsp;·&nbsp; Atualizado em <?= date("d/m/Y \à\s H:i:s") ?>
</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
Chart.register(ChartDataLabels);

const C = {expired:'#ef4444',expiring:'#f59e0b',valid:'#22c55e'};
const gridColor = 'rgba(255,255,255,.05)';
const tickColor = '#475569';
const dlBase = {color:'#e2e8f0',font:{weight:'bold',size:11},formatter:v=>v>0?v:''};

// ── Bar chart ──
new Chart(document.getElementById('barChart'),{
  type:'bar',
  data:{
    labels:['Expirados','Expirando','Válidos'],
    datasets:[{
      data:[<?= $expired ?>,<?= $expiring ?>,<?= $valid ?>],
      backgroundColor:['rgba(239,68,68,.2)','rgba(245,158,11,.2)','rgba(34,197,94,.2)'],
      borderColor:[C.expired,C.expiring,C.valid],
      borderWidth:2, borderRadius:10, borderSkipped:false
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,
    scales:{
      x:{grid:{color:gridColor},ticks:{color:tickColor}},
      y:{beginAtZero:true,ticks:{precision:0,color:tickColor},grid:{color:gridColor}}
    },
    plugins:{legend:{display:false},datalabels:{...dlBase,anchor:'end',align:'top'}}
  }
});

// ── Doughnut ──
new Chart(document.getElementById('donutChart'),{
  type:'doughnut',
  data:{
    labels:['Expirados','Expirando','Válidos'],
    datasets:[{
      data:[<?= $expired ?>,<?= $expiring ?>,<?= $valid ?>],
      backgroundColor:['rgba(239,68,68,.35)','rgba(245,158,11,.35)','rgba(34,197,94,.35)'],
      borderColor:[C.expired,C.expiring,C.valid],
      borderWidth:2, hoverOffset:10
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,cutout:'58%',
    plugins:{
      legend:{position:'bottom',labels:{color:'#94a3b8',padding:14,font:{size:12}}},
      datalabels:{...dlBase,anchor:'center',align:'center',
        formatter:(v,ctx)=>v>0?`${ctx.chart.data.labels[ctx.dataIndex]}\n${v}`:''}
    }
  }
});

// ── Days remaining ──
const certs = <?= json_encode(array_values(array_map(fn($c)=>[
  'url'           => $c['url'],
  'validityClass' => $c['validityClass'],
  'valid'         => $c['valid'],
  'daysRemaining' => $c['daysRemaining'] ?? 0,
], array_filter($certificates, fn($c)=>$c['valid'])))) ?>;

const sorted = [...certs].sort((a,b)=>a.daysRemaining-b.daysRemaining);

new Chart(document.getElementById('daysChart'),{
  type:'bar',
  data:{
    labels:sorted.map(c=>{ try{return new URL(c.url).hostname}catch(e){return c.url} }),
    datasets:[{
      label:'Dias',
      data:sorted.map(c=>c.daysRemaining),
      backgroundColor:sorted.map(c=>C[c.validityClass]+'33'),
      borderColor:sorted.map(c=>C[c.validityClass]),
      borderWidth:2, borderRadius:6
    }]
  },
  options:{
    indexAxis:'y',responsive:true,maintainAspectRatio:false,
    scales:{
      x:{beginAtZero:true,ticks:{precision:0,color:tickColor},grid:{color:gridColor}},
      y:{ticks:{color:'#94a3b8',font:{size:11}},grid:{color:gridColor}}
    },
    plugins:{
      legend:{display:false},
      datalabels:{...dlBase,anchor:'end',align:'right',formatter:v=>`${v}d`}
    }
  }
});

// ── Expiry by month ──
const monthLabels = <?= json_encode($monthLabels) ?>;
const monthData   = <?= json_encode(array_values($monthData)) ?>;
const monthColors = monthData.map(v=>v===0?'rgba(255,255,255,.06)':v>2?'rgba(239,68,68,.35)':'rgba(56,189,248,.35)');
const monthBorder = monthData.map(v=>v===0?'rgba(255,255,255,.1)':v>2?'#ef4444':'#38bdf8');

new Chart(document.getElementById('monthChart'),{
  type:'bar',
  data:{
    labels:monthLabels,
    datasets:[{
      label:'Expirações',
      data:monthData,
      backgroundColor:monthColors,
      borderColor:monthBorder,
      borderWidth:2, borderRadius:8
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,
    scales:{
      x:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:10}}},
      y:{beginAtZero:true,ticks:{precision:0,color:tickColor},grid:{color:gridColor}}
    },
    plugins:{
      legend:{display:false},
      datalabels:{...dlBase,anchor:'end',align:'top',formatter:v=>v>0?v:''}
    }
  }
});
</script>
</body>
</html>
