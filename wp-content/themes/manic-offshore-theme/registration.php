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
    echo '
    <script>
    (function($) {
        $(document).ready(function(){

            ////////////
            //// CHANGE STEPS
            ////////////

            function changeNextStep(id){

                $(".active").hide().removeClass("active");
                $("#step-"+id).show().addClass("active");
                
                $(".hide-step-indicators").css("display", "table");  

                $(".btn-primary").removeClass("btn-primary").addClass("btn-default").prop( "disabled", false );
                $(".step-"+id+"-circle").removeClass("btn-default").addClass("btn-primary").prop( "disabled", true );
                
                if(id=="1-1"|| id=="1-2") {
                    $("#step-2").find(".back-btn").data("id", id);
                    $(".step-1-circle").data("id", id);

                    // add the chosen route to hidden field
                    if(id=="1-1") {
                        update_input_val(1, "#chosen_route");
                        on_route_change(1);
                    }
                    else {
                        update_input_val(2, "#chosen_route");
                        on_route_change(2); 
                    } 

                }
            }

            function changePrevStep(id) {
                
                $(".active").hide().removeClass("active");
                $("#step-"+id).show().addClass("active");                

                if(id==0){
                    $(".hide-step-indicators").css("display", "none");
                }
                
                $(".btn-primary").removeClass("btn-primary").addClass("btn-default").prop( "disabled", false );
                $(".step-"+id+"-circle").removeClass("btn-default").addClass("btn-primary").prop( "disabled", true );
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
                $fieldContainer.find("label").html(selector+" "+fieldID);

                $fieldContainer.find("."+selector+"-name").attr("name", selector+"_"+fieldID+"_name").attr("data-"+selector+"-id", fieldID).val("");
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
                        // console.log(response);
                    },
                    error: function( error ){
                        // Log any error.
                        // console.log( "ERROR:", error );
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
                if(route==1) $("#company-names-summary").show();
                else $("#company-names-summary").hide();
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

            function updateKeyPersonnelSummary() {
                
                var directors = $("input.director-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var secretaries = $("input.secretary-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var shareholders = $("input.shareholder-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var shareholder_amounts = $("input.shareholder-amount").serializeArray().filter(function(k) { return $.trim(k.value) != "" && $.trim(k.value) != 0; });

                var services = $("input.service-name").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });                
                var services_ids = $("input.service-id").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });                
                var services_countries = $("select.service-country").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });
                var services_prices = $("input.service-price").serializeArray().filter(function(k) { return $.trim(k.value) != ""; });

                var newdata = [];

                for(index = 0; index < shareholders.length; index++) {
                    shareholders[index].amount_name = shareholder_amounts[index].name;
                    shareholders[index].amount_value = shareholder_amounts[index].value;
                }

                console.log(shareholders)
                
                // need to find out about select dropdown key and value
                console.log(services_countries);

                for(index = 0; index < services.length; index++) {
                    services[index].service_id_name = services_ids[index].name;
                    services[index].service_id_value = services_ids[index].value;
                    // services[index].service_country_name = services_countries[index].name;
                    // services[index].service_country_value = services_countries[index].value;
                    services[index].service_price_name = services_prices[index].name;
                    services[index].service_price_value = services_prices[index].value;
                }
                
                newdata["shareholders"] = shareholders;
                newdata["directors"] = directors;  
                newdata["secretaries"] = secretaries;
                newdata["services"] = services;

                createTemplateAndAppendHtml("#summaryshareholder-template", newdata, "#summaryshareholder");
                createTemplateAndAppendHtml("#summarydirector-template", newdata, "#summarydirector");
                createTemplateAndAppendHtml("#summarysecretary-template", newdata, "#summarysecretary");
                createTemplateAndAppendHtml("#summaryservice-template", newdata, "#summaryservice");

                $("#summary_total_share").val($("#total_share").val());

                if ($("input#nominee_director").prop("checked")) $("#summarydirector").find("#director-price p").text("$"+prices["directors"]);
                else $("#summarydirector").find("#director-price p").text("$0.00");

                if ($("input#nominee_shareholder").prop("checked")) $("#summaryshareholder").find("#shareholder-price p").text("$"+prices["secretaries"]);
                else $("#summaryshareholder").find("#shareholder-price p").text("$0.00");                

                if ($("input#nominee_secretary").prop("checked")) $("#summarysecretary").find("#secretary-price p").text("$"+prices["secretaries"]);
                else $("#summarysecretary").find("#secretary-price p").text("$0.00");                

                $("#summaryjurisdiction-price").children("p").text("$"+prices["jurisdiction"]);

                var summaryTotal = 0;
                $(".summary-price").each(function(index, obj){
                    var eachPrice = $(obj).children("p").text();
                    var priceArr = eachPrice.split("$");
                    summaryTotal += parseFloat(priceArr[1]);
                });

                $(".total-summary-price").html("<p>$"+summaryTotal.toFixed(2)+"</p>");

            }

            ////////////
            //// EVENTS
            ////////////

            $(".next-btn").on("click", function(e){
                e.preventDefault();                
                changeNextStep($(this).data("id"));
                if($(this).data("id")==4) {
                    updateKeyPersonnelSummary();
                }
                updateHashInURL($(this).data("hash"));
                
            });

            $(".back-btn").on("click", function(e){
                e.preventDefault();                
                changePrevStep($(this).data("id"));
                updateHashInURL($(this).data("hash"));
            });

            /////

            $("#step-2").on("click", ".add-more", function(e){
                e.preventDefault();
                
                cloneForm($(this).parent().find(".cloneable"))
                updateClonedFields($(this).parent().find(".pasteclone"), $(this).data("selector"));
            });

            /////

            $(".step-circle").on("click", function(e){
                e.preventDefault();                
                changePrevStep($(this).data("id"));

                updateHashInURL($(this).data("hash"));
            });

            /////

            var prices = [];
            $(".step-1").on("change", "select.type_of_company", function(e){
                
                var selectedCompanyTypeId = $(this).val();
                var selectedCompanyTypeName = $(this).find("option:selected").text();
                var step_id = $(this).data("id");

                update_input_val(selectedCompanyTypeName, "#jurisdiction");

                // with cross domain
                var response = makeJsonpRequest("", "http://103.25.203.23/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");
                // var response = makeJsonpRequest("", "'.SITEURL.'/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");

                // without cross domain
                // var response = makeRequest("", "'.SITEURL.'/b/admin/jurisdiction/"+selectedCompanyTypeId, "GET");
                var newdata = [];
                
                response.done(function(data, textStatus, jqXHR){                    
                    if(jqXHR.status==200) {
                        
                        if(step_id=="1-2") { // if shelf
                            newdata["companies"] = data.companies;                        
                            createTemplateAndAppendHtml("#shelf-companies-template", newdata, "#shelf-companies");    
                        }

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

                    }
                });

                failedRequest(response);                
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

                // with cross domain
                var response = makeJsonpRequest("", "http://103.25.203.23/b/admin/jurisdiction", "GET");
                // var response = makeJsonpRequest("", "'.SITEURL.'/b/admin/jurisdiction", "GET");

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
            <p>Click on our shelf offerings below for details:</p>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>        
            {{#companies}}
            <div class="field-container">                
                <input type="radio" name="company_name" value="{{id}}">
                <ul>
                    <li><label for="company_name">Company name: {{name}}</label></li>
                    <li><label for="incorporation_date">Date of incorporation: {{incorporation_date}}</label></li>
                    <li><label for="price">Price: {{price}}</label></li>
                </ul>                
            </div>        
            {{/companies}}
        {{else}}   
            <p>There is no shelf company under this jurisdiction</p>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
        {{/if}}
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
                    <label for="shareholder">Shareholder 1</label>
                    <input type="text" name="shareholder_1_name" placeholder="Shareholder name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="1" class="shareholder-name person-input custom-input-class-2">                
                    <input type="text" name="shareamount_1_amount" placeholder="Share amount" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="1" class="shareholder-amount person-input custom-input-class-2" value="0">
                </div>

                <div class="field-container">
                    <label for="shareholder">Shareholder 2</label>
                    <input type="text" name="shareholder_2_name" placeholder="Shareholder name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="2" class="shareholder-name person-input custom-input-class-2">                
                    <input type="text" name="shareamount_2_amount" placeholder="Share amount" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="2" class="shareholder-amount person-input custom-input-class-2" value="0">
                </div>
                <div class="cloneable">
                    <div class="field-container">
                        <label for="shareholder">Shareholder 3</label>
                        <input type="text" name="shareholder_3_name" placeholder="Shareholder name" data-selector="shareholder" data-shareholder-field="name" data-shareholder-id="3" class="shareholder-name person-input custom-input-class-2">                
                        <input type="text" name="shareamount_3_amount" placeholder="Share amount" data-selector="shareholder" data-shareholder-field="amount" data-shareholder-id="3" class="shareholder-amount person-input custom-input-class-2" value="0">
                    </div>
                </div>            
                <div class="pasteclone"></div>
            </div>
            <a href="#" data-selector="shareholder" class="add-more">Add More <i class="fa fa-plus"></i></a>

            <div class="field-container">
                <input type="checkbox" name="nominee_shareholder" id="nominee_shareholder">
                <label for="nominee_shareholder" class="checkbox-label">Offshore Company Solutions to provide nominee shareholders</label>
            </div>

            <div class="field-container">
                <label for="total_share">Total shares to be issued</label>
                <input type="text" name="total_share" id="total_share" class="custom-input-class-2" value="0">
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
            
            <div class="director">
                <div class="field-container">
                    <label for="director">Director 1</label>
                    <input type="text" name="director_1_name" placeholder="Director name" data-selector="director" data-director-field="name" data-director-id="1" class="director-name person-input custom-input-class">                                    
                </div>

                <div class="field-container">
                    <label for="director">Director 2</label>
                    <input type="text" name="director_2_name" placeholder="Director name" data-selector="director" data-director-field="name" data-director-id="2" class="director-name person-input custom-input-class">                                    
                </div>
                
                <div class="cloneable">
                    <div class="field-container">
                        <label for="director">Director 3</label>
                        <input type="text" name="director_3_name" placeholder="Director name" data-selector="director" data-director-field="name" data-director-id="3" class="director-name person-input custom-input-class">                                    
                    </div>
                </div>
                <div class="pasteclone"></div>
            </div>
            <a href="#" data-selector="director" class="add-more">Add More <i class="fa fa-plus"></i></a>

            <div class="field-container">
                <input type="checkbox" name="nominee_director" id="nominee_director">
                <label for="nominee_director" class="checkbox-label">Offshore Company Solutions to provide professional directors</label>
            </div>              

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>              
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
            
            <div class="field-container">
                <label for="secretary">Secretary name</label>
                <input type="text" name="secretary_1_name" placeholder="Secretary name" data-selector="secretary" data-secretary-field="name" data-secretary-id="1" class="secretary-name person-input custom-input-class">                                    
            </div>                

            <div class="field-container">
                <input type="checkbox" name="nominee_secretary" id="nominee_secretary">
                <label for="nominee_secretary" class="checkbox-label">Offshore Company Solutions to provide professional directors</label>
            </div>                   
        {{/if}}
    </script>

    <script id="service-template" type="text/x-handlebars-template">
        {{#if services.length}}
            <h3>Additional services</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            {{#services}}
                <div class="field-container">
                    <h4 class="pull-left">{{name}}</h4>
                    <h4 class="pull-right"></h4>
                    <div class="clear"></div>
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="country-options-container pull-left">                
                        <label for="service_country" class="country_options_label">Country options</label>
                        <div class="custom-input-class-select-container half">
                            <input type="hidden" name="service_{{counter @index}}_id" class="service-id" value="{{id}}">
                            <input type="hidden" name="service_{{counter @index}}_name" class="service-name" value="{{name}}">
                            <select id="service_country" name="service_{{counter @index}}_country" class="service-country custom-input-class">
                                <option value="" data-price="0.00" selected="selected">Please Select</option>
                                {{#countries}}
                                <option value="{{id}}" data-price="{{pivot.price}}">{{name}}</option>
                                {{/countries}}
                            </select>
                        </div>
                    </div>
                    <div id="service-price" class="service-price price pull-right"><p>$0.00</p></div>
                    <input type="hidden" name="service_{{counter @index}}_price" class="service-price" value="0.00">
                    <div class="clear"></div>
                </div>    
            {{/services}}
        {{/if}}
    </script>  

    <script id="informationservices-template" type="text/x-handlebars-template">
        {{#if informationservices.length}}
            <h3>Additional services</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
            
            <div class="field-container">
                <h4>I would like to receive information on:</h4>                    
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                {{#informationservices}}
                    <div class="field-container">
                        <input type="checkbox" name="info_services[]" value="{{id}}"><label for="info_services[]" class="checkbox-label">{{name}}</label>
                    </div>
                {{/informationservices}}
            </div>            
        {{/if}}
    </script>     

    <script id="summarydirector-template" type="text/x-handlebars-template">
        {{#directors}}
            {{#if value}}
            <div class="field-container">
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                <div class="input-container pull-left">                
                    <label for="summary_{{name}}">Director {{counter @index}}:</label>
                    <input type="text" id="summary_{{name}}" value="{{value}}" disabled="true" class="custom-input-class">
                </div>                
                {{#if @last}} <div id="director-price" class="price summary-price pull-right"><p>$0</p></div> {{/if}}
                <div class="clear"></div>
            </div>
            {{/if}}
        {{/directors}}
    </script>    

    <script id="summaryshareholder-template" type="text/x-handlebars-template">
        {{#shareholders}}
            {{#if value}}
                <div class="field-container">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container pull-left">                
                        <label for="summary_{{name}}">Shareholder {{counter @index}}:</label>
                        <input type="text" id="summary_{{name}}" value="{{value}}" disabled="true" class="custom-input-class small-input">
                        <input type="text" id="summary_{{amount_name}}" value="{{amount_value}}" disabled="true" class="custom-input-class small-input-2">
                    </div>     
                    {{#if @last}} <div id="shareholder-price" class="price summary-price pull-right"><p>$0</p></div> {{/if}}                          
                    <div class="clear"></div>
                </div>
                {{#if @last}}
                <div class="field-container">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container pull-left">                
                        <label for="summary_total_share">Total share allocation</label>
                        <div class="small-input"></div>
                        <input type="text" id="summary_total_share" disabled="true" class="custom-input-class small-input-2">
                    </div>                
                    <div class="clear"></div>
                </div>
                {{/if}}
            {{/if}}
        {{/shareholders}}
    </script>

    <script id="summarysecretary-template" type="text/x-handlebars-template">
        {{#secretaries}}
            {{#if value}}
            <div class="field-container">
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            
                <div class="input-container pull-left">                
                    <label for="summary_{{name}}">Secretary {{counter @index}}:</label>
                    <input type="text" id="summary_{{name}}" value="{{value}}" disabled="true" class="custom-input-class">
                </div>                
                {{#if @last}}<div id="secretary-price" class="price summary-price pull-right"><p>$0</p></div>{{/if}}
                <div class="clear"></div>
            </div>
            {{/if}}
        {{/secretaries}}
    </script>

    <script id="summaryservice-template" type="text/x-handlebars-template">
        <h4>Additional services required:</h4>
        <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>        
        {{#services}}
            {{#ifCond service_price_value ">" 0}}
                <div class="field-container">
                    <div class="pull-left">                
                        <p>Yes, I will need help setting up a {{value}}</p>
                    </div>
                    <div class="price summary-price pull-right"><p>${{service_price_value}}</p></div>      
                    <div class="clear"></div>
                </div>
            {{/ifCond}}
        {{/services}}        
    </script>
    
    <div class="stepwizard hide-step-indicators">
        <div class="stepwizard-row">
            <div class="stepwizard-step">
                <button type="button" data-id="1-1" data-hash="step-1" class="step-1-circle step-1-1-circle step-1-2-circle step-circle btn btn-primary btn-circle" disabled="disabled">1</button>
            </div>
            <div class="stepwizard-step">
                <button type="button" data-id="2" data-hash="step-2" class="step-2-circle step-circle btn btn-default btn-circle" disabled="disabled">2</button>                
            </div>
            <div class="stepwizard-step">
                <button type="button" data-id="3" data-hash="step-3" class="step-3-circle step-circle btn btn-default btn-circle" disabled="disabled">3</button>                
            </div> 
            <div class="stepwizard-step">
                <button type="button" data-id="4" data-hash="step-4" class="step-4-circle step-circle btn btn-default btn-circle" disabled="disabled">4</button>                
            </div>            
        </div>
    </div>

    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

    <p class="ip_address">IP Address detected: <span class="user_ip">'.$ip.'</span></p>

    <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>

    <div id="step-0" class="active">
        <form id="registration-page-form-step">
          <div class="field-container">
            <h3>Please select:</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            <input type="hidden" name="chosen_route" id="chosen_route" value="">
            <a href="#" id="incorporate_company"><button data-id="1-1" data-hash="step-1" class="custom-submit-class next-btn">Incorporate a new company</button></a>
            <a href="#" id="shelf_company"><button data-id="1-2" data-hash="step-1" class="custom-submit-class next-btn">Purchase a shelf company</button></a>            
          </div>             
        </form>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    </div>

    <div id="step-1-1" class="step-1 reg-step">
        <form id="registration-page-form-1-1">
          <div class="field-container">
            
            <h3>Offshore Company</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            <label for="type_of_company">Type of company:</label>
            <div class="custom-input-class-select-container">            
                <select name="type_of_company" class="type_of_company custom-input-class" data-id="1-1">
                    <option value="Please select">Please select</option>                    
                </select>
            </div>

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            <h3>Offshore name suggestions</h3>

            <p>The name may be in any language, provided it is expressed in Roman characters. It must end in the word "Limited" or its abbreviation "Ltd" to denote its limited liability status.</p>
            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
            <p>Please provide three suggestions for your company’s name in order of preference.  The company will be registered under the first name available.</p>

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            <div class="field-container">
                <label for="name">1st Choice</label>
                <input type="text" name="company_name[]" data-choice-id="1" class="company-name-choice custom-input-class" value="">
            </div>
            <div class="field-container">
                <label for="name">2nd Choice</label>
                <input type="text" name="company_name[]" data-choice-id="2" class="company-name-choice custom-input-class" value="">
            </div>
            <div class="field-container">
                <label for="name">3rd Choice</label>
                <input type="text" name="company_name[]" data-choice-id="3" class="company-name-choice custom-input-class" value="">
            </div>
            <a href="#" id="next"><button data-id="0" data-hash="#" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="2" data-hash="step-2" class="custom-submit-class next-btn">Next</button></a>
            
          </div>             
        </form>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
        <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
    </div>

    <div id="step-1-2" class="step-1 reg-step">
        <form id="registration-page-form-1-2">
          <div class="field-container">
            
            <h3>Shelf Company</h3>
            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>
            
            <label for="type_of_company">Type of company:</label>
            <div class="custom-input-class-select-container">            
                <select name="type_of_company" class="type_of_company custom-input-class" data-id="1-2">
                    <option value="Please select">Please select</option>                    
                </select>
            </div>

            <div class="vc_empty_space" style="height: 29px"><span class="vc_empty_space_inner"></span></div>            
            
            <div id="shelf-companies">
            <!-- JS CONTENT GOES HERE -->
            </div>
    
            <a href="#" id="next"><button data-id="0" data-hash="#" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="2" data-hash="step-2" class="custom-submit-class next-btn">Next</button></a>
           
            
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
                
            <a href="#" id="next"><button data-id="1-1" data-hash="step-1" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="3" data-hash="step-3" class="custom-submit-class next-btn">Next</button></a>
             
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
            
            <a href="#" id="next"><button data-id="2" data-hash="step-2" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button data-id="4" data-hash="step-4" class="custom-submit-class next-btn">Next</button></a>
             
        </form>
    </div>

    <div id="step-4" class="reg-step">
        <form id="registration-page-form-4">
            <div class="field-container">
                <h3 class="pull-left">Summary</h3>
                <h4 class="pull-right">Price</h4>
                <div class="clear"></div>
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                
                <div class="input-container pull-left">                
                    <label for="jurisdiction">Jurisdiction</label>
                    <input type="text" name="jurisdiction" id="jurisdiction" disabled="true" class="custom-input-class">
                </div>
                <div id="summaryjurisdiction-price" class="price summary-price pull-right"><p>$0</p></div>
                <div class="clear"></div>
            </div>

         <!--    <div class="field-container">
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                
                <div class="input-container pull-left">                
                    <label for="company_type">Company type</label>
                    <input type="text" name="company_type" disabled="true" class="custom-input-class">
                </div>
                <div class="price pull-right"><p>$1000</p></div>
                <div class="clear"></div>
            </div> -->
            
            <div id="company-names-summary">
                <h4>Three proposed company names:</h4>
                <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>

                <div class="field-container">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container pull-left">                
                        <label for="company_type">One:</label>
                        <input type="text" id="company_name_choice_1" disabled="true" class="custom-input-class">
                    </div>                
                    <div class="clear"></div>
                </div>            

                <div class="field-container">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container pull-left">                
                        <label for="company_type">Two:</label>
                        <input type="text" id="company_name_choice_2" disabled="true" class="custom-input-class">
                    </div>                
                    <div class="clear"></div>
                </div>            

                <div class="field-container">
                    <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>
                    
                    <div class="input-container pull-left">                
                        <label for="company_type">Three:</label>
                        <input type="text" id="company_name_choice_3" disabled="true" class="custom-input-class">
                    </div>                
                    <div class="clear"></div>
                </div>
            </div>

            <h4>Key names:</h4>
            <div class="vc_empty_space" style="height: 10px"><span class="vc_empty_space_inner"></span></div>            

            <div id="summarydirector">
                <!-- JS CONTENT GOES HERE -->
            </div>

            <div id="summaryshareholder">
                <!-- JS CONTENT GOES HERE -->
            </div>

            <div id="summarysecretary">
                <!-- JS CONTENT GOES HERE -->
            </div>

            <div id="summaryservice">
                <!-- JS CONTENT GOES HERE -->
            </div>                          
                        
            <div class="field-container">
                <div class="pull-left">                
                    <p>Total Cost</p>
                </div>
                <div class="total-summary-price price pull-right"><p>$TBC</p></div>     
                <div class="clear"></div> 
            </div>
            
            <a href="#" id="next"><button data-id="3" data-hash="step-3" class="custom-submit-class back-btn">Back</button></a>
            <a href="#" id="next"><button class="custom-submit-class">Payment Gateway</button></a>
            
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