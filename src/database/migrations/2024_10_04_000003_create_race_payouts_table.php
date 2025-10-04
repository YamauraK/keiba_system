<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->string('bet_type');
            $table->string('combination')->nullable();
            $table->decimal('odds', 8, 2)->nullable();
            $table->unsignedInteger('payout')->nullable();
            $table->timestamps();

            $table->index(['race_id', 'bet_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_payouts');
    }
};
