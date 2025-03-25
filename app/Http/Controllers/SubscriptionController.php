<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// use Unicodeveloper\Paystack\Paystack;

class SubscriptionController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
        // $this->middleware(['auth:api', 'role:admin'])->except(['index', 'show']);
        // $this->middleware('auth:api')->only(['index', 'show']);
    }

    public function index(Request $request)
    {
        // $subscriptions = Subscription::where('user_id', $request->user()->id)->get();
        // return SubscriptionResource::collection($subscriptions);

        try {
            $query = $request->user()->hasRole('admin')
                ? Subscription::query()
                : $request->user()->subscriptions();

            $subscriptions = $query
                ->with(['plan', 'user'])
                ->when($request->query('status'), fn($query, $status) => $query->where('status', $status))
                ->paginate(20);

            return response()->json([
                'page' => $subscriptions->currentPage(),
                'results' => $subscriptions,
                'total_pages' => $subscriptions->lastPage(),
                'total_results' => $subscriptions->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch subscriptions', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch subscriptions'], 500);
        }
    }

    public function store(StoreSubscriptionRequest $request)
    {
        try {
            $plan = Plan::findOrFail($request->plan_id);
            $user = \App\Models\User::findOrFail($request->user_id);

            // Check if user has a Paystack customer code
            $customerCode = $user->subscriptions()->latest()->first()?->paystack_customer_code;
            if (!$customerCode) {
                return response()->json(['message' => 'User must complete a payment first'], 400);
            }

            $response = $this->paystackService->createSubscription(
                $customerCode,
                $plan->paystack_plan_code
            );

            if ($response['status']) {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'paystack_subscription_code' => $response['data']['subscription_code'],
                    'paystack_customer_code' => $customerCode,
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                ]);

                return response()->json($subscription, 201);
            }

            return response()->json(['message' => 'Failed to create subscription on Paystack'], 500);
        } catch (\Exception $e) {
            Log::error('Failed to create subscription', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create subscription'], 500);
        }


        // try {
        //     $plan = Plan::findOrFail($request->validated()['plan_id']);
        //     $user = $request->user();

        //     // Initialize payment
        //     $paymentData = Paystack::initializePayment([
        //         'amount' => $plan->amount,
        //         'email' => $user->email,
        //         'plan' => $plan->paystack_plan_code,
        //         'callback_url' => route('subscriptions.verify'),
        //     ]);

        //     return response()->json([
        //         'message' => 'Payment initialized',
        //         'authorization_url' => $paymentData['data']['authorization_url'],
        //         'reference' => $paymentData['data']['reference'],
        //     ]);
        // } catch (\Exception $e) {
        //     Log::error('Error initializing subscription', ['exception' => $e->getMessage()]);
        //     return response()->json(['message' => 'Error initializing subscription'], 500);
        // }
    }

    public function verify(Request $request)
    {
        try {
            $reference = $request->query('reference');
            $payment = Paystack::verifyPayment($reference);

            if ($payment['data']['status'] === 'success') {
                $user = $request->user();
                $plan = Plan::where('paystack_plan_code', $payment['data']['plan'])->firstOrFail();

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonths($plan->duration_months),
                    'paystack_subscription_code' => $payment['data']['subscription_code'],
                    'paystack_subscription_token' => $payment['data']['token'],
                ]);

                return new SubscriptionResource($subscription);
            }

            return response()->json(['message' => 'Payment failed'], 400);
        } catch (\Exception $e) {
            Log::error('Error verifying subscription', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Error verifying subscription'], 500);
        }
    }

    public function destroy(Subscription $subscription)
    {
        try {
            if ($subscription->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            Paystack::disableSubscription([
                'code' => $subscription->paystack_subscription_code,
                'token' => $subscription->paystack_subscription_token,
            ]);

            $subscription->update(['status' => 'canceled', 'end_date' => now()]);
            return response()->json(['message' => 'Subscription canceled']);
        } catch (\Exception $e) {
            Log::error('Error canceling subscription', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Error canceling subscription'], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        // Verify Paystack signature
        $secret = config('paystack.secretKey');
        $signature = hash_hmac('sha512', $request->getContent(), $secret);

        if ($signature !== $request->header('x-paystack-signature')) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->all();
        Log::info('Paystack Webhook:', $event);

        switch ($event['event']) {
            case 'subscription.create':
                $subscription = Subscription::where('paystack_subscription_code', $event['data']['subscription_code'])->first();
                if ($subscription) {
                    $subscription->update([
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addMonths($subscription->plan->duration_months),
                    ]);
                }
                break;
            case 'subscription.disable':
                $subscription = Subscription::where('paystack_subscription_code', $event['data']['subscription_code'])->first();
                if ($subscription) {
                    $subscription->update(['status' => 'canceled', 'end_date' => now()]);
                }
                break;
            case 'charge.success':
                // Handle renewal payments
                $subscription = Subscription::where('paystack_subscription_code', $event['data']['subscription_code'])->first();
                if ($subscription) {
                    $subscription->update([
                        'end_date' => now()->addMonths($subscription->plan->duration_months),
                    ]);
                }
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}