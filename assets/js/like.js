jQuery(document).ready( function() {

	jQuery(".bfc-post-like").click( function(event) {
		var this2 = this;
		event.preventDefault(); 
		bfcPost_id = jQuery(this).attr("data-post_id");
		bfcNonce = jQuery(this).attr("data-nonce");

		var bfcType = jQuery(this).hasClass( 'like' ) ? 'like' : 'unlike';
		var bfcType2 = jQuery(this).hasClass( 'like' ) ? 'unlike' : 'like';

		var bfcAction = 'bfc_post_' + bfcType;

		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : bfcAjax.ajaxurl,
			data : {action: bfcAction, post_id: bfcPost_id, nonce: bfcNonce},
			success: function(response) {
				if(true === response.success) {
					var oldUrl = jQuery(this2).attr("href"); // Get current url
					var newUrl = oldUrl.replace(bfcType, bfcType2); // Create new url
					jQuery(this2).attr("href", newUrl); // Set herf value
					jQuery(this2).removeClass( bfcType );
					jQuery(this2).addClass( bfcType2 );

					if ( jQuery( this2 ).find( 'span' ).first().length ) {
						jQuery( this2 ).find( 'span' ).first().html( response.data.content );
						jQuery( this2 ).children('.bfc-post-like-text').html( response.data.content );
					} else {
						jQuery( this2 ).html( response.data.content );
					}

					if ('false' == jQuery( this2 ).attr( 'aria-pressed' ) ) {
						jQuery( this2 ).attr( 'aria-pressed', 'true' );
					} else {
						jQuery( this2 ).attr( 'aria-pressed', 'false' );
					}

					var likeState = jQuery( "#post-" + bfcPost_id).find('.like-state');
					var likeText = jQuery(likeState).find('.like-text');
					if (response.data.like_users_string == 0){response.data.like_users_string = '';}
					if (response.data.like_users_string) {
						jQuery(likeState).addClass( 'has-likes' );
						jQuery(likeText).show();
					} else {
						jQuery(likeState).removeClass( 'has-likes' );
						jQuery(likeText).hide();
					}

					jQuery(likeText).attr('data-hint', response.data.tooltip );
					jQuery(likeText).html( response.data.like_users_string );
				} else {
					console.log("fail");
				}
			}
		})   
	})
});