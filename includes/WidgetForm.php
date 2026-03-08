<?php

namespace Modules\MatrixFirewall\Includes;

use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CWidgetFieldIntegerBox;
use Zabbix\Widgets\Fields\CWidgetFieldSelect;
use Zabbix\Widgets\Fields\CWidgetFieldTextBox;

class WidgetForm extends CWidgetForm {

    public const MAX_NODES = 20;
    public const MAX_LINKS = 30;
    public const MAX_EXTRAS = 10;
    public const MAX_STATUS = 10;

    public const LAYOUT_AUTO = 0;
    public const LAYOUT_MANUAL = 1;

    public const DEMO_OFF = 0;
    public const DEMO_RANDOM = 1;

    public const STATUS_MODE_RAW = 0;
    public const STATUS_MODE_GOODBAD = 1;
    public const STATUS_MODE_OKWARNCRIT = 2;

    private function getNumberOptions(int $min, int $max): array {
        $options = [];

        for ($i = $min; $i <= $max; $i++) {
            $options[$i] = (string) $i;
        }

        return $options;
    }

    private function getNodeOptions(): array {
        $options = [];

        for ($i = 1; $i <= self::MAX_NODES; $i++) {
            $options[$i] = 'Node '.$i;
        }

        return $options;
    }

    private function getHostOptions(): array {
        $options = [0 => '- select host -'];

        try {
            $hosts = \API::Host()->get([
                'output' => ['hostid', 'host'],
                'sortfield' => 'host'
            ]);

            foreach ($hosts as $host) {
                if (!empty($host['hostid']) && !empty($host['host'])) {
                    $options[(int) $host['hostid']] = $host['host'];
                }
            }
        }
        catch (\Throwable $e) {
        }

        return $options;
    }

    private function getLayoutOptions(): array {
        return [
            self::LAYOUT_AUTO => 'Auto layout',
            self::LAYOUT_MANUAL => 'Manual node X/Y'
        ];
    }

    private function getDemoOptions(): array {
        return [
            self::DEMO_OFF => 'Real values only',
            self::DEMO_RANDOM => 'Random fallback when missing'
        ];
    }

    private function getStatusModeOptions(): array {
        return [
            self::STATUS_MODE_RAW => 'Raw',
            self::STATUS_MODE_GOODBAD => 'Good / Bad',
            self::STATUS_MODE_OKWARNCRIT => 'OK / Warning / Critical'
        ];
    }

    public function addFields(): self {
        $host_options = $this->getHostOptions();
        $node_options = $this->getNodeOptions();
        $status_modes = $this->getStatusModeOptions();

        $this
            ->addField(
                (new CWidgetFieldSelect(
                    'layout_mode',
                    'Layout mode',
                    $this->getLayoutOptions()
                ))->setDefault(self::LAYOUT_AUTO)
            )
            ->addField(
                (new CWidgetFieldSelect(
                    'demo_mode',
                    'Fallback mode',
                    $this->getDemoOptions()
                ))->setDefault(self::DEMO_OFF)
            )
            ->addField(
                (new CWidgetFieldSelect(
                    'node_count',
                    'How many nodes to show',
                    $this->getNumberOptions(1, self::MAX_NODES)
                ))->setDefault(3)
            )
            ->addField(
                (new CWidgetFieldSelect(
                    'link_count',
                    'How many links to show',
                    $this->getNumberOptions(0, self::MAX_LINKS)
                ))->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldSelect(
                    'extra_count',
                    'Extra items to show',
                    $this->getNumberOptions(0, self::MAX_EXTRAS)
                ))->setDefault(0)
            )
            ->addField(
                (new CWidgetFieldSelect(
                    'status_count',
                    'Status items to show',
                    $this->getNumberOptions(0, self::MAX_STATUS)
                ))->setDefault(0)
            );

        for ($i = 1; $i <= self::MAX_NODES; $i++) {
            $this
                ->addField(
                    (new CWidgetFieldTextBox(
                        'node'.$i.'_label',
                        'Node '.$i.' label'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'node'.$i.'_host',
                        'Node '.$i.' host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldIntegerBox(
                        'node'.$i.'_x',
                        'Node '.$i.' X %'
                    ))->setDefault(10)
                )
                ->addField(
                    (new CWidgetFieldIntegerBox(
                        'node'.$i.'_y',
                        'Node '.$i.' Y %'
                    ))->setDefault(10)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'node'.$i.'_cpu_key',
                        'Node '.$i.' CPU item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'node'.$i.'_mem_key',
                        'Node '.$i.' Memory item key'
                    ))->setDefault('')
                );
        }

        for ($i = 1; $i <= self::MAX_LINKS; $i++) {
            $this
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_label',
                        'Link '.$i.' label'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'link'.$i.'_from',
                        'Link '.$i.' from node',
                        $node_options
                    ))->setDefault(1)
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'link'.$i.'_to',
                        'Link '.$i.' to node',
                        $node_options
                    ))->setDefault(2)
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'link'.$i.'_in_host',
                        'Link '.$i.' IN host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_in_key',
                        'Link '.$i.' IN item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'link'.$i.'_out_host',
                        'Link '.$i.' OUT host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_out_key',
                        'Link '.$i.' OUT item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'link'.$i.'_health_host',
                        'Link '.$i.' health host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_loss_key',
                        'Link '.$i.' loss item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_latency_key',
                        'Link '.$i.' latency item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'link'.$i.'_errors_key',
                        'Link '.$i.' errors item key'
                    ))->setDefault('')
                );
        }

        for ($i = 1; $i <= self::MAX_EXTRAS; $i++) {
            $this
                ->addField(
                    (new CWidgetFieldTextBox(
                        'extra'.$i.'_label',
                        'Extra '.$i.' label'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'extra'.$i.'_host',
                        'Extra '.$i.' host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'extra'.$i.'_key',
                        'Extra '.$i.' item key'
                    ))->setDefault('')
                );
        }

        for ($i = 1; $i <= self::MAX_STATUS; $i++) {
            $this
                ->addField(
                    (new CWidgetFieldTextBox(
                        'status'.$i.'_label',
                        'Status '.$i.' label'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'status'.$i.'_host',
                        'Status '.$i.' host',
                        $host_options
                    ))->setDefault(0)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'status'.$i.'_key',
                        'Status '.$i.' item key'
                    ))->setDefault('')
                )
                ->addField(
                    (new CWidgetFieldSelect(
                        'status'.$i.'_mode',
                        'Status '.$i.' mode',
                        $status_modes
                    ))->setDefault(self::STATUS_MODE_RAW)
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'status'.$i.'_good',
                        'Status '.$i.' good value'
                    ))->setDefault('1')
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'status'.$i.'_warn',
                        'Status '.$i.' warning value'
                    ))->setDefault('2')
                )
                ->addField(
                    (new CWidgetFieldTextBox(
                        'status'.$i.'_crit',
                        'Status '.$i.' critical value'
                    ))->setDefault('3')
                );
        }

        return $this;
    }
}
