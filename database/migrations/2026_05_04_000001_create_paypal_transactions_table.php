<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            
            // PayPal subscription or order IDs
            $table->string('subscription_id')->nullable()->unique()->comment('PayPal subscription ID for recurring payments');
            $table->string('order_id')->nullable()->unique()->comment('PayPal order ID for one-time orders');
            $table->string('billing_plan_id')->nullable()->comment('PayPal billing plan ID used');
            
            // Billing details
            $table->enum('interval', ['monthly', 'yearly'])->comment('Billing interval');
            $table->decimal('amount', 10, 2)->comment('Amount charged');
            $table->string('currency', 3)->default('USD')->comment('Currency code');
            
            // Status tracking
            $table->enum('status', [
                'pending',      // Initial state, awaiting approval
                'active',       // Active subscription
                'suspended',    // Temporarily suspended
                'cancelled',    // Cancelled by user
                'expired',      // Expired
                'failed',       // Payment failed
            ])->default('pending')->comment('Transaction/subscription status');
            
            // Payment tracking
            $table->string('payer_email')->nullable()->comment('PayPal payer email');
            $table->string('payer_id')->nullable()->comment('PayPal payer ID');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Additional PayPal response data');
            $table->text('error_message')->nullable()->comment('Error message if payment failed');
            
            // Date tracking
            $table->timestamp('activated_at')->nullable()->comment('When subscription became active');
            $table->timestamp('cancelled_at')->nullable()->comment('When subscription was cancelled');
            $table->timestamp('next_billing_at')->nullable()->comment('Next billing date');
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['subscription_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paypal_transactions');
    }
};
