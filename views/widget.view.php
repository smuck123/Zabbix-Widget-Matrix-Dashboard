<?php

/**
 * @var CView $this
 * @var array $data
 */

use Modules\MatrixFirewall\Includes\WidgetForm;

$max_nodes = 20;
$max_links = 30;
$max_extras = 10;
$max_status = 10;

$clampInt = static function($value, int $min, int $max, int $default): int {
    if ($value === '' || !is_numeric($value)) {
        return $default;
    }

    $num = (int) $value;

    if ($num < $min) {
        $num = $min;
    }
    if ($num > $max) {
        $num = $max;
    }

    return $num;
};

$shortText = static function(string $value, int $limit = 28): string {
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (mb_strlen($value) > $limit) {
        return mb_substr($value, 0, $limit).'...';
    }

    return $value;
};

$getTrafficStyle = static function(float $traffic): array {
    $color = '#6bff9e';
    $width = 2.5;
    $glow_width = 8;
    $dur = 3.0;
    $balls = 1;

    if ($traffic >= 100000000) {
        $width = 3.0;
        $glow_width = 10;
        $dur = 2.3;
    }

    if ($traffic >= 500000000) {
        $color = '#ffd84d';
        $width = 4.0;
        $glow_width = 12;
        $dur = 1.7;
        $balls = 2;
    }

    if ($traffic >= 1000000000) {
        $color = '#ff5f5f';
        $width = 5.5;
        $glow_width = 15;
        $dur = 1.1;
        $balls = 3;
    }

    return [
        'color' => $color,
        'width' => $width,
        'glow_width' => $glow_width,
        'dur' => $dur,
        'balls' => $balls
    ];
};

$node_count = $clampInt($data['node_count'] ?? 5, 1, $max_nodes, 5);
$link_count = $clampInt($data['link_count'] ?? 3, 0, $max_links, 3);
$extra_count = $clampInt($data['extra_count'] ?? 0, 0, $max_extras, 0);
$status_count = $clampInt($data['status_count'] ?? 0, 0, $max_status, 0);
$layout_mode = $clampInt($data['layout_mode'] ?? 0, 0, 1, 0);

$nodes = [];

if ($layout_mode === WidgetForm::LAYOUT_MANUAL) {
    for ($i = 1; $i <= $node_count; $i++) {
        $nodes[$i] = [
            'label' => trim((string) ($data['node'.$i.'_label'] ?? '')),
            'host' => trim((string) ($data['node'.$i.'_host'] ?? '')),
            'cpu' => trim((string) ($data['node'.$i.'_cpu_value'] ?? '')),
            'mem' => trim((string) ($data['node'.$i.'_mem_value'] ?? '')),
            'has_error' => (($data['node'.$i.'_has_error'] ?? '0') === '1'),
            'x' => $clampInt($data['node'.$i.'_x'] ?? 10, 2, 90, 10),
            'y' => $clampInt($data['node'.$i.'_y'] ?? 10, 6, 78, 10)
        ];
    }
}
else {
    if ($node_count <= 3) {
        $cols = $node_count;
    }
    elseif ($node_count <= 6) {
        $cols = 3;
    }
    elseif ($node_count <= 12) {
        $cols = 4;
    }
    else {
        $cols = 5;
    }

    $rows = max(1, (int) ceil($node_count / $cols));

    $x_start = 7;
    $x_end = 83;
    $y_start = 10;
    $y_end = 72;

    $x_step = ($cols > 1) ? (($x_end - $x_start) / ($cols - 1)) : 0;
    $y_step = ($rows > 1) ? (($y_end - $y_start) / ($rows - 1)) : 0;

    for ($i = 1; $i <= $node_count; $i++) {
        $index = $i - 1;
        $row = (int) floor($index / $cols);
        $col = $index % $cols;

        $x = ($cols === 1) ? 44 : ($x_start + ($col * $x_step));
        $y = ($rows === 1) ? 26 : ($y_start + ($row * $y_step));

        if (($row % 2) === 1) {
            $x += 2.5;
        }

        $nodes[$i] = [
            'label' => trim((string) ($data['node'.$i.'_label'] ?? '')),
            'host' => trim((string) ($data['node'.$i.'_host'] ?? '')),
            'cpu' => trim((string) ($data['node'.$i.'_cpu_value'] ?? '')),
            'mem' => trim((string) ($data['node'.$i.'_mem_value'] ?? '')),
            'has_error' => (($data['node'.$i.'_has_error'] ?? '0') === '1'),
            'x' => min(86, max(5, $x)),
            'y' => min(74, max(8, $y))
        ];
    }
}

