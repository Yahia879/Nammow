<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create pivot table
        Schema::create('company_manager_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_manager_id')->constrained('company_managers')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Transfer data
        $managers = DB::table('company_managers')->get();
        foreach ($managers as $manager) {
            if ($manager->company_id) {
                DB::table('company_manager_company')->insert([
                    'company_manager_id' => $manager->id,
                    'company_id' => $manager->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Remove company_id from company_managers
        Schema::table('company_managers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_managers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
        });

        $pivots = DB::table('company_manager_company')->get();
        foreach ($pivots as $pivot) {
            DB::table('company_managers')
                ->where('id', $pivot->company_manager_id)
                ->update(['company_id' => $pivot->company_id]);
        }

        Schema::dropIfExists('company_manager_company');
    }
};
