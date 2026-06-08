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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('filament')->after('customer_id');
            $table->string('order_status')->default('completed')->after('source');
            $table->timestamp('ready_at')->nullable()->after('claimed_at');
            $table->timestamp('completed_at')->nullable()->after('ready_at');
            $table->text('customer_note')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
