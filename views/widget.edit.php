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

for ($i = 1; $i <= WidgetForm::MAX_NODES; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Node '.$i.' panel'))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['node'.$i.'_type']))
            ->addField(new CWidgetFieldSelectView($data['fields']['node'.$i.'_theme']))
            ->addField(new CWidgetFieldSelectView($data['fields']['node'.$i.'_host']))
            ->addField(new CWidgetFieldIntegerBoxView($data['fields']['node'.$i.'_x']))
            ->addField(new CWidgetFieldIntegerBoxView($data['fields']['node'.$i.'_y']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_cpu_key']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_mem_key']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['node'.$i.'_disk_key']))
    );
}

for ($i = 1; $i <= WidgetForm::MAX_LINKS; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Link '.$i.' panel'))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_from']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_to']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_style']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_show_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_drilldown']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_in_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_in_key']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_out_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_out_key']))
            ->addField(new CWidgetFieldSelectView($data['fields']['link'.$i.'_health_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_loss_key']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_latency_key']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['link'.$i.'_errors_key']))
    );
}

for ($i = 1; $i <= WidgetForm::MAX_EXTRAS; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Extra '.$i.' panel'))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['extra'.$i.'_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['extra'.$i.'_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['extra'.$i.'_key']))
    );
}

for ($i = 1; $i <= WidgetForm::MAX_STATUS; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Status item '.$i.' panel'))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['status'.$i.'_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_key']))
            ->addField(new CWidgetFieldSelectView($data['fields']['status'.$i.'_mode']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_good']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_warn']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['status'.$i.'_crit']))
    );
}

for ($i = 1; $i <= WidgetForm::MAX_MATRIX_VALUES; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Matrix background value '.$i.' panel'))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_label']))
            ->addField(new CWidgetFieldSelectView($data['fields']['matrix'.$i.'_host']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_key']))
            ->addField(new CWidgetFieldTextBoxView($data['fields']['matrix'.$i.'_static']))
            ->addField(new CWidgetFieldSelectView($data['fields']['matrix'.$i.'_random']))
    );
}

for ($i = 1; $i <= WidgetForm::MAX_SPARKS; $i++) {
    $form->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView('Spark zone '.$i.' panel'))
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
            ->addField(new CWidgetFieldTextBoxView($data['fields']['spark'.$i.'_item2_key']))
    );
}

$form
    ->includeJsFile('widget.edit.js.php')
    ->addJavaScript('window.matrix_firewall_form.init();')
    ->show();
