<?php

namespace App\Exports;

use App\Models\CsrStocks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CsrStocksReport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            ['REGULAR FUND', 'UNIT', 'UNIT COST', 'CSR', '', 'WARD', '', 'TOTAL BEGINNING BALANCE', '', 'SUPPLIES ISSUED TO WARDS', '', 'CONSUMPTION', '', 'CSR', '', 'WARD', 'TOTAL ENDING BALANCE'],
            ['ITEM DESCRIPTION', '', '', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'TOTAL QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'QUANTITY', 'TOTAL COST', 'TOTAL QUANTITY', 'TOTAL COST'],
        ];
    }

    // for array
    public function array(): array
    {
        return collect($this->data)->toArray();
    }
}
