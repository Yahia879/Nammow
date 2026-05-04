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
        Schema::table('employees', function (Blueprint $table) {
            // New fields
            if (!Schema::hasColumn('employees', 'full_name')) {
                $table->string('full_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('employees', 'basic_salary')) {
                $table->decimal('basic_salary', 10, 2)->nullable()->after('address');
            }
            if (!Schema::hasColumn('employees', 'housing_allowance')) {
                $table->decimal('housing_allowance', 10, 2)->nullable()->after('basic_salary');
            }
            if (!Schema::hasColumn('employees', 'transport_allowance')) {
                $table->decimal('transport_allowance', 10, 2)->nullable()->after('housing_allowance');
            }
            if (!Schema::hasColumn('employees', 'other_allowances')) {
                $table->decimal('other_allowances', 10, 2)->nullable()->after('transport_allowance');
            }
            if (!Schema::hasColumn('employees', 'join_date')) {
                $table->date('join_date')->nullable()->after('other_allowances');
            }

            // Make old fields nullable
            $table->foreignId('contract_id')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('father_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('mother_name')->nullable()->change();
            $table->string('birth_and_place')->nullable()->change();
            $table->string('national_number')->nullable()->change();
            $table->string('degree')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('profile_photo_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'basic_salary', 'housing_allowance', 'transport_allowance', 'other_allowances', 'join_date']);
        });
    }
};
