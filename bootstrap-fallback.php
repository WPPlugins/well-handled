<?php
/**
 * Well-Handled - Fallback Bootstrap
 *
 * This is run on environments that do not meet the
 * main plugin requirements. It will either deactivate
 * the plugin (if it has never been active) or provide
 * a semi-functional fallback environment to keep the
 * site from breaking, and suggest downgrading to the
 * legacy version.
 *
 * @package Well-Handled
 * @author  Blobfolio, LLC <hello@blobfolio.com>
 */

/**
 * Do not execute this file directly.
 */
if (!defined('ABSPATH')) {
	exit;
}



// ---------------------------------------------------------------------
// Compatibility Checking
// ---------------------------------------------------------------------

// There will be errors. What are they?
$wh_errors = array();

if (version_compare(PHP_VERSION, '5.6.0') < 0) {
	$wh_errors['version'] = __('PHP 5.6.0 or newer is required.', 'well-handled');
}

if (function_exists('is_multisite') && is_multisite()) {
	$wh_errors['multisite'] = __('This plugin cannot be used on Multi-Site.', 'well-handled');
}

if (!class_exists('DOMDocument')) {
	$wh_errors['domdocument'] = __('The DOMDocument PHP extension is required.', 'well-handled');
}

if (!function_exists('libxml_disable_entity_loader')) {
	$wh_errors['libxml'] = __('The libxml PHP extension is required.', 'well-handled');
}

if (!function_exists('hash_algos') || !in_array('sha512', hash_algos(), true)) {
	$wh_errors['hash_algos'] = __('PHP must support basic hashing algorithms like SHA512.', 'well-handled');
}

// Will downgrading to the legacy version help?
$wh_downgrade = (
	(1 === count($wh_errors)) &&
	isset($wh_errors['version']) &&
	version_compare(PHP_VERSION, '5.4.0') >= 0
);

/**
 * Admin Notice
 *
 * @return bool True/false.
 */
function wh_admin_notice() {
	global $wh_errors;
	global $wh_downgrade;

	if (!is_array($wh_errors) || !count($wh_errors)) {
		return false;
	}
	?>
	<div class="notice notice-error">
		<p><?php
		echo sprintf(
			esc_html__('Your server does not meet the requirements for running %s. You or your system administrator should take a look at the following:', 'well-handled'),
			'<strong>Well-Handled Email Templates</strong>'
		);
		?></p>

		<?php
		foreach ($wh_errors as $error) {
			echo '<p>&nbsp;&nbsp;&mdash; ' . esc_html($error) . '</p>';
		}

		// Can we recommend the old version?
		if (isset($wh_errors['disabled'])) {
			unset($wh_errors['disabled']);
		}

		if ($wh_downgrade) {
			echo '<p>' .
			sprintf(
				esc_html__('As a *stopgap*, you can %s the Well-Handled plugin to the legacy *1.5.x* series. The legacy series *will not* receive updates or development support, so please ultimately plan to remove the plugin or upgrade your server environment.', 'well-handled'),
				'<a href="' . admin_url('update-core.php') . '">' . esc_html__('downgrade', 'well-handled') . '</a>'
			) . '</p>';
		}
		?>
	</div>
	<?php
	return true;
}
add_action('admin_notices', 'wh_admin_notice');

/**
 * Self-Deactivate
 *
 * If the environment can't support the plugin and the
 * environment never supported the plugin, simply
 * remove it.
 *
 * @return bool True/false.
 */
function wh_deactivate() {
	// If the DB version option is set, an older version must have
	// once been installed. We won't auto-deactivate.
	if ('never' !== get_option('wh_db_version', 'never')) {
		return false;
	}

	require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');
	deactivate_plugins(WH_INDEX);

	global $wh_errors;
	global $wh_downgrade;
	$wh_downgrade = false;
	$wh_errors['disabled'] = __('The plugin has been automatically disabled.', 'well-handled');

	if (isset($_GET['activate'])) {
		unset($_GET['activate']);
	}

	return true;
}
add_action('admin_init', 'wh_deactivate');

