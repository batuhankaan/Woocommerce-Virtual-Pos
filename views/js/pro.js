///////////////////////////////////////////////////////////////////////KREDİ KARTI/////////////////////////////////////////////////////////////////////////////


(function (global, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['exports', 'module'], factory);
  } else if (typeof exports !== 'undefined' && typeof module !== 'undefined') {
    factory(exports, module);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, mod);
    global.fetchJsonp = mod.exports;
  }
})(this, function (exports, module) {
  'use strict';

  var defaultOptions = {
    timeout: 5000,
    jsonpCallback: 'callback',
    jsonpCallbackFunction: null
  };

  function generateCallbackFunction() {
    return 'jsonp_' + Date.now() + '_' + Math.ceil(Math.random() * 100000);
  }

  function clearFunction(functionName) {
    try {
      delete window[functionName];
    } catch (e) {
      window[functionName] = undefined;
    }
  }

  function removeScript(scriptId) {
    var script = document.getElementById(scriptId);
    if (script) {
      document.getElementsByTagName('head')[0].removeChild(script);
    }
  }

  function fetchJsonp(_url) {
    var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

    var url = _url;
    var timeout = options.timeout || defaultOptions.timeout;
    var jsonpCallback = options.jsonpCallback || defaultOptions.jsonpCallback;

    var timeoutId = undefined;

    return new Promise(function (resolve, reject) {
      var callbackFunction = options.jsonpCallbackFunction || generateCallbackFunction();
      var scriptId = jsonpCallback + '_' + callbackFunction;

      window[callbackFunction] = function (response) {
        resolve({
          ok: true,
          json: function json() {
            return Promise.resolve(response);
          }
        });

        if (timeoutId) clearTimeout(timeoutId);

        removeScript(scriptId);

        clearFunction(callbackFunction);
      };
      url += url.indexOf('?') === -1 ? '?' : '&';

      var jsonpScript = document.createElement('script');
      jsonpScript.setAttribute('src', '' + url + jsonpCallback + '=' + callbackFunction);
      if (options.charset) {
        jsonpScript.setAttribute('charset', options.charset);
      }
      jsonpScript.id = scriptId;
      document.getElementsByTagName('head')[0].appendChild(jsonpScript);

      timeoutId = setTimeout(function () {
        reject(new Error('JSONP request to ' + _url + ' timed out'));

        clearFunction(callbackFunction);
        removeScript(scriptId);
        window[callbackFunction] = function () {
          clearFunction(callbackFunction);
        };
      }, timeout);
      jsonpScript.onerror = function () {
        reject(new Error('JSONP request to ' + _url + ' failed'));

        clearFunction(callbackFunction);
        removeScript(scriptId);
        if (timeoutId) clearTimeout(timeoutId);
      };
    });
  }
  module.exports = fetchJsonp;
});


///////////////////////////////////////////////////////////////////////KREDİ KARTI/////////////////////////////////////////////////////////////////////////////

