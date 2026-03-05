<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('blocked_date');
            $table->timestamps();

            // 同一部屋・同一日のブロックは1つだけ
            $table->unique(['room_id', 'blocked_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_blocks');
    }
};