/**
 * Downgrade Update
 *
 * Pretend the legacy version is newer to make it easier
 * for people to downgrade. :)
 *
 * @param StdClass $option Plugin lookup info.
 * @return StdClass Option.
 */
function wh_fake_version($option) {

	// Argument must make sense.
	if (!is_object($option)) {
		return $option;
	}

	// Set up the entry.
	$path = 'well-handled/index.php';
	if (!array_key_exists($path, $option->response)) {
		$option->response[$path] = new stdClass();
	}

	// Steal some information from the installed plugin.
	require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');
	$info = get_plugin_data(WH_INDEX);

	$option->response[$path]->id = 0;
	$option->response[$path]->slug = 'well-handled';
	$option->response[$path]->plugin = $path;
	$option->response[$path]->new_version = '155-legacy';
	$option->response[$path]->url = $info['PluginURI'];
	$option->response[$path]->package = 'https://downloads.wordpress.org/plugin/well-handled.1.5.5.zip';
	$option->response[$path]->upgrade_notice = __('This will downgrade to the legacy 1.5.5 release, which is compatible with PHP 5.4. Do not upgrade from the legacy version until your server meets the requirements of the current release.', 'well-handled');

	// And done.
	return $option;
}
add_filter('transient_update_plugins', 'wh_fake_version');
add_filter('site_transient_update_plugins', 'wh_fake_version');


// --------------------------------------------------------------------- end compatibility



// ---------------------------------------------------------------------
// User Functions
// ---------------------------------------------------------------------

// These functions existed in an earlier version of the plugin and so
// are retained here for compatibility reasons. These functions are not
// functional in the fallback version; we just want to avoid breaking
// errors.

/**
 * Mail Template
 *
 * @param string|array $template_slug Template slug.
 * @param array $data Data.
 * @param array $options Options.
 * @return bool True/false.
 */
function wh_mail_template($template_slug, $data=null, $options=null) {
	// Send the email to the blog owner.
	$email = get_bloginfo('admin_email');
	$subject = sprintf('[%s] Server Incompatibility Error', get_bloginfo('name'));
	$body = 'Your server does not meet the requirements for running *Well-Handled Email Templates*. Please visit ' . admin_url('plugins.php') . ' to review the issues.';
	$body .= "\n\n" . str_repeat('-', 25) . "\n\nThe following message could not be compiled. The arguments are presented below for your reference.\n\n";

	// JSON is the easiest way to convey this information somewhat intelligibly.
	$out = array(
		'template'=>$template_slug,
		'template_options'=>$options,
		'data'=>$data
	);
	$body .= json_encode($out, JSON_PRETTY_PRINT);

	return wp_mail($email, $subject, $body);
}

/**
 * Mail General
 *
 * @param string|array $to To.
 * @param string $subject Subject.
 * @param string $message Message.
 * @param string|array $headers Headers.
 * @param string|array $attachments Attachments.
 * @param bool $testmode Testmode.
 * @return bool True/false.
 */
function wh_mail($to, $subject, $message, $headers=null, $attachments=null, $testmode=false) {
	return wp_mail($to, $subject, $message, $headers, $attachments);
}

/**
 * Build Template
 *
 * @param string|array $template_slug Template slug.
 * @param array $data Data.
 * @param array $options Options.
 * @return string|bool HTML or false.
 */
function wh_get_template($template_slug, $data=null, $options=null) {
	return false;
}

/**
 * Format (Single) Recipient
 *
 * @param string $email Email.
 * @param string $name Name.
 * @return string|bool Recipient or false.
 */
function wh_recipient($email, $name='') {
	$email = sanitize_email($email);
	return $email ? $email : false;
}

// --------------------------------------------------------------------- end functions
