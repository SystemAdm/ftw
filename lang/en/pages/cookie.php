<?php

return [
    'title' => 'Cookie policy',
    'updated' => 'Last updated: 2025-12-17',
    'summary_title' => 'In short',
    'summary' => [
        'essential' => 'We use only essential and basic preference cookies to run the site.',
        'nomarketing' => 'No marketing/advertising cookies.',
        'stripe' => 'Stripe may set their own cookies during checkout.',
    ],
    'toc' => [
        'title' => 'Contents',
        'what' => 'What are cookies?',
        'types' => 'Types of cookies we use',
        'list' => 'Cookies we use',
        'third' => 'Third‑party cookies (Stripe)',
        'manage' => 'How to manage cookies',
        'withdraw' => 'Withdraw consent',
        'contact' => 'Contact',
        'changes' => 'Changes to this policy',
    ],
    'what' => [
        'heading' => 'What are cookies?',
        'body' => 'Cookies are small text files stored on your device by your browser. They help websites remember information about your visit, like keeping you signed in or remembering preferences.',
    ],
    'types' => [
        'heading' => 'Types of cookies we use',
        'items' => [
            'necessary' => 'Strictly necessary: required for core functionality such as authentication and security.',
            'prefs' => 'Preferences: remembers your appearance (light/dark/system) and UI state like the sidebar.',
            'analytics' => 'Analytics/Marketing: not used.',
        ],
    ],
    'list' => [
        'heading' => 'Cookies we use',
        'xsrf' => 'XSRF‑TOKEN: helps protect against cross‑site request forgery. Required for forms and authentication.',
        'session' => 'laravel_session: session cookie that keeps you signed in and remembers state between requests.',
        'appearance' => 'appearance: stores your theme preference. Set by our middleware.',
        'sidebar' => 'sidebar_state: remembers whether the sidebar is open or collapsed.',
        'consent' => 'laravel_cookie_consent: records whether you dismissed/accepted the cookie notice.',
        'note' => 'Note: Cookie names may vary slightly between environments.',
    ],
    'third' => [
        'heading' => 'Third‑party cookies (Stripe)',
        'body' => 'When you start the subscription checkout, you may be redirected to Stripe. Stripe may set its own cookies to operate their checkout experience.',
    ],
    'manage' => [
        'heading' => 'How to manage cookies',
        'body' => 'You can control cookies in your browser settings and delete existing cookies. Disabling necessary cookies may affect site functionality (e.g., sign‑in will not work).',
    ],
    'withdraw' => [
        'heading' => 'Withdraw consent',
        'body' => 'If you consented to optional cookies in the past, you can withdraw your consent at any time by clearing cookies in your browser or using cookie controls if shown.',
    ],
    'contact' => [
        'heading' => 'Contact',
        'body' => 'Questions about this policy? Email web@spillhuset.com or use our contact form.',
    ],
    'changes' => [
        'heading' => 'Changes to this policy',
        'body' => 'We may update this policy from time to time. Please review it periodically for changes.',
    ],
];
