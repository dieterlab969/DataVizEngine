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
            $table->integer('table_index');
            $table->integer('row_index');
            $table->string('label')->nullable();
            $table->decimal('numeric_value', 15, 6)->nullable();
            $table->string('numeric_column');
            $table->string('raw_label_value')->nullable();
            $table->string('raw_numeric_value')->nullable();
            $table->json('full_row_data')->nullable();
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
