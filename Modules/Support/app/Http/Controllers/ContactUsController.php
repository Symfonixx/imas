<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Support\Http\Requests\StorePublicContactRequest;
use Modules\Support\Repositories\ContactForm\ContactFormRepository;

class ContactUsController extends Controller
{
    public function __construct(
        private readonly ContactFormRepository $contactFormRepository,
    ) {}

    public function index(): Response
    {
        $seoService = app(SeoDocumentService::class);
        $pageTitle = $seoService->labelFromBaseLang('contact_us.title', 'Connect With Us Today');

        return Inertia::render('Support::ContactUs', [
            'contactStoreUrl' => route('support.contact-us.store'),
        ])->withViewData([
            'seo' => $seoService->documentSeo([
                'page_title' => $pageTitle,
                'og_image' => $seoService->settingsImageUrl('contact_us_banner'),
                'canonical' => route('support.contact-us'),
            ]),
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
            'source_url' => $validated['source_url'] ?? null,
            'source_page' => $validated['source_page'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()
            ->back()
            ->with('contact_sent', true);
    }
}
