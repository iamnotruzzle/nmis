<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaEndorsementDetail extends Model
{
    use HasFactory;

    // ward assistant endorsement details
    protected $table = 'csrw_wa_endorsements_details';

    protected $fillable = [
        'endorsement_id',
        'description',
        'tag',
        'status',
    ];

    public function endorsement()
    {
        return $this->belongsTo(WaEndorsement::class, 'endorsement_id');
    }
}
