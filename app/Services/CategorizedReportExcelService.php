<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\CarbonImmutable;

class CategorizedReportExcelService
{
    public function build(array $report): Spreadsheet
    {
        $book = new Spreadsheet();
        try {
            $book->getProperties()->setCreator('MangroTix')->setTitle($report['title']);
            $sheet = $book->getActiveSheet();
            $sheet->setTitle('Laporan');
            $sheet->setShowGridlines(false);
            $last = Coordinate::stringFromColumnIndex(count($report['columns']));
            $metadata = [
                'KEBUN RAYA MANGROVE SURABAYA',
                $report['title'],
                'Periode: '.$report['period']['from'].' s.d. '.$report['period']['until'].' | WIB',
                $report['basis'].' | Data dihitung: '.$report['generated_at'],
            ];
            foreach ($metadata as $i => $value) {
                $r = $i + 1;
                $sheet->mergeCells("A{$r}:{$last}{$r}");
                $sheet->setCellValueExplicit('A'.$r, $value, DataType::TYPE_STRING);
            }
            $sheet->fromArray($report['columns'], null, 'A6');
            $row = 7;
            foreach (array_merge($report['rows'], [$report['totals']]) as $values) {
                foreach ($values as $i => $value) {
                    $cell = Coordinate::stringFromColumnIndex($i + 1).$row;
                    $type = $report['types'][$i];
                    if ($value !== '' && in_array($type, ['integer', 'money'], true) && is_numeric($value)) {
                        $sheet->setCellValueExplicit($cell, $type === 'integer' ? (int) $value : (float) $value, DataType::TYPE_NUMERIC);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($type === 'money' ? '"Rp " #,##0.00' : '#,##0');
                    } elseif (in_array($type, ['date', 'datetime'], true) && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value) && substr($value, 0, 10) >= '1900-01-01') {
                        $sheet->setCellValueExplicit($cell, Date::PHPToExcel(CarbonImmutable::parse($value, 'Asia/Jakarta')), DataType::TYPE_NUMERIC);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($type === 'date' ? 'dd/mm/yyyy' : 'dd/mm/yyyy hh:mm:ss');
                    } else {
                        $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                    }
                }
                $row++;
            }
            $total = $row - 1;
            foreach (["A1:{$last}2", "A6:{$last}6"] as $range) {
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('174B38');
                $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            }
            $sheet->getStyle("A{$total}:{$last}{$total}")->getFont()->setBold(true);
            $sheet->getStyle("A6:{$last}{$total}")->getAlignment()->setWrapText(true);
            foreach ($report['columns'] as $i => $label) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($report['types'][$i] === 'text' ? 40 : 25);
            }
            $sheet->getRowDimension(6)->setRowHeight(32);
            $sheet->freezePane('A7');
            if ($report['rows'] !== []) $sheet->setAutoFilter('A6:'.$last.($total - 1));
            $row++;
            foreach ($report['notes'] as $note) {
                $sheet->mergeCells("A{$row}:{$last}{$row}");
                $sheet->setCellValueExplicit('A'.$row, $note, DataType::TYPE_STRING);
                $sheet->getStyle('A'.$row)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(30);
                $row++;
            }
            $sheet->getPageSetup()->setOrientation('landscape')->setPaperSize(9)->setFitToWidth(1)->setFitToHeight(0)->setRowsToRepeatAtTopByStartAndEnd(1, 6);
            return $book;
        } catch (\Throwable $e) {
            $book->disconnectWorksheets();
            throw $e;
        }
    }
}
