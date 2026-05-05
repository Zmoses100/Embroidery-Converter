<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected string $currency;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.paypal.base_url'), '/');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->currency = config('services.paypal.currency', 'USD');
    }

    public function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        if (!$this->clientId || !$this->clientSecret) {
            Log::error('PayPal credentials not configured', [
                'has_client_id' => (bool) $this->clientId,
                'has_client_secret' => (bool) $this->clientSecret,
            ]);

            return null;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                Log::error('PayPal authentication failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'base_url' => $this->baseUrl,
                ]);

                return null;
            }

            $this->accessToken = $response->json('access_token');

            if (!$this->accessToken) {
                Log::error('PayPal authentication failed: access token missing', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $this->accessToken;
        } catch (\Throwable $e) {
            Log::error('PayPal authentication exception', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
            ]);

            return null;
        }
    }

    public function ensureProduct(Plan $plan): ?string
    {
        if ($plan->paypal_product_id) {
            return $plan->paypal_product_id;
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Prefer' => 'return=representation',
                ])
                ->post($this->baseUrl . '/v1/catalogs/products', [
                    'name' => $plan->name . ' Plan',
                    'description' => $plan->description ?: $plan->name . ' subscription plan',
                    'type' => 'SERVICE',
                    'category' => 'SOFTWARE',
                ]);

            if (!$response->successful()) {
                Log::error('Failed to create PayPal product', [
                    'plan_id' => $plan->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $productId = $response->json('id');

            if (!$productId) {
                Log::error('PayPal product creation failed: product id missing', [
                    'plan_id' => $plan->id,
                    'body' => $response->body(),
                ]);

                return null;
            }

            $plan->update([
                'paypal_product_id' => $productId,
            ]);

            Log::info('PayPal product created', [
                'plan_id' => $plan->id,
                'product_id' => $productId,
            ]);

            return $productId;
        } catch (\Throwable $e) {
            Log::error('Failed to create PayPal product exception', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function ensureBillingPlan(Plan $plan, string $interval): ?string
    {
        $interval = $interval === 'yearly' ? 'yearly' : 'monthly';

        $planIdColumn = $interval === 'yearly'
            ? 'paypal_plan_id_yearly'
            : 'paypal_plan_id_monthly';

        if ($plan->{$planIdColumn}) {
            return $plan->{$planIdColumn};
        }

        $productId = $this->ensureProduct($plan);

        if (!$productId) {
            return null;
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Prefer' => 'return=representation',
                ])
                ->post($this->baseUrl . '/v1/billing/plans', [
                    'product_id' => $productId,
                    'name' => $plan->name . ' - ' . ucfirst($interval),
                    'status' => 'ACTIVE',
                    'description' => 'Subscription plan for ' . $plan->name,
                    'billing_cycles' => [
                        [
                            'frequency' => [
                                'interval_unit' => $interval === 'yearly' ? 'YEAR' : 'MONTH',
                                'interval_count' => 1,
                            ],
                            'tenure_type' => 'REGULAR',
                            'sequence' => 1,
                            'total_cycles' => 0,
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'value' => $this->getPrice($plan, $interval),
                                    'currency_code' => $this->currency,
                                ],
                            ],
                        ],
                    ],
                    'payment_preferences' => [
                        'auto_bill_outstanding' => true,
                        'setup_fee_failure_action' => 'CONTINUE',
                        'payment_failure_threshold' => 3,
                    ],
                    'taxes' => [
                        'percentage' => '0',
                        'inclusive' => false,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Failed to create PayPal billing plan', [
                    'plan_id' => $plan->id,
                    'interval' => $interval,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $billingPlanId = $response->json('id');

            if (!$billingPlanId) {
                Log::error('PayPal billing plan creation failed: plan id missing', [
                    'plan_id' => $plan->id,
                    'interval' => $interval,
                    'body' => $response->body(),
                ]);

                return null;
            }

            $plan->update([
                $planIdColumn => $billingPlanId,
            ]);

            Log::info('PayPal billing plan created', [
                'plan_id' => $plan->id,
                'billing_plan_id' => $billingPlanId,
                'interval' => $interval,
            ]);

            return $billingPlanId;
        } catch (\Throwable $e) {
            Log::error('Failed to create PayPal billing plan exception', [
                'plan_id' => $plan->id,
                'interval' => $interval,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function createSubscriptionOrder(Plan $plan, string $interval, string $returnUrl, string $cancelUrl): ?array
    {
        $interval = $interval === 'yearly' ? 'yearly' : 'monthly';

        $billingPlanId = $this->ensureBillingPlan($plan, $interval);

        if (!$billingPlanId) {
            return null;
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Prefer' => 'return=representation',
                ])
                ->post($this->baseUrl . '/v1/billing/subscriptions', [
                    'plan_id' => $billingPlanId,
                    'subscriber' => [
                        'email_address' => auth()->user()->email,
                    ],
                    'application_context' => [
                        'brand_name' => config('app.name', 'Embroidery Converter'),
                        'locale' => 'en-US',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'SUBSCRIBE_NOW',
                        'payment_method' => [
                            'payer_selected' => 'PAYPAL',
                            'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        ],
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Failed to create PayPal subscription', [
                    'user_id' => auth()->id(),
                    'plan_id' => $plan->id,
                    'interval' => $interval,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $subscriptionId = $response->json('id');
            $links = $response->json('links') ?? [];
            $approvalUrl = null;

            foreach ($links as $link) {
                if (($link['rel'] ?? null) === 'approve') {
                    $approvalUrl = $link['href'] ?? null;
                    break;
                }
            }

            if (!$subscriptionId || !$approvalUrl) {
                Log::error('PayPal subscription missing approval link', [
                    'user_id' => auth()->id(),
                    'plan_id' => $plan->id,
                    'interval' => $interval,
                    'body' => $response->body(),
                ]);

                return null;
            }

            Log::info('PayPal subscription created', [
                'user_id' => auth()->id(),
                'plan_id' => $plan->id,
                'interval' => $interval,
                'subscription_id' => $subscriptionId,
            ]);

            return [
                'subscription_id' => $subscriptionId,
                'approval_url' => $approvalUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to create PayPal subscription exception', [
                'user_id' => auth()->id(),
                'plan_id' => $plan->id,
                'interval' => $interval,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId);

            if (!$response->successful()) {
                Log::error('Failed to get PayPal subscription', [
                    'subscription_id' => $subscriptionId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Failed to get PayPal subscription exception', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function activateSubscription(string $subscriptionId): bool
    {
        $subscription = $this->getSubscription($subscriptionId);

        if (!$subscription) {
            return false;
        }

        $status = strtoupper($subscription['status'] ?? '');

        return in_array($status, ['ACTIVE', 'APPROVAL_PENDING'], true);
    }

    public function cancelSubscription(string $subscriptionId, string $reason = 'User cancelled'): bool
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->post($this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel', [
                    'reason' => $reason,
                ]);

            if (!$response->successful()) {
                Log::error('Failed to cancel PayPal subscription', [
                    'subscription_id' => $subscriptionId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            Log::info('PayPal subscription cancelled', [
                'subscription_id' => $subscriptionId,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to cancel PayPal subscription exception', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function getPrice(Plan $plan, string $interval): string
    {
        $price = $interval === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        return number_format((float) $price, 2, '.', '');
    }
}