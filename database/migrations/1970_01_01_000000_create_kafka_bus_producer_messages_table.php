<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('kafka_bus_producer_messages', function (Blueprint $table) {
            $table->id();
            $table->string('connection_name');
            $table->string('topic_name');
            $table->text('payload');
            $table->json('headers');
            $table->string('key')->nullable();
            $table->smallInteger('partition')->default(-1);
            $table->json('additional_options')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kafka_producer_messages');
    }
};
