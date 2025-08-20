<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitMaster;

class UnitMasterSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['unit_code' => 'ml', 'unit_name' => 'Milli Litre'],
            ['unit_code' => 'bags', 'unit_name' => 'Bags'],
            ['unit_code' => 'bale', 'unit_name' => 'Bale'],
            ['unit_code' => 'base', 'unit_name' => 'Base'],
            ['unit_code' => 'beg', 'unit_name' => 'Beg.'],
            ['unit_code' => 'bkt', 'unit_name' => 'Bucket'],
            ['unit_code' => 'bottle', 'unit_name' => 'Bottle'],
            ['unit_code' => 'box', 'unit_name' => 'Box'],
            ['unit_code' => 'bundle', 'unit_name' => 'Bundle'],
            ['unit_code' => 'cartoon', 'unit_name' => 'Carton'],
            ['unit_code' => 'coils', 'unit_name' => 'Coils'],
            ['unit_code' => 'cum', 'unit_name' => 'Cubic Meter'],
            ['unit_code' => 'day', 'unit_name' => 'Day'],
            ['unit_code' => 'ft', 'unit_name' => 'Feet'],
            ['unit_code' => 'gm', 'unit_name' => 'Grams'],
            ['unit_code' => 'ha', 'unit_name' => 'Hectare'],
            ['unit_code' => 'job', 'unit_name' => 'Job'],
            ['unit_code' => 'kg', 'unit_name' => 'Kilograms'],
            ['unit_code' => 'ltr', 'unit_name' => 'Litre'],
            ['unit_code' => 'lot', 'unit_name' => 'Lot'],
            ['unit_code' => 'ls', 'unit_name' => 'Ls'],
            ['unit_code' => 'm3', 'unit_name' => 'Cubic Meter'],
            ['unit_code' => 'm', 'unit_name' => 'Meter'],
            ['unit_code' => 'mg', 'unit_name' => 'Milligram'],
            ['unit_code' => 'mt', 'unit_name' => 'Metric Ton'],
            ['unit_code' => 'nos', 'unit_name' => 'Nos.'],
            ['unit_code' => 'pack', 'unit_name' => 'Pack'],
            ['unit_code' => 'packet', 'unit_name' => 'Packet'],
            ['unit_code' => 'pair', 'unit_name' => 'Pair'],
            ['unit_code' => 'piece', 'unit_name' => 'Piece'],
            ['unit_code' => 'pkt', 'unit_name' => 'Pkt.'],
            ['unit_code' => 'rim', 'unit_name' => 'Rim'],
            ['unit_code' => 'rmt', 'unit_name' => 'Rmt'],
            ['unit_code' => 'roll', 'unit_name' => 'Roll'],
            ['unit_code' => 'set', 'unit_name' => 'Set'],
            ['unit_code' => 'sqft', 'unit_name' => 'Square Feet'],
            ['unit_code' => 'sqm', 'unit_name' => 'Square Meter'],
            ['unit_code' => 'strip', 'unit_name' => 'Strip'],
            ['unit_code' => 'treep', 'unit_name' => 'Treep'],
            ['unit_code' => 'trip', 'unit_name' => 'Trip'],
            ['unit_code' => 'truck', 'unit_name' => 'Truck'],
            ['unit_code' => 'ug', 'unit_name' => 'Ug'],
            ['unit_code' => 'unit', 'unit_name' => 'Unit'],
        ];

        foreach ($units as $unit) {
            UnitMaster::create($unit);
        }
    }
}
