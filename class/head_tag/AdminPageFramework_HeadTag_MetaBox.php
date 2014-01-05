<?php
if ( ! class_exists( 'AdminPageFramework_HeadTag_MetaBox' ) ) :
/**
 * Provides methods to enqueue or insert head tag elements into the head tag for the post type class.
 * 
 * @since			2.1.5
 * 
 */
class AdminPageFramework_HeadTag_MetaBox extends AdminPageFramework_HeadTag_Base {
	
	/**
	 * Appends the CSS rules of the framework in the head tag. 
	 * @since			2.0.0
	 * @since			2.1.5			Moved from AdminPageFramework_MetaBox. Changed the name from addAtyle() to replyToAddStyle().
	 * @remark			A callback for the <em>admin_head</em> hook.
	 * @internal
	 */ 	
	public function _replyToAddStyle() {
	
		// If it's not post (post edit) page nor the post type page,
		if ( 
			! (
				in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php', ) ) 
				&& ( 
					( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $this->oProp->aPostTypes ) )
					|| ( isset( $_GET['post'], $_GET['action'] ) && in_array( get_post_type( $_GET['post'] ), $this->oProp->aPostTypes ) )		// edit post page
				) 
			)
		) return;	
	
		$this->_printCommonStyles();
		$this->_printClassSpecificStyles();
			
	}
		/**
		 *	Prints the inline stylesheet of this class stored in this class property.
		 *	@since			3.0.0
		 */
		private function _printClassSpecificStyles() {
				
			$oCaller = $this->oProp->_getParentObject();		

			// Print out the filtered styles.
			$sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_{$this->oProp->sClassName}", $this->oProp->sStyle );
			if ( $sStyle )
				echo "<style type='text/css' id='admin-page-framework-style-meta-box-{$this->oProp->sClassName}'>{$sStyle}</style>";
				
			$sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_{$this->oProp->sClassName}", $this->oProp->sStyleIE );
			if ( $sStyleIE )
				echo  "<!--[if IE]><style type='text/css' id='admin-page-framework-ie-style-meta-box-{$this->oProp->sClassName}'>{$sStyleIE}</style><![endif]-->";
		
		}
		/**
		 * Prints the inline stylesheet of the meta-box common CSS rules with the style tag.
		 * 
		 * @remark			The meta box class may be instantiated multiple times so use a global flag.
		 * @since			3.0.0
		 */
		private function _printCommonStyles() {
			
			$sBaseClassName = get_class();
			if ( isset( $GLOBALS[ "{$sBaseClassName}_StyleLoaded" ] ) && $GLOBALS[ "{$sBaseClassName}_StyleLoaded" ] ) return;
			$GLOBALS[ "{$sBaseClassName}_StyleLoaded" ] = true;			
			
			$oCaller = $this->oProp->_getParentObject();				
			$sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_common_{$this->oProp->sClassName}", AdminPageFramework_Property_MetaBox::$_sDefaultStyle );
			if ( $sStyle )
				echo "<style type='text/css' id='admin-page-framework-style-meta-box-common'>{$sStyle}</style>";

			$sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_common_{$this->oProp->sClassName}", AdminPageFramework_Property_MetaBox::$_sDefaultStyleIE );
			if ( $sStyleIE )
				echo "<!--[if IE]><style type='text/css' id='admin-page-framework-style-meta-box-common'>{$sStyleIE}</style><![endif]-->";
				
		}
	/**
	 * Appends the JavaScript script of the framework in the head tag. 
	 * @since			2.0.0
	 * @since			2.1.5			Moved from AdminPageFramework_MetaBox. Changed the name from addScript() to replyToAddScript().
	 * @remark			A callback for the <em>admin_head</em> hook.
	 * @internal
	 */ 
	public function _replyToAddScript() {

		// If it's not post (post edit) page nor the post type page, do not add scripts for media uploader.
		if ( 
			! (
				in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php', ) ) 
				&& ( 
					( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $this->oProp->aPostTypes ) )
					|| ( isset( $_GET['post'], $_GET['action'] ) && in_array( get_post_type( $_GET['post'] ), $this->oProp->aPostTypes ) )		// edit post page
				) 
			)
		) return;	
	
		$this->_printCommonScripts();
		$this->_printClassSpecificScripts();
			
	}	
		/**
		 *	Prints the inline scripts of this class stored in this class property.
		 *	@since			3.0.0
		 */
		private function _printClassSpecificScripts() {
				
			$sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getParentObject(), "script_{$this->oProp->sClassName}", $this->oProp->sScript );
			if ( $sScript )
				echo "<script type='text/javascript' id='admin-page-framework-script-meta-box-{$this->oProp->sClassName}'>{$sScript}</script>";				
	
		}
		/**
		 * Prints the inline scripts of the meta-box common scripts.
		 * 
		 * @remark			The meta box class may be instantiated multiple times so use a global flag.
		 * @since			3.0.0
		 */
		private function _printCommonScripts() {
			
			$sBaseClassName = get_class();
			if ( isset( $GLOBALS[ "{$sBaseClassName}_ScriptLoaded" ] ) && $GLOBALS[ "{$sBaseClassName}_ScriptLoaded" ] ) return;
			$GLOBALS[ "{$sBaseClassName}_ScriptLoaded" ] = true;
			
			$sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getParentObject(), "script_common_{$this->oProp->sClassName}", AdminPageFramework_Property_MetaBox::$_sDefaultScript );
			if ( $sScript )
				echo "<script type='text/javascript' id='admin-page-framework-style-meta-box-common'>{$sScript}</script>";
		
		}	
	
	
	/**
	 * Enqueues styles by page slug and tab slug.
	 * 
	 * @since			2.1.5
	 * @internal
	 */
	public function _enqueueStyles( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) {
		
		$aHandleIDs = array();
		foreach( ( array ) $aSRCs as $sSRC )
			$aHandleIDs[] = $this->_enqueueStyle( $sSRC, $aPostTypes, $aCustomArgs );
		return $aHandleIDs;
		
	}
	/**
	 * Enqueues a style by page slug and tab slug.
	 * 
	 * <h4>Custom Argument Array for the Fourth Parameter</h4>
	 * <ul>
	 * 	<li><strong>handle_id</strong> - ( optional, string ) The handle ID of the stylesheet.</li>
	 * 	<li><strong>dependencies</strong> - ( optional, array ) The dependency array. For more information, see <a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_style">codex</a>.</li>
	 * 	<li><strong>version</strong> - ( optional, string ) The stylesheet version number.</li>
	 * 	<li><strong>media</strong> - ( optional, string ) the description of the field which is inserted into after the input field tag.</li>
	 * </ul>
	 * 
	 * @remark			The user may use this method.
	 * @since			2.1.5			
	 * @see				http://codex.wordpress.org/Function_Reference/wp_enqueue_style
	 * @param			string			$sSRC				The URL of the stylesheet to enqueue, the absolute file path, or the relative path to the root directory of WordPress. Example: '/css/mystyle.css'.
	 * @param			array			$aPostTypes		(optional) The post type slugs that the stylesheet should be added to. If not set, it applies to all the pages of the post types.
	 * @param 			array			$aCustomArgs		(optional) The argument array for more advanced parameters.
	 * @return			string			The script handle ID. If the passed url is not a valid url string, an empty string will be returned.
	 * @internal
	 */	
	public function _enqueueStyle( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) {
		
		$sSRC = trim( $sSRC );
		if ( empty( $sSRC ) ) return '';
		if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return '';	// if already set
		
		$sSRC = $this->oUtil->resolveSRC( $sSRC );
		
		$sSRCHash = md5( $sSRC );	// setting the key based on the url prevents duplicate items
		$this->oProp->aEnqueuingStyles[ $sSRCHash ] = $this->oUtil->uniteArrays( 
			( array ) $aCustomArgs,
			array(		
				'sSRC' => $sSRC,
				'aPostTypes' => empty( $aPostTypes ) ? $this->oProp->aPostTypes : $aPostTypes,
				'sType' => 'style',
				'handle_id' => 'style_' . $this->oProp->sClassName . '_' .  ( ++$this->oProp->iEnqueuedStyleIndex ),
			),
			self::$_aStructure_EnqueuingScriptsAndStyles
		);
		return $this->oProp->aEnqueuingStyles[ $sSRCHash ][ 'handle_id' ];
		
	}
	
	/**
	 * Enqueues scripts by page slug and tab slug.
	 * 
	 * @since			2.1.5
	 * @internal
	 */
	public function _enqueueScripts( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) {
		
		$aHandleIDs = array();
		foreach( ( array ) $aSRCs as $sSRC )
			$aHandleIDs[] = $this->_enqueueScript( $sSRC, $aPostTypes, $aCustomArgs );
		return $aHandleIDs;
		
	}	
	/**
	 * Enqueues a script by page slug and tab slug.
	 * 
	 * <h4>Custom Argument Array for the Fourth Parameter</h4>
	 * <ul>
	 * 	<li><strong>handle_id</strong> - ( optional, string ) The handle ID of the script.</li>
	 * 	<li><strong>dependencies</strong> - ( optional, array ) The dependency array.  Array of the handles of all the registered scripts that this script depends on. For more information, see <a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_script">codex</a>.</li>
	 * 	<li><strong>version</strong> - ( optional, string ) The stylesheet version number.</li>
	 * 	<li><strong>translation</strong> - ( optional, array ) The translation array. The handle ID will be used for the object name.</li>
	 * 	<li><strong>in_footer</strong> - ( optional, boolean ) Whether to enqueue the script before < / head > or before < / body > Default: <code>false</code>.</li>
	 * </ul>	 
	 * 
	 * 
	 * @since			2.1.5			
	 * @see				http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 * @param			string			$sSRC				The URL of the stylesheet to enqueue, the absolute file path, or relative path to the root directory of WordPress. Example: '/js/myscript.js'.
	 * @param			array			$aPostTypes		(optional) The post type slugs that the script should be added to. If not set, it applies to all the pages with the post type slugs.
	 * @param 			array			$aCustomArgs		(optional) The argument array for more advanced parameters.
	 * @return			string			The script handle ID. If the passed url is not a valid url string, an empty string will be returned.
	 * @internal
	 */
	public function _enqueueScript( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) {
		
		$sSRC = trim( $sSRC );
		if ( empty( $sSRC ) ) return '';
		if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return '';	// if already set
		
		$sSRC = $this->oUtil->resolveSRC( $sSRC );
		
		$sSRCHash = md5( $sSRC );	// setting the key based on the url prevents duplicate items
		$this->oProp->aEnqueuingScripts[ $sSRCHash ] = $this->oUtil->uniteArrays( 
			( array ) $aCustomArgs,
			array(		
				'sSRC' => $sSRC,
				'aPostTypes' => empty( $aPostTypes ) ? $this->oProp->aPostTypes : $aPostTypes,
				'sType' => 'script',
				'handle_id' => 'script_' . $this->oProp->sClassName . '_' .  ( ++$this->oProp->iEnqueuedScriptIndex ),
			),
			self::$_aStructure_EnqueuingScriptsAndStyles
		);
		return $this->oProp->aEnqueuingScripts[ $sSRCHash ][ 'handle_id' ];
	}

	/**
	 * A helper function for the above replyToEnqueueScripts() and replyToEnqueueStyle() methods.
	 * 
	 * @since			2.1.5
	 * @internal
	 */
	protected function _enqueueSRCByConditoin( $aEnqueueItem ) {
		
		$sCurrentPostType = isset( $_GET['post_type'] ) ? $_GET['post_type'] : ( isset( $GLOBALS['typenow'] ) ? $GLOBALS['typenow'] : null );
				
		if ( in_array( $sCurrentPostType, $aEnqueueItem['aPostTypes'] ) )		
			return $this->_enqueueSRC( $aEnqueueItem );
			
	}

}
endif;