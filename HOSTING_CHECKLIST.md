# Hosting Checklist (Transportrix)

## 1) Files
- Upload all project files to your domain root (`public_html` or equivalent).
- Ensure filename case is preserved (Linux hosting is case-sensitive).

## 2) Database
- Create a MySQL database and user in hosting panel.
- Import SQL files:
  - `DATABASE/transportrix (1).sql`
  - `DATABASE/invoice_tables.sql`
  - `DATABASE/price_list.sql` (optional; auto-creates in app too)

## 3) App config
- Edit `connect.php`:
  - `$host`
  - `$user`
  - `$pass`
  - `$db`

## 4) HTTPS + hardening
- Keep `.htaccess` in root (already added).
- Enable SSL certificate in hosting panel.

## 5) Permissions
- Files: `644`
- Directories: `755`

## 6) Smoke tests
- Login works
- Dashboard loads
- Add/edit customer, driver, truck, admin
- Create invoice + print invoice
- Price lookup auto-fill works in invoice form

## 7) Production note
- `display_errors` is disabled in key CRUD pages.
- Check hosting PHP error log for troubleshooting.
