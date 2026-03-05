<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category', 50)->nullable()->default('info');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_pinned_dashboard')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('is_pinned_dashboard');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
