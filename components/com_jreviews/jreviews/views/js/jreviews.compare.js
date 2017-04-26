jreviewsCompare = {
    numberOfListingsPerPage: 3,
	maxNumberOfListings: 15,
    compareURL: 'index.php?option=com_jreviews&url=categories/compare/type:type_id/',
    lang: {
		'compare_heading': jrLanguage["compare.heading"],
        'compare_all': jrLanguage["compare.compare_all"],
        'remove_all': jrLanguage["compare.remove_all"],
        'select_more': jrLanguage["compare.select_more"],
		'select_max': jrLanguage["compare.select_max"]
    },
    listingTypeID: null,
    set: function(options)
    {
        if(options!=undefined)jQuery.extend(jreviewsCompare, options);
    },
    initComparePage: function()
    {
    	var compareContainer = jQuery('#jr_compareview').parent();
        compareContainer.width(compareContainer.parent().width());

        //scrollpane parts
        var scrollPane = jQuery('.scroll-pane');
        var scrollContent = jQuery('.scroll-content');
        var scrollbar = jQuery(".scroll-bar");

        var numberOfListings = jQuery('div.scroll-content-item').length;

        if (numberOfListings >= jreviewsCompare.numberOfListingsPerPage) {
            var itemWidth = scrollPane.width() / jreviewsCompare.numberOfListingsPerPage;
        }
        else {
            var itemWidth = scrollPane.width() / numberOfListings;
        }

        var contentWidth = itemWidth * numberOfListings;

        scrollContent.width(contentWidth);
        jQuery('div.scroll-content-item').width(itemWidth);

        // prevent MooTools conflicts
        scrollbar[0].slide = null;
        scrollbar[1].slide = null;

        // build slider
        scrollbar.slider({
            slide: function(e, ui){
                if ( scrollContent.width() > scrollPane.width() ){
                    scrollContent.css('margin-left', Math.round( ui.value / 100 * ( scrollPane.width() - scrollContent.width() )) + 'px');
                }
                else {
                    scrollContent.css('margin-left', 0);
                }
                jQuery(".scroll-bar").slider("option", "value", ui.value);
            }
        });

        //append icon to handle
        var handleHelper = scrollbar.find('.ui-slider-handle')
        .mousedown(function(){
            scrollbar.width( handleHelper.width() );
        })
        .mouseup(function(){
            scrollbar.width( '100%' );
        })
        .append('<span class="ui-icon ui-icon-grip-dotted-vertical"></span>')
        .wrap('<div class="ui-handle-helper-parent"></div>').parent();

        //change overflow to hidden now that slider handles the scrolling
        scrollPane.css('overflow','hidden');

        adjustScrollbar();

        //size scrollbar and handle proportionally to scroll distance
        function sizeScrollbar(){
            var remainder = scrollContent.width() - scrollPane.width();
            var proportion = remainder / scrollContent.width();
            var handleSize = scrollPane.width() - (proportion * scrollPane.width());
            scrollbar.find('.ui-slider-handle').css({
                width: handleSize,
                'margin-left': -handleSize/2
            });
            handleHelper.width('').width( scrollbar.width() - handleSize);
        }

        //reset slider value based on scroll content position
        function resetValue(){
            var remainder = scrollPane.width() - scrollContent.width();
            var leftVal = scrollContent.css('margin-left') == 'auto' ? 0 : parseInt(scrollContent.css('margin-left'));
            var percentage = Math.round(leftVal / remainder * 100);
            scrollbar.slider("value", percentage);
        }

        //if the slider is 100% and window gets larger, reveal content
        function reflowContent(){
                var showing = scrollContent.width() + parseInt( scrollContent.css('margin-left') );
                var gap = scrollPane.width() - showing;
                if(gap > 0){
                    scrollContent.css('margin-left', parseInt( scrollContent.css('margin-left') ) + gap);
                }
        }

        // hide scrollbar if there are less items than overall width
        function adjustScrollbar() {
            if (numberOfListings <= jreviewsCompare.numberOfListingsPerPage) {
                jQuery('img.removeListing').hide();
                jQuery('div.scroll-bar-wrap').hide();
            }
        }

        //change handle position on window resize
        jQuery(window)
        .resize(function(){
                resetValue();
                sizeScrollbar();
                reflowContent();
        });

        //init scrollbar size
        setTimeout(sizeScrollbar,10);//safari wants a timeout

        jreviewsCompare.fixCompareAlignment();

        jQuery("div.comparedListings div.scroll-content-item").hover(
            function(){
                var listing = jQuery(this).attr('class').split(' ').slice(-1);
                var listingID = listing[0].substring(4);
                var listingWidth = jQuery(this).width();

                if (numberOfListings > jreviewsCompare.numberOfListingsPerPage) {
                    jQuery("div.scroll-content-item."+listing + " img.removeListing").live('click', function(){
                        jQuery("div.scroll-content-item."+listing).fadeOut('slow', function() {
                            jQuery("div.scroll-content-item."+listing).remove();
                            numberOfListings = jQuery('div.scroll-content-item').length;
                            scrollContent.width(itemWidth * numberOfListings);
                            resetValue();
                            sizeScrollbar();
                            reflowContent();
                            adjustScrollbar();
                            // remove the listing from comparison list and cookie also
                            jQuery('span#removelisting'+listingID).trigger('click');
                        });
                    });
                }
            }
        );
    },
    fixCompareAlignment: function()
    {
        function eqHeight(group) {
           tallest = 0;
           group.each(function() {
              thisHeight = jQuery(this).height();
              if(thisHeight > tallest) {
                 tallest = thisHeight;
              }
           });
           group.height(tallest);
        }
        function fixHeight(group) {
            group.each(function() {
                var firstclass = jQuery(this).attr('class').split(' ').slice(0,1);
                eqHeight(jQuery('div.'+firstclass));
            });
        }
        var compareFields = jQuery('div.scroll-content-item div.compareField');
        fixHeight(compareFields);
    },
	initCompareDashboard: function()
	{
		jQuery('body').append('<div id="jr_compareDashboard"><div id="jr_compareHeader"><div id="jr_compareArrow"></div></div><div id="jr_compareTabs"></div></div>');
		jQuery('#jr_compareTabs').append('<div id="jr_compareTitle">'+jreviewsCompare.lang.compare_heading+'</div><ul class="jr_tabsNav"></ul><div id="jr_tabsContainer"></div>');
		jQuery('#jr_compareHeader, #jr_compareTitle, ul.jr_tabsNav')
			.hover(function(){
				jQuery(this).css('cursor', 'pointer');
			})
			.click(function(){
				jQuery('#jr_compareTabs #jr_tabsContainer').slideToggle('slow', function(){
					var headerArrow = jQuery('#jr_compareArrow');
					jQuery(this).is(':visible') ? headerArrow.addClass('down') : headerArrow.removeClass('down');
				});
			});
		var cookies = jreviewsCompare.getCookies();
		if (cookies) {
			jQuery('#jr_compareDashboard').slideDown('slow');
			jQuery.each(cookies, function(cookieName, listingTypeTitle) {
				var listingTypeID = cookieName.substring(9);
				var cookieListings = jreviewsCompare.getListingsFromCookie(listingTypeID);
				if (cookieListings) {
					jreviewsCompare.insertTab(listingTypeID, listingTypeTitle);
					jQuery.each(cookieListings, function(key, value){
						jreviewsCompare.insertListingIntoComparison(key, value, listingTypeID);

					});
					if (jQuery('#tabLT'+listingTypeID+' li.ltItem').length > 4) {
						jQuery('#tabLT'+listingTypeID+' a.compareNext').css('visibility', 'visible');
					}
				}
			});
		}
        // remove listing from comparison after icon clicked
        jQuery('span.removeItem').live('click',function() {
            var item = jQuery(this).attr('id');
            var listingID = item.substring(13);
            var listingType = jQuery(this).text();
            listing = 'listing'+listingID;
            jQuery('input#'+listing).attr('checked', false);
			jreviewsCompare.removeListingFromComparison(listing, listingType);
        });
        // remove all listings from comparison
        jQuery('a.removeListings').live('click', function(e) {
            e.preventDefault();
            var cookieName = jQuery(this).parent().attr('id');
			var listingTypeID = cookieName.substring(9);
            var cookieListings = jreviewsCompare.getListingsFromCookie(listingTypeID);
            jQuery.each(cookieListings, function(key, value) {
				jreviewsCompare.removeListingFromCookie(key, listingTypeID);
                jQuery('input#'+key).attr('checked', false);
            });
			jreviewsCompare.removeTab(listingTypeID);
        });
        // compare all listings
        jQuery('a.compareListings').live('click', function(e) {
			e.preventDefault();
			var listingTypeID = jQuery(this).parent().attr('id').substring(9);
            var compareCount = jQuery('div#tabLT'+listingTypeID+' ul.ltList li').length;
            if (compareCount < 2) {
				if (jQuery('p.comparisonMessage.'+listingTypeID).val() != ''){
                    jQuery('div#tabLT'+listingTypeID).append('<p class="comparisonMessage '+ listingTypeID +' jr_tooltipBox">'+jreviewsCompare.lang.select_more+'</p>');
                    jQuery('p.comparisonMessage').hide().fadeIn('slow', function(){
                        setTimeout( function(){
                            jQuery('p.comparisonMessage').fadeOut('slow', function(){
                                jQuery(this).remove();
                            });
                        }, 3000 );
                    });
                }
            }
            else {
                var url = jQuery(this).attr('href');
                window.location.href = url;
            }
        });
		jQuery('ul.jr_tabsNav li a').click(function(){
			var tabsContainer = jQuery('#jr_tabsContainer');
			if (tabsContainer.is(':hidden')) {
				tabsContainer.slideDown('slow');
			}
		});
	},
	insertTab: function(listingTypeID, listingTypeTitle)
	{
		var compareTabs = jQuery('#jr_compareTabs');
		compareTabs.tabs('destroy');
		jQuery('#jr_compareTabs ul.jr_tabsNav').append('<li><a href="#tabLT'+ listingTypeID +'" class="listingTypeCompare jrCompare'+ listingTypeID +'">' + listingTypeTitle + ' <span class="numSelected">(0)</span></a></li>');
		jQuery('#jr_compareTabs #jr_tabsContainer').append('<div id="tabLT'+ listingTypeID +'" class="ui-helper-clearfix"><a class="comparePrev compareBrowse compareLeft"></a><div class="jr_compareScroll"><ul class="ltList"></ul></div><a class="compareNext compareBrowse compareRight"></a></div>');

		compareURL = jreviewsCompare.compareURL.replace('type_id',listingTypeID);
		compareAllLink = '<a rel="nofollow" href="' + compareURL + '" class="compareListings jrButton listingType' +listingTypeID+ '">'+jreviewsCompare.lang.compare_all+'</a>';
		removeAllLink = '<a href="#" class="removeListings jrButton listingType' + listingTypeID + '">'+jreviewsCompare.lang.remove_all+'</a>';
		jQuery('#jr_tabsContainer #tabLT'+listingTypeID).append('<div id="jrCompare' + listingTypeID + '" class="jr_compareButtons">'+ compareAllLink + removeAllLink +'</div>');

		var numTabs = jQuery('#jr_compareTabs ul.jr_tabsNav li').length;
		compareTabs.tabs({selected: numTabs-1});
		var compareScroll = jQuery('#tabLT' + listingTypeID + ' div.jr_compareScroll').scrollable({
			items: '.ltList',
			prev: '.comparePrev',
			next: '.compareNext',
			easing: 'swing',
			keyboard: false,
            loop: false,
			onSeek: function() {
				var numListings = jreviewsCompare.getNumberOfSelectedListings(listingTypeID);
				var pagePosition = this.getIndex();
				if (numListings - pagePosition < 5) {
					jQuery('#tabLT' + listingTypeID + ' a.compareNext').css('visibility','hidden');
				} else {
					jQuery('#tabLT' + listingTypeID + ' a.compareNext').css('visibility','visible');
				}
				if (pagePosition == 0) {
					jQuery('#tabLT' + listingTypeID + ' a.comparePrev').css('visibility','hidden');
				} else {
					jQuery('#tabLT' + listingTypeID + ' a.comparePrev').css('visibility','visible');
				}
			}
		});
	},
	removeTab: function(listingTypeID)
	{
		var $tabs = jQuery('#jr_compareTabs');
		var activeTab = $tabs.tabs('option', 'selected');
		var numTabs = $tabs.tabs('length');
		if (numTabs > 1) {
			jQuery('ul.jr_tabsNav li.ui-tabs-selected').fadeOut('slow', function(){
				$tabs.tabs('remove', activeTab);
			});
			$tabs.tabs('destroy');
			(activeTab > 0) ? $tabs.tabs({selected: activeTab-1}) : $tabs.tabs({selected: 0});
		} else {
			jQuery('div#jr_compareDashboard').slideUp('slow', function(){
				$tabs.tabs('remove', activeTab);
				$tabs.tabs('destroy');
			});
		}
		var cookieName = 'jrCompare'+listingTypeID;
		jQuery.extendedjsoncookie("removeCookie", cookieName);
		jreviewsCompare.removeListingTypeFromCookie('jrCompare'+listingTypeID);
	},
	insertListingIntoComparison: function(listingType, listing, listingTypeID)
	{
		if (listing.thumbnail != undefined) {
			if (listing.thumbnail.length > 0) {
				listingThumb = '<div class="compareThumb"><a href="'+ listing.url +'"><img src="'+ listing.thumbnail +'" /></a></div>';
			} else {
				listingThumb = '';
			}
		} else {
			listingThumb = '';
		}
		var listingTitleUrl = '<a href="'+ listing.url +'">' + listing.title + '</a>';
		jQuery('div#tabLT' + listingTypeID + ' ul').append('<li id="' + listingType + '" class="ltItem"><span id="remove' + listingType + '" class="removeItem">' + listingTypeID + '</span>' + listingThumb + '<span class="compareItemTitle">' + listingTitleUrl + '</span></li>');
		jQuery('input#'+listingType).attr('checked', 'checked');
		var numListings = jreviewsCompare.getNumberOfSelectedListings(listingTypeID);
		numListings++;
		jQuery('div#jr_compareTabs a.jrCompare'+listingTypeID+' span.numSelected').html('('+ numListings +')');
	},
	removeListingFromComparison: function(listing, listingTypeID)
	{
		var $tabs = jQuery('#jr_compareTabs');
		if(jQuery('li#'+listing).siblings().length > 0) {
			jQuery('li#'+listing).fadeOut('slow', function(){
				jQuery(this).remove();
			});
			jreviewsCompare.updateNumberOfSelectedListings(listingTypeID, -1);
			jreviewsCompare.removeListingFromCookie(listing, listingTypeID);
		}
		else {
			jreviewsCompare.removeTab(listingTypeID);
		}
		var numListings = jreviewsCompare.getNumberOfSelectedListings(listingTypeID);
		var scrollApi = jQuery('#tabLT' + listingTypeID + ' div.jr_compareScroll').data("scrollable");
		var pagePosition = scrollApi.getIndex();
		if (pagePosition > 0) {
			scrollApi.seekTo(pagePosition-1);
		}
		if (numListings < 5) {
			jQuery('#tabLT' + listingTypeID + ' a.compareNext').css('visibility','hidden');
		}
	},
    initListingsSelection: function()
    {
        // select listing for comparison - checkbox
        jQuery('input.checkListing').live('click', function() {
            var listingID = jQuery(this).attr('value');
            var listingTypeID = jQuery('span#listingID'+listingID).attr('class').substring(11);
            var listingTypeTitle = jQuery('span#listingID'+listingID).text();
            var listingTitle = jQuery(this).attr('name');
			var listingURL = jQuery('span.compareListing span.listingUrl'+listingID).text();
            if (jQuery('img#thumb'+listingID).length > 0) {
                var listingThumbSrc = jQuery('img#thumb'+listingID).attr('src');
				listingThumb = '<div class="compareThumb"><a href="'+ listingURL +'"><img src="'+ listingThumbSrc +'" /></a></div>';
            } else {
                listingThumb = '';
            }
			var listingTitleUrl = '<a href="'+ listingURL +'">' + listingTitle + '</a>';
            var listingData = '<li id="listing' + listingID + '" class="ltItem">'+ listingThumb +'<span class="compareItemTitle">' + listingTitleUrl + '</span><span id="removelisting' + listingID + '" class="removeItem">' + listingTypeID + '</span></li>';
			var compareDashboard = jQuery('#jr_compareDashboard');
			var compareTabs = jQuery('#jr_compareTabs');
			var tabsContainer = jQuery('#jr_tabsContainer');
			var headerArrow = jQuery('#jr_compareArrow');

            if (jQuery(this).is(':checked')) {
				if (jQuery('div#tabLT'+listingTypeID).length > 0) {
					if (tabsContainer.is(':hidden')) {
						tabsContainer.slideDown('slow');
						headerArrow.addClass('down');
					}
					jQuery(listingData).appendTo('div#tabLT' + listingTypeID + ' ul.ltList').hide().fadeIn('slow');
					var numListings = jreviewsCompare.getNumberOfSelectedListings(listingTypeID);
					var compareScroll = jQuery('div#tabLT' + listingTypeID + ' div.jr_compareScroll').data("scrollable");
					if (numListings < jreviewsCompare.maxNumberOfListings) {
						if (numListings > 3) {
							jQuery('div#tabLT' + listingTypeID + ' a.compareNext').trigger('click');
						}
					} else {
						if (jQuery('p.comparisonMessageMax.'+listingID).val() != ''){
							jQuery(this).parent().append('<p class="comparisonMessageMax '+ listingID +' jr_tooltipBox">'+jreviewsCompare.lang.select_max+'</p>');
							jQuery('p.comparisonMessageMax').hide().fadeIn('slow', function(){
								setTimeout( function(){
									jQuery('p.comparisonMessageMax').fadeOut('slow', function(){
										jQuery(this).remove();
									});
								}, 3000 );
							});
						}
						return false;
					}
					compareTabs.tabs('select', '#tabLT'+listingTypeID);
				} else {
					if (tabsContainer.is(':hidden')) {
						tabsContainer.show();
						headerArrow.addClass('down');
					}
					jreviewsCompare.insertTab(listingTypeID, listingTypeTitle);
					jQuery(listingData).appendTo('div#tabLT' + listingTypeID + ' ul.ltList').hide().fadeIn('slow');
					if (compareDashboard.is(':hidden')) {
						compareDashboard.slideDown('slow');
					}
				}
				jreviewsCompare.updateNumberOfSelectedListings(listingTypeID, 1);
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompare"+listingTypeID, "listing"+listingID, {'title': listingTitle, 'thumbnail' : listingThumbSrc } );
				jQuery.extendedjsoncookie( "setCookieVariable","jrCompareUrls"+listingTypeID, "listing"+listingID, {'url': listingURL } );
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompareIDs", "jrCompare"+listingTypeID, listingTypeTitle );
            }
            else {
				compareTabs.tabs('select', '#tabLT'+listingTypeID);
                listing = 'listing'+listingID;
				jreviewsCompare.removeListingFromComparison(listing, listingTypeID);
            }
        });
    },
	getNumberOfSelectedListings: function(listingTypeID)
	{
		var numListings = jQuery('div#jr_compareTabs a.jrCompare'+listingTypeID+' span.numSelected').html().slice(1,-1);
		return parseInt(numListings);
	},
	updateNumberOfSelectedListings: function(listingTypeID, update)
	{
		var numListings = jreviewsCompare.getNumberOfSelectedListings(listingTypeID);
		numListings = numListings + update;
		jQuery('div#jr_compareTabs a.jrCompare'+listingTypeID+' span.numSelected').html('('+ numListings +')');
	},

    // returns cookie names and listing type names
    getCookies: function(){
        var cookieListingTypes = jQuery.extendedjsoncookie("getCookieValueDecoded","jrCompareIDs");
        if (cookieListingTypes) {
            var listingTypes = jQuery.evalJSON(cookieListingTypes);
            return listingTypes;
        }
        else { return false; }
    },

    // returns all listings from a cookie
    getListingsFromCookie: function(listingType) {
        var listingsCookie = jQuery.extendedjsoncookie( "getCookieValueDecoded", 'jrCompare'+listingType);
        if (listingsCookie) {
			var urlsCookie = jQuery.extendedjsoncookie( "getCookieValueDecoded", 'jrCompareUrls'+listingType);
            var compareListings = jQuery.evalJSON(listingsCookie);
			var compareListingUrls = jQuery.evalJSON(urlsCookie);
			var listings = {};
			jQuery.each(compareListings, function(key, value) {
				listings[key] = {
					'title' : value.title,
					'thumbnail' : value.thumbnail,
					'url' : compareListingUrls[key].url
				}
			});
            return listings;
        }
        else { return false; }
    },

    // removes a listing from cookie
    removeListingFromCookie: function (listing, listingType) {
        var cookieValue = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompare"+listingType);
        compareListings = jQuery.evalJSON(cookieValue);
        delete compareListings[listing];
        jQuery.extendedjsoncookie("removeCookie","jrCompare"+listingType);
        if (compareListings) {
            jQuery.each(compareListings, function(key, value) {
                jQuery.extendedjsoncookie("setCookieVariable","jrCompare"+listingType, key, value);
            });
        }

        var cookieUrlValue = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompareUrls"+listingType);
        compareListingUrls = jQuery.evalJSON(cookieUrlValue);
        delete compareListingUrls[listing];
        jQuery.extendedjsoncookie("removeCookie","jrCompareUrls"+listingType);
        if (compareListingUrls) {
            jQuery.each(compareListingUrls, function(key, value) {
                jQuery.extendedjsoncookie("setCookieVariable","jrCompareUrls"+listingType, key, value);
            });
        }
    },

    // removes listing type from cookie
    removeListingTypeFromCookie: function(listingType) {
        var cookieValue = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompareIDs");
        compareListingTypes = jQuery.evalJSON(cookieValue);
        delete compareListingTypes[listingType];
        jQuery.extendedjsoncookie( "removeCookie","jrCompareIDs");
        if (compareListingTypes) {
            jQuery.each(compareListingTypes, function(key, value) {
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompareIDs", key, value);
            });
        }
    }
}

