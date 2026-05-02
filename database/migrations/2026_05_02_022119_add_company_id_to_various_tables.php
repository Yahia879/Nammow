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
        $tables = [
            'departments',
            'assets',
            'positions',
            'centers',
            'holidays',
            'fingerprints',
            'discounts',
            'employee_leave',
            'timelines',
            'categories',
            'sub_categories',
            'messages',
            'bulk_messages',
            'transitions',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'departments',
            'assets',
            'positions',
            'centers',
            'holidays',
            'fingerprints',
            'discounts',
            'employee_leave',
            'timelines',
            'categories',
            'sub_categories',
            'messages',
            'bulk_messages',
            'transitions',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
