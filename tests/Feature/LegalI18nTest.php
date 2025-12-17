<?php

function inertiaProps(string $html): array
{
    // Extract Inertia payload from data-page attribute
    if (preg_match('/data-page="([^"]+)"/i', $html, $m)) {
        $json = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        $page = json_decode($json, true);

        return $page['props'] ?? [];
    }

    // Fallback: try to find window.__INERTIA__ script payload (some setups)
    if (preg_match('/window\.\__INERTIA__\s*=\s*(\{.*?\});/s', $html, $m)) {
        $page = json_decode($m[1], true);

        return $page['props'] ?? [];
    }

    return [];
}

it('exposes privacy translations via Inertia props (nb)', function (): void {
    $response = $this->get('/privacy');
    $response->assertSuccessful();

    $props = inertiaProps($response->getContent());

    expect($props)
        ->toHaveKey('i18n')
        ->and($props['i18n']['locale'] ?? null)->toBe('nb')
        ->and(data_get($props, 'i18n.trans.pages.privacy.title'))->toBe('Personvernerklæring')
        ->and(data_get($props, 'i18n.trans.pages.privacy.summary_title'))->toBe('Kort fortalt')
        ->and(data_get($props, 'i18n.trans.pages.privacy.toc.who'))->toBe('Hvem vi er');
});

it('exposes terms translations via Inertia props (nb)', function (): void {
    $response = $this->get('/terms');
    $response->assertSuccessful();

    $props = inertiaProps($response->getContent());

    expect($props)
        ->toHaveKey('i18n')
        ->and($props['i18n']['locale'] ?? null)->toBe('nb')
        ->and(data_get($props, 'i18n.trans.pages.terms.title'))->toBe('Vilkår og juridisk')
        ->and(data_get($props, 'i18n.trans.pages.terms.toc.eula'))->toBe('Sluttbrukerlisensavtale (EULA)')
        ->and(data_get($props, 'i18n.trans.pages.terms.toc.website'))->toBe('Vilkår for bruk av nettstedet');
});

it('exposes cookie translations via Inertia props (nb)', function (): void {
    $response = $this->get('/cookie');
    $response->assertSuccessful();

    $props = inertiaProps($response->getContent());

    expect($props)
        ->toHaveKey('i18n')
        ->and($props['i18n']['locale'] ?? null)->toBe('nb')
        ->and(data_get($props, 'i18n.trans.pages.cookie.title'))->toBe('Cookie‑policy')
        ->and(data_get($props, 'i18n.trans.pages.cookie.toc.what'))->toBe('Hva er cookies?')
        ->and(data_get($props, 'i18n.trans.pages.cookie.toc.types'))->toBe('Typer cookies vi bruker');
});
