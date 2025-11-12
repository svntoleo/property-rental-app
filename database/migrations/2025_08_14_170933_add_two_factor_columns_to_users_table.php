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
        // 2FA disabled: noop migration to preserve history without altering schema
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes in up(), nothing to rollback
    }
};
