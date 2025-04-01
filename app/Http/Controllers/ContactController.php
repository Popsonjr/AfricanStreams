<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageAdmin;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'company' => 'nullable|string|max:255',
        ]);

        try {
            // Save contact message
            $contactMessage = ContactMessage::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'company' => $request->company,
            ]);

            // Send confirmation email to sender
            Mail::to($request->email)->send(new ContactMessageReceived($request->fullname, $request->subject));

            // Send notification email to admin
            Mail::to('admin@africanstreams.com')->send(new ContactMessageAdmin(
                $request->fullname,
                $request->email,
                $request->subject,
                $request->message,
                $request->company
            ));

            return response()->json(['message' => 'Your message has been received'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to process contact message', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to process your message'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $messages = ContactMessage::orderBy('submitted_at', 'desc')
                ->paginate(20);

            return response()->json([
                'page' => $messages->currentPage(),
                'results' => $messages,
                'total_pages' => $messages->lastPage(),
                'total_results' => $messages->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch contact messages', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch contact messages'], 500);
        }
    }
}