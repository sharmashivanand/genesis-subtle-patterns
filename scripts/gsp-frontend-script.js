/**
 *	Fronend UI builder js
 *	Builds the pattern slider UI for the frontend
 */

var GspFrontendPatterns = {
	toggleState: 			true,
	subtleContainer: 		'<div id="subtlepattern-container">'
							+ '<div class="pattern-loading">Just loading..</div>'
							+ '</div>',
	subtleSlider: 			'<div class="gsp-pattern-container-header">'
							+ '<p>Choose a pattern below to preview it live on your site. Hit the "Save Background" button once you\'ve chosen a background for your site.</p><div class="hide-pattern-selector dashicons dashicons-dismiss" onclick="GspFrontendPatterns.toggleBox()">Hide this Panel</span>'
							+ '</div></div>'
							+ '<div class="flexslider carousel">'
							+ '<ul class="slides">'
							+ '</ul>'
							+ '</div><div class="gsp-pattern-container-footer">'
							+ '<div class="gsp-footer-creds gsp-columns">'
							+ '<p>Background patterns from <a href="http://www.subtlepatterns.com" target="_blank">SubtlePatterns.Com</a></p><p class="author">Plugin by <a href="https://binaryturf.com/genesis-developer/" title="Binary Turf Web Technologies">Shivanand Sharma</a></p>'
							+ '</div><div class="gsp-buttons gsp-columns">'
							+ '<div class="save-pattern" onclick="GspFrontendPatterns.saveBackground()">Save Background</div></div>'
							+ '<div class="clearfix"></div></div>',
    subtleitem:     		'<li>'
    						+ '<div class="pattern-thumb" style="background-image: url(\'{{url}}\')" onclick="GspFrontendPatterns.setBackground(\'{{fetch}}\')">'
    						+ '<div class="pattern-name">{{name}}</div>'
    						+ '</div>'
    						+ '</li>',
    selectedBackground: 	'',
	inject: function() {
		var self = this;
		jQuery('body').prepend( self.subtleContainer );
		this.update();

	},

	update: function() {
		
		var container = jQuery('#subtlepattern-container');
        var self = this;

		jQuery.post( ajaxurl, {'action':'gsploader','action_type':'getPatterns'}, function( response ) {

			response = jQuery.parseJSON( response );

			if( response.error ) {
                alert(response.error);
                return;
			}

			container.html( self.subtleSlider );
			var ul = jQuery('#subtlepattern-container').find('ul');

			jQuery.each(response, function( k, v ) {
				var itemtemplate = self.subtleitem;
				ul.append( itemtemplate.replace('{{url}}', v).replace('{{fetch}}', v).replace('{{name}}', k) );
            }); 

            jQuery('.flexslider').flexslider({
				animation: "slide",
				animationLoop: false,
				itemWidth: 210,
				itemMargin: 5,
				slideshow: false,
				controlNav: false 
			});

		});

	},

	setBackground: function( url ) {
		
		jQuery('body').css('background-image', 'url(' + url + ')');
		this.selectedBackground = url;

	},

	saveBackground: function() {
		
		if( this.selectedBackground == '' ) {
			alert('No background selected! Please select any background before you try to save!');
			return;
		}
		jQuery('.hide-pattern-selector').html('Please wait, while the patterns are loaded');

        jQuery.post( ajaxurl, {'action':'gsploader','action_type':'setBackground', 'url': this.selectedBackground}, function( response ) {
            
            response = jQuery.parseJSON( response );
            
            if( response.success  ) window.location.reload();
            	else alert( response.error );
        });

	},

	toggleBox: function() {
		
		if( this.toggleState ) {
			jQuery('#subtlepattern-container').animate({
				'bottom' : '-9999px',
			}, 400);
			this.toggleState = false;
		} else {
			jQuery('#subtlepattern-container').animate({
				'bottom' : '50px',
			}, 400);
			this.toggleState = true;
		}
	}

}

jQuery(document).ready(function() {
    
    GspFrontendPatterns.inject();
                    
});