const visa = `data:image/svg+xml,%3Csvg xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:cc='http://creativecommons.org/ns%23' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns%23' xmlns:svg='http://www.w3.org/2000/svg' xmlns='http://www.w3.org/2000/svg' xmlns:sodipodi='http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd' xmlns:inkscape='http://www.inkscape.org/namespaces/inkscape' version='1.1' id='Layer_1' x='0px' y='0px' width='23' height='7' viewBox='0 0 1000.046 323.653' enable-background='new 0 0 258.381 161.154' xml:space='preserve' inkscape:version='0.91 r13725' sodipodi:docname='Visa_2006.svg'%3E%3Cmetadata id='metadata23'%3E%3Crdf:RDF%3E%3Ccc:Work rdf:about=''%3E%3Cdc:format%3Eimage/svg+xml%3C/dc:format%3E%3Cdc:type rdf:resource='http://purl.org/dc/dcmitype/StillImage' /%3E%3Cdc:title%3E%3C/dc:title%3E%3C/cc:Work%3E%3C/rdf:RDF%3E%3C/metadata%3E%3Cdefs id='defs21'%3E%3C/defs%3E%3Csodipodi:namedview pagecolor='%23ffffff' bordercolor='%23666666' borderopacity='1' objecttolerance='10' gridtolerance='10' guidetolerance='10' inkscape:pageopacity='0' inkscape:pageshadow='2' inkscape:window-width='30' inkscape:window-height='55' id='namedview19' showgrid='false' inkscape:zoom='0.35355339' inkscape:cx='34.690897' inkscape:cy='131.15483' inkscape:window-x='-8' inkscape:window-y='-8' inkscape:window-maximized='1' inkscape:current-layer='Layer_1' /%3E%3Cg id='g4158' transform='matrix(4.4299631,0,0,4.4299631,-81.165783,-105.04783)'%3E%3Cpolygon points='116.145,95.719 97.858,95.719 109.296,24.995 127.582,24.995 ' id='polygon9' style='fill:%2300579f' /%3E%3Cpath d='m 182.437,26.724 c -3.607,-1.431 -9.328,-3.011 -16.402,-3.011 -18.059,0 -30.776,9.63 -30.854,23.398 -0.15,10.158 9.105,15.8 16.027,19.187 7.075,3.461 9.48,5.72 9.48,8.805 -0.072,4.738 -5.717,6.922 -10.982,6.922 -7.301,0 -11.213,-1.126 -17.158,-3.762 l -2.408,-1.13 -2.559,15.876 c 4.289,1.954 12.191,3.688 20.395,3.764 19.188,0 31.68,-9.481 31.828,-24.153 0.073,-8.051 -4.814,-14.22 -15.35,-19.261 -6.396,-3.236 -10.313,-5.418 -10.313,-8.729 0.075,-3.01 3.313,-6.093 10.533,-6.093 5.945,-0.151 10.313,1.278 13.622,2.708 l 1.654,0.751 2.487,-15.272 0,0 z' id='path11' inkscape:connector-curvature='0' style='fill:%2300579f' /%3E%3Cpath d='m 206.742,70.664 c 1.506,-4.063 7.301,-19.788 7.301,-19.788 -0.076,0.151 1.503,-4.138 2.406,-6.771 l 1.278,6.094 c 0,0 3.463,16.929 4.215,20.465 -2.858,0 -11.588,0 -15.2,0 l 0,0 z m 22.573,-45.669 -14.145,0 c -4.362,0 -7.676,1.278 -9.558,5.868 l -27.163,64.855 19.188,0 c 0,0 3.159,-8.729 3.838,-10.609 2.105,0 20.771,0 23.479,0 0.525,2.483 2.182,10.609 2.182,10.609 l 16.932,0 -14.753,-70.723 0,0 z' id='path13' inkscape:connector-curvature='0' style='fill:%2300579f' /%3E%3Cpath d='M 82.584,24.995 64.675,73.222 62.718,63.441 C 59.407,52.155 49.023,39.893 37.435,33.796 l 16.404,61.848 19.338,0 28.744,-70.649 -19.337,0 0,0 z' id='path15' inkscape:connector-curvature='0' style='fill:%2300579f' /%3E%3Cpath d='m 48.045,24.995 -29.422,0 -0.301,1.429 c 22.951,5.869 38.151,20.016 44.396,37.02 L 56.322,30.94 c -1.053,-4.517 -4.289,-5.796 -8.277,-5.945 l 0,0 z' id='path17' inkscape:connector-curvature='0' style='fill:%23faa61a' /%3E%3C/g%3E%3C/svg%3E`;
const troy = `data:image/svg+xml,%3Csvg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 7086.61 3263.34'%3E%3Cdefs%3E%3Cstyle%3E.cls-1%7Bfill:%23323e48;%7D.cls-1,.cls-2%7Bfill-rule:evenodd;%7D.cls-2%7Bfill:%2300adbb;%7D%3C/style%3E%3C/defs%3E%3Ctitle%3Etroy-seeklogo%3C/title%3E%3Cpath class='cls-1' d='M7203.9,3145.06c-98.81,0-207.8,55.1-251.27,154.08L6490.74,4357.92,6324.37,3299.14c-19.56-99-86.82-154.08-193.28-154.08H5546l500.48,1758.38c9.22,33.72,11,71.11,4.22,110.43-26.19,146.74-166.37,265.78-313.16,265.78H5409.81c-83.22,0-138,51.54-164.8,168.23l-72.8,435.76h577.08c301.06,0,654.63-151.25,890.48-569.49L7795.24,3145.06Z' transform='translate(-708.66 -2620.3)'/%3E%3Cpath class='cls-1' d='M1523.52,2620.3c172.81,0,256.32,68.94,225.91,242.17l-49.79,282.22h397l-87.49,495.79H1612l-108.2,613.86c-37.4,213.87,174.31,242.36,295.76,242.36,24.2,0,44.31-.55,58.7-1.39l-97.12,552.87c-30,3.19-60.87,7.35-126.26,7.35-301.43,0-871.64-80.75-759.52-716.83L998.1,3640.48H708.66l87.13-495.79H1082.4l92.25-524.39Z' transform='translate(-708.66 -2620.3)'/%3E%3Cpath class='cls-2' d='M4859.92,3167.41l-102,578c125.79,64.24,211.95,194.72,211.95,345.44,0,198.7-149,361.51-340.74,384.93l-101.94,577.92c18,1.15,36.31,1.75,54.55,1.75,533.06,0,964.78-432,964.78-964.6,0-435.46-289.14-803.9-686.59-923.48' transform='translate(-708.66 -2620.3)'/%3E%3Cpath class='cls-2' d='M4405.88,4436.43c-125.66-63.88-212-195-212-345.49,0-197.44,149.27-361.52,341-384.52l101.81-578c-18.12-1.14-36.55-2-54.67-2-532.64,0-964.6,432.33-964.6,964.49,0,435.63,289.26,804.31,686.72,923.89Z' transform='translate(-708.66 -2620.3)'/%3E%3Cpath class='cls-1' d='M2326.52,3145.06h341.16c172.88,0,256,69.07,225.56,242.36l-37.33,209.66c127.11-258,402.82-470.87,685.64-470.87,37.09,0,72.8,7.35,72.8,7.35l-110.26,625.12s-49.49-11.5-126.74-11.5c-150.71,0-405.59,47.81-547.45,331-33.84,69.79-59.85,154.69-77.86,257l-123.43,702.32H1993.07Z' transform='translate(-708.66 -2620.3)'/%3E%3Cpath class='cls-1' d='M7639.56,5080.58c0-11.2-3.73-20.59-11.8-28.24-7.88-7.59-17.94-11.2-30.34-11.2h-41.61v78.46h41.61c12.4,0,22.46-3.92,30.34-11,8.07-7.47,11.8-16.56,11.8-28.06M7677,5237.31h-36.25L7589.53,5146h-33.72v91.28h-30.89V5013.62h75.93c19.57,0,36.25,6.69,49.91,19.33,13.55,12.83,20.78,28.78,20.78,47.63,0,31.07-16.26,51.66-48.23,61.42Zm86.22-111.69q0,72.35-50.58,123c-33.35,34-74.24,50.94-121.45,50.94s-87.49-16.92-121.14-50.94q-49.86-50.67-49.8-123,0-72.07,49.8-122.89c33.65-34.14,74.06-51.06,121.14-51.06s88.1,16.92,121.45,51.06q50.58,50.94,50.58,122.89m32,0c0,56.42-20,104.65-59.86,144.21-39.44,39.68-87.49,60-144.21,60-56.11,0-104.16-20.35-144.21-60-39.73-39.56-59.79-87.79-59.79-144.21,0-56.24,20.06-104.35,59.79-144.33,40.05-39.62,88.1-59.61,144.21-59.61,56.72,0,104.77,20,144.21,59.61q59.79,60,59.86,144.33' transform='translate(-708.66 -2620.3)'/%3E%3C/svg%3E`;
const amex = `data:image/svg+xml,%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 291.764 291.764' style='enable-background:new 0 0 291.764 291.764;' xml:space='preserve'%3E%3Cg%3E%3Cpath style='fill:%2326A6D1;' d='M18.235,41.025h255.294c10.066,0,18.235,8.169,18.235,18.244v173.235 c0,10.066-8.169,18.235-18.235,18.235H18.235C8.16,250.74,0,242.57,0,232.505V59.269C0,49.194,8.169,41.025,18.235,41.025z'/%3E%3Cpath style='fill:%23FFFFFF;' d='M47.047,113.966l-28.812,63.76h34.492l4.276-10.166h9.774l4.276,10.166h37.966v-7.759l3.383,7.759 h19.639l3.383-7.923v7.923h78.959l9.601-9.902l8.99,9.902l40.555,0.082l-28.903-31.784l28.903-32.058h-39.926l-9.346,9.719 l-8.707-9.719h-85.897l-7.376,16.457l-7.549-16.457h-34.42v7.495l-3.829-7.495C76.479,113.966,47.047,113.966,47.047,113.966z M53.721,123.02h16.813l19.111,43.236V123.02h18.418l14.761,31l13.604-31h18.326v45.752h-11.151l-0.091-35.851l-16.257,35.851 h-9.975l-16.348-35.851v35.851h-22.94l-4.349-10.257H50.147l-4.34,10.248H33.516C33.516,168.763,53.721,123.02,53.721,123.02z M164.956,123.02h45.342L224.166,138l14.315-14.98h13.868l-21.071,22.995l21.071,22.73h-14.497l-13.868-15.154l-14.388,15.154 h-44.64L164.956,123.02L164.956,123.02z M61.9,130.761l-7.741,18.272h15.473L61.9,130.761z M176.153,132.493v8.352h24.736v9.309 h-24.736v9.118h27.745l12.892-13.43l-12.345-13.357h-28.292L176.153,132.493z'/%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E`;
const mastercard = `data:image/svg+xml,%0A%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 131.39 86.9'%3E%3Cdefs%3E%3Cstyle%3E.a%7Bopacity:0;%7D.b%7Bfill:%23fff;%7D.c%7Bfill:%23ff5f00;%7D.d%7Bfill:%23eb001b;%7D.e%7Bfill:%23f79e1b;%7D%3C/style%3E%3C/defs%3E%3Ctitle%3EAsset 1%3C/title%3E%3Cg class='a'%3E%3Crect class='b' width='131.39' height='86.9'/%3E%3C/g%3E%3Crect class='c' x='48.37' y='15.14' width='34.66' height='56.61'/%3E%3Cpath class='d' d='M51.94,43.45a35.94,35.94,0,0,1,13.75-28.3,36,36,0,1,0,0,56.61A35.94,35.94,0,0,1,51.94,43.45Z'/%3E%3Cpath class='e' d='M120.5,65.76V64.6H121v-.24h-1.19v.24h.47v1.16Zm2.31,0v-1.4h-.36l-.42,1-.42-1h-.36v1.4h.26V64.7l.39.91h.27l.39-.91v1.06Z'/%3E%3Cpath class='e' d='M123.94,43.45a36,36,0,0,1-58.25,28.3,36,36,0,0,0,0-56.61,36,36,0,0,1,58.25,28.3Z'/%3E%3C/svg%3E`;
const defaultCard = `data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjIxcHgiIGhlaWdodD0iMThweCIgdmlld0JveD0iMCAwIDIxIDE4IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCAzOS4xICgzMTcyMCkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+Y2FyZDwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPjwvZGVmcz4KICAgIDxnIGlkPSJQYWdlLTEiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJjYXJkIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxLjAwMDAwMCwgMS4wMDAwMDApIiBzdHJva2U9IiNBQTkxQTQiIHN0cm9rZS13aWR0aD0iMiI+CiAgICAgICAgICAgIDxwb2x5bGluZSBpZD0iUmVjdGFuZ2xlIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHBvaW50cz0iMCAxNiAwIDAgMTkgMCAxOSAxNCA2LjAxNjgyMTI5IDE0Ij48L3BvbHlsaW5lPgogICAgICAgICAgICA8cGF0aCBkPSJNMC4wMzI3MTQ4NDM4LDUuNTgyMjc1MzkgTDE4LjE5NTE3NTksNS41ODIyNzUzOSIgaWQ9IlBhdGgtMiI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+`;
// const iframeDocument = document.getElementById("OM300x250").contentDocument
const InstallmentTablesRows = document.getElementById("installments-rows");
const InstallmentTable = document.getElementById("installment-table");
const inputCcNumber = document.getElementById("cc_number");
const cardIcon = document.getElementById("card-icons");
const Tbody = document.getElementsByTagName("tbody");
const inputMonth = document.getElementById("cc_expiry");
// const inputYears = document.getElementById("years");
// const closeX =  document.getElementById("close-x");
const htmlTr = document.getElementsByTagName("tr");
const inputCvc = document.getElementById("cc_cvc");
const inputName = document.getElementById("cc_name");
const installmentLoading = document.getElementById("installment-loading");
const sppForm = document.getElementById("cc_form");
const sppFormTwo = document.getElementById("spp-form-two");
const paymentButton = document.getElementById("cc_form_submit");
const ccName = document.getElementById("cc_name");
const siteLang = document.getElementsByTagName("html")[0]["lang"];
const DiffPayment = document.getElementById("diff-payment");
const ccPayment = document.getElementById("cc-payment");
const instTitles = document.getElementById("installment-titles").children;
const iframeEvent = window.top.document.getElementById("iframesty");
const sppContent = document.getElementById("spp-content");
const date = new Date();
const topBody = window.top.document.documentElement;

