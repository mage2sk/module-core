# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.1] — Marketplace Code Sniffer fixes

### Fixed
- `Model/UsageTracker.php` lines 174-175 — replaced `@curl_exec` and
  `@curl_close` (Generic.PHP.NoSilencedErrors.Discouraged severity 10)
  with `try/catch` blocks. The heartbeat is best-effort so silently
  ignoring failures is the right behaviour either way, and try/catch
  achieves the same outcome without violating the standard.
- `view/adminhtml/templates/system/config/child_theme_validation.phtml`
  line 71 — wrapped raw `<?= $icon ?>` output in
  `$block->escapeHtml($icon)` (Magento2.Security.XssTemplate.FoundUnescaped
  severity 10).
- Same template lines 54 + 58 — wrapped two more raw `Yes/No` literal
  outputs in `escapeHtml()` (preventive — Adobe would have flagged
  these on the next review round).

No functional changes — all three fixes are pure coding-standard
compliance. Behaviour is identical to 1.0.0.

---

## [1.0.0] — Initial release

### Added
- **Shared admin menu parent** — top-level "Panth Infotech" sidebar
  entry that every Panth extension hooks into. Without Core, every
  extension would create its own top-level menu — Core consolidates
  them so the admin sidebar stays clean.
- **Shared admin configuration** under
  `Stores → Configuration → Panth Extensions → Core Settings` so
  every Panth extension shares one consistent settings home.
- **`Panth\Core\Api\ThemeBuildExecutorInterface`** — single-method
  contract that decouples Core's admin "Rebuild Child Theme" button
  from `Panth_ThemeCustomizer`. Core ships a default no-op
  implementation that returns a friendly "module not installed"
  payload when ThemeCustomizer is missing, so Core can be installed
  standalone on any Magento store.
- **`Panth\Core\Model\NoopThemeBuildExecutor`** — default
  implementation of the interface. Returns `success: false` with
  a message asking the admin to install `Panth_ThemeCustomizer`.
- **DI preference** in `etc/di.xml` binding the interface to the
  no-op default. Sibling modules override this preference with
  their real implementation when installed.
- **Grid data-provider fix plugin** for the well-known Magento bug
  where `SearchResult` virtual types return null from
  `getCustomAttributes()` in developer mode and crash all admin
  grids.
- **`panth_modules.xml` registry** — declarative list of every
  Panth extension installed on the store, used by the admin "Panth
  Extensions" overview page.
- **Cron heartbeat** with admin opt-out, used to track installation
  health across the Panth extension suite.

### Compatibility
- Magento Open Source / Commerce / Cloud 2.4.4 → 2.4.8
- PHP 8.1, 8.2, 8.3, 8.4

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422