/*
 * jQuery Extended JSon Cookie Plugin
 * relese 2010-01-11
 *
 * It is licensed as free software under the terms of the GNU General Public License (GPL)
 * http://www.gnu.org/licenses/gpl.html
 *
 * This plugin is based on Jquery Cookie plugin http://plugins.jquery.com/project/cookie
 *
 * Work by Rodolphe Franceschi
 */


// Class definition
function jQueryextendedjsoncookieUtils()
{

}

/* Fonction de compatibilite avec la librairie initiale */
jQueryextendedjsoncookieUtils.corewritefunction = function(argss)
{
  var name = argss[0];
  var value = argss[1];

  document.cookie = name + '=' + encodeURIComponent(value) + '; path=/';
}

/* Simple deletion of the cookie */
jQueryextendedjsoncookieUtils.removeCookie = function(argss)
{
  var cookiename = argss[0];

  varcookievalue = jQueryextendedjsoncookieUtils.getCookieValueDecoded( argss )
  if ( varcookievalue != undefined )
  {
    document.cookie = cookiename +'=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
  }
}

/* Simple write of all the cookie content with an empty value */
jQueryextendedjsoncookieUtils.writeEmptyCookie = function(argss)
{
  var cookiename = argss[0];

  document.cookie = cookiename + '=; path=/';
}

