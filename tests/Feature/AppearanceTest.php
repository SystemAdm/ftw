<?php

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

it('shares system appearance by default when cookie is missing', function (): void {
    $response = $this->get('/');
    $response->assertSuccessful();

    $props = inertiaPropsFromHtml($response->getContent());
    expect($props)->toHaveKey('appearance')->and($props['appearance'])->toBe('system');
});

it('reads appearance from cookie when provided', function (): void {
    // Use unencrypted cookie since our middleware reads the raw cookie value
    $response = $this->withUnencryptedCookie('appearance', 'dark')->get('/');
    $response->assertSuccessful();

    $props = inertiaPropsFromHtml($response->getContent());
    expect($props)->toHaveKey('appearance')->and($props['appearance'])->toBe('dark');
});
