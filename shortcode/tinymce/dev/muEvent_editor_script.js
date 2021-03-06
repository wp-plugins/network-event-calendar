jQuery( document ).ready(
	function( $ )
	{
		$(".tabs").find("li").click(
			function( event )
			{
				$(".tabs").find("li").removeClass('current') ;
				$(event.currentTarget).addClass('current') ;
				$(".panel_wrapper").children().removeClass('current') ;
				$("#" + event.currentTarget.id.replace('_tab', '_panel')).addClass('current') ;
			}
		);
		
		$("#useMonthsM").change(
			function( event ) 
			{	
				if(event.currentTarget.checked)
				{
					$("#months").removeAttr("disabled").removeClass("disabledInput") ;
					$("#startmonth").removeAttr("disabled").removeClass("disabledInput") ;
					$(".startmonthLabel").removeClass("disabledElement") ;
					
					$("#afterMonth").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$("#afterDay").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$("#afterYear").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					
					$("#beforeMonth").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$("#beforeDay").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$("#beforeYear").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$(".month").addClass("disabledElement") ;
				}
			}
		);
		$("#useMonthsR").change(
			function( event ) 
			{
				if(event.currentTarget.checked)
				{
					$("#months").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$("#startmonth").attr("disabled" , "disabled" ).addClass("disabledInput") ;
					$(".startmonthLabel").addClass("disabledElement") ;
					
					$("#afterMonth").removeAttr("disabled").removeClass("disabledInput") ;
					$("#afterDay").removeAttr("disabled").removeClass("disabledInput") ;
					$("#afterYear").removeAttr("disabled").removeClass("disabledInput") ;
					
					$("#beforeMonth").removeAttr("disabled").removeClass("disabledInput") ;
					$("#beforeDay").removeAttr("disabled").removeClass("disabledInput") ;
					$("#beforeYear").removeAttr("disabled").removeClass("disabledInput") ;
					$(".month").removeClass("disabledElement") ;
				}
			}
		);
		$("#limitresults").change(
			function(event)
			{
				if(event.currentTarget.checked)
				{
					$("#limit").removeAttr("disabled").removeClass("disabledInput") ;
				}
				else
				{
					$("#limit").attr("disabled" , "disabled" ).addClass("disabledInput") ;
				}
			}
		);
		$("#availableSites").bind("change focus blur",
			function ( event )
			{
				if( event.currentTarget.selectedIndex > -1 )
				{
					$("#addSite").removeAttr("disabled").removeClass("disabledInput") ;	
				}
				else
				{
					$("#addSite").attr("disabled", "disabled").addClass("disabledInput") ;	
				}
			}		 
		);
		$("#sites").bind("change focus blur",
			function ( event )
			{
				if( event.currentTarget.selectedIndex > -1 )
				{
					$("#removeSite").removeAttr("disabled").removeClass("disabledInput") ;	
				}
				else
				{
					$("#removeSite").attr("disabled", "disabled").addClass("disabledInput") ;	
				}
			}		 
		);
		$("#addSite").click(
			function( event )
			{
				$("#availableSites option:selected").each(
					function ( index, element )
					{
						if( $("#sites option[value='"+$(element).attr("value")+"']").length == 0 )
						{
							element = $(element) ;
							$("#sites").append($("<option></option>").attr("value" , element.attr("value") ).text( element.text( ) )); 
						}
					}
				) ;
				$( "#availableSites" ).val( -1 ).blur( ) ;
			}
		);
		$("#removeSite").click(
			function( event )
			{
				$("#sites option:selected").remove() ;
				$( "#sites" ).val( -1 ).blur( ) ;
			}
		);
		$("#useMonthsM").change() ;
		$("#useMonthsR").change() ;
		$("#limitresults").change() ;
		$("#availableSites").blur() ;
		$("#sites").blur() ;
		
		
		$deltaH = parseInt( tinyMCEPopup.editor.getLang( 'mueventsshortcode.delta_height' , '160' ) ) + 260 ;
		$(".panel").css("height", $deltaH + "px") ;
	}
) ;	

