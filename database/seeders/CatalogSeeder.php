<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Masaje',               'category' => 'masaje',      'requires_schedule' => true,  'max_simultaneous' => 1],
            ['name' => 'Quincho',              'category' => 'instalacion', 'requires_schedule' => true,  'max_simultaneous' => 1],
            ['name' => 'Tinaja',               'category' => 'instalacion', 'requires_schedule' => true,  'max_simultaneous' => 1],
            ['name' => 'Pool',                 'category' => 'instalacion', 'requires_schedule' => true,  'max_simultaneous' => 1],
            ['name' => 'Desayuno',             'category' => 'comida',      'requires_schedule' => false, 'max_simultaneous' => null],
            ['name' => 'Almuerzo',             'category' => 'comida',      'requires_schedule' => false, 'max_simultaneous' => null],
            ['name' => 'Cena',                 'category' => 'comida',      'requires_schedule' => false, 'max_simultaneous' => null],
            ['name' => 'Asado',                'category' => 'comida',      'requires_schedule' => false, 'max_simultaneous' => null],
            ['name' => 'Guía de excursiones',  'category' => 'excursion',   'requires_schedule' => true,  'max_simultaneous' => null],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        foreach (['Llega tarde', 'Alergia alimentaria', 'Cicloturista', 'Cliente frecuente'] as $name) {
            Tag::create(['name' => $name]);
        }
    }
}