<?php

namespace Modules\MatrixFirewall\Actions;

use Modules\MatrixFirewall\Includes\WidgetForm;
use CControllerDashboardWidgetView;
use CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView {

    private function getField(array $fields, array $inputs, string $name, string $default = ''): string {
        if (array_key_exists($name, $inputs) && is_scalar($inputs[$name])) {
            return trim((string) $inputs[$name]);
        }

        if (array_key_exists($name, $fields)) {
            if (is_array($fields[$name]) && array_key_exists('value', $fields[$name])) {
                $value = $fields[$name]['value'];

                if (is_array($value)) {
                    return trim((string) implode(', ', $value));
                }

                return trim((string) $value);
            }

            if (is_scalar($fields[$name])) {
                return trim((string) $fields[$name]);
            }
        }

        return $default;
    }

    private function formatTraffic(string $value): string {
        $num = (float) $value;

        if ($num >= 1000000000) return round($num / 1000000000, 2).' Gbps';
        if ($num >= 1000000) return round($num / 1000000, 1).' Mbps';
        if ($num >= 1000) return round($num / 1000, 1).' Kbps';

        return round($num, 0).' bps';
    }

    private function formatGeneric(string $value): string {
        $value = trim((string) $value);
        if ($value === '') return '';

        if (is_numeric($value)) {
            $num = (float) $value;

            if (abs($num) >= 1000000000) return round($num / 1000000000, 2).'G';
            if (abs($num) >= 1000000) return round($num / 1000000, 2).'M';
            if (abs($num) >= 1000) return round($num / 1000, 2).'K';

            return (string) round($num, 2);
        }

        return $value;
    }

    private function hostIdToName(string $hostid): string {
        if ($hostid === '' || $hostid === '0') {
            return '';
        }

        $hosts = \API::Host()->get([
            'output' => ['hostid', 'host'],
            'hostids' => [(int) $hostid]
        ]);

        if (!empty($hosts[0]['host'])) {
            return (string) $hosts[0]['host'];
        }

        return '';
    }

    private function getRandomValue(string $key): array {
        $seed = abs(crc32($key.date('YmdHi')));

        if (stripos($key, 'cpu') !== false) {
            $val = 5 + ($seed % 70);
            return ['raw' => (float) $val, 'text' => (string) $val];
        }

        if (stripos($key, 'mem') !== false || stripos($key, 'memory') !== false) {
            $val = 20 + ($seed % 75);
            return ['raw' => (float) $val, 'text' => (string) $val];
        }

        if (stripos($key, 'lat') !== false) {
            $val = 2 + ($seed % 120);
            return ['raw' => (float) $val, 'text' => (string) $val];
        }

        if (stripos($key, 'loss') !== false) {
            $val = $seed % 4;
            return ['raw' => (float) $val, 'text' => (string) $val];
        }

        if (stripos($key, 'err') !== false) {
            $val = $seed % 20;
            return ['raw' => (float) $val, 'text' => (string) $val];
        }

        $bps = 5000000 + (($seed % 1600) * 1000000);
        return ['raw' => (float) $bps, 'text' => (string) $bps];
    }

    private function getRandomMatrixText(string $prefix = ''): string {
        $words = [
            'MATRIXRAIN', 'LINKFLOW', 'NEONTRACE', 'PACKETSTORM', 'PORTSCAN',
            'FIREWALL', 'AZUREEDGE', 'CPU', 'MEM', 'EXPRESSROUTE', 'LATENCY',
            'SESSIONS', 'ERRORS', 'NOCVIEW', 'FORTIGATE', 'TRAFFIC'
        ];

        shuffle($words);
        $value = $words[0].' '.rand(1, 9999);

        if ($prefix !== '') {
            return $prefix.' '.$value;
        }

        return $value;
    }

    private function getLatestRawByHostId(string $hostid, string $key, bool $allow_random = false): array {
        $result = [
            'raw' => 0.0,
            'text' => '',
            'is_random' => false,
            'has_error' => false
        ];

        if ($hostid === '' || $hostid === '0' || $key === '') {
            if ($allow_random && $key !== '') {
                $random = $this->getRandomValue($key);
                $random['is_random'] = true;
                $random['has_error'] = true;
                return $random;
            }

            $result['has_error'] = true;
            return $result;
        }

        $items = \API::Item()->get([
            'output' => ['itemid', 'key_', 'lastvalue'],
            'hostids' => [(int) $hostid],
            'filter' => ['key_' => [$key]]
        ]);

        if (isset($items[0]['lastvalue']) && $items[0]['lastvalue'] !== '') {
            $result['raw'] = (float) $items[0]['lastvalue'];
            $result['text'] = (string) $items[0]['lastvalue'];
            return $result;
        }

        $items = \API::Item()->get([
            'output' => ['itemid', 'key_', 'lastvalue'],
            'hostids' => [(int) $hostid],
            'search' => ['key_' => $key]
        ]);

        if (isset($items[0]['lastvalue']) && $items[0]['lastvalue'] !== '') {
            $result['raw'] = (float) $items[0]['lastvalue'];
            $result['text'] = (string) $items[0]['lastvalue'];
            return $result;
        }

        if ($allow_random) {
            $random = $this->getRandomValue($key);
            $random['is_random'] = true;
            $random['has_error'] = true;
            return $random;
        }

        $result['has_error'] = true;
        return $result;
    }

    private function getStatusText(string $raw, int $mode, string $good, string $warn, string $crit): array {
        $raw = trim($raw);

        if ($raw === '') {
            return ['text' => 'No value', 'class' => 'neutral'];
        }

        if ($mode === WidgetForm::STATUS_MODE_GOODBAD) {
            if ($raw === $good) return ['text' => 'Good', 'class' => 'good'];
            return ['text' => 'Bad', 'class' => 'bad'];
        }

        if ($mode === WidgetForm::STATUS_MODE_OKWARNCRIT) {
            if ($raw === $good) return ['text' => 'OK', 'class' => 'good'];
            if ($raw === $warn) return ['text' => 'Warning', 'class' => 'warn'];
            if ($raw === $crit) return ['text' => 'Critical', 'class' => 'bad'];
            return ['text' => $raw, 'class' => 'neutral'];
        }

        return ['text' => $raw, 'class' => 'neutral'];
    }

    private function parseSparkJson(string $json, int $max = 12): array {
        $result = [
            'count' => 0,
            'items' => [],
            'error' => ''
        ];

        if ($json === '') {
            $result['error'] = 'No data';
            return $result;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            $result['error'] = 'Invalid JSON';
            return $result;
        }

        if (empty($decoded['data']) || !is_array($decoded['data'])) {
            $result['error'] = 'No data array';
            return $result;
        }

        $items = array_slice($decoded['data'], 0, max(1, $max));

        foreach ($items as $item) {
            $process = trim((string) ($item['process'] ?? 'proc'));
            $ip = trim((string) ($item['r_ip'] ?? 'unknown'));
            $port = trim((string) ($item['r_port'] ?? ''));
            $label = $process.' '.$ip;
            if ($port !== '') {
                $label .= ':'.$port;
            }

            $result['items'][] = [
                'process' => $process,
                'ip' => $ip,
                'port' => $port,
                'label' => $label
            ];
        }

        $result['count'] = count($result['items']);
        return $result;
    }

    protected function doAction(): void {
        $fields = $this->getInput('fields', []);
        $inputs = $this->getInputAll();

        $demo_mode = (int) $this->getField($fields, $inputs, 'demo_mode', '0');
        $allow_random = ($demo_mode === WidgetForm::DEMO_RANDOM);

        $data = [
            'name' => $this->getInput('name', 'Matrix Firewall'),
            'layout_mode' => $this->getField($fields, $inputs, 'layout_mode', '0'),
            'demo_mode' => $this->getField($fields, $inputs, 'demo_mode', '0'),
            'matrix_speed' => $this->getField($fields, $inputs, 'matrix_speed', '1'),
            'node_count' => $this->getField($fields, $inputs, 'node_count', '5'),
            'link_count' => $this->getField($fields, $inputs, 'link_count', '3'),
            'extra_count' => $this->getField($fields, $inputs, 'extra_count', '0'),
            'status_count' => $this->getField($fields, $inputs, 'status_count', '0'),
            'matrix_value_count' => $this->getField($fields, $inputs, 'matrix_value_count', '8'),
            'spark_count' => $this->getField($fields, $inputs, 'spark_count', '0')
        ];

        for ($i = 1; $i <= WidgetForm::MAX_NODES; $i++) {
            $node_hostid = $this->getField($fields, $inputs, 'node'.$i.'_host', '0');

            $data['node'.$i.'_label'] = $this->getField($fields, $inputs, 'node'.$i.'_label', '');
            $data['node'.$i.'_type'] = $this->getField($fields, $inputs, 'node'.$i.'_type', '0');
            $data['node'.$i.'_hostid'] = $node_hostid;
            $data['node'.$i.'_host'] = $this->hostIdToName($node_hostid);
            $data['node'.$i.'_x'] = $this->getField($fields, $inputs, 'node'.$i.'_x', '10');
            $data['node'.$i.'_y'] = $this->getField($fields, $inputs, 'node'.$i.'_y', '10');

            $data['node'.$i.'_cpu_key'] = $this->getField($fields, $inputs, 'node'.$i.'_cpu_key', '');
            $data['node'.$i.'_mem_key'] = $this->getField($fields, $inputs, 'node'.$i.'_mem_key', '');

            $cpu = $this->getLatestRawByHostId($node_hostid, $data['node'.$i.'_cpu_key'], $allow_random);
            $mem = $this->getLatestRawByHostId($node_hostid, $data['node'.$i.'_mem_key'], $allow_random);

            $data['node'.$i.'_cpu_value'] = $this->formatGeneric($cpu['text']);
            $data['node'.$i.'_mem_value'] = $this->formatGeneric($mem['text']);
            $data['node'.$i.'_has_error'] = ($cpu['has_error'] || $mem['has_error']) ? '1' : '0';
        }

        for ($i = 1; $i <= WidgetForm::MAX_LINKS; $i++) {
            $in_hostid = $this->getField($fields, $inputs, 'link'.$i.'_in_host', '0');
            $out_hostid = $this->getField($fields, $inputs, 'link'.$i.'_out_host', '0');
            $health_hostid = $this->getField($fields, $inputs, 'link'.$i.'_health_host', '0');

            $data['link'.$i.'_label'] = $this->getField($fields, $inputs, 'link'.$i.'_label', '');
            $data['link'.$i.'_from'] = $this->getField($fields, $inputs, 'link'.$i.'_from', '1');
            $data['link'.$i.'_to'] = $this->getField($fields, $inputs, 'link'.$i.'_to', '2');
            $data['link'.$i.'_style'] = $this->getField($fields, $inputs, 'link'.$i.'_style', '0');
            $data['link'.$i.'_show_label'] = $this->getField($fields, $inputs, 'link'.$i.'_show_label', '1');

            $data['link'.$i.'_in_host'] = $this->hostIdToName($in_hostid);
            $data['link'.$i.'_out_host'] = $this->hostIdToName($out_hostid);
            $data['link'.$i.'_health_host'] = $this->hostIdToName($health_hostid);

            $data['link'.$i.'_in_key'] = $this->getField($fields, $inputs, 'link'.$i.'_in_key', '');
            $data['link'.$i.'_out_key'] = $this->getField($fields, $inputs, 'link'.$i.'_out_key', '');
            $data['link'.$i.'_loss_key'] = $this->getField($fields, $inputs, 'link'.$i.'_loss_key', '');
            $data['link'.$i.'_latency_key'] = $this->getField($fields, $inputs, 'link'.$i.'_latency_key', '');
            $data['link'.$i.'_errors_key'] = $this->getField($fields, $inputs, 'link'.$i.'_errors_key', '');

            $in_value = $this->getLatestRawByHostId($in_hostid, $data['link'.$i.'_in_key'], $allow_random);
            $out_value = $this->getLatestRawByHostId($out_hostid, $data['link'.$i.'_out_key'], $allow_random);
            $loss_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_loss_key'], $allow_random);
            $latency_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_latency_key'], $allow_random);
            $errors_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_errors_key'], $allow_random);

            $data['link'.$i.'_in_value'] = $this->formatTraffic($in_value['text']);
            $data['link'.$i.'_out_value'] = $this->formatTraffic($out_value['text']);
            $data['link'.$i.'_in_raw'] = $in_value['raw'];
            $data['link'.$i.'_out_raw'] = $out_value['raw'];

            $data['link'.$i.'_loss_value'] = $this->formatGeneric($loss_value['text']);
            $data['link'.$i.'_latency_value'] = $this->formatGeneric($latency_value['text']);
            $data['link'.$i.'_errors_value'] = $this->formatGeneric($errors_value['text']);
            $data['link'.$i.'_has_error'] = ($in_value['has_error'] || $out_value['has_error'] || $loss_value['has_error'] || $latency_value['has_error'] || $errors_value['has_error']) ? '1' : '0';
        }

        for ($i = 1; $i <= WidgetForm::MAX_EXTRAS; $i++) {
            $hostid = $this->getField($fields, $inputs, 'extra'.$i.'_host', '0');

            $data['extra'.$i.'_label'] = $this->getField($fields, $inputs, 'extra'.$i.'_label', '');
            $data['extra'.$i.'_host'] = $this->hostIdToName($hostid);
            $data['extra'.$i.'_key'] = $this->getField($fields, $inputs, 'extra'.$i.'_key', '');

            $extra_value = $this->getLatestRawByHostId($hostid, $data['extra'.$i.'_key'], $allow_random);
            $data['extra'.$i.'_value'] = $this->formatGeneric($extra_value['text']);
        }

        for ($i = 1; $i <= WidgetForm::MAX_STATUS; $i++) {
            $hostid = $this->getField($fields, $inputs, 'status'.$i.'_host', '0');
            $mode = (int) $this->getField($fields, $inputs, 'status'.$i.'_mode', (string) WidgetForm::STATUS_MODE_RAW);

            $data['status'.$i.'_label'] = $this->getField($fields, $inputs, 'status'.$i.'_label', '');
            $data['status'.$i.'_host'] = $this->hostIdToName($hostid);
            $data['status'.$i.'_key'] = $this->getField($fields, $inputs, 'status'.$i.'_key', '');
            $data['status'.$i.'_mode'] = (string) $mode;
            $data['status'.$i.'_good'] = $this->getField($fields, $inputs, 'status'.$i.'_good', '1');
            $data['status'.$i.'_warn'] = $this->getField($fields, $inputs, 'status'.$i.'_warn', '2');
            $data['status'.$i.'_crit'] = $this->getField($fields, $inputs, 'status'.$i.'_crit', '3');

            $status_value = $this->getLatestRawByHostId($hostid, $data['status'.$i.'_key'], $allow_random);
            $status = $this->getStatusText(
                $status_value['text'],
                $mode,
                $data['status'.$i.'_good'],
                $data['status'.$i.'_warn'],
                $data['status'.$i.'_crit']
            );

            $data['status'.$i.'_value'] = $status['text'];
            $data['status'.$i.'_class'] = $status['class'];
        }

        for ($i = 1; $i <= WidgetForm::MAX_MATRIX_VALUES; $i++) {
            $hostid = $this->getField($fields, $inputs, 'matrix'.$i.'_host', '0');
            $random_enabled = $this->getField($fields, $inputs, 'matrix'.$i.'_random', '0');

            $data['matrix'.$i.'_label'] = $this->getField($fields, $inputs, 'matrix'.$i.'_label', '');
            $data['matrix'.$i.'_host'] = $this->hostIdToName($hostid);
            $data['matrix'.$i.'_key'] = $this->getField($fields, $inputs, 'matrix'.$i.'_key', '');
            $data['matrix'.$i.'_static'] = $this->getField($fields, $inputs, 'matrix'.$i.'_static', '');
            $data['matrix'.$i.'_random'] = $random_enabled;

            $matrix_value = $this->getLatestRawByHostId($hostid, $data['matrix'.$i.'_key'], $allow_random);
            $value = $this->formatGeneric($matrix_value['text']);

            if ($value === '' && $random_enabled === '1') {
                $value = $this->getRandomMatrixText($data['matrix'.$i.'_label']);
            }

            $data['matrix'.$i.'_value'] = $value;
        }

        for ($i = 1; $i <= WidgetForm::MAX_SPARKS; $i++) {
            $hostid = $this->getField($fields, $inputs, 'spark'.$i.'_host', '0');
            $key = $this->getField($fields, $inputs, 'spark'.$i.'_key', '');
            $spark_value = $this->getLatestRawByHostId($hostid, $key, false);
            $spark_json = $this->parseSparkJson($spark_value['text'], (int) $this->getField($fields, $inputs, 'spark'.$i.'_max', '12'));

            $item1_label = $this->getField($fields, $inputs, 'spark'.$i.'_item1_label', '');
            $item1_key = $this->getField($fields, $inputs, 'spark'.$i.'_item1_key', '');
            $item2_label = $this->getField($fields, $inputs, 'spark'.$i.'_item2_label', '');
            $item2_key = $this->getField($fields, $inputs, 'spark'.$i.'_item2_key', '');

            $item1 = $this->getLatestRawByHostId($hostid, $item1_key, $allow_random);
            $item2 = $this->getLatestRawByHostId($hostid, $item2_key, $allow_random);

            $data['spark'.$i.'_label'] = $this->getField($fields, $inputs, 'spark'.$i.'_label', '');
            $data['spark'.$i.'_host'] = $this->hostIdToName($hostid);
            $data['spark'.$i.'_key'] = $key;
            $data['spark'.$i.'_group_mode'] = $this->getField($fields, $inputs, 'spark'.$i.'_group_mode', 'port');
            $data['spark'.$i.'_x'] = $this->getField($fields, $inputs, 'spark'.$i.'_x', '50');
            $data['spark'.$i.'_y'] = $this->getField($fields, $inputs, 'spark'.$i.'_y', '50');
            $data['spark'.$i.'_max'] = $this->getField($fields, $inputs, 'spark'.$i.'_max', '12');
            $data['spark'.$i.'_count'] = (string) $spark_json['count'];
            $data['spark'.$i.'_error'] = $spark_json['error'];
            $data['spark'.$i.'_items'] = $spark_json['items'];

            $data['spark'.$i.'_item1_label'] = $item1_label;
            $data['spark'.$i.'_item1_value'] = $this->formatGeneric($item1['text']);
            $data['spark'.$i.'_item2_label'] = $item2_label;
            $data['spark'.$i.'_item2_value'] = $this->formatGeneric($item2['text']);
        }

        $this->setResponse(new CControllerResponseData($data));
    }
}
