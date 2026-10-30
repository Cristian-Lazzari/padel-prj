<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\FieldHour;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le prenotazioni create dalla dashboard (lezioni, partite, tornei) non
 * possono finire sopra un'altra prenotazione attiva dello stesso campo.
 */
class AdminBookingOverlapTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $giorno;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Campo 1', 'Campo 2'] as $sort => $nome) {
            $field = Field::create([
                'name' => $nome,
                'type' => 'padel',
                'm_during' => 30,
                'm_during_client' => 90,
                'sort' => $sort,
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
        }

        Setting::forgetFieldSet();

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['playerId' => 1])->save();
        $this->giorno = Carbon::tomorrow('Europe/Rome')->format('Y-m-d');
    }

    /** Una partita dall'app: 18:00-19:30 sul Campo 1. */
    private function partita(int $status = 1): Reservation
    {
        $r = new Reservation;
        $r->date_slot = $this->giorno.' 18:00';
        $r->field = 'Campo 1';
        $r->status = $status;
        $r->duration = 3;
        $r->type = 'padel';
        $r->dinner = json_encode(['status' => false]);
        $r->booking_subject = 0;
        $r->save();

        return $r;
    }

    private function fasce(string $campo, array $orari, ?string $giorno = null): array
    {
        return array_map(fn ($t) => ($giorno ?? $this->giorno).'/'.$t.'/'.$campo, $orari);
    }

    private function crea(array $times, string $tipo = 'singola')
    {
        return $this->actingAs($this->admin)->post('/admin/reservations/createFromD', [
            'type_res' => $tipo,
            'times' => $times,
            'lesson' => 1,
        ]);
    }

    public function test_una_lezione_sopra_una_partita_viene_rifiutata(): void
    {
        $this->partita();

        $this->crea($this->fasce('Campo 1', ['19:00', '19:30', '20:00']))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_una_lezione_subito_dopo_la_partita_si_crea(): void
    {
        $this->partita();

        $this->crea($this->fasce('Campo 1', ['19:30', '20:00', '20:30']))
            ->assertSessionHas('message');

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_sopra_una_partita_annullata_si_puo_prenotare(): void
    {
        $this->partita(0);

        $this->crea($this->fasce('Campo 1', ['18:00', '18:30', '19:00']))
            ->assertSessionHas('message');

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_la_multipla_con_un_giorno_occupato_non_crea_niente(): void
    {
        $this->partita();
        $dopodomani = Carbon::parse($this->giorno)->addDay()->format('Y-m-d');

        $times = array_merge(
            $this->fasce('Campo 1', ['18:30', '19:00', '19:30'], $dopodomani),
            $this->fasce('Campo 1', ['18:30', '19:00', '19:30'])
        );

        $this->crea($times, 'multipla')->assertSessionHas('error');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_riattivare_una_prenotazione_su_uno_slot_ripreso_viene_rifiutato(): void
    {
        $annullata = $this->partita(0);
        $this->partita();

        $this->actingAs($this->admin)
            ->from('/admin/reservations/'.$annullata->id.'/edit')
            ->put('/admin/reservations/'.$annullata->id, [
                'status' => 1,
                'lesson' => 0,
            ])
            ->assertRedirect('/admin/reservations/'.$annullata->id.'/edit')
            ->assertSessionHas('error');

        $this->assertSame('0', (string) $annullata->fresh()->status);
    }
}
