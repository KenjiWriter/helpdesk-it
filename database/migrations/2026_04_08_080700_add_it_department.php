<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Department::firstOrCreate(['name' => 'IT']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Department::where('name', 'IT')->delete();
    }
};
