# AI Coding Agent Instructions for `wordattach-for-cf7`

These guidelines make an AI agent immediately productive in this WordPress plugin codebase.

## Overview & Flow
- Purpose: Mail-merge Contact Form 7 (CF7) submission data into Word `.docx` templates and attach generated file to outgoing mail.
- Core flow: CF7 submission -> hook `wpcf7_before_send_mail` -> collect posted data -> normalize/transform (uppercase, date format, etc.) -> `PhpOffice\PhpWord\TemplateProcessor` fills placeholders -> file saved temporarily -> added as email attachment -> cleanup handled by CF7 temp upload lifecycle.
- Placeholders in Word: `${field-name}` (NOT `[...]` due to PhpWord macro bug).

## Key Files & Libraries
- Main plugin bootstrap: `wordattach-for-cf7.php` (hooks + autoload + processing logic).
- Third-party update lib: `plugin-update-checker/` (do not modify unless upgrading library intentionally).
- External dependency: `phpoffice/phpword` (loaded via `vendor/autoload.php`; version is `dev-master` — avoid changing unless necessary).

## Data Sources & Directives
CF7 "Additional Settings" drive behavior:
- `wt_template:` absolute path(s) to `.docx`; fallback naming: `template-form-{formId}.docx` in `wp-content/uploads/wpcf7-templates/`.
- `wt_filename:` pattern for output file; supports CF7 mail tags and special tags. Must be sanitized.
- Transform directives (possibly multiple lines): `wt_uppercase: [tag]`, `wt_lowercase: [tag]`, `wt_ucwords: [tag]`, `wt_ucfirst: [tag]`.
- Date formatting: `wt_format_date: [date-tag]|d/m/Y` (split on `|`).
- Special extra tag injected: `[_date]` mapped internally to current localized date.

## Conventions & Style
- Function prefix: `wacf7_` (keep for new helpers to avoid collisions).
- Procedural structure in single file; prefer adding new helpers at end of `wordattach-for-cf7.php` unless refactoring into includes (only if justified).
- Use WordPress APIs: `wp_upload_dir()`, `path_join()`, `sanitize_file_name()`, `wp_date()`, `wp_mkdir_p()`.
- Always check `file_exists()` before processing templates.
- Normalize array form inputs: implode multiple values with ", "; single-value arrays collapse to scalar.

## Placeholder Expansion
- TemplateProcessor only handles strings; pre-coerce non-scalar values.
- Add new transformation directive pattern by mirroring existing loops: read `$wpcf7->additional_setting('directive', 100)` then sanitize names stripping brackets.

## Safety & Validation
- Sanitize outgoing filenames (`sanitize_file_name`).
- Treat user-provided paths in `wt_template:` as trusted only if within uploads; if adding validations, ensure backward compatibility.
- Avoid writing persistent files outside CF7 temp or uploads subfolder.

## Temporary Storage
- Generated files saved in: `wacf7_get_merged_upload_dir()` => `wp-content/uploads/wpcf7_uploads/wacf7_merged/` (created dynamically). Do not assume persistence; design features accordingly.

## Extending Functionality (Patterns)
Example: Add a trim directive.
```php
$trim_directives = $wpcf7->additional_setting('wt_trim', 100);
foreach ($trim_directives as $key) {
  $key = trim(str_replace(["[","]"], '', $key));
  if (isset($data[$key])) { $data[$key] = trim($data[$key]); }
}
```

## Adding Multi-Template Support
- Accept multiple `wt_template:` lines; already loop uses `$template_paths` array — extend by allowing comma-separated values (split + trim).
- Ensure each processed template attaches resulting file via `$submission->add_extra_attachments()`.

## Testing & Debugging
- Manual test: create CF7 form, add directives in Additional Settings, submit form.
- Use `error_log()` sparingly (already examples present but commented). Remove or guard debug logging before release.
- To simulate transformations, temporarily dump `$data` after normalization.

## External Constraints
- PhpWord bug prevents switching to `[` `]` macro chars; maintain `${...}` syntax.
- `dev-master` dependency implies potential upstream changes; pinning a stable version could be future improvement (not auto-change).

## Performance Notes
- Each submission processes templates sequentially; keep transformations O(n) over fields.
- Avoid heavy I/O or large loops; TemplateProcessor is the main cost — only instantiate when file exists.

## Introducing New Features
- Follow pattern: collect directives -> sanitize -> transform `$data` -> set values -> save -> attach.
- Prefix new functions with `wacf7_` and keep pure (receive input, return output) when possible.

## Out of Scope
- Do not modify vendor/ or plugin-update-checker/ directly for feature work.
- No persistent storage / DB inserts unless explicitly requested.

## Quick Reference
- Hook to extend: `wpcf7_before_send_mail`.
- Get submission: `WPCF7_Submission::get_instance()`.
- Attach file: `$submission->add_extra_attachments($absolutePath)`.

Provide feedback if any section needs elaboration or if new directives should be documented.
