<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('frame_number');
            $table->unsignedTinyInteger('horse_number');
            $table->string('horse_name')->nullable();
            $table->string('sex')->nullable();
            $table->string('running_style')->nullable();
            $table->unsignedTinyInteger('popularity')->nullable();
            $table->unsignedTinyInteger('finish_position')->nullable();
            $table->decimal('win_odds', 8, 2)->nullable();
            $table->unsignedInteger('win_payout')->nullable();
            $table->unsignedInteger('place_payout')->nullable();
            $table->timestamps();

            $table->index(['race_id', 'finish_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_entries');
    }
};
