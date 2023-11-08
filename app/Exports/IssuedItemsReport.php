<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithStartRow;

// WithDrawings
class IssuedItemsReport implements FromArray, WithHeadings, WithEvents, WithStyles, ShouldAutoSize, WithColumnWidths, WithStartRow
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function startRow(): int
    {
        return 2;
    }

    // add image
    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setDescription('This is my logo');
    //     $drawing->setPath(public_path('/images/hosp_logo.png'));
    //     $drawing->setHeight(90);
    //     $drawing->setCoordinates('A1');

    //     return $drawing;
    // }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the 1st
            1 => ['font' => ['bold' => true]],
        ];
    }
    public function columnWidths(): array
    {
        return [
            'D' => 11,
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // page orientation
                // $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

                // set default paper size
                $event->sheet->getPageSetup()->setPaperSizeDefault(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                );

                // set the scale of the paper
                $event->sheet->getPageSetup()->setScale(85);
            },

            AfterSheet::class => function (AfterSheet $event) {
                $alphabet       = $event->sheet->getHighestDataColumn();
                $totalRow       = $event->sheet->getHighestDataRow();
                $cellRange      = 'A1:' . $alphabet . $totalRow;
                $event->sheet->styleCells(
                    $cellRange,
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ]
                    ]
                );
                $event->sheet->getDelegate()
                    ->getStyle($cellRange)
                    ->applyFromArray(['alignment' => ['wrapText' => true]]);

                $event->sheet->getStyle('A1:A1')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '66CDAA'],
                    ],
                ]);
                $event->sheet->getStyle('B1:B1')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEE8AA'],
                    ],
                ]);
                $event->sheet->getStyle('C1:C1')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3CB371'],
                    ],
                ]);
                $event->sheet->getStyle('D1:D1')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '87CEFA'],
                    ],
                ]);
            },
        ];
    }

    public function headings(): array
    {
        return [
            ['ITEM', 'REQUESTED', 'APPROVED', 'REMARKS'],
        ];
    }

    // for array
    public function array(): array
    {
        return collect($this->data)->toArray();
    }
}
