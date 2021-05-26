class SppCcForm {
  constructor() {
    this.familyname;
    // familyname bug
    this.tr = {
      OneInstallment:"Tek Çekim",
      Installment:"Taksit",
      Commission:"Komisyonsuz"
    };
    this.en = {
      OneInstallment:"No Installment",
      Installment:"Installment",
      Commission:"No Commission"
    };
  }
  
   keyup(param) {
    if (param.value.length == 19) {
        if(!InstallmentTableTr[1]){
          this.cnumber();
          InstallmentTable.style.display = "inline-table";
        }
    }else if(param.value.length < 19){
          InstallmentTable.style.display = "none";
          while (InstallmentTableTr[1]) {
            InstallmentTableTr[1].remove();
          }
    }
  }

  cnumber() {
    let creditcard = document.getElementById("cc_number").value.substring(0, 7);
    creditcard = creditcard.replace(/\s/g, '');
    fetchJsonp(`https://bin.sanalpospro.com/?cc=${creditcard}&callback`, { jsonpCallback: 'callback', timeout: 10000 }).then((response) => {
       return response.json()
    }).then((json) => {
      if (json.family && cards[json.family]){
        this.familyname = json.family;
        this.InstFamily(cards[json.family]);
      }else{
        this.InstTable(1, defaultins);
      }
    }).catch((ex) => {
      console.log("Sunucuyla iletişim kurulamadı." + ex)
    });
  }

  InstFamily(familyname) {
    for (let index = 1; index < familyname.length; index++) {
      if (familyname[index] != undefined) {   
        const InstNumber = index;
        const InstAmount = familyname[index];
        this.InstTable(InstNumber, InstAmount);
      }
    }
  }

  InstTable(InstNumF, InstAmoF) {
    let dtable = `<tr>
    <td>

      <label class="input-radio-button">
      <input type="hidden" name="cc_family" value="${this.familyname ? this.familyname : 'all'}">
      <input ${InstNumF == 1 ? "checked" : ''} type="radio" value="${InstNumF}" dataamount="${InstAmoF}" name="cc_installment">
      ${InstNumF == 1 ? sppLang.OneInstallment : InstNumF + sppLang.Installment}
        <span class="checkmark"></span>
      </label>
    </td>

      <td>${InstNumF} x ${currency(InstAmoF / InstNumF * 100 / 100) + WooCurrency}</td>
      <td id="deleteAtt">${InstAmoF == defaultins ? sppLang.Commission : InstAmoF + WooCurrency}</td>

    </tr>`;
    InstallmentTable.insertRow(-1).innerHTML += dtable;
  }

  err(text) {
    e$("#form_error").innerHTML = `<input class="btn_err_style" type="reset" value="${text}">`
  }

  skipIfMax(element) {
   let max = element.maxLength;
    if (element.value.length == max && element.parentNode.nextElementSibling.querySelectorAll("input.cc_input")) {
      element.parentNode.nextElementSibling.querySelectorAll("input.cc_input")[0].focus();
    }
  }

}