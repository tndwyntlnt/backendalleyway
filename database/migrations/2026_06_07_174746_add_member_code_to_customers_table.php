<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('member_code')->nullable()->unique()->after('id');
        });

        DB::table('customers')
            ->whereNull('member_code')
            ->orderBy('id')
            ->get()
            ->each(function ($customer) {
                do {
                    $code = 'ALW-' . strtoupper(Str::random(6));
                } while (
                    DB::table('customers')->where('member_code', $code)->exists()
                );

                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['member_code' => $code]);
            });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('member_code')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('member_code');
        });
    }
};