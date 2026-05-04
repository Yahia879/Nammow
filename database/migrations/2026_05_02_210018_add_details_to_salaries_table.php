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
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('allowances', 12, 2)->default(0)->after('amount');
            $table->decimal('tax', 12, 2)->default(0)->after('allowances');
            $table->decimal('insurance', 12, 2)->default(0)->after('tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['allowances', 'tax', 'insurance']);
        });
    }
};
