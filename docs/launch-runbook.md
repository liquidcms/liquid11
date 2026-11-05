# liquidcms.ca D11 launch runbook

Peter's plan, with the checks that matter for this site.

## Before launch (local)
1. **Content freeze and final sync.** Anything edited on live D7 since the local copy (e.g. today's timezone change) is not in D11. Pull a fresh live DB into liquid7, then re-run the incremental migrations for content only (`migrate:import <id>` for new rows; `--update --idlist=nid:vid:und` for edited ones). Afterwards check aliases and redirects: pathauto and redirect can rewrite them on `--update`.
2. **Remove the migration tooling** once the sync is final:
   - uninstall migrate_upgrade, migrate_tools, migrate_plus, migrate_drupal and migrate (this drops the migrate_map tables);
   - delete the `$databases['migrate']` block and the `.ddev/docker-compose.d7source.yaml` mount;
   - `drush cex`.
3. **Export the DB:** `ddev export-db --file=liquid11.sql.gz` (after `drush cr`). ddev runs MySQL 8. If the server is MariaDB, replace the `utf8mb4_0900_ai_ci` collation with `utf8mb4_general_ci` in the dump, or the import fails with "Unknown collation".

## Server requirements (check first)
- PHP **8.3+** with gd, pdo_mysql, xml, mbstring, opcache. The live D7 server is probably on an older PHP.
- MySQL 8.0+ or MariaDB 10.6+.
- Apache mod_rewrite (the stock D11 `web/.htaccess` is used; the http→https redirect lives in the vhost, not D7's `.htaccess`).

## Upload
- Upload the **whole project**, not just `web/`: `composer.json/lock`, `vendor/`, `config/`, `web/`, `scripts/` (optional).
- **Docroot must point to `web/`.** `web/index.php` loads `../vendor/autoload.php`. Change the vhost DocumentRoot to `<project>/web` (or upload the project one level up and symlink the old docroot to `web`).
- `vendor/` is committed locally, so no `composer install` is needed, as long as the server PHP matches (8.3+).
- Files: upload `web/sites/default/files` but skip `css/`, `js/`, `styles/` (regenerated) and `php/`.

## Database
- Prefer importing into a **new** database (e.g. `liquidcms_d11`) rather than wiping the D7 one. Rollback is then just pointing back to the old docroot/DB.

## settings.php on the server
The local file relies on DDEV for some values. On the server set:
- `$databases['default']['default']` = the new DB credentials.
- `$settings['hash_salt']`: **must be set** (it is empty locally because DDEV injects it). Use a long random string.
- `$settings['trusted_host_patterns'] = ['^liquidcms\.ca$', '^www\.liquidcms\.ca$'];`
- `$settings['config_sync_directory'] = '../config/sync';` (already there)
- The DDEV-only blocks (migrate DB, twig php_storage) are guarded by `IS_DDEV_PROJECT` and do nothing on the server.
- Permissions: `sites/default/files` writable by the web server; `settings.php` read-only.

## After launch
- Rebuild caches: `drush cr` if drush is available, otherwise enable `$settings['rebuild_access'] = TRUE;` once and visit `/core/rebuild.php`, then remove it.
- Check `/admin/reports/status` for errors (cron, file permissions, trusted hosts).
- Smoke test: home (slider, Cool Projects, testimonials, clients, gallery), /about, /portfolio (filter, lightbox), /blog, a post, /contact (send a test message after waiting 5s for Honeypot).
- Re-run the D7 alias crawl against the live domain to confirm old URLs still resolve.
