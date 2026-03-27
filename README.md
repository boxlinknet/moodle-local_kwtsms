# kwtSMS for Moodle

[![Moodle Plugin](https://img.shields.io/badge/Moodle-4.3+-orange.svg)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://www.php.net)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![GitHub issues](https://img.shields.io/github/issues/boxlinknet/kwtsms-moodle)](https://github.com/boxlinknet/kwtsms-moodle/issues)
[![GitHub release](https://img.shields.io/github/v/release/boxlinknet/kwtsms-moodle?include_prereleases)](https://github.com/boxlinknet/kwtsms-moodle/releases)

Send SMS notifications to students and administrators via the kwtSMS gateway (kwtsms.com).

## Features

- **Event-driven SMS:** Automatic notifications for enrollment, unenrollment, grading, course completion, quiz submission, assignment submission, and new user registration
- **7-tab admin interface:** Dashboard, Settings, Gateway, Templates, Integrations, Logs, Help
- **Multilingual templates:** English + Arabic with placeholder support ({firstname}, {coursename}, {grade}, etc.)
- **Gateway management:** Login/logout, balance monitoring, sender ID selection, country code configuration
- **SMS logging:** Full log with status tracking, phone masking, filtering, pagination, and CSV export
- **Daily sync:** Automatic refresh of balance, sender IDs, and coverage data
- **GDPR compliant:** Full Privacy API implementation with data export and deletion
- **RTL support:** Right-to-left layout support for Arabic interfaces
- **Test mode:** Send to API without delivery for development and testing

## Requirements

- Moodle 4.3 or later
- PHP 8.1 or later
- A kwtSMS account (sign up at https://www.kwtsms.com)

## Installation

1. Download and extract to `local/kwtsms/` in your Moodle directory
2. Visit **Site administration > Notifications** to complete the installation
3. Go to **Site administration > Plugins > Local plugins > kwtSMS**
4. Open the **Gateway** tab and log in with your kwtSMS API credentials

## Configuration

1. **Gateway tab:** Enter your API username and password, click Login. Select your Sender ID and default country code.
2. **Settings tab:** Enable the gateway, configure test mode, set admin phone numbers, and set the low balance alert threshold.
3. **Integrations tab:** Enable the events you want to trigger SMS notifications.
4. **Templates tab:** Customize the English and Arabic SMS message templates for each event.

## Supported Events

| Event | Recipient | Description |
|-------|-----------|-------------|
| User Enrolled | Student | Sent when a user is enrolled in a course |
| User Unenrolled | Student | Sent when a user is removed from a course |
| Grade Posted | Student | Sent when a grade is published |
| Course Completed | Student | Sent when a user completes a course |
| Quiz Submitted | Student | Sent when a quiz attempt is submitted |
| Assignment Submitted | Student | Sent when an assignment file is uploaded |
| New User Registered | Admin | Sent to admin phones when a new user registers |

## Template Placeholders

| Placeholder | Available In | Description |
|-------------|-------------|-------------|
| `{firstname}` | All events | User's first name |
| `{lastname}` | All events | User's last name |
| `{coursename}` | Course events | Course full name |
| `{grade}` | Grade Posted | The grade value |
| `{activityname}` | Quiz/Assignment | Activity name |
| `{sitename}` | All events | Moodle site name |

## Sender ID

`KWT-SMS` is a shared testing sender ID. For production use, register a private sender ID through your kwtSMS account:

- **Transactional:** Required for OTP and alerts, bypasses DND filtering
- **Promotional:** For announcements and marketing, subject to DND filtering

## Support

- kwtSMS account support: https://www.kwtsms.com/support.html
- Plugin issues: https://github.com/boxlinknet/kwtsms-moodle/issues

## License

This plugin is licensed under the GNU GPL v3 or later.
See https://www.gnu.org/copyleft/gpl.html for details.
