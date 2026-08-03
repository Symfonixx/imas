<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;
use Modules\Support\Http\Requests\StorePublicContactRequest;
use Modules\Support\Repositories\ContactForm\ContactFormRepository;

class ContactUsController extends Controller
{
    public function __construct(
        private readonly ContactFormRepository $contactFormRepository,
        private readonly FrontViewData $frontViewData,
    ) {}

    public function index(): View
    {
        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();
        $translations = $this->frontViewData->getTranslations();
        $sectionTitle = front_trans('contact_us.title', $translations);

        return view('support::front.contact-us', [
            'contactStoreUrl' => route('support.contact-us.store'),
            'seo' => FrontSeo::forHub(
                $sectionTitle,
                $globals,
                $localeSwitcher,
                $appName,
                route('support.contact-us'),
            ),
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
