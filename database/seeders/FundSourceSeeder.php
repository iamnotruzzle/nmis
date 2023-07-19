<?php

namespace Database\Seeders;

use App\Models\FundSource;
use Illuminate\Database\Seeder;

class FundSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fundSource = [
            [
                'fsid' => 1,
                'fsName' => 'Regular Agency Fund',
                'cluster_code' => 01,
            ],
            [
                'fsid' => 3,
                'fsName' => 'Crossmatching Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 4,
                'fsName' => 'Blood Deposit Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 5,
                'fsName' => 'Hemodialysis Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 6,
                'fsName' => 'Drugs and Medicines (Revolving)',
                'cluster_code' => 06,
            ],
            [
                'fsid' => 7,
                'fsName' => 'Oxygen Revolving Fund',
                'cluster_code' => 06,
            ],
            [
                'fsid' => 8,
                'fsName' => 'Income Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 9,
                'fsName' => 'Bid Documents',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 10,
                'fsName' => 'Pro-Poor',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 13,
                'fsName' => 'HEMS Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 15,
                'fsName' => 'Emergency Preparedness',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 18,
                'fsName' => 'Consumer',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 19,
                'fsName' => 'Income Trust Fund (Supplemental)',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 22,
                'fsName' => 'NBS Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 23,
                'fsName' => 'Emergency Purchase',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 24,
                'fsName' => 'Business Related Fund',
                'cluster_code' => 06,
            ],
            [
                'fsid' => 26,
                'fsName' => 'Nuclear Medicine Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 27,
                'fsName' => 'CENDU Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 28,
                'fsName' => 'CT Scan Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 30,
                'fsName' => 'Gender and Development Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 88,
                'fsName' => 'Catheterization Laboratory Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 89,
                'fsName' => 'Affiliation Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 91,
                'fsName' => 'Drug Test Trust Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 92,
                'fsName' => 'Sports Fund',
                'cluster_code' => 01,
            ],
            [
                'fsid' => 93,
                'fsName' => 'MRI Income Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 95,
                'fsName' => 'Moleculuar Biology Laboratory Income Fund',
                'cluster_code' => 05,
            ],
            [
                'fsid' => 96,
                'fsName' => 'Contingency Fund',
                'cluster_code' => 05,
            ],
        ];

        FundSource::insert($fundSource);
    }
}
