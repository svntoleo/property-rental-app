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
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->nullable(false)->constrained()->cascadeOnDelete();
            $table->foreignId('stay_category_id')->nullable(false)->constrained();
            $table->decimal('price', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('due_date')->nullable()->comment('Day of month (1-31) when rent is due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};
