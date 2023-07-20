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
            'ID',
            'BATCH_NO',
            'CL2COMB',
            'BRAND',
            'FUND_SOURCE',
            'QUANTITY',
            'MANUFACTURED_DATE',
            'DELIVERED_DATE',
            'EXPIRATION_DATE',
        ];
    }

    // for array
    public function array(): array
    {
        return $this->data->toArray();
    }
}
