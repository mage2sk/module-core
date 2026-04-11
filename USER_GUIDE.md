# Panth Core — User Guide

Panth_Core is the base / shared dependency for every Panth Infotech
Magento 2 extension. It is **free to install** and is automatically
pulled in via composer when you install any other Panth extension.

This guide is for store administrators who want to verify that
Panth_Core is installed correctly, and explore the shared features
it provides.

---

## Table of contents

1. [Installation](#1-installation)
2. [Verifying the module is active](#2-verifying-the-module-is-active)
3. [Configuration screens](#3-configuration-screens)
4. [The Panth Infotech admin menu](#4-the-panth-infotech-admin-menu)
5. [The ThemeBuildExecutor interface](#5-the-themebuildexecutor-interface)
6. [Troubleshooting](#6-troubleshooting)
7. [CLI reference](#7-cli-reference)

---

## 1. Installation

### Composer (recommended)

```bash
composer require mage2kishan/module-core
bin/magento module:enable Panth_Core
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual zip

1. Download the extension package zip
2. Extract to `app/code/Panth/Core`
3. Run the same `module:enable … cache:flush` commands above

> Customers usually do not install Panth_Core directly — Composer
> pulls it in automatically as a dependency of any other Panth
> extension you install.

---

## 2. Verifying the module is active

```bash
bin/magento module:status Panth_Core
# Module is enabled
```

You should also see a new top-level **Panth Infotech** menu entry in
the admin sidebar.

---

## 3. Configuration screens

Navigate to **Stores → Configuration → Panth Extensions → Core
Settings**.

| Setting | Default | What it does |
|---|---|---|
| **Enable Cron Heartbeat** | Yes | Hourly cron job that pings a heartbeat endpoint with anonymized installation stats (Magento version, PHP version, list of Panth modules installed). Disable to opt out. |
| **Display Mode** | Auto | Auto-detect whether the storefront uses Hyva or Luma. Can be forced manually if auto-detection fails. |

---

## 4. The Panth Infotech admin menu

Panth_Core creates a top-level **Panth Infotech** entry in the admin
sidebar. Every other Panth extension you install adds its own group
under this single parent — so the admin never gets cluttered with one
top-level menu per extension.

Default children include:
- **Core Settings** — opens the Core configuration page
- **Documentation** — opens the in-admin documentation page (added
  by each extension)

---

## 5. The ThemeBuildExecutor interface

`Panth_Core` ships an optional contract:
**`Panth\Core\Api\ThemeBuildExecutorInterface`**

This is a single-method interface used by Core's admin "Rebuild Child
Theme" button. By default it is bound to a no-op implementation that
returns a friendly "Panth_ThemeCustomizer is not installed" message.

If you also install **Panth_ThemeCustomizer**, that module overrides
the binding with its real `BuildExecutor`, and the button starts
working.

This is the canonical Magento "interface in base module + preference
override in feature module" pattern. It means Panth_Core can be
installed standalone on any Magento store without requiring
ThemeCustomizer.

---

## 6. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `Panth Infotech` menu not visible | Module not enabled, or admin user lacks permission | `bin/magento module:status Panth_Core`; check admin user role permissions |
| Admin grid crashes with "foreach() argument must be of type array" | Magento bug with virtual types in dev mode — Core ships a plugin that fixes this | If you still see it, run `bin/magento cache:flush` and retry |
| "Rebuild Child Theme" button returns "module not installed" | `Panth_ThemeCustomizer` is not installed | Install Panth_ThemeCustomizer from the Marketplace |

---

## 7. CLI reference

```bash
# Verify module status
bin/magento module:status Panth_Core

# Enable / disable
bin/magento module:enable  Panth_Core
bin/magento module:disable Panth_Core
```

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422

Free email support is provided on a best-effort basis.
