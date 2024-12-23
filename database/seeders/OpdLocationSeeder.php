<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class OpdLocationSeeder extends Seeder
{
    public function run()
    {
        // Insert multiple rows
        $locations = [
            [
                'wardcode' => 'ANES',
                'wardname' => 'Diagnostics',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'APU',
                'wardname' => 'APU',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'DENT',
                'wardname' => 'Dental',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'ENT',
                'wardname' => 'ENT',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'FAMED',
                'wardname' => 'Family Medicine',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'GYNE',
                'wardname' => 'Gynecology',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'HEMO',
                'wardname' => 'Hemodialysis',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'MED',
                'wardname' => 'Internal Medicine',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'NUCME',
                'wardname' => 'Nuclear Medicine',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'OB',
                'wardname' => 'Obstetrics',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'ONCO',
                'wardname' => 'Oncology',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'OPHTH',
                'wardname' => 'Ophthalmology',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'ORTHO',
                'wardname' => 'Orthopedics',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'PEDIA',
                'wardname' => 'Pediatrics',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'REHAB',
                'wardname' => 'PT Rehab',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
            [
                'wardcode' => 'SURG',
                'wardname' => 'Surgery',
                'wardstat' => 'A',
                'enctype' => 'OPD',
            ],
        ];

        // Insert the data
        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
