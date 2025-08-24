
@extends('layouts.base')

@section('contents')
@vite(['resources/js/app.js'])

    <div class="stat">
        <h1>Statistiche</h1>

        <h2>Prodotti più ordinati</h2>
        <div class="chart">
            <canvas class="graph" id="topProductsChart"></canvas>
            <div class="list">
                <table class=" table table mytable"> 
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Prodotto</th>
                            <th scope="col">Quantità</th>
                        </tr>
                    </thead>
                    @php $index = 1 @endphp
                    @foreach ($topProducts as $key => $value)
                    <tr>
                        <td>{{$index}}</td>
                        <td>{{$key}}</td>
                        <td><strong>{{$value}}</strong></td>
                    </tr>
                    @php $index ++ @endphp
                    @endforeach
                </table>
            </div>
        </div>

        <h2>Come vengono ordinati i prodotti nel tempo</h2>
        <div class="chart">
            <canvas class="graph" id="ordersOverTimeChart"></canvas>
        </div>

        <h2>Ricavi nel tempo da ordini</h2>
        <div class="chart">
            <canvas class="graph" id="revenueOverTimeChart"></canvas>
        </div>

        <h2>Prenotazioni ai tavoli nel tempo</h2>
        <div class="chart">
            <canvas class="graph" id="reservationChart"></canvas>

            <div class="list">
                <table class="mytable table table-striped "> 
                    <thead>
                        <tr>
                            
                            <th scope="col">Data</th>
                            <th scope="col">Adulti</th>
                            <th scope="col">Bambini</th>
                        </tr>
                    </thead>
                    @foreach ($reservations as $key => $value)
                    <tr>
                        
                        <td>{{$value->date}}</td>
                        <td>{{$value->adults}}</td>
                        <td>{{$value->children}}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#66FF66', '#FF6633', '#0099FF', '#CC33FF',
            '#FF6699', '#66CCFF', '#CCFF33', '#FF9933', '#CC66CC',
            '#FFCC66', '#3366FF', '#33CC99', '#FF6666', '#66FFCC'
        ];


        // Grafico a torta per i prodotti più ordinati
        const topProductsData = @json($topProducts);
        new Chart(document.getElementById('topProductsChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(topProductsData),
                datasets: [{
                    data: Object.values(topProductsData),
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
            }
        });
        // grafico colonne prodotti nel tempo
        const labels = @json($labels);  // Assicurati che anche $labels venga passato correttamente
        const datasets = @json($datasets);  // Dati del grafico

        // Funzione per generare un array di colori ripetuto se necessario
        const getColors = (numberOfColorsNeeded, colors) => {
            const repeatedColors = [];
            for (let i = 0; i < numberOfColorsNeeded; i++) {
                repeatedColors.push(colors[i % colors.length]);  // Usa l'operatore modulo per ripetere i colori
            }
            return repeatedColors;
        };

        // Ottieni un array di colori sufficienti per tutti i dataset
        const colorsForDatasets = getColors(datasets.length, colors);

        // Crea un nuovo array di datasets con i colori associati
        const datasetsWithColors = datasets.map((dataset, index) => {
            return {
                label: dataset.label,  // Etichetta del dataset
                data: dataset.data,    // I dati numerici
                backgroundColor: colorsForDatasets[index]  // Colore corrispondente dal nuovo array di colori
            };
        });

        // Ora, puoi usare datasetsWithColors nel grafico
        const ctx_o_t = document.getElementById('ordersOverTimeChart').getContext('2d');

        const chart_o_t = new Chart(ctx_o_t, {
            type: "bar",
            data: {
                labels: labels,
                datasets: datasetsWithColors  // Usa il nuovo array con i colori associati
            },
            options: {
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            filter: (legendItem, chartData) => {
                                const totals = chartData.datasets.map(dataset => ({
                                    label: dataset.label,
                                    total: dataset.data.reduce((sum, value) => sum + value, 0),
                                }));
                                totals.sort((a, b) => b.total - a.total);
                                const top10 = totals.slice(0, 20).map(item => item.label);  // Cambiato 10 a 20 per visualizzare i primi 20
                                return top10.includes(legendItem.text);
                            }
                        }
                    }
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });


        // Grafico a linee per i ricavi nel tempo
        const revenueOverTime = @json($revenueOverTime);
        const chart_order = document.getElementById('revenueOverTimeChart').getContext('2d');
        const chart_or = new Chart(chart_order, {
            type: 'line',
            data: {
            datasets: [
                {
                label: 'Totale',
                data: revenueOverTime.tot, // Dati per ordini pagati
                borderColor: 'rgba(145, 220, 224, 1)',
                backgroundColor: 'rgba(145, 220, 224, .2)',
                fill: true,
                },
                {
                label: 'Pagati',
                data: revenueOverTime.paid, // Dati per ordini pagati
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                },
                {
                label: 'Pagati alla consegna',
                data: revenueOverTime.cod, // Dati per ordini pagati alla consegna
                borderColor: 'rgba(255, 206, 86, 1)',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                fill: true,
                },
                {
                label: 'Annullati',
                data: revenueOverTime.canceled, // Dati per ordini annullati
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true,
                },
            ],
            },
            options: {
            responsive: true,
            scales: {
                x: {
                    type: 'time', // Asse temporale
                    time: {
                        unit: 'day', // Mostra i dati per giorno
                    },
                },
                y: {
                title: {
                    display: true,
                    text: 'Totale (€)',
                },
                },
            },
            },
        });
        //ultimo grafico
        const data = @json($reservations);

            // Estrai le date, adulti e bambini
            const label = data.map(item => item.date); // Array di date (formato ISO)
            const adults = data.map(item => item.adults);
            const children = data.map(item => item.children);

            // Configurazione del grafico
            const ctx = document.getElementById('reservationChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: label,
                    datasets: [
                        {
                            label: 'Adulti',
                            data: adults,
                            stack: 'stack',
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Bambini',
                            data: children,
                            stack: 'stack',
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time', // Configura l'asse X come asse temporale
                            time: {
                                unit: 'day', // Mostra un intervallo basato sui giorni
                                tooltipFormat: 'dd-MM-yyyy', // Formato per il tooltip
                                displayFormats: {
                                    day: 'MM-dd' // Formato per le etichette
                                },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Numero di persone'
                            },
                            stacked: true // Abilita lo stacking
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Prenotazioni giornaliere'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
    });
</script>


