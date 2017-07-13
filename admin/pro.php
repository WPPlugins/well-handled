<?php
/**
 * Admin: Premium License
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

$data = array(
	'forms'=>array(
		'pro'=>array(
			'action'=>'wh_ajax_pro',
			'n'=>wp_create_nonce('wh-nonce'),
			'license'=>\blobfolio\wp\wh\options::get('license'),
			'errors'=>array(),
			'saved'=>false,
			'loading'=>false
		)
	)
);
$license = \blobfolio\wp\wh\license::get($data['forms']['pro']['license']);
$data['license'] = $license->get_license();

?><div class="wrap" id="vue-pro" data-env="<?php echo esc_attr(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>" v-cloak>
	<h1><?php echo esc_html__('Well-Handled: Premium License', 'well-handled'); ?></h1>

	<?php
	// Warn about OpenSSL.
	if (!function_exists('openssl_get_publickey')) {
		echo '<div class="notice notice-warning">';
			echo '<p>' . esc_html__('Please ask your system administrator to enable the OpenSSL PHP extension. Without this, your site will be unable to decode and validate the license details itself. In the meantime, Well-Handled will try to offload this task to its own server. This should get the job done, but won\'t be as efficient and could impact performance a bit.', 'well-handled') . '</p>';
		echo '</div>';
	}
	?>

	<div class="updated" v-if="forms.pro.saved"><p><?php echo esc_html__('Your license has been saved!', 'well-handled'); ?></p></div>
	<div class="error" v-for="error in forms.pro.errors"><p>{{error}}</p></div>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder wh-columns one-two">

			<!-- License -->
			<div class="postbox-container two">
				<div class="postbox">
					<h3 class="hndle"><?php echo esc_html__('License Key', 'well-handled'); ?></h3>
					<div class="inside">
						<form name="proForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="proSubmit">
							<textarea id="wh-license" class="wh-code" name="license" v-model.trim="forms.pro.license" placeholder="Paste your license key here."></textarea>
							<p><button type="submit" v-bind:disabled="forms.pro.loading" class="button button-primary button-large"><?php echo esc_html__('Save', 'well-handled'); ?></button></p>
						</form>
					</div>
				</div>
			</div><!--.postbox-container-->

			<!-- License -->
			<div class="postbox-container one">

				<div class="postbox" v-if="!license.license_id">
					<h3 class="hndle"><?php echo esc_html__('The Goodies', 'well-handled'); ?></h3>
					<div class="inside">
						<a href="<?php echo WH_URL; ?>" target="_blank"><?php echo file_get_contents(WH_BASE . 'img/logo.svg'); ?></a>

						<p><?php
						echo sprintf(
							esc_html__("Curious what happens to your beautiful transactional emails after they're sent? With a %s you can track open rates and clicks, search send history, and even view full message content.", 'well-handled'),
							'<a href="' . WH_URL . '" target="_blank">' . esc_html__('Pro License', 'well-handled') . '</a>'
						); ?></p>

						<p><?php
						echo sprintf(
							esc_html__('To learn more, visit %s.', 'well-handled'),
							'<a href="' . WH_URL . '" target="_blank">wellhandled.io</a>'
						);
						?></p>
					</div>
				</div>

				<div class="postbox" v-if="license.license_id">
					<h3 class="hndle"><?php echo esc_html__('Your License', 'well-handled'); ?></h3>
					<div class="inside">
						<table class="wh-meta">
							<tbody>
								<tr>
									<th scope="row"><?php echo esc_html__('Created', 'well-handled'); ?></th>
									<td>{{license.date_created}}</td>
								</tr>
								<tr v-if="license.date_created !== license.date_updated">
									<th scope="row"><?php echo esc_html__('Updated', 'well-handled'); ?></th>
									<td>{{license.date_updated}}</td>
								</tr>
								<tr v-if="license.errors.revoked">
									<th class="wh-fg-orange" scope="row"><?php echo esc_html__('Revoked', 'well-handled'); ?></th>
									<td>{{license.date_revoked}}</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Name', 'well-handled'); ?></th>
									<td>{{license.name}}</td>
								</tr>
								<tr v-if="license.company">
									<th scope="row"><?php echo esc_html__('Company', 'well-handled'); ?></th>
									<td>{{license.company}}</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Email', 'well-handled'); ?></th>
									<td>{{license.email}}</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Type', 'well-handled'); ?></th>
									<td>{{license.type}}</td>
								</tr>
								<tr>
									<th v-bind:class="{'wh-fg-orange' : license.errors.item}" scope="row"><?php echo esc_html__('Thing', 'well-handled'); ?></th>
									<td>{{license.item}}</td>
								</tr>
								<tr v-if="license.type === 'single'">
									<th v-bind:class="{'wh-fg-orange' : license.errors.domain}" scope="row"><?php echo esc_html__('Domain(s)', 'well-handled'); ?></th>
									<td>
										<div v-for="domain in license.domains">{{domain}}</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Help', 'well-handled'); ?></th>
									<td>
										<span v-if="!license.errors.domain && !license.errors.item && !license.errors.revoked"><?php echo esc_html__('Thanks for going Pro!', 'well-handled'); ?></span>
										<?php
										echo sprintf(
											__('If you have any questions or need help, visit %s.', 'well-handled'),
											'<a href="' . WH_URL . '" target="_blank">blobfolio.com</a>'
										);
										?>
									</td>
							</tbody>
						</table>
					</div>
				</div>

				<?php
				$plugins = \blobfolio\wp\wh\admin::sister_plugins();
				if (count($plugins)) {
					?>
					<div class="postbox">
						<div class="inside">
							<a href="https://blobfolio.com/" target="_blank" class="sister-plugins--blobfolio"><?php echo file_get_contents(WH_BASE . 'img/blobfolio.svg'); ?></a>

							<div class="sister-plugins--intro">
								<?php
								echo sprintf(
									esc_html__('Impressed with %s?', 'well-handled') . '<br>' .
									esc_html__('You might also enjoy these other fine and practical plugins from %s.', 'well-handled'),
									'<strong>Well-Handled</strong>',
									'<a href="https://blobfolio.com/" target="_blank">Blobfolio, LLC</a>'
								);
								?>
							</div>

							<nav class="sister-plugins">
								<?php foreach ($plugins as $p) { ?>
									<div class="sister-plugin">
										<a href="<?php echo esc_attr($p['url']); ?>" target="_blank" class="sister-plugin--name"><?php echo esc_html($p['name']); ?></a>

										<div class="sister-plugin--text"><?php echo esc_html($p['description']); ?></div>
									</div>
								<?php } ?>
							</nav>
						</div>
					</div>
				<?php } ?>

			</div><!--.postbox-container-->

		</div><!--#post-body-->
	</div><!--#poststuff-->
</div><!--.wrap-->
