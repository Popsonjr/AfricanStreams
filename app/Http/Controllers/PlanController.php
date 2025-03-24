<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use Unicodeveloper\Paystack\Facades\Paystack;
use App\Services\PaystackService;
use Illuminate\Routing\Controller;

// use Unicodeveloper\Paystack\Paystack;

class PlanController extends Controller
{
    protected $paystackService;
    
    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
        // $this->middleware('auth:api')->except(['handleGatewayCallback']);
    }
    
    public function index(Request $request) {
        $plans = Plan::all();
        return PlanResource::collection($plans);
    }    

    public function store(StorePlanRequest $request) {
        try {
            $data = $request->validated();

            $response = $this->paystackService->createPlan(
                $request->name,
                $request->amount,
                $request->interval
            );

            Log::info('Response from paystack', ['response' => $response]);
            if ($response['status']) {
                $plan = Plan::create([
                    'name' => $request->name,
                    'paystack_plan_code' => $response['data']['plan_code'],
                    'amount' => $request->amount,
                    'interval' => $request->interval,
                ]);
                $data = $response['data'];
                $plan = Plan::create([
                    'name' => $data['name'],
                    // 'duration_months' => $data['duration_months'],
                    'benefits' => $data['benefits'],
                    'amount' => $data['amount'],
                    'interval' => $data['interval'],
                    'active' => filter_var($data['active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
                    'paystack_plan_code' => $data['plan_code'],
            ]);

                return response()->json($plan, 201);
            }

            return response()->json(['message' => 'Failed to create plan on Paystack'], 500);

            // $paystackPlan =Paystack::createPlan([
            //     'name' => $data['name'],
            //     'amount' => $data['amount'],
            //     'interval' => $data['interval'],
            //     // 'descriptions' => $data['benefits'],
            // ]);

            // $plan = Plan::create([
            //     'name' => $data['name'],
            //     'duration_months' => $data['duration_months'],
            //     'benefits' => $data['benefits'],
            //     'amount' => $data['amount'],
            //     'interval' => $data['interval'],
            //     'active' => filter_var($data['active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            //     'paystack_plan_code' => $paystackPlan['data']['plan_code'],
            // ]);

            return new PlanResource($plan);
        } catch (\Exception $e) {
            Log::error('Error creating plan', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating plan'], 500);
        }
    }

    public function show(Plan $plan) {
        return new PlanResource($plan);
    }

    // public function update(UpdatePlanRequest $request, Plan $plan) {
    //     try {
    //         $data = $request->validated();
    //         $plan->update($data);

    //         // Update Paystack plan if needed
    //         if (isset($data['name']) || isset($data['amount']) || isset($data['interval'])) {
    //             Paystack::updatePlan($plan->paystack_plan_code, [
    //                 'name' => $data['name'] ?? $plan->name,
    //                 'amount' => $data['amount'] ?? $plan->amount,
    //                 'interval' => $data['interval'] ?? $plan->interval,
    //             ]);
    //         }

    //         return new PlanResource($plan);
    //     } catch (\Exception $e) {
    //         Log::error('Error updating plan', ['exception' => $e->getMessage()]);
    //         return response()->json(['message' => 'Error updating plan'], 500);
    //     }
    // }
    
    public function destroy(Plan $plan)
    {
        try {
            // Deactivate Paystack plan ??
            $plan->delete();
            return response()->json(['message' => 'Plan deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting plan', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting plan'], 500);
        }
    }
}