<?php
?>
window.matrix_firewall_form = new class {
    init() {
        const maxNodes = 20;
        const maxLinks = 30;
        const maxExtras = 10;
        const maxStatus = 10;
        const maxMatrix = 20;
        const maxSparks = 6;

        const findField = (name) => {
            return document.querySelector('[name="' + name + '"]')
                || document.querySelector('[name="fields[' + name + ']"]')
                || document.querySelector('[name$="[' + name + ']"]');
        };

        const getFieldRow = (el) => {
            if (!el) return null;

            let cur = el;
            while (cur) {
                if (cur.matches?.('li, tr, .form-field, .fields-group, .widget-field, .dashboard-widget-field')) {
                    return cur;
                }
                cur = cur.parentElement;
            }

            return el.parentElement;
        };

        const showField = (name, visible) => {
            const el = findField(name);
            const row = getFieldRow(el);

            if (row) {
                row.style.display = visible ? '' : 'none';
            }
        };


        const panelCache = {};

        const getPanelByKey = (cacheKey, fieldName) => {
            if (panelCache[cacheKey]) {
                return panelCache[cacheKey];
            }

            const el = findField(fieldName);
            if (!el) {
                return null;
            }

            const panel = el.closest('fieldset, .form-fieldset, .dashboard-widget-fieldset, .widget-fieldset, .list-accordion-item, .collapsible');
            if (panel) {
                panelCache[cacheKey] = panel;
            }

            return panel;
        };

        const showPanel = (cacheKey, fieldName, visible) => {
            const panel = getPanelByKey(cacheKey, fieldName);
            if (panel) {
                panel.style.display = visible ? '' : 'none';
            }
        };

        const getIntValue = (name, fallback = 0) => {
            const el = findField(name);
            if (!el) return fallback;

            const v = parseInt(el.value || fallback, 10);
            return Number.isNaN(v) ? fallback : v;
        };

        const refresh = () => {
            const layoutMode = getIntValue('layout_mode', 0);
            const nodeCount = getIntValue('node_count', 1);
            const linkCount = getIntValue('link_count', 0);
            const extraCount = getIntValue('extra_count', 0);
            const statusCount = getIntValue('status_count', 0);
            const matrixCount = getIntValue('matrix_value_count', 0);
            const sparkCount = getIntValue('spark_count', 0);

            for (let i = 1; i <= maxNodes; i++) {
                const visible = i <= nodeCount;
                showPanel('node-' + i, 'node' + i + '_label', visible);
                showField('node' + i + '_label', visible);
                showField('node' + i + '_type', visible);
                showField('node' + i + '_theme', visible);
                showField('node' + i + '_host', visible);
                showField('node' + i + '_x', visible && layoutMode === 1);
                showField('node' + i + '_y', visible && layoutMode === 1);
                showField('node' + i + '_cpu_key', visible);
                showField('node' + i + '_mem_key', visible);
                showField('node' + i + '_disk_key', visible);
            }

            for (let i = 1; i <= maxLinks; i++) {
                const visible = i <= linkCount;
                showPanel('link-' + i, 'link' + i + '_label', visible);
                showField('link' + i + '_label', visible);
                showField('link' + i + '_from', visible);
                showField('link' + i + '_to', visible);
                showField('link' + i + '_style', visible);
                showField('link' + i + '_show_label', visible);
                showField('link' + i + '_drilldown', visible);
                showField('link' + i + '_in_host', visible);
                showField('link' + i + '_in_key', visible);
                showField('link' + i + '_out_host', visible);
                showField('link' + i + '_out_key', visible);
                showField('link' + i + '_health_host', visible);
                showField('link' + i + '_loss_key', visible);
                showField('link' + i + '_latency_key', visible);
                showField('link' + i + '_errors_key', visible);
            }

            for (let i = 1; i <= maxExtras; i++) {
                const visible = i <= extraCount;
                showPanel('extra-' + i, 'extra' + i + '_label', visible);
                showField('extra' + i + '_label', visible);
                showField('extra' + i + '_host', visible);
                showField('extra' + i + '_key', visible);
            }

            for (let i = 1; i <= maxStatus; i++) {
                const visible = i <= statusCount;
                showPanel('status-' + i, 'status' + i + '_label', visible);
                showField('status' + i + '_label', visible);
                showField('status' + i + '_host', visible);
                showField('status' + i + '_key', visible);
                showField('status' + i + '_mode', visible);
                showField('status' + i + '_good', visible);
                showField('status' + i + '_warn', visible);
                showField('status' + i + '_crit', visible);
            }

            for (let i = 1; i <= maxMatrix; i++) {
                const visible = i <= matrixCount;
                showPanel('matrix-' + i, 'matrix' + i + '_label', visible);
                showField('matrix' + i + '_label', visible);
                showField('matrix' + i + '_host', visible);
                showField('matrix' + i + '_key', visible);
                showField('matrix' + i + '_static', visible);
                showField('matrix' + i + '_random', visible);
            }

            for (let i = 1; i <= maxSparks; i++) {
                const visible = i <= sparkCount;
                showPanel('spark-' + i, 'spark' + i + '_label', visible);
                showField('spark' + i + '_label', visible);
                showField('spark' + i + '_host', visible);
                showField('spark' + i + '_key', visible);
                showField('spark' + i + '_group_mode', visible);
                showField('spark' + i + '_x', visible);
                showField('spark' + i + '_y', visible);
                showField('spark' + i + '_max', visible);
                showField('spark' + i + '_item1_label', visible);
                showField('spark' + i + '_item1_key', visible);
                showField('spark' + i + '_item2_label', visible);
                showField('spark' + i + '_item2_key', visible);
            }
        };

        document.addEventListener('change', (e) => {
            const name = e.target?.getAttribute('name') || '';

            if (
                name.includes('layout_mode')
                || name.includes('node_count')
                || name.includes('link_count')
                || name.includes('extra_count')
                || name.includes('status_count')
                || name.includes('matrix_value_count')
                || name.includes('spark_count')
            ) {
                refresh();
            }
        });

        setTimeout(refresh, 0);
        setTimeout(refresh, 150);
        setTimeout(refresh, 400);
        setTimeout(refresh, 800);
    }
};
