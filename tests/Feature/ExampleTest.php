<?php

namespace Tests\Feature;

use App\Models\EventSetting;
use App\Models\InstitutionalGuest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_dashboard_requires_admin_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'secret-admin']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-admin'])
            ->assertRedirect('/');

        $this->get('/')->assertOk();
    }

    public function test_active_agenda_is_updated_across_dashboard_and_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/agenda-aktif', [
            'name' => 'Wisuda Periode Baru',
            'period' => 'Periode III Tahun 2027',
            'event_date' => '2027-01-15 09:30',
            'venue' => 'Gedung Serbaguna USH',
        ])->assertSessionHas('success');

        $this->get('/')
            ->assertOk()
            ->assertSee('Wisuda Periode Baru')
            ->assertSee('15 Januari 2027')
            ->assertSee('Gedung Serbaguna USH');

        auth()->logout();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Periode III Tahun 2027')
            ->assertSee('15 Januari 2027')
            ->assertSee('Gedung Serbaguna USH');

        $this->assertSame(1, EventSetting::where('is_active', true)->count());
    }

    public function test_admin_can_export_guest_report_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/tamu.pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_admin_can_export_invitation_report_excel_with_embedded_barcodes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/data-undangan.xlsx');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }

    public function test_admin_can_download_barcode_as_png(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/barcode-png/USH-INV-00001');

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
    }

    public function test_institutional_guest_can_be_found_and_checked_in_by_barcode(): void
    {
        $user = User::factory()->create();
        $guest = InstitutionalGuest::create([
            'code' => 'USH-INS-99999',
            'full_name' => 'Tamu LLDIKTI',
            'institution' => 'LLDIKTI Wilayah VI',
            'category' => 'LLDIKTI',
        ]);

        $this->actingAs($user)->getJson('/barcode/'.$guest->code)
            ->assertOk()
            ->assertJsonPath('kind', 'institutional')
            ->assertJsonPath('guest.full_name', 'Tamu LLDIKTI');

        $this->actingAs($user)->get('/tamu-institusi/'.$guest->id)
            ->assertOk()
            ->assertSee('Undangan Kehormatan')
            ->assertSee('LLDIKTI Wilayah VI');

        $this->actingAs($user)->get('/?qi=LLDIKTI')
            ->assertOk()
            ->assertSee('Tamu LLDIKTI');

        $this->actingAs($user)->post('/check-in', [
            'code' => $guest->code,
            'institutional_guest_id' => $guest->id,
            'guest_name' => $guest->full_name,
            'guest_type' => 'tamu_institusi',
        ])->assertSessionHas('success');

        $this->assertNotNull($guest->fresh()->checked_in_at);
    }
}
