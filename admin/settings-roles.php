<?php
/**
 * Admin: Settings
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

global $wpdb;

$nonce = wp_create_nonce('wh-nonce');
$data = array(
	'forms'=>array(
		'settings'=>array(
			'action'=>'wh_ajax_settings_roles',
			'n'=>$nonce,
			'roles'=>\blobfolio\wp\wh\options::get('roles'),
			'errors'=>array(),
			'saved'=>false,
			'loading'=>false
		)
	)
);
// Javascript doesn't like bools.
\blobfolio\wp\wh\vendor\common\ref\cast::to_int($data['forms']['settings']['roles']);

?><div class="wrap" id="vue-settings" data-env="<?php echo esc_attr(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>" v-cloak>
	<h1><?php echo esc_html__('Well-Handled: Settings & Tools', 'well-handled'); ?></h1>



	<!-- ==============================================
	STATUS UPDATES
	=============================================== -->
	<div class="updated" v-if="forms.settings.saved"><p><?php echo esc_html__('Your settings have been saved!', 'well-handled'); ?></p></div>
	<div class="error" v-for="error in forms.settings.errors"><p>{{error}}</p></div>



	<?php \blobfolio\wp\wh\admin::settings_navigation(); ?>



	<div id="poststuff">
		<div id="post-body" class="metabox-holder wh-columns">

			<!-- Column One -->
			<div class="postbox-container">

				<form name="settingsForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="settingsSubmit">

					<!-- ==============================================
					MAIL SETTINGS
					=============================================== -->
					<div class="postbox">
						<h3 class="hndle"><?php echo esc_html__('User Access', 'well-handled'); ?></h3>
						<div class="inside">

							<p><?php echo esc_html__('By default, only site administrators can manage Well-Handled templates and view stats. But if you believe in delegation, you can grant access to lower-level users.', 'well-handled'); ?></p>

							<p><?php
							echo sprintf(
								esc_html__('If enabled, the users will be granted the %s for their respective roles.', 'well-handled'),
								'<a href="https://codex.wordpress.org/Roles_and_Capabilities#Roles" target="_blank">' . esc_html__('usual capabilities', 'well-handled') . '</a>'
							); ?></p>

							<table class="wh-results">
								<thead>
									<tr>
										<th><?php echo esc_html__('Role', 'well-handled'); ?></th>
										<th><?php echo esc_html__('Content', 'well-handled'); ?></th>
										<th><?php echo esc_html__('Stats', 'well-handled'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item, role) in forms.settings.roles">
										<td>{{role}}</td>
										<td><input type="checkbox" v-model.number="forms.settings.roles[role].content" v-bind:true-value="1" v-bind:false-value="0" /></td>
										<td><input type="checkbox" v-model.number="forms.settings.roles[role].stats" v-bind:true-value="1" v-bind:false-value="0" /></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<p><button type="submit" class="button button-large button-primary" v-bind:disabled="forms.settings.loading"><?php echo esc_html__('Save', 'well-handled'); ?></button></p>

				</form>

			</div><!--.postbox-container-->

			<!-- Column One -->
			<div class="postbox-container">
				&nbsp;
			</div><!--.postbox-container-->

		</div><!--#post-body-->
	</div><!--#poststuff-->
</div><!--.wrap-->
