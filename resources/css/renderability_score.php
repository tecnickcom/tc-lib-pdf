<?php

/**
 * Renderability scoring for the real-page corpus.
 *
 * Usage:
 *   php resources/css/renderability_score.php
 *     --corpus=test/fixtures/html/real_pages/corpus.json
 *     --json=target/report/renderability-score.json
 *     --markdown=target/report/renderability-score.md
 *     --acceptable-threshold=80
 */

declare(strict_types=1);

/** @return array<string, string> */
function rs_parse_args(array $argv): array
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

/**
 * @return array{version:int,failure_tags:array<int,string>,severity_levels:array<int,string>,pages:array<int,array<string,mixed>>}
 */
function rs_load_corpus(string $path): array
{
    $raw = \file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Unable to read corpus file: ' . $path);
    }

    /** @var array<string, mixed>|null $decoded */
    $decoded = \json_decode($raw, true);
    if (!\is_array($decoded)) {
        throw new RuntimeException('Invalid corpus JSON file: ' . $path);
    }

    return [
        'version' => (int) ($decoded['version'] ?? 0),
        'failure_tags' => \array_values(\array_map('strval', (array) ($decoded['failure_tags'] ?? []))),
        'severity_levels' => \array_values(\array_map('strval', (array) ($decoded['severity_levels'] ?? []))),
        'pages' => \array_values((array) ($decoded['pages'] ?? [])),
    ];
}

/** @return array<string, mixed> */
function rs_score_page(array $page, int $acceptableThreshold): array
{
    $severityPenalty = [
        'critical' => 45,
        'high' => 25,
        'medium' => 12,
        'low' => 5,
    ];

    $flowRiskTags = ['overflow', 'overlap'];
    $structureRiskTags = ['dropped-style', 'selector-miss'];

    $penalty = 0;
    $highSeverityFailures = 0;
    $textFlowPreserved = true;
    $structurePreserved = true;
    $majorBlockPlacementPreserved = true;

    $failures = (array) ($page['failures'] ?? []);
    foreach ($failures as $failure) {
        if (!\is_array($failure)) {
            continue;
        }

        $severity = (string) ($failure['severity'] ?? 'low');
        $tag = (string) ($failure['tag'] ?? '');

        $penalty += $severityPenalty[$severity] ?? 0;

        if (\in_array($severity, ['high', 'critical'], true)) {
            $highSeverityFailures++;
        }

        if ($severity === 'critical') {
            $majorBlockPlacementPreserved = false;
        }

        if (\in_array($tag, $flowRiskTags, true) && \in_array($severity, ['high', 'critical'], true)) {
            $textFlowPreserved = false;
        }

        if (\in_array($tag, $structureRiskTags, true) && \in_array($severity, ['high', 'critical'], true)) {
            $structurePreserved = false;
        }

        if (($tag === 'overlap') && \in_array($severity, ['medium', 'high', 'critical'], true)) {
            $majorBlockPlacementPreserved = false;
        }
    }

    $score = \max(0, 100 - $penalty);
    $acceptable = ($score >= $acceptableThreshold) && $textFlowPreserved;

    return [
        'id' => (string) ($page['id'] ?? ''),
        'archetype' => (string) ($page['archetype'] ?? ''),
        'fixture' => (string) ($page['fixture'] ?? ''),
        'score' => $score,
        'acceptable' => $acceptable,
        'text_flow_preserved' => $textFlowPreserved,
        'structure_preserved' => $structurePreserved,
        'major_block_placement_preserved' => $majorBlockPlacementPreserved,
        'high_severity_failures' => $highSeverityFailures,
        'known_failures' => \count($failures),
    ];
}

