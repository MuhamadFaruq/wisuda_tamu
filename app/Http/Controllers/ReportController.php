<?php

namespace App\Http\Controllers;

use App\Services\GuestReportPdf;
use App\Services\InvitationExcelReport;
use App\Support\Code39;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function guests(GuestReportPdf $report): Response
    {
        return $report->make()->download('laporan-tamu-wisuda-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function invitations(InvitationExcelReport $report): StreamedResponse
    {
        $filename = 'laporan-data-undangan-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($report): void {
            (new Xlsx($report->make()))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function barcodePng(string $code): Response
    {
        return response(Code39::png($code), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="barcode-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $code).'.png"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
