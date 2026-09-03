{{--
    La griglia del mese dentro la finestra "Blocca giorni". Le caselle non hanno
    name: i giorni chiusi li tiene il javascript in un elenco unico e li rimanda
    tutti al salvataggio, altrimenti chiudere un giorno di ottobre
    riaprirebbe quelli di settembre solo perché non erano a schermo.
    Attende: $m (mese).
--}}
@php $currentDate = date('Y-m-d'); @endphp

<div class="cal__grid">
    @foreach ($m['days'] as $d)
        <input type="checkbox" id="off_{{ $d['date'] }}" data-ui-dayoff="{{ $d['date'] }}"
               @checked(! $d['status'])>
        <label for="off_{{ $d['date'] }}"
               class="cal__day @if ($currentDate === $d['date']) current @endif"
               style="grid-column-start: {{ $d['day_w'] }}">
            <p class="p_day">{{ $d['day'] }}</p>
        </label>
    @endforeach
</div>
