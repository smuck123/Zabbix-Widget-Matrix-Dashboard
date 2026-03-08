#!/bin/bash
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/includes/WidgetForm.php
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.php
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.js.php
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/actions/WidgetView.php
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.view.php
chown zabbix:apache /usr/share/zabbix/ui/modules/matrix_firewall/assets/css/widget.css

chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/includes/WidgetForm.php
chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.php
chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.js.php
chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/actions/WidgetView.php
chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.view.php
chmod 644 /usr/share/zabbix/ui/modules/matrix_firewall/assets/css/widget.css

restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/includes/WidgetForm.php
restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.php
restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.edit.js.php
restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/actions/WidgetView.php
restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/views/widget.view.php
restorecon -v /usr/share/zabbix/ui/modules/matrix_firewall/assets/css/widget.css

systemctl restart php-fpm
