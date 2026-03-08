# Matrix Firewall Widget for Zabbix 7.4

`Matrix Firewall` is a custom Zabbix dashboard widget that renders a Matrix-style network topology with animated links and optional telemetry overlays.

## Features

- Matrix-inspired background animation.
- Configurable topology with up to:
  - 20 nodes
  - 30 links
  - 10 extra metric labels
  - 10 status indicators
  - 20 matrix text/value rows
  - 6 spark widgets (JSON-driven mini visualizations)
- Auto or manual node layout.
- Link styles: elbow, straight, curved, explosive, dots, jumping, file transfer, zigzag.
- Link label themes: 20 compact presets (from simple dotted/minimal styles to richer HUD/neon styles), with automatic theme suggestion based on link item key names.
- Optional demo/random fallback mode when values are missing.
- Per-node and per-link item key mapping to pull real Zabbix values.
- Multiple node themes: box, glass, terminal, pill, neon, panel, outline, status panel, extra panel.
- Edit form panels split into per-node, per-link, per-extra, per-status, per-matrix, and per-spark sections for clearer configuration, with automatic hiding of unused panels based on selected counts.

## Requirements

- Zabbix 7.4
- Dashboard widget module support enabled

## Installation

1. Copy this repository into your Zabbix modules directory, for example:

   ```bash
   /usr/share/zabbix/modules/MatrixFirewall
   ```

2. Make sure file ownership/permissions match your Zabbix installation.
3. Reload Zabbix frontend (or restart related web/PHP services if needed).
4. In a dashboard, add the **Matrix Firewall** widget.

## Basic configuration flow

1. Set **How many nodes to show** and **How many links to show**.
2. Configure each node:
   - Label
   - Type
   - Host (optional)
   - CPU/memory item keys (optional)
3. Configure each link:
   - Label, source node, target node
   - IN/OUT item keys
   - Optional health/loss/latency/error keys
4. Optional: configure extras, status blocks, matrix rows, and spark widgets.

## Development notes

- Module metadata is defined in `manifest.json`.
- Widget form fields are defined in `includes/WidgetForm.php`.
- Rendering logic is in `views/widget.view.php`.
- Styling is in `assets/css/widget.css`.

## License

No license file is currently included in this repository.
