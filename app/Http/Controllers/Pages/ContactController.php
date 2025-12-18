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
        return Inertia::render('contact', [
            'captcha' => [
                'turnstile_site_key' => (string) (config('services.turnstile.site_key') ?? ''),
            ],
        ]);
    }

    public function submit(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            Mail::to('web@spillhuset.com')->send(
                new ContactMail(
                    userName: $data['name'],
                    userEmail: $data['email'],
                    subjectLine: $data['subject'],
                    messageBody: $data['message']
                )
            );

            return redirect()->route('contact.show')->with('status', 'Message sent. We will get back to you soon.');
        } catch (\Throwable $e) {
            report($e);

            // Flash error separately so UI can show a red toast/banner
            return back()->withInput()->with('error', 'We could not send the email right now. Please try again later. You may send us e-mail through your own mail-client instead. <a href="mailto://post@spillhuset.com?Kontakt&nbsp;Oss">post@spillhuset.com</a>');
        }
    }
}
