<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WardStockManualReport implements FromArray, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
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
