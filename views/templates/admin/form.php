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
<link rel='stylesheet' href='<?php echo plugins_url('/sanalpospro/views/css/bootstrap.min.css') ?>' type='text/css' media='all' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />

<div id="spp_management" class="container border bg-white m-auto rounded p-0">
	<div class="container row  m-auto rounded my-2">
		<div class="col-md-4 text-left">
			<a href="https://sanalpospro.com/wordpress" target="_blank;">
				<img src="<?php echo plugins_url() ?>/sanalpospro/img/logo.png"/> 
			</a>
		</div>
		<div class="col-md-4 text-center">
			<span>v<?php echo ((float) $this->version ) ?></span>
			<br/><a class="text-decoration-none text-danger" href="https://sanalpospro.com/wordpress" target="_blank;">Kontrol</a>
		</div>
		<div class="col-md-4" style="text-align:right;">
			<img src="<?php echo plugins_url() ?>/sanalpospro/img/eticsoft-logo-250px.png"/>
			<br/><a class="text-decoration-none text-dark" href="https://eticsoft.com" target="_blank;">Wordpress SanalPOS PRO! &copy; 2008 </a>
		</div>
	</div>
	<div class="row">

		<?php if ($messages): ?>
			<div class="col-md-12 d-flex my-1">
			<?php foreach ($messages as $mesg): ?>
				<div class="d-flex p-3 justify-content-center align-items-center spp-<?php echo $mesg['type'] ?> rounded m-auto">
						<i class="fas fa-exclamation-circle"></i>
						<p class="m-auto mx-3"><?php echo $mesg['message'] ?></p>
				</div>
			<?php endforeach; ?>
			</div>
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
	<span class="row" style="border-bottom: 1px solid #e0e0e0;"></span>
<div class="d-flex">
	<!-- Nav tabs -->
		<ul id="sppTab" class="nav justify-content-between p-2 d-block col-md-2">
			<li onclick="openSppTab('sanalposprosettings')" class="rounded tablink spp-tab-active"><i class="fas fa-cog"></i> Genel Ayarlar</li>
			<li onclick="openSppTab('pos')" class="rounded tablink"><i class="far fa-credit-card"></i> POS Ayarları</li>
			<li onclick="openSppTab('cards')" class="rounded tablink"><i class="far fa-calendar-alt"></i> Taksitler</li>
			<li onclick="openSppTab('integration')" class="rounded tablink"><i class="fas fa-cart-plus"></i> Yöntem Ekle</li>
			<li onclick="openSppTab('help')" class="rounded tablink"><i class="far fa-life-ring"></i> Destek</li>
			<li onclick="openSppTab('stats')" class="rounded tablink"><i class="fas fa-exchange-alt"></i> İşlemler</li>
			<li onclick="openSppTab('tools')" class="rounded tablink"><i class="fas fa-th-large"></i> Araçlar</li>
			<li onclick="openSppTab('campaign')" class="rounded tablink"><i class="fas fa-ticket-alt"></i> Kampanyalar</li>
		</ul>
	

	<!-- Tab panes -->
	<div class="tab-content col-md-10">
		<div class="spp_tab" active id="sanalposprosettings"><?php echo $general_tab ?></div>
		<div class="spp_tab" style="display:none;" id="pos"><?php echo $banks_tab ?></div>
		<div class="spp_tab" style="display:none;" id="cards"><?php echo $cards_tab ?></div>
		<div class="spp_tab" style="display:none;" id="integration"><?php echo $integration_tab ?></div>
		<div class="spp_tab" style="display:none;" id="help"> <?php echo $help_tab ?> </div>
		<div class="spp_tab" style="display:none;" id="stats"><?php echo include(dirname(__FILE__) . '/stats.php') ?></div>
		<div class="spp_tab" style="display:none;" id="tools"><?php echo $tools_tab ?></div>
		<div class="spp_tab" style="display:none;" id="campaign"><?php echo $campaign_tab ?></div>
	</div>
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