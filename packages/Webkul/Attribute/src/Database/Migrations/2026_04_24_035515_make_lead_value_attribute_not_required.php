<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->where('code', 'lead_value')
            ->update(['is_required' => 0]);
    }

    public function down(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->where('code', 'lead_value')
            ->update(['is_required' => 1]);
    }
};
