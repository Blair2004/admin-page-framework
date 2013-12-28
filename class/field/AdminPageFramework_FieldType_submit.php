<?php
if ( ! class_exists( 'AdminPageFramework_FieldType_submit' ) ) :
/**
 * Defines the submit field type.
 * 
 * @package			Admin Page Framework
 * @subpackage		Admin Page Framework - Field
 * @since			2.1.5
 */
class AdminPageFramework_FieldType_submit extends AdminPageFramework_FieldType_Base {
	
	/**
	 * Returns the array of the field type specific default keys.
	 */
	protected function getDefaultKeys() { 
		return array(		
			'class_attribute'					=> 'button button-primary',
			'redirect_url'							=> null,
			'links'								=> null,
			'is_reset'							=> null,
		);	
	}

	/**
	 * Loads the field type necessary components.
	 */ 
	public function replyToFieldLoader() {
	}	
	
	/**
	 * Returns the field type specific JavaScript script.
	 */ 
	public function replyToGetScripts() {
		return "";		
	}	

	/**
	 * Returns the field type specific CSS rules.
	 */ 
	public function replyToGetStyles() {
		return 		
		"/* Submit Buttons */
		.admin-page-framework-field input[type='submit'] {
			margin-bottom: 0.5em;
		}" . PHP_EOL;		
	}
	
	/**
	 * Returns the output of the field type.
	 * @since			2.1.5			Moved from AdminPageFramework_InputField.
	 */
	public function replyToGetField( $vValue, $aField, $aOptions, $aErrors, $aFieldDefinition ) {

		$aOutput = array();
		$field_name = $aField['field_name'];
		$tag_id = $aField['tag_id'];
		$field_class_selector = $aField['field_class_selector'];
		$_aDefaultKeys = $aFieldDefinition['aDefaultKeys'];	
		
		// $aFields = $aField['repeatable'] ? 
			// ( empty( $vValue ) ? array( '' ) : ( array ) $vValue )
			// : $aField['label'];		

		
		$vValue = $this->getInputFieldValueFromLabel( $aField );
		$field_nameFlat = $this->getInputFieldNameFlat( $aField );
		foreach( ( array ) $vValue as $sKey => $sValue ) {
			$sRedirectURL = $this->getCorrespondingArrayValue( $aField['redirect_url'], $sKey, $_aDefaultKeys['redirect_url'] );
			$sLinkURL = $this->getCorrespondingArrayValue( $aField['links'], $sKey, $_aDefaultKeys['links'] );
			$sResetKey = $this->getCorrespondingArrayValue( $aField['is_reset'], $sKey, $_aDefaultKeys['is_reset'] );
			$bResetConfirmed = $this->checkConfirmationDisplayed( $sResetKey, $field_nameFlat ); 
			$aOutput[] = 
				"<div class='{$field_class_selector}' id='field-{$tag_id}_{$sKey}'>"
					// embed the field id and input id
					. "<input type='hidden' "
						. "name='__submit[{$tag_id}_{$sKey}][input_id]' "
						. "value='{$tag_id}_{$sKey}' "
					. "/>"
					. "<input type='hidden' "
						. "name='__submit[{$tag_id}_{$sKey}][field_id]' "
						. "value='{$aField['field_id']}' "
					. "/>"		
					. "<input type='hidden' "
						. "name='__submit[{$tag_id}_{$sKey}][name]' "
						. "value='{$field_nameFlat}" . ( is_array( $vValue ) ? "|{$sKey}'" : "'" )
					. "/>" 						
					// for the redirect_url key
					. ( $sRedirectURL 
						? "<input type='hidden' "
							. "name='__redirect[{$tag_id}_{$sKey}][url]' "
							. "value='" . $sRedirectURL . "' "
						. "/>" 
						. "<input type='hidden' "
							. "name='__redirect[{$tag_id}_{$sKey}][name]' "
							. "value='{$field_nameFlat}" . ( is_array( $vValue ) ? "|{$sKey}" : "'" )
						. "/>" 
						: "" 
					)
					// for the links key
					. ( $sLinkURL 
						? "<input type='hidden' "
							. "name='__link[{$tag_id}_{$sKey}][url]' "
							. "value='" . $sLinkURL . "' "
						. "/>"
						. "<input type='hidden' "
							. "name='__link[{$tag_id}_{$sKey}][name]' "
							. "value='{$field_nameFlat}" . ( is_array( $vValue ) ? "|{$sKey}'" : "'" )
						. "/>" 
						: "" 
					)
					// for the is_reset key
					. ( $sResetKey && ! $bResetConfirmed
						? "<input type='hidden' "
							. "name='__reset_confirm[{$tag_id}_{$sKey}][key]' "
							. "value='" . $field_nameFlat . "' "
						. "/>"
						. "<input type='hidden' "
							. "name='__reset_confirm[{$tag_id}_{$sKey}][name]' "
							. "value='{$field_nameFlat}" . ( is_array( $vValue ) ? "|{$sKey}'" : "'" )
						. "/>" 
						: ""
					)
					. ( $sResetKey && $bResetConfirmed
						? "<input type='hidden' "
							. "name='__reset[{$tag_id}_{$sKey}][key]' "
							. "value='" . $sResetKey . "' "
						. "/>"
						. "<input type='hidden' "
							. "name='__reset[{$tag_id}_{$sKey}][name]' "
							. "value='{$field_nameFlat}" . ( is_array( $vValue ) ? "|{$sKey}'" : "'" )
						. "/>" 
						: ""
					)
					. $this->getCorrespondingArrayValue( $aField['before_input_tag'], $sKey, $_aDefaultKeys['before_input_tag'] ) 
					. "<span class='admin-page-framework-input-button-container admin-page-framework-input-container' style='min-width:" . $this->getCorrespondingArrayValue( $aField['label_min_width'], $sKey, $_aDefaultKeys['label_min_width'] ) . "px;'>"
						. "<input "
							. "id='{$tag_id}_{$sKey}' "
							. "class='" . $this->getCorrespondingArrayValue( $aField['class_attribute'], $sKey, $_aDefaultKeys['class_attribute'] ) . "' "
							. "type='{$aField['type']}' "	// submit
							. "name=" . ( is_array( $aField['label'] ) ? "'{$field_name}[{$sKey}]' " : "'{$field_name}' " )
							. "value='" . $this->getCorrespondingArrayValue( $vValue, $sKey, $this->oMsg->__( 'submit' ) ) . "' "
							. ( $this->getCorrespondingArrayValue( $aField['is_disabled'], $sKey ) ? "disabled='Disabled' " : '' )
						. "/>"
					. "</span>"
					. $this->getCorrespondingArrayValue( $aField['after_input_tag'], $sKey, $_aDefaultKeys['after_input_tag'] )
				. "</div>" // end of admin-page-framework-field
				. ( ( $sDelimiter = $this->getCorrespondingArrayValue( $aField['delimiter'], $sKey, $_aDefaultKeys['delimiter'], true ) )
					? "<div class='delimiter' id='delimiter-{$tag_id}_{$sKey}'>" . $sDelimiter . "</div>"
					: ""
				);
				
		}
		return "<div class='admin-page-framework-field-submit' id='{$tag_id}'>" 
				. implode( '', $aOutput ) 
			. "</div>";		
	
	}
		/**
		 * A helper function for the above getSubmitField() that checks if a reset confirmation message has been displayed or not when the is_reset key is set.
		 * 
		 */
		private function checkConfirmationDisplayed( $sResetKey, $sFlatFieldName ) {
				
			if ( ! $sResetKey ) return false;
			
			$bResetConfirmed =  get_transient( md5( "reset_confirm_" . $sFlatFieldName ) ) !== false 
				? true
				: false;
			
			if ( $bResetConfirmed )
				delete_transient( md5( "reset_confirm_" . $sFlatFieldName ) );
				
			return $bResetConfirmed;
			
		}

