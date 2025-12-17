<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Billing', [
            'hasActiveSubscription' => $user->subscribed('default'),
            'onGracePeriod' => $user->subscription('default')?->onGracePeriod() ?? false,
            'stripePortalEnabled' => true,
            'priceId' => (string) (config('services.stripe.price_id') ?? ''),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'price' => ['required', 'string'],
        ]);

        $user = $request->user();

        $successUrl = url('/settings/billing/success');
        $cancelUrl = url('/settings/billing/cancel');

        $priceId = $request->string('price');

        $checkout = $user
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'ui_mode' => 'hosted',
                // Normalize locale to Stripe supported set
                'locale' => $this->normalizeStripeLocale(),
            ]);

        // Cashier's Checkout returns an object that can directly redirect (303)
        return $checkout->redirect();
    }

    public function success(): RedirectResponse
    {
        return redirect()->route('settings.billing');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('settings.billing');
    }

    public function portal(Request $request): RedirectResponse
    {
        $returnUrl = url('/settings/billing');

        return $request->user()->redirectToBillingPortal($returnUrl);
    }

    /**
     * Map the application locale to a Stripe supported locale.
     */
    protected function normalizeStripeLocale(): string
    {
        $appLocale = (string) app()->getLocale();

        // Map common aliases
        $map = [
            'no' => 'nb', // Norwegian → Bokmål
            'nn' => 'nb', // Nynorsk → fallback to Bokmål (Stripe supports nb)
            'nb' => 'nb',
            'en_US' => 'en',
            'en_GB' => 'en-GB',
            'es_ES' => 'es',
            'pt_PT' => 'pt',
            'pt_BR' => 'pt-BR',
            'zh_CN' => 'zh',
            'zh_TW' => 'zh-TW',
            'zh_HK' => 'zh-HK',
        ];

        $candidate = $map[$appLocale] ?? $appLocale;

        // Stripe supported locales (as of 2025-12), plus 'auto'
        $supported = [
            'auto', 'bg', 'cs', 'da', 'de', 'el', 'en', 'en-GB', 'es', 'es-419', 'et', 'fi', 'fil', 'fr', 'fr-CA',
            'hr', 'hu', 'id', 'it', 'ja', 'ko', 'lt', 'lv', 'ms', 'mt', 'nb', 'nl', 'pl', 'pt', 'pt-BR', 'ro', 'ru',
            'sk', 'sl', 'sv', 'th', 'tr', 'vi', 'zh', 'zh-HK', 'zh-TW', 'he',
        ];

        return in_array($candidate, $supported, true) ? $candidate : 'auto';
    }
}
