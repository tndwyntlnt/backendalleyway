<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'supabase_user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->uuid('supabase_user_id')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'supabase_user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique(['supabase_user_id']);
                $table->dropColumn('supabase_user_id');
            });
        }
    }
};