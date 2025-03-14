<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('preferred_barber_id')->nullable();
            $table->foreignId('preferred_service_id')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_haircut_date')->nullable();
            $table->string('hair_length', 50)->nullable();
            $table->string('hair_type', 50)->nullable();
            $table->timestamps();

            // We'll add the foreign key constraints for preferred_barber_id and preferred_service_id
            // after those tables are created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_preferences');
    }
};
