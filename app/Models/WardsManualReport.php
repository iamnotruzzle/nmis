<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsManualReport extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_manual_report';

    protected $fillable = [
        'id',
        'cl2comb',
        'uomcode',
        'esti_budg_unit_cost',
        'beginning_balance',
        'received_from_csr',
        'total_stock',
        'consumption_surgery',
        'consumption_ob_gyne',
        'consumption_ortho',
        'consumption_pedia',
        'consumption_optha',
        'consumption_ent',
        'total_consumption_quantity',
        'total_consumption_cost',
        'ending_balance',
        'actual_inventory',
        'wardcode',
        'entry_by',
        'updated_by',
    ];

    public function item_description()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

    public function unit()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }

    public function entryBy()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }

    public function updatedBy()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }

    public function ward()
    {
        return $this->hasOne(Location::class, 'wardcode', 'wardcode');
    }
}