/* Simple write of all the cookie content */
jQueryextendedjsoncookieUtils.writeCookie = function(argss)
{
  jQueryextendedjsoncookieUtils.corewritefunction(argss);
}


/* Get the cookie value encoded */
jQueryextendedjsoncookieUtils.getCookieValue = function(argss)
{
  var cookiename =  argss[0];

  var fullcookievalue = jQueryextendedjsoncookieUtils.getFullCookie(argss);
  if (fullcookievalue == undefined)
  {
    return undefined;
  }
  return fullcookievalue.substring(cookiename.length + 1);
}

/* get The cookie valude decoded */
jQueryextendedjsoncookieUtils.getCookieValueDecoded = function(argss)
{
  var cookievalue = jQueryextendedjsoncookieUtils.getCookieValue(argss);
  if (cookievalue == undefined) {return undefined; };
  return decodeURIComponent(cookievalue);
}


/* Get the value of a cookie by cookie name*/
jQueryextendedjsoncookieUtils.getFullCookie = function(argss)
{
  var cookiename = argss[0];

  var cookieValue = undefined;
  var componentValueOutput = undefined;

  if (document.cookie && document.cookie != '')
  {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++)
    {
      var cookie = jQuery.trim(cookies[i]);

      if (cookie.substring(0, cookiename.length + 1) == (cookiename + '='))
      {
        componentValueOutput = cookie;
        break;
      }
    }
  }
  return componentValueOutput;
}