$status_bar = (new CDiv())->addClass('mf-status-bar');

for ($i = 1; $i <= $status_count; $i++) {
    $label = trim((string) ($data['status'.$i.'_label'] ?? ''));
    $value = trim((string) ($data['status'.$i.'_value'] ?? ''));
    $class = trim((string) ($data['status'.$i.'_class'] ?? 'neutral'));

    if ($label === '' && $value === '') {
        continue;
    }

    $chip = (new CDiv())->addClass('mf-status-chip mf-status-'.$class);
    $chip->addItem((new CDiv($label !== '' ? $label : 'Status '.$i))->addClass('mf-status-chip-label'));
    $chip->addItem((new CDiv($value !== '' ? $value : 'No value'))->addClass('mf-status-chip-value'));
    $status_bar->addItem($chip);
}

$svg = new CTag('svg', true);
$svg->setAttribute('class', 'mf-svg');
$svg->setAttribute('viewBox', '0 0 1000 700');
$svg->setAttribute('preserveAspectRatio', 'none');

$link_labels_layer = (new CDiv())->addClass('mf-link-label-layer');

for ($i = 1; $i <= $link_count; $i++) {
    $from = $clampInt($data['link'.$i.'_from'] ?? '', 1, $node_count, 1);
    $to = $clampInt($data['link'.$i.'_to'] ?? '', 1, $node_count, min(2, $node_count));

    if (!isset($nodes[$from]) || !isset($nodes[$to]) || $from === $to) {
        continue;
    }

    $label = trim((string) ($data['link'.$i.'_label'] ?? ''));
    $in_value = trim((string) ($data['link'.$i.'_in_value'] ?? ''));
    $out_value = trim((string) ($data['link'.$i.'_out_value'] ?? ''));
    $loss_value = trim((string) ($data['link'.$i.'_loss_value'] ?? ''));
    $latency_value = trim((string) ($data['link'.$i.'_latency_value'] ?? ''));
    $errors_value = trim((string) ($data['link'.$i.'_errors_value'] ?? ''));

    $in_raw = (float) ($data['link'.$i.'_in_raw'] ?? 0);
    $out_raw = (float) ($data['link'.$i.'_out_raw'] ?? 0);
    $traffic = max($in_raw, $out_raw);

    $style = $getTrafficStyle($traffic);
    $has_error = (($data['link'.$i.'_has_error'] ?? '0') === '1');

    /*
     * Use box edge anchors instead of center.
     * Node box is about 150px wide / 84px high in CSS.
     * In SVG coordinates we approximate that footprint.
     */
    $node_box_w = 72;
    $node_box_h = 46;

    $cx1 = ($nodes[$from]['x'] + 4.7) * 10;
    $cy1 = ($nodes[$from]['y'] + 4.4) * 7;

    $cx2 = ($nodes[$to]['x'] + 4.7) * 10;
    $cy2 = ($nodes[$to]['y'] + 4.4) * 7;

    $dx = $cx2 - $cx1;
    $dy = $cy2 - $cy1;
    $len = sqrt($dx * $dx + $dy * $dy);
    if ($len == 0) {
        $len = 1;
    }

    $ux = $dx / $len;
    $uy = $dy / $len;

    $x1 = $cx1 + ($ux * $node_box_w);
    $y1 = $cy1 + ($uy * $node_box_h);

    $x2 = $cx2 - ($ux * $node_box_w);
    $y2 = $cy2 - ($uy * $node_box_h);

    $px = -$uy;
    $py = $ux;

    $line_offset = (($i % 5) - 2) * 6;

    $x1o = $x1 + ($px * $line_offset);
    $y1o = $y1 + ($py * $line_offset);
    $x2o = $x2 + ($px * $line_offset);
    $y2o = $y2 + ($py * $line_offset);

    $mx = ($x1o + $x2o) / 2;
    $my = ($y1o + $y2o) / 2;

    $label_offset = (($i % 5) - 2) * 18;
    $mx += $px * $label_offset;
    $my += $py * $label_offset;

    $glow = new CTag('line', true);
    $glow->setAttribute('x1', (string) round($x1o, 2));
    $glow->setAttribute('y1', (string) round($y1o, 2));
    $glow->setAttribute('x2', (string) round($x2o, 2));
    $glow->setAttribute('y2', (string) round($y2o, 2));
    $glow->setAttribute('stroke', $style['color']);
    $glow->setAttribute('stroke-width', (string) $style['glow_width']);
    $glow->setAttribute('class', 'mf-svg-glow');

    $line = new CTag('line', true);
    $line->setAttribute('x1', (string) round($x1o, 2));
    $line->setAttribute('y1', (string) round($y1o, 2));
    $line->setAttribute('x2', (string) round($x2o, 2));
    $line->setAttribute('y2', (string) round($y2o, 2));
    $line->setAttribute('stroke', $style['color']);
    $line->setAttribute('stroke-width', (string) $style['width']);
    $line->setAttribute('class', 'mf-svg-line');

    $svg->addItem($glow);
    $svg->addItem($line);

    for ($b = 0; $b < $style['balls']; $b++) {
        $ball = new CTag('circle', true);
        $ball->setAttribute('r', (string) (5 + $b));
        $ball->setAttribute('fill', $style['color']);
        $ball->setAttribute('class', 'mf-svg-ball');

        $animate = new CTag('animateMotion', true);
        $animate->setAttribute('dur', (string) ($style['dur'] + ($b * 0.22)).'s');
        $animate->setAttribute('begin', (string) ($b * 0.18).'s');
        $animate->setAttribute('repeatCount', 'indefinite');
        $animate->setAttribute(
            'path',
            'M'.round($x1o, 2).','.round($y1o, 2).' L'.round($x2o, 2).','.round($y2o, 2)
        );

        $ball->addItem($animate);
        $svg->addItem($ball);
    }

    $label_box = (new CDiv())->addClass('mf-link-label'.($has_error ? ' mf-link-label-error' : ''));

    if ($label !== '') {
        $label_box->addItem((new CDiv($label))->addClass('mf-link-title'));
    }

    $mini = (new CDiv())->addClass('mf-link-items');

    if ($in_value !== '') {
        $mini->addItem((new CDiv('IN '.$in_value))->addClass('mf-link-item-line'));
    }
    if ($out_value !== '') {
        $mini->addItem((new CDiv('OUT '.$out_value))->addClass('mf-link-item-line'));
    }
    if ($loss_value !== '') {
        $mini->addItem((new CDiv('LOSS '.$loss_value))->addClass('mf-link-item-line'));
    }
    if ($latency_value !== '') {
        $mini->addItem((new CDiv('LAT '.$latency_value))->addClass('mf-link-item-line'));
    }
    if ($errors_value !== '') {
        $mini->addItem((new CDiv('ERR '.$errors_value))->addClass('mf-link-item-line'));
    }

    if ($in_value === '' && $out_value === '' && $loss_value === '' && $latency_value === '' && $errors_value === '') {
        $mini->addItem((new CDiv('No values'))->addClass('mf-link-item-line'));
    }

    $label_box->addItem($mini);

    $label_box->setAttribute(
        'style',
        'left: calc('.round($mx / 10, 2).'%' .
        ' - 82px); top: calc('.round($my / 7, 2).'%' .
        ' - 20px); border-color: '.$style['color'].'; box-shadow: 0 0 12px '.$style['color'].'22, inset 0 0 10px '.$style['color'].'14;'
    );

    $link_labels_layer->addItem($label_box);
}

