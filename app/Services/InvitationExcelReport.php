<?php

namespace App\Services;

use App\Models\EventSetting;
use App\Models\InstitutionalGuest;
use App\Models\Invitation;
use App\Support\Code39;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvitationExcelReport
{
    private const NAVY = '1D315B';

    private const GOLD = 'E3B923';

    private const LIGHT = 'F3F6FB';

    /** @var array<int, \GdImage> */
    private array $images = [];

    public function make(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('Universitas Sugeng Hartono')
            ->setTitle('Laporan Data Undangan Wisuda')
            ->setSubject('Data tamu dan barcode undangan');

        $studentSheet = $spreadsheet->getActiveSheet();
        $studentSheet->setTitle('Undangan Mahasiswa');
        $this->fillStudentInvitations($studentSheet);

        $institutionalSheet = $spreadsheet->createSheet();
        $institutionalSheet->setTitle('Tamu Institusi');
        $this->fillInstitutionalGuests($institutionalSheet);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillStudentInvitations(Worksheet $sheet): void
    {
        $headers = [
            'No.', 'Kode Barcode', 'Gambar Barcode', 'Link Download PNG',
            'Nama Mahasiswa', 'NIM', 'Fakultas', 'Program Studi',
            'Nama Lengkap Tamu', 'Jenis Tamu',
        ];
        $this->prepareSheet($sheet, 'LAPORAN DATA UNDANGAN MAHASISWA', $headers);

        $row = 5;
        $number = 1;
        $invitations = Invitation::with([
            'student',
            'registeredGuests' => fn ($query) => $query->orderBy('full_name'),
        ])->orderBy('code')->get();

        foreach ($invitations as $invitation) {
            $guests = $invitation->registeredGuests;
            if ($guests->isEmpty()) {
                $guests = collect([(object) ['full_name' => '-', 'guest_type' => '-']]);
            }

            foreach ($guests as $guest) {
                $sheet->fromArray([[
                    $number++,
                    $invitation->code,
                    '',
                    'Unduh barcode PNG',
                    $invitation->student->name,
                    $invitation->student->nim,
                    $invitation->student->faculty,
                    $invitation->student->study_program,
                    $guest->full_name,
                    $this->guestTypeLabel($guest->guest_type),
                ]], null, "A{$row}");

                $this->addBarcode($sheet, $invitation->code, "C{$row}");
                $this->addDownloadLink($sheet, $invitation->code, "D{$row}");
                $this->styleDataRow($sheet, $row, count($headers));
                $row++;
            }
        }

        $this->finishSheet($sheet, $row - 1, count($headers));
    }

    private function fillInstitutionalGuests(Worksheet $sheet): void
    {
        $headers = [
            'No.', 'Kode Barcode', 'Gambar Barcode', 'Link Download PNG',
            'Nama Lengkap Tamu', 'Instansi', 'Jabatan', 'Kategori',
            'Jumlah Pendamping', 'Catatan',
        ];
        $this->prepareSheet($sheet, 'LAPORAN TAMU INSTITUSI & VIP', $headers);

        $row = 5;
        foreach (InstitutionalGuest::orderBy('category')->orderBy('full_name')->get() as $index => $guest) {
            $sheet->fromArray([[
                $index + 1,
                $guest->code,
                '',
                'Unduh barcode PNG',
                $guest->full_name,
                $guest->institution,
                $guest->position ?: '-',
                $guest->category,
                $guest->companions,
                $guest->notes ?: '-',
            ]], null, "A{$row}");

            $this->addBarcode($sheet, $guest->code, "C{$row}");
            $this->addDownloadLink($sheet, $guest->code, "D{$row}");
            $this->styleDataRow($sheet, $row, count($headers));
            $row++;
        }

        $this->finishSheet($sheet, $row - 1, count($headers));
    }

    private function prepareSheet(Worksheet $sheet, string $title, array $headers): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $agenda = EventSetting::where('is_active', true)->first();

        $sheet->setShowGridlines(false);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A2', $agenda?->name ?? 'Agenda Wisuda Aktif');
        $sheet->mergeCells("A3:{$lastColumn}3");
        $sheet->setCellValue('A3', 'Dibuat: '.now()->translatedFormat('d F Y, H:i').' WIB');
        $sheet->fromArray([$headers], null, 'A4');

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => self::NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A2:{$lastColumn}3")->applyFromArray([
            'font' => ['color' => ['rgb' => '4B5563']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => self::LIGHT]],
        ]);
        $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => self::NAVY]],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => self::GOLD]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::NAVY]]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(4)->setRowHeight(32);
        $sheet->freezePane('A5');
    }

    private function styleDataRow(Worksheet $sheet, int $row, int $columnCount): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $sheet->getRowDimension($row)->setRowHeight(68);
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'D9E0EA']]],
        ]);
        $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function finishSheet(Worksheet $sheet, int $lastRow, int $columnCount): void
    {
        $widths = [7, 22, 35, 22, 28, 17, 37, 23, 29, 24];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setWidth($width);
        }

        if ($lastRow >= 5) {
            $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");
        }
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
    }

    private function addBarcode(Worksheet $sheet, string $code, string $coordinate): void
    {
        $image = Code39::image($code, 2, 48);
        $this->images[] = $image;

        $drawing = new MemoryDrawing;
        $drawing->setName("Barcode {$code}");
        $drawing->setImageResource($image);
        $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_PNG);
        $drawing->setHeight(58);
        $drawing->setCoordinates($coordinate);
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }

    private function addDownloadLink(Worksheet $sheet, string $code, string $coordinate): void
    {
        $sheet->getCell($coordinate)->getHyperlink()->setUrl(route('barcodes.png', ['code' => $code]));
        $sheet->getStyle($coordinate)->getFont()
            ->setColor(new Color(self::NAVY))
            ->setUnderline(true);
    }

    private function guestTypeLabel(string $type): string
    {
        return match ($type) {
            'orang_tua_1' => 'Orang Tua/Wali 1',
            'orang_tua_2' => 'Orang Tua/Wali 2',
            'tamu_tambahan' => 'Tamu Tambahan',
            default => $type === '-' ? '-' : str($type)->replace('_', ' ')->title()->toString(),
        };
    }
}
