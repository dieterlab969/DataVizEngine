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
        Schema::create('table_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('page_request_id')->constrained()->onDelete('cascade');
            $table->string('column_name');
            $table->json('values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_data');
    }
};
