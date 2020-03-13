<link rel='stylesheet' href='<?php echo plugins_url('/sanalpospro/views/css/admin.css') ?>' type='text/css' media='all' />

<div class="spp_bootstrap-wrapper">
	<div class="panel" style="padding:45px">
		<?php if (isset($error_message)): ?>
			<div class="alert alert-danger">
				Kayıt sırasında hata oluştu.
				<?php echo $error_message ?>
			</div>
		<?php endif; ?>
		<fieldset>
			<h2	align="center">Kurulum veya Güncelleme Yapmak Üzeresiniz!</h2>
			<hr/>
			<div class="row">
				<h3>SanalPOS Pro! Nedir ?</h3>
				<ul>
					<li>SanalPOS Pro! Eticaret sitenizden kredi kartı ile ödeme almanızı sağlayan,
						<strong>Türkiye'nin en gelişmiş ve en çok kullanılan </strong>SanalPOS yazılımıdır. </li>
					<li>Doğrudan kendi hizmetinize ait hesaba tahsilat yapar. Herhangi bir aracılık veya bankacılık servisi değildir.</li>
					<li>2005 yılında EticSoft tarafından geliştirilmeye başlanmıştır. Tamamen <strong>açık kaynaklı</strong>dır. 2018 itibariyle 3000+ mağazada kullanılmaktadır.</li>
					<li>Tüm bankalar ve onlarca ödeme kuruluşu ile entegredir.</li>
					<li>Teknik destek ve güncellemeler <strong>ömür boyu ücretsizdir</strong>. Teknik destek </li>
					<li>4691 sayılı <strong>Teknoloji Geliştirme Bölgeleri Kanunu</strong>'na istinaden,
						T.C. Bilim Sanayi ve Teknoloji Bakanlığı'nın 026763 proje kodu ile Akdeniz Ünv.
						Teknokenti'nde geliştirilmektedir. </li>
					<li>EticSoft'un başka bir ArGe projesi olan FP007 <strong>dolandırıclık koruma yazılımı</strong> ile entegredir.</li>
					<li>Uluslararası <strong>PCI-DSS</strong> güvenlik standartlarına uyumludur. Kart bilgilerini doğrudan pos sistemine iletir.
						Okumaz, saklamaz ve paylaşmaz.</li>
					<li>6102 Türk Ticaret Kanunu ve 5411 sayılı Bankacılık Kanunu'na uyumludur. Türkiye'deki
						tüm bankaların altayapısına uygundur.</li>
				</ul>
			</div>
			<hr/>
			<div class="row">
				<h2>Kullanmaya Başlamak İçin Aşağıdaki Formu Doldurunuz.</h2>
				<small>Bu formdaki bilgilerle SanalPosPRO! hesabınız kurulacak (veya güncellenecek) 
					girdiğiniz bilgiler tamamen gizli tutulacak, 3. kişi ve/veya kurumlarla paylaşılmayacaktır.
					Formu doldurduğunuzda mağazanızın URL adresi ve yazılımınızın versionu da kayıt edilecek,
					bu bilgiler ileride ücretsiz güncelleme ve güvenlik kontrolü v.b. işlemlerinde kullanılacaktır.
				</small>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="input-group">
						<label>Adınız</label><br/>
						<input type="text" name="spr_shop_firstname" class="form-control" required/>
					</div>
					<div class="input-group">
						<label>Soyadınız</label><br/>
						<input type="text" name="spr_shop_lastname" class="form-control" required />
					</div>
				</div>
				<div class="col-md-6">
					<div class="input-group">
						<label class="label label-danger"> Cep Telefonu Numaranız </label><br/>
						<input class="form-control" name="spr_shop_phone" type="tel" pattern="^[0-9\-\+\s\(\)]*$" maxlength="11" minlength="11" placeholder="05XX XXX XXXX" required />
					</div>
					<div class="input-group">
						<label>Bir Şifre Belirleyin</label><br/>
						<input class="form-control" type="text" name="spr_shop_password" required/>
					</div>
				</div>
			</div>
			<br/>
			<div class="alert alert-info"><b>Bu bilgiler hiçbir kişi veya kurum ile paylaşılmayacak olup</b> istenildiği 
				taktirde kullanıcı tarafından silinebilecektir. 
				Telefon numaranız ve e-postanız dolandırıcılık koruma işlemleri, değişiklikler ve güncellemeler 
				ile ilgili bilgilendirme amaçlı kullanılılacak olup isteğiniz dışında
				<strong>hiç bir şekilde reklam/tanıtım amaçlı kullanılmayacaktır.</strong></div>
			<div class="checkbox-inline">
				<input type="checkbox" checked="true" required name="tos_sanalpospro"/> 
				SanalPos Pro! <a href="https://sanalpospro.com/kullanim-sartlari" target="_blank">Kullanım Koşullarını</a> Okudum Kabul Ediyorum
			</div>
			<br/>
			<div class="checkbox-inline">
				<input type="checkbox" checked="true" class="" name="tos_sanalpospro"/> 
				FP007 Dolandırıcılık Koruma Servisi (Ücretsiz) <a href="https://sanalpospro.com/fp07-tos" target="_blank">Kullanım Koşullarını </a>Okudum Kabul Ediyorum
			</div>
		</fieldset>
		<div class="row text-center align-center">
			<br/>
			<input type="hidden" name="spr_terms" value="1"/>
			<input type="hidden" name="spr_shop_key" value=""/>
			<button type="submit" class="center btn btn-large btn-info">Kurulumu Tamamla</button>
		</div>
	</div>
</div>
