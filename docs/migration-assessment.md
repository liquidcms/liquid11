# liquidcms.ca D7 → D11 migration assessment

Assessed 2026-10-25 against liquid7.ddev.site (D7 source, Evolve theme) and liquid11.ddev.site (Drupal 11.4.10, PHP 8.3, Olivero/Claro).

## What migrated cleanly

| Area | D7 | D11 | Notes |
|---|---|---|---|
| Content types | 10 | 10 | All bundles created. |
| Nodes | 121 | 117 | Missing 3 newer portfolio nodes (P1.3) and the webform demo node. |
| Taxonomy | 5 vocabs | 5 vocabs, 25 terms | 2 newer `drupal_version` terms missing. |
| Fields | 41 storages / 60 instances | 30 / 40 | Gaps are newsletter entities (dead in D7) and `field_videos`. |
| Files | 255 local + ~20 remote video | 254 | Remote youtube/vimeo file entities not migrated. |
| URL aliases | 130 | 130 | 90 alias URLs return 200 on D7; 85 of those also do on D11, 5 return 404 (P1.3). |
| Redirects | 4 | 4 | OK. |
| Image styles | 13 | 11 | 2 used imagecache_actions effects. |
| Users | admin only | admin only | Nothing to do. |
| Comments | 0 | n/a | Comment module not needed. |
| Custom blocks | 102 | 102 | Content only; none placed. |

## What's missing or broken

### P1: Correctness, quick wins
1. **PHP text format.** D7 node 141 (page) uses the `php` format. D11 migrated `filter.format.php` with only `filter_null`, so that page renders blank. Rewrite node 141 as HTML (or a small custom block/controller), then delete the `php` and `php_code` formats.
2. **Menus never ran.** `d7_menu` (0/5) and `d7_menu_links` (0/51) show as unprocessed. Main menu has 47 visible links. Running them is one command.
3. **D7 source changed after the migration.** Source now has 26 portfolio nodes (23 imported): CanadaBuys (PSPC), Specialty Insurance Agency, Capital Volley, plus terms Drupal 9/10. An incremental `migrate:import` picks them up.
4. **Demo cleanup not run.** `scripts/cleanup-demo.php` exists but the 42 unpublished Evolve demo nodes and 102 blocks are still there. Run it (after confirming the keep-list).

