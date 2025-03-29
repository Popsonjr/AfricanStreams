<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeNewsletter;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request) {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $email = $request->email;

            //check if already subscribed
            if (Newsletter::where('email', $email)->exists()) {
                return response()->json(['message' => 'You are already subscribed to our newsletter', 409]);
            }

            // save subscription
            $newsletter = Newsletter::create([
                'email' => $email,
            ]);

            // send welcome email
            Mail::to($email)->send(new WelcomeNewsletter($email));
            return response()->json(['message' => 'Successfully subscribedto the newsletter'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to subscribe to newsletter', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to subscribe to newsletter'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $subscribers = Newsletter::orderBy('subscribed_at', 'desc')
                ->paginate(20);

            return response()->json([
                'page' => $subscribers->currentPage(),
                'results' => $subscribers,
                'total_pages' => $subscribers->lastPage(),
                'total_results' => $subscribers->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch newsletter subscribers', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch subscribers'], 500);
        }
    }
}