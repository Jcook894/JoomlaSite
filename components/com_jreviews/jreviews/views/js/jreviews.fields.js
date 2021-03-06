function jreviewsControlField(parent_id, cache_id) {

    var debug = false;
    var opts;
    var $_this = this;
    var $_parentElement = jQuery('#'+parent_id);
    var $fields = $_parentElement.find(':input[id^="jr_"]');
    var $fieldGroups = $_parentElement.find('fieldset[id^="group_"]');
    var $tabs = $_parentElement.find('li[id^="tab_"]');
    var $fieldRespCache = {};
    var groupArray;
    var isPaidListing = false;

    this.getTabs = function() {
        return $tabs;
    }

    this.loadData = function(options)
    {
        var cache_id_val;
        var defaults = {
            fieldLocation: 'Listing',
            entry_id: null,
            value: [], // selected value,
            page_setup: 0, // flag to indicate if this is the initialization call
            recallValues: true,
            lang: {
                'select_field': jrLanguage["field.select_field"],
                'no_results': jrLanguage["field.no_results"],
                'instructions': jrLanguage["field.ui_help"]
            }
        };

        var options = jQuery.extend(defaults, options);
        options.page_setup = options.page_setup ? 1 : 0;
        opts = options;

        // Bind tooltip and datepicker
        jreviews.tooltip();
        jreviews.datepicker();

        if(typeof(plans) != 'undefined' && opts.fieldLocation == 'Listing') {
            isPaidListing = true;
        }

        groupArray = $fieldGroups.map(function(){ return this.id.replace(/group_/g,''); });
        var selectedArray = {};
        var fieldsArray = [];
        $fields.each(function(){
            if(jQuery(this).attr('type') !='button' /*&& this.id.replace(/[^A-Z]/g, "").length == 0*/) {
                var selected = jQuery(this).data('selected');
                var fname = this.id.replace(/[^a-zA-Z_\s]+/g,'').match(/^jr_[a-z]+/).toString();
                if(selected) {
                    selectedArray[fname] = String(selected).search('_') > -1 ? selected.split('_') : [selected];
                    jQuery(this).removeAttr('data-selected');
                    jQuery(this).removeData('selected');
                }
                if(jQuery.inArray(fname,fieldsArray) == -1) {
                    fieldsArray.push(fname);
                }
            }
        });

		fieldsArray = jQuery.unique(fieldsArray);

       // Continue only if there are custom fields in the form
        if(fieldsArray.length) {

            if(!jQuery.isEmptyObject(fieldsArray)) {
                options.value = selectedArray;
            }

            if(cache_id)  {
                cache_id_val = $_parentElement.find('#'+cache_id).val();
                if(cache_id_val != undefined)  {
                    var cachedResp = $_parentElement.data('cachedResponse.'+cache_id_val);
                    if(cachedResp != undefined) {
                        $_this.processResponse(options.value, options.page_setup, cachedResp);
                        return;
                    }
                }
            }

            jreviews.dispatch({
                'method':'POST',
                'type':'json',
                'controller':'fields',
                'action':'_loadFieldData',
                'data':{
                    'data[entry_id]':options.entry_id,'data[fields]':fieldsArray,'data[value]':selectedArray,'data[page_setup]':options.page_setup,'data[fieldLocation]':options.fieldLocation,'data[referrer]':options.referrer
                },
                'onComplete':function(res){

                    $_this.processResponse(options.value, options.page_setup, res);
                    if(options.page_setup && cache_id) {
                        $_parentElement.data('cachedResponse.'+cache_id_val,res);
                    }

                    // PaidListings integration
                    if(isPaidListing) {
                        if(res.edit) {
                            JRPaid.Plans.submitFilter(0);
                        } else {
                            jQuery('#jr_paidPlans').find('input:radio:checked').click();
                        }
                    }
            }});
        }
    };

    this.getPaidFields = function() {
        var $plans = $_parentElement.find('#jr_paidPlans');
        if($plans.length > 0) {
            var plan_id = $plans.find('input:radio:checked').val();
            return plan_id > 0 ? plans['plan'+plan_id].fields : [];
        }
        var $fields = $_parentElement.find('#jr_planFields');
        if($fields.length) {
            return fields = $fields.val().split(',');
        }
    }

    this.getFieldOptions = function($jr_field,fieldType)
    {
        switch(fieldType) {
            case 'select':
            case 'selectmultiple':
                return $jr_field.children('option');
            break;
            case 'radiobuttons':
            case 'checkboxes':
            default:
                return $jr_field;
            break;
        }
    };

    this.getFieldObj = function(field_name) {
        return field_name in $fieldRespCache ? $fieldRespCache[field_name] : false;
    };

    this.getjQueryObj = function(res,field_name)
    {
        if(undefined == res.fields || !(field_name in res.fields)) return false;
        if(field_name in $fieldRespCache) return $fieldRespCache[field_name];

        // Search form: check field type to allow field conversions to other types.
        // The input is already rendered as a different type than what is stored in the DB so we make it consistent here.
        if(/*res.page_setup == 1 && */jQuery.inArray(opts.referrer,['adv_search_module']) > -1) {
            var $field = $_parentElement.find('#'+field_name);

            try {
                var type = $field.prop('type');
            } catch (err) {
                var type = $field.attr('type');
            }

            switch(type) {
                case 'select-one': res.fields[field_name].type = 'select'; break;
                case 'select-multiple': res.fields[field_name].type = 'selectmultiple'; break;
            }
        }

        var multipleValueFields = ['selectmultiple','checkboxes'];
        var selector = ':input[name="data[Field]['+res.location+']['+field_name+']'+(jQuery.inArray(res.fields[field_name].type,multipleValueFields) > -1 ? '[]"]' : '"]');
        $fieldRespCache[field_name] = $_parentElement.find(selector);

        return $fieldRespCache[field_name];
    };

    this.storeValues = function($jr_field,oldVal)
    {
        var cachedResp = $jr_field.data('fieldOptions') || {};
        var control_field_type = $jr_field.data('fieldType');
        var $fieldOptions = $_this.getFieldOptions($jr_field,control_field_type);
        oldVal = jQuery.isArray(oldVal) ? oldVal : [oldVal];

        jQuery(oldVal).each(function(i,old_val)
        {
            if(old_val in cachedResp)
            {
                /*******************************************
                * Store dependent groups field values
                *******************************************/
                jQuery(cachedResp[old_val].dependent_groups).each(function(i,group)
                {

                    $fieldGroups.filter('fieldset#group_'+group).find(':input').each(function()
                    {
                        $dep_field = jQuery(this);
                        if($dep_field.data('isControlled')===false || ($dep_field.data('isControlled')===true && $dep_field.data('hasControl')===true))
                        {
$_this.debug('6.1 >--- Store current val of ' + $dep_field.data('fieldName') + '[' + $dep_field.val() + '] for '+ $jr_field.data('fieldName') + '['+old_val+']');
                            var parentMemory = $dep_field.data('parentMemory') || [];
                            var pm_key = $jr_field.data('fieldName')+'.'+old_val;
                            parentMemory[pm_key] = $dep_field.val();
                            $dep_field.data('parentMemory',parentMemory);
                        }

                        if($dep_field.data('hasControl')===true) {
                            // Remember values recursively for each controlled field
                            var dep_sel_val = $dep_field.val();
$_this.debug ('6.1 >--- Run recursively for ' + '['+$dep_field.attr('id')+'['+dep_sel_val+']');
                            if(dep_sel_val != null) $_this.storeValues($dep_field,dep_sel_val);
                        }
                    });
                });

                /*******************************************
                * Store dependent field values
                *******************************************/
                jQuery(cachedResp[old_val].dependent_fields).each(function(i,dep_fname)
                {
                    var $dep_field = $_this.getjQueryObj(cachedResp[old_val],dep_fname);
                    if($dep_field.length == 0) return true;
                    var dep_field_type = cachedResp[old_val].fields[dep_fname].type;

$_this.debug('6.2 >--- Store current val of ' + dep_field_type + ' ' + $dep_field.data('fieldName') + '[' + $dep_field.val() + '] for '+ $jr_field.data('fieldName') + '['+old_val+']');
                    var parentMemory = $dep_field.data('parentMemory') || [];
                    var pm_key = $jr_field.data('fieldName') +'.'+old_val;
                    parentMemory[pm_key] = $dep_field.val();
                    $dep_field.data('parentMemory',parentMemory);

                    // Remember values recursively for each controlled field
                   if($dep_field.val() != null)  {
                       $_this.debug ('6.2 >--- Run recursively');
                       $_this.storeValues($dep_field,$dep_field.val());
                   }
                });
            }
        });
    };

    this.recallValue = function($jr_field, control_fname, sel_val)
    {
        var parentMemory = $jr_field.data('parentMemory') || [];
        var pm_key = control_fname + '.' + sel_val;

        if(parentMemory[pm_key] != undefined)
        {
            $_this.debug('** Recall ParentMemory:' + $jr_field.data('fieldName') + ' = [' + control_fname + ':' + sel_val + '] => ' + parentMemory[pm_key]);
            var fieldType = $jr_field.data('fieldType');
            // Add any click2add options previously removed
/*            var click2AddOptions = $jr_field.data('click2AddOptions') || [];
            if(click2AddOptions[control_fname][sel_val] != undefined) {
                $jr_field.append(click2AddOptions[control_fname][sel_val]);
            }
*/
            if(jQuery.inArray(fieldType,['checkboxes','radiobuttons']) > -1)
            {
				$jr_field.each(function() {
					var currVal = jQuery(this).val();
					jQuery(this).attr('checked',jQuery.inArray(currVal,parentMemory[pm_key]) > -1);
				});
			} else {
                $jr_field.val(parentMemory[pm_key]);
            }
            $jr_field.trigger('change')
        }
    }

    this.clearDependents = function($jr_field,val /*clear value*/,sel_val /*new value*/)
    {
        var cachedResp = $jr_field.data('fieldOptions') || {},
            clearVal = [],
            control_val = val,
            control_field_type = $jr_field.data('fieldType'),
            $fieldOptions = $_this.getFieldOptions($jr_field,control_field_type);

$_this.debug('5 >--- Clear dependents of ' + $jr_field.attr('id') + '['+val+']. Event type is ' + $jr_field.data('eventType'));

        clearVal = jQuery.isArray(val) ? val : [val];

        jQuery(clearVal).each(function(i,clear_val)
        {
            if(clear_val in cachedResp)
            {
                /*******************************************
                * Clear dependent groups
                *******************************************/
                jQuery(cachedResp[clear_val].dependent_groups).each(function(i,group)
                {
$_this.debug('Hide dependent group: ' + group);
                    // Loop through parent control field checkboxes/select options to find if
                    // other checked/selected options control this group
                    var clear_flag = true;

                    $fieldOptions.each(function()
                    {
                        var $option = jQuery(this);
                        if(cachedResp[$option.val()]
                            && ($option.is(':checked') || $option.is(':selected')))
                        { // care only about checked ones
                            if(jQuery.inArray(group,cachedResp[$option.val()].dependent_groups) > -1) clear_flag = false;
                        }
                    });

                    if(clear_flag) {
                        var $fieldGroup = $fieldGroups.filter('fieldset#group_'+group);
                        $fieldGroup.data('isActive',false).css('display','none').find(':input').val('').data('doNotStore',true).trigger('change');
                        if($tabs.length) $tabs.filter('li#tab_'+group).hide();
                    }
                });

                /*******************************************
                * Clear dependent fields
                *******************************************/
                jQuery(cachedResp[clear_val].dependent_fields).each(function(i,dep_fname)
                {
                    var $dep_field = $_this.getjQueryObj(cachedResp[clear_val],dep_fname);

                    if($dep_field.length === 0) return true; // The element is not present on the page (adv. search module)

                    var dep_field_type = cachedResp[clear_val].fields[dep_fname].type;

$_this.debug ('5.0 >--- Clear dependent [' + dep_fname + ']['+clear_val+']['+dep_field_type+']');

                    var dep_sel_val = $dep_field.val();

                    if(jQuery.inArray(dep_field_type,['select','selectmultiple']) > -1)
                    {
$_this.debug ('5.1 >--- Select, SelectMultiple');

                        // Clear current value for dependent fields
                        jQuery(cachedResp[clear_val].fields[dep_fname].options).each(function()
                        {
                            var dep_value = this.value;
$_this.debug(dep_fname+'['+dep_field_type+']'+'['+dep_value+']');
                            if (jQuery.inArray(control_field_type,['checkboxes','selectmultiple'] > -1)) {
                                // Loop through parent control field checkboxes/select options to find if
                                // other checked/selected options control this group
                                var clear_flag = true;

                                $fieldOptions.each(function()
                                {
                                    var $option = jQuery(this);

                                    if(cachedResp[$option.val()]
                                        && ($option.is(':checked') || $option.is(':selected')))
                                    { // care only about checked/selected options in control field
                                        jQuery(cachedResp[$option.val()].fields[dep_fname]).each(function(){
                                            jQuery(this.options).each(function(){
                                                if(this.value == dep_value) clear_flag = false;
                                            });
                                        });
                                    }
                                });

                                if(clear_flag) {
$_this.debug('5.1.1 >----- Removing field option [' + dep_value + ']');
                                    $dep_field.children('option[value="'+dep_value+'"]').remove();
                                }
                            }
                            else {
$_this.debug('5.1.2 >-----');
                                $dep_field.children('option[value="'+dep_value+'"]').remove();
                            }

                        // Clear dependents recursively for each controlled field option
                        if(dep_value != null) $_this.clearDependents($dep_field,dep_value);

                        });

                        // Prevents newly populated values from getting cleared on event trigger
                        $dep_field.data('old_val','');

                        var option_count = $dep_field.children('option').length;

                        if(
                            (dep_field_type == 'select' && option_count == 1)
                            ||
                            (dep_field_type == 'selectmultiple' && option_count == 0)
                        ) {
							var click2add = $dep_field.data('click2add') || 0;
							if(click2add == 0 || (click2add == 1 && $dep_field.data('isControlled'))) {
								$dep_field.data('isActive',false).val('')/*.css('display','none')*/.parents('div.jr_fieldDiv').css('display','none')
							}
                        }
                    }
                    else {
$_this.debug ('5.2 >--- ' + dep_field_type);
                        // Prevents newly populated values from getting cleared on event trigger
                        $dep_field.data('old_val','');

                        // Loop through parent control field checkboxes/select options to find if other checked/selected options control this field
                        if(jQuery.inArray(control_field_type,['checkboxes','selectmultiple'] > -1)) {
                            // Loop through parent control field checkboxes/select options to find if other checked/selected options control this value
                            var clear_flag = true;
                            $fieldOptions.each(function()
                            {
                                var $option = jQuery(this);
                                if(cachedResp[$option.val()]
                                    && ($option.is(':checked') || $option.is(':selected')))
                                { // care only about checked ones
                                    if(jQuery.inArray(dep_fname,cachedResp[$option.val()].dependent_fields) > -1) {
                                        clear_flag = false;
                                    }
                                }
                            });
                            if(clear_flag) $dep_field.val('');
                        }
                        else {
                            $dep_field.val('');
                        }

                        if(!clear_flag || (sel_val != undefined
                            && cachedResp[sel_val] != undefined
                            && cachedResp[sel_val].dependent_fields.length > 0
                            && jQuery.inArray(dep_fname,cachedResp[sel_val].dependent_fields) > -1)) {
                            // Don't hide text field
                        }
                        else {
                            // Hide text field
$_this.debug('   ----- Hiding text field ' + dep_fname);
                            $dep_field.data('isActive',false)./*css('display','none').*/parents('div.jr_fieldDiv').css('display','none');
                        }
                    }

                    $dep_field.trigger('change');

                });
            }
        });
    };

    this.processResponse = function(value, page_setup, res)
    {
        if(res.length == 0) return;

        /*********************************************************************
        * Edit mode - pre-cache responses for all fields with selected values
        **********************************************************************/
        if(res.responses != undefined)
		{
			jQuery.each(res.responses, function(fname,fresp)
			{
				var $jr_field = $_this.getjQueryObj(res,fname);
				if(!$jr_field) return true;
				jQuery.each(fresp,function(k,resp)
				{
	$_this.debug('1 >-- Caching response data for ' + fname + '['+resp.control_value+']');
					// Cache response
					var fieldOptionsResp = $jr_field.data('fieldOptions') || {}; // get options object
					fieldOptionsResp[resp.control_value] = resp; // add new response
					$jr_field.data('fieldOptions',fieldOptionsResp); // store it
				})
		   });

			// Initial load of control field options - run just once on form init
			if(page_setup) {
				$_this.populateOptions(res);
				// Bind relatedListings widget
				try {
					$_parentElement.find('.jrRelatedListing').relatedlisting();
				} catch (err) {
					//
				}
			}
		}

        // Attach event trigger to fields
        jQuery.each(res.control_field, function(k,fname)
        {
            var $jr_field = $_this.getjQueryObj(res,fname);

            // If text field no need to bind it to any events
            if(undefined == res.fields || res.fields[fname].type == 'text') return true;

            $jr_field.bind('change',function()
            {
$_this.debug('2 >--- Change event triggered for ' + fname + ', id = ' + $jr_field.attr('id') + ', ' + res.fields[fname].type);

                var sel_val = '';
                var selArray = []; // For checkboxes and multiple selects
                var unselArray = [];
                var old_val = $jr_field.data('old_val') || (jQuery.inArray(res.fields[fname].type,['selectmultiple'/*,'checkboxes'*/]) ? [] : null);
                var cache_key, cache_res;
                var clicked_val;

$_this.debug('   Old val: ' + old_val);

                // Getting the selected value varies depending on the input type
                switch(res.fields[fname].type) {
                    case 'radiobuttons':
                        sel_val =  jQuery(this).val();
                    break;
                    case 'selectmultiple':
                        // Create array of non-selected values used to clear dependents
                        $jr_field.children('option').not(':selected').each(function(){
                            unselArray.push(jQuery(this).val());
                        });

                        selArray = $jr_field.val();
                        if(selArray == null) selArray = [];
                        if(selArray.length == 1) {
                            sel_val = selArray[0];
                        }
                        else if(old_val != null) {
                            for(var i=0; i<selArray.length; i++) {
                                if(jQuery.inArray(selArray[i], old_val) == -1) {
                                    sel_val = $jr_field.children('option[value="' + selArray[i] + '"]').val();
                                    break; // break out of for loop
                                }
                            }
                        }
                    break;
                    case 'checkboxes':
                        $jr_field.each(function(){if(jQuery(this).is(':checked')) selArray.push(jQuery(this).val());});
                        if(jQuery(this).is(':checked')) sel_val =  jQuery(this).val();
                        clicked_val = jQuery(this).val();
                    break;
                    default:
                        sel_val =  $jr_field.val();
                    break;
                }

$_this.debug('   Sel val: ' + sel_val);

                /**************************************************
                *  Set dependent clear value based on field type
                **************************************************/
                var clear_val;
                switch(res.fields[fname].type) {
                    case 'checkboxes':
                        if(sel_val == '') clear_val = clicked_val;
                    break;
                    case 'selectmultiple':
                        if(unselArray.length > 0) clear_val = unselArray;
                    break;
                    default:
                        clear_val = old_val;
                    break;
                }

                var doNotStore = $jr_field.data('doNotStore') || false;
                if(opts.recallValues && old_val != null && old_val.length > 0 && doNotStore === false) {
                    $_this.storeValues($jr_field,old_val);
                }

                // Store curr val
                $jr_field.data('old_val', (selArray.length ? selArray : sel_val));

                // Check cached response object
                var fieldOptionsResp = $jr_field.data('fieldOptions') || {};
                cachedResp = (sel_val == null || fieldOptionsResp == {}) ? null : fieldOptionsResp[sel_val];

$_this.debug('   Cached response');
$_this.debug(undefined == cachedResp ? '       Not cached' : cachedResp);

                /**************************************************
                *  Populate dependent fields and load response
                * from server if necessary
                **************************************************/
                if((cachedResp == null && sel_val != '' && sel_val != null))
                {
$_this.debug(' -> Change Event AJAX');
                    $jr_field.attr('disabled','disabled');
                    jreviews.dispatch({
                        'method':'POST',
                        'type':'json',
                        'controller':'fields',
                        'action':'_loadFieldData',
                        'data':{'data[fields]':fname,'data[value]':sel_val,'data[page_setup]':false,'data[fieldLocation]':opts.fieldLocation,'data[referrer]':opts.referrer},
                        'onComplete':function(valResp){
                        // Cache response
$_this.debug('1 >-- Caching data for ' + fname + '['+sel_val+']');
                        var fieldOptionsResp = $jr_field.data('fieldOptions') || {}; // get options object
                        fieldOptionsResp[sel_val] = valResp; // add new response
                        $jr_field.data('fieldOptions',fieldOptionsResp); // store it
                        $jr_field.data('eventType','ajax');
                        if(undefined == valResp.fields) valResp.fields = [];
                        $_this.populateOptions(valResp, res.fields[fname], $jr_field, clear_val, sel_val);
                    }});
                }
                else if(!jQuery.isEmptyObject(cachedResp) && sel_val != '') {
$_this.debug(' -> Change Event CACHED');
                    $jr_field.data('eventType','cached');
                    $_this.populateOptions(cachedResp, res.fields[fname], $jr_field, clear_val, sel_val);
                }
                else {
$_this.debug(' -> Change Event CLEAR');
                    $jr_field.data('eventType','clear');
                    $_this.clearDependents($jr_field,clear_val,sel_val);
                }
                $jr_field.removeAttr('disabled');

                $jr_field.data('doNotStore',false);
            });  // end bind event

            // Init auto complete fields
            if($jr_field.hasClass('jrAutoComplete') && jQuery.inArray($jr_field.data('fieldType'),['select','selectmultiple']) > -1) {
$_this.debug(' -> Activate AutoComplete UI for ' + fname);
                $jr_field.autocompletefield({'optionType':$jr_field.data('autocompleteType'),'optionDivPosition':$jr_field.data('autocompletePos')});
            }
        });
    };

    this.populateOptions = function(res, controlField, $controlField, clear_val, sel_val)
    {
        if(res.length == 0) return;

        controlField = controlField != undefined ? controlField : {'type':'','name':''};

$_this.debug('4 >--- Populate options for ' + controlField.name + '['+ sel_val +'] and we clear it for [' + clear_val + ']');

        // When initializing the page show all groups that are not dependent groups
          if(res.page_setup) {
            jQuery.each(groupArray,function(i,group){
                if(jQuery.inArray(group,res.dependent_groups) == -1) {
                    var $GroupFieldset = $fieldGroups.filter('fieldset#group_'+group);
                    var counter = 0;
                    $GroupFieldset.find(':input').each(function() {
                        if(jQuery(this).attr('type') != "hidden") counter++;
                    });
                    if(counter >0) {
                        $GroupFieldset.show();
                        if($tabs.length) $tabs.filter('li#tab_'+group).show(10);
                    }
                }
            });
        }

        // Loop through controlled groups and toggle them
        jQuery.each(res.dependent_groups,function(k,group){
            var $group = $fieldGroups.filter('fieldset#group_'+group);
            $group.data('isControlled',true);
            if(res.page_setup) {
                $group.css('display','none').data('isActive',false);
                if($tabs.length) $tabs.filter('li#tab_'+group).hide();
            }
            else {
                $group.show().data('isActive',true);
                if($tabs.length) $tabs.filter('li#tab_'+group).show(10);
            }
        });

        // Loop through fields for current action
        var paidFields = $_this.getPaidFields() || [];
        if(isPaidListing) {
$_this.debug('   --- It is a paid listing!');
$_this.debug(paidFields);
        }

        jQuery.each(res.fields, function(k,field)
        {
$_this.debug('   --- Populating field ' + field.name+'['+field.title+']['+sel_val+']');
            if(jQuery.isArray(field.name)) return true;

            var $jr_field = $_this.getjQueryObj(res,field.name);

            try {
                var type = $jr_field.prop('type');
            } catch (err) {
                var type = $jr_field.attr('type');
            }

            switch(type) {
                case 'select-one': field.type = 'select'; break;
                case 'select-multiple': field.type = 'selectmultiple'; break;
            }

            $jr_field.data('fieldTitle',field.title);

            if(res.page_setup == 0  && /* don't clear it unless the page has already been set up */jQuery.inArray(field.type,['select','selectmultiple']) > -1) {
                $jr_field.val(''); /* Resets selected value - required for click2add feature so switching to a different parent option doesn't show the new option selected */
            }

            if(res.page_setup) {
                $jr_field.data('fieldName',field.name);
                $jr_field.data('fieldType',field.type);
                $jr_field.data('orderBy',field.order_by);
                $jr_field.data('hasControl',field.control);
                $jr_field.data('isControlled',field.controlled);
                $jr_field.data('autocompleteType',field.autocompletetype);
                $jr_field.data('autocompletePos',field.autocompletepos);
            }


var fieldInDependentGroup = jQuery.inArray(field.group,res.dependent_groups) > -1;
var groupIsActive = $fieldGroups.filter('fieldset#group_'+field.group).data('isActive');

			if(res.control_field.length == 1) {
                var controlledBy = $jr_field.data('controlledBy') || {};
                controlledBy[res.control_field[0]] = res.control_value;/* Click2Add options will use the current parent value */  /*$fieldRespCache[res.control_field[0]].val()*/;

				if(!fieldInDependentGroup){
					$jr_field.data('controlledBy',controlledBy);
				}

                // Necessary check for text fields inside controlled groups
                if((res.page_setup == 0 || res.page_setup == undefined /*because it's cached*/) && field.type == 'text' && field.controlled == true && field.controlgroup == true) {
                    return true;
                }
            }

            // Hide fields on startup
            if ($jr_field.data('isActive') == undefined /* forces unselected dependent fields to appear in adv. search module */
                && res.page_setup
                && (
                        (jQuery.inArray(field.type,['select','selectmultiple']) > -1
                            && ( // in between field
                                (field.control === true && field.controlled === true)
                                || // leaf field
                                (field.control == false && field.controlled == true)
                                ||  // regular select list without options
                                (field.autocomplete == "0" && field.options == undefined)
                            )
                        )
                        ||
                        (jQuery.inArray(field.type,['checkboxes','radiobuttons']) > -1
                            && ( // in between field
                                (field.control === true && field.controlled === true)
                                || // leaf field
                                (field.control == false && field.controlled == true)
                            )
                        )
                        ||
                        (// hides all other dependent fields
                            !field.control && field.controlled && (field.selected == undefined || field.selected.length == 0)
                        )
            )){
                // Don't remove existing options that may be there as dependents of other control fields
                if(field.type == 'select') {
                    $jr_field
                        .html(
                            jQuery('<option></option>')
                                .attr('value','')
                                .text(opts.lang.select_field.replace('%s', $jr_field.data('fieldTitle')))
                                .data('ordering',0)
                        )
                        .children('option:eq(0)').attr('selected','selected');
                }

$_this.debug('   ----- Hiding field ' + field.name + ' on startup');
				var click2add = $jr_field.data('click2add') || 0;
				if(click2add == 0 || (click2add == 1 && $jr_field.data('isControlled'))) {
					$jr_field.data('isActive',false)./*css('display','none').*/parents('div.jr_fieldDiv').css('display','none')
				}

                return true;
            }

            // Don't remove existing options that may be there as dependents of other control fields
            if(field.type == 'select' && $jr_field.children('option').length == 0) {
                $jr_field.html(
                    jQuery('<option></option>')
                        .attr('value','')
                        .text(opts.lang.select_field.replace('%s',$jr_field.data('fieldTitle')))
                        .data('ordering',0)
                    );
            }

            if(jQuery.inArray(field.type,['select','selectmultiple']) > -1)
            {
                $jr_field.children('option[data-controlledBy="'+res.control_field[0]+'"]').each(function(){
                    var $option = jQuery(this);
                    if( $option.data('controlValue') != res.control_value) {
                        $option.remove();
                    }
                })

                jQuery(field.options).each(function(){
                    if($jr_field.children('option[value="'+this.value+'"]').length == 0) { // add the option if not already there
                        $jr_field.append(
                            jQuery('<option></option>')
                                .attr('value',this.value)
                                .text(this.text)
                                .attr('data-ordering',this.ordering)
                        );
                    }
                });
            }

			// In edit mode
            if(res.edit)
            {
				// Pre-select current values and show dependent groups
                if(field.selected != undefined && field.selected.length > 0)
                {
					// Checkboxes and radiobuttons are already pre-selected
					if(jQuery.inArray(field.type,['checkboxes','radiobuttons']) == -1) {
						$jr_field.val(field.selected);
					}

					$jr_field.data('old_val',field.selected);

                    jQuery(field.selected).each(function(i,edit_val)
                    {

                        if(field.name in res.responses && edit_val in res.responses[field.name])
                        {
$_this.debug('  +++++++ Start populateOptions recursion for ' + field.name);
                            $jr_field.data('eventType','cached')
                            // Run recursively to add options to all dependent select lists and show dependent groups
                            $_this.populateOptions(res.responses[field.name][edit_val], field, $jr_field, undefined, edit_val);
                        }
                    });
                }
            }

            // Some dependent fields end up with no pre-selected default so we force it to -- Select --
            if(field.type == 'select' && $jr_field.find(':selected').length == 0) {
                $jr_field.children('option:eq(0)').attr('selected','selected');
            }

            // On listing edit, stop unpaid fields from showing up for the selected plan
            if(!res.page_setup && isPaidListing && jQuery.inArray(field.name, paidFields) == -1) {
                $jr_field.data('isActive',true);
                return true;
            }

$_this.debug('   ***** Start checks for displaying fields for ['+field.title+']['+field.type+']');

			if(fieldInDependentGroup && groupIsActive != true) {
				$jr_field.data('isActive',false)/*.show('fast')*/.parents('div.jr_fieldDiv').hide();
			}
			else {

				switch(field.type)
				{
					case 'select':
						if($jr_field.children('option').length > 1 || ($jr_field.data('click2add') == 1 && !fieldInDependentGroup) || ($jr_field.data('hasControl') === true && $jr_field.data('isControlled') === false) || ($jr_field.data('hasControl') === false && $jr_field.data('isControlled') === false) ) {

							$jr_field.data('isActive',true)/*.show('fast')*/.parents('div.jr_fieldDiv').show();
						}
						else {
							$jr_field.data('isActive',false)/*.show('fast')*/.parents('div.jr_fieldDiv').hide();
						}
					break;
					case 'selectmultiple':

						if($jr_field.children('option').length > 0 || ($jr_field.data('click2add') == 1 && !fieldInDependentGroup) || ($jr_field.data('hasControl') === true && $jr_field.data('isControlled') === false) || ($jr_field.data('hasControl') === false && $jr_field.data('isControlled') === false)) {

							$jr_field.data('isActive',true)/*.show('fast')*/.parents('div.jr_fieldDiv').show();
						}
					break;

					case 'checkboxes':
					case 'radiobuttons':
	if(!fieldInDependentGroup || (fieldInDependentGroup && groupIsActive && ($jr_field.data('hasControl') === true && $jr_field.data('isControlled') === false) || ($jr_field.data('hasControl') === false && $jr_field.data('isControlled') === false))) {
						$jr_field.data('isActive',true)/*.show('fast')*/.parents('div.jr_fieldDiv').show();
	}

					break;
					default:
							$jr_field.data('isActive',true)/*.show('fast')*/.parents('div.jr_fieldDiv').show();
					break;
				}

			}

            // Re-sort list
            if($jr_field.children('option').length > 0 && $jr_field.data('orderBy') == 'ordering') {
                $_this.sort_by_field($jr_field);
            }

        });

        if(undefined != $controlField && undefined != clear_val) {
            $_this.clearDependents($controlField,clear_val,sel_val);
        }

        /**********************************************************
        * Recall previous vals for selected parent val
        **********************************************************/
        if(!res.page_setup && opts.recallValues)
        {
            jQuery(res.dependent_fields).each(function(i,fname){
                $_this.recallValue(jQuery('#'+fname), controlField.name, sel_val);
            });

            jQuery.each(res.dependent_groups,function(k,group)
            {
                $fieldGroups.filter('fieldset#group_'+group).find(':input').each(function()
                {
                    var hasControl = jQuery(this).data('hasControl');
                    var isControlled = jQuery(this).data('isControlled');
                    if(hasControl || (hasControl === false && isControlled === false)) {
                        $_this.recallValue(jQuery(this), controlField.name, sel_val);
                    }
                });
            });
        }
    };

    this.sort_by_field = function($field)
    {
        var selected = $field.val(),
            order_type = $field.data('orderBy');

        var primer = order_type == 'ordering'
            ?
            function(a){return parseInt(a);}
            :
            function(a){return a.toUpperCase();}
            ;

        try {
            var type = $field.prop('type');
        } catch (err) {
            var type = $field.attr('type');
        }

        if(type == 'select-one') {
            var selectOption = $field.children('option[value=""]').detach();
        }

        var options = $field.children('option').sort(function(a,b)
        {
           a = order_type == 'ordering' ? jQuery(a).data('ordering') : a.text
           b = order_type == 'ordering' ? jQuery(b).data('ordering') : b.text;

           if (typeof(primer) != 'undefined'){
               a = primer(a);
               b = primer(b);
           }

           if (a<b) return -1;
           if (a>b) return 1;
           return 0;
        });

        if(selectOption != undefined) {
            $field.html(options).prepend(selectOption).val(selected);
        }
        else {
            $field.html(options).val(selected);
        }
    }

    this.debug = function(msg) {
        if(debug) console.log(msg);
    }
}

