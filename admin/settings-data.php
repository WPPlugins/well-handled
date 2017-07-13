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
			'action'=>'wh_ajax_settings_data',
			'n'=>$nonce,
			'nuclear'=>\blobfolio\wp\wh\options::get('nuclear'),
			'send_data'=>\blobfolio\wp\wh\options::get('send_data'),
			'errors'=>array(),
			'saved'=>false,
			'loading'=>false
		),
		'prune'=>array(
			'action'=>'wh_ajax_prune',
			'n'=>$nonce,
			'age'=>0,
			'mode'=>'full',
			'errors'=>array(),
			'saved'=>false,
			'loading'=>false
		)
	),
	'showExpiration'=>false,
	'hasContent'=>array(
		'content'=>\blobfolio\wp\wh\options::has('content'),
		'errors'=>\blobfolio\wp\wh\options::has('errors'),
		'links'=>\blobfolio\wp\wh\options::has('links'),
		'messages'=>\blobfolio\wp\wh\options::has('messages')
	),
	'prune'=>array(
		'full'=>100,
		'meta'=>60,
		'errors'=>15
	)
);

// Javascript doesn't handle boolean values well, so let's recast.
$data['forms']['settings']['send_data']['clicks'] = $data['forms']['settings']['send_data']['clicks'] ? 1 : 0;
$data['forms']['settings']['send_data']['errors'] = $data['forms']['settings']['send_data']['errors'] ? 1 : 0;
$data['forms']['settings']['nuclear'] = $data['forms']['settings']['nuclear'] ? 1 : 0;