**P1 status (done 2026-10-25, snapshot `pre-p1` taken first):**
- Menus and links migrated. 13 links failed: Evolve demo/settings paths, the webform, and `/portfolio` and `/blog`, which return once the views exist (re-run `upgrade_d7_menu_links --update`). Enabled core contact, recreated the "Website feedback" form (info@liquidcms.ca), and granted visitors access. Hid "The Team" link, which D7 visitors didn't see.
- Imported 3 portfolio nodes, 2 terms and 5 aliases. Their 19 images aren't in the local D7 files folder; copy them from live, then re-run `upgrade_d7_file` and `upgrade_d7_node_complete_dexp_portfolio --update`.
- **Bigger find:** the Full HTML format (98 nodes, 24 blocks) migrated with `filter_null` (standing in for D7's codefilter/shortcode/lightbox filters) plus an enabled `filter_html`, so most pages rendered blank. Removed the null filter and disabled filter_html to match D7. Node 141 ("For Steve", a JS snippet, not PHP) is now Full HTML, and the `php`/`php_code` formats are deleted.
- Images copied from live and re-imported (269/272 files). Still missing, and absent on live too (Peter will sort out later): `images/portfolio/arch.png` (UMD Architecture item) and `CanadaBuys-home-50_0.png` (Home Page Gallery), plus 3 unused root CanadaBuys-home*.png. "For Steve" (node 141) deleted at Peter's request.
- Demo cleanup run: 43 nodes and 76 blocks removed, 26 kept.
- Parity crawl: all 90 D7 alias URLs that return 200 now return 200 on D11. The pathauto blog/forum patterns stay unmigrated on purpose (those modules are gone).

### P2: The site's look and structure (the big job)
5. **Theme.** Evolve/drupalexp has no D11 port. The front page is the default `/node` listing; on D7 it was assembled from custom blocks.
6. **Shortcodes.** 38 custom blocks (homepage sections, sidebars) are built from dexp shortcodes: `column` 114, `box` 54, `button` 52, `animate` 37, `accordion` 30, `stats` 29, `title` 28, `flipbox` 22, `tab` 16, `skillbar` 16, `piegraph` 13, `carousel` 9, `icon` 8. No published node body uses them, so the work is confined to blocks. Options: convert to plain HTML + theme CSS, or rebuild those sections in Layout Builder / SDC components.
7. **Views.** None of the 12 D7 views migrate. Needed: portfolio, blog, blog_categories, team, testimonial, latest_projects, clients, gallery, ios_apps, basic_page_slideshow. `/blog` and `/portfolio` currently 404.
8. **Block placement.** 514 D7 placements target Evolve regions; recreate per the new theme instead.

**P2 status (done 2026-10-25):** Evolve has been ported as a standalone D11 theme (`web/themes/custom/evolve`) and is now the default. It keeps Bootstrap 3.1.1 and Evolve's compiled CSS as is.
- **Theme:** the page, region, block, menu, node, pager, views-wrapper and form-label templates reproduce the D7 markup. Block html ids, classes and animation attributes come from a map in `evolve.theme`. D7's core/module base CSS is bundled as `css/d7-core.css`.
- **jQuery 4:** jQuery Migrate 4.0.2 is vendored in the theme. `.load(fn)` and `.load()` calls in bxslider, stellar, sticky and portfolio filter were patched to `.on('load')` / `.trigger('load')`.
- **Shortcodes:** the 26 kept custom blocks now hold the HTML D7 rendered for them (`scripts/d7-blocks-rendered.json` via `scripts/evolve-import-block-html.php`). Pie graph scripts became data attributes. The homepage Revolution slider is a "Home slider" custom block plus `js/evolve-slider.js`.
- **Views:** a new `evolve_views` module provides the dexp_bxslider, dexp_portfolio and dexp_grid2 styles. `scripts/evolve-create-views.php` rebuilds latest_projects, portfolio, testimonial, clients, gallery, blog, blog_categories and team. `/portfolio` and `/blog` work again, and the Portfolio and Blog menu links are back.
- **Nodequeues:** recreated as entityqueues by `scripts/migrate-nodequeues.php` (contrib entityqueue added).
- **Blocks:** placed by `scripts/evolve-place-blocks.php` (38 blocks, re-runnable).
- **Content:** synced 3 nodes edited in D7 after the migration (About, Home Page Gallery, Mimi O. now unpublished as in D7). Set the date settings from D7.
- **Checks:** home, about, portfolio, a portfolio item, blog, a blog post and contact were compared with headless Chrome screenshots, and none of them log JS errors.
- **Open items:**
  - Google fonts now load over https; D7 fell back to Arial because of http mixed content.
  - The D7 site timezone is Asia/Krasnoyarsk (copied as is; probably should be America/Toronto).
  - Node 88 embeds a smiley from the live site's `/sites/all/libraries`.
  - The slider animation only got a static check.
  - Lightbox for portfolio zoom is not wired (P3).
  - The reCAPTCHA on contact is not ported (P3).
  - The facts/pie graph section (blocks 80–84) was hidden on all screen sizes in D7, so it is not placed.

**Bootstrap 5 (done 2026-10-25, at Peter's request):** the theme now runs on Bootstrap 5.3.3 instead of 3.1.1.
- `scripts/bs3-to-bs5.php` converts markup. It shifts breakpoints so layouts switch at the same widths (BS3 xs/sm/md/lg → BS5 base/md/lg/xl), turns `hidden-*` into `d-*`, `pull-*` into `float-*`, and converts carousel, tab and data-bs-* attributes. The block import runs it, and it was applied to the templates and views styles.
- `css/bs3-compat.css` restores the BS3 base the Evolve CSS assumes: 14px type, heading sizes, 750/970/1170 containers, 30px gutters, button sizing and no link underline.
- One Evolve tab selector was moved to BS5's `a.active`.
- A re-import of the About node regenerated its alias (pathauto) and the redirect module auto-created redirects. I restored `/about` and deleted 6 bogus redirects. The parity crawl is clean again (89/89; `/steve` was removed on purpose).
- The blog sidebar "Recent/Popular" tabs (block 14) now render live views (`blog` block_1/block_2) through an `evolve_tabs` theme hook. The D7 `[embeded]` shortcode snapshot in the block body is ignored.
- Later fixes from Peter's review:
  - bxSlider images are forced to eager loading (lazy loading left a spinner).
  - Client logos use a recreated `logo` image style, centred in 140px slides.
  - The CanadaBuys files were imported once copied in.
  - Portfolio grid classes moved to a wrapper div, because the node render cache dropped them.
  - The "Why Choose" lists were pulled closer together.
- Search was removed at Peter's request (search module uninstalled). Portfolio zoom opens a GLightbox gallery (`js/evolve-lightbox.js`). The bxSlider arrows use D7's Font Awesome defaults.

### P3: Feature parity (contrib / custom)
9. **Videos.** `field_videos` (D7 video module, 4 items) failed, and remote YouTube/Vimeo file entities were skipped. Move to core Media with oEmbed remote video.
10. **SEO.** metatag (18 overrides), xmlsitemap → simple_sitemap, googleanalytics → google_tag, site_verify, globalredirect (core handles most).
11. **Ordering queues.** nodequeue `cool_projects`, `team`, `portfolio` → entityqueue, or a weight field.
12. **Forms/spam.** Contact form + honeypot/captcha/recaptcha. Webform has 1 unpublished demo node and 0 submissions; propose dropping it.
13. **Smaller items.** auto_nodetitle → auto_entitylabel, realname, extlink, lightbox2/juicebox galleries → a lightbox module, metatag, codefilter. imce/plupload are replaced by CKEditor 5 + Media Library.
14. **Custom `liquid` module.** Provides node access rules, a contact form alter, tokens and variables. Review and port only what's still used.
15. **Private files.** D7 had a private path; D11 has none configured (`field_private_test` only, likely test). Confirm, then ignore or configure.
16. **Ignore.** The 9 newsletter field errors are for entity types from a module no longer enabled in D7; `comment_body` messages are informational.

## Where the D7 contrib modules went
D7 had about 74 non-core modules enabled (counting the 12 drupalexp sub-modules and the xmlsitemap/media/rules families separately). On D11:

| Fate | D7 modules | D11 |
|---|---|---|
| **Moved into core** | views, views_ui, ctools, entity, entityreference, link, ckeditor, media, media_internet, file_entity, entity_token, globalredirect, jquery_update, libraries, views_bulk_operations, field_delete | Core (Views, Entity Reference, Link, CKEditor 5, Media + oEmbed, bulk actions, redirect-to-alias) |
| **Contrib replacement installed** | nodequeue, admin_menu, field_group, pathauto, redirect, token | entityqueue, admin_toolbar, field_group, pathauto, redirect, token |
| **Replaced by our theme/module code** | dexp + 11 dexp_* modules, shortcode, block_class, css_editor, lightbox2, views_slideshow(+cycle), imagecache_actions/canvasactions, realname | `evolve` theme, `evolve_views` module, GLightbox, recreated image styles, name logic in `evolve.theme` |
| **Not needed on D11** | devel, backup_migrate, advagg, module_filter, bundle_copy, plup/plupload, imce, adminrole, taxonomy_manager, ip2country, comment_ip, views_customtext_booster, views_php, php, emfield, masquerade, rules/rules_admin, webform (only a demo form, 0 submissions) | Dev/perf tools, now-core UX, or unused |
| **Still to add (P3)** | metatag(+views), xmlsitemap family, googleanalytics, site_verify, honeypot/captcha/recaptcha, extlink, auto_nodetitle, video/video_ui + media_youtube/vimeo, juicebox | metatag, simple_sitemap, google_tag, honeypot/captcha, extlink, auto_entitylabel if needed, core Media remote video |

## Suggested order
P1.1–P1.4 (one sitting), then pick the theme approach (P2.5–6), then views and block placement, then P3.

## Reusable lessons for future D7 → D11 migrations
- Check `migrate:status` for **Unprocessed > 0**, not just errors. Menus silently didn't run here.
- To keep a D7 page-builder theme's look, port it rather than rebuild it. Keep its compiled CSS/Bootstrap version, reproduce its markup in Twig, and snapshot shortcode-built blocks with D7's own `check_markup()` (bootstrap D7 via a php script in the D7 docroot).
- D11 ships jQuery 4: add jQuery Migrate 4 and patch `.load(fn)` / `.load()` in old plugins. Find failing behaviors by temporarily logging `e.stack` in `core/misc/drupal.js` attachBehaviors.
- Base the ported theme on `stable9` (install it first, or the site fatals), not `base theme: false`. Without a base, admin tabs, views and pagers lose their markup classes. Evolve now extends stable9 and has Bootstrap tab templates for local tasks.
- A theme with `base theme: false` loses core's views and pager classes. Add `views-view.html.twig` (including `css_class`) and a D7-style pager template.
- Views config overrides need `defaults[key] = FALSE` for every overridden option, or they silently fall back to the default display.
- node_complete `--idlist` needs the full `nid:vid:language` source id.
- Re-importing nodes or terms with `--update` lets pathauto regenerate aliases, and redirect's auto-redirect then creates redirects. Set `path.pathauto = 0` on migrated entities (or turn off auto-redirect) before incremental imports, and re-run the alias parity crawl afterwards.
- Moving off Bootstrap 3: shift breakpoints by one (sm→md, md→lg, lg→xl) so layouts break at the same widths, and add a small BS3-defaults CSS layer under the old theme CSS rather than rewriting it.
- Headless Chrome (`--screenshot`, `--dump-dom`, `--enable-logging`) is enough for side-by-side comparison and console errors. Disable entrance animations with injected CSS for the shot.
- Any D7 filter with no D11 equivalent (php, codefilter, shortcode, lightbox2) migrates as `filter_null`, which blanks the whole format. Check every `filter.format.*` for `filter_null` right after migrating. Here it hid nearly all content.
- The `php` text format migrates to `filter_null`, so content disappears silently. Grep the source for `body_format='php'` before migrating.
- Re-run `migrate:status` before cutover: the D7 DB had changed since the import (Total 26 vs Imported 23).
- Page-builder themes (drupalexp, etc.) hide content in shortcodes in custom blocks. Count shortcode tags in `field_data_body` and `block_custom` early to size the theme work.
- Cheap parity test: crawl every D7 `url_alias` on both sites and diff HTTP codes.
- On ddev, D7 drush can fail on composer's PHP platform check (PHP 7.3); `ddev mysql` against the source DB works for inventory.