function inputCheck(variable,nextVariable,numberVariable) {
    variable.value.length === numberVariable ? nextVariable.focus() : console.log("degil 1");
}

function err(text) {
  document.getElementById("form_error").innerHTML=`<input class="btn_err_style" type="reset" value="${text}">`;
}

function openMethod(id,nextId,cls){
  id.style.display = "none";
  nextId.style.display = "block";
}

var SppTekCekim;
var SppKomisyon;
var SppTaksit;
function ModuleLanguage(params) {
      inputCcNumber.placeholder = params.kartnumarasi;
      inputMonth.placeholder = params.tarih;
      paymentButton.textContent = params.button;
      ccName.placeholder = params.isim;
      ccPayment.innerText = params.kredikarti;
      DiffPayment.innerText = params.digerodeme;
      instTitles[0].innerText = params.taksit;
      instTitles[1].innerText = params.taksittutar;
      instTitles[2].innerText = params.tutar;
      window.SppTekCekim = params.tekcekim;
      window.SppKomisyon = params.komisyon;
      window.SppTaksit = params.kactaksit;
}
const trLang = {
  "lang":"tr",
  "kartnumarasi":"KART NUMARASI",
  "tarih":"TARİH",
  "isim":"KART SAHİBİNİN ADI",
  "button":"ÖDEMEYİ TAMAMLA",
  "kredikarti":"Kredi Kartı İle",
  "digerodeme":"Diğer Ödeme",
  "taksit":"TAKSİT",
  "taksittutar":"T.TUTARI",
  "tutar":"T.TUTARI",
  "tekcekim":"Tek Çekim",
  "komisyon":"Komisyonsuz",
  "kactaksit":" Taksit"
};
const enLang = {
  "lang":"eng",
  "kartnumarasi":"CARD NUMBER",
  "tarih":"DATE",
  "isim":"YOUR NAME",
  "button":"COMPLETE PAYMENT",
  "kredikarti":"With Credit Card",
  "digerodeme":"Other Payment",
  "taksit":"INSTALLMENT",
  "taksittutar":"I.AMOUNT",
  "tutar":"AMOUNT",
  "tekcekim":"No Installment",
  "komisyon":"No Commission",
  "kactaksit":" Purchase"
  };

  window.addEventListener("DOMContentLoaded", function(){
    if(siteLang.toLowerCase().search("tr") == 0){
      ModuleLanguage(trLang);
    }else{
        ModuleLanguage(enLang);
    }
  });


