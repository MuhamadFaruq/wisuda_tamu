<?php

namespace Tests\Feature;

use App\Models\EventSetting;
use App\Models\InstitutionalGuest;
use App\Models\Invitation;
use App\Models\Student;
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

    public function test_admin_can_export_invitation_report_excel_with_embedded_qr_codes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/data-undangan.xlsx');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }

    public function test_admin_can_download_qr_code_as_png(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/qr-code-png/USH-INV-00001');

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(600, $size[0]);
        $this->assertSame(600, $size[1]);
    }

    public function test_institutional_guest_can_be_found_and_checked_in_by_qr_code(): void
    {
        $user = User::factory()->create();
        $guest = InstitutionalGuest::create([
            'code' => 'USH-INS-99999',
            'full_name' => 'Tamu LLDIKTI',
            'institution' => 'LLDIKTI Wilayah VI',
            'category' => 'LLDIKTI',
        ]);

        $this->actingAs($user)->getJson('/qr-code/'.$guest->code)
            ->assertOk()
            ->assertJsonPath('kind', 'institutional')
            ->assertJsonPath('guest.full_name', 'Tamu LLDIKTI')
            ->assertJsonPath('guest.seat_number', 'V1');

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

    public function test_seat_numbers_follow_each_guest_category_sequence(): void
    {
        $firstStudent = Student::create(['nim' => 'TEST-001', 'name' => 'Mahasiswa Pertama', 'faculty' => 'Fakultas A', 'study_program' => 'Prodi A']);
        $firstInvitation = Invitation::create(['student_id' => $firstStudent->id, 'code' => 'TEST-INV-001', 'base_quota' => 2, 'extra_quota' => 1]);
        $secondStudent = Student::create(['nim' => 'TEST-002', 'name' => 'Mahasiswa Kedua', 'faculty' => 'Fakultas B', 'study_program' => 'Prodi B']);
        $secondInvitation = Invitation::create(['student_id' => $secondStudent->id, 'code' => 'TEST-INV-002', 'base_quota' => 2, 'extra_quota' => 1]);

        $this->assertSame('1A', $firstInvitation->registeredGuests()->create(['full_name' => 'Orang Tua 1', 'guest_type' => 'orang_tua'])->seat_number);
        $this->assertSame('1B', $firstInvitation->registeredGuests()->create(['full_name' => 'Orang Tua 2', 'guest_type' => 'orang_tua'])->seat_number);
        $this->assertSame('2A', $secondInvitation->registeredGuests()->create(['full_name' => 'Orang Tua 3', 'guest_type' => 'wali'])->seat_number);
        $this->assertSame('T1', $firstInvitation->registeredGuests()->create(['full_name' => 'Tambahan 1', 'guest_type' => 'tamu_tambahan'])->seat_number);
        $this->assertSame('T2', $secondInvitation->registeredGuests()->create(['full_name' => 'Tambahan 2', 'guest_type' => 'tamu_tambahan'])->seat_number);
        $this->assertSame('V1', InstitutionalGuest::create(['code' => 'TEST-INS-001', 'full_name' => 'VIP 1', 'institution' => 'Institusi A', 'category' => 'VIP'])->seat_number);
        $this->assertSame('V2', InstitutionalGuest::create(['code' => 'TEST-INS-002', 'full_name' => 'VIP 2', 'institution' => 'Institusi B', 'category' => 'VIP'])->seat_number);
    }

    public function test_two_base_guests_are_shown_and_checked_in_together_while_additional_guest_stays_single(): void
    {
        $user = User::factory()->create();
        $student = Student::create(['nim' => 'BATCH-001', 'name' => 'Mahasiswa Batch', 'faculty' => 'Fakultas A', 'study_program' => 'Prodi A']);
        $invitation = Invitation::create(['student_id' => $student->id, 'code' => 'BATCH-QR-001', 'base_quota' => 2, 'extra_quota' => 1]);
        $father = $invitation->registeredGuests()->create(['full_name' => 'Bapak Batch', 'guest_type' => 'orang_tua']);
        $mother = $invitation->registeredGuests()->create(['full_name' => 'Ibu Batch', 'guest_type' => 'orang_tua']);
        $additional = $invitation->registeredGuests()->create(['full_name' => 'Tamu Tambahan Batch', 'guest_type' => 'tamu_tambahan']);

        $this->actingAs($user)->getJson('/qr-code/'.$invitation->code)
            ->assertOk()
            ->assertJsonPath('batch', true)
            ->assertJsonCount(2, 'guests')
            ->assertJsonPath('guests.0.full_name', 'Bapak Batch')
            ->assertJsonPath('guests.1.full_name', 'Ibu Batch');

        $this->actingAs($user)->post('/check-in', [
            'code' => $invitation->code,
            'registered_guest_ids' => [$father->id, $mother->id],
            'guest_name' => 'Bapak Batch & Ibu Batch',
            'guest_type' => 'orang_tua',
        ])->assertSessionHas('success');

        $this->assertNotNull($father->fresh()->attended_at);
        $this->assertNotNull($mother->fresh()->attended_at);
        $this->assertSame(2, $invitation->attendances()->count());

        $this->actingAs($user)->getJson('/qr-code/'.$invitation->code)
            ->assertOk()
            ->assertJsonPath('batch', false)
            ->assertJsonCount(1, 'guests')
            ->assertJsonPath('guest.id', $additional->id);
    }
}
