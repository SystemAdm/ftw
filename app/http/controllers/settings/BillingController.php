<?php

namespace App\http\controllers\settings;

use App\http\controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        return Inertia::render('settings/Billing', [
            'hasActiveSubscription' => $user->subscribed('default'),
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'stripePortalEnabled' => true,
            'priceId' => (string) (config('services.stripe.price_id') ?? ''),
            'time_left' => $user->getSubscriptionTimeLeft(),
            'next_billing_date' => $user->getSubscriptionNextBillingDate(),
        ]);
    }

    public function checkout(Request $request): SymfonyResponse
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

        return Inertia::location($checkout->redirect()->getTargetUrl());
    }

    public function success(Request $request): RedirectResponse
    {
        if ($sessionId = $request->get('session_id')) {
            $user = $request->user();
            $stripe = $user->stripe();

            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if (! $user->stripe_id) {
                $user->update(['stripe_id' => $session->customer]);
            }

            // If this was a subscription checkout, try to pull the subscription immediately
            if ($session->subscription) {
                try {
                    $stripeSubscription = $stripe->subscriptions->retrieve($session->subscription);

                    $subscription = $user->subscriptions()->updateOrCreate([
                        'stripe_id' => $stripeSubscription->id,
                    ], [
                        'type' => 'default',
                        'stripe_status' => $stripeSubscription->status,
                        'stripe_price' => $stripeSubscription->items->data[0]->price->id ?? null,
                        'quantity' => $stripeSubscription->items->data[0]->quantity ?? null,
                        'trial_ends_at' => $stripeSubscription->trial_end ? \Illuminate\Support\Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                        'ends_at' => null,
                    ]);

                    // Update items as well to be thorough
                    foreach ($stripeSubscription->items->data as $item) {
                        $subscription->items()->updateOrCreate([
                            'stripe_id' => $item->id,
                        ], [
                            'stripe_product' => $item->price->product,
                            'stripe_price' => $item->price->id,
                            'quantity' => $item->quantity ?? null,
                        ]);
                    }
                } catch (\Exception $e) {
                    // Fallback to sync existing if retrieval failed or other error
                }
            }

            // Also try to sync any existing incomplete subscriptions just in case
            try {
                $user->subscriptions()->where('stripe_status', 'incomplete')->each(function ($subscription) {
                    $subscription->syncStripeStatus();
                });
            } catch (\Exception $e) {
                // Ignore errors here, webhook will eventually sync it.
            }
        }

        return redirect()->route('settings.billing')->with('status', __('pages.settings.billing.messages.success'));
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('settings.billing');
    }

    public function portal(Request $request): SymfonyResponse
    {
        $returnUrl = url('/settings/billing');

        return Inertia::location($request->user()->billingPortalUrl($returnUrl));
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
