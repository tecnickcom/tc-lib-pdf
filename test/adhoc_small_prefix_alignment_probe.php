<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/TestUtil.php';
require __DIR__ . '/HTMLTest.php';

class AdhocHTMLCommandProbe extends \Test\TestableHTMLBBoxProbe
{
    /**
     * @var array<int, array{txt: string, posx: float, posy: float, width: float, offset: float, halign: string, td: array<int, array{x: float, y: float}>}>
     */
    private array $commandTrace = [];

    /**
     * @var array<int, array{fragment: int, txt: string, posx: float, posy: float, width: float}>
     */
    private array $lineTrace = [];

    private int $activeFragment = -1;

    /**
     * @return array<int, array{txt: string, posx: float, posy: float, width: float, offset: float, halign: string, td: array<int, array{x: float, y: float}>}>
     */
    public function exposeGetCommandTrace(): array
    {
        return $this->commandTrace;
    }

    public function exposeResetCommandTrace(): void
    {
        $this->commandTrace = [];
        $this->lineTrace = [];
        $this->activeFragment = -1;
    }

    /**
     * @return array<int, array{fragment: int, txt: string, posx: float, posy: float, width: float}>
     */
    public function exposeGetLineTrace(): array
    {
        return $this->lineTrace;
    }

    public function getTextCell(
        string $txt,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        float $offset = 0,
        float $linespace = 0,
        string $valign = 'C',
        string $halign = 'C',
        ?array $cell = null,
        array $styles = [],
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        bool $drawcell = true,
        string $forcedir = '',
        ?array $shadow = null,
    ): string {
        $this->activeFragment = \count($this->commandTrace);

        $out = parent::getTextCell(
            $txt,
            $posx,
            $posy,
            $width,
            $height,
            $offset,
            $linespace,
            $valign,
            $halign,
            $cell,
            $styles,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $jlast,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $drawcell,
            $forcedir,
            $shadow,
        );

        $td = [];
        if ((bool) \preg_match_all('/(-?[0-9]+\.[0-9]+) (-?[0-9]+\.[0-9]+) Td /', $out, $matches, \PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $td[] = [
                    'x' => (float) $match[1],
                    'y' => (float) $match[2],
                ];
            }
        }

        $this->commandTrace[] = [
            'txt' => $txt,
            'posx' => $posx,
            'posy' => $posy,
            'width' => $width,
            'offset' => $offset,
            'halign' => $halign,
            'td' => $td,
        ];

        $this->activeFragment = -1;

        return $out;
    }

    protected function getOutTextLine(
        string $txt,
        array $ordarr,
        array $dim,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        $this->lineTrace[] = [
            'fragment' => $this->activeFragment,
            'txt' => $txt,
            'posx' => $posx,
            'posy' => $posy,
            'width' => $width,
        ];

        return parent::getOutTextLine(
            $txt,
            $ordarr,
            $dim,
            $posx,
            $posy,
            $width,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }
}

$runner = new class ('adhocSmallPrefixAlignmentProbe') extends \Test\HTMLTest {
    /**
    * @return array{bbox: array<int, array<string, float|string>>, cmd: array<int, array{txt: string, posx: float, posy: float, width: float, offset: float, halign: string, td: array<int, array{x: float, y: float}>}>, lines: array<int, array{fragment: int, txt: string, posx: float, posy: float, width: float}>}
     */
    public function measure(): array
    {
        self::setUpBeforeClass();

        $obj = new AdhocHTMLCommandProbe();
        $this->initFontAndPage($obj);

        $html = '<table border="1" cellspacing="3" cellpadding="4">'
            . '<tr><td align="center"><small>3C small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu</td></tr>'
            . '<tr><td align="right"><small>3R small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu</td></tr>'
            . '</table>';

        $obj->exposeResetBBoxTrace();
        $obj->exposeResetCommandTrace();
        $obj->getHTMLCell($html, 0, 0, 150, 0);

        return [
            'bbox' => $obj->exposeGetBBoxTrace(),
            'cmd' => $obj->exposeGetCommandTrace(),
            'lines' => $obj->exposeGetLineTrace(),
        ];
    }
};

$result = $runner->measure();

foreach ($result['bbox'] as $idx => $bbox) {
    $cmd = $result['cmd'][$idx] ?? ['td' => []];
    $firstTd = $cmd['td'][0] ?? null;
    $tdCount = \count($cmd['td']);

    echo $idx,
        ' | txt=',
        \trim((string) $bbox['txt']),
        ' | in_x=',
        $bbox['in_x'],
        ' | in_y=',
        $bbox['in_y'],
        ' | bbox_x=',
        $bbox['bbox_x'],
        ' | bbox_y=',
        $bbox['bbox_y'],
        ' | bbox_w=',
        $bbox['bbox_w'],
        ' | bbox_h=',
        $bbox['bbox_h'],
        ' | font_size=',
        $bbox['font_size'],
        ' | td_count=',
        $tdCount,
        ' | first_td_x=',
        ($firstTd['x'] ?? 'n/a'),
        ' | first_td_y=',
        ($firstTd['y'] ?? 'n/a'),
        ' | cell_posx=',
        ($cmd['posx'] ?? 'n/a'),
        ' | cell_posy=',
        ($cmd['posy'] ?? 'n/a'),
        ' | cell_width=',
        ($cmd['width'] ?? 'n/a'),
        ' | cell_offset=',
        ($cmd['offset'] ?? 'n/a'),
        ' | cell_halign=',
        ($cmd['halign'] ?? 'n/a'),
        PHP_EOL;
}

echo PHP_EOL;

$ptToMm = static fn (float $points): float => ($points / 2.8346456692913);

$firstLineByFragment = [];
foreach ($result['lines'] as $line) {
    $fid = $line['fragment'];
    if (!isset($firstLineByFragment[$fid])) {
        $firstLineByFragment[$fid] = $line;
    }
}

foreach ([0 => '3C', 2 => '3R'] as $smallIdx => $label) {
    $small = $result['bbox'][$smallIdx];
    $nextLine = $firstLineByFragment[$smallIdx + 1] ?? null;
    $smallLine = $firstLineByFragment[$smallIdx] ?? null;
    if (($nextLine === null) || ($smallLine === null)) {
        continue;
    }

    $prefixStart = (float) $small['bbox_x'];
    $prefixEnd = (float) $small['bbox_end_x'];
    $nextStart = (float) $nextLine['posx'];
    $gap = $nextStart - $prefixEnd;
    $baselineDelta = (float) $nextLine['posy'] - (float) $smallLine['posy'];
    $nextLineTxt = (string) $nextLine['txt'];

    echo $label,
        ' summary',
        ' | prefix_start_mm=',
        $prefixStart,
        ' | prefix_end_mm=',
        $prefixEnd,
        ' | next_start_mm=',
        $nextStart,
        ' | gap_mm=',
        $gap,
        ' | baseline_delta_mm=',
        $baselineDelta,
        ' | next_line_txt=',
        \json_encode($nextLineTxt, JSON_UNESCAPED_UNICODE),
        PHP_EOL;
}