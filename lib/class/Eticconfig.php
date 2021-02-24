<?php
if(substr(get_locale(), 0,2) == 'tr'){
    include_once (dirname(__FILE__).'/../lang/tr.php');
}else{
    include_once (dirname(__FILE__).'/../lang/en.php');
}
Class EticConfig
{

	public static $order_themes = array(
		'pro' => 'PRO!tema (Önerilir)',
		// '3d' => 'Üç boyutlu JS tema (Seksi)',
		// 'cr' => 'Kredity Form (Resmi)',
		// 'st' => 'Basit standart form '
	);
	public static $installment_themes = array(
		'color' => 'Renkli (Önerilir)',
		// 'simple' => 'Basit (Renksiz)',
		// 'white' => 'Beyaz (Resmi)',
		// 'colorize' => 'Colorize (Seksi) '
	);
	public static $families = array(
		'axess', 'bonus', 'maximum', 'cardfinans', 'world', 'paraf', 'advantage', 'combo', 'miles-smiles'
	);
	public static $messages = array();
	public static $gateways;

	public static function get($key)
	{
		return get_option($key);
	}

	public static function set($key, $value)
	{
		return update_option($key, $value);
	}

	public static function getAdminGeneralSettingsForm($module_dir)
	{
		$t = '<form action="" method="post" id="general_settings_form" >
		<div class="panel">
		<div class="row mt-3 m-auto">
		<div class="col-md-8"> <!-- required for floating -->
		<div class="d-flex py-3 justify-content-center align-items-center spp-info rounded">
		<i class="fas fa-info-circle"></i>
		<p class="m-auto mx-3">Modülün görünümü ve temel fonksyionlarını bu panelden değiştirebilirsiniz. Pos ayarları için buraya tıklayınız.</p>
		</div>
		
		</div>
		<div class="col-md-4 w-auto m-auto" style="margin-top:0px !important;">
		<a class="btn bgred text-light"><i class="far fa-life-ring"></i> Yardım</a> 
		<button type="submit" name="submitspr" class="btn bggreen text-light"><i class="far fa-save"></i> Ayarları Kaydet</button>
		</div>
		</div>';

		$t .= '
		<h4 class="text-center my-3">GENEL AYARLAR</h4>
		<div class="row m-auto justify-content-around">';
// Enable Disable
		$woo_settings = get_option("woocommerce_sanalpospro_settings");

		$t .= '
		<div id="spp_eklenti_aktif" class="col-md-2 text-center sppbox rounded ' . ($woo_settings['enabled'] == 'yes' ? 'bggreen ' : 'bgred') . '">
		<h2>Eklenti Aktif ?</h2>
		<select class="form-control" name="WOO_POSPRO_SETTINGS[enabled]">
		<option value="yes"> Aktif </option>
		<option value="no" ' . ($woo_settings['enabled'] == 'no' ? 'SELECTED ' : '') . '> Pasif </option>
		</select>
		<p>Eklentiyi aktifleştir.</p>
		</div>';

// Hata Kayıt Modu
		$t .= '
		<div class="col-md-2 text-center sppbox ' . (EticConfig::get("POSPRO_DEBUG_MOD") == 'on' ? 'bgred ' : 'rounded text-center') . '">
		<h2>İşlem Kayıt</h2>
		<select class="form-control" name="spr_config[POSPRO_DEBUG_MOD]">
		<option value="on" ' . (EticConfig::get("POSPRO_DEBUG_MOD") == 'on' ? 'SELECTED ' : '') . '> Açık </option>
		<option value="off" ' . (EticConfig::get("POSPRO_DEBUG_MOD") == 'off' ? 'SELECTED ' : '') . '> Kapalı </option>
		</select>
		<p>Tüm işlemleri kaydeder.</p>
		</div>';

//3D Auto Form
		$t .= '
		<div class="col-md-2 text-center sppbox rounded text-center">
		<h2>3DS Otomatik Yönlendirme</h2>
		<select class="form-control" name="spr_config[POSPRO_ORDER_AUTOFORM]">
		<option value="on" > Açık (önerilir)</option>
		<option value="off" ' . (EticConfig::get("POSPRO_ORDER_AUTOFORM") == 'off' ? 'SELECTED ' : '') . ' > Kapalı </option>
		</select>
		<p>3DS formlarını otomatik yönlendir.</p>
		</div>';
// currency
		$t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		<h2>Oto TL Çevirimi</h2>
		<select class="form-control" name="spr_config[POSPRO_AUTO_CURRENCY]">
		<option value="on"> Açık (önerilir)</option>
		<option value="off" ' . (EticConfig::get("POSPRO_AUTO_CURRENCY") == 'off' ? 'SELECTED ' : '') . '> Kapalı </option>
		</select>
		<p>Döviz ödemelerini TL ye çevirir</p>
		</div>';
		$t .= '</div>'; //row
// Açıklama
		/*  $t .= '<div class="col-md-6 text-center sppbox bgred">
		  <h2>Önemli</h2>
		  Posların çoğu USD ve EUR codelarındaki ödemeleri kabul eder ve EticSoft SanalPos PRO! tüm kurları destekler.
		  Fakat sanal POS hizmetinizin banka tanımlarında eksiklik ve hatalar olması gibi durumlarda hatalı veya başarısız ödemelerle karşılaşabilirsiniz.<br/>
		  <b>Para birimlerinin ISO ve Numerik codelarını doğru girmeniz oldukça önemlidir. Örneğin Türk lisasının ISO codeu "TRY" dir.</b>
		  <br/>
		  <br/>
		  </div>
		  </div>';
		 */

		  $t .= '<h4 class="text-center my-3">TAKSİT AYARLARI</h4>
		  <div class="row m-auto justify-content-around">';
		  $default_rate = EticInstallment::getDefaultRate();
		  $gwas = EticGateway::getGateways(true);

		  if ($gwas) {

		  	$t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		  	<h2>Min Taksit Tutarı</h2>
			  <input name="spr_config[POSPRO_MIN_INST_AMOUNT]" size="4" class="form-control" value="' . (float) Eticconfig::get('POSPRO_MIN_INST_AMOUNT') . '" type="text"/>
			  <p>Taksit seçeneğinin aylık tutarı en az bu kadar olmalıdır. (TL)</p>
		  	</div>';


		  	$t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		  	<h2>Varsayılan POS</h2>';
		  	$t .= '<select name="spr_config_default_gateway" class="form-control">';
		  	foreach ($gwas as $gw)
		  		$t .= '<option value="' . $gw->name . '" ' . ($default_rate['gateway'] == $gw->name ? ' selected ' : '') . '>' . $gw->full_name . '</option>';
		  	$t .= '</select>'
		  	. '<p>Taksit yapılamayan kartlar için bu POS\'u kullan.</p>
		  	
		  	</div>';

		  	$t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		  	<h2>Tek Çekim Komisyonu </h2>
			  <input name="spr_config_default_rate" class="form-control" size="4" value="' . (float) $default_rate['rate'] . '" type="number" step="0.01"/>
			  <p>Varsayılan POS kullanıldığı zaman müşteriye yansıtılacak yüzde.</p>
		  	
		  	</div>';

		  	$t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		  	<h2>Tek Çekim Maliyeti </h2>
			  <input name="spr_config_default_fee" size="4" class="form-control" value="' . (float) $default_rate['fee'] . '" type="number" step="0.1"/>
			  <p>Varsayılan POS kullanıldığı zaman sizden kesilecek yüzde </p>
		  	
		  	</div>';
		  } else {
		  	$t .= '<div class="alert alert-danger">Kurulu POS hizetiniz bulunamadı. Lütfen önce bir POS kurulumunu yapınız</div>';
		  }

		  $t .= '</div>';



		  $t .= '<h4 class="text-center my-3">GÖRÜNÜM AYARLARI</h4>
		  <div class="row m-auto justify-content-around">';

// taksitleri göster
		  $t .= '
		  <div class="col-md-2 text-center sppbox rounded text-center">
		  <h2>Taksitler Sekmesi</h2>
		  <select name="spr_config[POSPRO_TAKSIT_GOSTER]" class="form-control">
		  <option value="on"> Göster </option>
		  <option value="off" ' . (EticConfig::get("POSPRO_TAKSIT_GOSTER") == 'off' ? 'SELECTED ' : '') . ' > Gizle </option>
		  </select>
		  <p>Ürün sayfasının altında bulunan taksit seçenekleri.</p>
		  
		  </div>';

// PDF göster
		/* $t .= '
		  <div class="col-md-2 text-center sppbox rounded text-center">
		  <h2>PDF Yerleşimi</h2>
		  <select name="spr_config[POSPRO_HOOK_PDF]" class="form-control">
		  <option value="Goster"> Göster </option>
		  <option value="Gizle" ' . (EticConfig::get("POSPRO_HOOK_PDF") == 'off' ? 'SELECTED ' : '') . ' > Gizle </option>
		  </select>
		  <br/>
		  PDF faturaya kredi kartı işlem bilgileri (silip) eklensin mi ?.<br/>
		  
		  </div>';
		 * 
		 */

// ödeme tema
		  $t .= '
		  <div class="col-md-2 text-center sppbox rounded text-center">
		  <h2>Ödeme Ekranı Teması</h2>
		  <p>Ödeme Formu Arka Planı<p>
		  <input style="padding:unset !important;" type="color" id="head" name="spr_config[POSPRO_PAYMENT_PAGE_BGCOLOR]" value='.EticConfig::get("POSPRO_PAYMENT_PAGE_BGCOLOR").'>
		  <p>Ödeme Formu Butonu<p>
		  <input style="padding:unset !important;" type="color" id="head" name="spr_config[POSPRO_PAYMENT_PAGE_BUTTON_COLOR]" value='.EticConfig::get("POSPRO_PAYMENT_PAGE_BUTTON_COLOR").'>
		  </div>';
// taksit tema
		  $t .= '<div class="col-md-2 text-center sppbox rounded text-center">
		  <h2>Taksitler Tema</h2>
		  <select name="spr_config[POSPRO_PRODUCT_TMP]" class="form-control">';
		  foreach (EticConfig::$installment_themes as $k => $v):
		  	$t .= '<option value="' . $k . '" ' . (EticConfig::get("POSPRO_PRODUCT_TMP") == $k ? 'SELECTED ' : '') . '>' . $v . '</option>';
		  endforeach;
		  $t .= '</select>
		  <p>Taksit seçenekleri sekmesinin görünümü.</p>
		  </div>
		  </div><hr/>';
		  
		  $t .= '
		  <div class="d-flex py-3 justify-content-center align-items-center spp-info rounded">
		  <i class="fas fa-info-circle"></i>
		  <p class="m-auto mx-3">SanalPOS PRO! tüm para birimlerini destekler. 
		  Fakat yabancı kurlarda ödeme alabilmeniz için hizmet aldığınız POS altyapısının da desteklemesi gerekir.</p>
		  </div> 
		  ';


		  $t .= '<input name="conf-form" type="hidden" value="1" />
		  </div>
		  ' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' 
		  </form>';
		  return $t;
		}


		public static function getAdminGatewaySettingsForm($module_dir)
		{
			$gateways = EticGateway::$gateways;
			if (!EticGateway::getGateways()) {
				return '<div class="panel text-center"> <i style="font-size:60px" class="process-icon-cancel"></i><br/> '
				. '<h1> Henüz hiç bir Sanal POS hizmeti kurulmamış !</h1>'
				. '<p>SanalPOS hizmeti aldığınız banka veya ödeme kuruluşlarının hizmetlerini '
				. '<a role="tab" data-toggle="tab" href="#integration"> Ödeme Yöntemleri </a> sekmesinden kurabilirsiniz.</p>'
				. ''
				. '</div>';
			}
			$t = '<form action="#pos" method="post" id="bank_settings_form" class="sppform ">
			<div class="panel container">
			<div class="row">
			<div style="text-align: right;" class="text-right mt-3"> 
			<a class="btn bgred text-light"><i class="far fa-life-ring"></i> Yardım</a> 
			<button type="submit" name="submit" class="btn bggreen text-light"><i class="far fa-save"></i> Tümünü Kaydet</button>
			<input type="hidden" name="submitgwsetting" value="1"/>
			</div>
			<div class="col-md-12 my-2 text-center"> <!-- required for floating -->
				<h4>POS AYARLARI</h4>
				</div>
			</div>
			<div class="row">
			<div class="col-md-12"> <!-- required for floating -->
			<!-- Nav tabs -->
			<ul class="nav nav-pills nav-stacked">
			';
			$satir = 0;
			foreach (EticGateway::getGateways(false) as $gwbutton):
				$t .= '
				<a class="' . ($satir == 0 ? 'spp-tab-active' : '' ) . ' pos_tab_link" onclick=openSppTab("'.$gwbutton->name.'","pos_settings_content","pos_tab_link")>
				<img src="' . plugins_url() . '/sanalpospro/img/gateways/' . $gwbutton->name . '.png" width="125px"/>
				</a>';
				$satir++;
			endforeach;

			$t .= '</ul>
			</div>
			<!-- Tab panes -->';
			$satir = 0;
			foreach (EticGateway::getGateways(false) as $gwd):
				if ($gw = New EticGateway($gwd->name)):

					$gwe = EticGateway::$gateways->{$gw->name};
					if (!isset($gwe->families)) {
						continue;
					}


					if (!isset($gwe->paid) OR ! $gwe->paid) {
						Etictools::rwm($gwe->full_name . ' POS lisansınız aktif değil. Bu sanalPOS kullanılamaz.'
							. '<br/>Lütfen eticsoft ile iletişime geçiniz.');
					}
				//print_r(EticGateway::$gateways); exit;
					$t .= '<!-- BANKA -->

					<div  class="tab-pane com-md-10 pos_settings_content" id="'. $gw->name . '">
					<div class="d-flex p-3 mt-3" style="background: #f1f1f1;">
					<div class="col-md-7 rounded text-center">
					<input name="pdata[' . $gw->name . '][id_bank]" type="hidden" value="' . $gw->name . '" />
					<h4>' . $gw->full_name . ' Pos Ayarları </h4>';
					
					$t .= '<h5>Parametreler</h5>' . $gw->createFrom();

					$t .= '<div class="row w-50 m-auto">
					<button type="submit" value="' . $gw->name . '" name="submit_for" class="btn bggreen text-light my-1"><i class="far fa-save"></i> Ayarları Kaydet</button>
					<a class="btn bgred text-light my-1"><i class="far fa-life-ring"></i> Yardım</a> 
					</div>
					</div>
					<div class="col-md-5 rounded text-center text-center d-flex flex-column">
					<h2 class="m-auto"><img src="' . plugins_url() . '/sanalpospro/img/gateways/' . $gw->name . '.png"/></h2>';

					if (json_decode($gwe->families)):
						$t .= '<div class="m-auto"> <span>Taksit yapabildiği kartlar:</span><br/>';
						foreach (json_decode($gwe->families) as $family):
							$t .= '<img class="img-thumbnail" width="90px" src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png"/>';
						endforeach;
						$t .= '</div>';
					endif;

						if (isset($gw->params->test_mode) && $gw->params->test_mode == 'on'):

							$t .= Etictools::displayError($gw->full_name."test modunda çalışıyor.");
						endif;
						$t .= '<a data-toggle="collapse" class="btn bgred text-light class="m-auto" data-target="#' . $gw->name . '_remove">
							<i class="far fa-trash-alt"></i> Bu POS\'u Kaldır</a>
							
							
							<div id="' . $gw->name . '_remove" class="collapse">
							<div class="panel">
							'.Etictools::displayError("Dikkat! POS silme işlemi geri alınamaz. Sildiğiniz POS\'u daha sonra yeniden kurabilirsiniz. Fakat".
							$gw->full_name ."için girdiğiniz kullanıcı bilgileri ve oranlar da silinecektir.").'
							<div class="toggle"><button type="submit" class="btn btn-danger" name="remove_pos" value="' . $gw->name . '">Kaldır</button></div>
							</div>
							</div></div></div>';
					endif;

					if (EticTools::getValue('adv')):
						$t .= '<div class="col-sm-6">
						<input name="' . $gw->name . '[lib]" value="' . $gw->lib . '"/>
						</div>';
					endif;


					$t .= '</div>';
					$satir++;
			endforeach;
			$t .= '
			</div>
			</div>
			<input name="bank-form" type="hidden" value="1" />
			' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' 
			</form>';
			return $t;
		}

		public static function getAdminIntegrationForm()
		{

			$t = '
			<div class="panel">
			<div class="row">';
			$exists_gws = array();


			foreach (EticGateway::getGateways() as $gateway)
				$exists_gws [] = $gateway->name;



			$t .= '
			<div class ="col-md-12 my-2 text-center">
			<h4>Kullanmak İstediğiniz POS servisini seçiniz</h4>
			EticSoft sadece BDDK lisanslı ve <b>güvenilir</b> ödeme kuruluşları ve bankalar ile entegrasyon sağlıyor.
			Kullanmak istediğiniz ödeme sistemi aşağıda yoksa, ilgili ödeme şirketi/banka standartlara 
			uygun bulunmamış veya bizimle hiç iletişime geçmemiş olabilir. 
			<hr/>
			<div class="d-flex py-3 justify-content-center align-items-center spp-info rounded w-75 m-auto">
								<i class="fas fa-info-circle"></i>
								<p class="m-auto mx-3">Tüm bankaları ve ödeme sistemlerini birlikte çalışacak şekilde (hibrit) kullanabilirsiniz.
								Örnek: Tüm kartların tek çekimlerini Xbankası üzerinden, 5 taksitli ödemeleri Ypay ödeme 
								kuruluşu üzerinden, ABC kartının 2 taksitli ödemelerini Zpara ödeme kuruluşu üzerinden tahsil edebilirsiniz.						
								Kart türlerine ödeme yönetiminin nasıl çalışacağını seçmek için 
								<a href="#cards" role="tab" data-toggle="tab">Taksitler</a> tıklayınız.</p>
							</div>
			</div>
			';
			$t .='<div class="row justify-content-center">';
			foreach (EticGateway::$gateways as $k => $gw) {
				$gw->is_bank = isset($gw->is_bank) && $gw->is_bank ? true : false;
				$gw->lib = isset($gw->lib) && $gw->lib ? $gw->lib : $k;
				$gw->eticsoft = isset($gw->eticsoft) && $gw->eticsoft ? true : false;

				$t .= '<div class="text-center rounded text-light bg-blue-linear m-3 w-25 position-relative spph300 d-flex">';
				$t .= '<div class="panel-body spph250 m-auto">';
				$t .= '<img src="' . plugins_url() . '/sanalpospro/img/gateways/' . $k . '.png" class="bg-white rounded p-1 m-1"/>';

				if (!$gw->active):
					$t .= '<i style="right: -10px;top: -12px;width: 33px;" class="fas fa-times text-light rounded-circle position-absolute p-2 bgred spp_check"><span class="spp_check_text bgred">Bu entegrasyon geçici olarak aktif değil.</span></i>';
				else :
					if ($gw->eticsoft)
						$t .= '<i style="right: -10px;top: -12px;background: #1eb300;" class="fas fa-check text-light rounded-circle position-absolute p-2 bggreen spp_check"><span class="spp_check_text bggreen">EticSoft resmi iş ortağıdır.</span></i>';
					else
						if (!$gw->is_bank)
							$t .= '<p align="center" class="alert alert-danger"> EticSoft iş ortağı DEĞİLDİR. Teknik destekte kısıtlamalar olabilir. </p>';
					endif;
					if (json_decode($gw->families)):
						$t .= '<div> <b>Taksit Yapılabilen Kartlar</b><br>';
						foreach (json_decode($gw->families) as $family):
							$t .= ' <div class="spp_add_method bg-white p-1 text-dark font-weight-bold rounded my-1">' . ucfirst($family) . '</div>';
						endforeach;
						$t .= '</div>';
					endif;


					$t .= '<div class="panel-footer">';
					if ($gw->active):

						if (in_array($k, $exists_gws)):
							$t .= '<p align="center" class="alert bggreen"> Kurulu !</p>';
						else:
							$t .= '<span class="spr_price">' . ($gw->price == 0 ? '<i style="right: -10px;top: 26px;font-size: 17px;width: 33px;" class="fas fa-lira-sign text-light rounded-circle position-absolute p-2 bggreen spp_check"><span class="spp_check_text bggreen">Ücretsiz</span></i>' : number_format($gw->price, 2) . ' TL/yıllık') . '</span><br/>';
							if (isset($gw->paid) AND $gw->paid):
								$t .= ' <form action="" method="post">'
								. '<input type="hidden" name="add_new_pos" value="' . $k . '"/>'
								. '<button type="submit" class="btn bggreen text-light"><i class="fas fa-check"></i> Kurulumu Tamamla</button>';
								$t .= wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '</form>';
							else:
								$t .= EticConfig::getApiForm(array('redirect' => '?controller=gateway&action=buy&gateway=' . $k));
							endif;
						endif;

					endif;
					$t .= '</div></div></div>';
				}
				$t .= '
				</div>
				</div>
				</div>';

				return $t;
			}

			public static function getApiForm($custom_array = false, $button_content = '<i class="fas fa-play"></i> Kurulumu Başlat')
			{
				$api = New SanalPosApiClient(1);
				$apilogininfo = $api->getLoginFormValues();
				$t = '<form action="' . $apilogininfo['url'] . '" target="_blank" method="post">';
				foreach ($apilogininfo as $k => $v)
					$t .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
				if ($custom_array AND is_array($custom_array))
					foreach ($custom_array as $k => $v)
						$t .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
					$t .= '<input type="hidden" name="api_login" value="1">'
					. '<button type="submit" class="btn bggreen text-light">' . $button_content . '</button>'
					. wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' </form>';
					return $t;
				}

				public static function getAdminToolsForm()
				{

					$t = '<form action="#tools" method="post" id="toolsform">

					<div class="panel">
					<div class="row">';
					$t .= '
					<div class="col-md-4 sppbox spp-danger spph300 text-center"> <!--required for floating -->
					<h2>Eski Kayıtları Temizle</h2>
					<p>SanalPOS PRO! üzerinden yapılan alışveriş işlemlerinin tüm detaylarını, banka sorgu ve cevaplarını veri tabanına kayıt eder. (Kredi kartı bilgileri kayıt edilmez.)
					Bu bilgileri banka kayıtlarındaki uyumsuzluklarla karşılaştırmak, hata ayıklamak ve olası hukuki ihtilaflarda resmi mercilere sunmak için sizin sisteminize kayıt ediyoruz.
					Veritabanınızda çok fazla veri biriktiğinde bu bilgileri zaman zaman temizleyebilirsiniz. Bu temizleme işlemi son bir ay işlemleri hariç tüm işlemlerin detaylarını <b>geri getirilemeyecek şekilde</b> siler.</p>
					<button style="background: #ad1313;" class="btn btn-large btn-warning text-light" name="clear-logs" value="1">Eski logları temizle</button>
					</div>';
					$t .= '
					<div class="col-md-4 text-center sppbox spp-success spph300">
					<h2>Sunucu Uyumluluk Testi</h2>
					<p>
					Pos ve ödeme kuruluşlarının sistemleri bazı özel gereksinimlere ihtiyaç duyar. Bu gereksinimleri ve sisteminizin uyumluluğunu kontrol etmek için aşağıdaki aracı kullanabilirsiniz.
					Bu araç ayrıca SanalPOS PRO! modülünün çalışmasına engel olacak/etkileyecek modülleri/eklentileri de listeler.</p>
					<button class="btn btn-large btn-success" name="check-server" value="1">Sunucuyu ve sistemi kontrol et</button>
					</div>';
					$t .= '
					<div class="col-md-4 text-center sppbox spp-tab-active spph300">
					<h2>Eski Versiyon Ayarları</h2>
					<p>
					Daha önce bir SanalPOS PRO! versiyonu kullandıysanız daha önce girilen banka parametrelerini göstermek için aşağıdaki butona tıklayabilirsiniz.
					Bu araç daha önceki versiyonlarda kurulu bankaları ve bilgilerini listeler. </p>
					<button class="btn btn-large btn-info" name="check-oldtables" value="1">Eski bankaları göster</button>
					</div>';
		$t .= '</div>'; // Row
		/*
		  $cats = New HelperTreeCategoriesCore(1);
		  $cats->setUseCheckBox(true);
		  $cats->setTitle('Taksit uygulanmayacak kategoriler');
		  $cats->setInputName('spr_config_res_cats');

		  if (is_array(EticConfig::getResCats()))
		  $cats->setSelectedCategories(EticConfig::getResCats());
		  $t .= '<div class="row">';

		  $t .= '
		  <div class="col-md-6 panel">
		  <h2>Taksit Kısıtlaması</h2>
		  <p> Taksit yapılmayacak ürünlerin kategorilerini seçiniz.
		  Alışveriş sepetinde bu kategorilerden ürünler varsa taksitli alışveriş yapılmayacak,
		  ödemeler tek çekim olarak yapılabilecektir. Taksit kısıtlaması olan ürünler
		  sepete atıldığında müşteriye bir uyarı mesajı gösterilmektedir.
		  <b>Taksit kısıtlaması olan ürünleriniz yoksa hiç bir kategoriyi seçmeyiniz !</b>
		  </p>
		  ' . $cats->render() . '
		  <button type="submit" name="savetoolsform" value="1" class="btn btn-default pull-right"><i class="process-icon-save"></i> Kısıtlamaları Kaydet</button>
		  </div>';

		  $t .= '<div class="col-md-6 bgblue sppbox">'
		  . '<h2>FP007 Dolandırıcılık Koruma Sistemi</h2>'
		  . '<div class="alert alert-info">SanalPOS PRO! mağazalarının alışveriş süreçlerini güvenli hale getiren'
		  . 'FP007 proje kodlu yazılım servisimiz henüz yapım aşamasında !</div>'
		  . '<hr>'
		  . '</div>';
		  $t .= '</div>'; // Row

		 */
		$t .= '</div>'; // Panel



		$t .= wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '</form>  ';
		return $t;
	}
	public static function getCampaigns(){
		$t = '<div class="panel">
		<div class="row">
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2VCy0Gi">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/ipara/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/38j47QA">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paybyme/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2CXqsY9">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paynet/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2YShNij">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paytr/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/38maylP">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paytrek/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/3ijNQ2x">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/parampos/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		</div>
		</div>';
		return $t;
	}

	public static function getApiSettingsForm()
	{

		$t = '<form action="#tools" method="post" id="toolsform">

		<div class="panel">
		<div class="row">
		<div class="col-md-12 sppbox bgred"> <!--required for floating -->
		<h2>Eski Kayıtları Temizle</h2>
		<p>SanalPOS PRO! üzerinden yapılan alışveriş işlemlerinin tüm detaylarını, banka sorgu ve cevaplarını veri tabanına kayıt eder. (Kredi kartı bilgileri kayıt edilmez.)
		Bu bilgileri banka kayıtlarındaki uyumsuzluklarla karşılaştırmak, hata ayıklamak ve olası hukuki ihtilaflarda resmi mercilere sunmak için sizin sisteminize kayıt ediyoruz.
		Veritabanınızda çok fazla veri biriktiğinde bu bilgileri zaman zaman temizleyebilirsiniz. Bu temizleme işlemi son bir ay işlemleri hariç tüm işlemlerin detaylarını <b>geri getirilemeyecek şekilde</b> siler.</p>
		<hr/>
		<button class="btn btn-large btn-warning" name="clear-logs" value="1">Eski logları temizle</button>
		</div>
		<div class="col-md-6 text-center sppbox bgpurple">
		<h2>Sunucu Uyumluluk Testi</h2>
		<p>
		Pos ve ödeme kuruluşlarının sistemleri bazı özel gereksinimlere ihtiyaç duyar. Bu gereksinimleri ve sisteminizin uyumluluğunu kontrol etmek için aşağıdaki aracı kullanabilirsiniz.
		Bu araç ayrıca SanalPOS PRO!modülünün çalışmasına engel olacak/etkileyecek modülleri/eklentileri de listeler.</p>
		<hr/>
		<button class="btn btn-large btn-warning" name="check-server" value="1">Sunucuyu ve sistemi kontrol et</button>
		<br/>
		<br/>
		</div>
		</div>
		</div>
		' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '
		</form> 
		';
		return $t;
	}

	public static function getCardSettingsForm($module_dir)
	{
		$all_gws = EticGateway::getGateways(true);
		$def_rate = EticInstallment::getDefaultRate();

		$t = '<form action="#cards" id="cards" method="post"> 
		<div class="panel">
		<div class="row ">
		<div style="text-align: right;" class="mt-3"> 
		<a class="btn bgred text-light"><i class="far fa-life-ring"></i> Yardım</a> 
		<button type="submit" name="submitcards" class="btn bggreen text-light"><i class="fas fa-percent"></i> Oranları Kaydet</button>
		<input type="hidden" name="submitcardrates" value="1"/>
		</div>
		<div class="col-md-12 my-2 text-center"> <!-- required for floating -->
		<h4>KARTLAR VE TAKSİT SEÇENEKLERİ</h4>
		</div>
		
		</div>';
		$t .='<div class="col-md-12 ins_tab_menu text-center">';
		foreach (EticConfig::$families as $family):
			$t .= '<a class="ins_tab_link m-3 p-3 rounded" onclick=openSppTab("'.$family.'",'."'ins_tab'".',"ins_tab_link")>'
			.'<img src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png" class="thumbnail center-block"/></a>';
		endforeach;
		$t .='</div>';

		$t .= '<div class="row">';
		foreach (EticConfig::$families as $family):
			$gwas = EticGateway::getByFamily($family, true);
			
			$t .= '<div class="col-md-12 ins_tab text-center w-75 m-auto" id="' . $family . '"><br/>'
			. '<div style="border: solid 1px #e0e0e0;"><img src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png" class="thumbnail center-block"/></div>';
			if (!$gwas OR empty($gwas)) {
				$t .= '<h2>Uygun POS Yok veya Kurulmamış</h2>'
				. '<p><i style="font-size: 45px;" class="icon-remove"></i></p>' . ucfirst($family) . ' kart ailesine taksit yapabileceğiniz'
				. ' hiç bir POS sistemi kurulu değil. ' . ucfirst($family) . ' ödemelerini taksitsiz olarak alabilirsiniz.<br/>'
				. '<div class="alert alert-info">Sadece Tek Çekim Ödeme alabilirsiniz. Tek Çekimler için '
				. 'tanımlanmış POS sistemi: ' . ucfirst($def_rate['gateway']) . ' </div>';
				$t .= '<div style="line-height:25px"> <h2>Taksit yapabilen pos sistemleri</h2>';
				foreach (EticGateway::getByFamily($family, false) as $gwall)
					$t .= ' <span class="label label-info">' . ucfirst(EticGateway::$gateways->{$gwall}->full_name) . '</span>';
				$t .= '</div></div>';
				continue;
			}

			$t .= '<div style="border: solid 1px #e0e0e0; text-align: -webkit-center;"><div class="col-sm-6 col-xs-6">'
			. 'Tümü için toplu seçim </div>'
			. '<div class="col-sm-6 col-xs-6">'
			. '<select class="inst_select_all" id="' . $family . '" name="' . $family . '_all">'
			. '<option value="">Seçiniz</option>'
			. '<option value="0">Taksit Yok</option>';

			foreach ($gwas as $gwa)
				$t .= '<option value="' . $gwa . '">' . ucfirst(EticGateway::$gateways->{$gwa}->full_name) . '</option>';

			$t .= '</select></div></div>'
			. '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">'
			. '<thead><tr>'
			. '<td scope="col">Taksit</td>'
			. '<td scope="col">Pos</td>'
			. '<td scope="col">Oran (%)</td>'
			. '</tr></thead>';
			for ($i = 1; $i <= 12; $i++) :
				$ins = EticInstallment::getByFamily($family, $i);

				$t .= '<tr>'
				. '<td>' . $i . '</td>'
				. '<td><select class="inst_select ' . $family . ' form-control" id="row_' . $family . '_' . $i . '" name="' . $family . '[' . $i . '][gateway]">'
				. '<option value="0">Kapalı</option>';

				if ($i == 1) {
					$t .= '<option value="' . $def_rate['gateway'] . '">' . ucfirst($def_rate['gateway']) . ''
					. '(Varsayılan)</option>';
					foreach ($all_gws as $gwa)
						$t .= '<option ' . ($ins && $ins['gateway'] == $gwa->name ? 'selected ' : '')
					. 'value="' . $gwa->name . '">' . ucfirst($gwa->name) . '</option>';
				} else
				foreach ($gwas as $gwa)
					$t .= '<option ' . ($ins && $ins['gateway'] == $gwa ? 'selected ' : '')
				. 'value="' . $gwa . '">' . ucfirst(EticGateway::$gateways->{$gwa}->name) . '</option>';

				$t .= '</select></td>'
				. '<td><div class="input-group">' . ($ins ? '<span class="row_' . $family . '_' . $i . ' input-group-addon">%</span>' : '' )
				. '<input class="form-control row_' . $family . '_' . $i . '  input_' . $family . '" size="5" step="0.01" type="number" style="width:60px" '
				. 'name="' . $family . '[' . $i . '][rate]" value="' . ($ins ? (float) $ins['rate'] : '') . '">'
				. '</div></td>'
				. '</tr>';
			endfor;

			$t .= '</table></div>';
			$t .= '<div style="line-height:25px"><span>' . ucfirst($family) . ' kartlarına taksit yapabilen pos sistemleri</span>';
			foreach (EticGateway::getByFamily($family, false) as $gwall)
				$t .= ' <span class="label label-' . (in_array($gwall, $gwas) ? 'success' : 'default') . '">'
			. '' . ucfirst(EticGateway::$gateways->{$gwall}->name) . '</span>';
			$t .= '</div>';
			$t .= '</div>';
		endforeach;
		$t .= '<div class="clear clearfix"></div>
		</div></div>
		' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '
		</form>';
		return $t;
	}

	public static function getHelpForm()
	{

		$t = '
		<div class="panel">
		<div class="row">
		<div class="col-sm-6 text-center">            
		<h1>Yardıma mı ihtiyacınız var? <br/> Hemen eticsoft\'u çağırın !</h1>
		<div class="row">
		<div class="col-sm-2"></div>
		<img src="' . plugins_url() . '/sanalpospro/img/help2.png" style="height: 300px;" class="img-responsive text-center" id="payment-logoh" />
		<div class="col-sm-2"></div>
		</div>
		</div>
		<div class="col-sm-6 panel text-center">
		<h1>Destek Kanalları</h1>
		' . EticConfig::getApiForm(array('redirect' => '?controller=home&action=addticket'), '<h2 class="text-light"><i class="fas fa-life-ring"></i> Destek Sistemine Bağlan</h2>')
		. '
		<div class="d-flex flex-column"><a class="btn spp-info text-light btn-large my-2"><i class="fas fa-book"></i> Kullanım Klavuzu</a>
		<a class="btn spp-info text-light btn-large my-2" href="mailto:destek@eticsoft.com?Subject=Wordpress SanalPOS PRO " 
		class="btn spp-info text-light btn-large"><i class="far fa-envelope"></i> destek@eticsoft.com</a>
		<a class="btn spp-info text-light btn-large my-2"><i class="fas fa-phone-alt"></i> 0242 241 59 85</a> </div>
		</div>
		</div>
		</div>

		<div class="panel">
		<div class="row">
		<div class="col-sm-6 spph450 panel text-center">            
		<h4>Projenizi bizimle geliştirmek ister misiniz ?</h4>
		<a href="https://eticsoft.com/">
		<img style="width: 600px;" src="' . plugins_url() . '/sanalpospro/img/eticsoft-infogram.png" class="img-responsive" id="payment-logoy" />
		</a>
		</div>
		<div class="col-sm-6 panel text-center">            
		<h4>Kusursuz Wordpress Hosting !</h4>
		<a href="https://iyonhost.com/wordpress-uyumlu-hosting-php//">
		<img src="' . plugins_url() . '/sanalpospro/img/hosting.jpg" class="img-responsive" id="payment-logo" />
		</a>
		</div>
		</div>
		</div>
		';
		return $t;
	}

	public static function saveCardSettingsForm()
	{
		foreach (EticConfig::$families as $family) {

			if (!Etictools::getValue($family) OR ! is_array(Etictools::getValue($family)))
				continue;
			$installments = Etictools::getValue($family);
			foreach ($installments as $i => $ins) {
				if ($ins['gateway'] == '0') {
					EticInstallment::deletebyFamily($family, $i);
					continue;
				}
				$ins['divisor'] = $i;
				$ins['family'] = $family;
				EticInstallment::save($ins);
			}
		}
		Etictools::rwm('Taksitler Güncellendi !', true, 'success');
	}

	public static function saveToolsForm()
	{
		if (Etictools::getValue('check-oldtables')) {
			if (EticSql::tableExists('spr_bank')) {
				$old_banks = EticSql::getRows('spr_bank');
				if ($old_banks) {
					$old_txt = '';
					foreach ($old_banks as $old_bank) {
						$params = unserialize($old_bank['params']);
						$old_txt .= '<hr/><b>' . $old_bank['ad'] . '</b> Parametreler <br/>';
						foreach ($params['params'] as $k => $v)
							$old_txt .= $k . ' : ' . $v['value'] . '</br>';
					}
					Etictools::rwm('Eski versiyona ait bankalar ' . $old_txt, true, 'success');
				}
			}
			Etictools::rwm('Eski versiyona ait bilgi bulunamadı', true, 'warning');
		} else {
			if (!Etictools::getValue('spr_config_res_cats') OR ! is_array(Etictools::getValue('spr_config_res_cats')))
				Eticconfig::set('SPR_RES_CATS', 'off');
			else
				Eticconfig::set('SPR_RES_CATS', json_encode(Etictools::getValue('spr_config_res_cats')));
			Etictools::rwm('Taksit Kısıtlamaları Güncellendi !', true, 'success');
		}
	}

	public static function saveGatewaySettings()
	{
		if (Etictools::getValue('remove_pos')) {
			$gw = New EticGateway(Etictools::getValue('remove_pos'));
			$gw->delete();
		}

		foreach (EticGateway::getGateways() as $gw) { 
			if (Etictools::getValue('submit_for') AND Etictools::getValue('submit_for') != $gw->name)
				continue;
			$data = Etictools::getValue($gw->name);
			if (isset($data['lib']))
				$gw->lib = $data['lib'];
			$lib = EticGateway::$api_libs->{$gw->lib};
			if (!$lib OR ! $data) {
				Etictools::rwm($gw->name . ' Güncelleme hatası tespit edildi. Lütfen formu gözden geçiriniz ' . $gw->lib, true, 'fail');
				continue;
			} 
			foreach ($lib->params as $pk => $pv)
				if (isset($data['params'][$pk]))
					$gw->params->{$pk} = $data['params'][$pk];
				$gw->test_mode = isset($data['test_mode']) ? $data['test_mode'] : false;
				if ($gw->name == "paybyme") {  
					if (Etictools::getValue('submit_for')) 
						Eticconfig::paybymeInstallment($data["params"]["username"],$data["params"]["token"],$data["params"]["keywordID"]);

				} 
				if ($gw->save()) {
					
					Etictools::rwm($gw->full_name . ' güncellendi', true, 'success');
				}
			}
		}

		public static function paybymeInstallment($username,$password,$keywordId)
		{
			$installment_url = "https://pos.payby.me/webServicesExt/FunctionInstallmentList"; 
			$assetPrice = "10000";
			$currencyCode = "TRY";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $installment_url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&password=$password&keywordId=$keywordId&assetPrice=$assetPrice&currencyCode=$currencyCode");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			$result = json_decode($server_output);
			$ins = array(); 

			foreach ($result->InstallmentList as $key => $il) {
				$ins['gateway'] = "paybyme";
				$ins['rate'] = ($il->lastPriceRatio*100)-100;
				$ins["fee"] = $il->commissionShare;
				$ins['divisor'] = $il->installmentCount;
				$ins['family'] = strtolower($il->program) == "bankkart" ? "combo" : strtolower($il->program) == "miles&smiles" ? "miles-smiles" : strtolower($il->program);
				EticInstallment::save($ins);
				
			}
		}

		public static function saveGeneralSettings()
		{
				if ($spr_config = EticTools::getValue('spr_config'))
					foreach ($spr_config as $k => $v)
						Eticconfig::set($k, $v);

					EticSql::updateRow('spr_installment', array(
						'rate' => Etictools::getValue('spr_config_default_rate'),
						'fee' => Etictools::getValue('spr_config_default_fee'),
						'gateway' => Etictools::getValue('spr_config_default_gateway')
					), array('family' => 'all'));
				}

				public static function getConfigNotifications()
				{
		//Check
					foreach (EticGateway::getGateways(true) as $gw) {
						if (isset($gw->params->test_mode) && $gw->params->test_mode == 'on')
							Etictools::rwm($gw->full_name . ' <strong>Test Modunda Çalışıyor</strong>');
					}
					if (EticSql::getRow('spr_installment', 'fee', 0)) {
						Etictools::rwm('Taksit tablosundaki maliyetler (Sizden kesilecek oranlar) eksik girilmiş.'
							. '<br/>Bu durum hesaplamada hatalara neden olabilir.');
					}
				}

				public static function cleardebuglogs()
				{
					return EticSql::deleteRows('spr_debug');
				}

				public static function testSys()
				{
					return true;
				}

				public static function getResCats()
				{
					return json_decode(EticConfig::get('SPR_RES_CATS'));
				}

				
			}