function isNumber(evt) {
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
}

///////////history validate

function ccFormatExpiry(e) {
var inputChar = String.fromCharCode(event.keyCode);
var code = event.keyCode;
var allowedKeys = [8];
if (allowedKeys.indexOf(code) !== -1) {
  return;
}

event.target.value = event.target.value.replace(
  /^([1-9]\/|[2-9])$/g, '0$1/' 
).replace(
  /^(0[1-9]|1[0-2])$/g, '$1/'
).replace(
  /^([0-1])([3-9])$/g, '0$1/$2' 
).replace(
  /^(0?[1-9]|1[0-2])([0-9]{2})$/g, '$1/$2' 
).replace(
  /^([0]+)\/|[0]+$/g, '0'
).replace(
  /[^\d\/]|^[\/]*$/g, '' 
).replace(
  /\/\//g, '/' 
);
}

///////////history validate

function cc_format(value) {
var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
var matches = v.match(/\d{4,16}/g);
var match = matches && matches[0] || ''
var parts = []
for (i=0, len=match.length; i<len; i+=4) {
  parts.push(match.substring(i, i+4))
}
if (parts.length) {
  return parts.join(' ');
} else {
  return value;
}
}

onload = function() {
inputCcNumber.oninput = function() {
  this.value = cc_format(this.value);
}
}