/*
 * Advanced function thar stores Extended attributes (not value).
 * Warning, This function must be called AT THE END OF THE CALLS
 */
jQueryextendedjsoncookieUtils.setExtendedAttributes = function(argss)
{
  var cookiename = argss[0];
  var extendedattributesarray = argss[1];

  var cookvalue = jQueryextendedjsoncookieUtils.getCookieValueDecoded(argss);
  var argstopass = Array(cookiename, cookvalue, extendedattributesarray);

  jQueryextendedjsoncookieUtils.corewritefunction(argstopass);
}

/*
 * Function that gets a variale Value from a cookie
 */
jQueryextendedjsoncookieUtils.getCookieVariable = function(argss)
{
  var cookiename = argss[0];
  var variablename = argss[1];

  var cookvalue = jQueryextendedjsoncookieUtils.getCookieValueDecoded(argss);
  if (cookvalue != '' && cookvalue != undefined)
  {
    jsonoutput_eval = jQuery.evalJSON(cookvalue);
    return jsonoutput_eval[variablename];
  }

  return undefined;
}


/*
 * Advanced function that stores a variable in the value of the cookie in Json data exchange format
 */
jQueryextendedjsoncookieUtils.setCookieVariable = function(argss)
{
  var cookiename = argss[0];
  var variablename = argss[1];
  var variablevalue = argss[2];

  var jsonoutput = undefined;

  // First, get the cookie value
  var cookvalue = jQueryextendedjsoncookieUtils.getCookieValueDecoded(Array(cookiename));

  // if cookie value is undefined, write empty cookie then set the variable
  if ( (cookvalue == undefined) || (cookvalue == '') )
  {
    jQueryextendedjsoncookieUtils.writeEmptyCookie(Array(cookiename));

    var variableunique = new Object();
    variableunique[variablename] = variablevalue;

    var chainejsonencoded = jQuery.toJSON(variableunique);

    var argstopass = new Array();
    argstopass.push(cookiename);
    argstopass.push(chainejsonencoded);

    jQueryextendedjsoncookieUtils.writeCookie(argstopass);
  }
  else
  {
    // else, add the variable to the cookie
    jsonoutput_eval = jQuery.evalJSON(cookvalue);
    jsonoutput_eval[variablename] = variablevalue;
    var chainejsonencoded = jQuery.toJSON(jsonoutput_eval);

    var argstopass = new Array();
    argstopass.push(cookiename);
    argstopass.push(chainejsonencoded);

    jQueryextendedjsoncookieUtils.writeCookie(argstopass);
  }
}

