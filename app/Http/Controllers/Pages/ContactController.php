<?php

namespace App\Http\Controllers\Pages;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('contact');
    }

    public function submit(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Mail::to('web@spillhuset.com')->send(
            new ContactMail(
                userName: $data['name'],
                userEmail: $data['email'],
                subjectLine: $data['subject'],
                messageBody: $data['message']
            )
        );

        return redirect()->route('contact.show')->with('success', 'Message sent. We will get back to you soon.');
    }
}
