<?php

namespace Tests\Feature;

use App\Mail\confermaOrdineAdmin;
use App\Models\Field;
use App\Models\FieldHour;
use App\Models\Player;
use App\Models\Reservation;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * La prenotazione dal sito del cliente.
 *
 * Il caso che conta: dopo il salvataggio nessun intoppo su email o
 * impostazioni deve tornare indietro come errore, perché la prenotazione
 * a quel punto esiste già e il cliente si vedrebbe rifiutare uno slot
 * occupato da sé stesso.
 */
class ClientBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forgetFieldSet();
        $this->campo();
    }

    private function campo(string $nome = 'Campo 1'): Field
    {
        $field = Field::create([
            'name' => $nome,
            'type' => 'padel',
            'm_during' => 30,
            'm_during_client' => 90,
            'sort' => 0,
        ]);

        foreach (array_keys(FieldHour::WEEKDAYS) as $weekday) {
            FieldHour::create([
                'field_id' => $field->id,
                'weekday' => $weekday,
                'closed' => false,
                'h_start' => '08:00',
                'h_end' => '23:00',
            ]);
        }

        Setting::forgetFieldSet();

        return $field;
    }

    private function giocatore(string $nickname = 'ospite'): Player
    {
        $player = new Player;
        $player->name = 'Mario';
        $player->surname = 'Rossi';
        $player->nickname = $nickname;
        $player->sex = 'm';
        $player->phone = '3330000000';
        $player->mail = $nickname.'@example.it';
        $player->mail_verified_at = now();
        $player->save();

        return $player;
    }

    private function impostazioni(): void
    {
        Setting::create([
            'name' => 'advanced',
            'status' => 1,
            'property' => json_encode(['day_off' => [], 'trainer_set' => [], 'delay_trainer' => 0]),
        ]);
    }

    private function contatti(): void
    {
        Setting::create([
            'name' => 'Contatti',
            'status' => 1,
            'property' => json_encode(['email' => 'admin@example.it', 'phone' => '0731000000']),
        ]);
    }

    private function payload(Player $player, array $extra = []): array
    {
        return array_merge([
            'user_id' => $player->id,
            'date_slot' => Carbon::tomorrow('Europe/Rome')->format('Y-m-d').' 18:00',
            'field' => 'Campo 1',
            'dinner' => ['status' => true, 'guests' => 2, 'time' => '20:00'],
            'type' => 'padel',
            'players' => [$player->nickname],
            'message' => null,
            'open' => null,
        ], $extra);
    }

    public function test_la_prenotazione_va_a_buon_fine_e_manda_le_due_email(): void
    {
        Mail::fake();
        $this->contatti();
        $player = $this->giocatore();

        $response = $this->postJson('/api/get_reservation', $this->payload($player));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('reservations', 1);
        // Una all'amministratore e una al giocatore.
        Mail::assertSent(confermaOrdineAdmin::class, 2);
    }

    public function test_senza_il_blocco_advanced_la_prenotazione_riesce_lo_stesso(): void
    {
        // `max_delay_default` vive in settings.advanced: quando manca il
        // riepilogo email deve ripiegare su un valore di scorta invece di
        // far fallire tutta la chiamata.
        Mail::fake();
        $this->contatti();
        $player = $this->giocatore();

        $this->assertNull(Setting::where('name', 'advanced')->first());

        $response = $this->postJson('/api/get_reservation', $this->payload($player));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_se_lemail_non_parte_la_prenotazione_resta_e_il_cliente_vede_ok(): void
    {
        // Nessuna riga "Contatti": il destinatario admin è vuoto e l'invio
        // esplode. La prenotazione però è già salvata.
        Mail::fake();
        $player = $this->giocatore();

        $response = $this->postJson('/api/get_reservation', $this->payload($player));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_lo_slot_gia_occupato_viene_rifiutato_senza_creare_nulla(): void
    {
        Mail::fake();
        $this->contatti();
        $player = $this->giocatore();
        $altro = $this->giocatore('secondo');

        $this->postJson('/api/get_reservation', $this->payload($player))
            ->assertJson(['success' => true]);

        // Mezz'ora dopo: si accavalla con l'ora e mezza appena prenotata.
        $sovrapposta = $this->payload($altro, [
            'date_slot' => Carbon::tomorrow('Europe/Rome')->format('Y-m-d').' 18:30',
        ]);

        $this->postJson('/api/get_reservation', $sovrapposta)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('reservations', 1);
        $this->assertSame(3, Reservation::first()->duration);
    }

    /**
     * Una lezione lunga tutto il giorno, iniziata stamattina: nel pomeriggio
     * il campo è ancora occupato e l'app non deve proporre orari liberi.
     * Prima l'API caricava solo le prenotazioni iniziate da meno di due ore
     * e mezza, e la lezione spariva dalle disponibilità.
     */
    private function lezioneDalMattino(): string
    {
        Carbon::setTestNow(Carbon::today('Europe/Rome')->setTime(15, 0));

        Setting::create([
            'name' => 'advanced',
            'status' => 1,
            'property' => json_encode(['day_off' => [], 'trainer_set' => [], 'delay_trainer' => 0]),
        ]);

        $oggi = Carbon::today('Europe/Rome')->format('Y-m-d');

        $lezione = new Reservation;
        $lezione->date_slot = $oggi.' 09:00';
        $lezione->field = 'Campo 1';
        $lezione->status = 1;
        $lezione->duration = 23; // fino alle 20:30
        $lezione->type = 'padel';
        $lezione->lesson = 1;
        $lezione->dinner = json_encode(['status' => false]);
        $lezione->booking_subject = 0;
        $lezione->save();

        return $oggi;
    }

    public function test_una_lezione_iniziata_ore_fa_blocca_ancora_le_disponibilita(): void
    {
        $oggi = $this->lezioneDalMattino();

        $giorni = $this->getJson('/api/reservation/get_date?type=padel')->json('data');
        $orari = collect($giorni)->firstWhere('date', $oggi)['fields']['Campo 1'];

        // Fino alle 20:30 è tutto della lezione: la prima partenza utile è 20:30.
        $this->assertSame('20:30', $orari[0] ?? null);

        Carbon::setTestNow();
    }

    public function test_non_si_prenota_sopra_una_lezione_iniziata_ore_fa(): void
    {
        Mail::fake();
        $this->contatti();
        $oggi = $this->lezioneDalMattino();
        $player = $this->giocatore();

        $this->postJson('/api/get_reservation', $this->payload($player, [
            'date_slot' => $oggi.' 17:00',
        ]))->assertJson(['success' => false]);

        $this->assertDatabaseCount('reservations', 1);

        Carbon::setTestNow();
    }

    /**
     * Il gestore annulla una settimana di un campo fisso: l'app mostra
     * l'orario libero e la prenotazione deve passare. Prima il controllo
     * guardava il campo fisso "sulla carta" e rispondeva "campo non
     * disponibile" su un orario proposto come libero.
     */
    public function test_un_campo_fisso_annullato_quel_giorno_si_puo_prenotare(): void
    {
        Mail::fake();
        $this->contatti();
        $player = $this->giocatore();
        $domani = Carbon::tomorrow('Europe/Rome');

        $this->impostazioni();
        $fisso = \App\Models\FixedSlot::create([
            'player_id' => $player->id,
            'field' => 'Campo 1',
            'weekday' => $domani->dayOfWeek,
            'start_time' => '18:00',
            'duration' => 3,
            'valid_from' => $domani->copy()->subWeek()->format('Y-m-d'),
            'valid_to' => $domani->copy()->addMonth()->format('Y-m-d'),
            'status' => 'active',
        ]);

        $occorrenza = new Reservation;
        $occorrenza->date_slot = $domani->format('Y-m-d').' 18:00';
        $occorrenza->field = 'Campo 1';
        $occorrenza->status = 0; // annullata dal gestore
        $occorrenza->duration = 3;
        $occorrenza->type = 'padel';
        $occorrenza->dinner = json_encode(['status' => false]);
        $occorrenza->booking_subject = 0;
        $occorrenza->fixed_slot_id = $fisso->id;
        $occorrenza->save();

        $giorni = $this->getJson('/api/reservation/get_date?type=padel')->json('data');
        $orari = collect($giorni)->firstWhere('date', $domani->format('Y-m-d'))['fields']['Campo 1'];
        $this->assertContains('18:00', $orari);

        $this->postJson('/api/get_reservation', $this->payload($player))
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('reservations', 2);
    }

    /** Ogni orario proposto dall'app deve essere davvero prenotabile. */
    public function test_ogni_orario_proposto_si_puo_prenotare(): void
    {
        Mail::fake();
        $this->contatti();
        $player = $this->giocatore();
        $domani = Carbon::tomorrow('Europe/Rome')->format('Y-m-d');
        $this->impostazioni();

        $altra = new Reservation;
        $altra->date_slot = $domani.' 18:00';
        $altra->field = 'Campo 1';
        $altra->status = 1;
        $altra->duration = 3;
        $altra->type = 'padel';
        $altra->dinner = json_encode(['status' => false]);
        $altra->booking_subject = 0;
        $altra->save();

        $giorni = $this->getJson('/api/reservation/get_date?type=padel')->json('data');
        $orari = collect($giorni)->firstWhere('date', $domani)['fields']['Campo 1'];

        foreach ($orari as $ora) {
            $this->assertNull(
                Reservation::overlapping('Campo 1', Carbon::parse($domani.' '.$ora), 3),
                "L'app propone le $ora ma il campo è occupato"
            );
        }
        $this->assertNotContains('17:00', $orari);
        $this->assertNotContains('19:00', $orari);
        $this->assertContains('19:30', $orari);
    }
}
