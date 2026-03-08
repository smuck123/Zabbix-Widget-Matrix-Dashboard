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
$max_matrix_values = 20;
$max_sparks = 6;

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

$normalizeId = static function($value): string {
    $id = trim((string) $value);
    return ($id !== '' && $id !== '0') ? $id : '';
};

$getDrilldownUrl = static function(string $hostid, string $itemid = '', int $mode = WidgetForm::LINK_DRILLDOWN_AUTO): string {
    if ($mode === WidgetForm::LINK_DRILLDOWN_PROBLEMS) {
        if ($hostid !== '' && $hostid !== '0') {
            return 'zabbix.php?action=problem.view&filter_set=1&hostids[]='.$hostid;
        }

        return '';
    }

    if ($mode === WidgetForm::LINK_DRILLDOWN_LATEST) {
        if ($hostid !== '' && $hostid !== '0') {
            return 'zabbix.php?action=latest.view&hostids[]='.$hostid;
        }

        return '';
    }

    if ($itemid !== '' && $itemid !== '0') {
        return 'history.php?action=showgraph&itemids[]='.$itemid;
    }

    if ($hostid !== '' && $hostid !== '0') {
        return 'zabbix.php?action=latest.view&hostids[]='.$hostid;
    }

    return '';
};

$applyDrilldown = static function($element, string $url, string $title = 'Open drill-down'): void {
    if ($url === '') {
        return;
    }

    $safe_url = str_replace("'", "\\'", $url);

    $element->addClass('mf-drilldown');
    $element->setAttribute('data-mf-drilldown-url', $url);
    $element->setAttribute('title', $title);
    $element->setAttribute('tabindex', '0');
    $element->setAttribute('role', 'link');
    $element->setAttribute('onclick', "window.open('{$safe_url}', '_blank', 'noopener'); return false;");
};

$getNodeTypeMeta = static function(int $type): array {
    switch ($type) {
        case WidgetForm::NODE_TYPE_FIREWALL:
            return ['text' => 'FW', 'class' => 'mf-node-type-firewall'];
        case WidgetForm::NODE_TYPE_CLOUD:
            return ['text' => 'CLD', 'class' => 'mf-node-type-cloud'];
        case WidgetForm::NODE_TYPE_SERVER:
            return ['text' => 'SRV', 'class' => 'mf-node-type-server'];
        case WidgetForm::NODE_TYPE_OFFICE:
            return ['text' => 'OFC', 'class' => 'mf-node-type-office'];
        case WidgetForm::NODE_TYPE_EXPRESSROUTE:
            return ['text' => 'XR', 'class' => 'mf-node-type-expressroute'];
        case WidgetForm::NODE_TYPE_INTERNET:
            return ['text' => 'NET', 'class' => 'mf-node-type-internet'];
        case WidgetForm::NODE_TYPE_DATACENTER:
            return ['text' => 'DC', 'class' => 'mf-node-type-datacenter'];
        case WidgetForm::NODE_TYPE_DATABASE:
            return ['text' => 'DB', 'class' => 'mf-node-type-database'];
        default:
            return ['text' => 'GEN', 'class' => 'mf-node-type-generic'];
    }
};

$getMatrixDurations = static function(int $speed): array {
    switch ($speed) {
        case WidgetForm::MATRIX_SPEED_SLOW:
            return [18, 14, 16, 20, 15, 19, 17, 21, 16, 22, 18, 20];
        case WidgetForm::MATRIX_SPEED_FAST:
            return [8, 6, 7, 9, 7, 10, 8, 11, 6, 9, 7, 8];
        case WidgetForm::MATRIX_SPEED_VERY_FAST:
            return [4, 3, 5, 4, 3, 5, 4, 6, 3, 4, 5, 3];
        default:
            return [12, 8, 10, 14, 9, 13, 11, 15, 10, 12, 11, 13];
    }
};

