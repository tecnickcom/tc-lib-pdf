<?php

/**
 * CacheTest.php
 *
 * @since       2026-06-16
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use Com\Tecnick\Pdf\Cache\CacheInterface;
use Com\Tecnick\Pdf\Cache\FontSubsetCacheAdapter;
use Com\Tecnick\Pdf\Cache\ImageCacheAdapter;

/**
 * Test the external cache interface, adapters, and Tcpdf wiring.
 */
class CacheTest extends TestUtil
{
    public function testFontSubsetCacheAdapterStoresAndRetrievesString(): void
    {
        $fake = new FakeCache();
        $adapter = new FontSubsetCacheAdapter($fake);

        $adapter->set('fontkey', 'subset-program');

        $this->assertSame(['fontkey'], $fake->setKeys);
        $this->assertSame('subset-program', $fake->store['fontkey'] ?? null);
        $this->assertSame('subset-program', $adapter->get('fontkey'));
        $this->assertSame(['fontkey'], $fake->getKeys);
    }

    public function testFontSubsetCacheAdapterReturnsNullOnMiss(): void
    {
        $adapter = new FontSubsetCacheAdapter(new FakeCache());

        $this->assertNull($adapter->get('missing'));
    }

    public function testFontSubsetCacheAdapterDegradesNonStringToNull(): void
    {
        $fake = new FakeCache();
        $fake->store['fontkey'] = ['not', 'a', 'string'];
        $adapter = new FontSubsetCacheAdapter($fake);

        $this->assertNull($adapter->get('fontkey'));
    }

    public function testImageCacheAdapterRetrievesArray(): void
    {
        $fake = new FakeCache();
        $fake->store['imgkey'] = ['width' => 10, 'height' => 20];
        $adapter = new ImageCacheAdapter($fake);

        $this->assertSame(['width' => 10, 'height' => 20], $adapter->get('imgkey'));
    }

    public function testImageCacheAdapterReturnsNullOnMiss(): void
    {
        $adapter = new ImageCacheAdapter(new FakeCache());

        $this->assertNull($adapter->get('missing'));
    }

    public function testImageCacheAdapterDegradesNonArrayToNull(): void
    {
        $fake = new FakeCache();
        $fake->store['imgkey'] = 'not-an-array';
        $adapter = new ImageCacheAdapter($fake);

        $this->assertNull($adapter->get('imgkey'));
    }

    /** @throws \Throwable */
    public function testConstructorWithoutCacheLeavesExternalCacheNull(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf();

        $this->assertNull($this->getObjectProperty($obj, 'extCache'));
    }

    /** @throws \Throwable */
    public function testConstructorStoresProvidedCache(): void
    {
        $fake = new FakeCache();
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, null, $fake);

        $this->assertSame($fake, $this->getObjectProperty($obj, 'extCache'));
    }

    /** @throws \Throwable */
    public function testImageObjectReceivesAdapterWhenCacheProvided(): void
    {
        $fake = new FakeCache();
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, null, $fake);

        /** @var mixed $image */
        $image = $this->getObjectProperty($obj, 'image');
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Image\Import::class, $image);
        $this->assertInstanceOf(ImageCacheAdapter::class, $this->getObjectProperty($image, 'imageCache'));
    }

    /** @throws \Throwable */
    public function testImageObjectHasNoAdapterWithoutCache(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf();

        /** @var mixed $image */
        $image = $this->getObjectProperty($obj, 'image');
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Image\Import::class, $image);
        $this->assertNull($this->getObjectProperty($image, 'imageCache'));
    }

    /** @throws \Throwable */
    public function testExternalCacheDisabledWhenNotConfigured(): void
    {
        $obj = new TestableTcpdf();

        $this->assertFalse($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_FONT));
        $this->assertFalse($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_IMAGE));
        $this->assertNull($obj->exposeFontSubsetCacheAdapter());
        $this->assertNull($obj->exposeImageCacheAdapter());
    }

    /** @throws \Throwable */
    public function testPlainCacheEnablesAllTypes(): void
    {
        $obj = new TestableTcpdf('mm', true, false, true, '', null, null, new FakeCache());

        $this->assertTrue($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_FONT));
        $this->assertTrue($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_IMAGE));
        $this->assertInstanceOf(FontSubsetCacheAdapter::class, $obj->exposeFontSubsetCacheAdapter());
        $this->assertInstanceOf(ImageCacheAdapter::class, $obj->exposeImageCacheAdapter());
    }

    /** @throws \Throwable */
    public function testSelectiveCacheEnablesFontsOnly(): void
    {
        $fake = new FakeSelectiveCache();
        $fake->supported = [CacheInterface::TYPE_FONT];
        $obj = new TestableTcpdf('mm', true, false, true, '', null, null, $fake);

        $this->assertTrue($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_FONT));
        $this->assertFalse($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_IMAGE));
        $this->assertInstanceOf(FontSubsetCacheAdapter::class, $obj->exposeFontSubsetCacheAdapter());
        $this->assertNull($obj->exposeImageCacheAdapter());
    }

    /** @throws \Throwable */
    public function testSelectiveCacheEnablesImagesOnly(): void
    {
        $fake = new FakeSelectiveCache();
        $fake->supported = [CacheInterface::TYPE_IMAGE];
        $obj = new TestableTcpdf('mm', true, false, true, '', null, null, $fake);

        $this->assertFalse($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_FONT));
        $this->assertTrue($obj->exposeExtCacheEnabledFor(CacheInterface::TYPE_IMAGE));
        $this->assertNull($obj->exposeFontSubsetCacheAdapter());
        $this->assertInstanceOf(ImageCacheAdapter::class, $obj->exposeImageCacheAdapter());
    }
}
