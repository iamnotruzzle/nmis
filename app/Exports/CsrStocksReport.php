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
use PhpOffice\PhpSpreadsheet\Style\Border;

class CsrStocksReport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
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
                $count = count($this->data) + 2;
                // $sumOfCountAndHeaderRow = $count
                // dd($count + 2);
                // $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // $event->sheet->setAllBorders('thin');
                // $event->sheet->getStyle('A1' . ':' . 'S' . $count)->applyFromArray([
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => Border::BORDER_THIN,
                //         ],
                //     ],
                // ]);
                // $event->sheet->getStyle('A1:A2')->applyFromArray([
                //     'borders' => [
                //         'bottom' => [
                //             'borderStyle' => Border::BORDER_NONE,
                //         ],
                //     ],
                // ]);

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
                $event->sheet->getStyle('D1:E1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('F1:G1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('H1:I1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('J1:K1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('L1:M1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('N1:O1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('P1:Q1')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
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
                $event->sheet->getStyle('R1' . ':' . 'S' . $count)->applyFromArray([
                    'borders' => [
                        'bottom' => [
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
