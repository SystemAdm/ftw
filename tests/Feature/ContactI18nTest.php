<?php

if (! function_exists('inertiaPropsFromHtml')) {
    function inertiaPropsFromHtml(string $html): array
    {
        if (preg_match('/data-page="([^"]+)"/i', $html, $m)) {
            $json = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            $page = json_decode($json, true);

            return $page['props'] ?? [];
        }
        if (preg_match('/window\.\__INERTIA__\s*=\s*(\{.*?\});/s', $html, $m)) {
            $page = json_decode($m[1], true);

            return $page['props'] ?? [];
        }

        return [];
    }
}

it('exposes contact translations via Inertia props (nb) and renders', function (): void {
    $response = $this->get('/contact');
    $response->assertSuccessful();

    $props = inertiaPropsFromHtml($response->getContent());

    expect($props)
        ->toHaveKey('i18n')
        ->and(data_get($props, 'i18n.locale'))->toBe('nb')
        ->and(data_get($props, 'i18n.trans.pages.contact.title'))->toBe('Kontakt oss')
        ->and(data_get($props, 'i18n.trans.pages.contact.labels.name'))->toBe('Navn')
        ->and(data_get($props, 'i18n.trans.pages.contact.actions.send'))->toBe('Send');
});
