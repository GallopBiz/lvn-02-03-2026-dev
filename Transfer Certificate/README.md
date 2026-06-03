# Transfer Certificate Module

This folder documents the T.C. module architecture. Runtime PHP classes remain in Laravel's PSR-4 paths so they autoload correctly.

## Structure

- Controllers: `app/Http/Controllers/backend/TransferCertificateController.php`
- Models: `app/Models/TransferCertificate.php` and `app/Models/TransferCertificate/*`
- Services: `app/Services/TransferCertificate/TransferCertificateService.php`
- Routes: `routes/web.php`, prefix `transfer-certificate`
- Views: `resources/views/backend/TransferCertificate/*`
- PDF templates: `resources/views/backend/TransferCertificate/pdf/*`
- Reports: `resources/views/backend/TransferCertificate/tc-index.blade.php`

## Database Tables

- `tc_certificates`: permanent certificate master record and student snapshot.
- `tc_certificate_issues`: every original or duplicate generation event.
- `tc_student_session_statuses`: transfer/inactive session marker.
- `tc_templates`: future PDF template registry.
- `tc_audit_logs`: audit trail for issue, duplicate, update, and cancellation actions.

`student_registration` also receives status helper columns so active listings can exclude transferred students while historical module data remains untouched.
