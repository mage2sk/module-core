<!-- SEO Meta -->
<!--
  Title: Panth Core - Free Required Base Module for Magento 2 | Panth Infotech
  Description: Panth Core is the free, required base module for the Panth Infotech Magento 2 extension suite. Provides shared utilities, admin foundation, Hyva/Luma theme detection, and module registry. Compatible with Magento 2.4.4 - 2.4.8 and PHP 8.1 - 8.4. Built by Top Rated Plus Magento developer Kishan Savaliya.
  Keywords: magento 2 base module, magento 2 free module, panth core, panth infotech, hire magento developer, top rated plus upwork, magento extensions, magento 2.4.8, hyva theme module, magento 2 admin foundation
  Author: Kishan Savaliya (Panth Infotech)
  Canonical: https://github.com/mage2sk/module-core
-->

# Panth Core — Free Required Base Module for Magento 2 | Panth Infotech Extensions

[![Magento 2.4.4 - 2.4.8](https://img.shields.io/badge/Magento-2.4.4%20--%202.4.8-orange?logo=magento&logoColor=white)](https://magento.com)
[![PHP 8.1 - 8.4](https://img.shields.io/badge/PHP-8.1%20--%208.4-blue?logo=php&logoColor=white)](https://php.net)
[![Free](https://img.shields.io/badge/License-Free-brightgreen)]()
[![Packagist](https://img.shields.io/badge/Packagist-mage2kishan%2Fmodule--core-orange?logo=packagist&logoColor=white)](https://packagist.org/packages/mage2kishan/module-core)
[![Upwork Top Rated Plus](https://img.shields.io/badge/Upwork-Top%20Rated%20Plus-14a800?logo=upwork&logoColor=white)](https://www.upwork.com/freelancers/~016dd1767321100e21)
[![Panth Infotech Agency](https://img.shields.io/badge/Agency-Panth%20Infotech-14a800?logo=upwork&logoColor=white)](https://www.upwork.com/agencies/1881421506131960778/)
[![Website](https://img.shields.io/badge/Website-kishansavaliya.com-0D9488)](https://kishansavaliya.com)
[![Get a Quote](https://img.shields.io/badge/Get%20a%20Quote-Free%20Estimate-DC2626)](https://kishansavaliya.com/get-quote)

> **Free, mandatory dependency** for the entire Panth Infotech extension suite on the Adobe Commerce Marketplace. Provides shared utilities, admin configuration foundation, Hyva and Luma theme detection, module registry, and the optional `ThemeBuildExecutorInterface` contract used by 33+ premium Panth extensions for Magento 2.

**Panth Core** is the foundational library module that every Panth extension depends on. It consolidates shared functionality — admin menu structure, configuration UI sections, theme detection helpers, and grid data-provider fixes — into a single lightweight package. Without Core, each Panth extension would create its own admin menu and duplicate utilities. With Core installed once, all 34 Panth extensions integrate seamlessly into a unified `Panth Infotech` admin section.

This module is **not a standalone product** — you install it because another Panth extension requires it. Composer handles this automatically when you install any module from the Panth Infotech suite. It is **completely free** and remains free forever.

---

## 🚀 Need Custom Magento 2 Development?

> **Get a free quote for your project in 24 hours** — custom modules, Hyva themes, performance optimization, M1→M2 migrations, and Adobe Commerce Cloud.

<p align="center">
  <a href="https://kishansavaliya.com/get-quote">
    <img src="https://img.shields.io/badge/Get%20a%20Free%20Quote%20%E2%86%92-Reply%20within%2024%20hours-DC2626?style=for-the-badge" alt="Get a Free Quote" />
  </a>
</p>

<table>
<tr>
<td width="50%" align="center">

### 🏆 Kishan Savaliya
**Top Rated Plus on Upwork**

[![Hire on Upwork](https://img.shields.io/badge/Hire%20on%20Upwork-Top%20Rated%20Plus-14a800?style=for-the-badge&logo=upwork&logoColor=white)](https://www.upwork.com/freelancers/~016dd1767321100e21)

100% Job Success • 10+ Years Magento Experience
Adobe Certified • Hyva Specialist

</td>
<td width="50%" align="center">

### 🏢 Panth Infotech Agency
**Magento Development Team**

[![Visit Agency](https://img.shields.io/badge/Visit%20Agency-Panth%20Infotech-14a800?style=for-the-badge&logo=upwork&logoColor=white)](https://www.upwork.com/agencies/1881421506131960778/)

Custom Modules • Theme Design • Migrations
Performance • SEO • Adobe Commerce Cloud

</td>
</tr>
</table>

**Visit our website:** [kishansavaliya.com](https://kishansavaliya.com) &nbsp;|&nbsp; **Get a quote:** [kishansavaliya.com/get-quote](https://kishansavaliya.com/get-quote)

---

## Table of Contents

- [Why Panth Core Exists](#why-panth-core-exists)
- [Key Features](#key-features)
- [Compatibility](#compatibility)
- [Installation](#installation)
- [Configuration](#configuration)
- [What's Inside](#whats-inside)
- [API Reference](#api-reference)
- [Theme Detection](#theme-detection)
- [Module Registry](#module-registry)
- [Cron Heartbeat](#cron-heartbeat)
- [Grid Bug Fix](#grid-bug-fix)
- [Compatibility with Other Panth Extensions](#compatibility-with-other-panth-extensions)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Support](#support)

---

## Why Panth Core Exists

When you install multiple extensions from the same vendor on Magento 2, you typically face three problems:

1. **Cluttered admin sidebar** — every extension creates its own top-level menu entry
2. **Duplicate utilities** — each extension reimplements theme detection, config helpers, and shared services
3. **Inconsistent UX** — each extension follows its own conventions for admin configuration

**Panth Core solves all three** by providing a single shared foundation that every Panth extension hooks into:

- One unified **`Panth Infotech`** admin sidebar entry
- One consistent **`Stores → Configuration → Panth Extensions`** settings home
- One shared **`Panth\Core\Helper\Theme`** for Hyva/Luma detection
- One shared **`panth_modules.xml`** registry for the extensions overview page

This means whether you install 1 or 34 Panth extensions, the admin experience stays clean, fast, and consistent.

---

## Key Features

### Shared Admin Foundation

- **Unified admin menu** — all Panth extensions appear under a single `Panth Infotech` sidebar entry
- **Centralized configuration** — every Panth extension's settings live under `Stores → Configuration → Panth Extensions`
- **Core Settings section** — global toggles for debug mode, caching, and module status

### Developer Utilities

- **`Panth\Core\Helper\Theme`** — detect whether the current store is running Hyva or Luma (used by 30+ Panth extensions to switch templates)
- **`Panth\Core\ViewModel\ThemeConfig`** — inject theme tokens into frontend templates
- **`Panth\Core\Api\ThemeBuildExecutorInterface`** — optional contract for triggering theme builds from admin

### Quality of Life

- **Grid data-provider fix** — patches the well-known Magento bug where `SearchResult` virtual types return null from `getCustomAttributes()` in developer mode
- **Cron heartbeat** — lightweight cron job that records last-run timestamps for diagnostics (with admin opt-out)
- **Module registry** — declarative `panth_modules.xml` lists every installed Panth extension for the overview page

### Security & Performance

- **MEQP compliant** — passes Adobe's Magento Extension Quality Program with zero severity-10 violations
- **Zero dependencies on third-party libraries** — uses only Magento framework classes
- **Lightweight** — under 50 PHP files, minimal memory footprint
- **Composer-installable** — no manual file copying required

---

## Compatibility

| Requirement | Versions Supported |
|---|---|
| Magento Open Source | 2.4.4, 2.4.5, 2.4.6, 2.4.7, 2.4.8 |
| Adobe Commerce | 2.4.4, 2.4.5, 2.4.6, 2.4.7, 2.4.8 |
| Adobe Commerce Cloud | 2.4.4 — 2.4.8 |
| PHP | 8.1.x, 8.2.x, 8.3.x, 8.4.x |
| MySQL | 8.0+ |
| MariaDB | 10.4+ |
| Hyva Theme | 1.0+ (optional) |
| Luma Theme | Native support |

Tested on:
- Magento 2.4.8-p4 with PHP 8.4
- Magento 2.4.7 with PHP 8.3
- Magento 2.4.6 with PHP 8.2

---

## Installation

### Composer Installation (Recommended)

```bash
composer require mage2kishan/module-core
bin/magento module:enable Panth_Core
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual Installation via ZIP

1. Download the latest release ZIP from [Packagist](https://packagist.org/packages/mage2kishan/module-core) or the [Adobe Commerce Marketplace](https://commercemarketplace.adobe.com)
2. Extract the contents to `app/code/Panth/Core/` in your Magento installation
3. Run the same commands as above starting from `bin/magento module:enable Panth_Core`

### Verify Installation

```bash
bin/magento module:status Panth_Core
# Expected output: Module is enabled
```

After installation, navigate to:
```
Admin → Stores → Configuration → Panth Extensions → Core Settings
```

---

## Configuration

Panth Core ships with sensible defaults and requires no configuration to function. The following optional settings are available at `Stores → Configuration → Panth Extensions → Core Settings`:

| Setting | Default | Description |
|---|---|---|
| Enable Panth Core | Yes | Master toggle for all Panth Core functionality. Disable to bypass all Panth Core hooks. |
| Debug Mode | No | Enable verbose logging for Panth Core operations. Useful for development and troubleshooting. Should be disabled in production. |
| Enable Caching | Yes | Enable internal caching for theme detection and module registry lookups. Recommended on. |
| Cron Heartbeat | Yes | Enable the lightweight cron heartbeat for diagnostics. Disable if you do not want any cron tracking. |

---

## What's Inside

```
Panth_Core/
├── Api/
│   ├── ThemeBuildExecutorInterface.php   # Optional contract for theme builds
│   └── Data/
├── Block/
│   └── Adminhtml/                         # Admin block helpers
├── Controller/
│   └── Adminhtml/                         # Admin controllers
├── Cron/
│   └── Heartbeat.php                      # Lightweight diagnostics cron
├── Helper/
│   ├── Data.php                           # General config helper
│   └── Theme.php                          # Hyva/Luma theme detection
├── Model/
│   ├── Config/                            # Configuration sources and backends
│   └── ModuleRegistry.php                 # Reads panth_modules.xml
├── Observer/                              # Event observers
├── Plugin/
│   └── GridDataProvider.php               # Magento grid bug fix
├── Service/                               # Reusable services
├── Setup/
│   └── Patch/Data/                        # Data patches
├── ViewModel/
│   └── ThemeConfig.php                    # Frontend theme config injector
├── etc/
│   ├── adminhtml/
│   │   ├── menu.xml                       # "Panth Infotech" sidebar entry
│   │   ├── routes.xml
│   │   └── system.xml                     # Core Settings config UI
│   ├── crontab.xml
│   ├── di.xml
│   ├── module.xml
│   └── panth_modules.xml                  # Module registry
└── view/
    ├── adminhtml/
    └── frontend/
```

---

## API Reference

### `Panth\Core\Helper\Theme`

Detect the current frontend theme.

```php
use Panth\Core\Helper\Theme;

class MyBlock extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Context $context,
        private readonly Theme $themeHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isHyvaActive(): bool
    {
        return $this->themeHelper->isHyva();
    }

    public function isLumaActive(): bool
    {
        return $this->themeHelper->isLuma();
    }

    public function getCurrentThemeType(): string
    {
        // Returns 'hyva' or 'luma'
        return $this->themeHelper->getCurrentTheme();
    }
}
```

### `Panth\Core\ViewModel\ThemeConfig`

Inject theme tokens into frontend templates via layout XML.

```xml
<block class="Magento\Framework\View\Element\Template"
       name="my.block"
       template="MyVendor_MyModule::template.phtml">
    <arguments>
        <argument name="view_model" xsi:type="object">
            Panth\Core\ViewModel\ThemeConfig
        </argument>
    </arguments>
</block>
```

```php
// In template.phtml
$viewModel = $block->getViewModel();
$primary = $viewModel->getColor('primary');
$radius = $viewModel->getRadius('default');
```

### `Panth\Core\Api\ThemeBuildExecutorInterface`

Optional contract for triggering theme rebuilds from admin. Default implementation returns a friendly "module not installed" payload. If `Panth_ThemeCustomizer` is installed, it overrides this with the real build executor.

```php
use Panth\Core\Api\ThemeBuildExecutorInterface;

class MyController
{
    public function __construct(
        private readonly ThemeBuildExecutorInterface $themeBuilder
    ) {}

    public function execute()
    {
        $result = $this->themeBuilder->build();
        // $result['success'], $result['message'], $result['output']
    }
}
```

---

## Theme Detection

Panth Core's theme detection logic checks multiple signals to reliably identify whether the storefront is running Hyva or Luma:

1. **Module check** — is `Hyva_Theme` enabled?
2. **Theme path check** — does the active theme's path explicitly contain a Luma marker?
3. **Parent theme check** — does the theme's parent chain include `Hyva/default`?

This multi-signal approach correctly handles edge cases like:
- Hyva child themes
- Luma child themes
- Mixed installations where Hyva module exists but a Luma theme is active
- Custom themes with non-standard parent chains

The result is cached per-request via `$cachedThemeType` to avoid repeated database lookups.

---

## Module Registry

The `etc/panth_modules.xml` file declares every Panth extension installed on the store. Each Panth extension contributes its own entry through DI configuration:

```xml
<config>
    <module name="Panth_AdvancedSEO" version="1.0.0">
        <label>Advanced SEO</label>
        <description>Enterprise-grade SEO suite</description>
        <admin_url>panth_seo/dashboard/index</admin_url>
    </module>
</config>
```

The registry is consumed by the `Panth Infotech` admin overview page, which displays installed extensions, their versions, and links to their config sections. This gives merchants a single dashboard to see and manage all Panth extensions.

---

## Cron Heartbeat

Panth Core registers a lightweight cron job (`panth_core_heartbeat`) that runs every hour and records the last execution timestamp. This is useful for:

- **Diagnostics** — confirming cron is running
- **Health checks** — external monitoring tools can read the timestamp
- **Telemetry opt-in** — anonymous module-version tracking (off by default, requires explicit admin opt-in)

To disable the heartbeat entirely, set **Cron Heartbeat** to **No** in Core Settings.

---

## Grid Bug Fix

A well-known Magento bug causes admin grids to crash in **developer mode** when virtual types extending `SearchResult` return `null` from `getCustomAttributes()`. Panth Core ships a plugin (`Panth\Core\Plugin\GridDataProvider`) that patches this by returning an empty array instead of null.

This fix is applied silently and only affects grids that would otherwise throw errors. It does not modify any product data or alter grid behaviour in production mode.

---

## Compatibility with Other Panth Extensions

Panth Core is the **required dependency** for all Panth Infotech extensions. Composer handles this automatically — when you install any Panth extension, Core is pulled in.

### All Panth Extensions That Require Core

| Extension | Composer Package |
|---|---|
| Advanced SEO | `mage2kishan/module-advanced-seo` |
| Advanced Contact Us | `mage2kishan/module-advanced-contact-us` |
| Advanced Cart | `mage2kishan/module-advancedcart` |
| Banner Slider | `mage2kishan/module-banner-slider` |
| Cache Manager | `mage2kishan/module-cachemanager` |
| Checkout Extended | `mage2kishan/module-checkout-extended` |
| Checkout Success Page | `mage2kishan/module-checkout-success` |
| Core Web Vitals | `mage2kishan/module-corewebvitals` |
| Custom Options | `mage2kishan/module-custom-options` |
| Dynamic Forms | `mage2kishan/module-dynamic-forms` |
| FAQ | `mage2kishan/module-faq` |
| Footer | `mage2kishan/module-footer` |
| Image Optimizer | `mage2kishan/module-imageoptimizer` |
| Live Activity | `mage2kishan/module-live-activity` |
| Low Stock Notification | `mage2kishan/module-low-stock-notification` |
| Malware Scanner | `mage2kishan/module-malware-scanner` |
| Mega Menu | `mage2kishan/module-mega-menu` |
| Not Found Page (404) | `mage2kishan/module-not-found-page` |
| Order Attachments | `mage2kishan/module-order-attachments` |
| PageBuilder AI | `mage2kishan/module-pagebuilder-ai` |
| Performance Optimizer | `mage2kishan/module-performance-optimizer` |
| Price Drop Alert | `mage2kishan/module-price-drop-alert` |
| Product Attachments | `mage2kishan/module-product-attachments` |
| Product Gallery | `mage2kishan/module-productgallery` |
| Product Slider | `mage2kishan/module-product-slider` |
| Product Tabs | `mage2kishan/module-producttabs` |
| Quick View | `mage2kishan/module-quickview` |
| Search Autocomplete | `mage2kishan/module-search-autocomplete` |
| Smart Badge | `mage2kishan/module-smart-badge` |
| Testimonials | `mage2kishan/module-testimonials` |
| Theme Customizer | `mage2kishan/module-theme-customizer` |
| WhatsApp Integration | `mage2kishan/module-whatsapp` |
| Zipcode Validation | `mage2kishan/module-zipcode-validation` |
| Panth Infotech Theme | `mage2kishan/theme-frontend-panth-infotech` |

---

## Troubleshooting

| Issue | Cause | Resolution |
|---|---|---|
| `Class Panth\Core\Helper\Theme not found` | Module not enabled or DI compile not run | Run `bin/magento module:enable Panth_Core && bin/magento setup:di:compile` |
| Admin sidebar missing `Panth Infotech` entry | Cache not flushed | Run `bin/magento cache:flush` and refresh admin |
| Core Settings section missing | ACL not refreshed | Log out and back in to admin |
| Grid bug fix not applying | Conflict with another grid plugin | Check `app/code` for custom plugins on `Magento\Framework\Api\SearchResults` |
| Cron heartbeat not running | Magento cron not configured | Verify `bin/magento cron:run` is scheduled in your system crontab |

For other issues, enable **Debug Mode** in Core Settings and check `var/log/panth_core.log` for detailed output.

---

## FAQ

### Do I have to pay for Panth Core?

No. Panth Core is **completely free** and will remain free forever. It is the foundation library that other (paid) Panth extensions depend on.

### Can I use Panth Core without any other Panth extensions?

Technically yes, but there is no practical reason to. Core only provides admin scaffolding and shared utilities — it has no standalone features.

### Will Panth Core slow down my store?

No. Core is a thin library with no frontend output and minimal admin overhead. Theme detection and module registry lookups are cached.

### Does Panth Core conflict with any other extensions?

No. Core only adds hooks under its own namespace (`Panth\Core`) and a single admin sidebar entry. It does not modify any Magento core files or override any third-party extension functionality.

### Can I uninstall Panth Core?

Only if you uninstall every other Panth extension first. Composer will block the removal otherwise. To uninstall:

```bash
composer remove mage2kishan/module-core
bin/magento setup:upgrade
```

### Is the source code available?

Yes. The full source is on GitHub at [github.com/mage2sk/module-core](https://github.com/mage2sk/module-core).

### Does Panth Core work with multi-store setups?

Yes. All settings respect Magento's standard scope hierarchy (default → website → store view).

### Does Panth Core support multi-language stores?

Yes. The module includes English translations and the codebase uses Magento's standard `__()` translation function for all user-facing strings, so it can be translated to any language.

---

## Support

| Channel | Contact |
|---|---|
| Email | kishansavaliyakb@gmail.com |
| Website | [kishansavaliya.com](https://kishansavaliya.com) |
| WhatsApp | +91 84012 70422 |
| GitHub Issues | [github.com/mage2sk/module-core/issues](https://github.com/mage2sk/module-core/issues) |
| Upwork (Top Rated Plus) | [Hire Kishan Savaliya](https://www.upwork.com/freelancers/~016dd1767321100e21) |
| Upwork Agency | [Panth Infotech](https://www.upwork.com/agencies/1881421506131960778/) |

Response time: 1-2 business days. Free email support is provided on a best-effort basis for the Core module.

### 💼 Need Custom Magento Development?

Looking for **custom Magento module development**, **Hyva theme customization**, **store migrations**, or **performance optimization**? Get a free quote in 24 hours:

<p align="center">
  <a href="https://kishansavaliya.com/get-quote">
    <img src="https://img.shields.io/badge/%F0%9F%92%AC%20Get%20a%20Free%20Quote-kishansavaliya.com%2Fget--quote-DC2626?style=for-the-badge" alt="Get a Free Quote" />
  </a>
</p>

<p align="center">
  <a href="https://www.upwork.com/freelancers/~016dd1767321100e21">
    <img src="https://img.shields.io/badge/Hire%20Kishan-Top%20Rated%20Plus-14a800?style=for-the-badge&logo=upwork&logoColor=white" alt="Hire on Upwork" />
  </a>
  &nbsp;&nbsp;
  <a href="https://www.upwork.com/agencies/1881421506131960778/">
    <img src="https://img.shields.io/badge/Visit-Panth%20Infotech%20Agency-14a800?style=for-the-badge&logo=upwork&logoColor=white" alt="Visit Agency" />
  </a>
  &nbsp;&nbsp;
  <a href="https://kishansavaliya.com">
    <img src="https://img.shields.io/badge/Visit%20Website-kishansavaliya.com-0D9488?style=for-the-badge" alt="Visit Website" />
  </a>
</p>

**Specializations:**

- 🛒 **Magento 2 Module Development** — custom extensions following MEQP standards
- 🎨 **Hyva Theme Development** — Alpine.js + Tailwind CSS, lightning-fast storefronts
- 🖌️ **Luma Theme Customization** — pixel-perfect designs, responsive layouts
- ⚡ **Performance Optimization** — Core Web Vitals, page speed, caching strategies
- 🔍 **Magento SEO** — structured data, hreflang, sitemaps, AI-generated meta
- 🛍️ **Checkout Optimization** — one-page checkout, conversion rate optimization
- 🚀 **M1 to M2 Migrations** — data migration, custom feature porting
- ☁️ **Adobe Commerce Cloud** — deployment, CI/CD, performance tuning
- 🤖 **AI-Powered eCommerce** — OpenAI/Claude integration for content, search, recommendations
- 🔌 **Third-party Integrations** — payment gateways, ERP, CRM, marketing tools

**Industries served:** Fashion & Apparel, Electronics, Health & Beauty, Food & Beverage, Home & Garden, B2B Wholesale, Multi-vendor Marketplaces.

---

## License

Panth Core is **free** under a proprietary license — see `LICENSE.txt`. You may install and use it on any number of Magento installations as the required dependency for other Panth extensions.

---

## About Panth Infotech

Built and maintained by **Kishan Savaliya** — [kishansavaliya.com](https://kishansavaliya.com) — a **Top Rated Plus** Magento developer on Upwork with 10+ years of eCommerce experience.

**Panth Infotech** is a Magento 2 development agency specializing in high-quality, security-focused extensions and themes for both Hyva and Luma storefronts. Our extension suite covers SEO, performance, checkout, product presentation, customer engagement, and store management — over 34 modules built to MEQP standards and tested across Magento 2.4.4 to 2.4.8.

Browse the full extension catalog on the [Adobe Commerce Marketplace](https://commercemarketplace.adobe.com) or [Packagist](https://packagist.org/packages/mage2kishan/).

### Quick Links

- 🌐 **Website:** [kishansavaliya.com](https://kishansavaliya.com)
- 💬 **Get a Quote:** [kishansavaliya.com/get-quote](https://kishansavaliya.com/get-quote)
- 👨‍💻 **Upwork Profile (Top Rated Plus):** [upwork.com/freelancers/~016dd1767321100e21](https://www.upwork.com/freelancers/~016dd1767321100e21)
- 🏢 **Upwork Agency:** [upwork.com/agencies/1881421506131960778](https://www.upwork.com/agencies/1881421506131960778/)
- 📦 **Packagist:** [packagist.org/packages/mage2kishan](https://packagist.org/packages/mage2kishan/)
- 🐙 **GitHub:** [github.com/mage2sk](https://github.com/mage2sk)
- 🛒 **Adobe Marketplace:** [commercemarketplace.adobe.com](https://commercemarketplace.adobe.com)
- 📧 **Email:** kishansavaliyakb@gmail.com
- 📱 **WhatsApp:** +91 84012 70422

---

<p align="center">
  <strong>Ready to upgrade your Magento 2 store?</strong><br/>
  <a href="https://kishansavaliya.com/get-quote">
    <img src="https://img.shields.io/badge/%F0%9F%9A%80%20Get%20Started%20%E2%86%92-Free%20Quote%20in%2024h-DC2626?style=for-the-badge" alt="Get Started" />
  </a>
</p>

---

**SEO Keywords:** magento 2 base module, panth core, magento 2 dependency, panth infotech foundation, magento 2 free module, magento 2 admin foundation, magento 2 hyva luma detection, magento 2 module registry, magento 2 grid bug fix, panth shared library, magento 2.4 base library, magento 2 extension dependency, magento 2 development agency, hire magento developer upwork, top rated plus magento freelancer, kishan savaliya magento, panth infotech magento, magento 2.4.8 module, php 8.4 magento module, hyva theme module, luma theme detection, magento 2 admin menu consolidation, magento 2 admin configuration helpers, magento 2 grid SearchResult fix, mage2kishan, mage2sk, magento marketplace developer, custom magento development india, magento 2 hyva development, magento 2 luma customization, magento 2 performance optimization, magento 2 SEO services, M1 to M2 migration, adobe commerce cloud expert, magento 2 checkout optimization, magento 2 conversion rate optimization, AI ecommerce magento
