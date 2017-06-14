<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

	<div class="page-header">
		<div class="container-fluid">
			<h1><?php echo $ms_account_offers; ?></h1>
			<div class="pull-right">
				<button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" id="delete-conversation"><i class="fa fa-trash-o"></i></button>
			</div>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>

	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<?php if (isset($success) && $success) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $ms_account_offers; ?></h3>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover" style="text-align: center" id="list-offers">
						<thead>
						<tr>
							<td class="mm_size_tiny"><?php echo $ms_offer_id; ?></td>
							<td class="mm_size_medium"><?php echo $ms_offer_update; ?></td>
							<td class="mm_size_large"><?php echo $ms_offer_name; ?></td>
							<td class="mm_size_small"><?php echo $ms_offer_total; ?></td>
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
		</div>

	</div>
</div>

<script type="text/javascript">
    $(function() {
        $('#list-offers').dataTable( {
            "sAjaxSource": "index.php?route=multimerch/offer/getTableData&token=<?php echo $token; ?>",
            "aoColumns": [
                { "mData": "offer_id" },
                { "mData": "date_created" },
                { "mData": "offer_name" },
                { "mData": "total_amount" }
            ],
            "aaSorting":  [[0,'desc']]
        });

    });

</script>
<?php echo $footer; ?>