<?php

namespace App\Exports;

use App\Models\CsrStocks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;


class CsrStocksReport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // for array
    public function array(): array
    {
        return $this->data->toArray();
    }
}
