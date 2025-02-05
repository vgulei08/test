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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'message' => 'required|string',
            'street' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
            'images' => 'array', // Expecting an array of images
            'files' => 'array',  // Expecting an array of files
        ]);

        // Handle image uploads
        if ($request->has('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('images', 'public'); // Store images in the 'public/images' directory
            }
            $validatedData['images'] = json_encode($imagePaths); // Store paths as JSON
        }

        // Handle file uploads
        if ($request->has('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store('files', 'public'); // Store files in the 'public/files' directory
            }
            $validatedData['files'] = json_encode($filePaths); // Store paths as JSON
        }

        // Save to the database
        \App\Models\Contact::create($validatedData);

        return response()->json([
            'message' => 'Message sent successfully!',
            'data' => $validatedData
        ], 200);
    }
}
