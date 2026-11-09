<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::orderBy('id')->get()->values();

        // Camila (ya hospedada): pago completo
        $camila = Guest::where('full_name', 'Camila Rojas')->first();
        Reservation::where('guest_id', $camila->id)->first()
            ->payments()->create(['amount' => 360000, 'method' => 'transferencia', 'paid_on' => today()->subDay()]);

        // Check-out hoy con saldo pendiente: habitación 3
        $andres = Guest::create([
            'full_name' => 'Andrés Silva', 'document_type' => 'rut', 'document_number' => '33.333.333-3',
            'country' => 'Chile', 'email' => 'andres@example.com', 'phone' => '+56933333333',
        ]);
        $r1 = Reservation::create([
            'guest_id' => $andres->id, 'people_count' => 2,
            'check_in_date' => today()->subDays(2), 'check_out_date' => today(),
            'arrival_time' => '14:00', 'departure_time' => '11:00',
            'status' => 'en_estadia', 'checked_in_at' => now()->subDays(2), 'total_amount' => 180000,
        ]);
        $r1->rooms()->attach($rooms[2]->id);
        $r1->payments()->create(['amount' => 100000, 'method' => 'efectivo', 'paid_on' => today()->subDays(2)]);

        // Check-in hoy: habitación 4 del Edificio B
        $munoz = Guest::create([
            'full_name' => 'Familia Muñoz', 'document_type' => 'rut', 'document_number' => '44.444.444-4',
            'country' => 'Chile', 'email' => 'munoz@example.com', 'phone' => '+56944444444',
        ]);
        $r2 = Reservation::create([
            'guest_id' => $munoz->id, 'people_count' => 4,
            'check_in_date' => today(), 'check_out_date' => today()->addDays(2),
            'arrival_time' => '17:30', 'departure_time' => '12:00',
            'status' => 'reservada', 'total_amount' => 200000,
        ]);
        $r2->rooms()->attach($rooms[7]->id);
        $r2->payments()->create(['amount' => 50000, 'method' => 'transferencia', 'paid_on' => today()->subDays(5)]);
        $r2->serviceBookings()->create([
            'service_id' => Service::where('name', 'Quincho')->value('id'),
            'date' => today(), 'start_time' => '19:00', 'end_time' => '22:00', 'people_count' => 4,
        ]);
    }
}