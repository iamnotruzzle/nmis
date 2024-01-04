<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WardStockManualReport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
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
                $event->sheet->getStyle('A1:P2')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $event->sheet->getStyle('A1' . ':' . 'P' . $count)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // item description
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
                $event->sheet->getStyle('B1' . ':' . 'B' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // estimated budget / unit cost
                $event->sheet->getStyle('C1' . ':' . 'C' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('C1' . ':' . 'C' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('C1:C1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // beginning balance
                $event->sheet->getStyle('D1' . ':' . 'D' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('D1' . ':' . 'D' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // received from csr
                $event->sheet->getStyle('E1' . ':' . 'E' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('E1' . ':' . 'E' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // TOTAL STOCK
                $event->sheet->getStyle('F1' . ':' . 'F' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('F1' . ':' . 'F' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // consumption
                $event->sheet->getStyle('G1' . ':' . 'L' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('G1' . ':' . 'L' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('G1:L1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // surgery
                $event->sheet->getStyle('G2' . ':' . 'G' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // ob gyne
                $event->sheet->getStyle('H2' . ':' . 'H' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // ortho
                $event->sheet->getStyle('I2' . ':' . 'I' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // pedia
                $event->sheet->getStyle('J2' . ':' . 'J' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // optha
                $event->sheet->getStyle('K2' . ':' . 'K' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // total consumption
                $event->sheet->getStyle('M1' . ':' . 'M' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('M1' . ':' . 'M' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // total consumption / estimated cost
                $event->sheet->getStyle('N1' . ':' . 'N' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('N1' . ':' . 'N' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('N1:N1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // ending balance
                $event->sheet->getStyle('O1' . ':' . 'O' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('O1' . ':' . 'O' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                // Actual inventory
                $event->sheet->getStyle('P1' . ':' . 'P' . $count)->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $event->sheet->getStyle('P1' . ':' . 'P' . $count)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
            },
        ];
    }

    public function headings(): array
    {
        return [
            ['ITEM DESCRIPTION', 'UNIT', 'ESTIMATED BUDGET', 'BEGINNING BALANCE', 'RECEIVED FROM CSR', 'TOTAL STOCK', '', '', 'CONSUMPTION', '', '', '', 'TOTAL CONSUMPTION', 'TOTAL CONSUMPTION', 'ENDING BALANCE', 'ACTUAL INVENTORY'],
            ['', '', 'UNIT COST', '', '', '', 'SURGERY', 'OB-GYNE', 'ORTHO', 'PEDIA', 'OPTHA', 'ENT', '', '(ESTIMATED COST)', '', ''],
        ];
    }

    // for array
    public function array(): array
    {
        return collect($this->data)->toArray();
    }
}
