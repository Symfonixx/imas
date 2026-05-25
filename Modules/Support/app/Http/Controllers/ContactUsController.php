<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Support\Http\Requests\StorePublicContactRequest;
use Modules\Support\Repositories\ContactForm\ContactFormRepository;

class ContactUsController extends Controller
{
    public function __construct(
        private readonly ContactFormRepository $contactFormRepository,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Support::ContactUs', [
            'contactStoreUrl' => route('support.contact-us.store'),
        ]);
    }

    public function store(StorePublicContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $fullName = trim($validated['first_name'].' '.$validated['last_name']);

        $this->contactFormRepository->create([
            'ip_address' => $request->ip(),
            'name' => $fullName !== '' ? $fullName : $validated['first_name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()
            ->back()
            ->with('contact_sent', true);
    }
}
