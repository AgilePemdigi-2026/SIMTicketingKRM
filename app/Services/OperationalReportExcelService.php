<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class OperationalReportExcelService
{
    private const GREEN = '174B38';

    private const LIGHT_GREEN = 'E9F0EB';

    private const BORDER = 'DCE5DE';

    private const MONEY_FORMAT = '"Rp " #,##0.00';

    private const METRICS = [
        'transaksi_tiket' => 'Pesanan Tiket',
        'pembayaran_lunas' => 'Pembayaran Tiket Lunas',
        'pendapatan_tiket' => 'Pendapatan Tiket',
        'tiket_terbit' => 'E-Tiket Terbit',
        'pengunjung_checkin' => 'Pengunjung Check-in',
        'reservasi_terjadwal' => 'Reservasi Terjadwal',
        'reservasi_lunas' => 'Reservasi Lunas',
        'pendapatan_reservasi' => 'Pendapatan Reservasi',
        'total_pendapatan' => 'Total Pendapatan',
    ];

    private const MONEY_KEYS = [
        'pendapatan_tiket',
        'pendapatan_reservasi',
        'total_pendapatan',
    ];

    public function build(array $report): Spreadsheet
    {
        $workbook = new Spreadsheet();

        try {
            $workbook->getProperties()
                ->setCreator('MangroTix')
                ->setTitle('Laporan Operasional Kebun Raya Mangrove')
                ->setSubject('Laporan transaksi dan kunjungan')
                ->setDescription(
                    'Rekapitulasi operasional Kebun Raya Mangrove Surabaya.'
                );

            $workbook->getDefaultStyle()
                ->getFont()
                ->setName('Calibri')
                ->setSize(11);

            $summarySheet = $workbook->getActiveSheet();

            $summarySheet->setTitle('Ringkasan');

            $this->buildSummary(
                $summarySheet,
                $report
            );

            $dailySheet = $workbook->createSheet();

            $dailySheet->setTitle('Rekap Harian');

            $this->buildDaily(
                $dailySheet,
                $report
            );

            $methodSheet = $workbook->createSheet();

            $methodSheet->setTitle('Metode Pembayaran');

            $this->buildPaymentMethods(
                $methodSheet,
                $report
            );

            $channelSheet = $workbook->createSheet();

            $channelSheet->setTitle('Kanal Penjualan');

            $this->buildSalesChannels(
                $channelSheet,
                $report
            );

            $workbook->setActiveSheetIndex(0);

            return $workbook;

        } catch (\Throwable $exception) {
            $workbook->disconnectWorksheets();

            throw $exception;
        }
    }

    private function prepareSheet(
        Worksheet $sheet,
        array $report,
        string $title,
        string $lastColumn
    ): void {
        $period = $report['period'];

        $generatedAt = CarbonImmutable::parse(
            $report['generated_at']
        )->timezone('Asia/Jakarta')
            ->format('d/m/Y H:i:s');

        $sheet->setShowGridlines(false);

        $sheet->mergeCells("A1:{$lastColumn}1");

        $sheet->setCellValue(
            'A1',
            'KEBUN RAYA MANGROVE SURABAYA'
        );

        $sheet->mergeCells("A2:{$lastColumn}2");

        $sheet->setCellValue('A2', $title);

        $sheet->mergeCells("A3:{$lastColumn}3");

        $sheet->setCellValue(
            'A3',
            'Periode: '.$period['from']
                .' s.d. '.$period['until']
                .' | '.$period['days'].' hari | WIB'
        );

        $sheet->mergeCells("A4:{$lastColumn}4");

        $sheet->setCellValue(
            'A4',
            'Data dihitung: '.$generatedAt.' WIB'
        );

        $sheet->getStyle("A1:{$lastColumn}2")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 14,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => self::GREEN,
                    ],
                ],
                'alignment' => [
                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);

        $sheet->getStyle("A3:{$lastColumn}4")
            ->getFont()
            ->getColor()
            ->setRGB('527A65');

        $sheet->getRowDimension(1)
            ->setRowHeight(28);

        $sheet->getRowDimension(2)
            ->setRowHeight(25);

        $sheet->getRowDimension(3)
            ->setRowHeight(21);

        $sheet->getRowDimension(4)
            ->setRowHeight(21);

        $sheet->freezePane('A7');

        $sheet->getPageSetup()
            ->setOrientation(
                PageSetup::ORIENTATION_LANDSCAPE
            )
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageSetup()
            ->setRowsToRepeatAtTopByStartAndEnd(1, 6);
    }

    private function writeText(
        Worksheet $sheet,
        string $cell,
        string $value
    ): void {
        $sheet->setCellValueExplicit(
            $cell,
            $value,
            DataType::TYPE_STRING
        );
    }

    private function writeNumber(
        Worksheet $sheet,
        string $cell,
        int|float|string $value,
        bool $money = false
    ): void {
        $number = $money
            ? (float) $value
            : (int) $value;

        $sheet->setCellValueExplicit(
            $cell,
            $number,
            DataType::TYPE_NUMERIC
        );

        $sheet->getStyle($cell)
            ->getNumberFormat()
            ->setFormatCode(
                $money
                    ? self::MONEY_FORMAT
                    : '#,##0'
            );
    }

    private function writeMetric(
        Worksheet $sheet,
        string $cell,
        string $key,
        int|float|string $value
    ): void {
        $this->writeNumber(
            $sheet,
            $cell,
            $value,
            in_array(
                $key,
                self::MONEY_KEYS,
                true
            )
        );
    }

    private function headers(
        Worksheet $sheet,
        array $headers
    ): void {
        $sheet->fromArray(
            $headers,
            null,
            'A6'
        );

        $lastColumn =
            Coordinate::stringFromColumnIndex(
                count($headers)
            );

        $sheet->getStyle("A6:{$lastColumn}6")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => self::GREEN,
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);

        $sheet->getRowDimension(6)
            ->setRowHeight(35);
    }

    private function finishTable(
        Worksheet $sheet,
        string $lastColumn,
        int $lastRow,
        array $widths
    ): void {
        $sheet->getStyle(
            "A7:{$lastColumn}{$lastRow}"
        )->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                        Border::BORDER_THIN,
                    'color' => [
                        'rgb' => self::BORDER,
                    ],
                ],
            ],
            'alignment' => [
                'vertical' =>
                    Alignment::VERTICAL_CENTER,
            ],
        ]);

        for ($row = 7; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)
                ->setRowHeight(22);

            if ($row % 2 === 0) {
                $sheet->getStyle(
                    "A{$row}:{$lastColumn}{$row}"
                )->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F7FAF7');
            }
        }

        $sheet->getStyle(
            "A{$lastRow}:{$lastColumn}{$lastRow}"
        )->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => self::GREEN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => self::LIGHT_GREEN,
                ],
            ],
        ]);

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        $sheet->getPageSetup()
            ->setPrintArea(
                "A1:{$lastColumn}{$lastRow}"
            );
    }

    private function buildSummary(
        Worksheet $sheet,
        array $report
    ): void {
        $this->prepareSheet(
            $sheet,
            $report,
            'RINGKASAN OPERASIONAL',
            'B'
        );

        $this->headers(
            $sheet,
            ['Indikator', 'Nilai']
        );

        $row = 7;

        foreach (self::METRICS as $key => $label) {
            $this->writeText(
                $sheet,
                'A'.$row,
                $label
            );

            $this->writeMetric(
                $sheet,
                'B'.$row,
                $key,
                $report['summary'][$key]
            );

            $row++;
        }

        $this->finishTable(
            $sheet,
            'B',
            $row - 1,
            [
                'A' => 42,
                'B' => 30,
            ]
        );

        $notes = [
            'Pendapatan berdasarkan tanggal pembayaran lunas.',
            'Kunjungan aktual berdasarkan E-Tiket yang digunakan.',
            'Reservasi terjadwal mencakup seluruh status administratif.',
            'Pendapatan bersifat bruto sebelum biaya atau refund.',
            'Seluruh tanggal menggunakan zona waktu WIB.',
        ];

        $noteRow = $row + 2;

        $sheet->mergeCells(
            "A{$noteRow}:B{$noteRow}"
        );

        $sheet->setCellValue(
            'A'.$noteRow,
            'CATATAN PERHITUNGAN'
        );

        $sheet->getStyle('A'.$noteRow)
            ->getFont()
            ->setBold(true);

        foreach ($notes as $note) {
            $noteRow++;

            $sheet->mergeCells(
                "A{$noteRow}:B{$noteRow}"
            );

            $this->writeText(
                $sheet,
                'A'.$noteRow,
                $note
            );

            $sheet->getStyle(
                "A{$noteRow}:B{$noteRow}"
            )->getAlignment()
                ->setWrapText(true);

            $sheet->getRowDimension($noteRow)
                ->setRowHeight(32);
        }

        $sheet->getPageSetup()
            ->setPrintArea("A1:B{$noteRow}");
    }

    private function buildDaily(
        Worksheet $sheet,
        array $report
    ): void {
        $this->prepareSheet(
            $sheet,
            $report,
            'REKAPITULASI HARIAN',
            'J'
        );

        $this->headers(
            $sheet,
            [
                'Tanggal',
                ...array_values(self::METRICS),
            ]
        );

        $row = 7;

        foreach ($report['daily'] as $day) {
            $date = CarbonImmutable::parse(
                $day['date'],
                'Asia/Jakarta'
            );

            $sheet->setCellValueExplicit(
                'A'.$row,
                Date::PHPToExcel($date),
                DataType::TYPE_NUMERIC
            );

            $sheet->getStyle('A'.$row)
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy');

            $columnIndex = 2;

            foreach (self::METRICS as $key => $label) {
                $column =
                    Coordinate::stringFromColumnIndex(
                        $columnIndex
                    );

                $this->writeMetric(
                    $sheet,
                    $column.$row,
                    $key,
                    $day[$key]
                );

                $columnIndex++;
            }

            $row++;
        }

        $this->writeText(
            $sheet,
            'A'.$row,
            'Total'
        );

        $columnIndex = 2;

        foreach (self::METRICS as $key => $label) {
            $column =
                Coordinate::stringFromColumnIndex(
                    $columnIndex
                );

            $this->writeMetric(
                $sheet,
                $column.$row,
                $key,
                $report['summary'][$key]
            );

            $columnIndex++;
        }

        $sheet->setAutoFilter(
            'A6:J'.($row - 1)
        );

        $sheet->freezePane('B7');

        $this->finishTable(
            $sheet,
            'J',
            $row,
            [
                'A' => 17,
                'B' => 20,
                'C' => 23,
                'D' => 25,
                'E' => 18,
                'F' => 22,
                'G' => 23,
                'H' => 20,
                'I' => 27,
                'J' => 27,
            ]
        );
    }

    private function buildPaymentMethods(
        Worksheet $sheet,
        array $report
    ): void {
        $this->prepareSheet(
            $sheet,
            $report,
            'RINCIAN METODE PEMBAYARAN',
            'G'
        );

        $this->headers(
            $sheet,
            [
                'Metode',
                'Transaksi Tiket',
                'Transaksi Reservasi',
                'Total Transaksi',
                'Pendapatan Tiket',
                'Pendapatan Reservasi',
                'Total Pendapatan',
            ]
        );

        $labels = [
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'other' => 'Lainnya',
        ];

        $columns = [
            'B' => 'transaksi_tiket',
            'C' => 'transaksi_reservasi',
            'D' => 'total_transaksi',
            'E' => 'pendapatan_tiket',
            'F' => 'pendapatan_reservasi',
            'G' => 'total_pendapatan',
        ];

        $row = 7;

        foreach (
            $report['payment_methods']
            as $method => $data
        ) {
            $this->writeText(
                $sheet,
                'A'.$row,
                $labels[$method] ?? $method
            );

            foreach ($columns as $column => $key) {
                $this->writeMetric(
                    $sheet,
                    $column.$row,
                    $key,
                    $data[$key]
                );
            }

            $row++;
        }

        $summary = $report['summary'];

        $totals = [
            'transaksi_tiket' =>
                $summary['pembayaran_lunas'],

            'transaksi_reservasi' =>
                $summary['reservasi_lunas'],

            'total_transaksi' =>
                $summary['pembayaran_lunas']
                + $summary['reservasi_lunas'],

            'pendapatan_tiket' =>
                $summary['pendapatan_tiket'],

            'pendapatan_reservasi' =>
                $summary['pendapatan_reservasi'],

            'total_pendapatan' =>
                $summary['total_pendapatan'],
        ];

        $this->writeText(
            $sheet,
            'A'.$row,
            'Total'
        );

        foreach ($columns as $column => $key) {
            $this->writeMetric(
                $sheet,
                $column.$row,
                $key,
                $totals[$key]
            );
        }

        $this->finishTable(
            $sheet,
            'G',
            $row,
            [
                'A' => 22,
                'B' => 20,
                'C' => 23,
                'D' => 20,
                'E' => 25,
                'F' => 27,
                'G' => 27,
            ]
        );
    }

    private function buildSalesChannels(
        Worksheet $sheet,
        array $report
    ): void {
        $this->prepareSheet(
            $sheet,
            $report,
            'RINCIAN KANAL PENJUALAN TIKET',
            'C'
        );

        $this->headers(
            $sheet,
            [
                'Kanal Penjualan',
                'Transaksi Lunas',
                'Pendapatan Tiket',
            ]
        );

        $labels = [
            'online' => 'Online',
            'counter' => 'Loket',
            'other' => 'Lainnya',
        ];

        $row = 7;

        foreach (
            $report['sales_channels']
            as $channel => $data
        ) {
            $this->writeText(
                $sheet,
                'A'.$row,
                $labels[$channel] ?? $channel
            );

            $this->writeNumber(
                $sheet,
                'B'.$row,
                $data['transaksi_lunas']
            );

            $this->writeNumber(
                $sheet,
                'C'.$row,
                $data['pendapatan_tiket'],
                true
            );

            $row++;
        }

        $this->writeText(
            $sheet,
            'A'.$row,
            'Total'
        );

        $this->writeNumber(
            $sheet,
            'B'.$row,
            $report['summary']['pembayaran_lunas']
        );

        $this->writeNumber(
            $sheet,
            'C'.$row,
            $report['summary']['pendapatan_tiket'],
            true
        );

        $this->finishTable(
            $sheet,
            'C',
            $row,
            [
                'A' => 30,
                'B' => 25,
                'C' => 30,
            ]
        );
    }
}
