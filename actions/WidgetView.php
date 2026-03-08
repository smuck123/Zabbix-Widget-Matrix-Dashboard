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

        if ($num >= 1000000000) {
            return round($num / 1000000000, 2).' Gbps';
        }

        if ($num >= 1000000) {
            return round($num / 1000000, 1).' Mbps';
        }

        if ($num >= 1000) {
            return round($num / 1000, 1).' Kbps';
        }

        return round($num, 0).' bps';
    }

    private function formatGeneric(string $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (is_numeric($value)) {
            $num = (float) $value;

            if (abs($num) >= 1000000000) {
                return round($num / 1000000000, 2).'G';
            }
            if (abs($num) >= 1000000) {
                return round($num / 1000000, 2).'M';
            }
            if (abs($num) >= 1000) {
                return round($num / 1000, 2).'K';
            }

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

    private function getLatestRawByHostId(string $hostid, string $key): array {
        $result = [
            'raw' => 0.0,
            'text' => ''
        ];

        if ($hostid === '' || $hostid === '0' || $key === '') {
            return $result;
        }

        $items = \API::Item()->get([
            'output' => ['itemid', 'key_', 'lastvalue'],
            'hostids' => [(int) $hostid],
            'filter' => ['key_' => [$key]]
        ]);

        if (!empty($items[0]['lastvalue'])) {
            $result['raw'] = (float) $items[0]['lastvalue'];
            $result['text'] = (string) $items[0]['lastvalue'];
            return $result;
        }

        $items = \API::Item()->get([
            'output' => ['itemid', 'key_', 'lastvalue'],
            'hostids' => [(int) $hostid],
            'search' => ['key_' => $key]
        ]);

        if (!empty($items[0]['lastvalue'])) {
            $result['raw'] = (float) $items[0]['lastvalue'];
            $result['text'] = (string) $items[0]['lastvalue'];
            return $result;
        }

        return $result;
    }

    private function getStatusText(string $raw, int $mode, string $good, string $warn, string $crit): array {
        $raw = trim($raw);

        if ($raw === '') {
            return ['text' => 'No value', 'class' => 'neutral'];
        }

        if ($mode === WidgetForm::STATUS_MODE_GOODBAD) {
            if ($raw === $good) {
                return ['text' => 'Good', 'class' => 'good'];
            }

            return ['text' => 'Bad', 'class' => 'bad'];
        }

        if ($mode === WidgetForm::STATUS_MODE_OKWARNCRIT) {
            if ($raw === $good) {
                return ['text' => 'OK', 'class' => 'good'];
            }

            if ($raw === $warn) {
                return ['text' => 'Warning', 'class' => 'warn'];
            }

            if ($raw === $crit) {
                return ['text' => 'Critical', 'class' => 'bad'];
            }

            return ['text' => $raw, 'class' => 'neutral'];
        }

        return ['text' => $raw, 'class' => 'neutral'];
    }

    protected function doAction(): void {
        $fields = $this->getInput('fields', []);
        $inputs = $this->getInputAll();

        $data = [
            'name' => $this->getInput('name', 'Matrix Firewall'),
            'node_count' => $this->getField($fields, $inputs, 'node_count', '5'),
            'link_count' => $this->getField($fields, $inputs, 'link_count', '3'),
            'extra_count' => $this->getField($fields, $inputs, 'extra_count', '0'),
            'status_count' => $this->getField($fields, $inputs, 'status_count', '0')
        ];

        for ($i = 1; $i <= WidgetForm::MAX_NODES; $i++) {
            $node_hostid = $this->getField($fields, $inputs, 'node'.$i.'_host', '0');

            $data['node'.$i.'_label'] = $this->getField($fields, $inputs, 'node'.$i.'_label', '');
            $data['node'.$i.'_hostid'] = $node_hostid;
            $data['node'.$i.'_host'] = $this->hostIdToName($node_hostid);

            $data['node'.$i.'_cpu_key'] = $this->getField($fields, $inputs, 'node'.$i.'_cpu_key', '');
            $data['node'.$i.'_mem_key'] = $this->getField($fields, $inputs, 'node'.$i.'_mem_key', '');

            $cpu = $this->getLatestRawByHostId($node_hostid, $data['node'.$i.'_cpu_key']);
            $mem = $this->getLatestRawByHostId($node_hostid, $data['node'.$i.'_mem_key']);

            $data['node'.$i.'_cpu_value'] = $this->formatGeneric($cpu['text']);
            $data['node'.$i.'_mem_value'] = $this->formatGeneric($mem['text']);
        }

        for ($i = 1; $i <= WidgetForm::MAX_LINKS; $i++) {
            $in_hostid = $this->getField($fields, $inputs, 'link'.$i.'_in_host', '0');
            $out_hostid = $this->getField($fields, $inputs, 'link'.$i.'_out_host', '0');
            $health_hostid = $this->getField($fields, $inputs, 'link'.$i.'_health_host', '0');

            $data['link'.$i.'_label'] = $this->getField($fields, $inputs, 'link'.$i.'_label', '');
            $data['link'.$i.'_from'] = $this->getField($fields, $inputs, 'link'.$i.'_from', '1');
            $data['link'.$i.'_to'] = $this->getField($fields, $inputs, 'link'.$i.'_to', '2');

            $data['link'.$i.'_in_host'] = $this->hostIdToName($in_hostid);
            $data['link'.$i.'_out_host'] = $this->hostIdToName($out_hostid);
            $data['link'.$i.'_health_host'] = $this->hostIdToName($health_hostid);

            $data['link'.$i.'_in_key'] = $this->getField($fields, $inputs, 'link'.$i.'_in_key', '');
            $data['link'.$i.'_out_key'] = $this->getField($fields, $inputs, 'link'.$i.'_out_key', '');
            $data['link'.$i.'_loss_key'] = $this->getField($fields, $inputs, 'link'.$i.'_loss_key', '');
            $data['link'.$i.'_latency_key'] = $this->getField($fields, $inputs, 'link'.$i.'_latency_key', '');
            $data['link'.$i.'_errors_key'] = $this->getField($fields, $inputs, 'link'.$i.'_errors_key', '');

            $in_value = $this->getLatestRawByHostId($in_hostid, $data['link'.$i.'_in_key']);
            $out_value = $this->getLatestRawByHostId($out_hostid, $data['link'.$i.'_out_key']);
            $loss_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_loss_key']);
            $latency_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_latency_key']);
            $errors_value = $this->getLatestRawByHostId($health_hostid, $data['link'.$i.'_errors_key']);

            $data['link'.$i.'_in_value'] = $this->formatTraffic($in_value['text']);
            $data['link'.$i.'_out_value'] = $this->formatTraffic($out_value['text']);
            $data['link'.$i.'_in_raw'] = $in_value['raw'];
            $data['link'.$i.'_out_raw'] = $out_value['raw'];

            $data['link'.$i.'_loss_value'] = $this->formatGeneric($loss_value['text']);
            $data['link'.$i.'_latency_value'] = $this->formatGeneric($latency_value['text']);
            $data['link'.$i.'_errors_value'] = $this->formatGeneric($errors_value['text']);
        }

        for ($i = 1; $i <= WidgetForm::MAX_EXTRAS; $i++) {
            $hostid = $this->getField($fields, $inputs, 'extra'.$i.'_host', '0');

            $data['extra'.$i.'_label'] = $this->getField($fields, $inputs, 'extra'.$i.'_label', '');
            $data['extra'.$i.'_host'] = $this->hostIdToName($hostid);
            $data['extra'.$i.'_key'] = $this->getField($fields, $inputs, 'extra'.$i.'_key', '');

            $extra_value = $this->getLatestRawByHostId($hostid, $data['extra'.$i.'_key']);
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

            $status_value = $this->getLatestRawByHostId($hostid, $data['status'.$i.'_key']);
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

        $this->setResponse(new CControllerResponseData($data));
    }
}
