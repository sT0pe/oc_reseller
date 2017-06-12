<?php echo $header; ?>
<div class="container">

	<?php if (isset($success) && $success) { ?>
	<div class="alert alert-success success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button></div>
	<?php } ?>

	<?php if (isset($error) && $error) { ?>
	<div class="alert alert-danger warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	<?php } ?>

	<div class="row"><?php echo $column_left; ?>
		<?php if ($column_left && $column_right) { ?>
		<?php $class = 'col-sm-6'; ?>
		<?php } elseif ($column_left || $column_right) { ?>
		<?php $class = 'col-sm-9'; ?>
		<?php } else { ?>
		<?php $class = 'col-sm-12'; ?>
		<?php } ?>
		<div id="content" class="<?php echo $class; ?> ms-account-dashboard"><?php echo $content_top; ?>
			<div class="mm_dashboard">
				<h1><?php echo $ms_account_offers_heading; ?></h1>
				<a class="btn btn-primary" href="<?php echo $offer; ?>"><?php echo $ms_btn_new_offer; ?></a>
				<div class="table-responsive">
					<table class="mm_dashboard_table table table-borderless table-hover" id="mm_offers">
						<thead>
						<tr>
							<td class="mm_size_tiny">ID:</td>
							<td class="mm_size_medium"><?php echo $ms_date_created; ?></td>
							<td class="mm_size_large"><?php echo $ms_account_offers_caption; ?></td>
							<td><?php echo $ms_account_orders_products; ?></td>
							<td class="mm_size_small"><?php echo $ms_account_orders_total; ?></td>
							<td class="mm_size_small"></td>
						</tr>
						<tr class="filter">
							<td><input type="text"/></td>
							<td><input type="text" class="input-date-datepicker"/></td>
							<td><input type="text"/></td>
							<td></td>
							<td><input type="text"/></td>
							<td></td>
						</tr>
						</thead>

						<tbody></tbody>
					</table>
				</div>
			</div>
			<?php echo $content_bottom; ?>
		</div>
		<?php echo $column_right; ?>
	</div>
</div>

<script>
	$(function() {
		$('#mm_offers').dataTable( {
			"sAjaxSource": $('base').attr('href') + "index.php?route=seller/account-offer/getTableData",
			"aoColumns": [
				{ "mData": "offer_id" },
				{ "mData": "date_created" },
				{ "mData": "offer_name" },
				{ "mData": "products", "bSortable": false, "sClass": "products" },
				{ "mData": "total_amount" },
                { "mData": "action_offer", "bSortable": false, "sClass": "action" }
			],
			"aaSorting":  [[1,'desc']]
		});

        $(document).on('click', '.btn-delete-offer', function() {
            return confirm("<?php echo $ms_delete_offer; ?>");
        });
	});

</script>
<?php echo $footer; ?>