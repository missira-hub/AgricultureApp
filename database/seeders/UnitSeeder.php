<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Milliliter', 'abbreviation' => 'mL'],
            ['name' => 'Piece', 'abbreviation' => 'piece'],
            ['name' => 'Bunch', 'abbreviation' => 'bunch'],
            ['name' => 'Sack', 'abbreviation' => 'sack'],
            ['name' => 'Tray', 'abbreviation' => 'tray'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
