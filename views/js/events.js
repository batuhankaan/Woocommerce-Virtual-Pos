const sppFormApp = new SppCcForm();
const sppLang = document.getElementById("spp_lang").value == "tr" ? sppFormApp.tr : sppFormApp.en;
const InstallmentTable = document.getElementById("installment-table");
const InstallmentTableTr = document.querySelectorAll("#installment-table tr");
const WooCurrency = document.getElementsByClassName("woocommerce-Price-currencySymbol")[0].textContent;
new Cleave('#cc_number', {
    creditCard: true,
});
new Cleave('#cc_expiry', {
    date: true,
    datePattern: ['m', 'y']
});
new Cleave('#cc_cvc', {
    numeral: true,
});