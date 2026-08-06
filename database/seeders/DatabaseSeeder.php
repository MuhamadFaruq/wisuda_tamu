<?php

namespace Database\Seeders;

use App\Models\EventSetting;
use App\Models\InstitutionalGuest;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        EventSetting::updateOrCreate(['id' => 1], [
            'name' => 'Wisuda Universitas Sugeng Hartono',
            'period' => 'Periode II Tahun Akademik 2025/2026',
            'event_date' => '2026-07-30 08:00:00',
            'venue' => 'Auditorium Universitas Sugeng Hartono',
            'is_active' => true,
        ]);

        $students = [
            [
                'nim' => '24.11.001',
                'name' => 'Aditya Pratama',
                'faculty' => 'Fakultas Sains & Teknologi',
                'study_program' => 'Informatika',
                'code' => 'USH-26-000001',
                'extra_quota' => 2,
                'package_name' => 'Paket Tambahan 2',
                'guests' => [
                    ['Bapak Surya Pratama', 'orang_tua'],
                    ['Ibu Dewi Pratama', 'orang_tua'],
                    ['Rina Pratama', 'tamu_tambahan'],
                    ['Doni Pratama', 'tamu_tambahan'],
                ],
            ],
            [
                'nim' => '24.12.002',
                'name' => 'Nabila Putri Ramadhani',
                'faculty' => 'Fakultas Ekonomi & Bisnis',
                'study_program' => 'Manajemen',
                'code' => 'USH-26-000002',
                'extra_quota' => 0,
                'package_name' => null,
                'guests' => [
                    ['Bapak Hendra Ramadhani', 'orang_tua'],
                    ['Ibu Ratna Ramadhani', 'orang_tua'],
                ],
            ],
        ];

        foreach ($students as $studentData) {
            $student = Student::create([
                'nim' => $studentData['nim'],
                'name' => $studentData['name'],
                'faculty' => $studentData['faculty'],
                'study_program' => $studentData['study_program'],
            ]);

            $invitation = $student->invitation()->create([
                'code' => $studentData['code'],
                'base_quota' => 2,
                'extra_quota' => $studentData['extra_quota'],
                'package_name' => $studentData['package_name'],
            ]);

            foreach ($studentData['guests'] as [$fullName, $guestType]) {
                $invitation->registeredGuests()->create([
                    'full_name' => $fullName,
                    'guest_type' => $guestType,
                ]);
            }
        }

        $institutionalGuests = [
            [
                'code' => 'USH-INS-00001',
                'full_name' => 'Dr. Lukman Hakim, M.Si.',
                'institution' => 'LLDIKTI Wilayah VI',
                'position' => 'Kepala LLDIKTI',
                'category' => 'LLDIKTI',
                'phone' => '081234560001',
                'companions' => 0,
            ],
            [
                'code' => 'USH-INS-00002',
                'full_name' => 'Hj. Siti Aminah, S.E.',
                'institution' => 'Yayasan Pendidikan Sugeng Hartono',
                'position' => 'Ketua Yayasan',
                'category' => 'Yayasan',
                'phone' => '081234560002',
                'companions' => 0,
            ],
        ];

        foreach ($institutionalGuests as $guest) {
            InstitutionalGuest::create($guest);
        }

        $this->command?->info('Dummy siap: 2 mahasiswa, 6 tamu mahasiswa, dan 2 tamu institusi.');
    }
}
