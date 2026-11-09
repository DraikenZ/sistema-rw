<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::orderBy('id')->get()->values();

        $camila = Guest::create([
            'full_name' => 'Camila Rojas', 'document_type' => 'rut', 'document_number' => '11.111.111-1',
            'country' => 'Chile', 'email' => 'camila@example.com', 'phone' => '+56911111111',
        ]);
        $diego = Guest::create([
            'full_name' => 'Diego Torres', 'document_type' => 'rut', 'document_number' => '22.222.222-2',
            'country' => 'Chile', 'email' => 'diego@example.com', 'phone' => '+56922222222',
        ]);
        $marta = Guest::create([
            'full_name' => 'Marta Tapia', 'document_type' => 'pasaporte', 'document_number' => 'P1234567',
            'country' => 'Argentina', 'email' => 'marta@example.com', 'phone' => '+5491133333333',
        ]);

        $diego->tags()->attach(Tag::where('name', 'Llega tarde')->value('id'));
        $marta->tags()->attach(Tag::where('name', 'Cicloturista')->value('id'));

        // Ocupada: habitación 1
        $r1 = Reservation::create([
            'guest_id' => $camila->id, 'people_count' => 2,
            'check_in_date' => today()->subDay(), 'check_out_date' => today()->addDays(3),
            'arrival_time' => '15:00', 'status' => 'en_estadia', 'checked_in_at' => now()->subDay(),
            'total_amount' => 360000,
        ]);
        $r1->rooms()->attach($rooms[0]->id);
        $r1->serviceBookings()->create([
            'service_id' => Service::where('name', 'Tinaja')->value('id'),
            'date' => today(), 'start_time' => '20:00', 'end_time' => '21:00', 'people_count' => 2,
        ]);

        // Reservada: habitación 2
        $r2 = Reservation::create([
            'guest_id' => $diego->id, 'people_count' => 2,
            'check_in_date' => today(), 'check_out_date' => today()->addDays(2),
            'arrival_time' => '15:00', 'status' => 'reservada',
        ]);
        $r2->rooms()->attach($rooms[1]->id);
        $r2->serviceBookings()->create([
            'service_id' => Service::where('name', 'Desayuno')->value('id'),
            'date' => today(), 'people_count' => 2,
        ]);

        // Familia en dos habitaciones (Edificio B): 1 y 2
        $r3 = Reservation::create([
            'guest_id' => $marta->id, 'people_count' => 4,
            'check_in_date' => today()->subDays(2), 'check_out_date' => today()->addDay(),
            'arrival_time' => '18:00', 'status' => 'en_estadia', 'checked_in_at' => now()->subDays(2),
        ]);
        $r3->rooms()->attach($rooms[4]->id);
        $r3->rooms()->attach($rooms[5]->id, ['extra_bed' => true]);

        // En limpieza: Lenga
        $rooms[3]->update(['needs_cleaning' => true]);

        // Mantenimiento: habitación 3 del Edificio B
        $rooms[6]->update(['out_of_service' => true, 'notes' => 'Reparación de baño']);
    }
}