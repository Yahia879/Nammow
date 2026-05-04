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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('companies', 'cr_number')) {
                $table->string('cr_number')->nullable()->after('owner_name');
            }
            if (!Schema::hasColumn('companies', 'unified_number')) {
                $table->string('unified_number')->nullable()->after('cr_number');
            }
            if (!Schema::hasColumn('companies', 'attestation_date')) {
                $table->date('attestation_date')->nullable()->after('unified_number');
            }
            if (!Schema::hasColumn('companies', 'attestation_expiry_date')) {
                $table->date('attestation_expiry_date')->nullable()->after('attestation_date');
            }
            if (!Schema::hasColumn('companies', 'cr_image')) {
                $table->string('cr_image')->nullable()->after('attestation_expiry_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'cr_number',
                'unified_number',
                'attestation_date',
                'attestation_expiry_date',
                'cr_image'
            ]);
        });
    }
};
