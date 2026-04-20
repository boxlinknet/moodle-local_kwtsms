# Changelog

All notable changes to this project will be documented in this file.

## [1.0.2] - 2026-04-19

### Approved
- 2026-04-20: Plugin approved on the Moodle Plugins Directory and published at https://moodle.org/plugins/local_kwtsms (tracker CONTRIB-10429 closed as Done).

### Added
- GitHub Actions workflow (`.github/workflows/ci.yml`) running moodle-plugin-ci on every push/PR across PHP 8.1/8.2/8.3 × Moodle 4.3/4.4/4.5/5.0/5.1/5.2 × pgsql/mariadb (7 matrix combinations, all green)

### Changed
- `amd/build/*.min.js` regenerated via Moodle's canonical grunt rollup (previous terser output failed CI "stale" check)
- `amd/src/logs.js` and `amd/src/templates.js`: replaced `window.confirm()` with `Notification.saveCancel()` (eslint no-alert)
- `amd/src/logs.js`: added `eslint-disable-next-line camelcase` comments on `filter_*` property assignments (mirror PHP param names)
- `styles.css`: lowercase hex color `#ffa200` (stylelint color-hex-case)

### Addressed
- CONTRIB-10429 issue #1 (boxlinknet/moodle-local_kwtsms#1)

## [1.0.1] - 2026-04-19

### Changed
- Replaced legacy `ajax/*.php` endpoints with External Services (`classes/external/*` + `db/services.php`); JS modules now call via `core/ajax`
- Migrated 7 admin tabs to Mustache templates rendered via `$OUTPUT->render_from_template()` (Output API)
- Extracted all hard-coded user-facing strings to `lang/en/` and `lang/ar/` (added ~40 new keys for help tab and error messages)
- Fixed hard-coded "Tab not found." string in `view.php`
- Moved CSV log download from `ajax/logs_export.php` to `logs_export.php` at plugin root (file download, not AJAX)

### Addressed
- Moodle Plugins Directory review CONTRIB-10429 issues #3, #4, #5

## [1.0.0] - Unreleased

### Added
- Initial release
- SMS notifications for 7 Moodle events (enrollment, unenrollment, grade, completion, quiz, assignment, new user)
- 7-tab admin UI (Dashboard, Settings, Gateway, Templates, Integrations, Logs, Help)
- Multilingual SMS templates (English + Arabic)
- Gateway login/logout with balance, sender ID, and coverage management
- SMS logging with filtering, detail view, clear, and CSV export
- Daily scheduled sync of balance, sender IDs, and coverage
- Low balance admin alert
- GDPR Privacy API compliance
