<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('kafka_bus_message_fails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('worker_name');
            $table->string('topic_name');
            $table->text('payload');
            $table->json('headers');
            $table->string('key')->nullable();
            $table->unsignedSmallInteger('partition');
            $table->unsignedBigInteger('offset');
            $table->unsignedBigInteger('timestamp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kafka_message_fails');
    }
};
