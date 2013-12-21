<?php
if ( ! class_exists( 'AdminPageFramework_InputFieldType_media' ) ) :
/**
 * Defines the media field type.
 * 
 * @package			Admin Page Framework
 * @subpackage		Admin Page Framework - Field
 * @since			2.1.5
 */
class AdminPageFramework_InputFieldType_media extends AdminPageFramework_InputFieldType_image {
	
	/**
	 * Returns the array of the field type specific default keys.
	 */
	protected function getDefaultKeys() { 
		return array(
			'attributes_to_capture'					=> array(),
			'size'									=> 60,
			'vMaxLength'							=> 400,
			'sTickBoxTitle' 						=> '',		// ( string ) This is for the image field type.
			'sLabelUseThis' 						=> '',		// ( string ) This is for the image field type.			
			'allow_external_source' 					=> true,	// ( boolean ) Indicates whether the media library box has the From URL tab.
		);	
	}

	/**
	 * Loads the field type necessary components.
	 */ 
	public function replyToFieldLoader() {
		$this->enqueueMediaUploader();
	}	
	
	/**
	 * Returns the field type specific JavaScript script.
	 */ 
	public function replyToGetInputScripts() {
		return $this->getScript_CustomMediaUploaderObject()	. PHP_EOL	// defined in the parent class
			. $this->getScript_MediaUploader(
				"admin_page_framework", 
				$this->oMsg->__( 'upload_file' ),
				$this->oMsg->__( 'use_this_file' )
			);
	}	
		/**
		 * Returns the media uploader JavaScript script to be loaded in the head tag of the created admin pages.
		 * 
		 * @since			2.1.3
		 * @since			2.1.5			Moved from ... Chaned the name from getMediaUploaderScript().
		 */
		private function getScript_MediaUploader( $sReferrer, $sThickBoxTitle, $sThickBoxButtonUseThis ) {
			
			if ( ! function_exists( 'wp_enqueue_media' ) )	// means the WordPress version is 3.4.x or below
				return "
					jQuery( document ).ready( function(){
						jQuery( '.select_media' ).click( function() {
							pressed_id = jQuery( this ).attr( 'id' );
							field_id = pressed_id.substring( 13 );	// remove the select_file_ prefix
							var fExternalSource = jQuery( this ).attr( 'data-enable_external_source' );					
							tb_show( '{$sThickBoxTitle}', 'media-upload.php?post_id=1&amp;enable_external_source=' + fExternalSource + '&amp;referrer={$sReferrer}&amp;button_label={$sThickBoxButtonUseThis}&amp;type=media&amp;TB_iframe=true', false );
							return false;	// do not click the button after the script by returning false.
						});
						
						window.original_send_to_editor = window.send_to_editor;
						window.send_to_editor = function( sRawHTML, param ) {

							var sHTML = '<div>' + sRawHTML + '</div>';	// This is for the 'From URL' tab. Without the wrapper element. the below attr() method don't catch attributes.
							var src = jQuery( 'a', sHTML ).attr( 'href' );
							var classes = jQuery( 'a', sHTML ).attr( 'class' );
							var id = ( classes ) ? classes.replace( /(.*?)wp-image-/, '' ) : '';	// attachment ID	
						
							// If the user wants to save relavant attributes, set them.
							jQuery( '#' + field_id ).val( src );	// sets the image url in the main text field. The url field is mandatory so it does not have the suffix.
							jQuery( '#' + field_id + '_id' ).val( id );			
								
							// restore the original send_to_editor
							window.send_to_editor = window.original_send_to_editor;
							
							// close the thickbox
							tb_remove();	

						}
					});
				";
				
			return "
			jQuery( document ).ready( function(){		
				// Global Function Literal 
				setAPFMediaUploader = function( sInputID, fMultiple, fExternalSource ) {

					jQuery( '#select_media_' + sInputID ).unbind( 'click' );	// for repeatable fields
					jQuery( '#select_media_' + sInputID ).click( function( e ) {
						
						window.wpActiveEditor = null;						
						e.preventDefault();
						
						// If the uploader object has already been created, reopen the dialog
						if ( media_uploader ) {
							media_uploader.open();
							return;
						}		
						
						// Store the original select object in a global variable
						oAPFOriginalMediaUploaderSelectObject = wp.media.view.MediaFrame.Select;
						
						// Assign a custom select object.
						wp.media.view.MediaFrame.Select = fExternalSource ? getAPFCustomMediaUploaderSelectObject() : oAPFOriginalMediaUploaderSelectObject;
						var media_uploader = wp.media({
							title: '{$sThickBoxTitle}',
							button: {
								text: '{$sThickBoxButtonUseThis}'
							},
							multiple: fMultiple  // Set this to true to allow multiple files to be selected
						});
			
						// When the uploader window closes, 
						media_uploader.on( 'close', function() {

							var state = media_uploader.state();
							
							// Check if it's an external URL
							if ( typeof( state.props ) != 'undefined' && typeof( state.props.attributes ) != 'undefined' ) 
								var image = state.props.attributes;	
							
							// If the image variable is not defined at this point, it's an attachment, not an external URL.
							if ( typeof( image ) !== 'undefined'  ) {
								setPreviewElement( sInputID, image );
							} else {
								
								var selection = media_uploader.state().get( 'selection' );
								selection.each( function( attachment, index ) {
									attachment = attachment.toJSON();
									if( index == 0 ){	
										// place first attachment in field
										setPreviewElement( sInputID, attachment );
									} else{
										
										var field_container = jQuery( '#' + sInputID ).closest( '.admin-page-framework-field' );
										var new_field = addAPFRepeatableField( field_container.attr( 'id' ) );
										var sInputIDOfNewField = new_field.find( 'input' ).attr( 'id' );
										setPreviewElement( sInputIDOfNewField, attachment );
			
									}
								});				
								
							}
							
							// Restore the original select object.
							wp.media.view.MediaFrame.Select = oAPFOriginalMediaUploaderSelectObject;	
							
						});
						
						// Open the uploader dialog
						media_uploader.open();											
						return false;       
					});	
				
					var setPreviewElement = function( sInputID, image ) {
									
						// If the user want the attributes to be saved, set them in the input tags.
						jQuery( '#' + sInputID ).val( image.url );		// the url field is mandatory so  it does not have the suffix.
						jQuery( '#' + sInputID + '_id' ).val( image.id );				
						jQuery( '#' + sInputID + '_caption' ).val( jQuery( '<div/>' ).text( image.caption ).html() );				
						jQuery( '#' + sInputID + '_description' ).val( jQuery( '<div/>' ).text( image.description ).html() );				
						
					}
				}		
				
			});";
		}
	/**
	 * Returns the field type specific CSS rules.
	 */ 
	public function replyToGetInputStyles() {
		return
		"/* Media Uploader Button */
			.admin-page-framework-field-media input {
				margin-right: 0.5em;
			}
			.select_media.button.button-small {
				vertical-align: baseline;
			}		
		";
	}
	
