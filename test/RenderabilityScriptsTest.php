<?php

/**
 * RenderabilityScriptsTest.php
 *
 * @since       2002-08-03
 * @category    Library
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

class RenderabilityScriptsTest extends TestUtil
{
    /**
     * @param list<string> $cmd
     * @return array{code:int,stdout:string,stderr:string}
     */
    private function runCommand(array $cmd): array
    {
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = \proc_open($cmd, $desc, $pipes, __DIR__ . '/..');
        if (!\is_resource($proc)) {
            return ['code' => 127, 'stdout' => '', 'stderr' => 'Unable to start process'];
        }

        \fclose($pipes[0]);
        $stdout = (string) \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);
        $stderr = (string) \stream_get_contents($pipes[2]);
        \fclose($pipes[2]);
        $code = \proc_close($proc);

        return ['code' => $code, 'stdout' => $stdout, 'stderr' => $stderr];
    }

    public function testRenderabilityScoreScriptWritesExpectedFilesAndMetrics(): void
    {
        $base = \sys_get_temp_dir() . '/tc-lib-pdf-renderability-' . \bin2hex(\random_bytes(6));
        $json = $base . '-score.json';
        $markdownFile = $base . '-score.md';

        $cmd = [
            'php',
            'resources/css/renderability_score.php',
            '--corpus=test/fixtures/html/real_pages/corpus.json',
            '--json=' . $json,
            '--markdown=' . $markdownFile,
            '--acceptable-threshold=80',
        ];

        $res = $this->runCommand($cmd);

        try {
            $this->assertSame(0, $res['code'], $res['stderr']);
            $this->assertFileExists($json);
            $this->assertFileExists($markdownFile);

            $raw = \file_get_contents($json);
            $this->assertNotFalse($raw);
            /** @var array<string, mixed>|null $report */
            $report = \json_decode($raw, true);
            $this->assertIsArray($report);
            $this->assertArrayHasKey('page_count', $report);
            $this->assertIsInt($report['page_count']);
            $this->assertSame(5, $report['page_count']);
            $this->assertArrayHasKey('overall_score', $report);
            $this->assertArrayHasKey('pass_rate', $report);
            $this->assertArrayHasKey('text_flow_rate', $report);
            $this->assertArrayHasKey('high_severity_failures', $report);

            $markdown = (string) \file_get_contents($markdownFile);
            $this->assertStringContainsString('CSS Renderability Score', $markdown);
            $this->assertStringContainsString('| Page | Score | Acceptable |', $markdown);
        } finally {
            if (\file_exists($json)) {
                \unlink($json);
            }
            if (\file_exists($markdownFile)) {
                \unlink($markdownFile);
            }
        }
    }

    public function testRenderabilityTrendScriptAppendsAndCapsHistory(): void
    {
        $base = \sys_get_temp_dir() . '/tc-lib-pdf-trend-' . \bin2hex(\random_bytes(6));
        $score = $base . '-score.json';
        $history = $base . '-trend.json';
        $markdownFile = $base . '-trend.md';

        $scorePayload = [
            'overall_score' => 88.2,
            'pass_rate' => 80.0,
            'text_flow_rate' => 80.0,
            'high_severity_failures' => 1,
        ];
        \file_put_contents($score, \json_encode($scorePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $historyPayload = [
            'history' => [
                [
                    'timestamp' => '2026-05-01T00:00:00Z',
                    'run_id' => '1',
                    'ref' => 'main',
                    'sha' => 'abc',
                    'overall_score' => 80.0,
                    'pass_rate' => 60.0,
                    'text_flow_rate' => 60.0,
                    'high_severity_failures' => 3,
                    'direction' => 'new',
                ],
                [
                    'timestamp' => '2026-05-02T00:00:00Z',
                    'run_id' => '2',
                    'ref' => 'main',
                    'sha' => 'def',
                    'overall_score' => 84.0,
                    'pass_rate' => 80.0,
                    'text_flow_rate' => 80.0,
                    'high_severity_failures' => 2,
                    'direction' => 'up',
                ],
            ],
        ];
        \file_put_contents(
            $history,
            \json_encode($historyPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        );

        $cmd = [
            'php',
            'resources/css/renderability_trend.php',
            '--score=' . $score,
            '--history=' . $history,
            '--markdown=' . $markdownFile,
            '--sha=xyz',
            '--ref=main',
            '--run-id=3',
            '--max-entries=2',
        ];

        $res = $this->runCommand($cmd);

        try {
            $this->assertSame(0, $res['code'], $res['stderr']);
            $this->assertFileExists($history);
            $this->assertFileExists($markdownFile);

            $rawHistory = \file_get_contents($history);
            $this->assertNotFalse($rawHistory);
            /** @var array<string, mixed>|null $trend */
            $trend = \json_decode($rawHistory, true);
            $this->assertIsArray($trend);
            $entries = \array_values((array) ($trend['history'] ?? []));

            $this->assertCount(2, $entries);
            $latest = $entries[1] ?? [];
            $this->assertIsArray($latest);
            $this->assertArrayHasKey('run_id', $latest);
            $this->assertArrayHasKey('ref', $latest);
            $this->assertArrayHasKey('direction', $latest);
            $this->assertSame('3', \is_scalar($latest['run_id']) ? (string) $latest['run_id'] : '');
            $this->assertSame('main', \is_scalar($latest['ref']) ? (string) $latest['ref'] : '');
            $this->assertSame('up', \is_scalar($latest['direction']) ? (string) $latest['direction'] : '');

            $markdown = (string) \file_get_contents($markdownFile);
            $this->assertStringContainsString('CSS Renderability Trend', $markdown);
            $this->assertStringContainsString('| Timestamp | Ref | Score |', $markdown);
        } finally {
            if (\file_exists($score)) {
                \unlink($score);
            }
            if (\file_exists($history)) {
                \unlink($history);
            }
            if (\file_exists($markdownFile)) {
                \unlink($markdownFile);
            }
        }
    }
}
