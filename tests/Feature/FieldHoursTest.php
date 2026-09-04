<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\FieldHour;
use App\Models\Player;
use App\Models\Setting;
use App\Services\FieldSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gli orari dei campi, giorno per giorno.
 *
 * Coprono le due strade che devono restare in piedi insieme: i campi letti
 * dalle tabelle e il vecchio `field_set` dentro `settings.advanced`, che
 * resta come rete di sicurezza per le installazioni non ancora migrate.
 */
class FieldHoursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forgetFieldSet();
    }

    private function campo(string $nome = 'Campo 1', array $orari = []): Field
    {
        $field = Field::create([
            'name' => $nome,
            'type' => 'padel',
            'm_during' => 30,
            'm_during_client' => 90,
            'sort' => 0,
        ]);

        foreach (array_keys(FieldHour::WEEKDAYS) as $weekday) {
            $riga = $orari[$weekday] ?? ['h_start' => '08:00', 'h_end' => '23:00'];

            FieldHour::create([
                'field_id' => $field->id,
                'weekday' => $weekday,
                'closed' => ! empty($riga['closed']),
                'h_start' => $riga['closed'] ?? false ? null : $riga['h_start'],
                'h_end' => $riga['closed'] ?? false ? null : $riga['h_end'],
            ]);
        }

        Setting::forgetFieldSet();

        return $field;
    }

    public function test_i_campi_arrivano_dalle_tabelle_nella_forma_di_sempre(): void
    {
        $this->campo('Campo 1', [
            1 => ['h_start' => '15:00', 'h_end' => '20:00'],
            7 => ['closed' => true],
        ]);

        $set = Setting::fieldSet();

        $this->assertArrayHasKey('Campo 1', $set);
        $this->assertSame(30, $set['Campo 1']['m_during']);
        $this->assertSame('padel', $set['Campo 1']['type']);
        $this->assertSame([7], $set['Campo 1']['closed_days']);
        $this->assertSame('08:00', $set['Campo 1']['h_start']);
        $this->assertSame(
            ['closed' => false, 'h_start' => '15:00', 'h_end' => '20:00'],
            $set['Campo 1']['hours'][1]
        );
    }

    public function test_senza_tabelle_si_continua_a_leggere_il_json(): void
    {
        Setting::create(['name' => 'advanced', 'status' => 1, 'property' => json_encode([
            'field_set' => [
                'Campo vecchio' => [
                    'h_start' => '09:00', 'n_slot' => '10', 'm_during' => '30',
                    'm_during_client' => '60', 'type' => 'padel', 'closed_days' => [7],
                ],
            ],
        ])]);

        $set = Setting::fieldSet();

        $this->assertArrayHasKey('Campo vecchio', $set);

        // Il servizio ricava comunque apertura e chiusura del giorno.
        $this->assertSame(
            ['h_start' => '09:00', 'h_end' => '19:00'],
            FieldSchedule::hours($set['Campo vecchio'], 1)
        );
        $this->assertNull(FieldSchedule::hours($set['Campo vecchio'], 7));
    }

    public function test_la_griglia_del_giorno_si_ferma_alla_chiusura(): void
    {
        $this->campo('Campo 1', [
            1 => ['h_start' => '15:00', 'h_end' => '17:00'],
            2 => ['closed' => true],
        ]);

        $campo = Setting::fieldSet()['Campo 1'];

        $this->assertSame(['15:00', '15:30', '16:00', '16:30', '17:00'], FieldSchedule::points($campo, 1));
        $this->assertSame([], FieldSchedule::points($campo, 2));
        $this->assertSame(120, FieldSchedule::minutes($campo, 1));
        $this->assertSame(0, FieldSchedule::minutes($campo, 2));
        $this->assertSame(['h_start' => '08:00', 'h_end' => '23:00'], FieldSchedule::span($campo));
    }

    public function test_la_disponibilita_online_segue_gli_orari_del_giorno(): void
    {
        $this->campo('Campo 1', [
            1 => ['h_start' => '15:00', 'h_end' => '20:00'], // lunedì corto
            2 => ['closed' => true],                          // martedì chiuso
        ]);

        Setting::create(['name' => 'advanced', 'status' => 1, 'property' => json_encode([
            'day_off' => [], 'delay_trainer' => 0, 'max_delay_default' => 24, 'trainer_set' => [],
        ])]);

        // Player non è mass assignable: si riempie a mano, come fa il resto del codice.
        $player = new Player();
        $player->nickname = 'tester';
        $player->name = 'Tester';
        $player->surname = 'Prova';
        $player->mail = 'tester@example.it';
        $player->phone = '3330000000';
        $player->level = 3;
        $player->sex = 'm';
        $player->save();

        $giorni = $this->getJson('/api/reservation/get_date?type=padel')
            ->assertOk()
            ->json('data');

        foreach ($giorni as $giorno) {
            $orari = $giorno['fields']['Campo 1'] ?? [];

            if ($giorno['dayOfWeek'] === '2') {
                $this->assertSame([], $orari, 'Il martedì il campo è chiuso');
                continue;
            }

            if ($giorno['dayOfWeek'] === '1' && $orari) {
                // Ultima partenza utile per finire l'ora e mezza entro le 20:00.
                $this->assertSame('15:00', reset($orari));
                $this->assertSame('18:30', end($orari));
            }
        }
    }
}
