<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Show the contact form
    public function index()
    {
        return view('contact'); // Make sure 'contact.blade.php' exists in resources/views
    }

    // Handle form submission
    public function saveContactInfo(Request $request)
    {
        // Validate form input
        return response()->json([
            'message' => 'Message sent successfully!',
            'dat' => $request
        ], 200);
    }
}