$canvas = (new CDiv())
    ->addClass('mf-canvas')
    ->addItem(
        (new CDiv())
            ->addClass('mf-matrix-bg')
            ->addItem((new CDiv('010101NODEMATRIX'))->addClass('mf-column mf-col-1'))
            ->addItem((new CDiv('ZABBIXNETWORKFLOW'))->addClass('mf-column mf-col-2'))
            ->addItem((new CDiv('FORTIGATEMONITOR'))->addClass('mf-column mf-col-3'))
            ->addItem((new CDiv('INOUTTRAFFIC'))->addClass('mf-column mf-col-4'))
            ->addItem((new CDiv('LATENCYHEALTH'))->addClass('mf-column mf-col-5'))
            ->addItem((new CDiv('LINKSTATUS'))->addClass('mf-column mf-col-6'))
            ->addItem((new CDiv('PACKETSERRORS'))->addClass('mf-column mf-col-7'))
            ->addItem((new CDiv('MATRIXRAIN'))->addClass('mf-column mf-col-8'))
    )
    ->addItem($svg)
    ->addItem($link_labels_layer);

for ($i = 1; $i <= $node_count; $i++) {
    $node = (new CDiv())
        ->addClass('mf-node'.($nodes[$i]['has_error'] ? ' mf-node-error' : ''))
        ->setAttribute('style', 'left: '.$nodes[$i]['x'].'%; top: '.$nodes[$i]['y'].'%;');

    $head = (new CDiv())->addClass('mf-node-head');
    $head->addItem((new CDiv($nodes[$i]['label'] !== '' ? $nodes[$i]['label'] : 'Node '.$i))->addClass('mf-node-title'));
    $node->addItem($head);

    if ($nodes[$i]['host'] !== '') {
        $node->addItem((new CDiv($shortText($nodes[$i]['host'], 30)))->addClass('mf-node-host'));
    }

    $metrics = (new CDiv())->addClass('mf-node-metrics');
    if ($nodes[$i]['cpu'] !== '') {
        $metrics->addItem((new CDiv('CPU '.$nodes[$i]['cpu']))->addClass('mf-node-metric-chip'));
    }
    if ($nodes[$i]['mem'] !== '') {
        $metrics->addItem((new CDiv('MEM '.$nodes[$i]['mem']))->addClass('mf-node-metric-chip'));
    }
    if ($nodes[$i]['cpu'] !== '' || $nodes[$i]['mem'] !== '') {
        $node->addItem($metrics);
    }

    $canvas->addItem($node);
}

