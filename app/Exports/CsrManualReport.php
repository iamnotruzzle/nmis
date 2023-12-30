<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CsrManualReport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the 1st and 2nd row.
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function registerEvents(): array
    {
        // dd(count($this->data));

        // $count = count($this->data);

        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 2 == 1st 2 rows = header & sub header
                $count = count($this->data) + 2;

                // all header
                $event->sheet->getStyle('A1:S2')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $event->sheet->getStyle('A1' . ':' . 'S' . $count)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // regular fund
                $event->sheet->getStyle('A1' . ':' . 'A' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('A1' . ':' . 'A' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // unit
                $event->sheet->getStyle('B1' . ':' . 'B' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // unit cost
                $event->sheet->getStyle('C1' . ':' . 'C' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // csr
                $event->sheet->getStyle('D1' . ':' . 'E' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('D2' . ':' . 'D' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('D1:E1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('D1:E2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEC137'],
                    ],
                ]);
                // ward
                $event->sheet->getStyle('F1' . ':' . 'G' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('F2' . ':' . 'F' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('F1:G1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('F1:G2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEC137'],
                    ],
                ]);
                // total beginning balance
                $event->sheet->getStyle('H1' . ':' . 'I' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('H2' . ':' . 'H' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('H1:I1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('H1:I2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEC137'],
                    ],
                ]);
                // supplies issued to wards
                $event->sheet->getStyle('J1' . ':' . 'K' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('J2' . ':' . 'J' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('J1:K1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('J1:K2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4CD07D'],
                    ],
                ]);
                // consumption
                $event->sheet->getStyle('L1' . ':' . 'M' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('L2' . ':' . 'L' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('L1:M1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('L1:M2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C79807'],
                    ],
                ]);
                // csr ending bal
                $event->sheet->getStyle('N1' . ':' . 'O' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('N2' . ':' . 'N' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('N1:O1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('N1:O2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '35C4DC'],
                    ],
                ]);
                // ward ending bal
                $event->sheet->getStyle('P1' . ':' . 'Q' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('P2' . ':' . 'P' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('P1:Q1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('P1:Q2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '35C4DC'],
                    ],
                ]);
                // total ending balance
                $event->sheet->getStyle('R1' . ':' . 'S' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('R2' . ':' . 'R' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('R1:S1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('R1:S2')->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '35C4DC'],
                    ],
                ]);
            },
        ];
    }

    public function headings(): array
    {
        return [
            ['REGULAR FUND', 'UNIT', 'UNIT COST',  'CSR', '', 'WARD', '', 'TOTAL BEGINNING BALANCE', '', 'SUPPLIES ISSUED TO WARDS', '', 'CONSUMPTION', '', 'CSR', '', 'WARD', '', 'TOTAL ENDING BALANCE'],
            ['ITEM DESCRIPTION', '', '', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'TOTAL QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'TOTAL QUANTITY', 'TOTAL COST'],
        ];
    }

    // for array
    public function array(): array
    {
        return collect($this->data)->toArray();
    }
}
