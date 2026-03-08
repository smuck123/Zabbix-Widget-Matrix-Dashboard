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
    public const MAX_MATRIX_VALUES = 20;
    public const MAX_SPARKS = 6;

    public const LAYOUT_AUTO = 0;
    public const LAYOUT_MANUAL = 1;

    public const DEMO_OFF = 0;
    public const DEMO_RANDOM = 1;

    public const STATUS_MODE_RAW = 0;
    public const STATUS_MODE_GOODBAD = 1;
    public const STATUS_MODE_OKWARNCRIT = 2;

    public const LINK_STYLE_ELBOW = 0;
    public const LINK_STYLE_STRAIGHT = 1;
    public const LINK_STYLE_CURVED = 2;
    public const LINK_STYLE_EXPLOSIVE = 3;
    public const LINK_STYLE_DOTS = 4;
    public const LINK_STYLE_JUMPING = 5;
    public const LINK_STYLE_FILE_TRANSFER = 6;
    public const LINK_STYLE_ZIGZAG = 7;

    public const LINK_DRILLDOWN_AUTO = 0;
    public const LINK_DRILLDOWN_GRAPH = 1;
    public const LINK_DRILLDOWN_LATEST = 2;
    public const LINK_DRILLDOWN_PROBLEMS = 3;

    public const NODE_TYPE_GENERIC = 0;
    public const NODE_TYPE_FIREWALL = 1;
    public const NODE_TYPE_CLOUD = 2;
    public const NODE_TYPE_SERVER = 3;
    public const NODE_TYPE_OFFICE = 4;
    public const NODE_TYPE_EXPRESSROUTE = 5;
    public const NODE_TYPE_INTERNET = 6;
    public const NODE_TYPE_DATACENTER = 7;
    public const NODE_TYPE_DATABASE = 8;

    public const NODE_THEME_BOX = 0;
    public const NODE_THEME_GLASS = 1;
    public const NODE_THEME_TERMINAL = 2;
    public const NODE_THEME_PILL = 3;
    public const NODE_THEME_NEON = 4;
    public const NODE_THEME_PANEL = 5;
    public const NODE_THEME_OUTLINE = 6;
    public const NODE_THEME_STATUS_PANEL = 7;
    public const NODE_THEME_EXTRA_PANEL = 8;

    public const MATRIX_SPEED_SLOW = 0;
    public const MATRIX_SPEED_NORMAL = 1;
    public const MATRIX_SPEED_FAST = 2;
    public const MATRIX_SPEED_VERY_FAST = 3;

    public const SPARK_GROUP_PORT = 0;
    public const SPARK_GROUP_PROCESS = 1;
    public const SPARK_GROUP_IP = 2;

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

    private function getLinkStyleOptions(): array {
        return [
            self::LINK_STYLE_ELBOW => 'Elbow',
            self::LINK_STYLE_STRAIGHT => 'Straight',
            self::LINK_STYLE_CURVED => 'Curved',
            self::LINK_STYLE_EXPLOSIVE => 'Explosive',
            self::LINK_STYLE_DOTS => 'Dots',
            self::LINK_STYLE_JUMPING => 'Jumping',
            self::LINK_STYLE_FILE_TRANSFER => 'File transfer',
            self::LINK_STYLE_ZIGZAG => 'Zigzag'
        ];
    }

    private function getYesNoOptions(): array {
        return [
            0 => 'No',
            1 => 'Yes'
        ];
    }

    private function getLinkDrilldownOptions(): array {
        return [
            self::LINK_DRILLDOWN_AUTO => 'Auto (Graph, fallback Latest values)',
            self::LINK_DRILLDOWN_GRAPH => 'Graph',
            self::LINK_DRILLDOWN_LATEST => 'Latest values',
            self::LINK_DRILLDOWN_PROBLEMS => 'Problems'
        ];
    }

    private function getNodeTypeOptions(): array {
        return [
            self::NODE_TYPE_GENERIC => 'Generic',
            self::NODE_TYPE_FIREWALL => 'Firewall',
            self::NODE_TYPE_CLOUD => 'Cloud',
            self::NODE_TYPE_SERVER => 'Server',
            self::NODE_TYPE_OFFICE => 'Office',
            self::NODE_TYPE_EXPRESSROUTE => 'ExpressRoute',
            self::NODE_TYPE_INTERNET => 'Internet',
            self::NODE_TYPE_DATACENTER => 'Datacenter',
            self::NODE_TYPE_DATABASE => 'Database'
        ];
    }

    private function getNodeThemeOptions(): array {
        return [
            self::NODE_THEME_BOX => 'Box',
            self::NODE_THEME_GLASS => 'Glass',
            self::NODE_THEME_TERMINAL => 'Terminal',
            self::NODE_THEME_PILL => 'Pill',
            self::NODE_THEME_NEON => 'Neon',
            self::NODE_THEME_PANEL => 'Panel',
            self::NODE_THEME_OUTLINE => 'Outline',
            self::NODE_THEME_STATUS_PANEL => 'Status panel',
            self::NODE_THEME_EXTRA_PANEL => 'Extra panel'
        ];
    }

    private function getMatrixSpeedOptions(): array {
        return [
            self::MATRIX_SPEED_SLOW => 'Slow',
            self::MATRIX_SPEED_NORMAL => 'Normal',
            self::MATRIX_SPEED_FAST => 'Fast',
            self::MATRIX_SPEED_VERY_FAST => 'Very fast'
        ];
    }

    private function getSparkGroupingOptions(): array {
        return [
            self::SPARK_GROUP_PORT => 'Group by port',
            self::SPARK_GROUP_PROCESS => 'Group by process',
            self::SPARK_GROUP_IP => 'Group by remote IP'
        ];
    }

    public function addFields(): self {
        $host_options = $this->getHostOptions();
        $node_options = $this->getNodeOptions();
        $status_modes = $this->getStatusModeOptions();
        $link_styles = $this->getLinkStyleOptions();
        $link_drilldowns = $this->getLinkDrilldownOptions();
        $yesno = $this->getYesNoOptions();
        $node_types = $this->getNodeTypeOptions();
        $node_themes = $this->getNodeThemeOptions();
        $matrix_speeds = $this->getMatrixSpeedOptions();
        $spark_grouping = $this->getSparkGroupingOptions();

        $this
            ->addField((new CWidgetFieldSelect('layout_mode', 'Layout mode', $this->getLayoutOptions()))->setDefault(self::LAYOUT_AUTO))
            ->addField((new CWidgetFieldSelect('demo_mode', 'Fallback mode', $this->getDemoOptions()))->setDefault(self::DEMO_OFF))
            ->addField((new CWidgetFieldSelect('matrix_speed', 'Matrix rain speed', $matrix_speeds))->setDefault(self::MATRIX_SPEED_NORMAL))
            ->addField((new CWidgetFieldSelect('node_count', 'How many nodes to show', $this->getNumberOptions(1, self::MAX_NODES)))->setDefault(3))
            ->addField((new CWidgetFieldSelect('link_count', 'How many links to show', $this->getNumberOptions(0, self::MAX_LINKS)))->setDefault(1))
            ->addField((new CWidgetFieldSelect('extra_count', 'Extra items to show', $this->getNumberOptions(0, self::MAX_EXTRAS)))->setDefault(0))
            ->addField((new CWidgetFieldSelect('status_count', 'Status items to show', $this->getNumberOptions(0, self::MAX_STATUS)))->setDefault(0))
            ->addField((new CWidgetFieldSelect('matrix_value_count', 'Matrix background values', $this->getNumberOptions(0, self::MAX_MATRIX_VALUES)))->setDefault(8))
            ->addField((new CWidgetFieldSelect('spark_count', 'Spark zones', $this->getNumberOptions(0, self::MAX_SPARKS)))->setDefault(0));

        for ($i = 1; $i <= self::MAX_NODES; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('node'.$i.'_label', 'Node '.$i.' label'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('node'.$i.'_type', 'Node '.$i.' type', $node_types))->setDefault(self::NODE_TYPE_GENERIC))
                ->addField((new CWidgetFieldSelect('node'.$i.'_theme', 'Node '.$i.' theme', $node_themes))->setDefault(self::NODE_THEME_BOX))
                ->addField((new CWidgetFieldSelect('node'.$i.'_host', 'Node '.$i.' host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldIntegerBox('node'.$i.'_x', 'Node '.$i.' X %'))->setDefault(10))
                ->addField((new CWidgetFieldIntegerBox('node'.$i.'_y', 'Node '.$i.' Y %'))->setDefault(10))
                ->addField((new CWidgetFieldTextBox('node'.$i.'_cpu_key', 'Node '.$i.' CPU item key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('node'.$i.'_mem_key', 'Node '.$i.' Memory item key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('node'.$i.'_disk_key', 'Node '.$i.' Disk item key'))->setDefault(''));
        }

        for ($i = 1; $i <= self::MAX_LINKS; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('link'.$i.'_label', 'Link '.$i.' label'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('link'.$i.'_from', 'Link '.$i.' from node', $node_options))->setDefault(1))
                ->addField((new CWidgetFieldSelect('link'.$i.'_to', 'Link '.$i.' to node', $node_options))->setDefault(2))
                ->addField((new CWidgetFieldSelect('link'.$i.'_style', 'Link '.$i.' route style', $link_styles))->setDefault(self::LINK_STYLE_ELBOW))
                ->addField((new CWidgetFieldSelect('link'.$i.'_show_label', 'Link '.$i.' show label', $yesno))->setDefault(1))
                ->addField((new CWidgetFieldSelect('link'.$i.'_drilldown', 'Link '.$i.' click target', $link_drilldowns))->setDefault(self::LINK_DRILLDOWN_AUTO))
                ->addField((new CWidgetFieldSelect('link'.$i.'_in_host', 'Link '.$i.' IN host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('link'.$i.'_in_key', 'Link '.$i.' IN item key'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('link'.$i.'_out_host', 'Link '.$i.' OUT host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('link'.$i.'_out_key', 'Link '.$i.' OUT item key'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('link'.$i.'_health_host', 'Link '.$i.' health host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('link'.$i.'_loss_key', 'Link '.$i.' loss item key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('link'.$i.'_latency_key', 'Link '.$i.' latency item key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('link'.$i.'_errors_key', 'Link '.$i.' errors item key'))->setDefault(''));
        }

        for ($i = 1; $i <= self::MAX_EXTRAS; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('extra'.$i.'_label', 'Extra '.$i.' label'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('extra'.$i.'_host', 'Extra '.$i.' host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('extra'.$i.'_key', 'Extra '.$i.' item key'))->setDefault(''));
        }

        for ($i = 1; $i <= self::MAX_STATUS; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('status'.$i.'_label', 'Status '.$i.' label'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('status'.$i.'_host', 'Status '.$i.' host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('status'.$i.'_key', 'Status '.$i.' item key'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('status'.$i.'_mode', 'Status '.$i.' mode', $status_modes))->setDefault(self::STATUS_MODE_RAW))
                ->addField((new CWidgetFieldTextBox('status'.$i.'_good', 'Status '.$i.' good value'))->setDefault('1'))
                ->addField((new CWidgetFieldTextBox('status'.$i.'_warn', 'Status '.$i.' warning value'))->setDefault('2'))
                ->addField((new CWidgetFieldTextBox('status'.$i.'_crit', 'Status '.$i.' critical value'))->setDefault('3'));
        }

        for ($i = 1; $i <= self::MAX_MATRIX_VALUES; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('matrix'.$i.'_label', 'Matrix '.$i.' prefix text'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('matrix'.$i.'_host', 'Matrix '.$i.' host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('matrix'.$i.'_key', 'Matrix '.$i.' item key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('matrix'.$i.'_static', 'Matrix '.$i.' static text'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('matrix'.$i.'_random', 'Matrix '.$i.' random text', $yesno))->setDefault(0));
        }

        for ($i = 1; $i <= self::MAX_SPARKS; $i++) {
            $this
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_label', 'Spark '.$i.' label'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('spark'.$i.'_host', 'Spark '.$i.' host', $host_options))->setDefault(0))
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_key', 'Spark '.$i.' JSON item key'))->setDefault(''))
                ->addField((new CWidgetFieldSelect('spark'.$i.'_group_mode', 'Spark '.$i.' grouping', $spark_grouping))->setDefault(self::SPARK_GROUP_PORT))
                ->addField((new CWidgetFieldIntegerBox('spark'.$i.'_x', 'Spark '.$i.' X %'))->setDefault(50))
                ->addField((new CWidgetFieldIntegerBox('spark'.$i.'_y', 'Spark '.$i.' Y %'))->setDefault(50))
                ->addField((new CWidgetFieldIntegerBox('spark'.$i.'_max', 'Spark '.$i.' max links'))->setDefault(12))
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_item1_label', 'Spark '.$i.' item 1 label'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_item1_key', 'Spark '.$i.' item 1 key'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_item2_label', 'Spark '.$i.' item 2 label'))->setDefault(''))
                ->addField((new CWidgetFieldTextBox('spark'.$i.'_item2_key', 'Spark '.$i.' item 2 key'))->setDefault(''));
        }

        return $this;
    }
}
