<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
</form>
<link rel='stylesheet' href='<?php echo plugins_url('/sanalpospro/views/css/admin.css') ?>' type='text/css' media='all' />

<div class="spp_bootstrap-wrapper">
	<hr/>
	<!--  -->
	<div style="border-top: 5px solid #1161ee;" class="spp-row-panel-grid-container">
		<div>
		  <a target="_blank;" href="https://sanalpospro.com/wordpress">
			<img style="padding:5px;" src="<?php echo plugins_url() ?>/sanalpospro/img/logo.png" alt="" srcset="">
		  </a>
		</div>
	 <div>
			<p style="text-align: center;font-weight: 700;font-size: 15px;margin: 0;">v<?php echo ((float) $this->version ) ?></p>
			<a style="width: 25%;text-align: center;" class="spp-btn spp-green-btn" href="https://sanalpospro.com/wordpress">Kontrol</a></div>
		<div>
			<a href="https://eticsoft.com/">
				<img style="float:right;padding:10px;" src="<?php echo plugins_url() ?>/sanalpospro/img/eticsoft-logo-250px.png" alt="" srcset="">
			</a>
		</div>
	</div>

	<div class="row">

		<?php if ($messages): ?>
			<?php foreach ($messages as $mesg): ?>
				<div class="col-sm-6 col-xs-12">
					<div class="alert alert-<?php echo $mesg['type'] ?>-spp"><?php echo $mesg['message'] ?></div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

	</div>
	<?php if ($viewlog): ?>
		<div class="row">
			<div class="col-sm-12 col-xs-12">
				İşlem İçin Kayıt Defteri
				<pre><textarea style="height: 300px"><?php echo $viewlog ?></textarea></pre>
														</div>
													</div>
	<?php endif; ?>

	<!-- Nav tabs -->

	<!--  -->
	<ul class="tablist" role="tablist">
		<li class="tab tab-active" role="tab"><a href="#sanalposprosettings">GENEL AYARLAR</a></li>
		<li class="tab" role="tab"><a href="#pos">POS AYARLARI</a></li>
		<li class="tab" role="tab"><a href="#cards">TAKSİTLER</a></li>
		<li class="tab" role="tab"><a href="#integration">YÖNTEM EKLE</a></li>
		<li class="tab" role="tab"><a href="#help">DESTEK</a></li>
		<li class="tab" role="tab"><a href="#stats">İŞLEMLER</a></li>
		<li class="tab" role="tab"><a href="#tools">ARAÇLAR</a></li>
		<!-- <li class="tab" role="tab"><a href="#panel8">MASTERPASS</a></li> -->
	  <li class="tab-menu">
		<div class="line"></div>
		<div class="line"></div>
		<div class="line"></div>
		<div class="line"></div>
		<div class="line"></div>
		<div class="line"></div>
		<div class="line"></div>
		<!-- <div class="line"></div> -->
	  </li>
	</ul>
	<!--  -->

	<!-- Tab panes -->
	<div class="tab-content">
		<div class="tabpanel" id="sanalposprosettings"><?php echo $general_tab ?></div>
		<div class="tabpanel" id="pos"><?php echo $banks_tab ?></div>
		<div class="tabpanel" id="cards"><?php echo $cards_tab ?></div>
		<div class="tabpanel" id="integration"><?php echo $integration_tab ?></div>
		<div class="tabpanel" id="help"> <?php echo $help_tab ?> </div>
		<div class="tabpanel" id="stats"><?php echo include(dirname(__FILE__) . '/stats.php') ?></div>
		<div class="tabpanel" id="tools"><?php echo $tools_tab ?></div>
		<div class="tabpanel" id="masterpass"><?php echo $masterpass_tab ?></div>
	</div>

	<div class="panel">
		<div class="panel-header">
			<h2> EticSoft R&D Lab Teknokent Akdeniz Ünv. </h2>
		</div>
		<div class="panel-body">
			<div class="row eticsoft_garantipay-header">
				<div class="col-xs-6 col-md-4 text-center">
					<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2FEticSoft%2F&width=450&layout=standard&action=like&size=small&show_faces=true&share=true&height=80&appId=166162726739815" width="450" height="80" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>		</div>
				<div class="col-xs-6 col-md-8 text-center">
					<a href="https://www.youtube.com/user/EticSoft"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/youtube.png" target="_blank" /></a>
					<a href="https://www.linkedin.com/company/eticsoft-yaz%C4%B1l%C4%B1m"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/linkedin.png" target="_blank" /></a>
					<a href="https://twitter.com/eticsoft"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/twitter.png" target="_blank" /></a>
					<a href="https://www.instagram.com/eticsoft/"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/instagram.png" target="_blank" /></a>
					<a href="https://wordpress.org/support/users/eticsoft-lab/"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/wordpress.png" target="_blank" /></a>
					<a href="https://github.com/eticsoft/"><img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/github.png" target="_blank" /></a>
				</div>
			</div>
		</div>
	</div>
</div>
<form style="display:none">