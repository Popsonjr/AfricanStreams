<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
        $this->middleware('auth:api')->except(['handleGatewayCallback']);
    }

    public function redirectToGateway(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'email' => 'required|email',
            'amount' => 'required|numeric|min:100',
        ]);

        try {
            $plan = Plan::findOrFail($request->plan_id);
            $reference = Str::random(16);
            $callbackUrl = url('/payment/callback');

            $response = $this->paystackService->initializeTransaction(
                $request->email,
                $request->amount,
                $reference,
                $callbackUrl
            );

            if ($response['status'] && isset($response['data']['authorization_url'])) {
                Subscription::create([
                    'user_id' => $request->user()->id,
                    'plan_id' => $plan->id,
                    'paystack_subscription_code' => null,
                    'paystack_customer_code' => null,
                    'status' => 'pending',
                ]);

                return response()->json([
                    'authorization_url' => $response['data']['authorization_url'],
                ]);
            }

            return response()->json(['message' => 'Failed to initialize payment'], 500);
        } catch (\Exception $e) {
            Log::error('Payment initialization failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payment initialization failed'], 500);
        }
    }

    public function handleGatewayCallback(Request $request)
    {
        try {
            $reference = $request->query('reference');
            if (!$reference) {
                return response()->json(['message' => 'Missing transaction reference'], 400);
            }

            $response = $this->paystackService->verifyTransaction($reference);
            if ($response['status'] && $response['data']['status'] === 'success') {
                $customerCode = $response['data']['customer']['customer_code'];
                $subscription = Subscription::where('user_id', auth('api')->id())->latest()->first();
                if (!$subscription) {
                    return response()->json(['message' => 'Subscription not found'], 404);
                }
                $plan = $subscription->plan;

                $subscriptionResponse = $this->paystackService->createSubscription(
                    $customerCode,
                    $plan->paystack_plan_code
                );

                if ($subscriptionResponse['status']) {
                    $subscription->update([
                        'paystack_subscription_code' => $subscriptionResponse['data']['subscription_code'],
                        'paystack_customer_code' => $customerCode,
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addMonth(),
                    ]);

                    return response()->json(['message' => 'Subscription created successfully']);
                }

                return response()->json(['message' => 'Failed to create subscription'], 500);
            }

            return response()->json(['message' => 'Transaction failed'], 400);
        } catch (\Exception $e) {
            Log::error('Payment callback failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payment callback failed'], 500);
        }
    }
}