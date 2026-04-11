# Panth Core

[![Magento 2.4.4 - 2.4.8](https://img.shields.io/badge/Magento-2.4.4%20--%202.4.8-orange)]()
[![PHP 8.1 - 8.4](https://img.shields.io/badge/PHP-8.1%20--%208.4-blue)]()
[![Free](https://img.shields.io/badge/License-Free-brightgreen)]()

**Required base module** for the Panth Infotech extension suite on the
Magento Marketplace. Provides shared utilities, admin configuration
helpers, the optional `ThemeBuildExecutorInterface` contract, and a
small registry of Panth modules. Free to install — no purchase
required.

This module is **a dependency**, not a standalone product. You install
it because another Panth extension requires it. Composer handles this
automatically when you install any Panth extension.

---

## ✨ What it provides

- **Shared admin config sections** under `Stores → Configuration →
  Panth Extensions → Core Settings` so every Panth extension shares
  one consistent settings home in the admin.
- **Admin menu parent** (`Panth Infotech` sidebar entry) that every
  Panth extension hooks into. Without Core, every extension would
  create its own top-level menu — Core consolidates them.
- **Shared cron heartbeat + usage tracker** (with admin opt-out).
- **Grid data-provider fix** for the well-known Magento bug where
  SearchResult virtual types return null from `getCustomAttributes()`
  in developer mode and crash all admin grids.
- **`Panth\Core\Api\ThemeBuildExecutorInterface`** — optional contract
  used by the admin "Rebuild Child Theme" button. The default
  implementation returns a friendly "module not installed" payload;
  if `Panth_ThemeCustomizer` is also installed, the override fires
  and the button does the real build.
- **`panth_modules.xml`** registry — declarative list of every Panth
  extension installed on the store, used by the admin "Panth
  Extensions" overview page.

---

## 📦 Installation

Customers usually do not install Panth_Core directly — Composer pulls
it in automatically as a dependency of any other Panth extension. If
you want to install it explicitly:

```bash
composer require mage2kishan/module-core
bin/magento module:enable Panth_Core
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Verify

```bash
bin/magento module:status Panth_Core
# Module is enabled
```

---

## 🛠 Requirements

| | Required |
|---|---|
| Magento | 2.4.4 — 2.4.8 (Open Source / Commerce / Cloud) |
| PHP | 8.1 / 8.2 / 8.3 / 8.4 |

---

## 🔧 Configuration

Open **Stores → Configuration → Panth Extensions → Core Settings**.

| Setting | Default | What it does |
|---|---|---|
| Enable Cron Heartbeat | Yes | Hourly cron job that pings a heartbeat endpoint with anonymized usage stats. Disable to opt out. |
| Display Mode | Auto | Auto-detect Hyva or Luma theme; can be forced manually. |

The module exposes very few settings of its own — most of its value
comes from the shared infrastructure other Panth extensions consume.

---

## 🆘 Support

| Channel | Contact |
|---|---|
| Email | kishansavaliyakb@gmail.com |
| Website | https://kishansavaliya.com |
| WhatsApp | +91 84012 70422 |

Free email support is provided on a best-effort basis. Priority
support is available to customers holding a paid license for any
Panth Infotech extension that depends on Panth_Core.

---

## 📄 License

Free — see `LICENSE.txt`. Distribution is restricted to the Adobe
Commerce Marketplace.

---

## 🏢 About the developer

Built and maintained by **Kishan Savaliya** — https://kishansavaliya.com.
Builds high-quality, security-focused Magento 2 extensions and themes
for both Hyva and Luma storefronts.
