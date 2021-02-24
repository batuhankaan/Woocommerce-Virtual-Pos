// (function () {
//     var startingTime = new Date().getTime();
//     // Load the script
//     var script = document.createElement("SCRIPT");
//     script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
//     script.type = 'text/javascript';
//     document.getElementsByTagName("head")[0].appendChild(script);

//     // Poll for jQuery to come into existance
//     var checkReady = function (callback) {
//         if (window.jQuery) {
//             callback(jQuery);
//         } else {
//             window.setTimeout(function () {
//                 checkReady(callback);
//             }, 20);
//         }
//     };

//     // Start polling...
//     checkReady(function (jQuery) {
//         jQuery(function () {
//             var endingTime = new Date().getTime();
//             var tookTime = endingTime - startingTime;
//             console.log("jQuery is loaded, after " + tookTime + " milliseconds!");
//         });
//     });
// })();


// jQuery('ul.nav-tabs li a').click(function (e) {
//     jQuery('ul.nav-tabs li.active').removeClass('active')
//     jQuery(this).parent('li').addClass('active')
// })

// var $ = jQuery;

// $(document).ready(function () {
//     $("select.inst_select").change(function () {
//         var cname = $(this).attr("id");
//         if ($(this).val() == 0)
//             $("." + cname).hide();
//         else
//             $("." + cname).show();
//     });

//     $("select.inst_select_all").change(function () {
//         var cnameall = $(this).attr("id");
//         if ($(this).val() == 0) {
//             $("select." + cnameall).val(0);
//             $("input.input_" + cnameall).hide();
//         } else {
//             $("select." + cnameall).val($(this).val());
//             $("input.input_" + cnameall).show()
//         }
//     });
//     $("select.inst_select").change();
// });



//////////////////////////////////////////////////////////////////////////////


(function() {
  
    function activateTab() {
        if(activeTab) {
          resetTab.call(activeTab);
        }
        this.parentNode.className = 'tab tab-active';
        activeTab = this;
        activePanel = document.getElementById(activeTab.getAttribute('href').substring(1));
           activePanel.className = 'tabpanel show';
          activePanel.setAttribute('aria-expanded', true);
      }
      
      function resetTab() {
            activeTab.parentNode.className = 'tab';
        if(activePanel) {
            activePanel.className = 'tabpanel hide';
              activePanel.setAttribute('aria-expanded', false);
        }
      }
      
      var doc = document,
          tabs = doc.querySelectorAll('.tab a'),
          panels = doc.querySelectorAll('.tabpanel'),
          activeTab = tabs[0],
          activePanel;
     
         activateTab.call(activeTab);
      
      for(var i = tabs.length - 1; i >= 0; i--) {
        tabs[i].addEventListener('click', activateTab, false);
      }
    
    })();

    
    var headers = document.querySelectorAll('.spp-inst-table-img');
    
    for(var i = 0; i < headers.length; i++) {
        headers[i].addEventListener('click', openCurrAccordion);
    }
    
    function openAccordion(e) {
        var parent = this.parentElement;
        var article = this.nextElementSibling;
        
        if (!parent.classList.contains('open')) {
            parent.classList.add('open');
            article.style.maxHeight = article.scrollHeight + 'px';
        }
        else {
            parent.classList.remove('open');
            article.style.maxHeight = '0px';
        }
    }
    
    function openCurrAccordion(e) {
        for(var i = 0; i < headers.length; i++) {
            var parent = headers[i].parentElement;
            var article = headers[i].nextElementSibling;
    
            if (this === headers[i] && !parent.classList.contains('open')) {
                parent.classList.add('open');
                article.style.maxHeight = article.scrollHeight + 'px';
            }
            else {
                parent.classList.remove('open');
                article.style.maxHeight = '0px';
            }
        }
    }
    
    
    const FamilyColor = {
        "axess":"#ffd51d",
        "bonus":"#56ba4d",
        "maximum":"#ec008c",
        "cardfinans":"#0044ee",
        "world":"#7b2f93",
        "paraf":"#1fe0ff",
        "advantage":"#f0801d",
        "bankkart":"#e20d1b",
    }
    
    // const instButton = document.getElementById("spp-bonus");
    // const generalSettingButton = document.getElementById("spp-general-desc-button");
    // const instImg = document.querySelectorAll("#spp-bonus img");
    // const instTableContainer = document.getElementById("inst-container");
    // const instTableHeader = document.getElementById("spp-inst-table-header");
    // const instTablePercent = document.getElementById("inst-table-percent");
    
    // generalSettingButton.addEventListener("click", function (e) {
    //     console.log(e.target.nextElementSibling);
    //     if(e.target.nextElementSibling.style.display == "block"){
    //         e.target.nextElementSibling.style.display="none"
    //     }else{
    //         e.target.nextElementSibling.style.display="block";
    //     }
        
    // })

    // function name(params) {
    //     instButton.addEventListener("click", function (e){
    //         for (let index = 0; index < instImg.length; index++) {
    //             instImg[index].style=""
    //         }
    //         if(e.target.tagName == 'IMG'){
    //             e.target.style.borderTop=".25rem solid #29cc00";
    //             const familyStr = e.target.src.split("/")
    //             console.log(familyStr[familyStr.length - 1].split('.').slice(0, -1).join('.'))
    //             const instTableFamilyType = familyStr[familyStr.length - 1].split('.').slice(0, -1).join('.')
    //             instTableHeader.style.background=`${FamilyColor[instTableFamilyType]}`;
    //             instTableContainer.className += " fade-in";
    //             setTimeout(() => {
    //                 instTableContainer.className="inst-container";
    //             }, 1000);
    //         }
    //     });
    // }
    const docId = document.getElementById;

    function TabShow(params,id) {
        params.addEventListener("click", function (e) {
            if(e.target.tagName == 'IMG'){
                for (let index = 0; index < e.target.parentElement.parentElement.children.length; index++) {
                    document.getElementById(id).children[index].style.display="none";
                }
                document.getElementById(e.target.parentElement.href.split("#")[1]).style.display="block";
                document.getElementById(id).className="fade-in";
                setTimeout(() => {
                    document.getElementById(id).className="";
                }, 0700);
            }

        })
    }

   TabShow(document.getElementById("spp-pos-setting-brand"),"spp-pos-setting-content");
   TabShow(document.getElementById("spp-bonus"),"spp-pos-inst-content");
    
    


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		// 
		// POS AYARLARI

		// spp-pos-setting-content = içerik
        // spp-pos-tab = button
        // .getElementsByTagName("li")
        // const sppPosTab = document.getElementById("spp-pos-tab");
        // sppPosTab.addEventListener('click', function (e) {
        //     console.log(e.target.children);
            
        // })
		// for (let index = 0; index < sppPosTab.length; index++) {
        //         console.log(sppPosTab[index])
        // }
        
        document.querySelectorAll("#bf_turkpos_form tbody")[0].innerHTML += '<tr><td style="width: 60%;">Parampos Hesap No (Firma Kart No)</td><td style="width: 40%;"><input type="text" id="pospro_paramcompany" value="'+POSPRO_PARAMCOMPANY+'" name="turkpos[params][company_card_number]" onkeyup="cValueParam(this.value)"></td></tr>';
        function cValueParam(data) {
            document.querySelectorAll('input[name="turkpos[params][company_card_number]"]')[0].value=data
        }
    
