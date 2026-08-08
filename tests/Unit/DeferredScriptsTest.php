<?php

namespace Tests\Unit;

use Modules\Base\Support\DeferredScripts;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeferredScriptsTest extends TestCase
{
    #[Test]
    public function it_returns_empty_parts_for_blank_html(): void
    {
        $this->assertSame(
            ['immediate' => '', 'deferred' => ''],
            DeferredScripts::split('   ')
        );
    }

    #[Test]
    public function it_keeps_noscript_immediate_and_defers_scripts(): void
    {
        $html = <<<'HTML'
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];})(window,document,'script','dataLayer','GTM-TEST');</script>
<!-- End Google Tag Manager -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TEST" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
HTML;

        $parts = DeferredScripts::split($html);

        $this->assertStringContainsString('<noscript>', $parts['immediate']);
        $this->assertStringContainsString('GTM-TEST', $parts['immediate']);
        $this->assertStringNotContainsString('<script>', $parts['immediate']);

        $this->assertStringContainsString('<script>', $parts['deferred']);
        $this->assertStringContainsString('GTM-TEST', $parts['deferred']);
        $this->assertStringNotContainsString('<noscript>', $parts['deferred']);
    }
}
