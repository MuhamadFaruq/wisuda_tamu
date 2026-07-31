<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\EventSetting;
use App\Models\InstitutionalGuest;
use App\Models\Invitation;
use App\Models\RegisteredGuest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ush.ac.id'],
            ['name' => 'Admin Registrasi', 'password' => 'Admin123!']
        );

        EventSetting::updateOrCreate(['id' => 1], [
            'name' => 'Wisuda Universitas Sugeng Hartono',
            'period' => 'Periode II Tahun Akademik 2025/2026',
            'event_date' => '2026-07-30 08:00:00',
            'venue' => 'Auditorium Universitas Sugeng Hartono',
            'is_active' => true,
        ]);

        $samples = [
            ['24.11.001', 'Aditya Pratama', 'Fakultas Sains & Teknologi', 'Informatika', 0, null, 2, [['Bapak Surya Pratama', 'orang_tua'], ['Ibu Dewi Pratama', 'orang_tua']]],
            ['24.12.014', 'Nabila Putri Ramadhani', 'Fakultas Ekonomi & Bisnis', 'Manajemen', 2, 'Paket Keluarga', 3, [['Bapak Hendra Ramadhani', 'orang_tua'], ['Ibu Ratna Ramadhani', 'orang_tua'], ['Siti Aisyah', 'tamu_tambahan'], ['Ahmad Ramadhani', 'tamu_tambahan']]],
            ['24.13.027', 'Rizky Maulana', 'Fakultas Ilmu Kesehatan', 'Administrasi Kesehatan', 1, 'Paket Tambahan 1', 1, [['Bapak Dedi Maulana', 'orang_tua'], ['Ibu Yuni Maulana', 'orang_tua'], ['Rina Maulana', 'tamu_tambahan']]],
            ['24.11.039', 'Citra Ayu Lestari', 'Fakultas Sains & Teknologi', 'Sistem Informasi', 0, null, 0, [['Bapak Joko Lestari', 'orang_tua'], ['Ibu Sri Lestari', 'orang_tua']]],
            ['24.12.052', 'Fajar Nugraha', 'Fakultas Ekonomi & Bisnis', 'Akuntansi', 0, null, 1, [['Bapak Agus Nugraha', 'orang_tua'], ['Ibu Lilis Nugraha', 'orang_tua']]],
            ['24.13.066', 'Salsabila Rahma', 'Fakultas Ilmu Kesehatan', 'Farmasi', 2, 'Paket Keluarga', 0, [['Bapak Rahmat Hidayat', 'wali'], ['Ibu Nurhayati', 'wali'], ['Farhan Hidayat', 'tamu_tambahan'], ['Nadia Hidayat', 'tamu_tambahan']]],
            ['24.11.078', 'Dimas Arya Saputra', 'Fakultas Sains & Teknologi', 'Teknik Industri', 1, 'Paket Tambahan 1', 2, [['Bapak Bambang Saputra', 'orang_tua'], ['Ibu Wati Saputra', 'orang_tua'], ['Rudi Saputra', 'tamu_tambahan']]],
            ['24.12.083', 'Aulia Maharani', 'Fakultas Ekonomi & Bisnis', 'Bisnis Digital', 0, null, 0, [['Bapak Irwan Maharani', 'orang_tua'], ['Ibu Fitri Maharani', 'orang_tua']]],
            ['24.13.095', 'Kevin Wijaya', 'Fakultas Ilmu Kesehatan', 'Keperawatan', 0, null, 2, [['Bapak Daniel Wijaya', 'orang_tua'], ['Ibu Maria Wijaya', 'orang_tua']]],
            ['24.11.107', 'Intan Permata Sari', 'Fakultas Sains & Teknologi', 'Informatika', 2, 'Paket Keluarga', 1, [['Bapak Suharto', 'wali'], ['Ibu Aminah', 'wali'], ['Rani Permata', 'tamu_tambahan'], ['Doni Permata', 'tamu_tambahan']]],
            ['24.12.119', 'Muhammad Ilham', 'Fakultas Ekonomi & Bisnis', 'Manajemen', 1, 'Paket Tambahan 1', 0, [['Bapak Hasan Basri', 'orang_tua'], ['Ibu Salmah', 'orang_tua'], ['Nurul Ilham', 'tamu_tambahan']]],
            ['24.13.125', 'Ghaitsa Nurfadila', 'Fakultas Ilmu Kesehatan', 'Administrasi Kesehatan', 0, null, 1, [['Bapak Arif Hidayat', 'orang_tua'], ['Ibu Rina Hidayat', 'orang_tua']]],
        ];

        foreach ($samples as $i => [$nim,$name,$faculty,$program,$extra,$package,$presentCount,$guests]) {
            $student = Student::updateOrCreate(['nim' => $nim], [
                'name' => $name,
                'faculty' => $faculty,
                'study_program' => $program,
            ]);

            $invitation = Invitation::updateOrCreate(['student_id' => $student->id], [
                'code' => 'USH-26-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'base_quota' => 2,
                'extra_quota' => $extra,
                'package_name' => $package,
                'notes' => $i % 4 === 0 ? 'Mohon arahkan ke baris kursi depan.' : null,
            ]);

            // Remove legacy records from earlier demo fixtures so this seeder stays deterministic.
            $invitation->attendances()->whereNull('registered_guest_id')->delete();
            $invitation->registeredGuests()
                ->whereNotIn('full_name', array_column($guests, 0))
                ->delete();

            foreach ($guests as $guestIndex => [$guestName, $guestType]) {
                $isPresent = $guestIndex < $presentCount;
                $checkedInAt = now()->startOfDay()->addHours(7)->addMinutes(($i * 7) + ($guestIndex * 3));

                $guest = $invitation->registeredGuests()->updateOrCreate(
                    ['full_name' => $guestName],
                    ['guest_type' => $guestType, 'attended_at' => $isPresent ? $checkedInAt : null]
                );

                if ($isPresent) {
                    $invitation->attendances()->updateOrCreate(
                        ['registered_guest_id' => $guest->id],
                        [
                            'guest_name' => $guestName,
                            'guest_type' => $guestType,
                            'checked_in_at' => $checkedInAt,
                            'gate' => $i % 3 === 0 ? 'Pintu Timur' : 'Pintu Utama',
                        ]
                    );
                }
            }
        }

        $institutionalSamples = [
            ['USH-INS-00001', 'Dr. Lukman Hakim, M.Si.', 'LLDIKTI Wilayah VI', 'Kepala LLDIKTI', 'LLDIKTI', '081234560001', 2, true, 'Penyambutan oleh Rektor'],
            ['USH-INS-00002', 'Hj. Siti Aminah, S.E.', 'Yayasan Pendidikan Sugeng Hartono', 'Ketua Yayasan', 'Yayasan', '081234560002', 1, true, 'Kursi VVIP baris depan'],
            ['USH-INS-00003', 'Budi Santoso, S.STP.', 'Pemerintah Kabupaten Sukoharjo', 'Sekretaris Daerah', 'Pejabat Pemerintah', '081234560003', 2, false, null],
            ['USH-INS-00004', 'Prof. Dr. Ahmad Fauzi', 'Universitas Mitra Nusantara', 'Rektor', 'Pimpinan Perguruan Tinggi', '081234560004', 1, false, null],
            ['USH-INS-00005', 'Rina Kusumawardani', 'PT Solusi Digital Indonesia', 'Direktur Utama', 'Mitra Universitas', '081234560005', 1, true, 'Koordinasi dengan bagian kerja sama'],
            ['USH-INS-00006', 'Andi Prakoso', 'Harian Suara Pendidikan', 'Jurnalis', 'Media', '081234560006', 0, false, 'Akses area dokumentasi'],
            ['USH-INS-00007', 'Dr. Sri Wahyuni', 'Dinas Pendidikan Provinsi Jawa Tengah', 'Kepala Bidang', 'VIP/VVIP', '081234560007', 1, false, null],
            ['USH-INS-00008', 'Ir. Hendra Gunawan', 'Kamar Dagang dan Industri', 'Ketua Bidang Pendidikan', 'Tamu Institusi Lainnya', '081234560008', 0, true, null],
        ];

        foreach ($institutionalSamples as $i => [$code,$fullName,$institution,$position,$category,$phone,$companions,$present,$notes]) {
            InstitutionalGuest::updateOrCreate(['code' => $code], [
                'full_name' => $fullName,
                'institution' => $institution,
                'position' => $position,
                'category' => $category,
                'phone' => $phone,
                'companions' => $companions,
                'checked_in_at' => $present ? now()->startOfDay()->addHours(7)->addMinutes(15 + ($i * 6)) : null,
                'gate' => $present ? 'Pintu VIP' : null,
                'notes' => $notes,
            ]);
        }

        $this->command?->info(sprintf(
            'Dummy siap: %d mahasiswa, %d undangan, %d tamu mahasiswa, %d tamu institusi, %d sudah hadir.',
            Student::count(),
            Invitation::count(),
            RegisteredGuest::count(),
            InstitutionalGuest::count(),
            Attendance::count(),
        ));
    }
}
