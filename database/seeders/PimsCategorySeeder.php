<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PimsCategorySeeder extends Seeder
{
    public function run()
    {
        $csrw_pims_categories = [
            ['catID' => 1, 'categoryname' => 'Medical Supplies', 'status' => 'A'],
            ['catID' => 2, 'categoryname' => 'Office Supplies', 'status' => 'A'],
            ['catID' => 3, 'categoryname' => 'IT Supplies', 'status' => 'A'],
            ['catID' => 4, 'categoryname' => 'Housekeeping Supplies', 'status' => 'A'],
            ['catID' => 5, 'categoryname' => 'Electrical Supplies', 'status' => 'A'],
            ['catID' => 6, 'categoryname' => 'Laboratory Supplies & Reagents', 'status' => 'A'],
            ['catID' => 7, 'categoryname' => 'Building Maintenance Supplies', 'status' => 'A'],
            ['catID' => 8, 'categoryname' => 'IT Equipment', 'status' => 'A'],
            ['catID' => 9, 'categoryname' => 'Drugs and Medicines', 'status' => 'A'],
            ['catID' => 10, 'categoryname' => 'Dental Supplies', 'status' => 'A'],
            ['catID' => 11, 'categoryname' => 'Medical Equipment', 'status' => 'A'],
            ['catID' => 12, 'categoryname' => 'PT Rehabilitation', 'status' => 'A'],
            ['catID' => 13, 'categoryname' => 'Hemodialysis Supplies', 'status' => 'A'],
            ['catID' => 14, 'categoryname' => 'Radiology Supplies', 'status' => 'A'],
            ['catID' => 15, 'categoryname' => 'Electrical Equipment', 'status' => 'A'],
            ['catID' => 16, 'categoryname' => 'Medical Instruments', 'status' => 'A'],
            ['catID' => 17, 'categoryname' => 'Transportation Equipment', 'status' => 'A'],
            ['catID' => 18, 'categoryname' => 'Foods (Dietary) Supplies', 'status' => 'A'],
            ['catID' => 19, 'categoryname' => 'Laboratory Equipment', 'status' => 'A'],
            ['catID' => 20, 'categoryname' => 'Laboratory Instrument', 'status' => 'A'],
            ['catID' => 21, 'categoryname' => 'Office Equipment', 'status' => 'A'],
            ['catID' => 22, 'categoryname' => 'Accountable Forms', 'status' => 'A'],
            ['catID' => 23, 'categoryname' => 'IT License, Plan, Subscription', 'status' => 'A'],
            ['catID' => 24, 'categoryname' => 'Motorpool', 'status' => 'A'],
            ['catID' => 25, 'categoryname' => 'Non-accountable Forms', 'status' => 'A'],
            ['catID' => 26, 'categoryname' => 'Furnitures, Fixtures, Books', 'status' => 'A'],
            ['catID' => 27, 'categoryname' => 'Spare parts', 'status' => 'A'],
            ['catID' => 28, 'categoryname' => 'Other Supplies and Materials', 'status' => 'A'],
            ['catID' => 29, 'categoryname' => 'Testing Materials', 'status' => 'A'],
            ['catID' => 30, 'categoryname' => 'Consumer', 'status' => 'A'],
            ['catID' => 31, 'categoryname' => 'Kitchen Ware', 'status' => 'A'],
            ['catID' => 32, 'categoryname' => 'Infrastructure', 'status' => 'A'],
            ['catID' => 33, 'categoryname' => 'Linen&Laundry Supplies', 'status' => 'A'],
            ['catID' => 34, 'categoryname' => 'CENDU Supplies', 'status' => 'A'],
            ['catID' => 35, 'categoryname' => 'Carpentry', 'status' => 'A'],
            ['catID' => 36, 'categoryname' => 'Visual Aids', 'status' => 'A'],
            ['catID' => 38, 'categoryname' => 'Foods & Snacks for Events', 'status' => 'A'],
            ['catID' => 39, 'categoryname' => 'Plumbing', 'status' => 'A'],
            ['catID' => 40, 'categoryname' => 'Metal & Welding', 'status' => 'A'],
            ['catID' => 41, 'categoryname' => 'Airconditioning', 'status' => 'A'],
            ['catID' => 42, 'categoryname' => 'Job Order', 'status' => 'A'],
            ['catID' => 43, 'categoryname' => 'Newborn Screening Supplies', 'status' => 'A'],
            ['catID' => 44, 'categoryname' => 'Painting and Sticker Supplies', 'status' => 'A'],
            ['catID' => 45, 'categoryname' => 'Nuclear Medicine Supplies', 'status' => 'A'],
            ['catID' => 47, 'categoryname' => 'Catherization Laboratory Supplies', 'status' => 'A'],
            ['catID' => 49, 'categoryname' => 'Cargo Forwarding and Hauling Services', 'status' => 'A'],
            ['catID' => 50, 'categoryname' => 'Lifting Equipment', 'status' => 'A'],
            ['catID' => 51, 'categoryname' => 'Annual Calibration and Preventive Maintenance', 'status' => 'A'],
            ['catID' => 52, 'categoryname' => 'Human Milk Bank Supplies', 'status' => 'A'],
            ['catID' => 53, 'categoryname' => 'Motorpool (Registration, Insurances & Emission)', 'status' => 'A'],
            ['catID' => 54, 'categoryname' => 'Advertising', 'status' => 'A'],
            ['catID' => 56, 'categoryname' => 'Program, Events, Activities', 'status' => 'A'],
            ['catID' => 57, 'categoryname' => 'Repair, Parts & Services', 'status' => 'A'],
            ['catID' => 58, 'categoryname' => 'Rentals', 'status' => 'A'],
            ['catID' => 59, 'categoryname' => 'Regulatory Requirements', 'status' => 'A'],
            ['catID' => 60, 'categoryname' => 'Fee (Procedures, Trainings, Miscellaneous, etc.)', 'status' => 'A'],
            ['catID' => 62, 'categoryname' => 'Other Equipment', 'status' => 'A'],
            ['catID' => 63, 'categoryname' => 'Annual Inspection Fees', 'status' => 'A'],
            ['catID' => 64, 'categoryname' => 'Civil Works', 'status' => 'A'],
            ['catID' => 65, 'categoryname' => 'Property Plant and Equipment', 'status' => 'A'],
            ['catID' => 66, 'categoryname' => 'For Renovation Materials (Itemized)', 'status' => 'A'],
            ['catID' => 71, 'categoryname' => 'Postage and Courier Service', 'status' => 'A'],
            ['catID' => 72, 'categoryname' => 'Printing and Publication', 'status' => 'A'],
            ['catID' => 74, 'categoryname' => 'Other Machineries and Equipment', 'status' => 'A'],
            ['catID' => 75, 'categoryname' => 'Radiation Oncology Supplies', 'status' => 'A'],
            ['catID' => 76, 'categoryname' => 'Perfusion Supplies', 'status' => 'A'],
            ['catID' => 77, 'categoryname' => 'Fire Sprinkler', 'status' => 'A'],
            ['catID' => 78, 'categoryname' => 'Medical Instruments (For Patients)', 'status' => 'A'],
            ['catID' => 79, 'categoryname' => 'Insurance Expense', 'status' => 'A'],
            ['catID' => 80, 'categoryname' => 'Fabrication', 'status' => 'A'],
        ];

        // Insert data into the database
        DB::table('csrw_pims_categories')->insert($csrw_pims_categories);
    }
}