function skipIfMax(element) {
max = parseInt(element.dataset.max);

if (element.value.length >= max && element.nextElementSibling) {
  element.nextElementSibling.focus();  
}
}


function formatterCurrency(price) {

const currency_type = "₺";

const currencyOutput = new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2,
  });

return currencyOutput.format(price).replace(currency_type, '');
}




///////////////////////////////////////////////////

// window.addEventListener("DOMContentLoaded", function(){
//   document.getElementById("spp-total-amount").innerHTML=defaultins;
//   topBody.style.background="red";
//   });


function keyup(){
  if(inputCcNumber.value.length === 19){
      if(!htmlTr[1]){
        cnumber();
        installmentLoading.style.display="none";
        InstallmentTable.style.display = "inline-table";
        InstallmentTable.className="scale-in-ver-top";
      }
  }else if(inputCcNumber.value.length < 9){
          installmentLoading.style.display="block";
          InstallmentTable.className="fade-out-top"; 
          cardIcon.src = defaultCard;
          cardIcon.className="";
          setTimeout(() => { 
            InstallmentTable.style.display = "none"
            while (htmlTr[1]) {
              htmlTr[1].remove();
            }
        }, 400);
    }
}

function getCORS(url, success) {
  var xhr = new XMLHttpRequest();
  if (!('withCredentials' in xhr)) xhr = new XDomainRequest(); // fix IE8/9
  xhr.open('GET', url);
  xhr.onload = success;
  xhr.send();
  return xhr;
}



