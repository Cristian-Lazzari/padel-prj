@extends('layouts.base')

@section('contents')
@php
    $role = ['admin' => 'Amministratore', 'trainer' => 'Istruttore'];
    $i = 0; 
    $currentDay = date("d");
    $currentMonth = date("m");
    $currentYear = date("Y");
@endphp
<div class="page_nav">
    <h1> <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-calendar2-week mx-3" viewBox="0 0 16 16">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5zM11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
        </svg> CALENDARIO</h1>
    <div id="carouselExampleIndicators" class="carousel slide my_carousel" >
        <div class="carousel-indicators">
            @foreach ($year as $m)
                <button  type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                    class="active" aria-current="true" 
                @endif
                aria-label="{{ 'Slide ' . $i }}"></button>
                @php $i ++ @endphp
            @endforeach
        </div>
        <div id="calendar" class="carousel-inner">
            @php $i = 0; @endphp
            @foreach ($year as $m)
                <div class="carousel-item
                @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                    active 
                @endif
                ">
                    
                    <h2 class="my">{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                    <div class="calendar-c">
                    
                        <div class="c-name">
                            @php
                            $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                            @endphp
                            @foreach ($day_name as $item)
                                <h4>{{$item}}</h4>
                            @endforeach
                        </div>
                        <div class="calendar">

                            @foreach ($m['days'] as $d)
                                <button data-day='@json($d)'
                                class="day  
                                @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif " 
                                style="grid-column-start:{{$d['day_w'] }}" method="get">        
                                    <p class="p_day">{{$d['day']}}</p>
                                    @if ($d['reserved'] > 0)
                                        <span class="bookings">{{$d['reserved']}} 
                                            <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="bi bi-dice-4" viewBox="0 0 16 16">
                                                <path d="M13 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2zM3 0a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V3a3 3 0 0 0-3-3z"/>
                                                <path d="M5.5 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m8 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0 8a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m-8 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                            </svg>
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php $i ++ @endphp
            @endforeach
        </div>
        <button class="carousel-control-prev" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <div class="lez-c prev">
                <div class="line"></div>
                <div class="line l2"></div>
            </div>
        </button>
        <button class="carousel-control-next" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <div class="lez-c ">
                <div class="line"></div>
                <div class="line l2"></div>
            </div>
        </button>
    </div>

    <!-- Qui appariranno le fasce orarie -->
    <div id="slots" class="my-3"></div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll("#calendar button");
    const slotsContainer = document.getElementById("slots");
    fieldname = {
        field_1 : 'Campo 1',
        field_2 : 'Campo 2',
        field_3 : 'Campo 3',
    }

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(e => {
                e.classList.remove('selected')
            });
            const day = JSON.parse(btn.dataset.day);
            btn.classList.toggle('selected')
            // Pulisco il contenitore
            slotsContainer.innerHTML = `
                <div class="fields" id="fields"></div>
            `;

            const fieldsContainer = document.getElementById("fields");

            // Ciclo i campi
            Object.entries(day.fields).forEach(([fieldName, slots]) => {
                let html = `<h4 class="font-bold mb-2">${fieldname[fieldName]}</h4>
                            <div class="field">`;
                
                if (slots.length > 0) {

                    slots.forEach(slot => { 
                        html += `<div class="${!slot.status ? 'time' : 'time booked'}"> `
                        if(slot.status){
                            html += `<a href="/admin/reservations/${slot.id}" > <span class="time_b">${slot.time}</span>`;
                            html += `<span class="booking_subject">#${slot.booking_subject}</span>`;
                            html += `</a>`;
                        }else{
                            html += `<p>${slot.time}</p>`;
                        }
                        html += `</div>`;
                    });

                } else {
                    html += `<p class="text-gray-500">Nessuna fascia oraria</p>`;
                }

                html += "</div>";
                fieldsContainer.insertAdjacentHTML("beforeend", html);
            });
        });
    });
});
</script>



@endsection

