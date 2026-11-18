<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->where('code', 'lead_value')
            ->update(['is_required' => 0]);

        $now = now();

        $attributes = [
            [
                'code'       => 'estimated_departure_date',
                'name'       => trans('installer::app.seeders.attributes.leads.estimated-departure'),
                'sort_order' => 11,
            ],
            [
                'code'       => 'estimated_arrival_date',
                'name'       => trans('installer::app.seeders.attributes.leads.estimated-arrival'),
                'sort_order' => 12,
            ],
        ];

        foreach ($attributes as $attribute) {
            if (DB::table('attributes')
                ->where('entity_type', 'leads')
                ->where('code', $attribute['code'])
                ->exists()
            ) {
                continue;
            }

            DB::table('attributes')->insert([
                'code'            => $attribute['code'],
                'name'            => $attribute['name'],
                'type'            => 'date',
                'entity_type'     => 'leads',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $attribute['sort_order'],
                'is_required'     => 0,
                'is_unique'       => 0,
                'quick_add'       => 1,
                'is_user_defined' => 0,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->whereIn('code', ['estimated_departure_date', 'estimated_arrival_date'])
            ->delete();

        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->where('code', 'lead_value')
            ->update(['is_required' => 1]);
    }
};
