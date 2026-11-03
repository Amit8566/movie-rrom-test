<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('video_url');
            $table->string('host_token', 64);
            $table->string('host_name');
            $table->enum('status', ['waiting', 'playing', 'paused', 'ended'])->default('waiting');
            $table->float('current_time')->default(0);
            $table->boolean('chat_enabled')->default(true);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

