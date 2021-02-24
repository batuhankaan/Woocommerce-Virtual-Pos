// function e$(selector, scope = document) {
//     let elem = scope.querySelectorAll(selector);
//     if(elem.length > 1) return [...elem];
//     return elem[0];
// }

// Object.prototype.on$ = function(event, callback) {
//     this.addEventListener(event, callback);
// }

// Array.prototype.on$ = function(event, callback) {
//     this.forEach(elem => elem.addEventListener(event, callback));
// }

const sppFormApp = new SppCcForm();

const InstallmentTable = document.getElementById("installment-table");
const inputCcNumber = document.getElementById("cc_number");
const cardIcon = document.getElementById("card-icons");
const htmlTr = $(".tr");
const installmentLoading = document.getElementById("installment-loading");

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