	/**
	 * Returns the output of the field type.
	 * 
	 * @since			2.1.5
	 */
	public function replyToGetInputField( $vValue, $aField, $aOptions, $aErrors, $aFieldDefinition ) {

		$aOutput = array();
		$sFieldName = $aField['sFieldName'];
		$sTagID = $aField['sTagID'];
		$sFieldClassSelector = $aField['sFieldClassSelector'];
		$_aDefaultKeys = $aFieldDefinition['aDefaultKeys'];	
		
		$aFields = $aField['repeatable'] ? 
			( empty( $vValue ) ? array( '' ) : ( array ) $vValue )
			: $aField['label'];			
		$bMultipleFields = is_array( $aFields );	
		$bRepeatable = $aField['repeatable'];			
			
		foreach( ( array ) $aFields as $sKey => $sLabel ) 
			$aOutput[] =
				"<div class='{$sFieldClassSelector}' id='field-{$sTagID}_{$sKey}'>"					
					. $this->getMediaInputTags( $vValue, $aField, $sFieldName, $sTagID, $sKey, $sLabel, $bMultipleFields, $_aDefaultKeys )
				. "</div>"	// end of admin-page-framework-field
				. ( ( $sDelimiter = $this->getCorrespondingArrayValue( $aField['delimiter'], $sKey, $_aDefaultKeys['delimiter'], true ) )
					? "<div class='delimiter' id='delimiter-{$sTagID}_{$sKey}'>" . $sDelimiter . "</div>"
					: ""
				);
				
		return "<div class='admin-page-framework-field-media' id='{$sTagID}'>" 
				. implode( PHP_EOL, $aOutput ) 
			. "</div>";		
			
	}
		/**
		 * A helper function for the above getImageField() method to return input elements.
		 * 
		 * @since			2.1.3
		 */
		private function getMediaInputTags( $vValue, $aField, $sFieldName, $sTagID, $sKey, $sLabel, $bMultipleFields, $_aDefaultKeys ) {
	
			// If the saving extra attributes are not specified, the input field will be single only for the URL. 
			$iCountAttributes = count( ( array ) $aField['attributes_to_capture'] );	
			
			// The URL input field is mandatory as the preview element uses it.
			$aOutputs = array(
				( $sLabel && ! $aField['repeatable']
					? "<span class='admin-page-framework-input-label-string' style='min-width:" . $this->getCorrespondingArrayValue( $aField['labelMinWidth'], $sKey, $_aDefaultKeys['labelMinWidth'] ) . "px;'>" . $sLabel . "</span>" 
					: ''
				)
				. "<input id='{$sTagID}_{$sKey}' "	// the main url element does not have the suffix of the attribute
					. "class='" . $this->getCorrespondingArrayValue( $aField['class_attribute'], $sKey, $_aDefaultKeys['class_attribute'] ) . "' "
					. "size='" . $this->getCorrespondingArrayValue( $aField['size'], $sKey, $_aDefaultKeys['size'] ) . "' "
					. "maxlength='" . $this->getCorrespondingArrayValue( $aField['vMaxLength'], $sKey, $_aDefaultKeys['vMaxLength'] ) . "' "
					. "type='text' "	// text
					. "name='" . ( $bMultipleFields ? "{$sFieldName}[{$sKey}]" : "{$sFieldName}" ) . ( $iCountAttributes ? "[url]" : "" ) .  "' "
					. "value='" . ( $this->getMediaInputValue( $vValue, $sKey, $bMultipleFields, $iCountAttributes ? 'url' : '', $_aDefaultKeys ) ) . "' "
					. ( $this->getCorrespondingArrayValue( $aField['vDisable'], $sKey ) ? "disabled='Disabled' " : '' )
					. ( $this->getCorrespondingArrayValue( $aField['vReadOnly'], $sKey ) ? "readonly='readonly' " : '' )
				. "/>"	
			);
			
			// Add the input fields for saving extra attributes. It overrides the name attribute of the default text field for URL and saves them as an array.
			foreach( ( array ) $aField['attributes_to_capture'] as $sAttribute )
				$aOutputs[] = 
					"<input id='{$sTagID}_{$sKey}_{$sAttribute}' "
						. "class='" . $this->getCorrespondingArrayValue( $aField['class_attribute'], $sKey, $_aDefaultKeys['class_attribute'] ) . "' "
						. "type='hidden' " 	// other additional attributes are hidden
						. "name='" . ( $bMultipleFields ? "{$sFieldName}[{$sKey}]" : "{$sFieldName}" ) . "[{$sAttribute}]' " 
						. "value='" . $this->getMediaInputValue( $vValue, $sKey, $bMultipleFields, $sAttribute, $_aDefaultKeys  ) . "' "
						. ( $this->getCorrespondingArrayValue( $aField['vDisable'], $sKey ) ? "disabled='Disabled' " : '' )
					. "/>";
			
			// Returns the outputs as well as the uploader buttons and the preview element.
			return 
				"<div class='admin-page-framework-input-label-container admin-page-framework-input-container media-field'>"
					. "<label for='{$sTagID}_{$sKey}' >"
						. $this->getCorrespondingArrayValue( $aField['vBeforeInputTag'], $sKey, $_aDefaultKeys['vBeforeInputTag'] )
						. implode( PHP_EOL, $aOutputs ) . PHP_EOL
						. $this->getCorrespondingArrayValue( $aField['vAfterInputTag'], $sKey, $_aDefaultKeys['vAfterInputTag'] )
					. "</label>"
				. "</div>"
				. $this->getMediaUploaderButtonScript( "{$sTagID}_{$sKey}", $aField['repeatable'] ? true : false, $aField['allow_external_source'] ? true : false );
			
		}
		/**
		 * A helper function for the above getMediaInputTags() method that retrieve the specified input field value.
		 * @since			2.1.3
		 */
		private function getMediaInputValue( $vValue, $sKey, $bMultipleFields, $sCaptureAttribute, $_aDefaultKeys ) {	

			$vValue = $bMultipleFields
				? $this->getCorrespondingArrayValue( $vValue, $sKey, $_aDefaultKeys['default'] )
				: ( isset( $vValue ) ? $vValue : $_aDefaultKeys['default'] );

			return $sCaptureAttribute
				? ( isset( $vValue[ $sCaptureAttribute ] ) ? $vValue[ $sCaptureAttribute ] : "" )
				: $vValue;
			
		}		
		/**
		 * A helper function for the above getMediaInputTags() method to add a image button script.
		 * 
		 * @since			2.1.3
		 */
		private function getMediaUploaderButtonScript( $sInputID, $bRpeatable, $bExternalSource ) {
			
			$sButton ="<a id='select_media_{$sInputID}' "
						. "href='#' "
						. "class='select_media button button-small'"
						. "data-uploader_type='" . ( function_exists( 'wp_enqueue_media' ) ? 1 : 0 ) . "'"
						. "data-enable_external_source='" . ( $bExternalSource ? 1 : 0 ) . "'"
					. ">"
						. $this->oMsg->__( 'select_file' )
				."</a>";
			
			$sScript = "
				if ( jQuery( 'a#select_media_{$sInputID}' ).length == 0 ) {
					jQuery( 'input#{$sInputID}' ).after( \"{$sButton}\" );
				}			
			" . PHP_EOL;

			if( function_exists( 'wp_enqueue_media' ) )	// means the WordPress version is 3.5 or above
				$sScript .="
					jQuery( document ).ready( function(){			
						setAPFMediaUploader( '{$sInputID}', '{$bRpeatable}', '{$bExternalSource}' );
					});" . PHP_EOL;	
					
			return "<script type='text/javascript'>" . $sScript . "</script>" . PHP_EOL;

		}	
		
}
endif;