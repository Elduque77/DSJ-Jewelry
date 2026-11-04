<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    private const DEFAULT_IMAGE = 'products/product-placeholder.svg';

    public function up(): void
    {
        Storage::disk('public')->put(self::DEFAULT_IMAGE, <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" role="img" aria-labelledby="title">
  <title>DSJ Jewelry</title>
  <rect width="800" height="600" fill="#f3eee8"/>
  <circle cx="400" cy="270" r="118" fill="none" stroke="#b48b5a" stroke-width="14"/>
  <path d="M400 102 425 215 538 240 425 265 400 378 375 265 262 240 375 215Z" fill="#b48b5a"/>
  <text x="400" y="485" text-anchor="middle" fill="#3c3028" font-family="Arial, sans-serif" font-size="42" letter-spacing="8">DSJ JEWELRY</text>
</svg>
SVG);

        DB::table('productos')
            ->whereNull('imagen')
            ->update(['imagen' => self::DEFAULT_IMAGE]);
    }

    public function down(): void
    {
        DB::table('productos')
            ->where('imagen', self::DEFAULT_IMAGE)
            ->update(['imagen' => null]);

        Storage::disk('public')->delete(self::DEFAULT_IMAGE);
    }
};
