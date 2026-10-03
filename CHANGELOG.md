# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.2.5] - 2026-10-03

### Fixed
- Core Settings: the Child Theme Validation panel now spans the full section width like the setup guide, so check messages no longer wrap into a narrow column at 1024px, and each message sits beside its label.
- Child Theme Validation reports "Child Theme: No" when the active theme is Panth/Infotech itself, matching the checks below it.
- Rebuild Theme CSS explains that Panth_ThemeCustomizer is not installed or is disabled.
- Hero icons are marked aria-hidden and not focusable, so screen readers skip decorative SVGs.
