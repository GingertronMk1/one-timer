<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiring_secrets', function (Blueprint $table) {
            $table->dropColumn('remaining_views');
        });
    }

    public function down(): void
    {
        Schema::table('expiring_secrets', function (Blueprint $table) {
            $table->unsignedInteger('remaining_views')->default(1);
        });
    }
};
