<?php
?>
window.matrix_firewall_form = new class {
    init() {
        const maxNodes = 20;
        const maxLinks = 30;
        const maxExtras = 10;
        const maxStatus = 10;

        const findField = (name) => {
            return document.querySelector('[name="' + name + '"]')
                || document.querySelector('[name="fields[' + name + ']"]')
                || document.querySelector('[name$="[' + name + ']"]');
        };

        const getFieldRow = (el) => {
            if (!el) {
                return null;
            }

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

        const getIntValue = (name, fallback = 0) => {
            const el = findField(name);

            if (!el) {
                return fallback;
            }

            const v = parseInt(el.value || fallback, 10);
            return Number.isNaN(v) ? fallback : v;
        };

        const refresh = () => {
            const layoutMode = getIntValue('layout_mode', 0);
            const nodeCount = getIntValue('node_count', 1);
            const linkCount = getIntValue('link_count', 0);
            const extraCount = getIntValue('extra_count', 0);
            const statusCount = getIntValue('status_count', 0);

            for (let i = 1; i <= maxNodes; i++) {
                const visible = i <= nodeCount;

                showField('node' + i + '_label', visible);
                showField('node' + i + '_host', visible);
                showField('node' + i + '_x', visible && layoutMode === 1);
                showField('node' + i + '_y', visible && layoutMode === 1);
                showField('node' + i + '_cpu_key', visible);
                showField('node' + i + '_mem_key', visible);
            }

            for (let i = 1; i <= maxLinks; i++) {
                const visible = i <= linkCount;

                showField('link' + i + '_label', visible);
                showField('link' + i + '_from', visible);
                showField('link' + i + '_to', visible);
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

                showField('extra' + i + '_label', visible);
                showField('extra' + i + '_host', visible);
                showField('extra' + i + '_key', visible);
            }

            for (let i = 1; i <= maxStatus; i++) {
                const visible = i <= statusCount;

                showField('status' + i + '_label', visible);
                showField('status' + i + '_host', visible);
                showField('status' + i + '_key', visible);
                showField('status' + i + '_mode', visible);
                showField('status' + i + '_good', visible);
                showField('status' + i + '_warn', visible);
                showField('status' + i + '_crit', visible);
            }
        };

        document.addEventListener('change', (e) => {
            const name = e.target?.getAttribute('name') || '';

            if (
                name === 'layout_mode'
                || name === 'fields[layout_mode]'
                || name.endsWith('[layout_mode]')
                || name === 'node_count'
                || name === 'fields[node_count]'
                || name.endsWith('[node_count]')
                || name === 'link_count'
                || name === 'fields[link_count]'
                || name.endsWith('[link_count]')
                || name === 'extra_count'
                || name === 'fields[extra_count]'
                || name.endsWith('[extra_count]')
                || name === 'status_count'
                || name === 'fields[status_count]'
                || name.endsWith('[status_count]')
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
