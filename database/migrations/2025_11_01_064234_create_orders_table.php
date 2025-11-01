<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            $table->string('transaction_code')->unique(); 
            
            $table->bigInteger('total_amount'); 
            $table->integer('points_earned'); 
            
            $table->string('status')->default('unclaimed');

            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();
            
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};