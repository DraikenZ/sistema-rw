<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Edificio A: habitaciones con nombre (CAMBIA los nombres y tipos por los reales)
        $a = Building::create(['name' => 'Edificio A', 'naming_type' => 'nombre']);
        $a->rooms()->createMany([
            ['name' => 'Araucaria', 'type' => 'matrimonial', 'capacity' => 2, 'bathroom' => 'individual'],
            ['name' => 'Pehuén',    'type' => 'premium',     'capacity' => 2, 'bathroom' => 'individual'],
            ['name' => 'Copihue',   'type' => 'doble',       'capacity' => 2, 'bathroom' => 'compartido'],
            ['name' => 'Lenga',     'type' => 'triple',      'capacity' => 3, 'bathroom' => 'compartido'],
        ]);

        // Edificio B: habitaciones con número (CAMBIA los tipos por los reales)
        $b = Building::create(['name' => 'Edificio B', 'naming_type' => 'numero']);
        $b->rooms()->createMany([
            ['name' => '1', 'type' => 'matrimonial', 'capacity' => 2, 'bathroom' => 'individual'],
            ['name' => '2', 'type' => 'individual',  'capacity' => 1, 'bathroom' => 'compartido'],
            ['name' => '3', 'type' => 'doble',       'capacity' => 2, 'bathroom' => 'compartido'],
            ['name' => '4', 'type' => 'triple',      'capacity' => 3, 'bathroom' => 'compartido'],
        ]);
    }
}