?><div class="wrap" id="vue-settings" data-env="<?php echo esc_attr(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>" v-cloak>
	<h1><?php echo esc_html__('Well-Handled: Settings & Tools', 'well-handled'); ?></h1>



	<!-- ==============================================
	STATUS UPDATES
	=============================================== -->
	<div class="updated" v-if="forms.settings.saved"><p><?php echo esc_html__('Your settings have been saved!', 'well-handled'); ?></p></div>
	<div class="error" v-for="error in forms.settings.errors"><p>{{error}}</p></div>

	<div class="updated" v-if="forms.prune.saved"><p><?php echo esc_html__('The database has been pruned!', 'well-handled'); ?></p></div>
	<div class="error" v-for="error in forms.prune.errors"><p>{{error}}</p></div>



	<?php \blobfolio\wp\wh\admin::settings_navigation(); ?>



	<div id="poststuff">
		<div id="post-body" class="metabox-holder wh-columns">

			<!-- Column One -->
			<div class="postbox-container">

				<form name="settingsForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="settingsSubmit">


					<!-- ==============================================
					DATA AND TRACKING
					=============================================== -->
					<div class="postbox">
						<h3 class="hndle"><?php echo esc_html__('Data & Tracking', 'well-handled'); ?></h3>
						<div class="inside">

							<table class="wh-settings">
								<tbody>
									<tr>
										<th scope="row">
											<label for="settings-send_data-method"><?php echo esc_html__('Data to Keep', 'well-handled'); ?></label>
										</th>
										<td>
											<select v-model="forms.settings.send_data.method" id="settings-send_data-method">
												<option value="none"><?php echo esc_html__('Nothing', 'well-handled'); ?></option>
												<option value="meta"><?php echo esc_html__('Metadata', 'well-handled'); ?></option>
												<option value="full"><?php echo esc_html__('Everything', 'well-handled'); ?></option>
											</select>

											<p class="description" v-if="forms.settings.send_data.method === 'none'"><?php echo esc_html__('No information about sent messages will be stored.', 'well-handled'); ?></p>

											<p class="description" v-else-if="forms.settings.send_data.method === 'meta'"><?php echo esc_html__('Metadata like to/from, send times, etc., will be stored, but not the messages themselves.', 'well-handled'); ?></p>

											<p class="description" v-else><?php echo esc_html__('Full copies of each sent message (except for attachments) will be stored.', 'well-handled'); ?></p>
										</td>
									</tr>
									<tr v-if="'full' === forms.settings.send_data.method">
										<th scope="row"><label for="settings-send_data-retention"><?php echo esc_html__('Retention', 'well-handled'); ?></label></th>
										<td>
											<?php echo esc_html__('For X Days', 'well-handled'); ?>:
											<input type="number" v-model.number="forms.settings.send_data.retention" min="0" step="1" max="999" id="settings-send_data-retention" />

											<p class="description"><?php echo esc_html__('If you would rather not store *full* message content indefinitely, enter the desired retention period above (or "0" to keep it forever).', 'well-handled'); ?></p>
										</td>
									</tr>
									<tr v-if="'none' !== forms.settings.send_data.method">
										<th scope="row">
											<label><?php echo esc_html__('Tracking', 'well-handled'); ?></label>
										</th>
										<td>
											<label>
												<input type="checkbox" checked disabled />
												<strong><?php echo esc_html__('Track Opens', 'well-handled'); ?></strong>
											</label>

											<p class="description"><?php
											echo esc_html__('Open rates are tracked automatically when you opt to keep metadata or full message content. This information, however, is often unavailable because of privacy controls employed by email software. Enabling click tracking (below) will help improve this metric.', 'well-handled');
											?></p>
										</td>
									</tr>
									<tr v-if="'none' !== forms.settings.send_data.method">
										<th scope="row">
											&nbsp;
										</th>
										<td>
											<label>
												<input type="checkbox" v-model.number="forms.settings.send_data.clicks" v-bind:true-value="1" v-bind:false-value="0" />
												<strong><?php echo esc_html__('Track Clicks', 'well-handled'); ?></strong>
											</label>

											<p class="description"><?php
											echo esc_html__('Click-tracking is achieved by rewriting all links within an email to point back to your site (with a unique identifier). When a recipient clicks a link, Well-Handled records the hit and then seamlessly redirects them to the intended link target.', 'well-handled');
											?></p>
										</td>
									</tr>
									<tr v-if="'none' !== forms.settings.send_data.method">
										<th scope="row">
											&nbsp;
										</th>
										<td>
											<label>
												<input type="checkbox" v-model.number="forms.settings.send_data.errors" v-bind:true-value="1" v-bind:false-value="0" />
												<strong><?php echo esc_html__('Log Errors', 'well-handled'); ?></strong>
											</label>

											<p class="description"><?php
											echo esc_html__('"Error", in this case, being anything that prevents a message from being compiled and handed off to your outgoing mailserver. This includes things like template errors, malformed recipients, and authentication errors.', 'well-handled');
											?></p>

											<p class="description"><?php
											echo esc_html__('The ultimate deliverability of a message is not something this plugin can detect, however if your "from" address resolves to a real mailbox, bounce notifications should wind up there.', 'well-handled');
											?></p>

										</td>
									</tr>
								</tbody>
							</table>

						</div>
					</div>

					<!-- ==============================================
					HOUSEKEEPING
					=============================================== -->
					<div class="postbox">
						<h3 class="hndle"><?php echo esc_html__('Housekeeping', 'well-handled'); ?></h3>
						<div class="inside">
							<p><label>
								<input type="checkbox" v-model.number="forms.settings.nuclear" v-bind:true-value="1" v-bind:false-value="0" />
								<strong><?php echo esc_html__('Remove Data When Uninstalling', 'well-handled'); ?></strong>
							</label></p>

							<p class="description"><?php echo esc_html__('If the above is checked, *all* plugin data (settings, templates, messages, etc.) will be removed in the event you decide to uninstall Well-Handled. Otherwise, that data will be retained so you can pick up where you left off should you ever re-install the plugin.', 'well-handled'); ?></p>
						</div>
					</div>

					<p><button type="submit" class="button button-large button-primary" v-bind:disabled="forms.settings.loading"><?php echo esc_html__('Save', 'well-handled'); ?></button></p>

				</form>

			</div><!--.postbox-container-->

			<!-- Column One -->
			<div class="postbox-container">

				<!-- ==============================================
				PRUNE OLD DATA
				=============================================== -->
				<div class="postbox" v-if="hasContent.links || hasContent.messages || hasContent.content || hasContent.errors">
					<h3 class="hndle"><?php echo esc_html__('Prune Old Data', 'well-handled'); ?></h3>
					<div class="inside">

						<p><?php
						echo esc_html__('A lot of data can be accrued over time. If needed, you can manually remove records that have outlived their usefulness to free up some space.', 'well-handled');
						?></p>

						<table class="wh-settings">
							<tbody>
								<tr v-if="hasContent.messages">
									<th scope="row">
										<label for="settings-prune-full"><?php echo esc_html__('Everything', 'well-handled'); ?></label>
									</th>
									<td>
										<form name="pruneFullForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="pruneSubmit('full')">

											<label for="settings-prune-full"><?php echo esc_html__('Older Than X Days', 'well-handled'); ?>:</label>
											<input type="number" v-model.number="prune.full" id="settings-prune-full" min="30" max="999" step="1" />

											<p class="description"><?php
											echo sprintf(
												esc_html__('This will remove *all* records, metadata, and stats for messages sent more than %s days ago.', 'well-handled'),
												'{{prune.full}}'
											);
											?></p>

											<p><strong><?php echo esc_html__('Warning', 'well-handled'); ?>:</strong> <?php echo esc_html__('This cannot be undone!', 'well-handled'); ?></p>

											<p v-if="hasContent.links"><strong><?php echo esc_html__('Warning', 'well-handled'); ?></strong> <?php echo esc_html__('If recipients have saved any of these old messages for reference, the links within them will stop working.', 'well-handled'); ?></p>

											<button type="submit" class="button button-large" v-bind:disabled="forms.prune.loading"><?php echo esc_html__('Delete', 'well-handled'); ?></button>
										</form>
									</td>
								</tr>
								<tr v-if="hasContent.content">
									<th scope="row">
										<label for="settings-prune-meta"><?php echo esc_html__('Content', 'well-handled'); ?></label>
									</th>
									<td>
										<form name="pruneMetaForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="pruneSubmit('meta')">
											<label for="settings-prune-meta"><?php echo esc_html__('Older Than X Days', 'well-handled'); ?>:</label>
											<input type="number" v-model.number="prune.meta" id="settings-prune-meta" min="30" max="999" step="1" />

											<p class="description"><?php
											echo sprintf(
												esc_html__('This will remove the message content — but leave metadata, click stats, etc., alone — for any message sent more than %s days ago.', 'well-handled'),
												'{{prune.meta}}'
											);
											?></p>

											<p><strong><?php echo esc_html__('Warning', 'well-handled'); ?></strong> <?php echo esc_html__('This cannot be undone!', 'well-handled'); ?></p>

											<button type="submit" class="button button-large" v-bind:disabled="forms.prune.loading"><?php echo esc_html__('Delete', 'well-handled'); ?></button>
										</form>
									</td>
								</tr>
								<tr v-if="hasContent.errors">
									<th scope="row">
										<label for="settings-prune-errors"><?php echo esc_html__('Errors', 'well-handled'); ?></label>
									</th>
									<td>
										<form name="pruneErrorsForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="pruneSubmit('errors')">
											<label for="settings-prune-errors"><?php echo esc_html__('Older Than X Days', 'well-handled'); ?>:</label>
											<input type="number" v-model.number="prune.errors" id="settings-prune-errors" min="1" max="999" step="1" />

											<p class="description"><?php
											echo sprintf(
												esc_html__('This will remove all logged errors created more than %s days ago.', 'well-handled'),
												'{{prune.errors}}'
											);
											?></p>

											<p><strong><?php echo esc_html__('Warning', 'well-handled'); ?></strong> <?php echo esc_html__('This cannot be undone!', 'well-handled'); ?></p>

											<button type="submit" class="button button-large" v-bind:disabled="forms.prune.loading"><?php echo esc_html__('Delete', 'well-handled'); ?></button>
										</form>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

			</div><!--.postbox-container-->

		</div><!--#post-body-->
	</div><!--#poststuff-->
</div><!--.wrap-->
