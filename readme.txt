=== Plugin Name ===
Contributors: blobfolio
Donate link: https://blobfolio.com/plugin/well-handled/
Tags: template, handlebar, mustache, css, email, transactional, analytics
Requires at least: 4.7
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build, manage, preview, send, and track complex transactional email templates from WordPress.

== Description ==

Well-Handled lets developers build, manage, preview, send, and track complex transactional email templates with WordPress, freeing them from the time and expense of using a third-party service like Mandrill.  It comes with a ton of template processing options, easy drop-in functions for generating and sending transactional emails, and hookable filters for developers with additional needs.

  * Manage and preview email templates through WP-Admin;
  * Color-coded editor with dozens of themes;
  * Support for Handlebar/Mustache markup;
  * Preview templates in WP-Admin or send as an email;
  * Numerous post-processing options such as CSS inlining, comment removal, whitespace compression, etc., let you keep your working code readable and the rendered product optimal;
  * Shortcode and fragment support (like reusable headers, etc.);
  * Pro: send emails via SMTP, Amazon SES, or Mandrill;
  * Pro: track open rates and clicks, search send history, view statistics, access full message details;
  * Pro: assign template and statistic access on a per-role basis;
  * Pro: mail sending via queue instead of realtime

== Installation ==

1. Unzip the archive and upload the entire `well-handled` directory to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Done!

== Requirements ==

Well-Handled is more complex than the average plugin and therefore requires a litlte more from your server:

 * WordPress 4.7 or later.
 * PHP 5.6 or later.
 * PHP extensions: date, dom, filter, hash, imap, json, libxml, openssl, pcre
 * UTF-8 encoding.
 * Well-Handled is *not* compatible with WordPress Multi-Site.

Please note: it is **not safe** to run WordPress atop a version of PHP that has reached its [End of Life](http://php.net/supported-versions.php). As of right now, that means your server should only be running **PHP 5.6 or newer**.

Future releases of this plugin might, out of necessity, drop support for old, unmaintained versions of PHP. To ensure you continue to receive plugin updates, bug fixes, and new features, just make sure PHP is kept up-to-date. :)

== Frequently Asked Questions ==

Please see [blobfolio.com](https://blobfolio.com/plugin/well-handled/) for more information.

== Pro Licensing ==

Well-Handled’s template management, processing options, and send functions are free for anyone to use.

A Pro License, which costs about the same as an expensive sandwich, gives you everything you need to level-up your email game:

 * SMTP and Amazon SES support;
 * Open and click tracking;
 * Searchable email history (metadata and/or full content);
 * Stats, stats, stats!
 * Access Control (e.g. set RW access by user type);

See [blobfolio.com](https://blobfolio.com/plugin/well-handled/) for more information or to purchase a license.

== Screenshots ==

1. Manage templates in a familiar setting (WP Admin).
2. Code editor supports themable syntax highlighting.
3. Preview your templates, test compile options, and even pass arbitrary data to verify correct rendering.
4. Comprehensive reference materials viewable from WP Admin.
5. Pro: can send via SMTP or Amazon SES, track open and click rates, and more.
6. Pro: can search send activity and view full copies of messages sent.
7. Pro: pretty stats!

== Changelog ==

= 2.0.4 =
* [change] Updated MIME and domain databases.
* [fix] Localization issue.

= 2.0.3 =
* [change] Updated MIME and domain databases.

= 2.0.2 =
* [fix] Localization issue.

= 2.0.1 =
* [new] Added Mandrill to the send options for Pro users.
* [fix] Linkify phone detection issue.

= 2.0.0 =
* [misc] The plugin code has been completely overhauled, updated, and streamlined!
* [change] PHP 5.6 or newer is now required.
* [new] Optional queue-based sending (Pro).
* [new] `linkify` template option to convert plain-text URLs, email addresses, and telephone numbers to clickable HTML links.
* [new] Better support for internationalized and Unicode domains.
* [new] Tools for data pruning (Pro).
* [improved] Template previews now render in a modal; can preview before saving changes.

= 1.5.5 =
* [change] Improved upgrade compatibility notice.

= 1.5.4 =
* [change] Future releases will require PHP 5.6+.

= 1.5.3 =
* [fix] PHP notices.

= 1.5.2 =
* [fix] Bug affecting UTM tag insertion on links with #hash fragments.

= 1.5.1 =
* [change] `inflect` helper can now accept an array as the `$count` variable.

= 1.5 =
* [new] Fragment support (e.g. common template parts).
* [new] Array helpers: `avg`, `count`, `join`, `min`, `max`, `sum`.
* [new] Comparison helpers: `ifGreater`, `ifLesser`.
* [new] Misc helpers: `currency`, `nl2br`, `now`, `wp_bloginfo`, `wp_site_url`.
* [misc] Full overhaul of Handlebar parsing.
* [misc] Cleaned up documentation.

= 1.0.2 =
* [new] Inaugural WordPress.org release!

= 1.0.1 =
* [new] Strip `<style>` processing option.
* [change] Improved documentation.

= 1.0 =
* [new] First public release!

== Upgrade Notice ==

= 2.0.4 =
* [change] Updated MIME and domain databases.
* [fix] Localization issue.

= 2.0.3 =
* [change] Updated MIME and domain databases.

= 2.0.2 =
* [fix] Localization issue.

= 2.0.1 =
* [new] Added Mandrill to the send options for Pro users.
* [fix] Linkify phone detection issue.

= 2.0.0 =
* [new] This is a major new release. Please note, PHP 5.6 or newer is required to run this version.

= 1.5.5 =
* [change] Improved upgrade compatibility notice.

= 1.5.4 =
* [change] Future releases will require PHP 5.6+.

= 1.5.3 =
* [fixed] PHP notices;

= 1.5.2 =
* [fixed] Bug affecting UTM tag insertion on links with #hash fragments;

= 1.5.1 =
* [improved] #inflect helper can now accept an array as the 'count' variable;

= 1.5 =
* [new] Fragment support (e.g. common template parts);
* [new] Added array helpers: avg, count, join, min, max, sum;
* [new] Added comparison helpers: ifGreater, ifLesser;
* [new] Added misc helpers: currency, nl2br, now, wp_bloginfo, wp_site_url;
* [improved] Full overhaul of Handlebar parsing;
* [improved] Cleaned up documentation;

= 1.0.2 =
* [new] Inaugural WordPress.org release!

= 1.0.1 =
* [new] Strip `<style>` processing option;
* [improved] Documentation;