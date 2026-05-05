<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // PayPal product IDs (needed to create subscriptions)
            $table->string('paypal_product_id')->nullable()->comment('PayPal product ID for this plan');
            
            // PayPal plan IDs for monthly and yearly billing
            $table->string('paypal_plan_id_monthly')->nullable()->comment('PayPal subscription plan ID for monthly billing');
            $table->string('paypal_plan_id_yearly')->nullable()->comment('PayPal subscription plan ID for yearly billing');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_product_id',
                'paypal_plan_id_monthly',
                'paypal_plan_id_yearly',
            ]);
        });
    }
};