/** @return array<string, mixed> */
function rs_build_report(array $corpus, int $acceptableThreshold): array
{
    $pages = [];
    $totalScore = 0.0;
    $acceptableCount = 0;
    $flowCount = 0;
    $highSeverityFailures = 0;

    foreach ($corpus['pages'] as $page) {
        if (!\is_array($page)) {
            continue;
        }

        $row = rs_score_page($page, $acceptableThreshold);
        $pages[] = $row;
        $totalScore += (float) $row['score'];

        if ((bool) $row['acceptable']) {
            $acceptableCount++;
        }
        if ((bool) $row['text_flow_preserved']) {
            $flowCount++;
        }
        $highSeverityFailures += (int) $row['high_severity_failures'];
    }

    $count = \count($pages);
    $overallScore = ($count > 0) ? \round($totalScore / $count, 2) : 0.0;
    $passRate = ($count > 0) ? \round(($acceptableCount * 100) / $count, 2) : 0.0;
    $textFlowRate = ($count > 0) ? \round(($flowCount * 100) / $count, 2) : 0.0;

    return [
        'generated_at' => \gmdate('c'),
        'corpus_version' => (int) ($corpus['version'] ?? 0),
        'page_count' => $count,
        'acceptable_threshold' => $acceptableThreshold,
        'overall_score' => $overallScore,
        'pass_rate' => $passRate,
        'text_flow_rate' => $textFlowRate,
        'high_severity_failures' => $highSeverityFailures,
        'pages' => $pages,
    ];
}

function rs_to_markdown(array $report): string
{
    $lines = [];
    $lines[] = '## CSS Renderability Score';
    $lines[] = '';
    $lines[] = '| Metric | Value |';
    $lines[] = '|---|---:|';
    $lines[] = '| Corpus version | ' . (string) ($report['corpus_version'] ?? 0) . ' |';
    $lines[] = '| Page count | ' . (string) ($report['page_count'] ?? 0) . ' |';
    $lines[] = '| Overall score | ' . (string) ($report['overall_score'] ?? 0) . ' |';
    $lines[] = '| Pass rate | ' . (string) ($report['pass_rate'] ?? 0) . '% |';
    $lines[] = '| Text flow preserved | ' . (string) ($report['text_flow_rate'] ?? 0) . '% |';
    $lines[] = '| High severity failures | ' . (string) ($report['high_severity_failures'] ?? 0) . ' |';
    $lines[] = '';
    $lines[] = '| Page | Score | Acceptable | Text Flow | High-Severity Failures |';
    $lines[] = '|---|---:|:---:|:---:|---:|';

    foreach ((array) ($report['pages'] ?? []) as $row) {
        if (!\is_array($row)) {
            continue;
        }

        $lines[] = '| ' . (string) ($row['id'] ?? '')
            . ' | ' . (string) ($row['score'] ?? 0)
            . ' | ' . (((bool) ($row['acceptable'] ?? false)) ? 'yes' : 'no')
            . ' | ' . (((bool) ($row['text_flow_preserved'] ?? false)) ? 'yes' : 'no')
            . ' | ' . (string) ($row['high_severity_failures'] ?? 0) . ' |';
    }

    $lines[] = '';

    return \implode(PHP_EOL, $lines) . PHP_EOL;
}

$args = rs_parse_args($argv);
$corpusPath = $args['corpus'] ?? 'test/fixtures/html/real_pages/corpus.json';
$jsonPath = $args['json'] ?? 'target/report/renderability-score.json';
$markdownPath = $args['markdown'] ?? 'target/report/renderability-score.md';
$acceptableThreshold = (int) ($args['acceptable-threshold'] ?? '80');

$corpus = rs_load_corpus($corpusPath);
$report = rs_build_report($corpus, $acceptableThreshold);
$markdown = rs_to_markdown($report);

$jsonDir = \dirname($jsonPath);
if (($jsonDir !== '.') && !\is_dir($jsonDir)) {
    \mkdir($jsonDir, 0777, true);
}
$mdDir = \dirname($markdownPath);
if (($mdDir !== '.') && !\is_dir($mdDir)) {
    \mkdir($mdDir, 0777, true);
}

\file_put_contents($jsonPath, \json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
\file_put_contents($markdownPath, $markdown);

echo 'Renderability score report written to: ' . $jsonPath . PHP_EOL;
echo 'Renderability markdown summary written to: ' . $markdownPath . PHP_EOL;
