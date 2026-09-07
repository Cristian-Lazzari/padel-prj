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
}
