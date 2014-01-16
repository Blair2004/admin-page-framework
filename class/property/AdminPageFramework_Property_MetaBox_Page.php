<?php
if ( ! class_exists( 'AdminPageFramework_Property_MetaBox_Page' ) ) :
/**
 * Provides the space to store the shared properties for meta boxes.
 * 
 * This class stores various types of values. This is used to encapsulate properties so that it helps to avoid naming conflicts.
 * 
 * @since			3.0.0
 * @package			Admin Page Framework
 * @subpackage		Admin Page Framework - Property
 * @extends			AdminPageFramework_Property_Base
 */
class AdminPageFramework_Property_MetaBox_Page extends AdminPageFramework_Property_MetaBox {

	/**
	 * The condition array for page slugs associated with the meta box.
	 * 
	 * This is used in the meta box class for pages.
	 * 
	 * @since			3.0.0
	 */
	public $aPageSlugs = array();
	
	/**
	 * Stores the admin page object currently browsed.
	 * @since			3.0.0
	 */
	public $oAdminPage;
	
	public $aHelpTabs = array();
	
	function __construct() {		
		
		add_action( 'admin_menu', array( $this, '_replyToSetUpProperties' ), 100 );			// this must be done after the menu class finishes building the menu with the _replyToBuildMenu() method.
		
		// Call the parent constructor.
		$aArgs = func_get_args();
		call_user_func_array( array( $this, "parent::__construct" ), $aArgs );
		
	} 	
	
	/**
	 * Determines the current page and sets the appropriate properties.
	 * @since			3.0.0
	 * @internal
	 */
	public function _replyToSetUpProperties() {
		
		if ( ! isset( $_GET['page'] ) ) return;		
				
		$this->oAdminPage = $this->_getOwnerClass( $_GET['page'] );
		if ( ! $this->oAdminPage ) return;
		
		$this->aHelpTabs = $this->oAdminPage->oProp->aHelpTabs;	// the $this->oHelpPane object access it.
		
		$this->oAdminPage->oProp->bEnableForm = true;	// enable the form tag
		
		$this->aOptions = $this->oAdminPage->oProp->aOptions;
		
	}
		
	/**
	 * Retrieves the screen ID (hook suffix) of the given page slug.
	 * @since			3.0.0
	 * @internal
	 */
	public function _getScreenIDOfPage( $sPageSlug ) {
		
		return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) )
			? $oAdminPage->oProp->aPages[ $sPageSlug ]['_page_hook']
			: '';
		
	}	
	
	/**
	 * Checks if the given page slug is one of the pages added by the framework.
	 * 
	 * @sicne			3.0.0
	 * @return			boolean			Returns true if it is of framework's added page; otherwise, false.
	 */
	public function isPageAdded( $sPageSlug='' ) {	
		
		return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) )
			? $oAdminPage->oProp->isPageAdded( $sPageSlug )
			: false;

	}
	
	/**
	 * Retrieves the default in-page tab from the given tab slug.
	 * 
	 * @since			3.0.0
	 * @remark			Used in the __call() method in the main class.
	 * @return			string			The default in-page tab slug if found; otherwise, an empty string.
	 */ 		
	public function getDefaultInPageTab( $sPageSlug ) {
	
		if ( ! $sPageSlug ) return '';		
		return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) )
			? $oAdminPage->oProp->getDefaultInPageTab( $sPageSlug )
			: '';	

	}	
	
	/**
	 * Returns the option key for the given page slug that is supposed to be one of the added page by the framework.
	 * @since			3.0.0
	 */
	public function getOptionKey( $sPageSlug ) {
		
		if ( ! $sPageSlug ) return '';		
		return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) )
			? $oAdminPage->oProp->sOptionKey
			: '';			
		
	}
	/**
	 * Returns the class object that owns the page of the given page slug.
	 * @since			3.0.0
	 * @internal
	 */
	private function _getOwnerClass( $sPageSlug ) {
		
		foreach( $GLOBALS['aAdminPageFramework']['aPageClasses'] as $oClass )
			if ( $oClass->oProp->isPageAdded( $sPageSlug ) )
				return $oClass;
		return null;
		
	}
}
endif;