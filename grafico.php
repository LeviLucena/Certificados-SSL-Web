<!DOCTYPE html>
<!-- ====================================================================== -->
<!-- Desenvolvido por Levi Lucena - linkedin.com/in/levilucena -->
<!-- ====================================================================== -->
<html>
<head>
    <title>Dashboard de Certificados SSL</title>
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

        .chart-container {
            width: 500px;
            height: 500px;
            margin: 0 10px;
            position: relative;
            display: inline-block;
        }

        .container {
            display: none;
        }

        .chart-legend {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .legend-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 15px;
        }

        .legend-title {
            margin-bottom: 5px;
        }

        .legend-color {
            width: 10px;
            height: 10px;
            margin-right: 5px;
        }

        .legend-urls {
            white-space: pre-line;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>
<body>
    <?php require_once('index3.php'); ?>

    <div class="chart-container">
        <canvas id="certificateChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="pieChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="lineChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="scatterChart"></canvas>
    </div>

    <div class="chart-legend">
    <div class="legend-item">
        <div class="legend-color" style="background-color: red;"></div>
        <div class="legend-title">Expirados ou inválidos:</div>
        <div class="legend-urls"></div>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background-color: orange;"></div>
        <div class="legend-title">Expirando:</div>
        <div class="legend-urls"></div>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background-color: green;"></div>
        <div class="legend-title">Válidos:</div>
        <div class="legend-urls"></div>
    </div>
</div>


    <script>
        const certificates = <?php echo json_encode($certificates); ?>;

        let expiredCount = 0;
        let expiringCount = 0;
        let validCount = 0;

        const expiredUrls = [];
        const expiringUrls = [];
        const validUrls = [];

        certificates.forEach((certificate) => {
            if (certificate.validityClass === 'expired') {
                expiredCount++;
                expiredUrls.push(certificate.url);
            } else if (certificate.validityClass === 'expiring') {
                expiringCount++;
                expiringUrls.push(certificate.url);
            } else if (certificate.validityClass === 'valid') {
                validCount++;
                validUrls.push(certificate.url);
            }
        });

        const ctx1 = document.getElementById('certificateChart').getContext('2d');
        const chart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Expirados ou inválidos', 'Expirando', 'Válidos'],
                datasets: [{
                    label: 'Quantidade de Certificados',
                    data: [expiredCount, expiringCount, validCount],
                    backgroundColor: ['red', 'orange', 'green'],
                    borderColor: ['red', 'orange', 'green'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }
            }
        });

        const ctx2 = document.getElementById('pieChart').getContext('2d');
        const chart2 = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Expirados ou inválidos', 'Expirando', 'Válidos'],
                datasets: [{
                    label: 'Quantidade de Certificados',
                    data: [expiredCount, expiringCount, validCount],
                    backgroundColor: ['red', 'orange', 'green'],
                    borderColor: ['red', 'orange', 'green'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }
            }
        });

        const ctx3 = document.getElementById('lineChart').getContext('2d');
        const chart3 = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: ['Expirados ou inválidos', 'Expirando', 'Válidos'],
                datasets: [{
                    label: 'Quantidade de Certificados',
                    data: [expiredCount, expiringCount, validCount],
                    backgroundColor: 'blue',
                    borderColor: 'blue',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }
            }
        });

        const ctx4 = document.getElementById('scatterChart').getContext('2d');
        const chart4 = new Chart(ctx4, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Quantidade de Certificados',
                    data: [
                        { x: expiredCount, y: expiringCount },
                        { x: expiringCount, y: validCount },
                        { x: validCount, y: expiredCount }
                    ],
                    backgroundColor: 'purple',
                    borderColor: 'purple',
                    borderWidth: 1,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom'
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }
            }
        });

        const legendUrls = document.querySelectorAll('.legend-urls');
        legendUrls[0].innerText = expiredUrls.join('\n');
        legendUrls[1].innerText = expiringUrls.join('\n');
        legendUrls[2].innerText = validUrls.join('\n');

        const containers = document.querySelectorAll('.container');
        containers.forEach((container) => {
            container.style.display = 'none';
        });
    </script>


</body>
</html>
               
