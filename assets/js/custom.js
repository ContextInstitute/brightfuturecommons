/* This is your custom Javascript */

jQuery( document ).foundation();
/*
These functions make sure WordPress
and Foundation play nice together.
*/
jQuery( document ).ready( function() {// Remove empty P tags created by WP inside of Accordion and Orbit
	jQuery( '.accordion p:empty, .orbit p:empty' ).remove();// Adds Flex Video to YouTube and Vimeo Embeds
	jQuery( 'iframe[src*="youtube.com"], iframe[src*="vimeo.com"]' ).each( function() {
		if ( jQuery( this ).innerWidth() / jQuery( this ).innerHeight() > 1.5 ) {
			jQuery( this ).wrap( "<div class='widescreen responsive-embed'/>" );
		} else {
			jQuery( this ).wrap( "<div class='responsive-embed'/>" );
		}
	} );

	jQuery( '#buddypress .bp-wrap, #content .bfc-user-panels' ).on( 'click', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonAction );
	jQuery( '#buddypress .bp-wrap, #content .bfc-user-panels' ).on( 'blur', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonRevert );
	jQuery( '#buddypress .bp-wrap, #content .bfc-user-panels' ).on( 'mouseover', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonHover );
	jQuery( '#buddypress .bp-wrap, #content .bfc-user-panels' ).on( 'mouseout', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonHoverout );
	jQuery( '.bp-nouveau .type-post' ).on( 'click', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonAction );
	jQuery( '.bp-nouveau .type-post' ).on( 'blur', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonRevert );
	jQuery( '.bp-nouveau .type-post' ).on( 'mouseover', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonHover );
	jQuery( '.bp-nouveau .type-post' ).on( 'mouseout', '[data-bp-btn-action]', bp.Nouveau, bp.Nouveau.buttonHoverout );
} );
