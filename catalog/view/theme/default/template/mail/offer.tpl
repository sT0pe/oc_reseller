<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo 'title'; ?></title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div class="page">

  <h3><?php echo $offer_info['offer_name']; ?> #<?php echo $offer_info['offer_id']; ?></h3>
  <hr/>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
    <tr>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2"><?php echo $ms_offer_details; ?></td>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b><?php echo $ms_mail_offer_id; ?> </b> <?php echo $offer_info['offer_id']; ?><br />
        <b><?php echo $ms_offer_date_start; ?> </b><?php echo $offer_info['date_start']; ?><br />
        <b><?php echo $ms_offer_date_end; ?> </b><?php echo $offer_info['date_end']; ?><br />
      </td>
    </tbody>
  </table>

  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
    <tr>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;"><?php echo $ms_issued_by; ?></td>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;"><?php echo $ms_offer_for; ?></td>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $offer_info['offer_by_name']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $offer_info['offer_for_name']; ?></td>
    </tr>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $ms_offer_phone; ?><?php echo $offer_info['offer_by_phone']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $ms_offer_phone; ?><?php echo $offer_info['offer_for_phone']; ?></td>
    </tr>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $ms_offer_email; ?><?php echo $offer_info['offer_by_email']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $ms_offer_email; ?><?php echo $offer_info['offer_for_email']; ?></td>
    </tr>
    <?php if( !empty($offer_info['offer_by_company']) || !empty($offer_info['offer_for_company']) ) { ?>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_by_company']) ) { echo $ms_offer_company; echo $offer_info['offer_by_company']; } ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_for_company']) ) { echo $ms_offer_company; echo $offer_info['offer_for_company']; } ?></td>
    </tr>
    <?php } ?>
    <?php if( !empty($offer_info['offer_by_nip']) || !empty($offer_info['offer_for_nip']) ) { ?>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_by_nip']) ) { echo $ms_offer_nip; echo $offer_info['offer_by_nip']; } ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_for_nip']) ) { echo $ms_offer_nip; ?><?php echo $offer_info['offer_for_nip']; } ?></td>
    </tr>
    <?php } ?>
    <?php if( !empty($offer_info['offer_by_address']) || !empty($offer_info['offer_for_address']) ) { ?>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_by_address']) ) { echo $ms_offer_address; ?><?php echo $offer_info['offer_by_address']; } ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php if( !empty($offer_info['offer_for_address']) ) { echo $ms_offer_address; ?><?php echo $offer_info['offer_for_address']; } ?></td>
    </tr>
    <?php } ?>
    </tbody>
  </table>
  <hr/>

  <?php if(isset($products)){ ?>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
    <tr>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;"><?php echo $ms_column_product; ?></td>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;"><?php echo $ms_model_column; ?></td>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $ms_quantity_column; ?></td>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $ms_column_price; ?></td>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $ms_total_column; ?></td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $product) { ?>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $product['name']; ?>
        <?php foreach ($product['option'] as $option) { ?>
        <br />
        &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
        <?php } ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $product['model']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $product['quantity']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $product['price_brutto']; ?></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $product['total']; ?></td>
    </tr>
    <?php } ?>
    </tbody>
    <tfoot>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;" colspan="4"><b><?php echo $ms_total_service; ?></b></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $total_client_brutto; ?></td>
    </tr>
    </tfoot>
  </table>
  <?php } ?>

  <?php if(isset($services) && !empty($services) ) { ?>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
    <tr>
      <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2"><b><?php echo $ms_tab_services; ?></b></td>
    </tr>
    <tr>
      <th><?php echo $ms_service_text; ?></th>
      <th><?php echo $ms_column_price; ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($services as $service) { ?>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $service['text']; ?></td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $service['price']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
    <tfoot>
    <tr>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b><?php echo $ms_total_service; ?></b></td>
      <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><b><?php echo $total_services['text']; ?></b></td>
    </tr>
    </tfoot>
  </table>
  <?php } ?>

  <h2 style="text-align: right;"><b><?php echo $ms_total_price; ?><?php echo $total_price; ?></b></h2>

  <p style="margin-top: 0px; margin-bottom: 20px;"><?php echo $ms_reply_to_mail; ?></p>
</div>
</body>
</html>