$extras = (new CDiv())->addClass('mf-extra-panel');

for ($i = 1; $i <= $extra_count; $i++) {
    $label = trim((string) ($data['extra'.$i.'_label'] ?? ''));
    $host = trim((string) ($data['extra'.$i.'_host'] ?? ''));
    $value = trim((string) ($data['extra'.$i.'_value'] ?? ''));

    if ($label === '' && $host === '' && $value === '') {
        continue;
    }

    $card = (new CDiv())->addClass('mf-extra-card');

    $title = $label !== '' ? $label : 'Extra '.$i;
    $card->addItem((new CDiv($title))->addClass('mf-extra-title'));

    if ($host !== '') {
        $card->addItem((new CDiv($shortText($host, 28)))->addClass('mf-extra-host'));
    }

    $card->addItem((new CDiv($value !== '' ? $value : 'No value'))->addClass('mf-extra-value'));
    $extras->addItem($card);
}

$legend_text = (($data['demo_mode'] ?? '0') === '1')
    ? 'Demo fallback enabled: missing items use random values.'
    : 'High traffic = thicker line, hotter color, faster balls.';

(new CWidgetView($data))
    ->addItem(
        (new CDiv())
            ->addClass('mf-root')
            ->addItem((new CDiv('Matrix Firewall'))->addClass('mf-title'))
            ->addItem((new CDiv('Traffic heatmap + status strip'))->addClass('mf-subtitle'))
            ->addItem($status_bar)
            ->addItem($canvas)
            ->addItem((new CDiv($legend_text))->addClass('mf-legend'))
            ->addItem($extras)
    )
    ->show();
