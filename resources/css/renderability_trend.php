<?php

/**
 * Renderability trend updater.
 *
 * Usage:
 *   php resources/css/renderability_trend.php
 *     --score=target/report/renderability-score.json
 *     --history=target/report/renderability-trend.json
 *     --markdown=target/report/renderability-trend.md
 *     --sha=<git-sha>
 *     --ref=<branch-or-tag>
 *     --run-id=<ci-run-id>
 */

declare(strict_types=1);

/** @return array<string, string> */
function rt_parse_args(array $argv): array
{
    $out = [];
    foreach ($argv as $idx => $arg) {
        if ($idx === 0) {
            continue;
        }

        if (!\str_starts_with((string) $arg, '--')) {
            continue;
        }

        $pair = \explode('=', (string) \substr((string) $arg, 2), 2);
        $key = (string) ($pair[0] ?? '');
        $val = (string) ($pair[1] ?? '1');
        if ($key !== '') {
            $out[$key] = $val;
        }
    }

    return $out;
}

/** @return array<string, mixed> */
function rt_load_json_file(string $path): array
{
    $raw = \file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Unable to read JSON file: ' . $path);
    }

    /** @var array<string, mixed>|null $decoded */
    $decoded = \json_decode($raw, true);
    if (!\is_array($decoded)) {
        throw new RuntimeException('Invalid JSON in file: ' . $path);
    }

    return $decoded;
}

/** @return array<string, mixed> */
function rt_load_history(string $path): array
{
    if (!\is_file($path)) {
        return ['history' => []];
    }

    $decoded = rt_load_json_file($path);
    $history = \array_values((array) ($decoded['history'] ?? []));

    return ['history' => $history];
}

function rt_direction(float $current, ?float $previous): string
{
    if ($previous === null) {
        return 'new';
    }

    if ($current > $previous) {
        return 'up';
    }

    if ($current < $previous) {
        return 'down';
    }

    return 'flat';
}

function rt_to_markdown(array $trend, array $latest): string
{
    $rows = \array_slice((array) ($trend['history'] ?? []), -5);

    $lines = [];
    $lines[] = '## CSS Renderability Trend';
    $lines[] = '';
    $lines[] = '| Metric | Value |';
    $lines[] = '|---|---:|';
    $lines[] = '| Current overall score | ' . (string) ($latest['overall_score'] ?? 0) . ' |';
    $lines[] = '| Direction | ' . (string) ($latest['direction'] ?? 'new') . ' |';
    $lines[] = '| Current pass rate | ' . (string) ($latest['pass_rate'] ?? 0) . '% |';
    $lines[] = '| Current text flow rate | ' . (string) ($latest['text_flow_rate'] ?? 0) . '% |';
    $lines[] = '| Current high severity failures | ' . (string) ($latest['high_severity_failures'] ?? 0) . ' |';
    $lines[] = '';
    $lines[] = '| Timestamp | Ref | Score | Pass Rate | Text Flow | High Severity |';
    $lines[] = '|---|---|---:|---:|---:|---:|';

    foreach ($rows as $row) {
        if (!\is_array($row)) {
            continue;
        }

        $lines[] = '| ' . (string) ($row['timestamp'] ?? '')
            . ' | ' . (string) ($row['ref'] ?? '')
            . ' | ' . (string) ($row['overall_score'] ?? 0)
            . ' | ' . (string) ($row['pass_rate'] ?? 0) . '%'
            . ' | ' . (string) ($row['text_flow_rate'] ?? 0) . '%'
            . ' | ' . (string) ($row['high_severity_failures'] ?? 0) . ' |';
    }

    $lines[] = '';

    return \implode(PHP_EOL, $lines) . PHP_EOL;
}

$args = rt_parse_args($argv);
$scorePath = $args['score'] ?? 'target/report/renderability-score.json';
$historyPath = $args['history'] ?? 'target/report/renderability-trend.json';
$markdownPath = $args['markdown'] ?? 'target/report/renderability-trend.md';
$sha = $args['sha'] ?? '';
$ref = $args['ref'] ?? '';
$runId = $args['run-id'] ?? '';
$maxEntries = (int) ($args['max-entries'] ?? '120');

$score = rt_load_json_file($scorePath);
$trend = rt_load_history($historyPath);

$history = (array) ($trend['history'] ?? []);
$previous = null;
if ($history !== []) {
    $last = \end($history);
    if (\is_array($last) && \array_key_exists('overall_score', $last)) {
        $previous = (float) $last['overall_score'];
    }
}

$currentOverall = (float) ($score['overall_score'] ?? 0);
$entry = [
    'timestamp' => \gmdate('c'),
    'run_id' => $runId,
    'ref' => $ref,
    'sha' => $sha,
    'overall_score' => $currentOverall,
    'pass_rate' => (float) ($score['pass_rate'] ?? 0),
    'text_flow_rate' => (float) ($score['text_flow_rate'] ?? 0),
    'high_severity_failures' => (int) ($score['high_severity_failures'] ?? 0),
    'direction' => rt_direction($currentOverall, $previous),
];

$history[] = $entry;
if (($maxEntries > 0) && (\count($history) > $maxEntries)) {
    $history = \array_slice($history, -$maxEntries);
}

$trend = ['history' => \array_values($history)];
$markdown = rt_to_markdown($trend, $entry);

$historyDir = \dirname($historyPath);
if (($historyDir !== '.') && !\is_dir($historyDir)) {
    \mkdir($historyDir, 0777, true);
}
$mdDir = \dirname($markdownPath);
if (($mdDir !== '.') && !\is_dir($mdDir)) {
    \mkdir($mdDir, 0777, true);
}

\file_put_contents($historyPath, \json_encode($trend, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
\file_put_contents($markdownPath, $markdown);

echo 'Renderability trend history written to: ' . $historyPath . PHP_EOL;
echo 'Renderability trend markdown written to: ' . $markdownPath . PHP_EOL;