function InstFamily(familyname) {
  for (let index = 1; index < familyname.length; index++) {
      if (familyname[index] != undefined) {
        console.log(index)
      const InstNumber = index;
      const InstAmount = familyname[index];
      InstTable(InstNumber,InstAmount);
      }
  }
}
function InstTable(InstNumF,InstAmoF) {
  const dtable = `
  <tr class="test">
  <td><label class="input-radio-button">${InstNumF === 1 ? SppTekCekim : InstNumF + SppTaksit}
    <input ${InstNumF === 1 ? "checked" :''} type="radio" value="${InstNumF}" dataamount="${InstAmoF}" name="cc_installment">
    <span class="checkmark"></span>
  </label></td>
  <td>${InstNumF} x ${formatterCurrency(InstAmoF / InstNumF * 100 / 100) +document.getElementsByClassName("woocommerce-Price-currencySymbol")[0].textContent}</td>
  <td id="deleteAtt">${InstAmoF == defaultins ? SppKomisyon : InstAmoF + document.getElementsByClassName("woocommerce-Price-currencySymbol")[0].textContent}</td>
</tr>`;
InstallmentTable.insertRow(-1).innerHTML += dtable;
}




function cnumber(){
const creditcard = document.getElementById("cc_number").value.substring(0,7);
//formatter json
var str=creditcard;
str=str.replace( /\s/g, '');
//formatter json
        var result = fetchJsonp(`https://bin.sanalpospro.com/?cc=${str}&callback`,{jsonpCallback: 'callback',timeout: 10000})
        result.then(function(response) {
            return response.json()
        }).then(function(json) {
        //  JSON.stringify(json);
        console.log(document.getElementById("mp_tx_selected_holder_family").value=json.family)
         console.log(json)
          if(json.family && cards[json.family]){
            document.getElementById("mp_tx_selected_holder_family").value=json.family;
            console.log(json.family == cards[json.family])
            InstFamily(cards[json.family])
            cardIcon.className="jello-vertical";
            if(json.brand == "AMEX"){
              cardIcon.src=amex;
            }else if(json.brand == "MASTER"){
              cardIcon.src=mastercard;
            }else if(json.brand == "VISA"){
              console.log("visa kart")
              cardIcon.src=visa;
            }else if(json.brand == "TROY"){
              cardIcon.src=troy;
            }else{
              console.log("json brand boş");
            }
            
          }else {
            console.log("cnumber else");
            InstTable(1,defaultins)
            document.getElementById("mp_tx_selected_holder_family").value="all";
          }
          })['catch'](function(ex) {
              err("Sunucuyla iletişim kurulamadı.")
          });
  }