	/*
	 *	Shared Methods 
	 */
	/**
	 * Retrieves the field name attribute whose dimensional elements are delimited by the pile character.
	 * 
	 * Instead of [] enclosing array elements, it uses the pipe(|) to represent the multi dimensional array key.
	 * This is used to create a reference the submit field name to determine which button is pressed.
	 * 
	 * @remark			Used by the import and submit field types.
	 * @since			2.0.0
	 * @since			2.1.5			Made the parameter mandatory. Changed the scope to protected from private. Moved from AdminPageFramework_InputField.
	 */ 
	protected function getInputFieldNameFlat( $aField ) {	
	
		return isset( $aField['option_key'] ) // the meta box class does not use the option key
			? "{$aField['option_key']}|{$aField['page_slug']}|{$aField['field_id']}"
			: $aField['field_id'];
		
	}			
	/**
	 * Retrieves the input field value from the label.
	 * 
	 * This method is similar to the above <em>getInputFieldValue()</em> but this does not check the stored option value.
	 * It uses the value set to the <var>label</var> key. 
	 * This is for submit buttons including export custom field type that the label should serve as the value.
	 * 
	 * @remark			The submit, import, and export field types use this method.
	 * @since			2.0.0
	 * @since			2.1.5			Moved from AdminPageFramwrork_InputField. Changed the scope to protected from private. Removed the second parameter.
	 */ 
	protected function getInputFieldValueFromLabel( $aField ) {	
		
		// If the value key is explicitly set, use it.
		if ( isset( $aField['vValue'] ) ) return $aField['vValue'];
		
		if ( isset( $aField['label'] ) ) return $aField['label'];
		
		// If the default value is set,
		if ( isset( $aField['default'] ) ) return $aField['default'];
		
	}
	
}
endif;