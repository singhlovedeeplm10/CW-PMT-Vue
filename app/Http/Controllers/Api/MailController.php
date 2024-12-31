<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class MailController extends Controller
{
    public function sendTestEmail()
    {
        try {
            // Sending the email
            Mail::to('rajputbharat1623@gmail.com
')->send(new TestMail());

            // Return success response
            return response()->json(['message' => 'Email sent successfully.'], 200);
        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return response()->json(['message' => 'Failed to send email. ' . $e->getMessage()], 500);
        }
    }
}
