<?php

/**
 * @var CView $this
 * @var array $data
 */

use Modules\MatrixFirewall\Includes\WidgetForm;

$form = (new CWidgetFormView($data))
    ->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('General'))
            ->addField(new CWidgetFieldSelectView($data['fields']['layout_mode']))
            ->addField(new CWidgetFieldSelectView($data['fields']['demo_mode']))
            ->addField(new CWidgetFieldSelectView($data['fields']['matrix_speed']))
            ->addField(new CWidgetFieldSelectView($data['fields']['node_count']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link_count']))
            ->addField(new CWidgetFieldSelectView($data['fields']['extra_count']))
            ->addField(new CWidgetFieldSelectView($data['fields']['status_count']))
            ->addField(new CWidgetFieldSelectView($data['fields']['matrix_value_count']))
            ->addField(new CWidgetFieldSelectView($data['fields']['spark_count']))
    );

$nodes_fieldset = new CWidgetFormFieldsetCollapsibleView('Nodes');
for ($i = 1; $i <= WidgetForm::MAX_NODES; $i++) {
    $nodes_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['node'.$i.'_type']))
        ->addField(new CWidgetFieldSelectView($data['fields']['node'.$i.'_host']))
        ->addField(new CWidgetFieldIntegerBoxView($data['fields']['node'.$i.'_x']))
        ->addField(new CWidgetFieldIntegerBoxView($data['fields']['node'.$i.'_y']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_cpu_key']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_mem_key']));
}
$form->addFieldset($nodes_fieldset);

$links_fieldset = new CWidgetFormFieldsetCollapsibleView('Links');
for ($i = 1; $i <= WidgetForm::MAX_LINKS; $i++) {
    $links_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_from']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_to']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_style']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_show_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_in_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_in_key']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_out_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_out_key']))
        ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_health_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_loss_key']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_latency_key']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_errors_key']));
}
$form->addFieldset($links_fieldset);

$extras_fieldset = new CWidgetFormFieldsetCollapsibleView('Extras');
for ($i = 1; $i <= WidgetForm::MAX_EXTRAS; $i++) {
    $extras_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['extra'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['extra'.$i.'_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['extra'.$i.'_key']));
}
$form->addFieldset($extras_fieldset);

$status_fieldset = new CWidgetFormFieldsetCollapsibleView('Status items');
for ($i = 1; $i <= WidgetForm::MAX_STATUS; $i++) {
    $status_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['status'.$i.'_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_key']))
        ->addField(new CWidgetFieldSelectView($data['fields']['status'.$i.'_mode']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_good']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_warn']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_crit']));
}
$form->addFieldset($status_fieldset);

$matrix_fieldset = new CWidgetFormFieldsetCollapsibleView('Matrix background values');
for ($i = 1; $i <= WidgetForm::MAX_MATRIX_VALUES; $i++) {
    $matrix_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['matrix'.$i.'_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_key']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_static']))
        ->addField(new CWidgetFieldSelectView($data['fields']['matrix'.$i.'_random']));
}
$form->addFieldset($matrix_fieldset);

$spark_fieldset = new CWidgetFormFieldsetCollapsibleView('Spark zones');
for ($i = 1; $i <= WidgetForm::MAX_SPARKS; $i++) {
    $spark_fieldset
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_label']))
        ->addField(new CWidgetFieldSelectView($data['fields']['spark'.$i.'_host']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_key']))
        ->addField(new CWidgetFieldSelectView($data['fields']['spark'.$i.'_group_mode']))
        ->addField(new CWidgetFieldIntegerBoxView($data['fields']['spark'.$i.'_x']))
        ->addField(new CWidgetFieldIntegerBoxView($data['fields']['spark'.$i.'_y']))
        ->addField(new CWidgetFieldIntegerBoxView($data['fields']['spark'.$i.'_max']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_item1_label']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_item1_key']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_item2_label']))
        ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_item2_key']));
}

$form
    ->addFieldset($spark_fieldset)
    ->includeJsFile('widget.edit.js.php')
    ->addJavaScript('window.matrix_firewall_form.init();')
    ->show();
