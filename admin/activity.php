<?php
/**
 * Admin: Activity
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



// Find the boundaries.
global $wpdb;
$date_min = $wpdb->get_var("SELECT MIN(DATE(`date_created`)) FROM `{$wpdb->prefix}wh_messages`");
if (!is_null($date_min)) {
	$date_max = $wpdb->get_var("SELECT MAX(DATE(`date_created`)) FROM `{$wpdb->prefix}wh_messages`");
}
else {
	$date_min = current_time('Y-m-d');
	$date_max = current_time('Y-m-d');
}



$orders = array(
	'date_created'=>__('Date', 'well-handled'),
	'email'=>__('Email', 'well-handled'),
	'name'=>__('Name', 'well-handled'),
	'subject'=>__('Subject', 'well-handled'),
	'template'=>__('Template', 'well-handled')
);



$templates = array();
$dbResult = $wpdb->get_results("SELECT m.template, p.post_title FROM `{$wpdb->prefix}wh_messages` AS m LEFT JOIN `{$wpdb->prefix}posts` AS p ON p.post_name=m.template GROUP BY m.template ORDER BY m.template ASC", ARRAY_A);
if (is_array($dbResult) && count($dbResult)) {
	foreach ($dbResult as $Row) {
		$templates[$Row['template']] = $Row['template'] . ($Row['post_title'] ? " ({$Row['post_title']})" : '');
	}
}



$data = array(
	'date_min'=>$date_min,
	'date_max'=>$date_max,
	'forms'=>array(
		'search'=>array(
			'action'=>'wh_ajax_activity',
			'n'=>wp_create_nonce('wh-nonce'),
			'date_min'=>$date_min,
			'date_max'=>$date_max,
			'email'=>'',
			'emailExact'=>1,
			'name'=>'',
			'nameExact'=>1,
			'subject'=>'',
			'subjectExact'=>1,
			'template'=>'',
			'method'=>'',
			'opened'=>-1,
			'page'=>0,
			'pageSize'=>10,
			'orderby'=>'date_created',
			'order'=>'desc',
			'errors'=>array(),
			'loading'=>false
		)
	),
	'modal'=>false,
	'preview'=>array(
		'options'=>'',
		'data'=>''
	),
	'results'=>array(
		'page'=>0,
		'pages'=>0,
		'total'=>0,
		'items'=>array()
	),
	'searched'=>false
);

?><div class="wrap" id="vue-search" data-env="<?php echo esc_attr(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>" v-cloak>
	<h1><?php echo esc_html__('Well-Handled: Activity', 'well-handled'); ?></h1>

	<div class="error" v-for="error in forms.search.errors"><p>{{error}}</p></div>
	<div class="updated" v-if="!searched"><p><?php echo esc_html__('The sending activity is being fetched. Hold tight.', 'well-handled'); ?></p></div>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder wh-columns one-two" v-if="searched">

			<!-- Results -->
			<div class="postbox-container two">
				<div class="postbox">
					<h3 class="hndle">
						<?php echo esc_html__('Sent', 'well-handled'); ?>
						<span v-if="results.total">({{results.total}})</span>
					</h3>
					<div class="inside">
						<p v-if="!results.total"><?php echo esc_html__('No messages matched the search. Sorry.', 'well-handled'); ?></p>

						<table v-if="results.total" class="wh-results">
							<thead>
								<tr>
									<th><?php echo esc_html__('Date', 'well-handled'); ?></th>
									<th><?php echo esc_html__('Template', 'well-handled'); ?></th>
									<th><?php echo esc_html__('Message', 'well-handled'); ?></th>
									<th><?php echo esc_html__('Stats', 'well-handled'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="item in results.items">
									<td>{{item.date_created}}</td>
									<td>
										<a v-on:click.prevent="forms.search.template = item.template_slug; searchSubmit()" style="cursor:pointer">{{item.template_slug}}</a>
									</td>
									<td>
										<table class="wh-meta">
											<tbody>
												<tr v-if="item.message.email">
													<th scope="row"><?php echo esc_html__('Email', 'well-handled'); ?></th>
													<td>
														<a v-on:click.prevent="forms.search.email = item.message.email; forms.search.emailExact = 1; searchSubmit()" style="cursor:pointer">{{item.message.email}}</a>
													</td>
												</tr>
												<tr v-if="item.message.name">
													<th scope="row"><?php echo esc_html__('Name', 'well-handled'); ?></th>
													<td>
														<a v-on:click.prevent="forms.search.name = item.message.name; forms.search.nameExact = 1; searchSubmit()" style="cursor:pointer">{{item.message.name}}</a>
													</td>
												</tr>
												<tr v-if="item.message.subject">
													<th scope="row"><?php echo esc_html__('Subject', 'well-handled'); ?></th>
													<td>
														<a v-on:click.prevent="forms.search.subject = item.message.subject; forms.search.subjectExact = 1; searchSubmit()" style="cursor:pointer">{{item.message.subject}}</a>
													</td>
												</tr>
												<tr v-if="item.message.message">
													<th scope="row"><?php echo esc_html__('Message', 'well-handled'); ?></th>
													<td>
														<a v-on:click.prevent="getMessage(item.message.message, item.message.options, item.message.data)" class="button button-small"><?php echo esc_html__('View', 'well-handled'); ?></a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
									<td>
										<table class="wh-meta">
											<tbody>
												<tr>
													<th scope="row"><?php echo esc_html__('Built In', 'well-handled'); ?></th>
													<td>{{item.stats.compilation_time}}s</td>
												</tr>
												<tr>
													<th scope="row"><?php echo esc_html__('Sent In', 'well-handled'); ?></th>
													<td>{{item.stats.execution_time}}s</td>
												</tr>
												<tr>
													<th scope="row"><?php echo esc_html__('Method', 'well-handled'); ?></th>
													<td>
														<a v-on:click.prevent="forms.search.method = item.stats.method; searchSubmit()" style="cursor:pointer">
															<span v-if="item.stats.method === 'wp_mail'"><?php echo esc_html__('Default', 'well-handled'); ?></span>
															<span v-else-if="item.stats.method === 'smtp'">SMTP</span>
															<span v-else-if="item.stats.method === 'mandrill'">Mandrill</span>
															<span v-else>Amazon SES</span>
														</a>
													</td>
												</tr>
												<tr>
													<th scope="row"><?php echo esc_html__('Opened', 'well-handled'); ?></th>
													<td>
														<span v-if="item.stats.opened"><?php echo esc_html__('Yes', 'well-handled'); ?></span>
														<span v-else><?php echo esc_html__('No', 'well-handled'); ?></span>
													</td>
												</tr>
												<tr v-if="item.stats.clicks">
													<th scope="row"># <?php echo esc_html__('Clicks', 'well-handled'); ?></th>
													<td>{{item.stats.clicks}}</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>

						<nav class="wh-pagination" v-if="results.pages > 0">
							<a v-bind:disabled="forms.search.loading || results.page === 0" v-on:click.prevent="!forms.search.loading && pageSubmit(-1)" class="wh-pagination--link"><span class="dashicons dashicons-arrow-left-alt2"></span> <?php echo esc_html__('Back', 'well-handled'); ?></a>

							<span class="wh-pagination--current wh-fg-grey">{{results.page + 1}} / {{results.pages + 1}}</span>

							<a v-bind:disabled="forms.search.loading || results.page === results.pages" v-on:click.prevent="!forms.search.loading && pageSubmit(1)" class="wh-pagination--link"><?php echo esc_html__('Next', 'well-handled'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
						</nav>
					</div>
				</div>
			</div><!--.postbox-container-->

			<!-- Search -->
			<div class="postbox-container one">

				<div class="postbox">
					<h3 class="hndle"><?php echo esc_html__('Search', 'well-handled'); ?></h3>
					<div class="inside">
						<form name="searchForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" v-on:submit.prevent="searchSubmit">
							<table class="wh-settings narrow">
								<tbody>
									<tr>
										<th scope="row"><label for="search-date_min"><?php echo esc_html__('From', 'well-handled'); ?></label></th>
										<td>
											<input type="date" id="search-date_min" v-model="forms.search.date_min" required v-bind:min="date_min" v-bind:max="date_max" />
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-date_max"><?php echo esc_html__('To', 'well-handled'); ?></label></th>
										<td>
											<input type="date" id="search-date_max" v-model="forms.search.date_max" required v-bind:min="date_min" v-bind:max="date_max" />
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-email"><?php echo esc_html__('Email', 'well-handled'); ?></label></th>
										<td>
											<input type="text" id="search-email" v-model.trim="forms.search.email" minlength="3" />

											<p v-if="forms.search.email.length >= 3"><label><input type="checkbox" v-model.number="forms.search.emailExact" v-bind:true-value="1" v-bind:false-value="0" /> <?php echo esc_html__('Exact Match', 'well-handled'); ?></input></label></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-name"><?php echo esc_html__('Name', 'well-handled'); ?></label></th>
										<td>
											<input type="text" id="search-name" v-model.trim="forms.search.name" minlength="3" />

											<p v-if="forms.search.name.length >= 3"><label><input type="checkbox" v-model.number="forms.search.nameExact" v-bind:true-value="1" v-bind:false-value="0" /> <?php echo esc_html__('Exact Match', 'well-handled'); ?></input></label></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-subject"><?php echo esc_html__('Subject', 'well-handled'); ?></label></th>
										<td>
											<input type="text" id="search-subject" v-model.trim="forms.search.subject" minlength="3" />

											<p v-if="forms.search.subject.length >= 3"><label><input type="checkbox" v-model.number="forms.search.subjectExact" v-bind:true-value="1" v-bind:false-value="0" /> <?php echo esc_html__('Exact Match', 'well-handled'); ?></input></label></p>
										</td>
									</tr>
									<?php if (count($templates)) { ?>
										<tr>
											<th scope="row"><label for="search-template"><?php echo esc_html__('Template', 'well-handled'); ?></label></th>
											<td>
												<select id="search-template" v-model.trim="forms.search.template">
													<option value=""> --- </option>
													<?php
													foreach ($templates as $k=>$v) {
														echo '<option value="' . esc_attr($k) . '">' . esc_attr($v) . '</option>';
													}
													?>
												</select>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<th scope="row"><label for="search-method"><?php echo esc_html__('Method', 'well-handled'); ?></label></th>
										<td>
											<select id="search-method" v-model.trim="forms.search.method">
												<option value=""> --- </option>
												<option value="wp_mail"><?php echo esc_html__('Default', 'well-handled'); ?></option>
												<option value="smtp">SMTP</option>
												<option value="ses">Amazon SES</option>
												<option value="mandrill">Mandrill</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-opened"><?php echo esc_html__('Opened', 'well-handled'); ?></label></th>
										<td>
											<select id="search-opened" v-model.number="forms.search.opened">
												<option value="-1"> --- </option>
												<option value="1"><?php echo esc_html__('Yes', 'well-handled'); ?></option>
												<option value="0"><?php echo esc_html__('No', 'well-handled'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-pageSize"><?php echo esc_html__('Page Size', 'well-handled'); ?></label></th>
										<td>
											<input type="number" id="search-pageSize" v-model.number="forms.search.pageSize" min="1" max="50" step="1" />

											<p class="description"><?php echo esc_html__('Search results are paginated. This value indicates how much you want to see per page.', 'well-handled'); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="search-pageSize"><?php echo esc_html__('Order By', 'well-handled'); ?></label></th>
										<td>
											<select v-model="forms.search.orderby">
												<?php foreach ($orders as $k=>$v) { ?>
													<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
												<?php } ?>
											</select>
											<select v-model="forms.search.order">
												<option value="asc"><?php echo esc_html__('ASC', 'well-handled'); ?></option>
												<option value="desc"><?php echo esc_html__('DESC', 'well-handled'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row">&nbsp;</th>
										<td>
											<button type="submit" class="button button-large button-primary" v-bind:disabled="forms.search.loading"><?php echo esc_html__('Search', 'well-handled'); ?></button>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
				</div>

			</div><!--.postbox-container-->

		</div><!--#post-body-->
	</div><!--#poststuff-->

	<!-- Message Preview -->
	<transition name="fade">
		<div v-if="modal" id="wh-modal" class="wh-modal">
			<span v-on:click.prevent="modal=false" id="wh-modal--close" class="dashicons dashicons-no"></span>

			<div id="wh-modal--inner">
				<div id="wh-modal--inner--data" v-if="preview.data || preview.options">
					<fieldset class="wh-fieldset" v-if="preview.options">
						<label class="wh-label" for="wh-modal--options"><?php echo esc_html__('Build Options', 'well-handled'); ?></label>
						<textarea id="wh-modal--options" class="wh-code" v-model.trim="preview.options"></textarea>
					</fieldset>
					<fieldset class="wh-fieldset" v-if="preview.data">
						<label class="wh-label" for="wh-modal--data"><?php echo esc_html__('Data Used', 'well-handled'); ?></label>
						<textarea id="wh-modal--data" class="wh-code" v-model.trim="preview.data"></textarea>
					</fieldset>
				</div>

				<div id="wh-modal--inner--message">
					<iframe id="wh-modal--message" src="about:blank" frameborder="0" allowfullscreen></iframe>
				</div>
			</div>
		</div>
	</transition>

</div><!--.wrap-->
