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
			'action'=>'wh_ajax_settings_queue',
			'n'=>$nonce,
			'send_queue'=>\blobfolio\wp\wh\options::get('send_queue'),
			'errors'=>array(),
			'saved'=>false,
			'loading'=>false
		),
		'queue'=>array(
			'action'=>'wh_ajax_queue',
			'n'=>$nonce
		)
	),
	'queue'=>array(
		'total'=>0,
		'due'=>0,
		'next'=>''
	)
);

// Javascript doesn't like bools.
$data['forms']['settings']['send_queue']['enabled'] = $data['forms']['settings']['send_queue']['enabled'] ? 1 : 0;


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
						<h3 class="hndle"><?php echo esc_html__('Send Scheduling', 'well-handled'); ?></h3>
						<div class="inside">

							<p><?php
							echo esc_html__('By default, Well-Handled sends messages immediately. This is recommended for most users, but if the send process adds too much time to the overall script execution (because of e.g. slow SMTP authentication), messages can instead be added to a queue and processed en masse at set intervals.', 'well-handled');
							?></p>

							<p><?php
							echo esc_html__('No coding changes are required either way, however if your messages include attached files, those files will need to still exist on the system at the time the message is actually sent.', 'well-handled');
							?></p>

							<label class="wh-label">
								<input type="checkbox" v-model.number="forms.settings.send_queue.enabled" v-bind:true-value="1" v-bind:false-value="0" /> <?php echo esc_html__('Enable Sending Queue', 'well-handled'); ?>
							</label>

							<table class="wh-settings">
								<tbody>
									<tr v-if="forms.settings.send_queue.enabled > 0">
										<th scope="row">
											<label for="settings-send_queue-frequency"><?php echo esc_html__('Frequency', 'well-handled'); ?></label>
										</th>
										<td>
											<select v-model="forms.settings.send_queue.frequency" id="settings-send_queue-frequency">
												<?php
												foreach (\blobfolio\wp\wh\options::QUEUE_FREQUENCIES as $k=>$v) {
													echo '<option value="' . $k . '">' . "$v</option>";
												}
												?>
											</select>

											<?php if (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) { ?>
												<p class="description">
													<?php echo esc_html__("WordPress' built-in job scheduler is not 100% precise. See the configuration instructions at right to have it run more regularly.", 'well-handled'); ?>
												</p>
											<?php } else { ?>
												<p class="description"><?php echo esc_html__('This frequency provides a target time only. Jobs will not be run until your crontab rule executes.', 'well-handled'); ?></p>
											<?php } ?>
										</td>
									</tr>
									<tr v-if="forms.settings.send_queue.enabled > 0">
										<th scope="row">
											<label for="settings-send_queue-qty"><?php esc_html__('Batch Size', 'well-handled'); ?></label>
										</th>
										<td>
											<input type="number" min="1" max="30" v-model="forms.settings.send_queue.qty" id="settings-send_queue-qty" />

											<p class="description"><?php echo esc_html__('This defines the maximum number of messages that will be processed at one time.', 'well-handled'); ?></p>

											<p class="description">
												<u><?php echo esc_html__('Be careful', 'well-handled'); ?>:</u> <?php
												echo sprintf(
													esc_html__('WordPress must be able to process this number of messages in *under %s %s %s* to avoid the jobs overlapping (which could be bad).', 'well-handled'),
													'{{forms.settings.send_queue.frequency}}',
													'<span v-if="forms.settings.send_queue.frequency === 1">' . esc_html__('minute', 'well-handled') . '</span>',
													'<span v-else>' . esc_html__('minutes', 'well-handled') . '</span>'
												);
												?>
											</p>

											<p class="description"><?php echo esc_html__('It is recommended you start with a conservative value and nudge it upwards based on the realworld execution times you see.', 'well-handled'); ?></p>
										</td>
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

				<!-- ==============================================
				QUEUE
				=============================================== -->
				<div class="postbox" v-if="queue.next">
					<h3 class="hndle"><?php echo esc_html__('Current Queue', 'well-handled'); ?></h3>
					<div class="inside">
						<p v-if="!queue.total"><?php echo esc_html__('There are no messages in the queue right now.', 'well-handled'); ?></p>

						<table v-else class="wh-meta">
							<tbody>
								<tr>
									<th scope="row"><?php echo esc_html__('Queued', 'well-handled'); ?></th>
									<td>{{queue.total}}</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Due Now', 'well-handled'); ?></th>
									<td>{{queue.due}}</td>
								</tr>
								<tr>
									<th scope="row"><?php echo esc_html__('Next Job', 'well-handled'); ?></th>
									<td>{{queue.next}}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- ==============================================
				INSTRUCTIONS
				=============================================== -->
				<div class="postbox">
					<h3 class="hndle"><?php echo esc_html__('CRON Configuration', 'well-handled'); ?></h3>
					<div class="inside">
						<p><?php
						echo sprintf(
							esc_html__("In order to have WordPress jobs process more regularly (and frequently), it is necessary to disable the built-in job runner and use your server's %s handler instead.", 'well-handled'),
							'<a href="https://en.wikipedia.org/wiki/Cron" target="_blank">CRON</a>'
						); ?></p>

						<p v-if="forms.settings.send_queue.enabled > 0"><?php echo esc_html__('If this is too technical or if your server environment does not provide crontab access, please do not use the queue feature.', 'well-handled'); ?></p>

						<p><?php
						echo sprintf(
							esc_html__('First, open `%s` and add the following anywhere in the middle', 'well-handled'),
							trailingslashit(ABSPATH) . 'wp-config.php'
						); ?>:</p>

						<?php // @codingStandardsIgnoreStart ?>
						<pre class="language-php line-numbers"><code># Disable built-in task runner.
define('DISABLE_WP_CRON', true);</code></pre>

						<p><?php echo esc_html__('Then add the following job to your crontab', 'well-handled'); ?>:</p>
						<pre class="language-bash line-numbers"><code># Check for scheduled tasks once per minute.
*	*	*	*	*	wget -O- <?php echo site_url('wp-cron.php?doing_wp_cron'); ?> > /dev/null 2>&amp;1

# Note: the crontab file should have a blank line at the end.
# Some CRON handlers won't run the last entry otherwise. :)
&nbsp;
&nbsp;</code></pre>
						<?php // @codingStandardsIgnoreEnd ?>
					</div>
				</div>

			</div><!--.postbox-container-->

		</div><!--#post-body-->
	</div><!--#poststuff-->
</div><!--.wrap-->
