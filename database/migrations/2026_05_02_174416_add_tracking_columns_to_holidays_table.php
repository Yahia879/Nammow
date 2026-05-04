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
        Schema::table('holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('holidays', 'created_by')) {
                $table->string('created_by')->nullable()->after('scope');
            }
            if (!Schema::hasColumn('holidays', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('holidays', 'deleted_by')) {
                $table->string('deleted_by')->nullable()->after('updated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }
};
