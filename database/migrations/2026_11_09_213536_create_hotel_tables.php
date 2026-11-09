<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('naming_type'); // nombre | numero
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Pehuén" o "1"
            $table->string('type'); // matrimonial, premium, individual, doble, triple
            $table->unsignedTinyInteger('capacity');
            $table->boolean('allows_extra_bed')->default(true);
            $table->string('bathroom'); // individual | compartido
            $table->boolean('out_of_service')->default(false); // alerta / mantención
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['building_id', 'name']);
        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('document_type')->nullable(); // rut | pasaporte | ci
            $table->string('document_number')->nullable();
            $table->string('country')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable(); // observaciones y preferencias
            $table->timestamps();
            $table->unique(['document_type', 'document_number']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_tag', function (Blueprint $table) {
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['guest_id', 'tag_id']);
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained(); // quien reserva
            $table->unsignedTinyInteger('people_count');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->time('arrival_time')->nullable();    // hora estimada de llegada
            $table->time('departure_time')->nullable();  // hora estimada de salida
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('status')->default('reservada'); // reservada | en_estadia | finalizada | cancelada
            $table->unsignedInteger('total_amount')->nullable(); // CLP
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['check_in_date', 'check_out_date']);
        });

        // Una reserva puede incluir varias habitaciones
        Schema::create('reservation_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained();
            $table->boolean('extra_bed')->default(false);
            $table->timestamps();
            $table->unique(['reservation_id', 'room_id']);
        });

        // Grupo familiar o amigos (el titular está en reservations.guest_id)
        Schema::create('reservation_guest', function (Blueprint $table) {
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->primary(['reservation_id', 'guest_id']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // masaje | instalacion | comida | excursion
            $table->boolean('requires_schedule')->default(false);
            $table->unsignedTinyInteger('max_simultaneous')->nullable(); // null = sin límite
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Solo registra qué servicios usa cada reserva (sin precios)
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedTinyInteger('people_count')->default(1);
            $table->string('status')->default('reservado'); // reservado | realizado | cancelado
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['service_id', 'date']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount'); // CLP
            $table->string('method'); // efectivo | transferencia | link_externo | otro
            $table->date('paid_on');
            $table->string('receipt_path')->nullable(); // foto del comprobante
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('service_bookings');
        Schema::dropIfExists('services');
        Schema::dropIfExists('reservation_guest');
        Schema::dropIfExists('reservation_room');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guest_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('buildings');
    }
};