function validateForm( $ )
{
	var returnValue = true ;
	var message = "" ;
	
	if( $("input:radio[name=useMonths]:checked").val() == 1 )
	{
		
		var m = parseInt( $( "#months" ).attr( "value" ) ) ;
		if( m > 0 && m < 12 )
		{
				_months = m ;
				_startmonth = $($( "#startmonth option:selected" )[0]).attr( "value" );
		}
		else
		{
			returnValue = false ;
			message += __monthError ;
		}
	}
	else // assuming useMonthsR is selected
	{ 
		var am, ad, ay, bm, bd, by ;
		am = parseInt($("#afterMonth").val()) ;
		ad = parseInt( $( "#afterDay" ).attr( "value" ) ) ;
		ay = $( "#afterYear" ).attr( "value" ) ;
		bm = parseInt( $("#beforeMonth").val() );
		bd = parseInt( $( "#beforeDay" ).attr( "value" ) ) ;
		by = $( "#beforeYear" ).attr( "value" ) ;
		
		if( isNaN(am) || am == 0 )
		{
			returnValue = false ;
			message += __afterMonthError ; 
		}
		if( isNaN(ad) || ad < 1 || ad > 31 )
		{
			returnValue = false ;
			message += __afterDayError ;
		}
		if( parseInt( ay ) < 1970 || ay.length != 4 )
		{
			returnValue = false ;
			message += __afterYearError ;
		}
		
		if( isNaN(bm) || bm == 0 )
		{
			returnValue = false ;
			message += __beforeMonthError ;
		}
		if( isNaN(bd) || bd < 1 || bd > 31 )
		{
			returnValue = false ;
			message += __beforeDayError ;
		}
		if( parseInt( by ) < 1970 || by.length != 4 )
		{
			returnValue = false ;
			message += __beforeYearError ;
		}
		
		_events_dated_after = ay + "-" + am + "-" + ad ;
		_events_dated_before =  by + "-" + bm + "-" + bd ;
	}

	if ( $('#limitresults:checked').val( ) == 1 ) 
	{
		var l = parseInt( $("#limit").val( ) ) ;

		if( isNaN(l) || l < 1 || l > 100 )
		{
			returnValue = false ;
			message += __limitError ;
		}
		else
		{
			_limit = l  ;	
		}
	}
	
	if( $("#sites option").length == 0 ) 
	{
		returnValue = false ;
		message += __sitesError1 ;
	}
	else
	{
		var sa = new Array() ;
		$("#sites option").each( 
			function( index , element )
			{
				sa.push( $(element).val() ) ;
			}
		);
		_sites = sa.join(",") ;
	}
	
	
	_orderby = $("#orderby").val( ) ;
	_orderas = $("#orderas").val( ) ;
	
	if(!returnValue)
	{
		alert( __baseMessage + message + "\n\n" ) ;	
	}
	
	return returnValue ;
}


function insertShortTag( )
{
	
	if( validateForm( jQuery ) )
	{
		var shortcode = buildShortCode( jQuery ) ;

		var e = tinyMCEPopup.editor.selection.getNode() ;
		var newNode = tinyMCEPopup.editor.dom.create( 
			'img', 
			{
				'src'   : __placeholderURL,
				'title' : shortcode,
				'class' : 'muEventMCE mceItem' ,
				'style' : "cursor:pointer;cursor:hand;" 
			}
		) ;
		e.parentNode.insertBefore( newNode , e.nextSibling  ) ;
		e.parentNode.removeChild( e ) ;
		tinyMCEPopup.close( ) ;
	}
}



function buildShortCode( $ )
{
	var shortcode = "mu-events" ;
	
	if( $("input:radio[name=useMonths]:checked").val() == 1 )
	{
		shortcode += ' months="' + _months + '"'
		shortcode += ' startmonth="' + _startmonth + '"'
	}
	else
	{
		shortcode += ' events_dated_after="' + _events_dated_after + '"'
		shortcode += ' events_dated_before="' + _events_dated_before + '"'
	}
	
	if ( $('#limitresults:checked').val( ) == 1 ) 
	{
		shortcode += ' limit="' + _limit + '"'
	}
	
	shortcode += ' orderby="' + _orderby + '"'
	shortcode += ' orderas="' + _orderas + '"'
	shortcode += ' sites="' + _sites + '"'
	
	return shortcode ;
}