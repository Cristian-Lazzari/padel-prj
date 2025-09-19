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
    @if (session('message'))
    @php
        $message = session('message');
    @endphp
    <div class="alert-cont">
        <div class="alert alert-dismissible fade show notify_success" role="alert">
            {{$message}}
            <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    @if (session('error'))
    @php
        $error = session('error');
    @endphp
    <div class="alert-cont">
        <div class="alert alert-dismissible fade show notify_success error" role="alert">
            {{$error}}
            <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
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


<!-- Button trigger modal -->
    <form  action="{{ route('admin.reservations.createFromD')}}"   method="POST">
        @csrf
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content  mymodal_make_res">
                    <div class="modal-body box_container">
                        <div class="top">
                            <h3>Completa la prenotazione</h3>
                            <button type="button" class="btn_close" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ml-auto bi bi-arrow-90deg-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708z"/>
                            </svg>
                        </button>
                        </div>
                        <div class="body creation">
                            <div class="box players new_players">
                                <h3 class="second">Aggiungi giocatori</h3>                            
                                @foreach ($players as $p)
                                    <input type="checkbox" name="players[]" id="{{$p->id}}" value="{{$p->id}}">
                                    <label for="{{$p->id}}"
                                    class="res_item">
                                        <div class="left">
                                            <div class="time_slot">#{{$p->nickname}}</div>
                                            <div class="date">{{$p->name}} {{$p->surname}}</div>
                                        </div>
                                        <div class="center player_center">
                                            <div class="line">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
                                                <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                                </svg>
                                                <p>{{$p->level}} / 5</p>
                                            </div>
                                            <div class="line">
                                                @if ($p->sex == 'm')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing man" viewBox="0 0 16 16">
                                                    <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing-dres girl" viewBox="0 0 16 16">
                                                    <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m-.5 12.25V12h1v3.25a.75.75 0 0 0 1.5 0V12h1l-1-5v-.215a.285.285 0 0 1 .56-.078l.793 2.777a.711.711 0 1 0 1.364-.405l-1.065-3.461A3 3 0 0 0 8.784 3.5H7.216a3 3 0 0 0-2.868 2.118L3.283 9.079a.711.711 0 1 0 1.365.405l.793-2.777a.285.285 0 0 1 .56.078V7l-1 5h1v3.25a.75.75 0 0 0 1.5 0Z"/>
                                                    </svg>
                                                @endif
                                                <p>{{$p->sex == 'm' ? 'UOMO': 'DONNA'}}</p>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            
                                            <a href="{{route('admin.players.show', $p)}}" class="show">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </label>
                                @endforeach
                                
                            </div>
                            <p class="desc"> 
                                <label class="label_c" for="note">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                    </svg>
                                    Note 
                                </label>
                                <textarea name="note" id="note" cols="30" rows="10" ></textarea>
                            </p>
                        </div>
                        <div class="actions w-100">
                            <button class="my_btn_3 ml-auto"  type="submit">Conferma</button>
                            {{-- <button class="my_btn_1 ml-auto" value="1" name="mail" type="submit">Conferma e Avvisa giocatori</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Qui appariranno le fasce orarie -->
        <div id="slots" class="my-3"></div>
    </form>


</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll("#calendar button");
    const slotsContainer = document.getElementById("slots");
    const fieldname = {
        field_1 : 'Campo 1',
        field_2 : 'Campo 2',
        field_3 : 'Campo 3',
    };


    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            // rimuovo la classe selected da tutte e imposto quella corrente
            buttons.forEach(e => e.classList.remove('selected'));
            const day = JSON.parse(btn.dataset.day);
            btn.classList.add('selected');

            // Pulisco il contenitore e inserisco anche il bottone (nascosto)
            slotsContainer.innerHTML = `
                <div class="fields" id="fields"></div>
                <div class="mt-4">
                    <button id="unique-btn" type="button" class="m-auto my_btn_7 mt-4" style='display:none;' data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Prenota
                    </button>
                </div>
            `;

            const fieldsContainer = document.getElementById("fields");

            // Ciclo i campi
            Object.entries(day.fields).forEach(([fieldName, slots]) => {
                // titolo della sezione
                const title = document.createElement("h4");
                title.classList.add("font-bold", "mb-2");
                title.textContent = fieldname[fieldName] || fieldName;
                fieldsContainer.appendChild(title);

                // contenitore del field
                const fieldDiv = document.createElement("div");
                fieldDiv.classList.add("field");

                if (slots.length > 0) {
                    slots.forEach(slot => {
                        // sanitizzo il time per creare un id HTML sicuro
                        const safeTime = String(slot.time).replace(/[^a-z0-9]/gi, '_');
                        const inputId = `i_${safeTime}_${fieldName}`;

                        // contenitore del singolo slot
                        const timeDiv = document.createElement("div");
                        timeDiv.classList.add("time");
                        if (slot.status) {
                            timeDiv.classList.add("booked", `bk_${slot.d}`);
                        }

                        if (slot.status) {
                            // se è già prenotato, creo link e span
                            const link = document.createElement("a");
                            link.href = `/admin/reservations/${slot.id}`;

                            const spanTime = document.createElement("span");
                            spanTime.classList.add("time_b");
                            spanTime.textContent = slot.time;

                            const spanSubj = document.createElement("span");
                            spanSubj.classList.add("booking_subject");
                            spanSubj.textContent = `#${slot.booking_subject}`;

                            link.appendChild(spanTime);
                            link.appendChild(spanSubj);
                            timeDiv.appendChild(link);
                        } else {
                            // altrimenti creo checkbox + label
                            const input = document.createElement("input");
                            input.type = "checkbox";
                            input.classList.add("slot-checkbox");
                            input.value = `${day.date}/${slot.time}/${fieldName}`;
                            input.name = `times[]`;
                            input.id = inputId;
                            input.addEventListener("change", checkCheckboxes)

                            const label = document.createElement("label");
                            label.htmlFor = inputId;
                            if(!slot.s)label.classList.add("middle");
                            label.textContent = slot.time;

                            timeDiv.appendChild(input);
                            timeDiv.appendChild(label);

                            // esempio: aggiungo event listener qui se vuoi controllare selezioni
                            input.addEventListener("change", () => {
                                const checked = fieldDiv.querySelectorAll(".slot-checkbox:checked");
                                console.log(`Nel campo ${fieldName} ci sono ${checked.length} selezioni`);
                            });
                        }

                        fieldDiv.appendChild(timeDiv);
                    });
                } else {
                    // nessuna fascia oraria
                    const p = document.createElement("p");
                    p.classList.add("text-gray-500");
                    p.textContent = "Nessuna fascia oraria";
                    fieldDiv.appendChild(p);
                }

                // aggiungo il blocco completo al container
                fieldsContainer.appendChild(fieldDiv);
            });


            // Assicuro che il bottone sia nascosto dopo il render
            const uniqueBtn = document.getElementById("unique-btn");
            if (uniqueBtn) uniqueBtn.style.display = "none";
        });
    });
    function checkCheckboxes() {
        const checked = document.querySelectorAll("#fields input[type='checkbox']:checked");
        const extraDiv = document.getElementById("unique-btn");

        if (checked.length > 1 && checked.length < 4) {
            extraDiv.style.display = "block"; // mostra
        } else {
            extraDiv.style.display = "none";  // nascondi
        }
    }
});


</script>



@endsection