// JQuery Interface
// We assume that first argument is method name....
jQuery.extendedjsoncookie = function()
{
  // We forge dynamic call chain
  var chain = "jQueryextendedjsoncookieUtils.";
  var internalargs = new Array();
  for (var i=0; i < arguments.length; i++)
  {
     var thisarg = arguments[i];
     if (i == 0)
     {
       chain = chain + thisarg;
     }
     else
     {
       internalargs.push(thisarg);
     }
  }

  // We make an eval on it
  return eval(chain) ( internalargs );
};


(function($){$.toJSON=function(o)
{if(typeof(JSON)=='object'&&JSON.stringify)
return JSON.stringify(o);var type=typeof(o);if(o===null)
return"null";if(type=="undefined")
return undefined;if(type=="number"||type=="boolean")
return o+"";if(type=="string")
return $.quoteString(o);if(type=='object')
{if(typeof o.toJSON=="function")
return $.toJSON(o.toJSON());if(o.constructor===Date)
{var month=o.getUTCMonth()+1;if(month<10)month='0'+month;var day=o.getUTCDate();if(day<10)day='0'+day;var year=o.getUTCFullYear();var hours=o.getUTCHours();if(hours<10)hours='0'+hours;var minutes=o.getUTCMinutes();if(minutes<10)minutes='0'+minutes;var seconds=o.getUTCSeconds();if(seconds<10)seconds='0'+seconds;var milli=o.getUTCMilliseconds();if(milli<100)milli='0'+milli;if(milli<10)milli='0'+milli;return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
if(o.constructor===Array)
{var ret=[];for(var i=0;i<o.length;i++)
ret.push($.toJSON(o[i])||"null");return"["+ret.join(",")+"]";}
var pairs=[];for(var k in o){var name;var type=typeof k;if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;if(typeof o[k]=="function")
continue;var val=$.toJSON(o[k]);pairs.push(name+":"+val);}
return"{"+pairs.join(", ")+"}";}};$.evalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);return eval("("+src+")");};$.secureEvalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};$.quoteString=function(string)
{if(string.match(_escapeable))
{return'"'+string.replace(_escapeable,function(a)
{var c=_meta[a];if(typeof c==='string')return c;c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};var _escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var _meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};})(jQuery);

