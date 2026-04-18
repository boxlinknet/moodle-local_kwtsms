# Changelog

All notable changes to this project will be documented in this file.

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
