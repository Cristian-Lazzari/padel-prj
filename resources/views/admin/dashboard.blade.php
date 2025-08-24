@extends('layouts.base')

@section('contents')
@php
    $pack = ['NON attivo', 'Essentials * 360', 'Work on * 360', 'Boost up * 360', 'Essentials * 30', 'Work on * 30', 'Boost up * 30' ];
    $role = ['Propr. o Socio', 'Dipendente', 'SMM' ];
@endphp
<div class="page_nav">
    <div class="m-5">

        <a class="my_btn_7  w-auto m-auto" href="{{route('admin.mailer.index')}}"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4.5a.5.5 0 0 1-1 0V5.383l-7 4.2-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h5.5a.5.5 0 0 1 0 1H2a2 2 0 0 1-2-1.99zm1 7.105 4.708-2.897L1 5.383zM1 4v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                </svg> 
            <span>Email Marketing</span>
        </a>
    </div>
    <div class="mydash">
        <div class="tables">
            <div class="my-table">
                <div class="head">
                    <p>Cliente</p>
                    <p class="ex-mb">rinnovo</p>
                    <p class="ex-mb">Pacchetto</p>
                    <p>Dettagli</p>
                </div>
                <div class="body">
                    @foreach ($consumers as $c)
                        <div class="client @if (!$c->active) opacity-50 @endif">
                            @php
                                $domain = $c->domain ? parse_url(json_decode($c->domain, 1)['domain'], PHP_URL_HOST) : '';
                            @endphp
                            <p class="name">
                                <img src="{{'https://' . $domain . '/img/favicon.png'}}" alt="">
                                <span>{{$c->activity_name}}</span>
                            </p>
                            <p class="date ex-mb">{{$c->created_at}}</p>
                            <p class="pack ex-mb">{{$pack[$c->status]}}</p>
                            <p class="link">
                                <a class="pack dt" href="{{route('admin.consumers.show', $c->id)}}"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                        <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                    </svg>
                                </a>
                                <a href="{{'https://' . $domain}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
                                        <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
                                    </svg>
                                </a>
                                <a class="pack" href="{{'https://db.'.$domain }}"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-window-dash" viewBox="0 0 16 16">
                                        <path d="M2.5 5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M4 5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m2-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                        <path d="M0 4a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v4a.5.5 0 0 1-1 0V7H1v5a1 1 0 0 0 1 1h5.5a.5.5 0 0 1 0 1H2a2 2 0 0 1-2-2zm1 2h13V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1z"/>
                                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-5.5 0a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5"/>
                                    </svg>
                                </a>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="my-table user">
                <div class="head">
                    <p>Nome</p>
                    <p class="ex-mb">Ruolo</p>
                    <p class="">Locali</p>
                    <p>Info</p>
                </div>
                <div class="body">
                    @foreach ($users as $c)
                        @if ($c->role == 'admin')
                            <div class="client">
                                <p class="name">{{$c->name}} {{$c->surname}}</span></p>
                                <p class="date ex-mb">{{$role[$c->role_agency]}}</p>
                                <p class="pack"></p>
                                <p class="link">
                                    <a class="dt" href="{{route('admin.consumers.show', $c->id)}}"> 
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        </svg>
                                    </a>
                                </p>
                            </div>
                        @endif
                    @endforeach
                    @foreach ($users as $c)
                        @if ($c->role == 'client')
                            <div class="client">
                                <p class="name">{{$c->name}} {{$c->surname}}</span></p>
                                <p class="date ex-mb">{{$role[$c->role_agency]}}</p>
                                <p class="pack">
                                    @foreach ($c->consumers as $item)
                                        @php
                                            $domain = $item->domain ? parse_url(json_decode($item->domain, 1)['domain'], PHP_URL_HOST) : '';
                                        @endphp
                                        <img src="{{'https://' . $domain . '/img/favicon.png'}}" alt="">
                                    @endforeach
                                </p>
                                <p class="link">
                                    <a class="dt" href="{{route('admin.consumers.show', $c->id)}}"> 
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        </svg>
                                    </a>
                                </p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
        <div class="chart-cont">
            <canvas class="chart " id="statusChart"></canvas>
            <div class="result">
                @php
                    $subscriptions_chart = json_decode($subscriptions_chart, 1);
                    
                @endphp
                @foreach ($statusCounts as $key => $value)
                    <div class="r">
                        <h3>{{$value}}</h3>
                        <p>{{$key}}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="chart-cont large-c">
            <canvas class="chart" id="subscriptionsChart"></canvas>
        </div>
        
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = {!! json_encode(array_keys($statusCounts)) !!};
        const data = {!! json_encode(array_values($statusCounts)) !!};

        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#10b7934f', '#10b7937b', '#10b793', '#090333'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        // display: false,
                        position: 'top', // Sposta la legenda sotto il grafico
                    }
                }
            }
        });

        
        const data_s = {!! json_encode(($subscriptions_chart)) !!};
        
        var ctx_1 = document.getElementById('subscriptionsChart').getContext('2d');
        var subscriptionsChart = new Chart(ctx_1, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Essentials',
                        data: data.essentials,
                        borderColor: '#10b7934f',
                        backgroundColor: '#10b7934f',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Work On',
                        data: data.WorkOn,
                        borderColor: '#10b7937b',
                        backgroundColor: '#10b7937b',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Boost Up',
                        data: data.BoostUp,
                        borderColor: '#10b793',
                        backgroundColor: '#10b793',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: false,
                            text: 'Data'
                        }
                    },
                    y: {
                        title: {
                            display: false,
                            text: 'Numero di sottoscrizioni'
                        },

                    }
                }
            }
        });
    });


</script>




@endsection

