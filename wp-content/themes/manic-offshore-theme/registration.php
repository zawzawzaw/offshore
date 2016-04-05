<?php
/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip) {
    if (strtolower($ip) === 'unknown')
        return false;

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647) return false;
        if ($ip >= 167772160 && $ip <= 184549375) return false;
        if ($ip >= 2130706432 && $ip <= 2147483647) return false;
        if ($ip >= 2851995648 && $ip <= 2852061183) return false;
        if ($ip >= 2886729728 && $ip <= 2887778303) return false;
        if ($ip >= 3221225984 && $ip <= 3221226239) return false;
        if ($ip >= 3232235520 && $ip <= 3232301055) return false;
        if ($ip >= 4294967040) return false;
    }
    return true;
}

function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (validate_ip($ip))
                    return $ip;
            }
        } else {
            if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

function registration_form() {
    $ip = get_ip_address();
    $user_id = get_current_user_id();
    echo '
    <script>
    (function($) {
        $(document).ready(function(){     

            ///////////
            //// VALIDATIONS
            ///////////     

            $.extend( $.validator.prototype, {
                checkForm: function () {
                    this.prepareForm();
                    for (var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++) {
                        if (this.findByName(elements[i].name).length != undefined && this.findByName(elements[i].name).length > 1) {
                            for (var cnt = 0; cnt < this.findByName(elements[i].name).length; cnt++) {
                                this.check(this.findByName(elements[i].name)[cnt]);
                            }
                        } else {
                            this.check(elements[i]);
                        }
                    }
                    return this.valid();
                }
            });

            function validateForm($selector, formID) {

                $.validator.addMethod("telephone", function (value, element) 
                {
                    return this.optional(element) || /^(?=.*[0-9])[- +()0-9]+$/.test(value.replace(/\s/g, ""));
                }, "Invalid phone no");

                $.validator.addMethod("companynames", function (value, element) 
                {
                    var check;
                    var val = value.toLowerCase();                    
                    if(val.indexOf("ltd") > -1 || val.indexOf("limited") > -1) check = true;
                    else check = false;

                    return this.optional(element) || check;
                }, "Invalid phone no");

                if(formID==1) {
                    $selector.validate({
                        rules : {
                            "company_name[]": {
                                required : {
                                    depends : function(elem) {
                                        return $("#new-incorporation").is(":checked");
                                    }
                                },
                                companynames : true
                            }
                        },
                        messages: {
                            "company_name[]" : "Please provide three company names"
                        },
                        errorPlacement: function(error, element) {                            
                            element.attr("placeholder", error.text());
                        }
                    });
                }else if(formID==2) {
                    $selector.validate({
                        focusInvalid: false,
                        invalidHandler: function(form, validator) {

                            if (!validator.numberOfInvalids())
                                return;

                            console.log($(validator).errorList);

                            // $("html, body").animate({
                            //     scrollTop: $(validator.errorList[0].element).offset().top
                            // }, 2000);

                        },
                        rules : {
                            "shareholder_1_name" : "required",
                            "shareholder_1_address" : "required",
                            "shareholder_1_address_2" : "required",
                            "shareholder_1_address_4" : "required",
                            "shareholder_1_telephone" : {
                                "required" : true,
                                "telephone" : true
                            },
                            "shareamount_1_amount" : "required",
                            "director_1_name" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_director").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "director_1_address" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_director").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "director_1_address_2" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_director").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },                            
                            "director_1_address_4" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_director").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "director_1_telephone" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_director").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "secretary_1_name" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_secretary").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "secretary_1_address" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_secretary").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "secretary_1_address_2" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_secretary").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },                            
                            "secretary_1_address_4" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_secretary").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            },
                            "secretary_1_telephone" : {
                                required: {
                                    depends : function(elem) {
                                        if($("#nominee_secretary").is(":checked")==false) return true;
                                        else return false;
                                    }
                                }
                            }
                        },
                        errorPlacement: function(error, element) {                            
                            element.attr("placeholder", error.text());
                        }
                    });
                }else if(formID==4) {

                    $selector.validate({
                        rules: {
                            "nominee_director_annual_fee": "required",
                            "nominee_secretary_annual_fee": "required",
                            "summary_shareholder_1_name": "required",
                            "summary_shareholder_1_address": "required",
                            "summary_shareholder_1_address_2": "required",
                            "summary_shareholder_1_address_3": "required",
                            "summary_director_1_name": "required",
                            "summary_director_1_address": "required",
                            "summary_director_1_address_2": "required",
                            "summary_director_1_address_3": "required",
                            "summary_secretary_1_name": "required",
                            "summary_secretary_1_address": "required",
                            "summary_secretary_1_address_2": "required",
                            "summary_secretary_1_address_3": "required",
                            "company_name_choices[]": "required",
                            "tnc": "required"
                        },
                        messages: {
                            "nominee_director_annual_fee": "Please assign at least one director for your company.",
                            "nominee_secretary_annual_fee": "Please assign at least one secretary for your company."
                        },
                        errorPlacement: function(error, element) {
                            if (element.attr("name") == "nominee_director_annual_fee") {

                                element.next(".error").remove();
                                element.after("<p class=\"error pull-left\" style=\"display:inline-block;\">"+error.text()+"</p>");
                                element.parent().find(".go-step-2").show();
                                element.parent().addClass("half-field-container-2")

                            } else if (element.attr("name") == "nominee_secretary_annual_fee") {

                                element.next(".error").remove();
                                element.after("<p class=\"error pull-left\" style=\"display:inline-block;\">"+error.text()+"</p>");
                                element.parent().find(".go-step-2").show();
                                element.parent().addClass("half-field-container-2")

                            } else if (element.attr("name") == "tnc") {
                                error.insertAfter($("label[for=tnc]"));
                            } else {
                                // error.insertAfter(element);
                                element.attr("placeholder", error.text());
                            }
                        }
                    });
                }
            }

            ////////////
            //// CHANGE STEPS
            ////////////

            function moveToTop() {
                window.scrollTo(0, 420);
            }

            function changeNextStep(id, hash){

                var $form1 = $("#registration-page-form-1-1");
                var $form2 = $("#registration-page-form-2");
                validateForm($form1, 1);

                if($form1.valid() && $form2.valid()) {

                    $(".active").removeClass("active");
                    $("#step-"+id).addClass("active");

                    $(".btn-primary").removeClass("btn-primary").addClass("btn-default").prop( "disabled", false );
                    $(".step-"+id+"-circle").removeClass("btn-default").addClass("btn-primary").prop( "disabled", true );

                    $(".active-step").removeClass("active-step");
                    $(".step-"+id+"-circle").parent().addClass("active-step");

                    moveToTop();

                    updateHashInURL(hash);

                }
                
                // if(id=="1-1"|| id=="1-2") {
                //     $("#step-2").find(".back-btn").data("id", id);
                //     $(".step-1-circle").data("id", id);

                //     add the chosen route to hidden field
                //     if(id=="1-1") {
                //         update_input_val(1, "#chosen_route");
                //         on_route_change(1);
                //     }
                //     else {
                //         update_input_val(2, "#chosen_route");
                //         on_route_change(2); 
                //     } 

                // }                
            }

            function changePrevStep(id, hash) {
                
                $(".active").removeClass("active");
                $("#step-"+id).addClass("active");                
                
                $(".btn-primary").removeClass("btn-primary").addClass("btn-default").prop( "disabled", false );
                $(".step-"+id+"-circle").removeClass("btn-default").addClass("btn-primary").prop( "disabled", true );

                $(".active-step").removeClass("active-step");
                $(".step-"+id+"-circle").parent().addClass("active-step");

                moveToTop();

                updateHashInURL(hash);
            }

            ////////////
            //// CLONE FORM
            ////////////

            function cloneForm($el) {
                var html = $el.children(".field-container").clone();
                $el.next(".pasteclone").append(html);
            }

            function updateClonedFields($pasteclone, selector) {
                var fieldID = $("."+selector).find(".field-container").length;

                var $fieldContainer = $pasteclone.find(".field-container").last();
                var lblName = selector.charAt(0).toUpperCase() + selector.slice(1);

                $fieldContainer.find("label.name").html(lblName+" "+fieldID);
                $fieldContainer.find("label.address").html(lblName+" "+fieldID+" address");

                $fieldContainer.find("."+selector+"-name").attr("name", selector+"_"+fieldID+"_name").attr("data-"+selector+"-id", fieldID).val("");
                $fieldContainer.find("."+selector+"-address").attr("name", selector+"_"+fieldID+"_address").attr("data-"+selector+"-id", fieldID).val("");
                $fieldContainer.find("."+selector+"-address-2").attr("name", selector+"_"+fieldID+"_address_2").attr("data-"+selector+"-id", fieldID).val("");
                $fieldContainer.find("."+selector+"-address-3").attr("name", selector+"_"+fieldID+"_address_3").attr("data-"+selector+"-id", fieldID).val("");
                $fieldContainer.find("."+selector+"-amount").attr("name", selector+"_"+fieldID+"_amount").attr("data-"+selector+"-id", fieldID).val("");
            }

            //////////
            /// AJAX REQUEST
            //////////

            function makeRequest(Data, URL, Method) {

                var request = $.ajax({
                    url: URL,
                    type: Method,
                    data: Data,
                    success: function(response) {
                        // if success remove current item
                        console.log(response);
                    },
                    error: function( error ){
                        // Log any error.
                        console.log("ERROR:", error);
                    }
                });

                return request;
            };

            function makeJsonpRequest(Data, URL, Method) {
                
                var request = $.ajax({
                    url: URL,
                    crossDomain: true,
                    type: Method,
                    data: Data,
                    dataType: "jsonp",
                    jsonpCallback: "jsonpCallback",
                    contentType: "application/json; charset=utf-8;",                    
                    success: function (data) {
                        // console.log(data);
                    },
                    error: function(xhr, status, error) {
                        // console.log(status + "; " + error);
                    }
                });

                return request;

            }

            function failedRequest(response){
                response.fail(function(jqXHR, textStatus, errorThrown){
                    console.log(errorThrown);
                });
            }

            ///////////
            /// FILE UPLOAD
            ///////////

            function initFileUpload($selector) {
                $selector.each(function(i, obj) {
                    
                    // $(obj).uploadifive({
                    //     "auto"         : false,
                    //     "multi"        : false,
                    //     "queueSizeLimit" : 1,
                    //     "simUploadLimit" : 1,
                    //     "fileType"     : "application/pdf|image/*",
                    //     "fileSizeLimit": "5MB",
                    //     "buttonText"   : "Upload passport",
                    //     "uploadLimit"  : 1,
                    //     "uploadScript" : "'.SITEURL.'/b/api/uploadfiles",
                    //     "onError"      : function(errorType) {
                    //         // $uploadBtn.uploadifive("cancel", $(".uploadifive-queue-item").first().data("file"));
                    //         // $uploadResponse.text(errorType).css("color","red");
                    //     },
                    //     "onUploadComplete" : function(file, data) {
                    //         console.log(data);

                    //         var data = data.split("||").concat();

                    //         var shortText = jQuery.trim(data[1]).substring(0, 20).trim(this) + "...";

                    //         console.log(data[0]);
                    //         console.log(data[1]);
                    //         console.log(shortText);

                    //         $(this).parent().parent().find("input[type=hidden]").val(data[0]);
                    //         // $(this).parent().parent().insertAfter("<p>"+shortText+"</p>");                            

                    //     }
                    // });

                    var selector = $(obj).attr("data-fieldname");

                    var url = "'.SITEURL.'/b/api/uploadfiles";
                    $(obj).fileupload({
                        url: url,
                        dataType: "json",
                        done: function (e, data) {

                            var shortText = jQuery.trim(data.result.file.org_name).substring(0, 20).trim(this) + "...";

                            $("input[name=summary_"+selector).val(data.result.file.name);
                            $("#"+selector+"_files").html("");
                            $("<p/>").text(shortText).appendTo("#"+selector+"_files");

                        }
                    }).prop("disabled", !$.support.fileInput)
                        .parent().addClass($.support.fileInput ? undefined : "disabled"); 

                });                
  
            }

            ///////////
            /// DOM MANIPULATION
            ///////////

            function createTemplate(selector) {
                var source = $(selector).html(),
                    template = Handlebars.compile(source);

                return template;
            }

            function appendToSelect(data, selector) {
                $.each(data, function(index, each_data){
                    var option = new Option(each_data.name, each_data.id);
                    $(option).data("prices", each_data.price);
                    $("."+selector).append($(option));                    
                });
            }

            function appendToHtml(data, selector) {
                $(selector).html(data);
            }

            function createTemplateAndAppendHtml(template_selector, newdata, append_to_selector) {
                var template = createTemplate(template_selector);
                var data = template(newdata);
                
                appendToHtml(data, append_to_selector);
            }

            function on_route_change(route) {
                if(route==1) {
                    $("#route-1-summary").show();                       
                    $("#route-2-summary").hide(); 

                    appendToHtml("$"+prices["jurisdiction"], "#summaryjurisdiction-price");       

                    update_input_val("", "#shelf_company_id"); // summary forms

                }else {
                    $("#route-2-summary").show();                    
                    $("#route-1-summary").hide();                    
                    appendToHtml("$0.00", "#summaryjurisdiction-price");
                }
            }

            function update_input_val(data, selector) {                
                $(selector).val(data);
            }

            function addAmount(amount, add_amount) {
                if(add_amount=="") add_amount = 0;
                amount += parseFloat(add_amount);
                return amount;
            }         

            function updateHashInURL(hash) {
                window.location.hash = hash;
                return false;
            }

            function initPlugin(selector) {
                
                // init plugin
                var elems = Array.prototype.slice.call(document.querySelectorAll(selector));

                elems.forEach(function(html) {
                    var init = new Switchery(html, { color: "#008b9b" });

                    if(selector==".js-switch") {
                        html.onchange = function() {
                            if(html.checked) {
                                $(html).parent().parent().find(".key-person-info").hide();
                                $(html).parent().parent().find(".key-person-info").children(".field-container").children(".person-input").val("");
                                $(html).parent().parent().find(".nominee-container").show();
                            }else {
                                $(html).parent().parent().find(".key-person-info").show();
                                $(html).parent().parent().find(".nominee-container").hide();
                            } 
                        };
                    }else if(selector==".service-js-switch") {
                        html.onchange = function() {
                            var countryId = $(html).data("country-id");
                            var serviceId = $(html).data("service-id");
                            var serviceName = $(html).data("service-name");

                            if(html.checked) {                                
                                $(".service-"+serviceId+"-country-"+countryId).prop("disabled", false);                                
                                if(serviceName=="Bank accounts") {
                                    // console.log("hi")
                                    $(".credit_card_in_country_"+countryId).prop("disabled", false);
                                }                                
                            }else {
                                $(".service-"+serviceId+"-country-"+countryId).prop("disabled", true);
                                if(serviceName=="Bank accounts") {
                                    $(".credit_card_in_country_"+countryId).val("").trigger("change");    
                                    $(".credit_card_in_country_"+countryId).prop("disabled", true);    
                                }                                                                
                            }
                        }
                    }
                });                                            

            }

            function on_nominee_switch_change(selector, switch_input, price) {
                if ($(switch_input).prop("checked")) {
                    $(".summary-"+selector+"-price-container").show();
                    $("#summary-"+selector+"-price").html("<p>$"+price+"</p>");  
                    $("#nominee_"+selector+"_annual_fee").prop("checked", true);
                } 
                else {
                    $(".summary-"+selector+"-price-container").hide();
                    $("#summary-"+selector+"-price").hide().html("<p>$0.00</p>");
                    $("#nominee_"+selector+"_annual_fee").prop("checked", false);
                } 
                $("#nominee_"+selector+"_annual_fee").val(price);
            }

            function updateSummaryTotal() {
                var summaryTotal = 0;
                $(".summary-price").each(function(index, obj){
                    var eachPrice = $(obj).text();
                    var priceArr = eachPrice.split("$");
                    summaryTotal += parseFloat(priceArr[1]);
                });

                $(".total-summary-price").html("<h6>$"+summaryTotal+"</h6>");
            }

            function updateKeyPersonnelSummary() {
                
                var chosen_route = $("#chosen_route").val();

                var directors = $("input.director-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var director_address = $("input.director-address").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var director_address_2 = $("input.director-address-2").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var director_address_3 = $("input.director-address-3").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var director_address_4 = $("select.director-address-4").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var director_telephone = $("input.director-telephone").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });

                var secretaries = $("input.secretary-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var secretary_address = $("input.secretary-address").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var secretary_address_2 = $("input.secretary-address-2").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var secretary_address_3 = $("input.secretary-address-3").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var secretary_address_4 = $("select.secretary-address-4").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var secretary_telephone = $("input.secretary-telephone").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });

                var shareholders = $("input.shareholder-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var shareholder_amounts = $("input.shareholder-amount").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var shareholder_address = $("input.shareholder-address").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var shareholder_address_2 = $("input.shareholder-address-2").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var shareholder_address_3 = $("input.shareholder-address-3").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var shareholder_address_4 = $("select.shareholder-address-4").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });
                var shareholder_telephone = $("input.shareholder-telephone").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });

                var services = $("input.service-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });                
                var services_ids = $("input.service-id").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });     
                var services_countries = [];
                var services_countries_ids = [];
                var services_prices = [];
                var services_credit_card_counts = [];                

                for(index = 0; index < services_ids.length; index++) {
                    services_countries_ids[index] = $("input.service-"+services_ids[index].value+"-country-id").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                    services_countries[index] = $("input.service-"+services_ids[index].value+"-country").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                    services_prices[index] = $("input.service-"+services_ids[index].value+"-price").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                    services_credit_card_counts[index] = $("input.service-"+services_ids[index].value+"-credit-card-count").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                }

                var info_services = $("input.info-service-id").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var info_services_names = $("input.info-service-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });

                for(index =0; index < info_services.length; index++) {
                    if(info_services_names[index] && info_services_names[index].name) info_services[index].service_name = info_services_names[index].name;
                    if(info_services_names[index] && info_services_names[index].value) info_services[index].service_value = info_services_names[index].value;
                }

                var selectedData = [];

                // amend shareholders
                for(index = 0; index < shareholders.length; index++) {
                    if(shareholder_amounts[index] && shareholder_amounts[index].name) shareholders[index].amount_name = shareholder_amounts[index].name;
                    if(shareholder_amounts[index] && shareholder_amounts[index].value) shareholders[index].amount_value = shareholder_amounts[index].value;

                    if(shareholder_address[index] && shareholder_address[index].name) shareholders[index].address_name = shareholder_address[index].name;
                    if(shareholder_address[index] && shareholder_address[index].value) shareholders[index].address_value = shareholder_address[index].value;

                    if(shareholder_address_2[index] && shareholder_address_2[index].name) shareholders[index].address_2_name = shareholder_address_2[index].name;
                    if(shareholder_address_2[index] && shareholder_address_2[index].value) shareholders[index].address_2_value = shareholder_address_2[index].value;

                    if(shareholder_address_3[index] && shareholder_address_3[index].name) shareholders[index].address_3_name = shareholder_address_3[index].name;
                    if(shareholder_address_3[index] && shareholder_address_3[index].value) shareholders[index].address_3_value = shareholder_address_3[index].value;

                    if(shareholder_address_4[index] && shareholder_address_4[index].name) shareholders[index].address_4_name = shareholder_address_4[index].name;
                    if(shareholder_address_4[index] && shareholder_address_4[index].value) shareholders[index].address_4_value = shareholder_address_4[index].value;

                    if(shareholder_telephone[index] && shareholder_telephone[index].name) shareholders[index].telephone_name = shareholder_telephone[index].name;
                    if(shareholder_telephone[index] && shareholder_telephone[index].value) shareholders[index].telephone_value = shareholder_telephone[index].value;
                }

                // amend directors
                for(index = 0; index < directors.length; index++) {                    
                    if(director_address[index] && director_address[index].name) directors[index].address_name = director_address[index].name;
                    if(director_address[index] && director_address[index].value) directors[index].address_value = director_address[index].value;

                    if(director_address_2[index] && director_address_2[index].name) directors[index].address_2_name = director_address_2[index].name;
                    if(director_address_2[index] && director_address_2[index].value) directors[index].address_2_value = director_address_2[index].value;

                    if(director_address_3[index] && director_address_3[index].name) directors[index].address_3_name = director_address_3[index].name;
                    if(director_address_3[index] && director_address_3[index].value) directors[index].address_3_value = director_address_3[index].value;

                    if(director_address_4[index] && director_address_4[index].name) directors[index].address_4_name = director_address_4[index].name;
                    if(director_address_4[index] && director_address_4[index].value) directors[index].address_4_value = director_address_4[index].value;

                    if(director_telephone[index] && director_telephone[index].name) directors[index].telephone_name = director_telephone[index].name;
                    if(director_telephone[index] && director_telephone[index].value) directors[index].telephone_value = director_telephone[index].value;
                }

                if(secretaries.length > 0) {
                    if(secretary_address[0] && secretary_address[0].name) secretaries[0].address_name = secretary_address[0].name;
                    if(secretary_address[0] && secretary_address[0].value) secretaries[0].address_value = secretary_address[0].value;

                    if(secretary_address_2[0] && secretary_address_2[0].name) secretaries[0].address_2_name = secretary_address_2[0].name;
                    if(secretary_address_2[0] && secretary_address_2[0].value) secretaries[0].address_2_value = secretary_address_2[0].value;

                    if(secretary_address_3[0] && secretary_address_3[0].name) secretaries[0].address_3_name = secretary_address_3[0].name;
                    if(secretary_address_3[0] && secretary_address_3[0].value) secretaries[0].address_3_value = secretary_address_3[0].value;    

                    if(secretary_address_4[0] && secretary_address_4[0].name) secretaries[0].address_4_name = secretary_address_4[0].name;
                    if(secretary_address_4[0] && secretary_address_4[0].value) secretaries[0].address_4_value = secretary_address_4[0].value;    

                    if(secretary_telephone[0] && secretary_telephone[0].name) secretaries[0].telephone_name = secretary_telephone[0].name;
                    if(secretary_telephone[0] && secretary_telephone[0].value) secretaries[0].telephone_value = secretary_telephone[0].value;    
                }
                

                // console.log(shareholders)            
                // console.log(services_prices);
                // console.log(services_credit_card_counts);
                // console.log(services);

                for(index = 0; index < services.length; index++) {
                    if(services_ids[index] && services_ids[index].name) services[index].service_id_name = services_ids[index].name;
                    if(services_ids[index] && services_ids[index].value) services[index].service_id_value = services_ids[index].value;

                    services[index].countries = services_countries[index];

                    $.each(services[index].countries, function(i, v){
                        if(services_prices[index].length > 0) {
                            if(services_credit_card_counts[index].length > 0) {
                                if(services_credit_card_counts[index][i]) {
                                    v.service_country_id_name = services_countries_ids[index][i].name;
                                    v.service_country_id_value = services_countries_ids[index][i].value;                            
                                    v.service_price_name = services_prices[index][i].name;
                                    var total_credit_card_price = parseFloat(services_prices[index][i].value) * parseFloat(services_credit_card_counts[index][i].value);
                                    v.service_price_value = total_credit_card_price;
                                    v.services_credit_card_counts_name = services_credit_card_counts[index][i].name;
                                    v.services_credit_card_counts_value = services_credit_card_counts[index][i].value;                                      
                                }                                
                            }else {
                                v.service_country_id_name = services_countries_ids[index][i].name;
                                v.service_country_id_value = services_countries_ids[index][i].value;
                                v.service_price_name = services_prices[index][i].name;
                                v.service_price_value = services_prices[index][i].value;
                            }                                                    
                        }                        
                    });                    
                }                

                // console.log(services);
                // console.log(info_services);
                // console.log(secretaries);
                
                selectedData["shareholders"] = shareholders;
                selectedData["directors"] = directors;
                selectedData["secretaries"] = secretaries;
                selectedData["services"] = services;
                selectedData["infoservices"] = info_services;

                createTemplateAndAppendHtml("#summaryshareholder-template", selectedData, "#summaryshareholder");
                createTemplateAndAppendHtml("#summarydirector-template", selectedData, "#summarydirector");
                createTemplateAndAppendHtml("#summarysecretary-template", selectedData, "#summarysecretary");
                createTemplateAndAppendHtml("#summaryservice-template", selectedData, "#summaryservice");
                createTemplateAndAppendHtml("#summaryinfoservice-template", selectedData, "#summaryinfoservice");

                // temp fix
                $("#summarydirector").removeClass("half-field-container-2");
                $("#summarysecretary").removeClass("half-field-container-2");

                $("#summary_total_share").val($("#total_share").val());

                on_nominee_switch_change("director", $("input#nominee_director"), prices["directors"]);
                on_nominee_switch_change("shareholder", $("input#nominee_shareholder"), prices["shareholders"]);
                on_nominee_switch_change("secretary", $("input#nominee_secretary"), prices["secretaries"]);                                     

                updateSummaryTotal();

                initFileUpload($(".passport_upload"));
                initFileUpload($(".bill_upload"));

                
                var $form4 = $("#registration-page-form-4");
                validateForm($form4, 4);

            }

            function updateOnJurisdictionChange(selectedCompanyTypeName, selectedCompanyTypePrice, selectedCompanyTypeId){
                appendToHtml(selectedCompanyTypeName, ".summaryjurisdiction-name");
                appendToHtml(selectedCompanyTypeName, "#jurisdiction-name");
                appendToHtml("$"+selectedCompanyTypePrice, "#jurisdiction-price");   
                update_input_val(selectedCompanyTypeId, "#jurisdiction_id"); // summary form
            }

            function isNumeric(n) {
              return !isNaN(parseFloat(n)) && isFinite(n);
            }


            ////////////
            //// EVENTS
            ////////////

            $(".next-btn").on("click", function(e){
                e.preventDefault();   

                $("#new-incorporation").prop( "checked", true );

                changeNextStep($(this).data("id"), $(this).data("hash"));
                if($(this).data("id")==2) {                    

                    update_input_val(1, "#chosen_route");
                    on_route_change(1);
                }
                if($(this).data("id")==4) {
                    updateKeyPersonnelSummary();
                }
            });

            $(".back-btn").on("click", function(e){
                e.preventDefault();                
                changePrevStep($(this).data("id"), $(this).data("hash"));
            });

            /////

            $("#step-2").on("click", ".add-more", function(e){
                e.preventDefault();
                
                if($(this).parent().find(".pasteclone").children(".field-container").length < 6) {
                    cloneForm($(this).parent().find(".cloneable"))
                    updateClonedFields($(this).parent().find(".pasteclone"), $(this).data("selector"));     
                }else {
                    alert("Can\'t add more than is 6 fields");
                }
                
            });

            $("#step-4").on("click", ".edit-summary-btn", function(e){
                e.preventDefault();

                $(this).parent().parent().parent().parent().find(".edit-form").show();
                $(this).parent().parent().parent().parent().find(".person-info").hide();
            });

            $("#step-4").on("click", ".save-summary-btn", function(e){
                e.preventDefault();

                var editForm = $(this).parent().parent().parent().find(".edit-form");
                var personInfo = $(this).parent().parent().parent().find(".person-info");                

                editFromValues = editForm.find(".custom-input-class").serializeArray();

                // console.log(editFromValues)

                var valid = true;

                $.each(editFromValues, function(i, v){
                    v.name = v.name.replace("edit_","");

                    if(v.name.indexOf("amount") != -1) {
                        if(isNumeric(v.value)==false || v.value=="" || v.value <= 0) {
                            valid = false;                            
                            $("#summary_"+v.name).addClass("error").attr("placeholder", "Invalid");
                        }else {
                            $("#summary_"+v.name).removeClass("error").attr("placeholder", "");
                            personInfo.find("."+v.name).text(v.value+" shares");    
                            personInfo.find("#"+v.name).val(parseFloat(v.value)); 
                        }
                    }else {
                        if(v.value==""){
                            valid = false;
                            $("#summary_"+v.name).addClass("error").attr("placeholder", "This field is required.");
                        }
                        else {
                            $("#summary_"+v.name).removeClass("error").attr("placeholder", "");
                            personInfo.find("."+v.name).text(v.value);    
                            personInfo.find("#"+v.name).val(v.value);    
                        }
                    }                    
                });

                if(valid) {
                    editForm.hide();
                    personInfo.show();
                }                

            });

            $("#step-4").on("change keyup", ".edit-no-of-card", function(e){
                e.preventDefault();

                var editedVal = $(this).val();
                var editedInputName = $(this).attr("name");

                var totalPrice = $(this).data("price");
                var noOfCard = $(this).data("noofcard");

                if(editedVal=="" || isNaN(editedVal)==true){
                    editedVal = 0;
                }

                var pricePerCard = totalPrice / noOfCard;

                var newtotalPrice = parseFloat(pricePerCard) * parseFloat(editedVal);                

                $(this).parent().parent().parent().find(".summary-price").text("$"+newtotalPrice);

                $(this).parent().parent().parent().find("."+editedInputName).text(editedVal);

                updateSummaryTotal();

            });

            $("#step-4").on("click", ".remove-btn", function(e){
                e.preventDefault();
                $(this).parent().parent().parent().remove();

                var selector = $(this).data("selector");
                
                if($("#"+selector).attr("type")=="checkbox") {
                    $("#"+selector).trigger("click");
                    $("#"+selector+"_annual_fee").prop("checked", false); // for secretary, direcor, shareholder validation

                    var $form4 = $("#registration-page-form-4"); // to appear error message straight away
                    if(!$form4.valid()) return false;
                }
                else {
                    // console.log(selector)
                    // console.log($("#"+selector).val());
                    $("#"+selector).val("").trigger("change");
                }

                updateSummaryTotal();
            });

            $("#step-4").on("click", ".upload-passport-btn", function(e){
                e.preventDefault();
            });

            $("#step-4").on("click", ".upload-bill-btn", function(e){
                e.preventDefault();
            });

            $("#step-4").on("click", ".go-step-2", function(e){
                e.preventDefault();
                console.log("Hi plz")
                changePrevStep(2, 2);                    
            });

            /////

            $(".step-circle").on("click", function(e){
                e.preventDefault();                         
                changePrevStep($(this).data("id"), $(this).data("hash"));                    
            });

            /////

            var prices = [];
            var newdata = [];
            $(".step-1").on("change", "select.type_of_company", function(e){
                
                var selectedCompanyTypeId = $(this).val();
                var selectedCompanyTypeName = $(this).find("option:selected").text();
                var selectedCompanyTypePrice = $(this).find("option:selected").data("prices");
                var step_id = $(this).data("id");                

                // with cross domain
                // var response = makeJsonpRequest("", "http://103.25.203.23/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");
                var response = makeJsonpRequest("", "'.SITEURL.'/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");

                // without cross domain
                // var response = makeRequest("", "'.SITEURL.'/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");                
                
                response.done(function(data, textStatus, jqXHR){                    
                    if(jqXHR.status==200) {
                        
                        newdata["companies"] = data.companies;                        
                        createTemplateAndAppendHtml("#shelf-companies-template", newdata, "#shelf-companies");    

                        prices["jurisdiction"] = data.price;                        
                        
                        newdata["shareholders"] = data.shareholders;
                        createTemplateAndAppendHtml("#shareholder-template", newdata, "#shareholder");
                        prices["shareholders"] = data.shareholders[0].price;                         

                        newdata["directors"] = data.directors;
                        createTemplateAndAppendHtml("#director-template", newdata, "#director");
                        prices["directors"] = data.directors[0].price;                         

                        newdata["secretaries"] = data.secretaries;
                        createTemplateAndAppendHtml("#secretary-template", newdata, "#secretary");
                        prices["secretaries"] = data.secretaries[0].price;                         

                        newdata["services"] = data.services;
                        createTemplateAndAppendHtml("#service-template", newdata, "#service");             

                        // console.log(data.services);

                        newdata["informationservices"] = data.informationservices;
                        createTemplateAndAppendHtml("#informationservices-template", newdata, "#informationservices");

                        // init plugin
                        initPlugin(".js-switch");
                        initPlugin(".info-service-js-switch");
                        initPlugin(".service-js-switch");

                        console.log($("#registration-page-form-2"));

                        // validate form 2
                        var $form2 = $("#registration-page-form-2");
                        validateForm($form2, 2);

                        updateOnJurisdictionChange(selectedCompanyTypeName, selectedCompanyTypePrice, selectedCompanyTypeId);

                    }
                });

                failedRequest(response);                
            });

            $("#step-3").on("change keyup", ".credit-card-count", function(e){
                if($(this).val()!==""){
                    $(this).parent().parent().find("input[type=hidden]").prop("disabled", false);
                }else {
                    $(this).parent().parent().find("input[type=hidden]").prop("disabled", true);
                }                
            });

            $("#step-1").on("click", ".new-incorporation", function(e){
                e.preventDefault();

                $("#new-incorporation").prop( "checked", true ); // check checkbox for validation purpose

                $("#new-incorporation-container").slideDown().show();                

                update_input_val(1, "#chosen_route");
                on_route_change(1);
            });

            $("#step-1").on("click", ".buy-now", function(e){
                e.preventDefault(); 

                $("#new-incorporation").prop( "checked", false ); // uncheck checkbox for validation purpose               

                update_input_val(2, "#chosen_route");
                on_route_change(2); 

                changeNextStep(2, $(this).data("hash")); 

                update_input_val($(this).data("company-id"), "#shelf_company_id"); // summary forms
                appendToHtml($(this).data("company-name"), "#summarycompany-name");               
                appendToHtml("$"+$(this).data("company-price"), "#summarycompany-price");                               
            });

            /////

            $("#step-3").on("change", "#service_country", function(e){
                e.preventDefault();
                var servicePrice = $(this).find(":selected").data("price");
                $(this).parent().parent().next("#service-price").html("$"+servicePrice);
                $(this).parent().parent().parent().find("input.service-price").val(servicePrice);
            });

            //////

            $(".company-name-choice").on("change keyup", function(e){
                var id = $(this).data("choice-id");
                var data = $(this).val();
                update_input_val(data, "#company_name_choice_"+id);
            });

            ///////

            $("#step-2").on("change keyup", ".person-input", function(e){
                var selector = $(this).data("selector");
                var field = $(this).data(selector+"-field");
                var id = $(this).data(selector+"-id");
                var data = $(this).val();
                var totalShareAmount = 0;
                
                if(selector=="shareholder" && field=="amount"){                                    
                    $(".shareholder-amount").each(function(i, obj){
                        totalShareAmount = addAmount(totalShareAmount, $(obj).val());
                    });       

                    update_input_val(totalShareAmount, "#total_share");
                }
                
                // update_input_val(data, "#summary_"+selector+"_"+id+"_"+field); 
            });

            ///////

            $(".payment-gateway-btn").on("click", function(e){

                e.preventDefault();
                // console.log($("#registration-page-form-4").serializeArray().filter(function(k) { return $.trim(k.value) != ""; }));

                var $form4 = $("#registration-page-form-4");

                if($form4.valid()) {
                    var data = $form4.serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                    var response = makeRequest(data, "'.SITEURL.'/b/admin/company", "POST");

                    // $(this).prop("disabled", true);

                    response.done(function(data, textStatus, jqXHR){                    
                        if(jqXHR.status==200) {
                            alert("Successfully submitted!");
                        }
                    });

                    failedRequest(response);
                }

            });

            ///////////
            /// INIT
            ///////////

            function init() {                

                // add operator support for handlebar
                Handlebars.registerHelper("ifCond", function (v1, operator, v2, options) {

                    switch (operator) {
                        case "==":
                            return (v1 == v2) ? options.fn(this) : options.inverse(this);
                        case "===":
                            return (v1 === v2) ? options.fn(this) : options.inverse(this);
                        case "<":
                            return (v1 < v2) ? options.fn(this) : options.inverse(this);
                        case "<=":
                            return (v1 <= v2) ? options.fn(this) : options.inverse(this);
                        case ">":
                            return (v1 > v2) ? options.fn(this) : options.inverse(this);
                        case ">=":
                            return (v1 >= v2) ? options.fn(this) : options.inverse(this);
                        case "&&":
                            return (v1 && v2) ? options.fn(this) : options.inverse(this);
                        case "||":
                            return (v1 || v2) ? options.fn(this) : options.inverse(this);
                        default:
                            return options.inverse(this);
                    }
                });

                Handlebars.registerHelper("counter", function (index){
                    return index + 1;
                });

                updateHashInURL("1");

                // with cross domain
                // var response = makeJsonpRequest("", "http://103.25.203.23/b/admin/jurisdiction", "GET");
                var response = makeJsonpRequest("", "'.SITEURL.'/b/admin/jurisdiction", "GET");

                // without cross domain
                // var response = makeRequest("", "'.SITEURL.'/b/admin/jurisdiction", "GET");

                response.done(function(data, textStatus, jqXHR){                    
                    if(jqXHR.status==200) {
                        appendToSelect(data, "type_of_company");
                    }
                });

                failedRequest(response);
            }

            init();

        });
        
    }(jQuery));
    </script>
    <script id="shelf-companies-template" type="text/x-handlebars-template">
        {{#if companies.length}}
            <p>The following shelf companies are immediately available.  You may purchase one of these or order a new incorporation below.</p>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
            <div class="field-container">  
                <div class="header">
                    <div class="each-header">
                        <h6>Company name</h6>
                    </div>
                    <div class="each-header">
                        <h6>Incorporated</h6>
                    </div>
                    <div class="each-header">
                        <h6>Price</h6>
                    </div>
                    <div class="each-header"></div>
                </div>   

                {{#companies}}                                       
                    <div class="content">
                        <div class="each-content">
                            <p>{{ name }}</p>
                        </div>
                        <div class="each-content">
                            <p>{{ incorporation_date }}</p>    
                        </div>
                        <div class="each-content">
                            <p>${{ price }}</p>
                        </div>
                        <div class="each-content">
                            <button data-company-name="{{name}}" data-company-id="{{id}}" data-company-price="{{price}}" class="custom-submit-class buy-now" data-hash="2">Buy now</button>
                        </div>                        
                    </div>                               
                {{/companies}}
            </div>    
        {{else}}   
            <p>Unfortunately no shelf companies are presently available.  You may order a new incorporation below.</p>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
        {{/if}}

        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>                         
        
        <h6>New incorporation</h6>
        <div class="pull-left">
            <p>I would like Offshore Company Solutions to arrange a new incorporation for me.</p>
            <p><span id="jurisdiction-name"></span> new incorporation charge: <span id="jurisdiction-price"></span></p>
        </div>
        <div class="pull-right">
            <button data-id="0" data-hash="#" class="custom-submit-class new-incorporation">Incorporate now</button>                    
        </div>
        <div class="clear"></div>
        <!-- <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>             -->
        
    </script>
    <script id="shareholder-template" type="text/x-handlebars-template">
        {{#if shareholders.length}}
            <h3>Shareholders</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
                     
            {{#shareholders}}
                <p>{{name_rules}}</p>
            {{/shareholders}}

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
            
            <div class="shareholder">
                <div class="field-container">
                    <div class="custom-input-container-left pull-left">
                        <label for="shareholder" class="name">Shareholder 1</label>
                        <input type="text" name="shareholder_1_name" placeholder="Name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="1" class="shareholder-name person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <label for="shareholder_1_address" class="address">Shareholder 1 address</label>
                        <input type="text" name="shareholder_1_address" placeholder="Street" data-selector="shareholder" data-shareholder-field="address" data-shareholder-id="1" class="shareholder-address person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_1_address_2" placeholder="City" data-selector="shareholder" data-shareholder-field="address_2" data-shareholder-id="1" class="shareholder-address-2 person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_1_address_3" placeholder="State" data-selector="shareholder" data-shareholder-field="address_3" data-shareholder-id="1" class="shareholder-address-3 person-input custom-input-class">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <div class="custom-input-class-select-container">            
                            <select name="shareholder_1_address_4" data-selector="shareholder" data-shareholder-field="address_4" data-shareholder-id="1" class="shareholder-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                        </div>
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_1_telephone" placeholder="Telephone" data-selector="shareholder" data-shareholder-field="telephone" data-shareholder-id="1" class="shareholder-telephone person-input custom-input-class">          
                    </div>
                    <div class="custom-input-container-right pull-right">
                        <label for="shareamount_1_amount">Number of shares</label>
                        <input type="text" name="shareamount_1_amount" placeholder="" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="1" class="shareholder-amount person-input custom-input-class" value="">
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="field-container">
                    <div class="custom-input-container-left pull-left">
                        <label for="shareholder" class="name">Shareholder 2</label>
                        <input type="text" name="shareholder_2_name" placeholder="Name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="2" class="shareholder-name person-input custom-input-class">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <label for="shareholder_2_address" class="address">Shareholder 2 address</label>
                        <input type="text" name="shareholder_2_address" placeholder="Street" data-selector="shareholder" data-shareholder-field="address" data-shareholder-id="2" class="shareholder-address person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_2_address_2" placeholder="City" data-selector="shareholder" data-shareholder-field="address_2" data-shareholder-id="2" class="shareholder-address-2 person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_2_address_3" placeholder="State" data-selector="shareholder" data-shareholder-field="address_3" data-shareholder-id="2" class="shareholder-address-3 person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <div class="custom-input-class-select-container">            
                            <select name="shareholder_2_address_4" placeholder="Country" data-selector="shareholder" data-shareholder-field="address_4" data-shareholder-id="2" class="shareholder-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                        </div>
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="shareholder_2_telephone" placeholder="Telephone" data-selector="shareholder" data-shareholder-field="telephone" data-shareholder-id="2" class="shareholder-telephone person-input custom-input-class">                
                    </div>
                    <div class="custom-input-container-right pull-right">
                        <label for="shareamount_2_amount">Number of shares</label>
                        <input type="text" name="shareamount_2_amount" placeholder="" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="2" class="shareholder-amount person-input custom-input-class" value="">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="cloneable">
                    <div class="field-container">
                        <div class="custom-input-container-left pull-left">
                            <label for="shareholder" class="name">Shareholder 3</label>
                            <input type="text" name="shareholder_3_name" placeholder="Name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="3" class="shareholder-name person-input custom-input-class">                
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <label for="shareholder_3_address" class="address">Shareholder 3 address</label>
                            <input type="text" name="shareholder_3_address" placeholder="Street" data-selector="shareholder" data-shareholder-field="address" data-shareholder-id="3" class="shareholder-address person-input custom-input-class">                
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="shareholder_3_address_2" placeholder="City" data-selector="shareholder" data-shareholder-field="address_2" data-shareholder-id="3" class="shareholder-address-2 person-input custom-input-class">                
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="shareholder_3_address_3" placeholder="State" data-selector="shareholder" data-shareholder-field="address_3" data-shareholder-id="3" class="shareholder-address-3 person-input custom-input-class">                
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <div class="custom-input-class-select-container">            
                                <select name="shareholder_3_address_4" placeholder="Country" data-selector="shareholder" data-shareholder-field="address_4" data-shareholder-id="3" class="shareholder-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                            </div>
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="shareholder_3_telephone" placeholder="Telephone" data-selector="shareholder" data-shareholder-field="telephone" data-shareholder-id="3" class="shareholder-telephone person-input custom-input-class">
                        </div>
                        <div class="custom-input-container-right pull-right">
                            <label for="shareamount_3_amount">Number of shares</label>                            
                            <input type="text" name="shareamount_3_amount" placeholder="" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="3" class="shareholder-amount person-input custom-input-class" value="">
                        </div>     
                        <div class="clear"></div>                   
                    </div>
                </div>            
                <div class="pasteclone"></div>
            </div>

            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
            <a href="#" data-selector="shareholder" class="add-more">Add More <i class="fa fa-plus"></i></a>
            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            

            <div class="field-container">
                <div class="custom-input-container-left pull-left">
                    <label for="total_share" class="align-label">Total shares to be issued</label>
                </div>
                <div class="custom-input-container-right pull-right">
                    <input type="text" name="total_share" id="total_share" class="custom-input-class" value="0">
                </div>
                <div class="clear"></div>                   
            </div>          

            <p>Should confidentiality be required, Offshore Company Solutions can arrange for the shares to be held by nominees on behalf of the above shareholders instead of registering the shares directly in the names of the shareholders.  An annual nominee shareholder fee of will apply for this service.</p>  
            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            

            <div class="pull-left">
                <p>Offshore Company Solutions to provide nominee shareholders?</p>
                <p>Annual nominee shareholder fee: {{#shareholders}}<span class="nominee-shareholder-price">${{price}}</span>{{/shareholders}}</p>
            </div>
            <div class="pull-right yesno-btn"><input type="checkbox" name="nominee_shareholder" id="nominee_shareholder" class="js-switch"></div>
            <div class="clear"></div>

            <div class="field-container">
                <div class="vc_empty_space" style="height: 20px"><span class="vc_empty_space_inner"></span></div>
                
                <!-- <div class="nominee-container hidden">
                    <div class="pull-left">
                        <label for="nominee_shareholder" class="checkbox-label align-label">Annual nominee shareholder fee:</label>
                    </div>
                    <div class="pull-right">
                        {{#shareholders}}
                                <p class="nominee-shareholder-price">${{price}}</p>
                        {{/shareholders}}
                    </div>
                    <div class="clear"></div>
                </div> -->
            </div>            

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
        {{/if}}
    </script>
    <script id="director-template" type="text/x-handlebars-template">
        {{#if directors.length}}
            <h3>Directors</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            {{#directors}}
                <p>{{name_rules}}</p>
            {{/directors}}

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            

            <p>Professional directors may be provided by Offshore Company Solutions if confidentiality is required or if double tax treaty benefits will be claimed.  An annual professional director fee will be charged for this service.</p>
            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
            
            <div class="pull-left">
                <p>Offshore Company Solutions to provide professional directors?</p>
                <p>Annual professional director fee: {{#directors}}<span class="nominee-director-price">${{price}}</span>{{/directors}}</p>
            </div>
            <div class="pull-right yesno-btn"><input type="checkbox" name="nominee_director" id="nominee_director" class="js-switch"></div>
            <div class="clear"></div>

            <div class="field-container">
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>                            
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>                                        

                <!-- <div class="nominee-container hidden">
                    <div class="pull-left">
                        <label for="nominee_director" class="checkbox-label">Annual professional director fee:</label>
                    </div>
                    <div class="pull-right">
                        {{#directors}}
                            <p class="nominee-director-price">${{price}}</p>
                        {{/directors}}
                    </div>
                    <div class="clear"></div>
               
                    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>                      
                </div>             -->
            </div>  
                    
            <div class="director key-person-info">
                <div class="field-container">
                    <label for="director" class="name">Director 1</label>
                    <input type="text" name="director_1_name" placeholder="Name" data-selector="director" data-director-field="name" data-director-id="1" class="director-name person-input custom-input-class">   
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <label for="director_1_address" class="address">Director 1 address</label>
                    <input type="text" name="director_1_address" placeholder="Street" data-selector="director" data-director-field="address" data-director-id="1" class="director-address person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_1_address_2" placeholder="City" data-selector="director" data-director-field="address_2" data-director-id="1" class="director-address-2 person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_1_address_3" placeholder="State" data-selector="director" data-director-field="address_3" data-director-id="1" class="director-address-3 person-input custom-input-class">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <div class="custom-input-class-select-container">            
                        <select name="director_1_address_4" placeholder="Country" data-selector="director" data-director-field="address_3" data-director-id="1" class="director-address-3 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                    </div>                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_1_telephone" placeholder="Telephone" data-selector="director" data-director-field="telephone" data-director-id="1" class="director-telephone person-input custom-input-class">                                
                </div>

                <div class="field-container">
                    <label for="director" class="name">Director 2</label>
                    <input type="text" name="director_2_name" placeholder="Name" data-selector="director" data-director-field="name" data-director-id="2" class="director-name person-input custom-input-class">    
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <label for="director_2_address" class="address">Director 2 address</label>
                    <input type="text" name="director_2_address" placeholder="Street" data-selector="director" data-director-field="address" data-director-id="2" class="director-address person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_2_address_2" placeholder="City" data-selector="director" data-director-field="address_2" data-director-id="2" class="director-address-2 person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_2_address_3" placeholder="State" data-selector="director" data-director-field="address_3" data-director-id="2" class="director-address-3 person-input custom-input-class">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    <div class="custom-input-class-select-container">            
                        <select name="director_2_address_4" placeholder="Country" data-selector="director" data-director-field="address_4" data-director-id="2" class="director-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                    </div>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="director_2_telephone" placeholder="Telephone" data-selector="director" data-director-field="telephone" data-director-id="2" class="director-telephone person-input custom-input-class">
                </div>
                
                <div class="cloneable">
                    <div class="field-container">
                        <label for="director" class="name">Director 3</label>
                        <input type="text" name="director_3_name" placeholder="Name" data-selector="director" data-director-field="name" data-director-id="3" class="director-name person-input custom-input-class">    
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <label for="director_3_address" class="address">Director 3 address</label>
                        <input type="text" name="director_3_address" placeholder="Street" data-selector="director" data-director-field="address" data-director-id="3" class="director-address person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="director_3_address_2" placeholder="City" data-selector="director" data-director-field="address_2" data-director-id="3" class="director-address-2 person-input custom-input-class">                
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="director_3_address_3" placeholder="State" data-selector="director" data-director-field="address_3" data-director-id="3" class="director-address-3 person-input custom-input-class">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                        <div class="custom-input-class-select-container">            
                            <select name="director_3_address_4" placeholder="Country" data-selector="director" data-director-field="address_4" data-director-id="3" class="director-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                        </div>
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                        <input type="text" name="director_3_telephone" placeholder="Telephone" data-selector="director" data-director-field="telephone" data-director-id="3" class="director-telephone person-input custom-input-class">
                    </div>
                </div>
                <div class="pasteclone"></div>

                <a href="#" data-selector="director" class="add-more">Add More <i class="fa fa-plus"></i></a>            
            </div>
            

        {{/if}}
    </script>

    <script id="secretary-template" type="text/x-handlebars-template">
        {{#if secretaries.length}}            
            <h3>Secretary</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

            {{#secretaries}}
                <p>{{name_rules}}</p>
            {{/secretaries}}

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            

            <p>A professional secretary may be provided by Offshore Company Solutions, with the necessary experience to handle the responsibilities carried by this position.  An annual company secretary fee will apply for this service.</p>

            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
            <div class="pull-left">
                <p>Offshore Company Solutions to provide a company secretary?</p>
                <p>Annual company secretary fee: {{#secretaries}}<span class="nominee-secretary-price">${{price}}</span>{{/secretaries}}</p>
            </div>
            <div class="pull-right yesno-btn"><input type="checkbox" name="nominee_secretary" id="nominee_secretary" class="js-switch"></div>
            <div class="clear"></div>

            <div class="field-container">
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>                            
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>  

                <div class="nominee-container hidden">
                    <div class="pull-left">
                        <label for="nominee_secretary" class="checkbox-label">Annual company secretary fee:</label>
                    </div>
                    <div class="pull-right">
                        {{#secretaries}}
                            <p class="nominee-secretary-price">${{price}}</p>
                        {{/secretaries}}
                    </div>
                    <div class="clear"></div>
               

                    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>                                    

                </div>                         
            </div>                
            <div class="secretary key-person-info">
                <div class="field-container">
                    <label for="secretary" class="name">Secretary name</label>
                    <input type="text" name="secretary_1_name" placeholder="Name" data-selector="secretary" data-secretary-field="name" data-secretary-id="1" class="secretary-name person-input custom-input-class">     
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <label for="secretary_1_address" class="address">Secretary address</label>
                    <input type="text" name="secretary_1_address" placeholder="Street" data-selector="secretary" data-secretary-field="address" data-secretary-id="1" class="secretary-address person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="secretary_1_address_2" placeholder="City" data-selector="secretary" data-secretary-field="address_2" data-secretary-id="1" class="secretary-address-2 person-input custom-input-class">                
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="secretary_1_address_3" placeholder="State" data-selector="secretary" data-secretary-field="address_3" data-secretary-id="1" class="secretary-address-3 person-input custom-input-class">                               
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <div class="custom-input-class-select-container">            
                        <select name="secretary_1_address_4" placeholder="Country" data-selector="secretary" data-secretary-field="address_4" data-secretary-id="1" class="secretary-address-4 person-input custom-input-class"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                    </div>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                    <input type="text" name="secretary_1_telephone" placeholder="Telephone" data-selector="secretary" data-secretary-field="telephone" data-secretary-id="1" class="secretary-telephone person-input custom-input-class">                               
                </div>
            </div>   
        {{/if}}
    </script>

    <script id="service-template" type="text/x-handlebars-template">
        {{#if services.length}}
            <p>Now that we know how to structure your company, would you like to add any of the following services?</p>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            {{#services}}
                <div class="field-container">
                    {{#ifCond name "==" "Registered office annual fee (compulsory)"}}

                    {{else}}
                    <h4 class="pull-left">{{name}}</h4>
                    <h4 class="pull-right"></h4>
                    <div class="clear"></div>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    {{/ifCond}}
                    {{#ifCond name "==" "Bank accounts"}}
                        <p>A bank account may be opened in the following jurisdictions for your company (multiple jurisdictions may be selected):</p>                    
                    {{/ifCond}}
                    {{#ifCond name "==" "Credit/debit cards"}}
                        <p>Credit or debit cards are available in the following jurisdictions for your company.  Note that a bank account must be opened at the same bank before credit or debit cards may be issued:</p>                    
                    {{/ifCond}}

                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="country-options-container pull-left">                                                                
                        <input type="hidden" name="service_{{counter @index}}_id" class="service-id" value="{{id}}">
                        <input type="hidden" name="service_{{counter @index}}_name" class="service-name" value="{{name}}">
                        <!-- <select id="service_country" name="service_{{counter @index}}_country" class="service-country custom-input-class">
                            <option value="" data-price="0.00" selected="selected">Please Select</option>
                            {{#countries}}
                            <option value="{{pivot.id}}" data-price="{{pivot.price}}">{{name}}</option>
                            {{/countries}}
                        </select> -->
                        {{#ifCond name "==" "Registered office annual fee (compulsory)"}}
                            {{#countries}}                           
                            <input type="hidden" name="service_{{../id}}_country_{{counter @index}}" class="service-{{../id}}-country-{{id}} service-{{../id}}-country" value="{{name}}">
                            <input type="hidden" name="service_{{../id}}_price_{{counter @index}}" class="service-{{../id}}-country-{{id}} service-{{../id}}-price" value="{{pivot.price}}">
                            <input type="hidden" name="service_{{../id}}_country_{{counter @index}}_id" id="service_{{../id}}_country_{{counter @index}}_id" data-service-name="{{../name}}" data-service-id="{{../id}}" data-country-id="{{id}}" value="{{pivot.id}}" class="service-{{../id}}-country-id">
                            {{/countries}}
                        {{else}}
                            <div class="header">
                                <div class="col-1-header">
                                    {{#ifCond name "==" "Credit/debit cards"}}
                                    <h6 for="service_country">Bank location</h6>
                                    {{else}}
                                    <h6 for="service_country">Location</h6>
                                    {{/ifCond}}
                                </div>
                                <div class="col-2-header">
                                    {{#ifCond name "==" "Credit/debit cards"}}
                                    <h6 for="service_country">Charge per card</h6>
                                    {{else}}
                                    <h6 for="service_country">Price</h6>
                                    {{/ifCond}}
                                </div>
                                <div class="col-3-header">
                                    {{#ifCond name "==" "Credit/debit cards"}}
                                    <h6 for="service_country">No. of cards to issue</h6>
                                    {{else}}
                                    <h6 for="service_country">Open account</h6>
                                    {{/ifCond}}
                                </div>
                            </div>
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                            {{#countries}}
                                <div class="each-country">
                                    <div class="col-1">
                                        <div id="service-country" class="service-country"><p>{{name}}</p></div>
                                        <input type="hidden" name="service_{{../id}}_country_{{counter @index}}" class="service-{{../id}}-country-{{id}} service-{{../id}}-country" value="{{name}}" disabled="disabled">
                                    </div>
                                    <div class="col-2">
                                        <div id="service-price" class="service-price price"><p>${{pivot.price}}</p></div>
                                        <input type="hidden" name="service_{{../id}}_price_{{counter @index}}" class="service-{{../id}}-country-{{id}} service-{{../id}}-price" value="{{pivot.price}}" disabled="disabled">
                                    </div>               
                                    {{#ifCond ../name "==" "Credit/debit cards"}}           
                                    <div class="col-3">
                                        <input type="text" name="service_{{../id}}_country_{{counter @index}}_no_of_card" id="service_{{../id}}_country_{{counter @index}}_no_of_card" class="credit_card_in_country_{{id}} service-{{../id}}-credit-card-count credit-card-count custom-input-class-2" disabled="disabled">
                                        <input type="hidden" name="service_{{../id}}_country_{{counter @index}}_id" value="{{pivot.id}}" class="service-{{../id}}-country-id">                
                                    </div>        
                                    {{else}}
                                    <div class="col-3">
                                        <input type="checkbox" name="service_{{../id}}_country_{{counter @index}}_id" id="service_{{../id}}_country_{{counter @index}}_id" data-service-name="{{../name}}" data-service-id="{{../id}}" data-country-id="{{id}}" value="{{pivot.id}}" class="service-js-switch service-{{../id}}-country-id">
                                    </div>
                                    {{/ifCond}}
                                    
                                </div>
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            {{/countries}}
                        {{/ifCond}}
                    </div>
                    <div class="clear"></div>
                </div>    
            {{/services}}
        {{/if}}
    </script>  

    <script id="informationservices-template" type="text/x-handlebars-template">
        {{#if informationservices.length}}
            <h3>Other services</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
            
            <div class="field-container">
                <p>Please let us know whether you would like to receive information on any of the following:</p>                    
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                {{#informationservices}}
                    <div class="field-container">
                        <div class="pull-left">
                            <label for="info_services[]" class="checkbox-label">{{name}}</label>
                            <input type="hidden" name="info_services_name[]" class="info-service-name" value="{{name}}">
                        </div>
                        <div class="pull-right">
                            <input type="checkbox" name="info_services_id[]" value="{{id}}" class="info-service-id info-service-js-switch">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                {{/informationservices}}
            </div>            
        {{/if}}
    </script>    

    <script id="summaryshareholder-template" type="text/x-handlebars-template">
        {{#shareholders}}
            {{#if value}}
                {{#if @first}}
                    <h4>Shareholders</h4>                    
                    <input type="hidden" name="shareholder_count" value="{{../shareholders.length}}">
                {{/if}}
                <div class="field-container half-field-container-2">
                    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container col-1 pull-left">
                        <div class="person-info">                
                            <p class="shareholder_{{counter @index}}_name">{{value}}</label>
                            <p class="shareholder_{{counter @index}}_address">{{address_value}}</p>
                            <p class="shareholder_{{counter @index}}_address_2">{{address_2_value}}</p>
                            <p class="shareholder_{{counter @index}}_address_3">{{address_3_value}}</p>
                            <p class="shareholder_{{counter @index}}_address_4">{{address_4_value}}</p>
                            <p class="shareholder_{{counter @index}}_telephone">{{telephone_value}}</p>
                            <input type="hidden" name="shareholder_{{counter @index}}_name" id="shareholder_{{counter @index}}_name" value="{{value}}">
                            <input type="hidden" name="shareholder_{{counter @index}}_address" id="shareholder_{{counter @index}}_address" value="{{address_value}}">
                            <input type="hidden" name="shareholder_{{counter @index}}_address_2" id="shareholder_{{counter @index}}_address_2" value="{{address_2_value}}">
                            <input type="hidden" name="shareholder_{{counter @index}}_address_3" id="shareholder_{{counter @index}}_address_3" value="{{address_3_value}}">
                            <input type="hidden" name="shareholder_{{counter @index}}_address_4" id="shareholder_{{counter @index}}_address_4" value="{{address_3_value}}">
                            <input type="hidden" name="shareholder_{{counter @index}}_telephone" id="shareholder_{{counter @index}}_telephone" value="{{address_3_value}}">
                        </div>
                        <div class="edit-form">
                            <input type="text" name="edit_shareholder_{{counter @index}}_name" id="summary_{{name}}" value="{{value}}" class="name-edit-input custom-input-class one-row">
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="edit_shareholder_{{counter @index}}_address" id="summary_{{address_name}}" value="{{address_value}}" class="address-edit-input custom-input-class one-row">
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="edit_shareholder_{{counter @index}}_address_2" id="summary_{{address_2_name}}" value="{{address_2_value}}" class="address-2-edit-input custom-input-class one-row">
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="edit_shareholder_{{counter @index}}_address_3" id="summary_{{address_3_name}}" value="{{address_3_value}}" class="address-3-edit-input custom-input-class one-row">
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <div class="custom-input-class-select-container">            
                                <select name="edit_shareholder_{{counter @index}}_address_4" id="summary_{{address_4_name}}" value="{{address_4_value}}" class="address-4-edit-input custom-input-class one-row"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                            </div>    
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <input type="text" name="edit_shareholder_{{counter @index}}_telephone" id="summary_{{telephone_name}}" value="{{telephone_value}}" class="telephone-edit-input custom-input-class one-row">                        
                            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                            <button class="save-summary-btn custom-submit-class custom-submit-class-2">Save</button>                     
                        </div>                      
                    </div>
                    <div class="input-container text-container col-2 pull-left">                
                        <div class="person-info">
                            <p class="shareholder_{{counter @index}}_amount">{{amount_value}} shares</p>                
                            <input type="hidden" name="shareholder_{{counter @index}}_amount" id="shareholder_{{counter @index}}_amount" value="{{amount_value}}" class="custom-input-class small-input-2 one-row shareholder-amount-input">        
                        </div>
                        <div class="edit-form">
                            <input type="text" name="edit_shareholder_{{counter @index}}_amount" id="summary_shareholder_{{counter @index}}_amount" value="{{amount_value}}" class="custom-input-class small-input-2 one-row shareholder-amount-input">                            
                        </div>
                    </div>
                    <div class="col-3 pull-right upload-col-container">       
                        <div class="edit-btn-container">    
                            <span class="btn btn-success fileinput-button">                                                             
                                <button class="edit-summary-btn custom-submit-class custom-submit-class-2">Edit</button>
                            </span>
                        </div>
                        <div class="upload-btn-container">
                            <input type="hidden" name="summary_shareholder_{{counter @index}}_passport" />
                            <span class="btn btn-success fileinput-button">                            
                                <button class="upload-passport-btn custom-submit-class custom-submit-class-2">Upload passport</button>
                                <!-- The file input field used as target for the file upload widget -->
                                <input class="passport_upload" type="file" name="files" data-fieldname="shareholder_{{counter @index}}_passport" />
                            </span>
                            <!-- The container for the uploaded files -->
                            <div id="shareholder_{{counter @index}}_passport_files" class="files"></div>
                        </div>
                        
                        <div class="upload-btn-container">
                            <input type="hidden" name="summary_shareholder_{{counter @index}}_bill" />
                            <span class="btn btn-success fileinput-button">                            
                                <button class="upload-bill-btn custom-submit-class custom-submit-class-2">Upload utility bill</button>
                                <!-- The file input field used as target for the file upload widget -->                            
                                <input class="bill_upload" type="file" name="files" data-fieldname="shareholder_{{counter @index}}_bill" />
                            </span>                
                            <!-- The container for the uploaded files -->
                            <div id="shareholder_{{counter @index}}_bill_files" class="files"></div>        
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>                
                {{#if @last}} 
                    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
                    <input type="checkbox" name="nominee_shareholder_annual_fee" id="nominee_shareholder_annual_fee" checked="checked" value="">
                    <div class="summary-shareholder-price-container">                        
                        <div class="nominee-lbl-container pull-left col-1"><p>Nominee shareholders annual fee</p></div>
                        <div class="pull-left col-2"><div class="nominee-cta-container"><button data-selector="nominee_shareholder" class="remove-btn custom-submit-class custom-submit-class-2">Remove</button></div></div>
                        <div id="summary-shareholder-price" class="col-3 price summary-price pull-right"><p>$0</p></div>
                        <div class="clear"></div>
                    </div>
                    
                    <input type="hidden" name="total_share" id="summary_total_share" disabled="true" class="custom-input-class small-input-2">
                {{/if}}                                              
            {{/if}}
        {{/shareholders}}
    </script> 

    <script id="summarydirector-template" type="text/x-handlebars-template">                
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
        <h4>Directors</h4>        
        {{#if directors.length}}                        
            {{#directors}}
                {{#if value}}     
                    {{#if @first}}<input type="hidden" name="director_count" value="{{../directors.length}}">{{/if}}
                    <div class="field-container half-field-container-2">
                        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>   

                        <div class="input-container col-1 pull-left">                
                            <div class="person-info">
                                <p class="director_{{counter @index}}_name">{{value}}</label>
                                <p class="director_{{counter @index}}_address">{{address_value}}</p>
                                <p class="director_{{counter @index}}_address_2">{{address_2_value}}</p>
                                <p class="director_{{counter @index}}_address_3">{{address_3_value}}</p>
                                <p class="director_{{counter @index}}_address_4">{{address_4_value}}</p>
                                <p class="director_{{counter @index}}_telephone">{{telephone_value}}</p>
                                <input type="hidden" name="director_{{counter @index}}_name" id="director_{{counter @index}}_name" value="{{value}}">
                                <input type="hidden" name="director_{{counter @index}}_address" id="director_{{counter @index}}_address" value="{{address_value}}">
                                <input type="hidden" name="director_{{counter @index}}_address_2" id="director_{{counter @index}}_address_2" value="{{address_2_value}}">
                                <input type="hidden" name="director_{{counter @index}}_address_3" id="director_{{counter @index}}_address_3" value="{{address_3_value}}">
                                <input type="hidden" name="director_{{counter @index}}_address_4" id="director_{{counter @index}}_address_4" value="{{address_4_value}}">
                                <input type="hidden" name="director_{{counter @index}}_telephone" id="director_{{counter @index}}_telephone" value="{{telephone_value}}">
                            </div>
                            <div class="edit-form">
                                <input type="text" name="edit_director_{{counter @index}}_name" id="summary_{{name}}" value="{{value}}" class="name-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_director_{{counter @index}}_address" id="summary_{{address_name}}" value="{{address_value}}" class="address-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_director_{{counter @index}}_address_2" id="summary_{{address_2_name}}" value="{{address_2_value}}" class="address-2-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_director_{{counter @index}}_address_3" id="summary_{{address_3_name}}" value="{{address_3_value}}" class="address-3-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <div class="custom-input-class-select-container">            
                                    <select name="edit_director_{{counter @index}}_address_4" id="summary_{{address_4_name}}" value="{{address_4_value}}" class="address-4-edit-input custom-input-class one-row"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                                </div>
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_director_{{counter @index}}_telephone" id="summary_{{telephone_name}}" value="{{telephone_value}}" class="telephone-edit-input custom-input-class one-row">

                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <button class="save-summary-btn custom-submit-class custom-submit-class-2">Save</button>                     
                            </div>
                        </div>
                        <div class="col-2 text-container pull-left">
                            
                        </div>
                        <div class="col-3 pull-right">
                            <div class="edit-btn-container">    
                                <span class="btn btn-success fileinput-button">                                                             
                                    <button class="edit-summary-btn custom-submit-class custom-submit-class-2">Edit</button>    
                                </span>
                            </div>
                            <div class="upload-btn-container">
                                <input type="hidden" name="summary_director_{{counter @index}}_passport" />
                                <span class="btn btn-success fileinput-button">                            
                                    <button class="upload-passport-btn custom-submit-class custom-submit-class-2">Upload passport</button>
                                    <!-- The file input field used as target for the file upload widget -->
                                    <input class="passport_upload" type="file" name="files" data-fieldname="director_{{counter @index}}_passport" />
                                </span>
                                <!-- The container for the uploaded files -->
                                <div id="director_{{counter @index}}_passport_files" class="files"></div>
                            </div>
                            
                            <div class="upload-btn-container">
                                <input type="hidden" name="summary_director_{{counter @index}}_bill" />
                                <span class="btn btn-success fileinput-button">                            
                                    <button class="upload-bill-btn custom-submit-class custom-submit-class-2">Upload utility bill</button>
                                    <!-- The file input field used as target for the file upload widget -->                            
                                    <input class="bill_upload" type="file" name="files" data-fieldname="director_{{counter @index}}_bill" />
                                </span>                
                                <!-- The container for the uploaded files -->
                                <div id="director_{{counter @index}}_bill_files" class="files"></div>        
                            </div>                            
                        </div>      
                        <div class="clear"></div>                    
                    </div>
                {{/if}}            
            {{/directors}}
        {{else}}
            <div class="vc_empty_space" style="height: 15px"><span class="vc_empty_space_inner"></span></div>            
            <input type="checkbox" name="nominee_director_annual_fee" id="nominee_director_annual_fee" value="" checked="checked">
            <div class="summary-director-price-container">                
                <p>Offshore Company Solutions to provide professional directors</p>
                <div class="vc_empty_space" style="height: 20px"><span class="vc_empty_space_inner"></span></div>        
                <div class="nominee-lbl-container col-1 pull-left"><p>Professional directors annual fee</p></div>
                <div class="col-2 pull-left"><div class="nominee-cta-container"><button data-selector="nominee_director" class="remove-btn custom-submit-class custom-submit-class-2">Remove</button></div></div>                
                <div id="summary-director-price" class="col-3 price summary-price pull-right"><p>$0</p></div>
            </div>   
            <a href="#" class="go-step-2 pull-right"><button class="custom-submit-class custom-submit-class-2">Assign Director</button></a>         
            <div class="clear"></div>
        {{/if}}            
    </script>        

    <script id="summarysecretary-template" type="text/x-handlebars-template">                
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
        <h4>Secretary</h4>
        {{#if secretaries.length}}                        
            {{#secretaries}}
                {{#if value}}
                    {{#if @first}}<input type="hidden" name="secretary_count" value="{{../secretaries.length}}">{{/if}}
                    <div class="field-container half-field-container-2">
                        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            

                        <div class="input-container col-1 pull-left">    
                            <div class="person-info">            
                                <p class="secretary_{{counter @index}}_name">{{value}}</label>
                                <p class="secretary_{{counter @index}}_address">{{address_value}}</p>
                                <p class="secretary_{{counter @index}}_address_2">{{address_2_value}}</p>
                                <p class="secretary_{{counter @index}}_address_3">{{address_3_value}}</p>
                                <p class="secretary_{{counter @index}}_address_4">{{address_4_value}}</p>
                                <p class="secretary_{{counter @index}}_telephone">{{telephone_value}}</p>
                                <input type="hidden" name="secretary_{{counter @index}}_name" id="secretary_{{counter @index}}_name" value="{{value}}">
                                <input type="hidden" name="secretary_{{counter @index}}_address" id="secretary_{{counter @index}}_address" value="{{address_value}}">
                                <input type="hidden" name="secretary_{{counter @index}}_address_2" id="secretary_{{counter @index}}_address_2" value="{{address_2_value}}">
                                <input type="hidden" name="secretary_{{counter @index}}_address_3" id="secretary_{{counter @index}}_address_3" value="{{address_3_value}}">
                                <input type="hidden" name="secretary_{{counter @index}}_address_4" id="secretary_{{counter @index}}_address_4" value="{{address_4_value}}">
                                <input type="hidden" name="secretary_{{counter @index}}_telephone" id="secretary_{{counter @index}}_telephone" value="{{telephone_value}}">
                            </div>
                            <div class="edit-form">
                                <input type="text" name="edit_secretary_{{counter @index}}_name" id="summary_{{name}}" value="{{value}}" class="name-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_secretary_{{counter @index}}_address" id="summary_{{address_name}}" value="{{address_value}}" class="address-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_secretary_{{counter @index}}_address_2" id="summary_{{address_2_name}}" value="{{address_2_value}}" class="address-2-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_secretary_{{counter @index}}_address_3" id="summary_{{address_3_name}}" value="{{address_3_value}}" class="address-3-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <div class="custom-input-class-select-container">            
                                    <select name="edit_secretary_{{counter @index}}_address_4" id="summary_{{address_4_name}}" value="{{address_4_value}}" class="address-4-edit-input custom-input-class one-row"><option value="Afghanistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote D`Ivoire">Cote D`Ivoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curacao">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea South">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option> <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="United Kingdom">United Kingdom</option> <option value="United States of America">United States of America</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option></select>     
                                </div>
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                                <input type="text" name="edit_secretary_{{counter @index}}_telephone" id="summary_{{telephone_name}}" value="{{telephone_value}}" class="address-3-edit-input custom-input-class one-row">
                                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>                                        
                                <button class="save-summary-btn custom-submit-class custom-submit-class-2">Save</button>                     
                            </div>
                        </div>      
                        <div class="col-2 text-container pull-left">
                            
                        </div>   
                        <div class="col-3 pull-right">                                                            
                            <div class="edit-btn-container">    
                                <span class="btn btn-success fileinput-button">                                                             
                                    <button class="edit-summary-btn custom-submit-class custom-submit-class-2">Edit</button>    
                                </span>
                            </div>
                            <div class="upload-btn-container">
                                <input type="hidden" name="summary_secretary_{{counter @index}}_passport" />
                                <span class="btn btn-success fileinput-button">                            
                                    <button class="upload-btn upload-passport-btn custom-submit-class custom-submit-class-2">Upload passport</button>
                                    <!-- The file input field used as target for the file upload widget -->
                                    <input class="passport_upload" type="file" name="files" data-fieldname="secretary_{{counter @index}}_passport" />
                                </span>
                                <!-- The container for the uploaded files -->
                                <div id="secretary_{{counter @index}}_passport_files" class="files"></div>
                            </div>
                            
                            <div class="upload-btn-container">
                                <input type="hidden" name="summary_secretary_{{counter @index}}_bill" />
                                <span class="btn btn-success fileinput-button">                            
                                    <button class="upload-btn upload-bill-btn custom-submit-class custom-submit-class-2">Upload utility bill</button>
                                    <!-- The file input field used as target for the file upload widget -->                            
                                    <input class="bill_upload" type="file" name="files" data-fieldname="secretary_{{counter @index}}_bill" />
                                </span>                
                                <!-- The container for the uploaded files -->
                                <div id="secretary_{{counter @index}}_bill_files" class="files"></div>        
                            </div>
                        </div>  
                        <div class="clear"></div>                           
                    </div>
                {{/if}}
            {{/secretaries}}
        {{else}}    
            <div class="vc_empty_space" style="height: 15px"><span class="vc_empty_space_inner"></span></div>                    
            <input type="checkbox" name="nominee_secretary_annual_fee" id="nominee_secretary_annual_fee" value="" checked="checked">
            <div class="summary-secretary-price-container">                
                <p>Offshore Company Solutions to provide a company secretary</p>
                <div class="vc_empty_space" style="height: 20px"><span class="vc_empty_space_inner"></span></div>        
                <div class="nominee-lbl-container pull-left col-1"><p>Company secretary annual fee</p></div>
                <div class="pull-left col-2"><div class="nominee-cta-container"><button data-selector="nominee_secretary" class="remove-btn custom-submit-class custom-submit-class-2">Remove</button></div></div>                
                <div id="summary-secretary-price" class="col-3 price summary-price pull-right"><p>$0</p></div>
                <div class="clear"></div>
            </div>                          
            <a href="#" class="go-step-2 pull-right"><button class="custom-submit-class custom-submit-class-2">Assign Secretary</button></a>            
            <div class="clear"></div>
        {{/if}}     
    </script>

    <script id="summaryservice-template" type="text/x-handlebars-template">
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
        <h4>Other services</h4>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
        {{#services}}
            {{#if @first}}<input type="hidden" name="service_count" value="{{../services.length}}">{{/if}}                   
            {{#countries}}
                {{#if @first}}<input type="hidden" name="service_{{counter @../index}}_country_count" value="{{../countries.length}}">{{/if}}                   
                <input type="hidden" name="service_{{counter @../index}}_country_{{counter @index}}_id" value="{{service_country_id_value}}">

                <div class="field-container">
                    <div class="pull-left col-1">
                        {{#ifCond ../value "==" "Registered office annual fee (compulsory)"}}              
                            <p>{{../value}}</p>
                        {{else}}
                            {{#ifCond ../value "==" "Bank accounts"}}              
                            <p>Bank account in {{value}}</p>
                            {{else}}
                            <p>{{../value}} in {{value}} {{#if services_credit_card_counts_value}} <!-- - <span class="service_{{counter @../index}}_country_{{counter @index}}_no_of_card">{{services_credit_card_counts_value}}</span> cards --> {{/if}}</p>
                            {{/ifCond}}                            
                        {{/ifCond}}
                    </div>
                    <div class="pull-left col-2">
                        <div class="service-cta-container">
                        {{#if services_credit_card_counts_value}}
                            <input type="text" name="service_{{counter @../index}}_country_{{counter @index}}_no_of_card" value="{{services_credit_card_counts_value}}" class="edit-no-of-card custom-input-class small-input-2" data-price="{{service_price_value}}" data-noofcard="{{services_credit_card_counts_value}}">                            
                            <button data-selector="{{services_credit_card_counts_name}}" class="remove-btn custom-submit-class custom-submit-class-2">Remove</button>
                        {{else}}
                            {{#ifCond ../value "==" "Registered office annual fee (compulsory)"}}
                            {{else}}
                                <button data-selector="service_{{counter @../index}}_country_{{counter @index}}_id" class="remove-btn custom-submit-class custom-submit-class-2">Remove</button>
                            {{/ifCond}}
                        {{/if}}                        
                        </div>                        
                    </div>
                    <div class="price summary-price pull-right col-3">${{service_price_value}}</div>      
                    <div class="clear"></div>
                    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
                </div>                
            {{/countries}}
        {{/services}}        
    </script>

    <script id="summaryinfoservice-template" type="text/x-handlebars-template">        
        {{#infoservices}}                        
            <input type="hidden" name="{{name}}" value="{{value}}">            
        {{/infoservices}}        
    </script>

    <!-- <p class="ip_address">IP Address detected: <span class="user_ip">'.$ip.'</span></p> -->

    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    
    <div class="stepwizard">
        <div class="stepwizard-row">
            <div class="stepwizard-step">
                <button type="button" data-id="1" data-hash="1" class="step-1-circle step-1-1-circle step-1-2-circle step-circle btn btn-circle btn-primary" disabled>
                </button>
                <p>Incorporate from <br> scratch or choose <br> a shelf company</p>
            </div>
            <div class="stepwizard-step">
                <button type="button" data-id="2" data-hash="2" class="step-2-circle step-circle btn btn-default btn-circle" disabled="disabled">                    
                </button>
                <p>Tell us how to structure it</p>
            </div>
            <div class="stepwizard-step">
                <button type="button" data-id="3" data-hash="3" class="step-3-circle step-circle btn btn-default btn-circle" disabled="disabled">                    
                </button>
                <p>Select any <br> optional services</p>
            </div> 
            <div class="stepwizard-step">
                <button type="button" data-id="4" data-hash="4" class="step-4-circle step-circle btn btn-default btn-circle" disabled="disabled">                    
                </button>
                <p>Review and <br> submit your order</p>
            </div>            
        </div>
    </div>

    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>    
    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>    

    <div id="step-1" class="step-1 reg-step active">
        <form id="registration-page-form-1-1">
          <div class="field-container">

            <input type="hidden" name="chosen_route" id="chosen_route" value="">
            
            <h3>Please select the type of company you would like to purchase:</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            <!-- <label for="type_of_company">Type of company:</label> -->
            <div class="custom-input-class-select-container">            
                <select name="type_of_company" class="type_of_company custom-input-class" data-id="1-1">
                    <option value="Please select">Please select</option>                    
                </select>
            </div>

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

            <div id="shelf-companies">
            <!-- JS CONTENT GOES HERE -->
            </div>            

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>                                

            <input type="checkbox" name="new-incorporation" id="new-incorporation" value="true" />

            <div id="new-incorporation-container" style="display: none;">                

                <p>Please provide three suggestions for your company’s name in order of preference.  The company will be registered under the first name available.</p>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

                <p>The name may be in any language, provided it is expressed in Roman characters. It must end in the word "Limited" or its abbreviation "Ltd" to denote its limited liability status.</p>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
                <div class="field-container">
                    <label for="name">1st choice</label>
                    <input type="text" name="company_name[]" data-choice-id="1" class="company-name-choice custom-input-class" value="">
                </div>
                <div class="field-container">
                    <label for="name">2nd choice</label>
                    <input type="text" name="company_name[]" data-choice-id="2" class="company-name-choice custom-input-class" value="">
                </div>
                <div class="field-container">
                    <label for="name">3rd choice</label>
                    <input type="text" name="company_name[]" data-choice-id="3" class="company-name-choice custom-input-class" value="">
                </div>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

                <!-- <a href="#" id="next"><button data-id="0" data-hash="#" class="custom-submit-class back-btn">Back</button></a> -->
                <a href="#" id="next"><button data-id="2" data-hash="2" class="custom-submit-class next-btn">Next</button></a>

            </div>            
            
          </div>             
        </form>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    </div>

    <div id="step-2" class="reg-step">
        <form id="registration-page-form-2">
            
            <div id="shareholder" class="personnel">
                <!-- JS CONTENT GOES HERE -->                
            </div>

            <div id="director" class="personnel">
                <!-- JS CONTENT GOES HERE -->                                
            </div>

            <div id="secretary" class="personnel">
                <!-- JS CONTENT GOES HERE -->       
            </div>
                
            <a href="#" id="next"><button data-id="1" data-hash="1" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="3" data-hash="3" class="custom-submit-class next-btn">Next</button></a>
             
        </form>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    </div>

    <div id="step-3" class="reg-step">
        <form id="registration-page-form-3">
            
            <div class="personnel">
                <div id="service">
                    <!-- JS CONTENT GOES HERE -->
                </div>
                
                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
                
                <div id="informationservices">
                    <!-- JS CONTENT GOES HERE -->
                </div>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            </div>            
            
            <a href="#" id="next"><button data-id="2" data-hash="2" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="4" data-hash="4" class="custom-submit-class next-btn">Next</button></a>
             
        </form>
    </div>

    <div id="step-4" class="reg-step">
        <p>Below is a summary of your order.  Please review and make any corrections here.</p>
        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
        <p>In order to comply with our due diligence requirements, we are required to receive scanned copies of passports and recent utility bills (not older than 3 months) in respect of all persons listed here.  These may be uploaded using the buttons next to each person’s name.  You will not be able to finalise your order until all passports and utility bills have been uploaded.</p>

        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
        <form name="registration-page-form-4" id="registration-page-form-4">
            <div class="field-container">
                <h3 class="pull-left"></h3>
                <h4 class="pull-right">Charge</h4>
                <div class="clear"></div>
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
            </div>

            <input type="hidden" name="user_id" id="user_id" value="'.$user_id.'">
            <input type="hidden" name="jurisdiction_id" id="jurisdiction_id">

            <div id="route-1-summary" class="route-specific-summary">
                <div class="input-container pull-left">                
                    <p>New company formation - <span class="summaryjurisdiction-name"></span>:</p>
                </div>
                <div id="summaryjurisdiction-price" class="price summary-price pull-right"><p>$0</p></div>
                <div class="clear"></div>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
            
                <div id="company-names-summary">
                    <h4>Proposed company names</h4>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>

                    <div class="field-container half-field-container">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                        
                        <!-- <div class="input-container pull-left">                
                            <label for="company_type">One:</label>                            
                        </div>                 -->
                        <div class="pull-left">
                            <input type="text" name="company_name_choices[]" id="company_name_choice_1" class="custom-input-class">
                        </div>
                        <div class="clear"></div>
                    </div>            

                    <div class="field-container half-field-container">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                        
                        <!-- <div class="input-container pull-left">                
                            <label for="company_type">Two:</label>                            
                        </div> -->
                        <div class="pull-left">
                            <input type="text" name="company_name_choices[]" id="company_name_choice_2" class="custom-input-class">
                        </div>                
                        <div class="clear"></div>
                    </div>            

                    <div class="field-container half-field-container">
                        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                        
                        <!-- <div class="input-container pull-left">                
                            <label for="company_type">Three:</label>                            
                        </div> -->
                        <div class="pull-left">
                            <input type="text" name="company_name_choices[]" id="company_name_choice_3" class="custom-input-class">
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            </div>
            <div id="route-2-summary" class="route-specific-summary">
                <div class="input-container pull-left">                
                    <p>Purchase of shelf company - <span class="summaryjurisdiction-name"></span>: <span id="summarycompany-name"></span></p>                    
                    <input type="hidden" name="shelf_company_id" id="shelf_company_id" value="">
                </div>
                <div id="summarycompany-price" class="price summary-price pull-right"><p>$0</p></div>
                <div class="clear"></div>

                <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            </div>            

            <div id="summaryshareholder">
                <!-- JS CONTENT GOES HERE -->
            </div>

            <div id="summarydirector">
                <!-- JS CONTENT GOES HERE -->
            </div>            

            <div id="summarysecretary">
                <!-- JS CONTENT GOES HERE -->
            </div>

            <div id="summaryservice">
                <!-- JS CONTENT GOES HERE -->
            </div>                          

            <div id="summaryinfoservice">
                <!-- JS CONTENT GOES HERE -->
            </div>                          
                        
            <div class="field-container">
                <div class="pull-left">                
                    <h6>Total amount to pay</h6>
                </div>
                <div class="total-summary-price price pull-right"><h6>$TBC</h6></div>     
                <div class="clear"></div> 
            </div>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
            <div class="field-container">
                <input type="checkbox" name="tnc" value="yes"> <label for="tnc">I have read and agree with the Terms and conditions</label>
            </div>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
            <a href="#" id="next"><button data-id="3" data-hash="3" class="custom-submit-class back-btn">Back</button></a>
            <a href="#"><button class="custom-submit-class payment-gateway-btn">Proceed to checkout</button></a>
            
        </form>        
    </div>';
}

function custom_registration_function() { 
    registration_form();
}

// Register a new shortcode: [custom_registration]
add_shortcode( 'custom_registration', 'custom_registration_shortcode' );
 
// The callback function that will replace [book]
function custom_registration_shortcode() {
    ob_start();
    custom_registration_function();
    return ob_get_clean();
}
?>