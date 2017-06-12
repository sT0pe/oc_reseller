<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
	<div class="container-fluid">
	  <div class="pull-right">
		<button id="ms-submit-button" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
		<a href="<?php echo $this->url->link('multimerch/seller', 'token=' . $token); ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
	  <h1><?php echo $ms_catalog_sellers_heading; ?></h1>
	  <ul class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
		<?php } ?>
	  </ul>
	</div>
  </div>
  <div class="container-fluid">
	<div style="display: none" class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
	  <button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	  <div class="panel panel-default">
	  <div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo isset($seller['seller_id']) ? $ms_catalog_sellerinfo_heading : $ms_catalog_sellers_newseller; ?></h3>
	  </div>
	  <div class="panel-body">
		<form id="ms-sellerinfo" class="form-horizontal">
			<input type="hidden" id="seller_id" name="seller[seller_id]" value="<?php echo $seller['seller_id']; ?>" />
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
				<li><a href="#tab-offers" data-toggle="tab">Offers</a></li>
			</ul>
			<div class="tab-content">
			<div class="tab-pane active" id="tab-general">

			<fieldset>
			<legend><?php echo $ms_catalog_sellerinfo_customer_data; ?></legend>
			 <div class="form-group required">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer; ?></label>

				<div class="col-sm-10">
				<?php if (!$seller['seller_id']) { ?>
				<select class="form-control" name="customer[customer_id]">
					<optgroup label="<?php echo $ms_catalog_sellerinfo_customer_new; ?>">
					<option value="0"><?php echo $ms_catalog_sellerinfo_customer_create_new; ?></option>
					</optgroup>
					<?php if (isset($customers)) { ?>
					<optgroup label="<?php echo $ms_catalog_sellerinfo_customer_existing; ?>">
					<?php foreach ($customers as $c) { ?>
					<option value="<?php echo $c['c.customer_id']; ?>"><?php echo $c['c.name']; ?></option>
					<?php } ?>
					</optgroup>
					<?php } ?>
				</select>
				<?php } else { ?>
					<a href="<?php echo $this->url->link('customer/customer/edit', 'token=' . $this->session->data['token'] . '&customer_id=' . $seller['seller_id'], 'SSL'); ?>"><?php echo $seller['name']; ?></a>
				<?php } ?>
				</div>
			</div>

			<div class="form-group required">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_firstname; ?></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="customer[firstname]" value="" />
				</div>
			</div>

			<div class="form-group required">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_lastname; ?></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="customer[lastname]" value="" />
				</div>
			</div>

			<div class="form-group required">
				<?php if (!$seller['seller_id']) { ?>
					<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_email; ?></label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="customer[email]" value="" />
					</div>
				<?php } else { ?>
					<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_email; ?></label>
					<div class="col-sm-10">
						<span><?php echo $seller['c.email']; ?></span>
					</div>
				<?php } ?>
			</div>

			<div class="form-group required">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_password; ?></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="customer[password]" value="" />
				</div>
			</div>

			<div class="form-group required">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_customer_password_confirm; ?></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="customer[password_confirm]" value="" />
				</div>
			</div>
			</fieldset>

			<fieldset>
			<legend><?php echo $ms_catalog_sellerinfo_seller_data; ?></legend>
			<div class="form-group required">
				<label class="col-sm-2 control-label required"><?php echo $ms_catalog_sellerinfo_nickname; ?></label>
				<?php if (!empty($seller['ms.nickname'])) { ?>
					<div class="col-sm-10" style="padding-top: 5px">
						<b><?php echo $seller['ms.nickname']; ?></b>
					</div>
				<?php } else { ?>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="seller[nickname]" value="<?php echo $seller['ms.nickname']; ?>" />
					</div>
				<?php } ?>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_keyword; ?></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="seller[keyword]" value="<?php echo $seller['keyword']; ?>" />
				</div>
			</div>


			<div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_description; ?></label>
				<div class="col-sm-10">
					<ul class="nav nav-tabs" id="language">
						<?php foreach ($languages as $language) { ?>
						<li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab">
								<img class="lang_image" src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?>
							</a>
						</li>
						<?php } ?>
					</ul>
					<div class="tab-content">
						<?php foreach ($languages as $language) { ?>
						<div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
							<textarea name="seller[description][<?php echo $language['language_id']; ?>][description]" id="seller_textarea<?php echo $language['language_id']; ?>" class="form-control summernote"> <?php echo $this->config->get('msconf_enable_rte') ? htmlspecialchars_decode($seller['descriptions'][$language['language_id']]['description']) : strip_tags(htmlspecialchars_decode($seller['descriptions'][$language['language_id']]['description'])); ?> </textarea>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $ms_catalog_sellerinfo_avatar; ?></label>
				<div class="col-sm-10">
					<div id="sellerinfo_avatar_files">
						<?php if (!empty($seller['avatar'])) { ?>
						<input type="hidden" name="seller[avatar_name]" value="<?php echo $seller['avatar']['name']; ?>" />
						<img src="<?php echo $seller['avatar']['thumb']; ?>" />
						<?php } ?>
					</div>
				</div>
			</div>

			<?php $msSeller = new ReflectionClass('MsSeller'); ?>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $ms_status; ?></label>
				<div class="col-sm-10">
					<select class="form-control" name="seller[status]">
					<?php foreach ($msSeller->getConstants() as $cname => $cval) { ?>
						<?php if (strpos($cname, 'STATUS_') !== FALSE) { ?>
							<option value="<?php echo $cval; ?>" <?php if ($seller['ms.seller_status'] == $cval) { ?>selected="selected"<?php } ?>><?php echo $this->language->get('ms_seller_status_' . $cval); ?></option>
						<?php } ?>
					<?php } ?>
				</select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">
					<span data-toggle="tooltip" title="<?php echo $ms_catalog_sellerinfo_notify_note; ?>"><?php echo $ms_catalog_sellerinfo_notify; ?></span>
				</label>
				<div class="col-sm-10">
					<input type="checkbox" style="margin-top: 10px" name="seller[notify]" value="1" checked="checked" /><br>
					<textarea class="form-control" name="seller[message]" placeholder="<?php echo $ms_catalog_sellerinfo_message_note; ?>"></textarea>
				</div>
			</div>
			</fieldset>

			</div>


			<!-- begin offer tab -->
			<div class="tab-pane" id="tab-offers">
				<div class="form-group required">
					<label for="offer-limit" class="col-sm-2 control-label">Offer Limit: </label>
					<div class="col-sm-10 control-inline">
						<input id="offer-limit" type="number" step="1" min="0" class="form-control" name="seller[offer_limit]" value="<?php if( isset($seller['ms.offer_limit']) ) { echo $seller['ms.offer_limit']; } else { echo 20; } ?>" size="3" required/>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-hover" style="text-align: center" id="list-offers">
						<thead>
						<tr>
							<td class="mm_size_tiny">ID</td>
							<td class="mm_size_medium">Last Update</td>
							<td class="mm_size_large">Name</td>
							<td class="mm_size_small">Total</td>
						</tr>
						<tr class="filter">
							<td><input type="text"/></td>
							<td><input type="text" class="input-date-datepicker"/></td>
							<td><input type="text"/></td>
							<td><input type="text"/></td>
						</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<!-- end offer tab -->

			</div>
		</div>
		</form>
	  </div>
	</div>
  </div>
<script type="text/javascript" src="view/javascript/summernote/summernote.js"></script>
<link href="view/javascript/summernote/summernote.css" rel="stylesheet" />
<script type="text/javascript" src="view/javascript/summernote/opencart.js"></script>
	<script type="text/javascript">
	$(function() {
		$('input[name^="customer"]').parents('div.form-group').hide();
		$('select[name="customer[customer_id]"]').bind('change', function() {
			if (this.value == '0') {
				$('input[name^="customer"]').parents('div.form-group').show();
				$('[name="seller[notify]"], [name="seller[message]"]').parents('div.form-group').hide();
			} else {
				$('input[name^="customer"]').parents('div.form-group').hide();
				$('[name="seller[notify]"], [name="seller[message]"]').parents('div.form-group').show();
			}
		}).change();

		$("#ms-submit-button").click(function() {
			var button = $(this);
			var id = $(this).attr('id');
			$.ajax({
				type: "POST",
				dataType: "json",
				url: 'index.php?route=multimerch/seller/jxsavesellerinfo&token=<?php echo $token; ?>',
				data: $('#ms-sellerinfo').serialize(),
				beforeSend: function() {
					$('div.text-danger').remove();
					$('.alert-danger').hide().find('i').text('');
				},
				complete: function(jqXHR, textStatus) {
					button.show().prev('span.wait').remove();
					$('.alert-danger').hide().find('i').text('');
				},
				error: function(jqXHR, textStatus, errorThrown) {
				   $('.alert-danger').show().find('i').text(textStatus);
				},
				success: function(jsonData) {
					if (!jQuery.isEmptyObject(jsonData.errors)) {
						for (error in jsonData.errors) {
							$('[name="'+error+'"]').after('<div class="text-danger">' + jsonData.errors[error] + '</div>');
						}
						window.scrollTo(0,0);
					} else {
						window.location = 'index.php?route=multimerch/seller&token=<?php echo $token; ?>';
					}
					}
			});
		});

	});
	</script>

<script type="text/javascript"><!--
		$('#language a:first').tab('show');
//--></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#list-offers').dataTable( {
			"sAjaxSource": "index.php?route=multimerch/offer/getTableData&token=<?php echo $token; ?>&seller_id=<?php echo $seller['seller_id']; ?>",
			"aoColumns": [
				{ "mData": "offer_id" },
				{ "mData": "date_created" },
				{ "mData": "offer_name" },
				{ "mData": "total_amount" }
			],
			"aaSorting":  [[0,'desc']]
		});

		setTimeout(function() {
			var dataTables = $('table.dataTable');
			$.map(dataTables, function(item) {
				if($(item).find('tbody tr:first td').length == 1) {
					$(item).find('tbody tr:first td').attr('colspan', '100%');
				}
			});
		}, 500);


	});

</script>

<?php echo $footer; ?>