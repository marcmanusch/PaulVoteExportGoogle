<?php

namespace PaulRaitingExportGoogle\Tests;

use PaulRaitingExportGoogle\PaulRaitingExportGoogle as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'PaulRaitingExportGoogle' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['PaulRaitingExportGoogle'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