/* Related listings widget */
(function($) {
    $.widget("ui.relatedlisting", {

        // default options
        options: {
            minLength: 2,
            classes: "jrAutoSuggest",
            instructionsClass: 'acInstructions',
            instructionsText: jrLanguage["field.ui_help"],
            listingTitleClass: 'relatedListingTitle'
        },
        _create: function() {

            var el = this.element.hide(),
                    o = this.options,
                    searchInput = (this.searchInput = $('<input type="text" />'))
                        .val( o.instructionsText )
                        .addClass( o.instructionsClass )
                        .addClass( o.classes )
                        .insertAfter( el ),

                    labelCheckbox = (this.labelCheckbox = $('<input type="checkbox" />'))
                        .attr('checked','checked')
                        .addClass('jr_hidden')
                        .insertAfter( searchInput )

                    searchLabel = (this.searchLabel = $('<span />'))
                        .addClass( o.listingTitleClass )
                        .insertAfter( labelCheckbox )
                    ;

                this._bindEvents();
        },
        _init: function() {

            var self = this,
                    el = this.element,
                    o = this.options,
                    id = el.val(),
                    searchLabel = this.searchLabel;

            if(id > 0) {
                self._getListingData('',function(data) {
                    searchLabel.html(data[0].label).trigger('selected');
                },id);
            }
        },
        _getListingData: function(value, callback, id) {

            var el = this.element;

            id = id || '';
            jreviews.dispatch({
                'type':'json',
                'controller':'fields',
                'action':'_relatedListings',
                'data': {
                    "data[limit]": 12,
                    "data[id]": id,
                    "data[fname]": el.attr('id'),
                    "data[listingtype]": el.data('listingtype') || '',
                    "data[value]": value
                },
                'onComplete':function( data ) {
                    callback(data);
                }
            });
        },
        _bindEvents: function() {

            var self = this,
                o = this.options,
                el = this.element,
                searchInput = this.searchInput,
                searchLabel = this.searchLabel,
                labelCheckbox = this.labelCheckbox;

           // Attach auto complete function to text field
            searchInput
                .bind({
                    'click focusin': function(){
                        searchInput.val('').removeClass( o.instructionsClass );
                    },
                    blur: function() {
                        searchInput.val(o.instructionsText).addClass( o.instructionsClass );
                    }
                })
                .autocomplete({
                    source: function( request, response ) {

                        var $element = this.element,
                            term = request.term.toLowerCase(),
                            cache = $element.data('autocompleteCache') || {},
                            foundInCache  = false;

                        $.each(cache, function(key, data){
                            if (term == key && data.length > 0) {
                              response(data);
                              foundInCache = true;
                              return;
                            }
                        });

                        if (foundInCache) return;

                        self._getListingData(term,function(data) {
                            cache[term] = data;
                            $element.data('autocompleteCache', cache);
                            response(data);
                        });
                    },
                    search: function( event, ui) {
                        //
                    },
                    minLength: o.minLength,
                    focus: function( event, ui) { /* Make select label show up searchLabel */
                        searchLabel.html(ui.item.label);
                        return false;
                    },
                    select: function( event, ui )
                    {
                        el.val(ui.item.value);
                        searchInput.val('');
                        searchLabel.trigger('selected');
                        return false;
                    }
                }
            );

            searchLabel.bind('selected',function(){
               labelCheckbox.attr('checked','checked').show(5);
            });

            labelCheckbox.bind('click',function(){
                if(labelCheckbox.is(':checked') === false) {
                    labelCheckbox.hide();
                    searchLabel.html('');
                    el.val('');
                }
            });
        },
        destroy: function() {
            //$.widget.prototype.apply(this, arguments); // default destroy
        }
    });
})(jQuery);

