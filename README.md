# kwtSMS for Moodle

Send SMS notifications to students and administrators via the kwtSMS gateway (kwtsms.com).

## Features

- SMS notifications for enrollment, grades, course completion, and more
- 7-tab admin interface (Dashboard, Settings, Gateway, Templates, Integrations, Logs, Help)
- Multilingual SMS templates (English + Arabic) with placeholder support
- Gateway login/logout with balance and sender ID management
- Comprehensive SMS logging with filtering and CSV export
- Daily automatic sync of balance, sender IDs, and coverage
- GDPR/Privacy API compliant

## Requirements

- Moodle 4.4 or later
- PHP 8.1 or later
- A kwtSMS account (sign up at https://www.kwtsms.com)

## Installation

1. Download and extract to `local/kwtsms/` in your Moodle directory
2. Visit Site administration > Notifications to complete the installation
3. Go to Site administration > Plugins > Local plugins > kwtSMS
4. Open the Gateway tab and log in with your kwtSMS API credentials

## Configuration

1. **Gateway tab:** Enter your API username and password, click Login
2. **Settings tab:** Enable the gateway, configure test mode and admin phones
3. **Integrations tab:** Enable the events you want to trigger SMS notifications
4. **Templates tab:** Customize the SMS message templates

## Support

- kwtSMS support: https://www.kwtsms.com/support.html
- Plugin issues: Use the GitHub Issues tracker

## License

This plugin is licensed under the GNU GPL v3 or later.
See https://www.gnu.org/copyleft/gpl.html for details.
