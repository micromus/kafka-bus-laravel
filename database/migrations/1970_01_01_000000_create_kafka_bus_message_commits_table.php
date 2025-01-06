<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('kafka_bus_message_commits', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('topic_name');
            $table->string('key')->nullable();
            $table->unsignedBigInteger('timestamp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kafka_message_commits');
    }
};