/* Autocomplete UI widget for lists */
(function($) {
    $.widget("ui.autocompletefield",{

        // default options
        options: {
            optionType: 'link', // 'link'|'checkbox'
            optionDivPosition: 'after', //'before'
            minSearchLength: 1,
            lang: {
                help: jrLanguage["field.ui_help"],
                no_results: jrLanguage["field.no_results"]
            },
            helpClass: 'acInstructions',
            inputClass: 'jrAutoSuggest',
            disabledLinkClass: 'ui-disabled',
            checkboxClass: 'ui-option',
            optionsDivClass: 'ui-optionsDiv'
        },

        _create: function()
        {
            var el = this.element.hide(),
                o = this.options,
                fname = (this.fname = el.data('field').name),
                fid = (this.fid = el.data('field').id),
                widgetid = (this.widgetid = 'text-' + fname);

            el.data('validation',false); // Set initial status

            // Here we generate the widget that will replace the original element and insert it in the DOM
            var searchInput = (this.searchInput = $('<input type="text" />'))
                    .attr({id: widgetid, name: widgetid})
                    .val( o.lang.help )
                    .addClass( o.helpClass + ' ' + o.inputClass)
                    .insertAfter( el ),

                optionsDiv = (this.optionsDiv = $('<div></div'))
                    .attr('data-fname', fname)
                    .addClass(o.optionsDivClass + ' clearfix'),

                noResults = (this.noResults = $('<span></span>') )
                    .html(o.lang.no_results)
                    .css('margin-left','5px')
                    .addClass('jr_validation');

            switch(o.optionDivPosition) {
                case 'before': optionsDiv.insertBefore(searchInput); break;
                case 'after': optionsDiv.insertAfter(searchInput); break;
            }

            this._bindEvents();
        },

        _init: function()
        {
            var self = this,
                el = this.element,
                o = this.options,
                optionsDiv = this.optionsDiv;

            // Find pre-selected options and add them to optionsDiv
            var $fieldOptions = el.children('option:selected');

            if($fieldOptions.length) {
                $.each($fieldOptions, function()
                {
                    var $option = $(this);
                    if($option.val() == '') return true;

                    switch(o.optionType) {
                        case 'link':
                            var newOption = self._createLink({'value':$option.val(),'text':$option.text(),'checked':true})
                        break;
                        case 'checkbox':
                            newOption = self._createCheckbox({'value':$option.val(),'text':$option.text(),'checked':true})
                        break;
                    }

                    optionsDiv.append(newOption);
                });
            }

            switch(o.optionType) {
                case 'link':
                    optionsDiv.find('a').length == 0 ? optionsDiv.hide() : optionsDiv.show(5);
                break;
                case 'checkbox':
                    optionsDiv.find('input').length == 0 ? optionsDiv.hide() : optionsDiv.show(5);
                break;
            }
        },

        _bindEvents: function()
        {
            var self = this,
                el = this.element,
                o = this.options,
                searchInput = this.searchInput,
                optionsDiv = this.optionsDiv;

            // Events for the widget text input
            searchInput
                .bind('click focusin',function(){
                    if(this.value == o.lang.help) {
                        $(this).val('').removeClass(o.helpClass);
                    }
                })
                .bind('blur focusout',function(){
                    if(this.value == '') {
                        $(this).val(o.lang.help).addClass(o.helpClass);
                    }
                });

            this._bindAutoComplete(); // Adds jQuery UI autocomplete to searchInput

            // Events for the widget optionsDiv
            // Bind change event to current and future widget options
            optionsDiv
                .delegate(':input:checkbox','change',function(){
                    var widgetOption = $(this);
                    var value = widgetOption.val();
                    var label = widgetOption.next('span').html();
                    var sourceOption = el.children('option[value="'+value+'"]');

                    if(widgetOption.is(':checked')) { // Checked
                        if(sourceOption.length > 0) {
                            sourceOption.attr('selected','selected');
                        }
                        else {
                            sourceOption.append($("<option></option>").attr("value",value).text(label));
                        }
                    }
                    else if(sourceOption.length > 0) { // Unchecked
                        sourceOption.removeAttr('selected');
                    }
                    el.trigger('change');
                    return false;
                })
               .delegate('a','click',function(){
                    var widgetOption = $(this);
                    var value = widgetOption.data('value');
                    var label = widgetOption.html();
                    var sourceOption = el.children('option[value="'+value+'"]');

                    if(widgetOption.data('active') == false) { // Checked
                        widgetOption.data('active',true).removeClass(o.disabledLinkClass);
                        if(sourceOption.length > 0) {
                            sourceOption.attr('selected','selected');
                        }
                        else {
                            sourceOption.append($("<option></option>").attr("value",value).text(label));
                        }
                    }
                    else if(sourceOption.length > 0) { // Unchecked
                        widgetOption.data('active',false).addClass(o.disabledLinkClass);
                        sourceOption.removeAttr('selected');
                    }
                    el.trigger('change');
                    return false;
                });
                ;

            // Events for the widget source element
            switch(o.optionType) {
                case 'link':
                    this._bindForLinks();
                break;
                case 'checkbox':
                    this._bindForCheckboxes();
                break;
            }
            this._bindClick2Add();
        },

        _bindForCheckboxes: function()
        {
            // Add mirror effect. What happens to source element also happens to the widget
            var self = this,
                el = this.element,
                o = this.options,
                optionsDiv = this.optionsDiv;

            el
                .bind('change',function() {
                    //console.log('Errrrrmm something changed in '+el.data('fieldTitle'));
                    var widgetOptions = optionsDiv.find(':input'),
                        sourceOptions = el.children('option'),
                        listArray = sourceOptions.map(function() { return this.value; });

                    sourceOptions.each(function() {
                        var sourceOption = $(this),
                            value = sourceOption.val();

                        if(value != '') {
                            var widgetOption = optionsDiv.find(':input[value="'+value+'"]');
                            if(sourceOption.is(':selected') == false) {
                                if(widgetOption.length) {
                                    widgetOption.removeAttr('checked');
                                }
                            }
                            else {
                                if(widgetOption.length) {
                                    widgetOption.attr('checked','checked');
                                }
                                else {

                                    optionsDiv.show(5).append(self._createCheckbox({'value':value,'text':sourceOption.text(),'checked':true}));
                                }
                            }
                        }
                    });

                    // Loop through widget options and remove those not in the select list.
                    // Important with controlled fields to remove old options.
                    widgetOptions.each(function() {
                        var widgetOption = $(this);
                        if($.inArray(widgetOption.val(), listArray) == -1) {
                            widgetOption.parents('label').remove();
                        }
                    });

                    optionsDiv.find(':input').length || optionsDiv.hide();
                });
        },

        _bindForLinks: function()
        {
            // Add mirror effect. What happens to source element also happens to the widget
            var self = this,
                el = this.element,
                o = this.options,
                optionsDiv = this.optionsDiv;

            el
                .bind('change',function() {
                    //console.log('Errrrrmm something changed in '+el.data('fieldTitle'));
                    var widgetOptions = optionsDiv.find('a'),
                        sourceOptions = el.children('option'),
                        listArray = sourceOptions.map(function() { return this.value; });

                    sourceOptions.each(function() {
                        var sourceOption = $(this),
                            value = sourceOption.val();

                        if(value != '') {
                            var widgetOption = optionsDiv.find('a[data-value="'+value+'"]');
                            if(sourceOption.is(':selected') == false) {
                                if(widgetOption.length) {
                                    widgetOption.data('active',false).addClass(o.disabledLinkClass);
                                }
                            }
                            else {
                                if(widgetOption.length) {
                                    widgetOption.data('active',true).removeClass(o.disabledLinkClass);
                                }
                                else {
                                    optionsDiv.show(5).append(self._createLink({'value':value,'text':sourceOption.text(),'checked':true}));
                                }
                            }
                        }
                    });

                    // Loop through widget options and remove those not in the select list.
                    // Important with controlled fields to remove old options.
                    widgetOptions.each(function() {
                        var widgetOption = $(this);
                        if($.inArray(String(widgetOption.data('value')), listArray) == -1) {
                            widgetOption.remove();
                        }
                    });

                    optionsDiv.find('a').length || optionsDiv.hide();
                });
        },

        _bindAutoComplete: function()
        {
            var self = this,
                el = this.element,
                o = this.options,
                searchInput = this.searchInput,
                optionsDiv = this.optionsDiv,
                fid = this.fid,
                fname = this.fname;

            // Attach auto complete function to text field
            searchInput.autocomplete({

                source: function( request, response ) {
                    var reqTerm = request.term.toLowerCase(),
                        cache = searchInput.data('cache') || [],
                        dataResp = [];

                    switch(o.optionType) {
                        case 'link':
                            var widgetOptionArray = optionsDiv.find('a').map(function() {
                                return $(this).data('value');
                            });
                        break;
                        case 'checkbox':
                            var widgetOptionArray = optionsDiv.find(':input').map(function() {
                                return this.value;
                            });
                        break;
                    }

                    if(el.data('isControlled')) {
                        // The list of possible options is already pre-loaded in the target input list
                        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                        dataResp = el.children( "option" ).map(function() {
                            var text = $(this).text();
                            if (this.value != ''
                                && ( !request.term || matcher.test(text) )
                                && $.inArray(this.value,widgetOptionArray) == -1
                            )
                                return {
                                    label: text,
                                    value: this.value
                                };
                        });
                        response(dataResp);
                        self._noMatch(dataResp);
                    }
                    else if(cache[reqTerm]) {
                        $(cache[reqTerm]).each(function(i,row) {
                            if(row!=undefined && $.inArray(row.value,widgetOptionArray) == -1) {
                                dataResp.push(row);
                            }
                        });
                        response(dataResp);
                        self._noMatch(dataResp);
                    }
                    else {
                        jreviews.dispatch({
                            'type':'json',
                            'controller':'fields',
                            'action':'_loadValues',
                            'data': {
                                "data[limit]": 12,
                                "data[field_id]": fid,
                                "data[value]": reqTerm
                            },
                            'onComplete':function( data ) {
                                cache[reqTerm] = data;
                                searchInput.data('cache',cache);
                                $(data).each(function(i,row) {
                                    if(row != undefined && $.inArray(row.value,widgetOptionArray) == -1) {
                                        dataResp.push(row);
                                    }
                                });
                                response(dataResp);
                                self._noMatch(dataResp);
                            }
                        });
                   }
                },
                search: function( event, ui) {
                    if(fid == '') return false;
                },
                minLength: o.minSearchLength,
                focus: function( event, ui) { /* Force select label to show up in text input */
                    searchInput.val(ui.item.label);
                    return false;
                },
                select: function( event, ui )
                {
                    if(ui.item.value != '')
                    {
                        searchInput.val('').focus();

                        // Required for changes to parent list via UI to cascade down to child fields
                        var currSel = el.val() || [],
                            option = el.children('option[value="' + ui.item.value + '"]');

                        el.val('').trigger('change');

                        if(option.length == 0) {
                            el.append($("<option></option>")
                                .attr({'value':ui.item.value})
                                .text(ui.item.label)
                            );
                        }

                        try {
                            var type = el.prop('type');
                        } catch (err) {
                            var type = el.attr('type');
                        }

                        if(type == 'select-multiple') {
                            currSel.push(ui.item.value); // for multiple select
                            el.val(currSel);
                        }
                        else {
                            el.val(ui.item.value);
                        }

                        el.trigger('change');
                    }

                    searchInput.val('');
                    return false;
                }
            });

            $('.ui-autocomplete').css('text-align','left');
        },

        _bindClick2Add: function()
        {
            var el = this.element,
                o = this.options,
                searchInput = this.searchInput;

            if(el.data('click2add') == 1)
            {
                $('<input type="button" />')
                    .attr({'class':'jrButton','value':jrLanguage["field.ui_add"]})
                    .bind('click',function() {
                        var value = searchInput.val();
                        var controlledBy = '';
                        var parent_fname, parent_value;

                        if(el.data('controlledBy')) {
                            $.each(el.data('controlledBy'),function(field,value){
                                parent_fname = field;
                                parent_value = value;
                                controlledBy = '|'+field+'|'+value;
                            });
                        }

                        var optionValue =  encodeURIComponent(value).replace(/'/g, "&#039;")+"|click2add"+controlledBy;

                        var currOption = el.children('option[value="' + optionValue+'"]');

                        if(value != '' && value != o.lang.help && currOption.length == 0) {
                            el.append(
                                $("<option></option>")
                                    .attr({
                                        'value':optionValue,
                                        'selected':'selected',
                                        'data-ordering':99999, /* make sure it shows up last*/
                                        'data-controlledBy':parent_fname,
                                        'data-controlValue':parent_value
                                    })
                                    .text(value)
                            ).trigger('change');
                            searchInput.val('');
                        }
                        else if (currOption.length == 1) {
                            /********************
                            *  Add check for single select list so only one option is selected at a time
                            ********************/
                            currOption.attr('selected','selected');
                            el.trigger('change');
                        }
                        searchInput.focus();
                    })
                    .insertAfter(searchInput);
            }
        },

        _createCheckbox: function(option)
        {
            var fname = this.fname,
                o = this.options,
                checkboxid = 'cb-'+fname+'_'+option.value.replace(/ /g,"_"),

                checkbox = $('<input type="checkbox" />')
                    .attr({id: checkboxid, name: checkboxid})
                    .addClass(o.checkboxClass)
                    .val(option.value)
                    .attr('checked',option.checked),

                label = $('<label for="'+checkboxid+'" />')
                .css('text-align','left')
                .append(checkbox)
                .append('<span>'+option.text+'</span>');

            return label;
        },

        _createLink: function(option)
        {
            var o = this.options;

            return $('<a href="javascript:void(0)"></a>')
                .addClass(o.checkboxClass)
                .html(option.text)
                .attr('data-value',option.value)
                .data('active',option.checked ? true : false);
        },

        _noMatch: function(data)
        {
            var el = this.element,
                noResults = this.noResults;

            if(data.length == 0 && el.data('validation') === false)
            {
                el.prevAll('label:eq(0)')
                    .append(noResults)
                    .data('validation',true);

                noResults.fadeIn(100).delay(3000).fadeOut(500,function(){
                    $(this).detach();
                    el.data('validation',false);
                });
           }
        },

        _destroy: function()
        {
        }

    });

}) (jQuery);
