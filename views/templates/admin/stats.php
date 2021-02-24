<?php echo $last_records->display(); ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<div class="row panel" style="min-height:400px">
	<div class="col-sm-6">
		<?php echo $stats_monthly ?>
	</div>
	<div class="col-sm-6">
		<?php echo $stats_gateways ?>
	</div>
</div>
<?php $transactions = EticSql::getRows('spr_transaction'); ?>
<div class="table-responsive">
	<table class="table table-striped table-sm ">
	  <thead>
		<tr>
		  <th scope="col">ID</th>
		  <th scope="col">CC SAHİBİ</th>
		  <th scope="col">CC NUMARASI</th>
		  <th scope="col">SEPET ID</th>
		  <th scope="col">PARA BİRİMİ</th>
		  <th scope="col">SEPET TOPLAMI</th>
		  <th scope="col">TAKSİT</th>
		  <th scope="col">IP ADRESİ</th>
		  <th scope="col">DURUMU</th>
		  <th scope="col">TARİHİ</th>
		</tr>
	  </thead>
	  <tbody>
	  <?php foreach($transactions as $key => $value): ?>
		<tr>
		  <th scope="row"><?php echo $value["id_transaction"] ?></th>
		  <th scope="row"><?php echo $value["cc_name"] ?></th>
		  <th scope="row"><?php echo $value["cc_number"] ?></th>
		  <th scope="row"><?php echo $value["id_cart"] ?></th>
		  <th scope="row"><?php echo $value["id_currency"] ?></th>
		  <th scope="row"><?php echo $value["total_cart"] ?></th>
		  <th scope="row"><?php echo $value["installment"] ?></th>
		  <th scope="row"><?php echo $value["cip"] ?></th>
		  <th scope="row"><?php echo $value["result_message"] ?></th>
		  <th scope="row"><?php echo $value["date_update"] ?></th>
		</tr>
		<?php endforeach; ?>
	  </tbody>
	</table>
  </div>