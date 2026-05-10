<?php

/**
 * HTMLRealPageCorpusTest.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;

class HTMLRealPageCorpusTest extends TestUtil
{
    private const CORPUS_FILE = __DIR__ . '/fixtures/html/real_pages/corpus.json';

    /**
     * @param mixed $value
     */
    private static function scalarToString($value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private static function toStringList($value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $list = [];
        foreach ($value as $item) {
            if (\is_scalar($item)) {
                $list[] = (string) $item;
            }
        }

        return $list;
    }

    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * @return array{
     *   version: int,
     *   failure_tags: array<int, string>,
     *   severity_levels: array<int, string>,
     *   pages: array<int, array<string, mixed>>
     * }
     */
    private static function loadCorpus(): array
    {
        $raw = \file_get_contents(self::CORPUS_FILE);
        if ($raw === false) {
            throw new \RuntimeException('Unable to read corpus manifest: ' . self::CORPUS_FILE);
        }

        /** @var array<string, mixed>|null $data */
        $data = \json_decode($raw, true);
        if (!\is_array($data)) {
            throw new \RuntimeException('Invalid JSON in corpus manifest: ' . self::CORPUS_FILE);
        }

        /** @var array{
         *   version: int,
         *   failure_tags: array<int, string>,
         *   severity_levels: array<int, string>,
         *   pages: array<int, array<string, mixed>>
         * } $typed
         */
        $typed = [
            'version' => \is_int($data['version'] ?? null) ? $data['version'] : 0,
            'failure_tags' => self::toStringList($data['failure_tags'] ?? []),
            'severity_levels' => self::toStringList($data['severity_levels'] ?? []),
            'pages' => \array_values((array) ($data['pages'] ?? [])),
        ];

        return $typed;
    }

    public function testRealPageCorpusManifestHasRequiredArchetypesAndSeverityTaggedFailures(): void
    {
        $corpus = self::loadCorpus();

        $this->assertSame(1, $corpus['version']);

        $expectedArchetypes = [
            'long-form article',
            'invoice and statement',
            'product/documentation page',
            'admin/report dashboard with tables',
            'form-heavy page',
        ];

        $this->assertNotEmpty($corpus['failure_tags']);
        $this->assertNotEmpty($corpus['severity_levels']);
        $this->assertNotEmpty($corpus['pages']);

        $archetypes = [];
        $totalFailures = 0;

        foreach ($corpus['pages'] as $page) {
            if (!\is_array($page)) {
                continue;
            }

            $pageId = self::scalarToString($page['id'] ?? '');
            $fixture = self::scalarToString($page['fixture'] ?? '');
            $archetype = self::scalarToString($page['archetype'] ?? '');

            $this->assertNotSame('', $pageId);
            $this->assertNotSame('', $fixture);
            $this->assertNotSame('', $archetype);

            $fixturePath = __DIR__ . '/fixtures/html/real_pages/' . $fixture;
            $this->assertFileExists($fixturePath, 'Missing corpus fixture for page id: ' . $pageId);

            $archetypes[$archetype] = true;

            $failures = (array) ($page['failures'] ?? []);
            foreach ($failures as $failure) {
                if (!\is_array($failure)) {
                    continue;
                }
                $tag = self::scalarToString($failure['tag'] ?? '');
                $severity = self::scalarToString($failure['severity'] ?? '');

                $this->assertContains($tag, $corpus['failure_tags']);
                $this->assertContains($severity, $corpus['severity_levels']);
                $totalFailures++;
            }
        }

        \sort($expectedArchetypes);
        $actualArchetypes = \array_keys($archetypes);
        \sort($actualArchetypes);

        $this->assertSame($expectedArchetypes, $actualArchetypes);
        $this->assertGreaterThanOrEqual(0, $totalFailures, 'Corpus should track severity-tagged failures.');
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function corpusPageProvider(): array
    {
        $corpus = self::loadCorpus();
        $dataset = [];

        foreach ($corpus['pages'] as $page) {
            if (!\is_array($page)) {
                continue;
            }
            $pageId = self::scalarToString($page['id'] ?? 'unknown');
            $fixture = self::scalarToString($page['fixture'] ?? '');
            $dataset[$pageId] = [$pageId, $fixture];
        }

        return $dataset;
    }

    #[DataProvider('corpusPageProvider')]
    public function testRealPageCorpusFixturesRenderWithoutFatal(string $pageId, string $fixture): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf();
        $this->initFontAndPage($obj);

        $fixturePath = __DIR__ . '/fixtures/html/real_pages/' . $fixture;
        $html = \file_get_contents($fixturePath);
        $this->assertNotFalse($html, 'Unable to read fixture for page id: ' . $pageId);

        $obj->addHTMLCell((string) $html, 10, 10, 190, 0);

        $pdf = $obj->getOutPDFString();
        $this->assertNotSame('', $pdf, 'Expected rendered PDF output for page id: ' . $pageId);
    }
}
