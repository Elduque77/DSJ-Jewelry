<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    protected function tearDown(): void
    {
        app()->setLocale('es');
        parent::tearDown();
    }

    public function test_supported_locales_have_the_required_catalog_labels(): void
    {
        foreach (['es', 'en'] as $locale) {
            app()->setLocale($locale);

            $this->assertSame('DSJ Jewelry', __('messages.brand'));
            $this->assertNotSame('messages.search', __('messages.search'));
            $this->assertNotSame('messages.add_to_cart', __('messages.add_to_cart'));
        }
    }

    public function test_spanish_and_english_use_different_search_labels(): void
    {
        app()->setLocale('es');
        $spanish = __('messages.search');

        app()->setLocale('en');
        $english = __('messages.search');

        $this->assertSame('Buscar', $spanish);
        $this->assertSame('Search', $english);
        $this->assertNotSame($spanish, $english);
    }

    public function test_product_empty_state_replaces_the_search_term(): void
    {
        app()->setLocale('es');

        $this->assertSame(
            'No encontramos productos que coincidan con "anillo".',
            __('messages.no_match', ['search' => 'anillo']),
        );
    }

    public function test_all_required_language_keys_exist_in_both_locales(): void
    {
        $keys = [
            'brand',
            'home',
            'language',
            'featured_title',
            'search',
            'add_to_cart',
            'no_products',
        ];

        foreach (['es', 'en'] as $locale) {
            foreach ($keys as $key) {
                $this->assertNotSame("messages.$key", __(
                    "messages.$key",
                    locale: $locale,
                ));
            }
        }
    }

    public function test_locale_route_persists_a_supported_language(): void
    {
        $this->get('/idioma/en')
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');
    }

    public function test_locale_route_rejects_an_unsupported_language(): void
    {
        $this->get('/idioma/fr')->assertNotFound();
    }
}