$getTrafficStyle = static function(float $traffic): array {
    $color = '#6bff9e';
    $width = 2.5;
    $glow_width = 9;
    $dur = 2.4;
    $balls = 1;

    if ($traffic >= 100000000) {
        $width = 3.0;
        $glow_width = 12;
        $dur = 1.8;
    }

    if ($traffic >= 500000000) {
        $color = '#ffd84d';
        $width = 4.0;
        $glow_width = 15;
        $dur = 1.25;
        $balls = 2;
    }

    if ($traffic >= 1000000000) {
        $color = '#ff5f5f';
        $width = 5.5;
        $glow_width = 18;
        $dur = 0.8;
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

$getMidPointOnPolyline = static function(array $points): array {
    $segments = [];
    $total = 0.0;

    for ($i = 0; $i < count($points) - 1; $i++) {
        $x1 = $points[$i][0];
        $y1 = $points[$i][1];
        $x2 = $points[$i + 1][0];
        $y2 = $points[$i + 1][1];
        $len = sqrt(($x2 - $x1) * ($x2 - $x1) + ($y2 - $y1) * ($y2 - $y1));

        $segments[] = [
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x2,
            'y2' => $y2,
            'len' => $len
        ];

        $total += $len;
    }

    if ($total <= 0) {
        return [$points[0][0], $points[0][1]];
    }

    $half = $total / 2.0;
    $walk = 0.0;

    foreach ($segments as $seg) {
        if (($walk + $seg['len']) >= $half) {
            $remain = $half - $walk;
            $ratio = ($seg['len'] > 0) ? ($remain / $seg['len']) : 0;

            return [
                $seg['x1'] + (($seg['x2'] - $seg['x1']) * $ratio),
                $seg['y1'] + (($seg['y2'] - $seg['y1']) * $ratio)
            ];
        }

        $walk += $seg['len'];
    }

    $last = end($points);
    return [$last[0], $last[1]];
};

$getSparkPortMeta = static function(string $port): array {
    $p = (int) $port;

    if ($p === 22) {
        return ['key' => 'ssh', 'title' => 'SSH', 'class' => 'mf-spark-port-ssh'];
    }
    if (in_array($p, [80, 8080], true)) {
        return ['key' => 'web', 'title' => 'WEB', 'class' => 'mf-spark-port-web'];
    }
    if (in_array($p, [443, 8443], true)) {
        return ['key' => 'tls', 'title' => 'TLS', 'class' => 'mf-spark-port-tls'];
    }
    if ($p === 3389) {
        return ['key' => 'rdp', 'title' => 'RDP', 'class' => 'mf-spark-port-rdp'];
    }
    if ($p === 5228) {
        return ['key' => 'app', 'title' => 'APP', 'class' => 'mf-spark-port-app'];
    }
    if ($p >= 27000 && $p <= 27100) {
        return ['key' => 'game', 'title' => 'GAME', 'class' => 'mf-spark-port-game'];
    }

    return ['key' => 'other', 'title' => 'OTHER', 'class' => 'mf-spark-port-default'];
};

$node_count = $clampInt($data['node_count'] ?? 5, 1, $max_nodes, 5);
$link_count = $clampInt($data['link_count'] ?? 3, 0, $max_links, 3);
$extra_count = $clampInt($data['extra_count'] ?? 0, 0, $max_extras, 0);
$status_count = $clampInt($data['status_count'] ?? 0, 0, $max_status, 0);
$layout_mode = $clampInt($data['layout_mode'] ?? 0, 0, 1, 0);
$matrix_speed = $clampInt($data['matrix_speed'] ?? 1, 0, 3, 1);
$matrix_value_count = $clampInt($data['matrix_value_count'] ?? 8, 0, $max_matrix_values, 8);
$spark_count = $clampInt($data['spark_count'] ?? 0, 0, $max_sparks, 0);

$nodes = [];

if ($layout_mode === WidgetForm::LAYOUT_MANUAL) {
    for ($i = 1; $i <= $node_count; $i++) {
        $nodes[$i] = [
            'label' => trim((string) ($data['node'.$i.'_label'] ?? '')),
            'type' => $clampInt($data['node'.$i.'_type'] ?? 0, 0, 8, 0),
            'theme' => $clampInt($data['node'.$i.'_theme'] ?? WidgetForm::NODE_THEME_BOX, WidgetForm::NODE_THEME_BOX, WidgetForm::NODE_THEME_OUTLINE, WidgetForm::NODE_THEME_BOX),
            'hostid' => $normalizeId($data['node'.$i.'_hostid'] ?? ''),
            'host' => trim((string) ($data['node'.$i.'_host'] ?? '')),
            'cpu' => trim((string) ($data['node'.$i.'_cpu_value'] ?? '')),
            'mem' => trim((string) ($data['node'.$i.'_mem_value'] ?? '')),
            'cpu_itemid' => $normalizeId($data['node'.$i.'_cpu_itemid'] ?? ''),
            'mem_itemid' => $normalizeId($data['node'.$i.'_mem_itemid'] ?? ''),
            'problem_count' => $clampInt($data['node'.$i.'_problem_count'] ?? 0, 0, 9999, 0),
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
            'type' => $clampInt($data['node'.$i.'_type'] ?? 0, 0, 8, 0),
            'theme' => $clampInt($data['node'.$i.'_theme'] ?? WidgetForm::NODE_THEME_BOX, WidgetForm::NODE_THEME_BOX, WidgetForm::NODE_THEME_OUTLINE, WidgetForm::NODE_THEME_BOX),
            'hostid' => $normalizeId($data['node'.$i.'_hostid'] ?? ''),
            'host' => trim((string) ($data['node'.$i.'_host'] ?? '')),
            'cpu' => trim((string) ($data['node'.$i.'_cpu_value'] ?? '')),
            'mem' => trim((string) ($data['node'.$i.'_mem_value'] ?? '')),
            'cpu_itemid' => $normalizeId($data['node'.$i.'_cpu_itemid'] ?? ''),
            'mem_itemid' => $normalizeId($data['node'.$i.'_mem_itemid'] ?? ''),
            'problem_count' => $clampInt($data['node'.$i.'_problem_count'] ?? 0, 0, 9999, 0),
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
    $hostid = $normalizeId($data['status'.$i.'_hostid'] ?? '');
    $itemid = $normalizeId($data['status'.$i.'_itemid'] ?? '');
    $status_url = $getDrilldownUrl($hostid, $itemid);

    if ($label === '' && $value === '') {
        continue;
    }

    $chip = (new CDiv())->addClass('mf-status-chip mf-status-'.$class);
    $applyDrilldown($chip, $status_url);
    $chip->addItem((new CDiv($label !== '' ? $label : 'Status '.$i))->addClass('mf-status-chip-label'));
    $chip->addItem((new CDiv($value !== '' ? $value : 'No value'))->addClass('mf-status-chip-value'));
    $status_bar->addItem($chip);
}

$matrix_values = [];
for ($i = 1; $i <= $matrix_value_count; $i++) {
    $prefix = trim((string) ($data['matrix'.$i.'_label'] ?? ''));
    $value = trim((string) ($data['matrix'.$i.'_value'] ?? ''));
    $static = trim((string) ($data['matrix'.$i.'_static'] ?? ''));

    $text = '';
    if ($value !== '' && $prefix !== '') {
        $text = $prefix.' '.$value;
    }
    elseif ($value !== '') {
        $text = $value;
    }
    elseif ($static !== '') {
        $text = $static;
    }
    elseif ($prefix !== '') {
        $text = $prefix;
    }

    if ($text !== '') {
        $matrix_values[] = $text;
    }
}

if (!$matrix_values) {
    $matrix_values = [
        'MATRIXRAIN',
        'ZABBIXNETWORKFLOW',
        'FORTIGATEMONITOR',
        'INOUTTRAFFIC',
        'LATENCYHEALTH',
        'LINKSTATUS',
        'PACKETSERRORS',
        'NEONTRACE'
    ];
}

$durations = $getMatrixDurations($matrix_speed);
$summary_high = $clampInt($data['summary_high'] ?? 0, 0, 99999, 0);
$summary_disaster = $clampInt($data['summary_disaster'] ?? 0, 0, 99999, 0);

$svg = new CTag('svg', true);
$svg->setAttribute('class', 'mf-svg');
$svg->setAttribute('viewBox', '0 0 1000 700');
$svg->setAttribute('preserveAspectRatio', 'none');

$link_labels_layer = (new CDiv())->addClass('mf-link-label-layer');
$link_lane_counts = [];

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
    $route_style = $clampInt($data['link'.$i.'_style'] ?? 0, WidgetForm::LINK_STYLE_ELBOW, WidgetForm::LINK_STYLE_ZIGZAG, WidgetForm::LINK_STYLE_ELBOW);
    $show_label = $clampInt($data['link'.$i.'_show_label'] ?? 1, 0, 1, 1) === 1;
    $in_hostid = $normalizeId($data['link'.$i.'_in_hostid'] ?? '');
    $out_hostid = $normalizeId($data['link'.$i.'_out_hostid'] ?? '');
    $health_hostid = $normalizeId($data['link'.$i.'_health_hostid'] ?? '');
    $in_itemid = $normalizeId($data['link'.$i.'_in_itemid'] ?? '');
    $out_itemid = $normalizeId($data['link'.$i.'_out_itemid'] ?? '');
    $loss_itemid = $normalizeId($data['link'.$i.'_loss_itemid'] ?? '');
    $latency_itemid = $normalizeId($data['link'.$i.'_latency_itemid'] ?? '');
    $errors_itemid = $normalizeId($data['link'.$i.'_errors_itemid'] ?? '');

    $link_itemid = $in_itemid !== '' ? $in_itemid : ($out_itemid !== '' ? $out_itemid : ($loss_itemid !== '' ? $loss_itemid : ($latency_itemid !== '' ? $latency_itemid : $errors_itemid)));
    $link_hostid = $health_hostid !== '' ? $health_hostid : ($in_hostid !== '' ? $in_hostid : $out_hostid);
    $link_drilldown_mode = $clampInt($data['link'.$i.'_drilldown'] ?? WidgetForm::LINK_DRILLDOWN_AUTO, WidgetForm::LINK_DRILLDOWN_AUTO, WidgetForm::LINK_DRILLDOWN_PROBLEMS, WidgetForm::LINK_DRILLDOWN_AUTO);
    $link_url = $getDrilldownUrl($link_hostid, $link_itemid, $link_drilldown_mode);

    $node_box_w = 72;
    $node_box_h = 46;

    $cx1 = ($nodes[$from]['x'] + 4.7) * 10;
    $cy1 = ($nodes[$from]['y'] + 4.4) * 7;

    $cx2 = ($nodes[$to]['x'] + 4.7) * 10;
    $cy2 = ($nodes[$to]['y'] + 4.4) * 7;

    $dx = $cx2 - $cx1;
    $dy = $cy2 - $cy1;

    $start_x = $cx1;
    $start_y = $cy1;
    $end_x = $cx2;
    $end_y = $cy2;

    if (abs($dx) >= abs($dy)) {
        $start_x += ($dx >= 0) ? $node_box_w : -$node_box_w;
        $end_x   -= ($dx >= 0) ? $node_box_w : -$node_box_w;
    }
    else {
        $start_y += ($dy >= 0) ? $node_box_h : -$node_box_h;
        $end_y   -= ($dy >= 0) ? $node_box_h : -$node_box_h;
    }

    $pair_key = ($from < $to) ? ($from.'-'.$to) : ($to.'-'.$from);
    $lane_index = $link_lane_counts[$pair_key] ?? 0;
    $link_lane_counts[$pair_key] = $lane_index + 1;
    $lane_direction = ($lane_index % 2 === 0) ? 1 : -1;
    $lane_level = (int) floor($lane_index / 2) + 1;
    $lane = $lane_direction * (10 + ($lane_level * 12));
    $path_d = '';
    $label_points = [];

    $line_class = 'mf-svg-line';
    $ball_class = 'mf-svg-ball';

    if ($route_style === WidgetForm::LINK_STYLE_STRAIGHT || $route_style === WidgetForm::LINK_STYLE_FILE_TRANSFER) {
        $path_d = 'M'.round($start_x, 2).','.round($start_y, 2).' L'.round($end_x, 2).','.round($end_y, 2);
        $label_points = [[$start_x, $start_y], [$end_x, $end_y]];

        $glow = new CTag('line', true);
        $glow->setAttribute('x1', (string) round($start_x, 2));
        $glow->setAttribute('y1', (string) round($start_y, 2));
        $glow->setAttribute('x2', (string) round($end_x, 2));
        $glow->setAttribute('y2', (string) round($end_y, 2));
        $glow->setAttribute('stroke', $style['color']);
        $glow->setAttribute('stroke-width', (string) $style['glow_width']);
        $glow->setAttribute('class', 'mf-svg-glow');

        $line = new CTag('line', true);
        $line->setAttribute('x1', (string) round($start_x, 2));
        $line->setAttribute('y1', (string) round($start_y, 2));
        $line->setAttribute('x2', (string) round($end_x, 2));
        $line->setAttribute('y2', (string) round($end_y, 2));
        $line->setAttribute('stroke', $style['color']);
        $line->setAttribute('stroke-width', (string) $style['width']);

        if ($route_style === WidgetForm::LINK_STYLE_FILE_TRANSFER) {
            $line_class = 'mf-svg-line mf-svg-line-file-transfer';
            $ball_class = 'mf-svg-ball mf-svg-ball-file-transfer';
            $line->setAttribute('stroke-dasharray', '22 8');
        }

        $line->setAttribute('class', $line_class);
        $svg->addItem($glow);
        $svg->addItem($line);
    }
    elseif ($route_style === WidgetForm::LINK_STYLE_CURVED || $route_style === WidgetForm::LINK_STYLE_JUMPING) {
        $curve_lane = ($route_style === WidgetForm::LINK_STYLE_JUMPING) ? ($lane * 1.6) : $lane;
        $c1x = $start_x + (($end_x - $start_x) * 0.35);
        $c1y = $start_y + $curve_lane;
        $c2x = $start_x + (($end_x - $start_x) * 0.65);
        $c2y = $end_y + $curve_lane;

        $path_d = 'M'.round($start_x, 2).','.round($start_y, 2)
            .' C'.round($c1x, 2).','.round($c1y, 2)
            .' '.round($c2x, 2).','.round($c2y, 2)
            .' '.round($end_x, 2).','.round($end_y, 2);

        $label_points = [[$start_x, $start_y], [($start_x + $end_x) / 2, (($start_y + $end_y) / 2) + $curve_lane], [$end_x, $end_y]];

        $glow = new CTag('path', true);
        $glow->setAttribute('d', $path_d);
        $glow->setAttribute('fill', 'none');
        $glow->setAttribute('stroke', $style['color']);
        $glow->setAttribute('stroke-width', (string) $style['glow_width']);
        $glow->setAttribute('class', 'mf-svg-glow');

        $line = new CTag('path', true);
        $line->setAttribute('d', $path_d);
        $line->setAttribute('fill', 'none');
        $line->setAttribute('stroke', $style['color']);
        $line->setAttribute('stroke-width', (string) $style['width']);

        if ($route_style === WidgetForm::LINK_STYLE_JUMPING) {
            $line_class = 'mf-svg-line mf-svg-line-jumping';
            $ball_class = 'mf-svg-ball mf-svg-ball-jumping';
        }

        $line->setAttribute('class', $line_class);
        $svg->addItem($glow);
        $svg->addItem($line);
    }
    else {
        if (abs($dx) >= abs($dy)) {
            $mid_x = ($start_x + $end_x) / 2 + $lane;
            $label_points = [
                [$start_x, $start_y],
                [$mid_x, $start_y],
                [$mid_x, $end_y],
                [$end_x, $end_y]
            ];
        }
        else {
            $mid_y = ($start_y + $end_y) / 2 + $lane;
            $label_points = [
                [$start_x, $start_y],
                [$start_x, $mid_y],
                [$end_x, $mid_y],
                [$end_x, $end_y]
            ];
        }

        if ($route_style === WidgetForm::LINK_STYLE_ZIGZAG) {
            $zigzag_points = [];
            $zigzag_points[] = $label_points[0];
            for ($p = 1; $p < count($label_points); $p++) {
                $from_point = $label_points[$p - 1];
                $to_point = $label_points[$p];
                $mid_x = ($from_point[0] + $to_point[0]) / 2;
                $mid_y = ($from_point[1] + $to_point[1]) / 2;

                if (abs($from_point[0] - $to_point[0]) >= abs($from_point[1] - $to_point[1])) {
                    $zigzag_points[] = [$mid_x, $mid_y + (($p % 2 === 0) ? -12 : 12)];
                }
                else {
                    $zigzag_points[] = [$mid_x + (($p % 2 === 0) ? -12 : 12), $mid_y];
                }

                $zigzag_points[] = $to_point;
            }
            $label_points = $zigzag_points;
            $line_class = 'mf-svg-line mf-svg-line-zigzag';
        }
        elseif ($route_style === WidgetForm::LINK_STYLE_DOTS) {
            $line_class = 'mf-svg-line mf-svg-line-dots';
            $ball_class = 'mf-svg-ball mf-svg-ball-dots';
        }
        elseif ($route_style === WidgetForm::LINK_STYLE_EXPLOSIVE) {
            $line_class = 'mf-svg-line mf-svg-line-explosive';
            $ball_class = 'mf-svg-ball mf-svg-ball-explosive';
        }

        $polyline_points = [];
        foreach ($label_points as $p) {
            $polyline_points[] = round($p[0], 2).','.round($p[1], 2);
        }
        $polyline_str = implode(' ', $polyline_points);

        $path_d = 'M'.round($label_points[0][0], 2).','.round($label_points[0][1], 2);
        for ($p = 1; $p < count($label_points); $p++) {
            $path_d .= ' L'.round($label_points[$p][0], 2).','.round($label_points[$p][1], 2);
        }

        $glow = new CTag('polyline', true);
        $glow->setAttribute('points', $polyline_str);
        $glow->setAttribute('fill', 'none');
        $glow->setAttribute('stroke', $style['color']);
        $glow->setAttribute('stroke-width', (string) $style['glow_width']);
        $glow->setAttribute('class', 'mf-svg-glow');

        $line = new CTag('polyline', true);
        $line->setAttribute('points', $polyline_str);
        $line->setAttribute('fill', 'none');
        $line->setAttribute('stroke', $style['color']);
        $line->setAttribute('stroke-width', (string) $style['width']);
        $line->setAttribute('class', $line_class);

        $svg->addItem($glow);
        $svg->addItem($line);
    }

    for ($b = 0; $b < $style['balls']; $b++) {
        $ball = new CTag('circle', true);
        $ball->setAttribute('r', (string) (5 + $b));
        $ball->setAttribute('fill', $style['color']);
        $ball->setAttribute('class', $ball_class);

        $animate = new CTag('animateMotion', true);
        $animate->setAttribute('dur', (string) ($style['dur'] + ($b * 0.22)).'s');
        $animate->setAttribute('begin', (string) ($b * 0.18).'s');
        $animate->setAttribute('repeatCount', 'indefinite');
        $animate->setAttribute('path', $path_d);

        $ball->addItem($animate);
        $svg->addItem($ball);
    }

    foreach ([[$start_x, $start_y], [$end_x, $end_y]] as $ep) {
        $pulse = new CTag('circle', true);
        $pulse->setAttribute('cx', (string) round($ep[0], 2));
        $pulse->setAttribute('cy', (string) round($ep[1], 2));
        $pulse->setAttribute('r', '5');
        $pulse->setAttribute('fill', $style['color']);
        $pulse->setAttribute('opacity', '0.75');
        $pulse->setAttribute('class', 'mf-svg-ball');
        $svg->addItem($pulse);
    }

    if ($show_label) {
        [$mx, $my] = $getMidPointOnPolyline($label_points);

        $label_box = (new CDiv())->addClass('mf-link-label'.($has_error ? ' mf-link-label-error' : ''));
        $applyDrilldown($label_box, $link_url);

        if ($label !== '') {
            $label_box->addItem((new CDiv($shortText($label, 16)))->addClass('mf-link-title'));
        }

        $flow = (new CDiv())->addClass('mf-link-flow');
        $flow->addItem((new CDiv('IN '.($in_value !== '' ? $in_value : '-')))->addClass('mf-link-flow-chip'));
        $flow->addItem((new CDiv('OUT '.($out_value !== '' ? $out_value : '-')))->addClass('mf-link-flow-chip'));
        $label_box->addItem($flow);

        $bottom_metrics = (new CDiv())->addClass('mf-link-bottom');
        if ($loss_value !== '') $bottom_metrics->addItem((new CDiv('LOSS '.$loss_value))->addClass('mf-link-bottom-chip'));
        if ($latency_value !== '') $bottom_metrics->addItem((new CDiv('LAT '.$latency_value))->addClass('mf-link-bottom-chip'));
        if ($errors_value !== '') $bottom_metrics->addItem((new CDiv('ERR '.$errors_value))->addClass('mf-link-bottom-chip'));
        if ($loss_value !== '' || $latency_value !== '' || $errors_value !== '') {
            $label_box->addItem($bottom_metrics);
        }

        $label_box->setAttribute(
            'style',
            '--mf-link-color: '.$style['color'].'; left: calc('.round($mx / 10, 2).'%' .
            ' - 74px); top: calc('.round($my / 7, 2).'%' .
            ' - 24px);'
        );

        $link_labels_layer->addItem($label_box);
    }
}

$canvas = (new CDiv())->addClass('mf-canvas');

$alert_summary = (new CDiv())->addClass('mf-alert-summary');
$alert_summary->addItem((new CDiv('Critical alerts'))->addClass('mf-alert-summary-title'));
$alert_summary->addItem((new CDiv('Disaster '.$summary_disaster))->addClass('mf-alert-chip mf-alert-chip-disaster'));
$alert_summary->addItem((new CDiv('High '.$summary_high))->addClass('mf-alert-chip mf-alert-chip-high'));

$matrix_bg = (new CDiv())->addClass('mf-matrix-bg');
$matrix_repeat = ($matrix_speed === WidgetForm::MATRIX_SPEED_VERY_FAST) ? 5 : 3;

for ($i = 0; $i < count($matrix_values); $i++) {
    $text = trim(str_repeat($matrix_values[$i].' ', $matrix_repeat));
    $column = (new CDiv($text))->addClass('mf-column');
    $column->setAttribute('style', 'animation-duration: '.$durations[$i % count($durations)].'s;');
    $matrix_bg->addItem($column);
}

$canvas
    ->addItem($matrix_bg)
    ->addItem($svg)
    ->addItem($alert_summary)
    ->addItem($link_labels_layer);

for ($i = 1; $i <= $node_count; $i++) {
    $type_meta = $getNodeTypeMeta($nodes[$i]['type']);

    $node = (new CDiv())
        ->addClass('mf-node mf-node-theme-'.$nodes[$i]['theme'].($nodes[$i]['has_error'] ? ' mf-node-error' : ''))
        ->setAttribute('style', 'left: '.$nodes[$i]['x'].'%; top: '.$nodes[$i]['y'].'%;');

    $node_url = $getDrilldownUrl($nodes[$i]['hostid']);
    $applyDrilldown($node, $node_url, 'Open host values');

    $head = (new CDiv())->addClass('mf-node-head');
    $head->addItem(
        (new CDiv($type_meta['text']))
            ->addClass('mf-node-icon '.$type_meta['class'])
    );
    $head->addItem((new CDiv($nodes[$i]['label'] !== '' ? $nodes[$i]['label'] : 'Node '.$i))->addClass('mf-node-title'));
    $node->addItem($head);

    if ($nodes[$i]['host'] !== '') {
        $node->addItem((new CDiv($shortText($nodes[$i]['host'], 30)))->addClass('mf-node-host'));
    }

    if ($nodes[$i]['problem_count'] > 0) {
        $node->addItem((new CDiv($nodes[$i]['problem_count'].' problem'.($nodes[$i]['problem_count'] === 1 ? '' : 's')))->addClass('mf-node-problems'));
    }

    $metrics = (new CDiv())->addClass('mf-node-metrics');
    if ($nodes[$i]['cpu'] !== '') $metrics->addItem((new CDiv('CPU '.$nodes[$i]['cpu']))->addClass('mf-node-metric-chip'));
    if ($nodes[$i]['mem'] !== '') $metrics->addItem((new CDiv('MEM '.$nodes[$i]['mem']))->addClass('mf-node-metric-chip'));
    if ($nodes[$i]['cpu'] !== '' || $nodes[$i]['mem'] !== '') $node->addItem($metrics);

    $canvas->addItem($node);
}

for ($i = 1; $i <= $spark_count; $i++) {
    $label = trim((string) ($data['spark'.$i.'_label'] ?? ''));
    $host = trim((string) ($data['spark'.$i.'_host'] ?? ''));
    $group_mode = $clampInt($data['spark'.$i.'_group_mode'] ?? 0, 0, 2, 0);
    $x = $clampInt($data['spark'.$i.'_x'] ?? 50, 5, 95, 50);
    $y = $clampInt($data['spark'.$i.'_y'] ?? 50, 5, 90, 50);
    $items = $data['spark'.$i.'_items'] ?? [];
    $count = $clampInt($data['spark'.$i.'_count'] ?? 0, 0, 100, 0);
    $error = trim((string) ($data['spark'.$i.'_error'] ?? ''));

    $item1_label = trim((string) ($data['spark'.$i.'_item1_label'] ?? ''));
    $item1_value = trim((string) ($data['spark'.$i.'_item1_value'] ?? ''));
    $item2_label = trim((string) ($data['spark'.$i.'_item2_label'] ?? ''));
    $item2_value = trim((string) ($data['spark'.$i.'_item2_value'] ?? ''));
    $spark_hostid = $normalizeId($data['spark'.$i.'_hostid'] ?? '');
    $spark_itemid = $normalizeId($data['spark'.$i.'_itemid'] ?? '');
    $spark_item1_itemid = $normalizeId($data['spark'.$i.'_item1_itemid'] ?? '');
    $spark_item2_itemid = $normalizeId($data['spark'.$i.'_item2_itemid'] ?? '');

    $spark_target_itemid = $spark_itemid !== '' ? $spark_itemid : ($spark_item1_itemid !== '' ? $spark_item1_itemid : $spark_item2_itemid);
    $spark_url = $getDrilldownUrl($spark_hostid, $spark_target_itemid);

    if ($label === '' && $host === '' && !$items && $error === '') {
        continue;
    }

    $spark = (new CDiv())->addClass('mf-spark');
    $spark->setAttribute('style', 'left: '.$x.'%; top: '.$y.'%;');
    $applyDrilldown($spark, $spark_url);

    $spark->addItem((new CDiv(''))->addClass('mf-spark-core'));
    $spark->addItem((new CDiv($label !== '' ? $label : 'Spark '.$i))->addClass('mf-spark-title'));

    if ($host !== '') {
        $spark->addItem((new CDiv($shortText($host, 24)))->addClass('mf-spark-host'));
    }

    if ($error !== '') {
        $spark->addItem((new CDiv($error))->addClass('mf-spark-error'));
    }
    else {
        $spark->addItem((new CDiv('Flows '.$count))->addClass('mf-spark-count'));

        $own = (new CDiv())->addClass('mf-spark-own-items');
        if ($item1_label !== '' || $item1_value !== '') {
            $own->addItem((new CDiv(trim($item1_label.' '.$item1_value)))->addClass('mf-spark-own-line'));
        }
        if ($item2_label !== '' || $item2_value !== '') {
            $own->addItem((new CDiv(trim($item2_label.' '.$item2_value)))->addClass('mf-spark-own-line'));
        }
        if ($item1_label !== '' || $item1_value !== '' || $item2_label !== '' || $item2_value !== '') {
            $spark->addItem($own);
        }

        $groups = [];

        foreach ($items as $entry) {
            $process = trim((string) ($entry['process'] ?? ''));
            $ip = trim((string) ($entry['ip'] ?? ''));
            $port = trim((string) ($entry['port'] ?? ''));
            $port_meta = $getSparkPortMeta($port);

            if ($group_mode === 1) {
                $group_key = strtolower($process !== '' ? $process : 'process');
                $group_title = strtoupper($process !== '' ? $process : 'PROCESS');
                $group_class = $port_meta['class'];
            }
            elseif ($group_mode === 2) {
                $group_key = strtolower($ip !== '' ? $ip : 'unknown');
                $group_title = $ip !== '' ? $ip : 'UNKNOWN';
                $group_class = $port_meta['class'];
            }
            else {
                $group_key = $port_meta['key'];
                $group_title = $port_meta['title'];
                $group_class = $port_meta['class'];
            }

            if (!array_key_exists($group_key, $groups)) {
                $groups[$group_key] = [
                    'title' => $group_title,
                    'class' => $group_class,
                    'count' => 0,
                    'rows' => [],
                    'seen_ip' => []
                ];
            }

            $groups[$group_key]['count']++;

            $ip_key = strtolower($ip);
            if ($ip_key !== '') {
                if (!array_key_exists($ip_key, $groups[$group_key]['seen_ip'])) {
                    if ($group_mode === 1) {
                        $display = $ip.($port !== '' ? ':'.$port : '');
                    }
                    elseif ($group_mode === 2) {
                        $display = $process !== '' ? $process.($port !== '' ? ':'.$port : '') : ($port !== '' ? ':'.$port : $ip);
                    }
                    else {
                        $display = ($process !== '' ? $process.' ' : '').$ip;
                    }

                    $groups[$group_key]['seen_ip'][$ip_key] = count($groups[$group_key]['rows']);
                    $groups[$group_key]['rows'][] = [
                        'text' => $display,
                        'count' => 1
                    ];
                }
                else {
                    $row_index = $groups[$group_key]['seen_ip'][$ip_key];
                    $groups[$group_key]['rows'][$row_index]['count']++;
                }
            }
            else {
                $groups[$group_key]['rows'][] = [
                    'text' => trim(($process !== '' ? $process : 'entry').($port !== '' ? ':'.$port : '')),
                    'count' => 1
                ];
            }
        }

        $slot_positions = [
            ['box_left' => 110,  'box_top' => -44,  'line_left' => 16,  'line_top' => 1,   'line_width' => 84, 'line_height' => 2,  'transform' => 'none'],
            ['box_left' => 90,   'box_top' => -184, 'line_left' => 8,   'line_top' => -34, 'line_width' => 88, 'line_height' => 2,  'transform' => 'rotate(-35deg)', 'origin' => 'left center'],
            ['box_left' => 90,   'box_top' => 120,  'line_left' => 8,   'line_top' => 35,  'line_width' => 88, 'line_height' => 2,  'transform' => 'rotate(35deg)', 'origin' => 'left center'],
            ['box_left' => -214, 'box_top' => -44,  'line_left' => -98, 'line_top' => 1,   'line_width' => 84, 'line_height' => 2,  'transform' => 'none'],
            ['box_left' => -194, 'box_top' => -184, 'line_left' => -80, 'line_top' => -34, 'line_width' => 88, 'line_height' => 2,  'transform' => 'rotate(35deg)', 'origin' => 'right center'],
            ['box_left' => -194, 'box_top' => 120,  'line_left' => -80, 'line_top' => 35,  'line_width' => 88, 'line_height' => 2,  'transform' => 'rotate(-35deg)', 'origin' => 'right center'],
            ['box_left' => -54,  'box_top' => -208, 'line_left' => -1,  'line_top' => -126, 'line_width' => 2, 'line_height' => 90, 'transform' => 'none'],
            ['box_left' => -54,  'box_top' => 158,  'line_left' => -1,  'line_top' => 26,  'line_width' => 2,  'line_height' => 90, 'transform' => 'none']
        ];

        $slot_index = 0;

        foreach ($groups as $group) {
            if ($slot_index >= count($slot_positions)) {
                break;
            }

            $slot = $slot_positions[$slot_index];
            $slot_index++;

            $line_style = 'left: '.$slot['line_left'].'px; top: '.$slot['line_top'].'px; width: '.$slot['line_width'].'px; height: '.$slot['line_height'].'px;';
            if (!empty($slot['transform']) && $slot['transform'] !== 'none') {
                $line_style .= ' transform: '.$slot['transform'].';';
            }
            if (!empty($slot['origin'])) {
                $line_style .= ' transform-origin: '.$slot['origin'].';';
            }

            $line = (new CDiv())->addClass('mf-spark-line '.$group['class']);
            $line->setAttribute('style', $line_style);
            $spark->addItem($line);

            $box = (new CDiv())->addClass('mf-spark-group-box '.$group['class']);
            $box->setAttribute('style', 'left: '.$slot['box_left'].'px; top: '.$slot['box_top'].'px;');

            $box->addItem(
                (new CDiv($group['title'].' '.$group['count']))
                    ->addClass('mf-spark-group-title')
            );

            $shown = 0;
            foreach ($group['rows'] as $row) {
                $shown++;

                if ($shown > 4) {
                    $remaining = count($group['rows']) - 4;
                    if ($remaining > 0) {
                        $box->addItem((new CDiv('+'.$remaining.' more'))->addClass('mf-spark-more'));
                    }
                    break;
                }

                $text = $row['text'];
                if ($row['count'] > 1) {
                    $text .= ' x'.$row['count'];
                }

                $box->addItem(
                    (new CDiv($shortText($text, 28)))
                        ->addClass('mf-spark-group-item')
                );
            }

            $spark->addItem($box);
        }
    }

    $canvas->addItem($spark);
}

$extras = (new CDiv())->addClass('mf-extra-panel');

for ($i = 1; $i <= $extra_count; $i++) {
    $label = trim((string) ($data['extra'.$i.'_label'] ?? ''));
    $host = trim((string) ($data['extra'.$i.'_host'] ?? ''));
    $value = trim((string) ($data['extra'.$i.'_value'] ?? ''));
    $hostid = $normalizeId($data['extra'.$i.'_hostid'] ?? '');
    $itemid = $normalizeId($data['extra'.$i.'_itemid'] ?? '');
    $extra_url = $getDrilldownUrl($hostid, $itemid);

    if ($label === '' && $host === '' && $value === '') {
        continue;
    }

    $card = (new CDiv())->addClass('mf-extra-card');
    $applyDrilldown($card, $extra_url);

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
