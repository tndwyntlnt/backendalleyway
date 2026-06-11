<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'orders_customer_created_idx');
            $table->index(['customer_id', 'status'], 'orders_customer_status_idx');
            $table->index(['customer_id', 'order_status'], 'orders_customer_order_status_idx');
            $table->index(['source', 'order_status', 'created_at'], 'orders_source_status_created_idx');
        });

        Schema::table('customer_rewards', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'customer_rewards_customer_created_idx');
            $table->index(['customer_id', 'status', 'expires_at'], 'customer_rewards_customer_status_expires_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'products_active_created_idx');
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'promos_active_created_idx');
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'rewards_active_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_created_idx');
            $table->dropIndex('orders_customer_status_idx');
            $table->dropIndex('orders_customer_order_status_idx');
            $table->dropIndex('orders_source_status_created_idx');
        });

        Schema::table('customer_rewards', function (Blueprint $table) {
            $table->dropIndex('customer_rewards_customer_created_idx');
            $table->dropIndex('customer_rewards_customer_status_expires_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_created_idx');
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->dropIndex('promos_active_created_idx');
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->dropIndex('rewards_active_created_idx');
        });
    }
};