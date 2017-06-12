<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_TEMPLATE; ?>default/stylesheet/offer_pdf.css">
</head>
<body>

<div class="page">

  <h3><?php echo $offer_info['offer_name']; ?> (<?php echo $offer_info['date_start']; ?>)</h3>
  <hr/>
  <div class="container offer-info">
    <table>
      <tr>
        <td><?php echo $data['offer_date_start']; ?></td>
        <td><?php echo $offer_info['date_start']; ?></td>
      </tr>
      <tr>
        <td><?php echo $data['offer_date_end']; ?></td>
        <td><?php echo $offer_info['date_end']; ?></td>
      </tr>
    </table>
  </div>

  <div class="container information">
    <div class="col-6">
      <table id="offer-by">
        <tr>
          <th><h3><?php echo $data['text_issued_by']; ?></h3></th>
          <th><img src="<?php echo $offer_info['offer_by_image_thumb']; ?>"/></th>
        </tr>
        <tr>
          <td colspan="2"><?php echo $offer_info['offer_by_name']; ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_phone']; ?> <?php echo $offer_info['offer_by_phone']; ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_email']; ?> <?php echo $offer_info['offer_by_email']; ?></td>
        </tr>
        <?php if( !empty($offer_info['offer_by_company']) ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_company']; ?> <?php echo $offer_info['offer_by_company']; ?></td>
        </tr>
        <?php } ?>
        <?php if( $offer_info['offer_by_nip'] != 0 ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['text_offer_nip']; ?> <?php echo $offer_info['offer_by_nip']; ?></td>
        </tr>
        <?php } ?>
        <?php if( !empty($offer_info['offer_by_address']) ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_address']; ?> <?php echo $offer_info['offer_by_address']; ?></td>
        </tr>
        <?php } ?>
      </table>
    </div>

    <div class="col-6">
      <table id="offer-for">
        <tr>
          <th><h3><?php echo $data['text_offer_for']; ?></h3></th>
          <th><img src="<?php echo $offer_info['offer_for_image_thumb']; ?>"/></th>
        </tr>
        <tr>
          <td colspan="2"><?php echo $offer_info['offer_for_name']; ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_phone']; ?> <?php echo $offer_info['offer_for_phone']; ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_email']; ?> <?php echo $offer_info['offer_for_email']; ?></td>
        </tr>
        <?php if( !empty($offer_info['offer_for_company']) ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_company']; ?> <?php echo $offer_info['offer_for_company']; ?></td>
        </tr>
        <?php } ?>
        <?php if( $offer_info['offer_for_nip'] != 0 ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['text_offer_nip']; ?> <?php echo $offer_info['offer_for_nip']; ?></td>
        </tr>
        <?php } ?>
        <?php if( !empty($offer_info['offer_for_address']) ) { ?>
        <tr>
          <td colspan="2"><?php echo $data['ms_offer_address']; ?> <?php echo $offer_info['offer_for_address']; ?></td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>
<hr/>

  <div class="container">
    <h3><?php echo $data['ms_tab_products']; ?></h3>
    <table id="products">
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo $data['column_image']; ?></th>
          <th><?php echo $data['column_name']; ?></th>
          <th><?php echo $data['column_model']; ?></th>
          <th><?php echo $data['ms_price_netto']; ?></th>
          <th><?php echo $data['ms_column_tax']; ?></th>
          <th><?php echo $data['ms_price_brutto']; ?></th>
          <th><?php echo $data['ms_quantity_pdf']; ?></th>
          <th><?php echo $data['column_total']; ?></th>
        </tr>
      </thead>
      <tbody>
      <?php $i=1; foreach($result as $product) { ?>
        <tr>
          <td><?php echo $i; ?></td>
          <td><img src="<?php echo $product['thumb']?>" /></td>
          <td><?php echo $product['name']; ?></td>
          <td><?php echo $product['model']; ?></td>
          <td><?php echo $product['price_netto']; ?></td>
          <td style="text-align: center;"><?php echo $product['tax']; ?></td>
          <td><?php echo $product['price_brutto']; ?></td>
          <td style="text-align: center;"><?php echo $product['quantity']; ?></td>
          <td><?php echo $product['total']; ?></td>
        </tr>
      <?php $i++; } ?>
      </tbody>
    </table>
  </div>

  <div class="container total">
    <table id="total">
      <tr>
        <td><?php echo $data['ms_total_netto']; ?></td>
        <td style="text-align: right;"><?php echo $total_client_netto; ?></td>
      </tr>
      <tr style="text-align: left;">
        <td><?php echo $data['ms_total_brutto']; ?></td>
        <td style="text-align: right;"><strong><?php echo $total_client_brutto; ?></strong></td>
      </tr>
    </table>
  </div>

  <?php if(isset($offer_info['services']) && !empty($offer_info['services']) ) { ?>
  <div class="container services">
    <h2><?php echo $data['ms_tab_services']; ?></h2>
    <table id="services">
      <thead>
        <tr>
          <th><?php echo $data['ms_service_text']; ?></th>
          <th><?php echo $data['ms_service_price']; ?></th>
          <th><?php echo $data['ms_column_tax']; ?></th>
          <th><?php echo $data['ms_service_final']; ?></th>
        </tr>
      </thead>
      <?php foreach($offer_info['services'] as $service) { ?>
      <tr>
        <td><?php echo $service['text']; ?></td>
        <td><?php echo $service['price']; ?></td>
        <td style="text-align: center"><?php echo $service['tax']; ?></td>
        <td style="text-align: right"><?php echo $service['final_price']; ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td colspan="2"></td>
        <td><strong><?php echo $data['ms_total_service']; ?></strong></td>
        <td style="text-align: right;"><strong><?php echo $total_services['text']; ?></strong></td>
      </tr>
    </table>
  </div>
  <?php } ?>
<hr/>
    <h3 class="total-price"><?php echo $data['ms_total_price']; ?><?php echo $total_price; ?></h3>
</div>

</body>
</html>