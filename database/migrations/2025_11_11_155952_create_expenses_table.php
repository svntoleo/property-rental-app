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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            // Optional: expense may apply to whole property without accommodation
            $table->foreignId('accommodation_id')->nullable()->constrained()->cascadeOnDelete();
            // Optional: uncategorized expenses allowed; if category deleted set null
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 255);
            $table->decimal('price', 10, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
