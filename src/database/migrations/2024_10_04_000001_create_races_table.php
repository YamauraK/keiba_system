<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->date('race_date');
            $table->string('race_name')->nullable();
            $table->string('racecourse');
            $table->enum('course_type', ['芝', 'ダート'])->nullable();
            $table->string('weather')->nullable();
            $table->string('track_condition')->nullable();
            $table->unsignedInteger('distance');
            $table->enum('direction', ['右', '左'])->nullable();
            $table->unsignedTinyInteger('number_of_turns')->nullable();
            $table->unsignedTinyInteger('number_of_runners');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
