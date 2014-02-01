<?php 
/**
 * Loads the Admin Page Framework library.
 * 
 * @info
 * Library Name: Admin Page Framework
 * Library URI: http://wordpress.org/extend/plugins/admin-page-framework/
 * Author:  Michael Uno
 * Author URI: http://michaeluno.jp
 * Version: 3.0.0b10
 * Requirements: WordPress 3.3 or above, PHP 5.2.4 or above.
 * Description: Provides simpler means of building administration pages for plugin and theme developers.
 * @copyright		2013-2014 Michael Uno
 * @license			GPL v2 or later.
 * @see				http://wordpress.org/plugins/admin-page-framework/
 * @see				https://github.com/michaeluno/admin-page-framework
 * @link			http://en.michaeluno.jp/admin-page-framework
 * @since			3.0.0
 * @remark			The minifier script will refer this comment section to create the comment header. So don't remove the @info section.
 * @remark			This class will not be included in the minifiled version.
 * @package			AdminPageFramework
 * @subpackage		Utility
 * @internal
 */ if ( ! class_exists( 'AdminPageFramework_Debug' ) ) : class AdminPageFramework_Debug { static public function dumpArray( $arr, $sFilePath=null ) { echo self::getArray( $arr, $sFilePath ); } static public function getArray( $arr, $sFilePath=null, $bEscape=true ) { if ( $sFilePath ) self::logArray( $arr, $sFilePath ); return $bEscape ? "<pre class='dump-array'>" . htmlspecialchars( print_r( $arr, true ) ) . "</pre>" : print_r( $arr, true ); } static public function logArray( $arr, $sFilePath=null ) { $oCallerInfo = debug_backtrace(); $sCallerFunction = $oCallerInfo[ 1 ]['function']; $sCallerClasss = $oCallerInfo[ 1 ]['class']; file_put_contents( $sFilePath ? $sFilePath : dirname( __FILE__ ) . '/array_log.txt', date( "Y/m/d H:i:s", current_time( 'timestamp' ) ) . ' ' . "{$sCallerClasss}::{$sCallerFunction}" . PHP_EOL . print_r( $arr, true ) . PHP_EOL . PHP_EOL , FILE_APPEND ); } } endif;if ( ! class_exists( 'AdminPageFramework_HelpPane_Base' ) ) : abstract class AdminPageFramework_HelpPane_Base extends AdminPageFramework_Debug { protected $_oScreen; protected function _setHelpTab( $sID, $sTitle, $aContents, $aSideBarContents=array() ) { if ( empty( $aContents ) ) return; $this->_oScreen = isset( $this->_oScreen ) ? $this->_oScreen : get_current_screen(); $this->_oScreen->add_help_tab( array( 'id' => $sID, 'title' => $sTitle, 'content' => implode( PHP_EOL, $aContents ), ) ); if ( ! empty( $aSideBarContents ) ) $this->_oScreen->set_help_sidebar( implode( PHP_EOL, $aSideBarContents ) ); } protected function _formatHelpDescription( $sHelpDescription ) { return "<div class='contextual-help-description'>" . $sHelpDescription . "</div>"; } } endif;if ( ! class_exists( 'AdminPageFramework_HelpPane_MetaBox' ) ) : class AdminPageFramework_HelpPane_MetaBox extends AdminPageFramework_HelpPane_Base { function __construct( $oProp ) { $this->oProp = $oProp; add_action( "load-{$GLOBALS['pagenow']}", array( $this, '_replyToRegisterHelpTabTextForMetaBox' ), 20 ); } public function _addHelpText( $sHTMLContent, $sHTMLSidebarContent="" ) { $this->oProp->aHelpTabText[] = "<div class='contextual-help-description'>" . $sHTMLContent . "</div>"; $this->oProp->aHelpTabTextSide[] = "<div class='contextual-help-description'>" . $sHTMLSidebarContent . "</div>"; } public function _addHelpTextForFormFields( $sFieldTitle, $sHelpText, $sHelpTextSidebar="" ) { $this->_addHelpText( "<span class='contextual-help-tab-title'>" . $sFieldTitle . "</span> - " . PHP_EOL . $sHelpText, $sHelpTextSidebar ); } public function _replyToRegisterHelpTabTextForMetaBox() { if ( ! in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php', ) ) ) return; if ( isset( $_GET['post_type'] ) && ! in_array( $_GET['post_type'], $this->oProp->aPostTypes ) ) return; if ( ! isset( $_GET['post_type'] ) && ! in_array( 'post', $this->oProp->aPostTypes ) ) return; if ( isset( $_GET['post'], $_GET['action'] ) && ! in_array( get_post_type( $_GET['post'] ), $this->oProp->aPostTypes ) ) return; $this->_setHelpTab( $this->oProp->sMetaBoxID, $this->oProp->sTitle, $this->oProp->aHelpTabText, $this->oProp->aHelpTabTextSide ); } } endif;if ( ! class_exists( 'AdminPageFramework_HelpPane_Page' ) ) : class AdminPageFramework_HelpPane_Page extends AdminPageFramework_HelpPane_Base { protected static $_aStructure_HelpTabUserArray = array( 'page_slug' => null, 'page_tab_slug' => null, 'help_tab_title' => null, 'help_tab_id' => null, 'help_tab_content' => null, 'help_tab_sidebar_content' => null, ); function __construct( $oProp ) { $this->oProp = $oProp; add_action( 'admin_head', array( $this, '_replyToRegisterHelpTabs' ), 200 ); } public function _replyToRegisterHelpTabs() { $sCurrentPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : ''; $sCurrentPageTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : ( isset( $this->oProp->aDefaultInPageTabs[ $sCurrentPageSlug ] ) ? $this->oProp->aDefaultInPageTabs[ $sCurrentPageSlug ] : '' ); if ( empty( $sCurrentPageSlug ) ) return; if ( ! $this->oProp->isPageAdded( $sCurrentPageSlug ) ) return; foreach( $this->oProp->aHelpTabs as $aHelpTab ) { if ( $sCurrentPageSlug != $aHelpTab['sPageSlug'] ) continue; if ( isset( $aHelpTab['sPageTabSlug'] ) && ! empty( $aHelpTab['sPageTabSlug'] ) && $sCurrentPageTabSlug != $aHelpTab['sPageTabSlug'] ) continue; $this->_setHelpTab( $aHelpTab['sID'], $aHelpTab['sTitle'], $aHelpTab['aContent'], $aHelpTab['aSidebar'] ); } } public function _addHelpTab( $aHelpTab ) { $aHelpTab = ( array ) $aHelpTab + self::$_aStructure_HelpTabUserArray; if ( ! isset( $this->oProp->aHelpTabs[ $aHelpTab['help_tab_id'] ] ) ) { $this->oProp->aHelpTabs[ $aHelpTab['help_tab_id'] ] = array( 'sID' => $aHelpTab['help_tab_id'], 'sTitle' => $aHelpTab['help_tab_title'], 'aContent' => ! empty( $aHelpTab['help_tab_content'] ) ? array( $this->_formatHelpDescription( $aHelpTab['help_tab_content'] ) ) : array(), 'aSidebar' => ! empty( $aHelpTab['help_tab_sidebar_content'] ) ? array( $this->_formatHelpDescription( $aHelpTab['help_tab_sidebar_content'] ) ) : array(), 'sPageSlug' => $aHelpTab['page_slug'], 'sPageTabSlug' => $aHelpTab['page_tab_slug'], ); return; } if ( ! empty( $aHelpTab['help_tab_content'] ) ) $this->oProp->aHelpTabs[ $aHelpTab['help_tab_id'] ]['aContent'][] = $this->_formatHelpDescription( $aHelpTab['help_tab_content'] ); if ( ! empty( $aHelpTab['help_tab_sidebar_content'] ) ) $this->oProp->aHelpTabs[ $aHelpTab['help_tab_id'] ]['aSidebar'][] = $this->_formatHelpDescription( $aHelpTab['help_tab_sidebar_content'] ); } } endif;if ( ! class_exists( 'AdminPageFramework_HelpPane_TaxonomyField' ) ) : class AdminPageFramework_HelpPane_TaxonomyField extends AdminPageFramework_HelpPane_MetaBox { public function _replyToRegisterHelpTabTextForMetaBox() { $this->_setHelpTab( $this->oProp->sMetaBoxID, $this->oProp->sTitle, $this->oProp->aHelpTabText, $this->oProp->aHelpTabTextSide ); } } endif;if ( ! class_exists( 'AdminPageFramework_PageLoadInfo_Base' ) ) : abstract class AdminPageFramework_PageLoadInfo_Base { function __construct( $oProp, $oMsg ) { if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { $this->oProp = $oProp; $this->oMsg = $oMsg; $this->nInitialMemoryUsage = memory_get_usage(); add_action( 'admin_menu', array( $this, '_replyToSetPageLoadInfoInFooter' ), 999 ); } } public function _replyToSetPageLoadInfoInFooter() {} public function _replyToGetPageLoadInfo( $sFooterHTML ) { $nSeconds = timer_stop(0); $nQueryCount = get_num_queries(); $nMemoryUsage = round( $this->_convert_bytes_to_hr( memory_get_usage() ), 2 ); $nMemoryPeakUsage = round( $this->_convert_bytes_to_hr( memory_get_peak_usage() ), 2 ); $nMemoryLimit = round( $this->_convert_bytes_to_hr( $this->_let_to_num( WP_MEMORY_LIMIT ) ), 2 ); $sInitialMemoryUsage = round( $this->_convert_bytes_to_hr( $this->nInitialMemoryUsage ), 2 ); $sOutput = "<div id='admin-page-framework-page-load-stats'>" . "<ul>" . "<li>" . sprintf( $this->oMsg->__( 'queries_in_seconds' ), $nQueryCount, $nSeconds ) . "</li>" . "<li>" . sprintf( $this->oMsg->__( 'out_of_x_memory_used' ), $nMemoryUsage, $nMemoryLimit, round( ( $nMemoryUsage / $nMemoryLimit ), 2 ) * 100 . '%' ) . "</li>" . "<li>" . sprintf( $this->oMsg->__( 'peak_memory_usage' ), $nMemoryPeakUsage ) . "</li>" . "<li>" . sprintf( $this->oMsg->__( 'initial_memory_usage' ), $sInitialMemoryUsage ) . "</li>" . "</ul>" . "</div>"; return $sFooterHTML . $sOutput; } private function _let_to_num( $size ) { $l = substr( $size, -1 ); $ret = substr( $size, 0, -1 ); switch( strtoupper( $l ) ) { case 'P': $ret *= 1024; case 'T': $ret *= 1024; case 'G': $ret *= 1024; case 'M': $ret *= 1024; case 'K': $ret *= 1024; } return $ret; } private function _convert_bytes_to_hr( $bytes ) { $units = array( 0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB' ); $log = log( $bytes, 1024 ); $power = ( int ) $log; $size = pow( 1024, $log - $power ); return $size . $units[ $power ]; } } endif;if ( ! class_exists( 'AdminPageFramework_PageLoadInfo_Page' ) ) : class AdminPageFramework_PageLoadInfo_Page extends AdminPageFramework_PageLoadInfo_Base { private static $_oInstance; private static $aClassNames = array(); public static function instantiate( $oProp, $oMsg ) { if ( in_array( $oProp->sClassName, self::$aClassNames ) ) return self::$_oInstance; self::$aClassNames[] = $oProp->sClassName; self::$_oInstance = new AdminPageFramework_PageLoadInfo_Page( $oProp, $oMsg ); return self::$_oInstance; } public function _replyToSetPageLoadInfoInFooter() { $sCurrentPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : ''; if ( $this->oProp->isPageAdded( $sCurrentPageSlug ) ) add_filter( 'update_footer', array( $this, '_replyToGetPageLoadInfo' ), 999 ); } } endif;if ( ! class_exists( 'AdminPageFramework_PageLoadInfo_PostType' ) ) : class AdminPageFramework_PageLoadInfo_PostType extends AdminPageFramework_PageLoadInfo_Base { private static $_oInstance; private static $aClassNames = array(); public static function instantiate( $oProp, $oMsg ) { if ( in_array( $oProp->sClassName, self::$aClassNames ) ) return self::$_oInstance; self::$aClassNames[] = $oProp->sClassName; self::$_oInstance = new AdminPageFramework_PageLoadInfo_PostType( $oProp, $oMsg ); return self::$_oInstance; } public function _replyToSetPageLoadInfoInFooter() { if ( isset( $_GET['page'] ) && $_GET['page'] ) return; if ( isset( $_GET['post_type'], $this->oProp->sPostType ) && $_GET['post_type'] == $this->oProp->sPostType || $this->oProp->isPostDefinitionPage( $this->oProp->sPostType ) ) add_filter( 'update_footer', array( $this, '_replyToGetPageLoadInfo' ), 999 ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldTypeRegistration' ) ) : class AdminPageFramework_FieldTypeRegistration { protected static $aDefaultFieldTypeSlugs = array( 'default', 'text', 'number', 'textarea', 'radio', 'checkbox', 'select', 'hidden', 'file', 'submit', 'import', 'export', 'image', 'media', 'color', 'taxonomy', 'posttype', 'size', ); function __construct( &$aFieldTypeDefinitions, $sExtendedClassName, $oMsg ) { foreach( self::$aDefaultFieldTypeSlugs as $sFieldTypeSlug ) { $sInstantiatingClassName = "AdminPageFramework_FieldType_{$sFieldTypeSlug}"; if ( class_exists( $sInstantiatingClassName ) ) { $oFieldType = new $sInstantiatingClassName( $sExtendedClassName, null, $oMsg, false ); foreach( $oFieldType->aFieldTypeSlugs as $sSlug ) $aFieldTypeDefinitions[ $sSlug ] = $oFieldType->getDefinitionArray(); } } } static public function _setFieldHeadTagElements( array $aField, $oProp, $oHeadTag ) { $sFieldType = $aField['type']; $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ] = isset( $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ] ) && is_array( $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ] ) ? $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ] : array(); if ( isset( $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ][ $sFieldType ] ) && $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ][ $sFieldType ] ) return; $GLOBALS['aAdminPageFramework']['aFieldFlags'][ $oProp->_sPropertyType ][ $sFieldType ] = true; if ( ! isset( $oProp->aFieldTypeDefinitions[ $sFieldType ] ) ) return; if ( is_callable( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfFieldSetTypeSetter'] ) ) call_user_func_array( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfFieldSetTypeSetter'], array( $oProp->_sPropertyType ) ); if ( is_callable( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfFieldLoader'] ) ) call_user_func_array( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfFieldLoader'], array() ); if ( is_callable( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetScripts'] ) ) $oProp->sScript .= call_user_func_array( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetScripts'], array() ); if ( is_callable( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetStyles'] ) ) $oProp->sStyle .= call_user_func_array( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetStyles'], array() ); if ( is_callable( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetIEStyles'] ) ) $oProp->sStyleIE .= call_user_func_array( $oProp->aFieldTypeDefinitions[ $sFieldType ]['hfGetIEStyles'], array() ); foreach( $oProp->aFieldTypeDefinitions[ $sFieldType ]['aEnqueueStyles'] as $asSource ) { if ( is_string( $asSource ) ) $oHeadTag->_forceToEnqueueStyle( $asSource ); else if ( is_array( $asSource ) && isset( $asSource[ 'src' ] ) ) $oHeadTag->_forceToEnqueueStyle( $asSource[ 'src' ], $asSource ); } foreach( $oProp->aFieldTypeDefinitions[ $sFieldType ]['aEnqueueScripts'] as $asSource ) { if ( is_string( $asSource ) ) $oHeadTag->_forceToEnqueueScript( $asSource ); else if ( is_array( $asSource ) && isset( $asSource[ 'src' ] ) ) $oHeadTag->_forceToEnqueueScript( $asSource[ 'src' ], $asSource ); } } } endif;if ( ! class_exists( 'AdminPageFramework_WalkerTaxonomyChecklist' ) ) : class AdminPageFramework_WalkerTaxonomyChecklist extends Walker_Category { function start_el( &$sOutput, $oCategory, $iDepth=0, $aArgs=array(), $iCurrentObjectID=0 ) { $aArgs = $aArgs + array( 'name' => null, 'disabled' => null, 'selected' => array(), 'input_id' => null, 'attributes' => array(), 'taxonomy' => null, ); $iID = $oCategory->term_id; $sTaxonomy = empty( $aArgs['taxonomy'] ) ? 'category' : $aArgs['taxonomy']; $sID = "{$aArgs['input_id']}_{$sTaxonomy}_{$iID}"; $aInputAttributes = isset( $aInputAttributes[ $iID ] ) ? $aInputAttributes[ $iID ] + $aArgs['attributes'] : $aArgs['attributes']; $aInputAttributes = array( 'id' => $sID, 'value' => 1, 'type' => 'checkbox', 'name' => "{$aArgs['name']}[{$iID}]", 'checked' => in_array( $iID, ( array ) $aArgs['selected'] ) ? 'Checked' : '', ) + $aInputAttributes; $sOutput .= "\n" . "<li id='list-{$sID}' class='category-list'>" . "<label for='{$sID}' class='taxonomy-checklist-label'>" . "<input value='0' type='hidden' name='{$aArgs['name']}[{$iID}]' />" . "<input " . AdminPageFramework_WPUtility::generateAttributes( $aInputAttributes ) . " />" . esc_html( apply_filters( 'the_category', $oCategory->name ) ) . "</label>"; } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_Base' ) ) : abstract class AdminPageFramework_HeadTag_Base { protected static $_aStructure_EnqueuingScriptsAndStyles = array( 'sSRC' => null, 'aPostTypes' => array(), 'sPageSlug' => null, 'sTabSlug' => null, 'sType' => null, 'handle_id' => null, 'dependencies' => array(), 'version' => false, 'translation' => array(), 'in_footer' => false, 'media' => 'all', ); function __construct( $oProp ) { $this->oProp = $oProp; $this->oUtil = new AdminPageFramework_WPUtility; add_action( 'admin_head', array( $this, '_replyToAddStyle' ), 999 ); add_action( 'admin_head', array( $this, '_replyToAddScript' ), 999 ); add_action( 'admin_enqueue_scripts', array( $this, '_replyToEnqueueScripts' ) ); add_action( 'admin_enqueue_scripts', array( $this, '_replyToEnqueueStyles' ) ); } public function _replyToAddStyle() {} public function _replyToAddScript() {} protected function _enqueueSRCByConditoin( $aEnqueueItem ) {} public function _forceToEnqueueStyle( $sSRC, $aCustomArgs=array() ) {} public function _forceToEnqueueScript( $sSRC, $aCustomArgs=array() ) {} protected function _enqueueSRC( $aEnqueueItem ) { if ( $aEnqueueItem['sType'] == 'style' ) { wp_enqueue_style( $aEnqueueItem['handle_id'], $aEnqueueItem['sSRC'], $aEnqueueItem['dependencies'], $aEnqueueItem['version'], $aEnqueueItem['media'] ); return; } wp_enqueue_script( $aEnqueueItem['handle_id'], $aEnqueueItem['sSRC'], $aEnqueueItem['dependencies'], $aEnqueueItem['version'], $aEnqueueItem['in_footer'] ); if ( $aEnqueueItem['translation'] ) wp_localize_script( $aEnqueueItem['handle_id'], $aEnqueueItem['handle_id'], $aEnqueueItem['translation'] ); } public function _replyToEnqueueStyles() { foreach( $this->oProp->aEnqueuingStyles as $sKey => $aEnqueuingStyle ) $this->_enqueueSRCByConditoin( $aEnqueuingStyle ); } public function _replyToEnqueueScripts() { foreach( $this->oProp->aEnqueuingScripts as $sKey => $aEnqueuingScript ) $this->_enqueueSRCByConditoin( $aEnqueuingScript ); } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_MetaBox' ) ) : class AdminPageFramework_HeadTag_MetaBox extends AdminPageFramework_HeadTag_Base { private $_sPostTypeSlugOfCurrentPost = null; private function _isMetaBoxPage() { if ( ! in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php', ) ) ) return false; if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $this->oProp->aPostTypes ) ) return true; $this->_sPostTypeSlugOfCurrentPost = isset( $this->_sPostTypeSlugOfCurrentPost ) ? $this->_sPostTypeSlugOfCurrentPost : ( isset( $_GET['post'], $_GET['action'] ) ? get_post_type( $_GET['post'] ) : '' ); if ( in_array( $this->_sPostTypeSlugOfCurrentPost, $this->oProp->aPostTypes ) ) return true; return false; } public function _replyToAddStyle() { if ( ! $this->_isMetaBoxPage() ) return; $this->_printCommonStyles( 'admin-page-framework-style-meta-box-common', get_class() ); $this->_printClassSpecificStyles( 'admin-page-framework-style-meta-box' ); $this->oProp->_bAddedStyle = true; } public function _replyToAddScript() { if ( ! $this->_isMetaBoxPage() ) return; $this->_printCommonScripts( 'admin-page-framework-style-meta-box-common', get_class() ); $this->_printClassSpecificScripts( 'admin-page-framework-script-meta-box' ); $this->oProp->_bAddedScript = true; } protected function _printClassSpecificStyles( $sIDPrefix ) { $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_{$this->oProp->sClassName}", $this->oProp->sStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='{$sIDPrefix}-{$this->oProp->sClassName}'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_{$this->oProp->sClassName}", $this->oProp->sStyleIE ); $sStyleIE = $this->oUtil->minifyCSS( $sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='{$sIDPrefix}-ie-{$this->oProp->sClassName}'>{$sStyleIE}</style><![endif]-->"; } protected function _printCommonStyles( $sIDPrefix, $sClassName ) { if ( isset( $GLOBALS[ "{$sClassName}_StyleLoaded" ] ) && $GLOBALS[ "{$sClassName}_StyleLoaded" ] ) return; $GLOBALS[ "{$sClassName}_StyleLoaded" ] = true; $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='{$sIDPrefix}'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultStyleIE ); $sStyleIE = $this->oUtil->minifyCSS( $sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='{$sIDPrefix}-ie'>{$sStyleIE}</style><![endif]-->"; } protected function _printClassSpecificScripts( $sIDPrefix ) { $sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getCallerObject(), "script_{$this->oProp->sClassName}", $this->oProp->sScript ); if ( $sScript ) echo "<script type='text/javascript' id='{$sIDPrefix}-{$this->oProp->sClassName}'>{$sScript}</script>"; } protected function _printCommonScripts( $sIDPrefix, $sClassName ) { if ( isset( $GLOBALS[ "{$sClassName}_ScriptLoaded" ] ) && $GLOBALS[ "{$sClassName}_ScriptLoaded" ] ) return; $GLOBALS[ "{$sClassName}_ScriptLoaded" ] = true; $sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getCallerObject(), "script_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultScript ); if ( $sScript ) echo "<script type='text/javascript' id='{$sIDPrefix}'>{$sScript}</script>"; } public function _enqueueStyles( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueStyle( $sSRC, $aPostTypes, $aCustomArgs ); return $aHandleIDs; } public function _enqueueStyle( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingStyles[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sSRC' => $sSRC, 'aPostTypes' => empty( $aPostTypes ) ? $this->oProp->aPostTypes : $aPostTypes, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedStyleIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingStyles[ $sSRCHash ][ 'handle_id' ]; } public function _enqueueScripts( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueScript( $sSRC, $aPostTypes, $aCustomArgs ); return $aHandleIDs; } public function _enqueueScript( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingScripts[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sSRC' => $sSRC, 'aPostTypes' => empty( $aPostTypes ) ? $this->oProp->aPostTypes : $aPostTypes, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedScriptIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingScripts[ $sSRCHash ][ 'handle_id' ]; } public function _forceToEnqueueStyle( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueStyle( $sSRC, array(), $aCustomArgs ); } public function _forceToEnqueueScript( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueScript( $sSRC, array(), $aCustomArgs ); } protected function _enqueueSRCByConditoin( $aEnqueueItem ) { $sCurrentPostType = isset( $_GET['post_type'] ) ? $_GET['post_type'] : ( isset( $GLOBALS['typenow'] ) ? $GLOBALS['typenow'] : null ); if ( in_array( $sCurrentPostType, $aEnqueueItem['aPostTypes'] ) ) return $this->_enqueueSRC( $aEnqueueItem ); } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_Page' ) ) : class AdminPageFramework_HeadTag_Page extends AdminPageFramework_HeadTag_Base { public function _replyToAddStyle() { $sPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : null; $sTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab( $sPageSlug ); if ( ! $this->oProp->isPageAdded( $sPageSlug ) ) return; $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, $this->oUtil->getFilterArrayByPrefix( 'style_common_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), AdminPageFramework_Property_Page::$_sDefaultStyle ) . $this->oUtil->addAndApplyFilters( $oCaller, $this->oUtil->getFilterArrayByPrefix( 'style_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), $this->oProp->sStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='admin-page-framework-style_{$this->oProp->sClassName}'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, $this->oUtil->getFilterArrayByPrefix( 'style_common_ie_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), AdminPageFramework_Property_Page::$_sDefaultStyleIE ) . $this->oUtil->addAndApplyFilters( $oCaller, $this->oUtil->getFilterArrayByPrefix( 'style_ie_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), $this->oProp->sStyleIE ); $sStyleIE = $this->oUtil->minifyCSS( $sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='admin-page-framework-style-for-IE_{$this->oProp->sClassName}'>{$sStyleIE}</style><![endif]-->"; $this->oProp->_bAddedStyle = true; } public function _replyToAddScript() { $sPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : null; $sTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab( $sPageSlug ); if ( ! $this->oProp->isPageAdded( $sPageSlug ) ) return; $oCaller = $this->oProp->_getCallerObject(); echo "<script type='text/javascript' id='admin-page-framework-script_{$this->oProp->sClassName}'>" . ( $sScript = $this->oUtil->addAndApplyFilters( $oCaller, $this->oUtil->getFilterArrayByPrefix( 'script_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), $this->oProp->sScript ) ) . "</script>"; $this->oProp->_bAddedScript = true; } public function _enqueueStyles( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueStyle( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); return $aHandleIDs; } public function _enqueueStyle( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingStyles[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sSRC' => $sSRC, 'sPageSlug' => $sPageSlug, 'sTabSlug' => $sTabSlug, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedStyleIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingStyles[ $sSRCHash ][ 'handle_id' ]; } public function _enqueueScripts( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueScript( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); return $aHandleIDs; } public function _enqueueScript( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingScripts[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sPageSlug' => $sPageSlug, 'sTabSlug' => $sTabSlug, 'sSRC' => $sSRC, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedScriptIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingScripts[ $sSRCHash ][ 'handle_id' ]; } public function _forceToEnqueueStyle( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueStyle( $sSRC, '', '', $aCustomArgs ); } public function _forceToEnqueueScript( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueScript( $sSRC, '', '', $aCustomArgs ); } protected function _enqueueSRCByConditoin( $aEnqueueItem ) { $sCurrentPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : ''; $sCurrentTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab( $sCurrentPageSlug ); $sPageSlug = $aEnqueueItem['sPageSlug']; $sTabSlug = $aEnqueueItem['sTabSlug']; if ( ! $sPageSlug && $this->oProp->isPageAdded( $sCurrentPageSlug ) ) return $this->_enqueueSRC( $aEnqueueItem ); if ( ( $sPageSlug && $sCurrentPageSlug == $sPageSlug ) && ( $sTabSlug && $sCurrentTabSlug == $sTabSlug ) ) return $this->_enqueueSRC( $aEnqueueItem ); if ( ( $sPageSlug && ! $sTabSlug ) && ( $sCurrentPageSlug == $sPageSlug ) ) return $this->_enqueueSRC( $aEnqueueItem ); } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_MetaBox_Page' ) ) : class AdminPageFramework_HeadTag_MetaBox_Page extends AdminPageFramework_HeadTag_Page { private function _isMetaBoxPage() { if ( ! isset( $_GET['page'] ) ) return false; if ( in_array( $_GET['page'], $this->oProp->aPageSlugs ) ) return true; return false; } public function _replyToAddStyle() { if ( ! $this->_isMetaBoxPage() ) return; $this->_printCommonStyles( 'admin-page-framework-style-meta-box-common', get_class() ); $this->_printClassSpecificStyles( 'admin-page-framework-style-meta-box' ); $this->oProp->_bAddedStyle = true; } public function _replyToAddScript() { if ( ! $this->_isMetaBoxPage() ) return; $this->_printCommonScripts( 'admin-page-framework-style-meta-box-common', get_class() ); $this->_printClassSpecificScripts( 'admin-page-framework-script-meta-box' ); $this->oProp->_bAddedScript = true; } protected function _printClassSpecificStyles( $sIDPrefix ) { $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_{$this->oProp->sClassName}", $this->oProp->sStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='{$sIDPrefix}-{$this->oProp->sClassName}'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_{$this->oProp->sClassName}", $this->oProp->sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='{$sIDPrefix}-ie-{$this->oProp->sClassName}'>{$sStyleIE}</style><![endif]-->"; } protected function _printCommonStyles( $sIDPrefix, $sClassName ) { if ( isset( $GLOBALS[ "{$sClassName}_StyleLoaded" ] ) && $GLOBALS[ "{$sClassName}_StyleLoaded" ] ) return; $GLOBALS[ "{$sClassName}_StyleLoaded" ] = true; $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='{$sIDPrefix}'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultStyleIE ); $sStyleIE = $this->oUtil->minifyCSS( $sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='{$sIDPrefix}-ie'>{$sStyleIE}</style><![endif]-->"; } protected function _printClassSpecificScripts( $sIDPrefix ) { $sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getCallerObject(), "script_{$this->oProp->sClassName}", $this->oProp->sScript ); if ( $sScript ) echo "<script type='text/javascript' id='{$sIDPrefix}-{$this->oProp->sClassName}'>{$sScript}</script>"; } protected function _printCommonScripts( $sIDPrefix, $sClassName ) { if ( isset( $GLOBALS[ "{$sClassName}_ScriptLoaded" ] ) && $GLOBALS[ "{$sClassName}_ScriptLoaded" ] ) return; $GLOBALS[ "{$sClassName}_ScriptLoaded" ] = true; $sScript = $this->oUtil->addAndApplyFilters( $this->oProp->_getCallerObject(), "script_common_{$this->oProp->sClassName}", AdminPageFramework_Property_Base::$_sDefaultScript ); if ( $sScript ) echo "<script type='text/javascript' id='{$sIDPrefix}'>{$sScript}</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_PostType' ) ) : class AdminPageFramework_HeadTag_PostType extends AdminPageFramework_HeadTag_MetaBox { public function _replyToAddStyle() { if ( ! ( in_array( $GLOBALS['pagenow'], array( 'edit.php', 'edit-tags.php' ) ) && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->oProp->sPostType ) || $this->oProp->isPostDefinitionPage( $this->oProp->sPostType ) ) ) return; if ( isset( $_GET['page'] ) && $_GET['page'] ) return; $sRootClassName = get_class(); if ( isset( $GLOBALS[ "{$sRootClassName}_StyleLoaded" ] ) && $GLOBALS[ "{$sRootClassName}_StyleLoaded" ] ) return; $GLOBALS[ "{$sRootClassName}_StyleLoaded" ] = true; $oCaller = $this->oProp->_getCallerObject(); $sStyle = $this->oUtil->addAndApplyFilters( $oCaller, "style_common_{$this->oProp->sClassName}", AdminPageFramework_Property_PostType::$_sDefaultStyle ) . $this->oUtil->addAndApplyFilters( $oCaller, "style_{$this->oProp->sClassName}", $this->oProp->sStyle ); $sStyle = $this->oUtil->minifyCSS( $sStyle ); if ( $sStyle ) echo "<style type='text/css' id='admin-page-framework-style-post-type'>{$sStyle}</style>"; $sStyleIE = $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_{$this->oProp->sClassName}", AdminPageFramework_Property_PostType::$_sDefaultStyleIE ) . $this->oUtil->addAndApplyFilters( $oCaller, "style_ie_{$this->oProp->sClassName}", $this->oProp->sStyleIE ); $sStyleIE = $this->oUtil->minifyCSS( $sStyleIE ); if ( $sStyleIE ) echo "<!--[if IE]><style type='text/css' id='admin-page-framework-style-post-type'>{$sStyleIE}</style><![endif]-->"; } public function _replyToAddScript() { if ( ! ( in_array( $GLOBALS['pagenow'], array( 'edit.php', 'edit-tags.php' ) ) && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->oProp->sPostType ) || $this->oProp->isPostDefinitionPage( $this->oProp->sPostType ) ) ) return; if ( isset( $_GET['page'] ) && $_GET['page'] ) return; $sRootClassName = get_class(); if ( isset( $GLOBALS[ "{$sRootClassName}_ScriptLoaded" ] ) && $GLOBALS[ "{$sRootClassName}_ScriptLoaded" ] ) return; $GLOBALS[ "{$sRootClassName}_ScriptLoaded" ] = true; $oCaller = $this->oProp->_getCallerObject(); $sScript = $this->oUtil->addAndApplyFilters( $oCaller, "script_{$this->oProp->sClassName}", $this->oProp->sScript ); if ( $sScript ) echo "<script type='text/javascript' id='admin-page-framework-script-post-type'>{$sScript}</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_HeadTag_TaxonomyField' ) ) : class AdminPageFramework_HeadTag_TaxonomyField extends AdminPageFramework_HeadTag_MetaBox { public function _replyToAddStyle() { if ( $GLOBALS['pagenow'] != 'edit-tags.php' ) return; $this->_printCommonStyles( 'admin-page-framework-style-taxonomy-field-common', get_class() ); $this->_printClassSpecificStyles( 'admin-page-framework-style-taxonomy-field' ); $this->oProp->_bAddedStyle = true; } public function _replyToAddScript() { if ( $GLOBALS['pagenow'] != 'edit-tags.php' ) return; $this->_printCommonScripts( 'admin-page-framework-style-taxonomy-field-common', get_class() ); $this->_printClassSpecificScripts( 'admin-page-framework-script-taxonomy-field' ); $this->oProp->_bAddedScript = true; } public function _enqueueStyles( $aSRCs, $aCustomArgs=array(), $_deprecated=null ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueStyle( $sSRC, $aCustomArgs ); return $aHandleIDs; } public function _enqueueStyle( $sSRC, $aCustomArgs=array(), $_deprecated=null ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingStyles[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sSRC' => $sSRC, 'sType' => 'style', 'handle_id' => 'style_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedStyleIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingStyles[ $sSRCHash ][ 'handle_id' ]; } public function _enqueueScripts( $aSRCs, $aCustomArgs=array(), $_deprecated=null ) { $aHandleIDs = array(); foreach( ( array ) $aSRCs as $sSRC ) $aHandleIDs[] = $this->_enqueueScript( $sSRC, $aCustomArgs ); return $aHandleIDs; } public function _enqueueScript( $sSRC, $aCustomArgs=array(), $_deprecated=null ) { $sSRC = trim( $sSRC ); if ( empty( $sSRC ) ) return ''; if ( isset( $this->oProp->aEnqueuingScripts[ md5( $sSRC ) ] ) ) return ''; $sSRC = $this->oUtil->resolveSRC( $sSRC ); $sSRCHash = md5( $sSRC ); $this->oProp->aEnqueuingScripts[ $sSRCHash ] = $this->oUtil->uniteArrays( ( array ) $aCustomArgs, array( 'sSRC' => $sSRC, 'sType' => 'script', 'handle_id' => 'script_' . $this->oProp->sClassName . '_' . ( ++$this->oProp->iEnqueuedScriptIndex ), ), self::$_aStructure_EnqueuingScriptsAndStyles ); return $this->oProp->aEnqueuingScripts[ $sSRCHash ][ 'handle_id' ]; } public function _forceToEnqueueStyle( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueStyle( $sSRC, $aCustomArgs ); } public function _forceToEnqueueScript( $sSRC, $aCustomArgs=array() ) { return $this->_enqueueScript( $sSRC, $aCustomArgs ); } protected function _enqueueSRCByConditoin( $aEnqueueItem ) { return $this->_enqueueSRC( $aEnqueueItem ); } } endif;if ( ! class_exists( 'AdminPageFramework_MetaBox' ) ) : abstract class AdminPageFramework_MetaBox_Base { protected $oDebug; protected $oUtil; protected $oMsg; protected $oHeadTag; function __construct( $sMetaBoxID, $sTitle, $asPostTypeOrScreenID=array( 'post' ), $sContext='normal', $sPriority='default', $sCapability='edit_posts', $sTextDomain='admin-page-framework' ) { if ( empty( $asPostTypeOrScreenID ) ) return; $this->oUtil = new AdminPageFramework_WPUtility; $this->oMsg = AdminPageFramework_Message::instantiate( $sTextDomain ); $this->oDebug = new AdminPageFramework_Debug; $this->oProp = isset( $this->oProp ) ? $this->oProp : new AdminPageFramework_Property_MetaBox( $this, get_class( $this ), $sCapability ); $this->oProp->sMetaBoxID = $this->oUtil->sanitizeSlug( $sMetaBoxID ); $this->oProp->sTitle = $sTitle; $this->oProp->sContext = $sContext; $this->oProp->sPriority = $sPriority; if ( $this->oProp->bIsAdmin ) { add_action( 'wp_loaded', array( $this, '_replyToLoadDefaultFieldTypeDefinitions' ), 10 ); add_action( 'wp_loaded', array( $this, 'setUp' ), 11 ); add_action( 'add_meta_boxes', array( $this, '_replyToAddMetaBox' ) ); add_action( 'save_post', array( $this, '_replyToSaveMetaBoxFields' ) ); } } public function setUp() {} public function addHelpText( $sHTMLContent, $sHTMLSidebarContent="" ) { $this->oHelpPane->_addHelpText( $sHTMLContent, $sHTMLSidebarContent ); } public function enqueueStyles( $aSRCs, $_vArg2=null, $_vArg3=null ) {} public function enqueueStyle( $sSRC, $_vArg2=null, $_vArg3=null ) {} public function enqueueScripts( $aSRCs, $_vArg2=null, $_vArg3=null ) {} public function enqueueScript( $sSRC, $_vArg2=null, $_vArg3=null ) {} public function addSettingField( array $aField ) {} public function _replyToAddMetaBox() {} public function _replyToLoadDefaultFieldTypeDefinitions() { new AdminPageFramework_FieldTypeRegistration( $this->oProp->aFieldTypeDefinitions, $this->oProp->sClassName, $this->oMsg ); $this->oProp->aFieldTypeDefinitions = $this->oUtil->addAndApplyFilter( $this, 'field_types_' . $this->oProp->sClassName, $this->oProp->aFieldTypeDefinitions ); } public function addSettingFields( $aField1, $aField2=null, $_and_more=null ) { foreach( func_get_args() as $aField ) $this->addSettingField( $aField ); } public function _replyToPrintMetaBoxContents( $oPost, $vArgs ) { $sOut = wp_nonce_field( $this->oProp->sMetaBoxID, $this->oProp->sMetaBoxID, true, false ); $sOut .= '<table class="form-table">'; $iPostID = isset( $oPost->ID ) ? $oPost->ID : ( isset( $_GET['page'] ) ? $_GET['page'] : null ); $this->setOptionArray( $iPostID, $vArgs['args'] ); foreach ( ( array ) $vArgs['args'] as $aField ) { $aField = $aField + array( '_field_type' => 'post_meta_box' ) + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $aField['capability'] = isset( $aField['capability'] ) ? $aField['capability'] : $this->oProp->sCapability; if ( ! current_user_can( $aField['capability'] ) ) continue; $sOut .= "<tr>"; if ( $aField['show_title_column'] ) $sOut .= "<th>" ."<label for='{$aField['field_id']}'>" . "<a id='{$aField['field_id']}'></a>" . "<span title='" . strip_tags( isset( $aField['tip'] ) ? $aField['tip'] : $aField['description'] ) . "'>" . $aField['title'] . "</span>" . "</label>" . "</th>"; $sOut .= "<td>"; $sOut .= $this->getFieldOutput( $aField ); $sOut .= "</td>"; $sOut .= "</tr>"; } $sOut .= '</table>'; $sOut = $this->oUtil->addAndApplyFilters( $this, 'content_' . $this->oProp->sClassName, $sOut ); $this->oUtil->addAndDoActions( $this, 'do_' . $this->oProp->sClassName ); echo $sOut; } protected function setOptionArray( $isPostIDOrPageSlug, $aFields ) { if ( ! is_array( $aFields ) ) return; if ( is_numeric( $isPostIDOrPageSlug ) ) : $iPostID = $isPostIDOrPageSlug; foreach( $aFields as $iIndex => $aField ) { $aField = $aField + array( '_field_type' => 'post_meta_box' ) + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $this->oProp->aOptions[ $iIndex ] = get_post_meta( $iPostID, $aField['field_id'], true ); } endif; } protected function getFieldOutput( $aField ) { $sFieldType = isset( $this->oProp->aFieldTypeDefinitions[ $aField['type'] ]['hfRenderField'] ) && is_callable( $this->oProp->aFieldTypeDefinitions[ $aField['type'] ]['hfRenderField'] ) ? $aField['type'] : 'default'; $oField = new AdminPageFramework_InputField( $aField, $this->oProp->aOptions, array(), $this->oProp->aFieldTypeDefinitions, $this->oMsg ); $oField->isMetaBox( true ); $sFieldOutput = $oField->_getInputFieldOutput(); unset( $oField ); return $this->oUtil->addAndApplyFilters( $this, array( 'field_' . $this->oProp->sClassName . '_' . $aField['field_id'] ), $sFieldOutput, $aField ); } public function _replyToSaveMetaBoxFields( $iPostID ) { if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; if ( ! isset( $_POST[ $this->oProp->sMetaBoxID ] ) || ! wp_verify_nonce( $_POST[ $this->oProp->sMetaBoxID ], $this->oProp->sMetaBoxID ) ) return; if ( in_array( $_POST['post_type'], $this->oProp->aPostTypes ) && ( ( ! current_user_can( $this->oProp->sCapability, $iPostID ) ) || ( ! current_user_can( $this->oProp->sCapability, $iPostID ) ) ) ) return; $aInput = array(); foreach( $this->oProp->aFields as $aField ) $aInput[ $aField['field_id'] ] = isset( $_POST[ $aField['field_id'] ] ) ? $_POST[ $aField['field_id'] ] : null; $aOriginal = array(); foreach ( $aInput as $sFieldID => $v ) $aOriginal[ $sFieldID ] = get_post_meta( $iPostID, $sFieldID, true ); $aInput = $this->oUtil->addAndApplyFilters( $this, "validation_{$this->oProp->sClassName}", $aInput, $aOriginal ); foreach ( $aInput as $sFieldID => $vValue ) { $sOldValue = isset( $aOriginal[ $sFieldID ] ) ? $aOriginal[ $sFieldID ] : null; if ( ! is_null( $vValue ) && $vValue != $sOldValue ) { update_post_meta( $iPostID, $sFieldID, $vValue ); continue; } } } function __call( $sMethodName, $aArgs=null ) { if ( $sMethodName == 'start_' . $this->oProp->sClassName ) return; if ( substr( $sMethodName, 0, strlen( 'field_' . $this->oProp->sClassName . '_' ) ) == 'field_' . $this->oProp->sClassName . '_' ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "field_types_{$this->oProp->sClassName}" ) ) == "field_types_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "script_common_{$this->oProp->sClassName}" ) ) == "script_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "script_{$this->oProp->sClassName}" ) ) == "script_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "style_ie_common_{$this->oProp->sClassName}" ) ) == "style_ie_common_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "style_common_{$this->oProp->sClassName}" ) ) == "style_common_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "style_ie_{$this->oProp->sClassName}" ) ) == "style_ie_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "style_{$this->oProp->sClassName}" ) ) == "style_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "validation_{$this->oProp->sClassName}" ) ) == "validation_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "content_{$this->oProp->sClassName}" ) ) == "content_{$this->oProp->sClassName}" ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( "do_{$this->oProp->sClassName}" ) ) == "do_{$this->oProp->sClassName}" ) return; } } endif;if ( ! class_exists( 'AdminPageFramework_MetaBox' ) ) : abstract class AdminPageFramework_MetaBox extends AdminPageFramework_MetaBox_Base { function __construct( $sMetaBoxID, $sTitle, $asPostTypeOrScreenID=array( 'post' ), $sContext='normal', $sPriority='default', $sCapability='edit_posts', $sTextDomain='admin-page-framework' ) { $this->oProp = new AdminPageFramework_Property_MetaBox( $this, get_class( $this ), $sCapability ); parent::__construct( $sMetaBoxID, $sTitle, $asPostTypeOrScreenID, $sContext, $sPriority, $sCapability, $sTextDomain ); $this->oHeadTag = new AdminPageFramework_HeadTag_MetaBox( $this->oProp ); $this->oHelpPane = new AdminPageFramework_HelpPane_MetaBox( $this->oProp ); $this->oProp->aPostTypes = is_string( $asPostTypeOrScreenID ) ? array( $asPostTypeOrScreenID ) : $asPostTypeOrScreenID; $this->oUtil->addAndDoAction( $this, "start_{$this->oProp->sClassName}" ); } public function setUp() {} public function enqueueStyles( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyles( $aSRCs, $aPostTypes, $aCustomArgs ); } public function enqueueStyle( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyle( $sSRC, $aPostTypes, $aCustomArgs ); } public function enqueueScripts( $aSRCs, $aPostTypes=array(), $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScripts( $aSRCs, $aPostTypes, $aCustomArgs ); } public function enqueueScript( $sSRC, $aPostTypes=array(), $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScript( $sSRC, $aPostTypes, $aCustomArgs ); } public function addSettingField( array $aField ) { $aField = array( '_field_type' => 'post_meta_box' ) + $aField + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $aField['field_id'] = $this->oUtil->sanitizeSlug( $aField['field_id'] ); if ( ! isset( $aField['field_id'], $aField['type'] ) ) return; if ( ! $aField['if'] ) return; if ( $this->oProp->isPostDefinitionPage( $this->oProp->aPostTypes ) ) AdminPageFramework_FieldTypeRegistration::_setFieldHeadTagElements( $aField, $this->oProp, $this->oHeadTag ); if ( $this->oProp->isPostDefinitionPage( $this->oProp->aPostTypes ) && $aField['help'] ) $this->oHelpPane->_addHelpTextForFormFields( $aField['title'], $aField['help'], $aField['help_aside'] ); $this->oProp->aFields[ $aField['field_id'] ] = $aField; } public function _replyToAddMetaBox() { foreach( $this->oProp->aPostTypes as $sPostType ) add_meta_box( $this->oProp->sMetaBoxID, $this->oProp->sTitle, array( $this, '_replyToPrintMetaBoxContents' ), $sPostType, $this->oProp->sContext, $this->oProp->sPriority, $this->oProp->aFields ); } } endif;if ( ! class_exists( 'AdminPageFramework_MetaBox_Page' ) ) : abstract class AdminPageFramework_MetaBox_Page extends AdminPageFramework_MetaBox_Base { function __construct( $sMetaBoxID, $sTitle, $asPageSlugs=array(), $sContext='normal', $sPriority='default', $sCapability='manage_options', $sTextDomain='admin-page-framework' ) { if ( empty( $asPageSlugs ) ) return; $this->oProp = new AdminPageFramework_Property_MetaBox_Page( $this, get_class( $this ), $sCapability ); parent::__construct( $sMetaBoxID, $sTitle, $asPageSlugs, $sContext, $sPriority, $sCapability, $sTextDomain ); $this->oHeadTag = new AdminPageFramework_HeadTag_MetaBox_Page( $this->oProp ); $this->oHelpPane = new AdminPageFramework_HelpPane_MetaBox( $this->oProp ); $this->oProp->aPageSlugs = is_string( $asPageSlugs ) ? array( $asPageSlugs ) : $asPageSlugs; foreach( $this->oProp->aPageSlugs as $sPageSlug ) add_filter( "validation_{$sPageSlug}", array( $this, '_replyToValidateOptions' ), 10, 2 ); $this->oUtil->addAndDoAction( $this, "start_{$this->oProp->sClassName}" ); } public function enqueueStyles( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyles( $aSRCs, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueStyle( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyle( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueScripts( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScripts( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueScript( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScript( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function addSettingField( array $aField ) { $aField = array( '_field_type' => 'page_meta_box' ) + $aField + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $aField['field_id'] = $this->oUtil->sanitizeSlug( $aField['field_id'] ); if ( ! isset( $aField['field_id'], $aField['type'] ) ) return; if ( ! $aField['if'] ) return; if ( $this->_isMetaBoxPage( isset( $_GET['page'] ) ? $_GET['page'] : null ) ) AdminPageFramework_FieldTypeRegistration::_setFieldHeadTagElements( $aField, $this->oProp, $this->oHeadTag ); if ( $this->_isMetaBoxPage( isset( $_GET['page'] ) ? $_GET['page'] : null ) && $aField['help'] ) $this->oHelpPane->_addHelpTextForFormFields( $aField['title'], $aField['help'], $aField['help_aside'] ); $this->oProp->aFields[ $aField['field_id'] ] = $aField; } private function _isMetaBoxPage( $sPageSlug ) { if ( ! isset( $sPageSlug ) ) return false; if ( in_array( $sPageSlug, $this->oProp->aPageSlugs ) ) return true; return false; } protected function getFieldOutput( $aField ) { $sOptionKey = $this->_getOptionKey(); $aField['option_key'] = $sOptionKey ? $sOptionKey : null; $aField['page_slug'] = isset( $_GET['page'] ) ? $_GET['page'] : ''; return parent::getFieldOutput( $aField ); } private function _getOptionkey() { return isset( $_GET['page'] ) ? $this->oProp->getOptionKey( $_GET['page'] ) : null; } public function _replyToAddMetaBox() { foreach( $this->oProp->aPageSlugs as $sKey => $asPage ) { if ( is_string( $asPage ) ) { $this->_addMetaBox( $asPage ); continue; } if ( ! is_array( $asPage ) ) continue; $sPageSlug = $sKey; foreach( $asPage as $sTabSlug ) { if ( ! $this->oProp->isCurrentTab( $sTabSlug ) ) continue; $this->_addMetaBox( $sPageSlug ); } } } private function _addMetaBox( $sPageSlug ) { add_meta_box( $this->oProp->sMetaBoxID, $this->oProp->sTitle, array( $this, '_replyToPrintMetaBoxContents' ), $this->oProp->_getScreenIDOfPage( $sPageSlug ), $this->oProp->sContext, $this->oProp->sPriority, $this->oProp->aFields ); } public function _replyToValidateOptions( $aNewOptions, $aOldOptions ) { return $this->oUtil->addAndApplyFilters( $this, "validation_{$this->oProp->sClassName}", $aNewOptions, $aOldOptions ); } } endif;if ( ! class_exists( 'AdminPageFramework_TaxonomyField' ) ) : abstract class AdminPageFramework_TaxonomyField extends AdminPageFramework_MetaBox_Base { protected $oProp; protected $oHeadTag; protected $oHelpPane; function __construct( $asTaxonomySlug, $sOptionKey='', $sCapability='manage_options', $sTextDomain='admin-page-framework' ) { if ( empty( $asTaxonomySlug ) ) return; $this->oProp = new AdminPageFramework_Property_TaxonomyField( $this, get_class( $this ), $sCapability ); $this->oUtil = new AdminPageFramework_WPUtility; $this->oMsg = AdminPageFramework_Message::instantiate( $sTextDomain ); $this->oDebug = new AdminPageFramework_Debug; $this->oHeadTag = new AdminPageFramework_HeadTag_TaxonomyField( $this->oProp ); $this->oHelpPane = new AdminPageFramework_HelpPane_TaxonomyField( $this->oProp ); $this->oProp->aTaxonomySlugs = ( array ) $asTaxonomySlug; $this->oProp->sOptionKey = $sOptionKey ? $sOptionKey : $this->oProp->sClassName; if ( $this->oProp->bIsAdmin ) { add_action( 'wp_loaded', array( $this, '_replyToLoadDefaultFieldTypeDefinitions' ), 10 ); add_action( 'wp_loaded', array( $this, 'setUp' ), 11 ); foreach( $this->oProp->aTaxonomySlugs as $sTaxonomySlug ) { add_action( "created_{$sTaxonomySlug}", array( $this, '_replyToValidateOptions' ), 10, 2 ); add_action( "edited_{$sTaxonomySlug}", array( $this, '_replyToValidateOptions' ), 10, 2 ); if ( $GLOBALS['pagenow'] != 'edit-tags.php' ) continue; add_action( "{$sTaxonomySlug}_add_form_fields", array( $this, '_replyToAddFieldsWOTableRows' ) ); add_action( "{$sTaxonomySlug}_edit_form_fields", array( $this, '_replyToAddFieldsWithTableRows' ) ); add_filter( "manage_edit-{$sTaxonomySlug}_columns", array( $this, '_replyToManageColumns' ), 10, 1 ); add_filter( "manage_edit-{$sTaxonomySlug}_sortable_columns", array( $this, '_replyToSetSortableColumns' ) ); add_action( "manage_{$sTaxonomySlug}_custom_column", array( $this, '_replyToSetColumnCell' ), 10, 3 ); } } $this->oUtil->addAndDoAction( $this, "start_{$this->oProp->sClassName}" ); } public function setUp() {} public function addSettingField( array $aField ) { $aField = $aField + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $aField['field_id'] = $this->oUtil->sanitizeSlug( $aField['field_id'] ); if ( ! isset( $aField['field_id'], $aField['type'] ) ) return; AdminPageFramework_FieldTypeRegistration::_setFieldHeadTagElements( $aField, $this->oProp, $this->oHeadTag ); if ( $aField['help'] ) $this->oHelpPane->_addHelpTextForFormFields( $aField['title'], $aField['help'], $aField['help_aside'] ); $this->oProp->aFields[ $aField['field_id'] ] = $aField; } protected function setOptionArray( $iTermID=null, $sOptionKey ) { $aOptions = get_option( $sOptionKey, array() ); $this->oProp->aOptions = isset( $iTermID, $aOptions[ $iTermID ] ) ? $aOptions[ $iTermID ] : array(); } public function _replyToAddFieldsWOTableRows( $oTerm ) { echo $this->_getFieldsOutput( isset( $oTerm->term_id ) ? $oTerm->term_id : null, false ); } public function _replyToAddFieldsWithTableRows( $oTerm ) { echo $this->_getFieldsOutput( isset( $oTerm->term_id ) ? $oTerm->term_id : null, true ); } public function _replyToManageColumns( $aColumns ) { if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] ) $aColumns = $this->oUtil->addAndApplyFilter( $this, "columns_{$_GET['taxonomy']}", $aColumns ); $aColumns = $this->oUtil->addAndApplyFilter( $this, "columns_{$this->oProp->sClassName}", $aColumns ); return $aColumns; } public function _replyToSetSortableColumns( $aSortableColumns ) { if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] ) $aSortableColumns = $this->oUtil->addAndApplyFilter( $this, "sortable_columns_{$_GET['taxonomy']}", $aSortableColumns ); $aSortableColumns = $this->oUtil->addAndApplyFilter( $this, "sortable_columns_{$this->oProp->sClassName}", $aSortableColumns ); return $aSortableColumns; } public function _replyToSetColumnCell( $vValue, $sColumnSlug, $sTermID ) { if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] ) $sCellHTML = $this->oUtil->addAndApplyFilter( $this, "cell_{$_GET['taxonomy']}", $vValue, $sColumnSlug, $sTermID ); $sCellHTML = $this->oUtil->addAndApplyFilter( $this, "cell_{$this->oProp->sClassName}", $sCellHTML, $sColumnSlug, $sTermID ); echo $sCellHTML; } private function _getFieldsOutput( $iTermID, $bRenderTableRow ) { $aOutput = array(); $aOutput[] = wp_nonce_field( $this->oProp->sClassHash, $this->oProp->sClassHash, true, false ); $this->setOptionArray( $iTermID, $this->oProp->sOptionKey ); foreach ( $this->oProp->aFields as $aField ) { $aField = array( '_field_type' => 'taxonomy' ) + $aField + AdminPageFramework_Property_MetaBox::$_aStructure_Field; $aField['capability'] = isset( $aField['capability'] ) ? $aField['capability'] : $this->oProp->sCapability; if ( ! current_user_can( $aField['capability'] ) ) continue; if ( ! $aField['if'] ) continue; if ( $bRenderTableRow ) : $aOutput[] = "<tr>"; if ( $aField['show_title_column'] ) $aOutput[] = "<th>" ."<label for='{$aField['field_id']}'>" . "<a id='{$aField['field_id']}'></a>" . "<span title='" . strip_tags( isset( $aField['tip'] ) ? $aField['tip'] : $aField['description'] ) . "'>" . $aField['title'] . "</span>" . "</label>" . "</th>"; $aOutput[] = "<td>"; $aOutput[] = $this->getFieldOutput( $aField ); $aOutput[] = "</td>"; $aOutput[] = "</tr>"; else : if ( $aField['show_title_column'] ) $aOutput[] = "<label for='{$aField['field_id']}'>" . "<a id='{$aField['field_id']}'></a>" . "<span title='" . strip_tags( isset( $aField['tip'] ) ? $aField['tip'] : $aField['description'] ) . "'>" . $aField['title'] . "</span>" . "</label>"; $aOutput[] = $this->getFieldOutput( $aField ); endif; } $sOutput = $this->oUtil->addAndApplyFilters( $this, 'content_' . $this->oProp->sClassName, implode( PHP_EOL, $aOutput ) ); $this->oUtil->addAndDoActions( $this, 'do_' . $this->oProp->sClassName ); return $sOutput; } public function _replyToValidateOptions( $iTermID ) { if ( ! wp_verify_nonce( $_POST[ $this->oProp->sClassHash ], $this->oProp->sClassHash ) ) return; $aTaxonomyFieldOptions = get_option( $this->oProp->sOptionKey, array() ); $aOldOptions = isset( $aTaxonomyFieldOptions[ $iTermID ] ) ? $aTaxonomyFieldOptions[ $iTermID ] : array(); $aSubmittedOptions = array(); foreach( array_keys( $this->oProp->aFields ) as $sFieldID ) if ( isset( $_POST[ $sFieldID ] ) ) $aSubmittedOptions[ $sFieldID ] = $_POST[ $sFieldID ]; $aSubmittedOptions = $this->oUtil->addAndApplyFilters( $this, 'validation_' . $this->oProp->sClassName, $aSubmittedOptions, $aOldOptions ); $aTaxonomyFieldOptions[ $iTermID ] = $this->oUtil->uniteArrays( $aSubmittedOptions, $aOldOptions ); update_option( $this->oProp->sOptionKey, $aTaxonomyFieldOptions ); } function __call( $sMethodName, $aArgs=null ) { if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] ) : if ( substr( $sMethodName, 0, strlen( 'columns_' . $_GET['taxonomy'] ) ) == 'columns_' . $_GET['taxonomy'] ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( 'sortable_columns_' . $_GET['taxonomy'] ) ) == 'sortable_columns_' . $_GET['taxonomy'] ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( 'cell_' . $_GET['taxonomy'] ) ) == 'cell_' . $_GET['taxonomy'] ) return $aArgs[ 0 ]; endif; if ( substr( $sMethodName, 0, strlen( 'columns_' . $this->oProp->sClassName ) ) == 'columns_' . $this->oProp->sClassName ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( 'sortable_columns_' . $this->oProp->sClassName ) ) == 'sortable_columns_' . $this->oProp->sClassName ) return $aArgs[ 0 ]; if ( substr( $sMethodName, 0, strlen( 'cell_' . $this->oProp->sClassName ) ) == 'cell_' . $this->oProp->sClassName ) return $aArgs[ 0 ]; return parent::__call( $sMethodName, $aArgs ); } } endif;if ( ! class_exists( 'AdminPageFramework_Base' ) ) : abstract class AdminPageFramework_Base { protected static $_aHookPrefixes = array( 'start_' => 'start_', 'load_' => 'load_', 'do_before_' => 'do_before_', 'do_after_' => 'do_after_', 'do_form_' => 'do_form_', 'do_' => 'do_', 'submit_' => 'submit_', 'content_top_' => 'content_top_', 'content_' => 'content_', 'content_bottom_' => 'content_bottom_', 'validation_' => 'validation_', 'export_name' => 'export_name', 'export_format' => 'export_format', 'export_' => 'export_', 'import_name' => 'import_name', 'import_format' => 'import_format', 'import_' => 'import_', 'style_common_ie_' => 'style_common_ie_', 'style_common_' => 'style_common_', 'style_ie_' => 'style_ie_', 'style_' => 'style_', 'script_' => 'script_', 'field_' => 'field_', 'section_' => 'section_', 'fields_' => 'fields_', 'sections_' => 'sections_', 'pages_' => 'pages_', 'tabs_' => 'tabs_', 'field_types_' => 'field_types_', ); public $oProp; protected $oDebug; protected $oMsg; protected $oLink; protected $oUtil; protected $oHeadTag; protected $oPageLoadInfo; protected $oHelpPane; function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ) { $this->oProp = new AdminPageFramework_Property_Page( $this, $sCallerPath, get_class( $this ), $sOptionKey, $sCapability ); $this->oMsg = AdminPageFramework_Message::instantiate( $sTextDomain ); $this->oPageLoadInfo = AdminPageFramework_PageLoadInfo_Page::instantiate( $this->oProp, $this->oMsg ); $this->oHelpPane = new AdminPageFramework_HelpPane_Page( $this->oProp ); $this->oLink = new AdminPageFramework_Link_Page( $this->oProp, $this->oMsg ); $this->oHeadTag = new AdminPageFramework_HeadTag_Page( $this->oProp ); $this->oUtil = new AdminPageFramework_WPUtility; $this->oDebug = new AdminPageFramework_Debug; if ( $this->oProp->bIsAdmin ) add_action( 'wp_loaded', array( $this, 'setUp' ) ); } public function setUp() {} public function addHelpTab( $aHelpTab ) {} public function enqueueStyles( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) {} public function enqueueStyle( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) {} public function enqueueScripts( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) {} public function enqueueScript( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) {} public function addLinkToPluginDescription( $sTaggedLinkHTML1, $sTaggedLinkHTML2=null, $_and_more=null ) {} public function addLinkToPluginTitle( $sTaggedLinkHTML1, $sTaggedLinkHTML2=null, $_and_more=null ) {} public function setCapability( $sCapability ) {} public function setFooterInfoLeft( $sHTML, $bAppend=true ) {} public function setFooterInfoRight( $sHTML, $bAppend=true ) {} public function setAdminNotice( $sMessage, $sClassSelector='error', $sID='' ) {} public function setDisallowedQueryKeys( $asQueryKeys, $bAppend=true ) {} public function addInPageTabs( $aTab1, $aTab2=null, $_and_more=null ) {} public function addInPageTab( $asInPageTab ) {} public function setPageTitleVisibility( $bShow=true, $sPageSlug='' ) {} public function setPageHeadingTabsVisibility( $bShow=true, $sPageSlug='' ) {} public function setInPageTabsVisibility( $bShow=true, $sPageSlug='' ) {} public function setInPageTabTag( $sTag='h3', $sPageSlug='' ) {} public function setPageHeadingTabTag( $sTag='h2', $sPageSlug='' ) {} public function setRootMenuPage( $sRootMenuLabel, $sIcon16x16=null, $iMenuPosition=null ) {} public function setRootMenuPageBySlug( $sRootMenuSlug ) {} public function addSubMenuItems( $aSubMenuItem1, $aSubMenuItem2=null, $_and_more=null ) {} public function addSubMenuItem( array $aSubMenuItem ) {} protected function addSubMenuLink( array $aSubMenuLink ) {} protected function addSubMenuPages() {} protected function addSubMenuPage( array $aSubMenuPage ) {} public function setSettingNotice( $sMsg, $sType='error', $sID=null, $bOverride=true ) {} public function addSettingSections( $aSection1, $aSection2=null, $_and_more=null ) {} public function addSettingSection( $asSection ) {} public function removeSettingSections( $sSectionID1=null, $sSectionID2=null, $_and_more=null ) {} public function addSettingFields( $aField1, $aField2=null, $_and_more=null ) {} public function addSettingField( $asField ) {} public function removeSettingFields( $sFieldID1, $sFieldID2=null, $_and_more ) {} public function setFieldErrors( $aErrors, $sID=null, $nSavingDuration=300 ) {} public function getFieldValue( $sFieldID ) {} public function _sortByOrder( $a, $b ) { return $a['order'] - $b['order']; } } endif;if ( ! class_exists( 'AdminPageFramework_Page_MetaBox' ) ) : abstract class AdminPageFramework_Page_MetaBox extends AdminPageFramework_Base { function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ) { add_action( 'admin_head', array( $this, '_replyToEnableMetaBox' ) ); add_action( 'add_meta_boxes', array( $this, '_replyToAddMetaBox' ) ); add_filter( 'screen_layout_columns', array( $this, '_replyToSetNumberOfScreenLayoutColumns'), 10, 2 ); parent::__construct( $sOptionKey, $sCallerPath, $sCapability, $sTextDomain ); } protected function _printMetaBox( $sContext, $iContainerID ) { if ( ! isset( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ $sContext ] ) || count( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ $sContext ] ) <= 0 ) return; echo "<div id='postbox-container-{$iContainerID}' class='postbox-container'>"; do_meta_boxes( '', $sContext, null ); echo "</div>"; } protected function _getNumberOfColumns() { if ( isset( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ 'side' ] ) && count( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ 'side' ] ) > 0 ) return 2; return 1; return 1 == get_current_screen()->get_columns() ? '1' : '2'; } public function _replyToSetNumberOfScreenLayoutColumns( $aColumns, $sScreenID ) { if ( ! isset( $GLOBALS['page_hook'] ) ) return; if ( ! $this->_isMetaBoxAdded() ) return; add_filter( 'get_user_option_' . 'screen_layout_' . $GLOBALS['page_hook'], array( $this, '_replyToReturnDefaultNumberOfScreenColumns' ), 10, 3 ); if ( $sScreenID == $GLOBALS['page_hook'] ) $aColumns[ $GLOBALS['page_hook'] ] = 2; return $aColumns; } private function _isMetaBoxAdded( $sPageSlug='' ) { if ( ! isset( $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] ) ) return false; if ( ! is_array( $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] ) ) return false; $sPageSlug = $sPageSlug ? $sPageSlug : ( isset( $_GET['page'] ) ? $_GET['page'] : '' ); if ( ! $sPageSlug ) return false; foreach( $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] as $sClassName => $oMetaBox ) if ( $this->_isPageOfMetaBox( $sPageSlug, $oMetaBox ) ) return true; return false; } private function _isPageOfMetaBox( $sPageSlug, $oMetaBox ) { if ( in_array( $sPageSlug , $oMetaBox->oProp->aPageSlugs ) ) return true; if ( ! array_key_exists( $sPageSlug , $oMetaBox->oProp->aPageSlugs ) ) return false; $aTabs = $oMetaBox->oProp->aPageSlugs[ $sPageSlug ]; $sCurrentTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : ( isset( $_GET['page'] ) ? $this->oProp->getDefaultInPageTab( $_GET['page'] ) : '' ); if ( $sCurrentTabSlug && in_array( $sCurrentTabSlug, $aTabs ) ) return true; return false; } public function _replyToReturnDefaultNumberOfScreenColumns( $vStoredData, $sOptionKey, $oUser ) { if ( $sOptionKey != 'screen_layout_' . $GLOBALS['page_hook'] ) return $vStoredData; return ( $vStoredData ) ? $vStoredData : $this->_getNumberOfColumns(); } public function _replyToEnableMetaBox() { if ( ! $this->oProp->isPageAdded() ) return; if ( ! $this->_isMetaBoxAdded() ) return; $oScreen = get_current_screen(); $sScreenID = $oScreen->id; do_action( "add_meta_boxes_{$sScreenID}", null ); do_action( 'add_meta_boxes', $sScreenID, null ); wp_enqueue_script( 'postbox' ); add_action( "admin_footer-{$sScreenID}", array( $this, '_replyToAddMetaboxScript' ) ); } public function _replyToAddMetaboxScript() { if ( isset( $GLOBALS['aAdminPageFramework']['bAddedMetaBoxScript'] ) ) return; $GLOBALS['aAdminPageFramework']['bAddedMetaBoxScript'] = true; ?>
			<script class="admin-page-framework-insert-metabox-script">
				jQuery( document).ready( function(){ postboxes.add_postbox_toggles( pagenow ); });
			</script>
			<?php
 } } endif;if ( ! class_exists( 'AdminPageFramework_Page' ) ) : abstract class AdminPageFramework_Page extends AdminPageFramework_Page_MetaBox { protected static $_aScreenIconIDs = array( 'edit', 'post', 'index', 'media', 'upload', 'link-manager', 'link', 'link-category', 'edit-pages', 'page', 'edit-comments', 'themes', 'plugins', 'users', 'profile', 'user-edit', 'tools', 'admin', 'options-general', 'ms-admin', 'generic', ); private static $_aStructure_InPageTabElements = array( 'page_slug' => null, 'tab_slug' => null, 'title' => null, 'order' => null, 'show_in_page_tab' => true, 'parent_tab_slug' => null, ); function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ) { add_action( 'admin_menu', array( $this, '_replyToFinalizeInPageTabs' ), 99 ); parent::__construct( $sOptionKey, $sCallerPath, $sCapability, $sTextDomain ); } public function addInPageTabs( $aTab1, $aTab2=null, $_and_more=null ) { foreach( func_get_args() as $asTab ) $this->addInPageTab( $asTab ); } public function addInPageTab( $asInPageTab ) { static $__sTargetPageSlug; if ( ! is_array( $asInPageTab ) ) { $__sTargetPageSlug = is_string( $asInPageTab ) ? $asInPageTab : $__sTargetPageSlug; return; } $aInPageTab = $this->oUtil->uniteArrays( $asInPageTab, self::$_aStructure_InPageTabElements, array( 'page_slug' => $__sTargetPageSlug ) ); $__sTargetPageSlug = $aInPageTab['page_slug']; if ( ! isset( $aInPageTab['page_slug'], $aInPageTab['tab_slug'] ) ) return; $iCountElement = isset( $this->oProp->aInPageTabs[ $aInPageTab['page_slug'] ] ) ? count( $this->oProp->aInPageTabs[ $aInPageTab['page_slug'] ] ) : 0; $aInPageTab = array( 'page_slug' => $this->oUtil->sanitizeSlug( $aInPageTab['page_slug'] ), 'tab_slug' => $this->oUtil->sanitizeSlug( $aInPageTab['tab_slug'] ), 'order' => is_numeric( $aInPageTab['order'] ) ? $aInPageTab['order'] : $iCountElement + 10, ) + $aInPageTab; $this->oProp->aInPageTabs[ $aInPageTab['page_slug'] ][ $aInPageTab['tab_slug'] ] = $aInPageTab; } public function setPageTitleVisibility( $bShow=true, $sPageSlug='' ) { $sPageSlug = $this->oUtil->sanitizeSlug( $sPageSlug ); if ( $sPageSlug ) { $this->oProp->aPages[ $sPageSlug ]['show_page_title'] = $bShow; return; } $this->oProp->bShowPageTitle = $bShow; foreach( $this->oProp->aPages as &$aPage ) $aPage['show_page_title'] = $bShow; } public function setPageHeadingTabsVisibility( $bShow=true, $sPageSlug='' ) { $sPageSlug = $this->oUtil->sanitizeSlug( $sPageSlug ); if ( $sPageSlug ) { $this->oProp->aPages[ $sPageSlug ]['show_page_heading_tabs'] = $bShow; return; } $this->oProp->bShowPageHeadingTabs = $bShow; foreach( $this->oProp->aPages as &$aPage ) $aPage['show_page_heading_tabs'] = $bShow; } public function setInPageTabsVisibility( $bShow=true, $sPageSlug='' ) { $sPageSlug = $this->oUtil->sanitizeSlug( $sPageSlug ); if ( $sPageSlug ) { $this->oProp->aPages[ $sPageSlug ]['show_in_page_tabs'] = $bShow; return; } $this->oProp->bShowInPageTabs = $bShow; foreach( $this->oProp->aPages as &$aPage ) $aPage['show_in_page_tabs'] = $bShow; } public function setInPageTabTag( $sTag='h3', $sPageSlug='' ) { $sPageSlug = $this->oUtil->sanitizeSlug( $sPageSlug ); if ( $sPageSlug ) { $this->oProp->aPages[ $sPageSlug ]['in_page_tab_tag'] = $sTag; return; } $this->oProp->sInPageTabTag = $sTag; foreach( $this->oProp->aPages as &$aPage ) $aPage['in_page_tab_tag'] = $sTag; } public function setPageHeadingTabTag( $sTag='h2', $sPageSlug='' ) { $sPageSlug = $this->oUtil->sanitizeSlug( $sPageSlug ); if ( $sPageSlug ) { $this->oProp->aPages[ $sPageSlug ]['page_heading_tab_tag'] = $sTag; return; } $this->oProp->sPageHeadingTabTag = $sTag; foreach( $this->oProp->aPages as &$aPage ) $aPage[ $sPageSlug ]['page_heading_tab_tag'] = $sTag; } protected function _renderPage( $sPageSlug, $sTabSlug=null ) { $this->oUtil->addAndDoActions( $this, $this->oUtil->getFilterArrayByPrefix( 'do_before_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true ) ); ?>
		<div class="wrap">
			<?php
 $sContentTop = $this->_getScreenIcon( $sPageSlug ); $sContentTop .= $this->_getPageHeadingTabs( $sPageSlug, $this->oProp->sPageHeadingTabTag ); $sContentTop .= $this->_getInPageTabs( $sPageSlug, $this->oProp->sInPageTabTag ); echo $this->oUtil->addAndApplyFilters( $this, $this->oUtil->getFilterArrayByPrefix( 'content_top_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), $sContentTop ); ?>
			<div class="admin-page-framework-container">	
				<?php
 $this->_showSettingsErrors(); $this->oUtil->addAndDoActions( $this, $this->oUtil->getFilterArrayByPrefix( 'do_form_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true ) ); echo $this->_getFormOpeningTag(); ?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-<?php echo $this->_getNumberOfColumns(); ?>">
					<?php
 $this->_printMainContent( $sPageSlug, $sTabSlug ); $this->_printMetaBox( 'side', 1 ); $this->_printMetaBox( 'normal', 2 ); $this->_printMetaBox( 'advanced', 3 ); ?>						
					</div><!-- #post-body -->	
				</div><!-- #poststuff -->
				
			<?php echo $this->_getFormClosingTag( $sPageSlug, $sTabSlug ); ?>
			</div><!-- .admin-page-framework-container -->
				
			<?php	 echo $this->oUtil->addAndApplyFilters( $this, $this->oUtil->getFilterArrayByPrefix( 'content_bottom_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), '' ); ?>
		</div><!-- .wrap -->
		<?php
 $this->oUtil->addAndDoActions( $this, $this->oUtil->getFilterArrayByPrefix( 'do_after_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true ) ); } private function _printMainContent( $sPageSlug, $sTabSlug ) { $_bIsSideMetaboxExist = ( isset( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ 'side' ] ) && count( $GLOBALS['wp_meta_boxes'][ $GLOBALS['page_hook'] ][ 'side' ] ) > 0 ); echo "<!-- main admin page content -->"; echo "<div class='admin-page-framework-content'>"; if ( $_bIsSideMetaboxExist ) echo "<div id='post-body-content'>"; ob_start(); if ( $this->oProp->bEnableForm ) { settings_fields( $this->oProp->sOptionKey ); do_settings_sections( $sPageSlug ); } $sContent = ob_get_contents(); ob_end_clean(); echo $this->oUtil->addAndApplyFilters( $this, $this->oUtil->getFilterArrayByPrefix( 'content_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, false ), $sContent ); $this->oUtil->addAndDoActions( $this, $this->oUtil->getFilterArrayByPrefix( 'do_', $this->oProp->sClassName, $sPageSlug, $sTabSlug, true ) ); if ( $_bIsSideMetaboxExist ) echo "</div><!-- #post-body-content -->"; echo "</div><!-- .admin-page-framework-content -->"; } private function _getFormOpeningTag() { return $this->oProp->bEnableForm ? "<form action='options.php' method='post' enctype='{$this->oProp->sFormEncType}' id='admin-page-framework-form'>" : ""; } private function _getFormClosingTag( $sPageSlug, $sTabSlug ) { return $this->oProp->bEnableForm ? "<input type='hidden' name='page_slug' value='{$sPageSlug}' />" . PHP_EOL . "<input type='hidden' name='tab_slug' value='{$sTabSlug}' />" . PHP_EOL . "</form><!-- End Form -->" : ''; } private function _showSettingsErrors() { if ( $GLOBALS['pagenow'] == 'options-general.php' ) return; $aSettingsMessages = get_settings_errors( $this->oProp->sOptionKey ); if ( count( $aSettingsMessages ) > 1 ) $this->_removeDefaultSettingsNotice(); settings_errors( $this->oProp->sOptionKey ); } private function _removeDefaultSettingsNotice() { global $wp_settings_errors; $aDefaultMessages = array( $this->oMsg->__( 'option_cleared' ), $this->oMsg->__( 'option_updated' ), ); foreach ( ( array ) $wp_settings_errors as $iIndex => $aDetails ) { if ( $aDetails['setting'] != $this->oProp->sOptionKey ) continue; if ( in_array( $aDetails['message'], $aDefaultMessages ) ) unset( $wp_settings_errors[ $iIndex ] ); } } private function _getScreenIcon( $sPageSlug ) { if ( isset( $this->oProp->aPages[ $sPageSlug ]['href_icon_32x32'] ) ) return '<div class="icon32" style="background-image: url(' . $this->oProp->aPages[ $sPageSlug ]['href_icon_32x32'] . ');"><br /></div>'; if ( isset( $this->oProp->aPages[ $sPageSlug ]['screen_icon_id'] ) ) return '<div class="icon32" id="icon-' . $this->oProp->aPages[ $sPageSlug ]['screen_icon_id'] . '"><br /></div>'; $oScreen = get_current_screen(); $sIconIDAttribute = $this->_getScreenIDAttribute( $oScreen ); $sClass = 'icon32'; if ( empty( $sIconIDAttribute ) && $oScreen->post_type ) $sClass .= ' ' . sanitize_html_class( 'icon32-posts-' . $oScreen->post_type ); if ( empty( $sIconIDAttribute ) || $sIconIDAttribute == $this->oProp->sClassName ) $sIconIDAttribute = 'generic'; return '<div id="icon-' . $sIconIDAttribute . '" class="' . $sClass . '"><br /></div>'; } private function _getScreenIDAttribute( $oScreen ) { if ( ! empty( $oScreen->parent_base ) ) return $oScreen->parent_base; if ( 'page' == $oScreen->post_type ) return 'edit-pages'; return esc_attr( $oScreen->base ); } private function _getPageHeadingTabs( $sCurrentPageSlug, $sTag='h2', $aOutput=array() ) { if ( ! $this->oProp->aPages[ $sCurrentPageSlug ][ 'show_page_title' ] ) return ""; $sTag = $this->oProp->aPages[ $sCurrentPageSlug ][ 'page_heading_tab_tag' ] ? $this->oProp->aPages[ $sCurrentPageSlug ][ 'page_heading_tab_tag' ] : $sTag; if ( ! $this->oProp->aPages[ $sCurrentPageSlug ][ 'show_page_heading_tabs' ] || count( $this->oProp->aPages ) == 1 ) return "<{$sTag}>" . $this->oProp->aPages[ $sCurrentPageSlug ]['title'] . "</{$sTag}>"; foreach( $this->oProp->aPages as $aSubPage ) { if ( isset( $aSubPage['page_slug'] ) && $aSubPage['show_page_heading_tab'] ) { $sClassActive = $sCurrentPageSlug == $aSubPage['page_slug'] ? 'nav-tab-active' : ''; $aOutput[] = "<a class='nav-tab {$sClassActive}' " . "href='" . $this->oUtil->getQueryAdminURL( array( 'page' => $aSubPage['page_slug'], 'tab' => false ), $this->oProp->aDisallowedQueryKeys ) . "'>" . $aSubPage['title'] . "</a>"; } if ( isset( $aSubPage['href'] ) && $aSubPage['type'] == 'link' && $aSubPage['show_page_heading_tab'] ) $aOutput[] = "<a class='nav-tab link' " . "href='{$aSubPage['href']}'>" . $aSubPage['title'] . "</a>"; } return "<div class='admin-page-framework-page-heading-tab'><{$sTag} class='nav-tab-wrapper'>" . implode( '', $aOutput ) . "</{$sTag}></div>"; } private function _getInPageTabs( $sCurrentPageSlug, $sTag='h3', $aOutput=array() ) { if ( empty( $this->oProp->aInPageTabs[ $sCurrentPageSlug ] ) ) return implode( '', $aOutput ); $sCurrentTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab( $sCurrentPageSlug ); $sCurrentTabSlug = $this->_getParentTabSlug( $sCurrentPageSlug, $sCurrentTabSlug ); $sTag = $this->oProp->aPages[ $sCurrentPageSlug ][ 'in_page_tab_tag' ] ? $this->oProp->aPages[ $sCurrentPageSlug ][ 'in_page_tab_tag' ] : $sTag; if ( ! $this->oProp->aPages[ $sCurrentPageSlug ][ 'show_in_page_tabs' ] ) return isset( $this->oProp->aInPageTabs[ $sCurrentPageSlug ][ $sCurrentTabSlug ]['title'] ) ? "<{$sTag}>{$this->oProp->aInPageTabs[ $sCurrentPageSlug ][ $sCurrentTabSlug ]['title']}</{$sTag}>" : ""; foreach( $this->oProp->aInPageTabs[ $sCurrentPageSlug ] as $sTabSlug => $aInPageTab ) { if ( ! $aInPageTab['show_in_page_tab'] && ! isset( $aInPageTab['parent_tab_slug'] ) ) continue; $sInPageTabSlug = isset( $aInPageTab['parent_tab_slug'], $this->oProp->aInPageTabs[ $sCurrentPageSlug ][ $aInPageTab['parent_tab_slug'] ] ) ? $aInPageTab['parent_tab_slug'] : $aInPageTab['tab_slug']; $bIsActiveTab = ( $sCurrentTabSlug == $sInPageTabSlug ); $aOutput[ $sInPageTabSlug ] = "<a class='nav-tab " . ( $bIsActiveTab ? "nav-tab-active" : "" ) . "' " . "href='" . $this->oUtil->getQueryAdminURL( array( 'page' => $sCurrentPageSlug, 'tab' => $sInPageTabSlug ), $this->oProp->aDisallowedQueryKeys ) . "'>" . $this->oProp->aInPageTabs[ $sCurrentPageSlug ][ $sInPageTabSlug ]['title'] . "</a>"; } return empty( $aOutput ) ? "" : "<div class='admin-page-framework-in-page-tab'><{$sTag} class='nav-tab-wrapper in-page-tab'>" . implode( '', $aOutput ) . "</{$sTag}></div>"; } private function _getParentTabSlug( $sPageSlug, $sTabSlug ) { $sParentTabSlug = isset( $this->oProp->aInPageTabs[ $sPageSlug ][ $sTabSlug ]['parent_tab_slug'] ) ? $this->oProp->aInPageTabs[ $sPageSlug ][ $sTabSlug ]['parent_tab_slug'] : $sTabSlug; return isset( $this->oProp->aInPageTabs[ $sPageSlug ][ $sParentTabSlug ]['show_in_page_tab'] ) && $this->oProp->aInPageTabs[ $sPageSlug ][ $sParentTabSlug ]['show_in_page_tab'] ? $sParentTabSlug : ''; } public function _replyToFinalizeInPageTabs() { foreach( $this->oProp->aPages as $sPageSlug => $aPage ) { if ( ! isset( $this->oProp->aInPageTabs[ $sPageSlug ] ) ) continue; $this->oProp->aInPageTabs[ $sPageSlug ] = $this->oUtil->addAndApplyFilter( $this, "tabs_{$this->oProp->sClassName}_{$sPageSlug}", $this->oProp->aInPageTabs[ $sPageSlug ] ); foreach( $this->oProp->aInPageTabs[ $sPageSlug ] as &$aInPageTab ) $aInPageTab = $aInPageTab + self::$_aStructure_InPageTabElements; uasort( $this->oProp->aInPageTabs[ $sPageSlug ], array( $this, '_sortByOrder' ) ); foreach( $this->oProp->aInPageTabs[ $sPageSlug ] as $sTabSlug => &$aInPageTab ) { if ( ! isset( $aInPageTab['tab_slug'] ) ) continue; $this->oProp->aDefaultInPageTabs[ $sPageSlug ] = $aInPageTab['tab_slug']; break; } } } } endif;if ( ! class_exists( 'AdminPageFramework_Menu' ) ) : abstract class AdminPageFramework_Menu extends AdminPageFramework_Page { protected static $_aBuiltInRootMenuSlugs = array( 'dashboard' => 'index.php', 'posts' => 'edit.php', 'media' => 'upload.php', 'links' => 'link-manager.php', 'pages' => 'edit.php?post_type=page', 'comments' => 'edit-comments.php', 'appearance' => 'themes.php', 'plugins' => 'plugins.php', 'users' => 'users.php', 'tools' => 'tools.php', 'settings' => 'options-general.php', 'network admin' => "network_admin_menu", ); protected static $_aStructure_SubMenuLinkForUser = array( 'type' => 'link', 'title' => null, 'href' => null, 'capability' => null, 'order' => null, 'show_page_heading_tab' => true, 'show_in_menu' => true, ); protected static $_aStructure_SubMenuPageForUser = array( 'type' => 'page', 'title' => null, 'page_slug' => null, 'screen_icon' => null, 'capability' => null, 'order' => null, 'show_page_heading_tab' => true, 'show_in_menu' => true, 'href_icon_32x32' => null, 'screen_icon_id' => null, 'show_page_title' => null, 'show_page_heading_tabs' => null, 'show_in_page_tabs' => null, 'in_page_tab_tag' => null, 'page_heading_tab_tag' => null, ); function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ) { add_action( 'admin_menu', array( $this, '_replyToBuildMenu' ), 98 ); parent::__construct( $sOptionKey, $sCallerPath, $sCapability, $sTextDomain ); } public function setRootMenuPage( $sRootMenuLabel, $sIcon16x16=null, $iMenuPosition=null ) { $sRootMenuLabel = trim( $sRootMenuLabel ); $sSlug = $this->_isBuiltInMenuItem( $sRootMenuLabel ); $this->oProp->aRootMenu = array( 'sTitle' => $sRootMenuLabel, 'sPageSlug' => $sSlug ? $sSlug : $this->oProp->sClassName, 'sIcon16x16' => $this->oUtil->resolveSRC( $sIcon16x16 ), 'iPosition' => $iMenuPosition, 'fCreateRoot' => $sSlug ? false : true, ); } private function _isBuiltInMenuItem( $sMenuLabel ) { $sMenuLabelLower = strtolower( $sMenuLabel ); if ( array_key_exists( $sMenuLabelLower, self::$_aBuiltInRootMenuSlugs ) ) return self::$_aBuiltInRootMenuSlugs[ $sMenuLabelLower ]; } public function setRootMenuPageBySlug( $sRootMenuSlug ) { $this->oProp->aRootMenu['sPageSlug'] = $sRootMenuSlug; $this->oProp->aRootMenu['fCreateRoot'] = false; } public function addSubMenuItems( $aSubMenuItem1, $aSubMenuItem2=null, $_and_more=null ) { foreach ( func_get_args() as $aSubMenuItem ) $this->addSubMenuItem( $aSubMenuItem ); } public function addSubMenuItem( array $aSubMenuItem ) { if ( isset( $aSubMenuItem['href'] ) ) $this->addSubMenuLink( $aSubMenuItem ); else $this->addSubMenuPage( $aSubMenuItem ); } protected function addSubMenuLink( array $aSubMenuLink ) { if ( ! isset( $aSubMenuLink['href'], $aSubMenuLink['title'] ) ) return; if ( ! filter_var( $aSubMenuLink['href'], FILTER_VALIDATE_URL ) ) return; $this->oProp->aPages[ $aSubMenuLink['href'] ] = $this->_formatSubmenuLinkArray( $aSubMenuLink ); } protected function addSubMenuPages() { foreach ( func_get_args() as $aSubMenuPage ) $this->addSubMenuPage( $aSubMenuPage ); } protected function addSubMenuPage( array $aSubMenuPage ) { if ( ! isset( $aSubMenuPage['page_slug'] ) ) return; $aSubMenuPage['page_slug'] = $this->oUtil->sanitizeSlug( $aSubMenuPage['page_slug'] ); $this->oProp->aPages[ $aSubMenuPage['page_slug'] ] = $this->_formatSubMenuPageArray( $aSubMenuPage ); } public function _replyToBuildMenu() { if ( $this->oProp->aRootMenu['fCreateRoot'] ) $this->_registerRootMenuPage(); $this->oProp->aPages = $this->oUtil->addAndApplyFilter( $this, "pages_{$this->oProp->sClassName}", $this->oProp->aPages ); uasort( $this->oProp->aPages, array( $this, '_sortByOrder' ) ); foreach ( $this->oProp->aPages as $aPage ) { if ( ! isset( $aPage['page_slug'] ) ) continue; $this->oProp->sDefaultPageSlug = $aPage['page_slug']; break; } foreach ( $this->oProp->aPages as &$aSubMenuItem ) { $aSubMenuItem = $this->_formatSubMenuItemArray( $aSubMenuItem ); $aSubMenuItem['_page_hook'] = $this->_registerSubMenuItem( $aSubMenuItem ); } if ( $this->oProp->aRootMenu['fCreateRoot'] ) remove_submenu_page( $this->oProp->aRootMenu['sPageSlug'], $this->oProp->aRootMenu['sPageSlug'] ); } private function _registerRootMenuPage() { $this->oProp->aRootMenu['_page_hook'] = add_menu_page( $this->oProp->sClassName, $this->oProp->aRootMenu['sTitle'], $this->oProp->sCapability, $this->oProp->aRootMenu['sPageSlug'], '', $this->oProp->aRootMenu['sIcon16x16'], isset( $this->oProp->aRootMenu['iPosition'] ) ? $this->oProp->aRootMenu['iPosition'] : null ); } private function _formatSubMenuItemArray( $aSubMenuItem ) { if ( isset( $aSubMenuItem['page_slug'] ) ) return $this->_formatSubMenuPageArray( $aSubMenuItem ); if ( isset( $aSubMenuItem['href'] ) ) return $this->_formatSubmenuLinkArray( $aSubMenuItem ); return array(); } private function _formatSubmenuLinkArray( $aSubMenuLink ) { if ( ! filter_var( $aSubMenuLink['href'], FILTER_VALIDATE_URL ) ) return array(); return $this->oUtil->uniteArrays( array( 'capability' => isset( $aSubMenuLink['capability'] ) ? $aSubMenuLink['capability'] : $this->oProp->sCapability, 'order' => isset( $aSubMenuLink['order'] ) && is_numeric( $aSubMenuLink['order'] ) ? $aSubMenuLink['order'] : count( $this->oProp->aPages ) + 10, ), $aSubMenuLink + self::$_aStructure_SubMenuLinkForUser ); } private function _formatSubMenuPageArray( $aSubMenuPage ) { $aSubMenuPage = $aSubMenuPage + self::$_aStructure_SubMenuPageForUser; $aSubMenuPage['screen_icon_id'] = trim( $aSubMenuPage['screen_icon_id'] ); return $this->oUtil->uniteArrays( array( 'href_icon_32x32' => $this->oUtil->resolveSRC( $aSubMenuPage['screen_icon'], true ), 'screen_icon_id' => in_array( $aSubMenuPage['screen_icon'], self::$_aScreenIconIDs ) ? $aSubMenuPage['screen_icon'] : 'generic', 'capability' => isset( $aSubMenuPage['capability'] ) ? $aSubMenuPage['capability'] : $this->oProp->sCapability, 'order' => is_numeric( $aSubMenuPage['order'] ) ? $aSubMenuPage['order'] : count( $this->oProp->aPages ) + 10, ), $aSubMenuPage, array( 'show_page_title' => $this->oProp->bShowPageTitle, 'show_page_heading_tabs' => $this->oProp->bShowPageHeadingTabs, 'show_in_page_tabs' => $this->oProp->bShowInPageTabs, 'in_page_tab_tag' => $this->oProp->sInPageTabTag, 'page_heading_tab_tag' => $this->oProp->sPageHeadingTabTag, ) ); } private function _registerSubMenuItem( $aArgs ) { $sType = $aArgs['type']; $sTitle = $sType == 'page' ? $aArgs['title'] : $aArgs['title']; $sCapability = isset( $aArgs['capability'] ) ? $aArgs['capability'] : $this->oProp->sCapability; $_sPageHook = ''; if ( ! current_user_can( $sCapability ) ) return; $sRootPageSlug = $this->oProp->aRootMenu['sPageSlug']; $sMenuLabel = plugin_basename( $sRootPageSlug ); if ( $sType == 'page' && isset( $aArgs['page_slug'] ) ) { $sPageSlug = $aArgs['page_slug']; $_sPageHook = add_submenu_page( $sRootPageSlug, $sTitle, $sTitle, $sCapability, $sPageSlug, array( $this, $this->oProp->sClassHash . '_page_' . $sPageSlug ) ); add_action( "load-" . $_sPageHook , array( $this, "load_pre_" . $sPageSlug ) ); if ( ! $aArgs['show_in_menu'] ) { foreach( ( array ) $GLOBALS['submenu'][ $sMenuLabel ] as $iIndex => $aSubMenu ) { if ( ! isset( $aSubMenu[ 3 ] ) ) continue; if ( $aSubMenu[0] == $sTitle && $aSubMenu[3] == $sTitle && $aSubMenu[2] == $sPageSlug ) { unset( $GLOBALS['submenu'][ $sMenuLabel ][ $iIndex ] ); $this->oProp->aHiddenPages[ $sPageSlug ] = $sTitle; add_filter( 'admin_title', array( $this, '_replyToFixPageTitleForHiddenPages' ), 10, 2 ); break; } } } } if ( $sType == 'link' && $aArgs['show_in_menu'] ) { if ( ! isset( $GLOBALS['submenu'][ $sMenuLabel ] ) ) $GLOBALS['submenu'][ $sMenuLabel ] = array(); $GLOBALS['submenu'][ $sMenuLabel ][] = array ( $sTitle, $sCapability, $aArgs['href'], ); } return $_sPageHook; } public function _replyToFixPageTitleForHiddenPages( $sAdminTitle, $sPageTitle ) { if ( isset( $_GET['page'], $this->oProp->aHiddenPages[ $_GET['page'] ] ) ) return $this->oProp->aHiddenPages[ $_GET['page'] ] . $sAdminTitle; return $sAdminTitle; } } endif;if ( ! class_exists( 'AdminPageFramework_Setting' ) ) : abstract class AdminPageFramework_Setting extends AdminPageFramework_Menu { protected static $_aStructure_Section = array( 'section_id' => null, 'page_slug' => null, 'tab_slug' => null, 'title' => null, 'description' => null, 'capability' => null, 'if' => true, 'order' => null, 'help' => null, 'help_aside' => null, ); protected static $_aStructure_Field = array( 'field_id' => null, 'section_id' => null, 'type' => null, 'section_title' => null, 'page_slug' => null, 'tab_slug' => null, 'option_key' => null, 'class_name' => null, 'capability' => null, 'title' => null, 'tip' => null, 'description' => null, 'name' => null, 'error_message' => null, 'before_label' => null, 'after_label' => null, 'if' => true, 'order' => null, 'help' => null, 'help_aside' => null, 'repeatable' => null, 'sortable' => null, 'attributes' => null, '_field_type' => null, ); protected $aFieldErrors; function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ) { add_action( 'admin_menu', array( $this, '_replyToRegisterSettings' ), 100 ); add_action( 'admin_init', array( $this, '_replyToCheckRedirects' ) ); parent::__construct( $sOptionKey, $sCallerPath, $sCapability, $sTextDomain ); } public function setSettingNotice( $sMsg, $sType='error', $sID=null, $bOverride=true ) { $aWPSettingsErrors = isset( $GLOBALS['wp_settings_errors'] ) ? ( array ) $GLOBALS['wp_settings_errors'] : array(); $sID = isset( $sID ) ? $sID : $this->oProp->sOptionKey; foreach( $aWPSettingsErrors as $iIndex => $aSettingsError ) { if ( $aSettingsError['setting'] != $this->oProp->sOptionKey ) continue; if ( $aSettingsError['message'] == $sMsg ) return; if ( $aSettingsError['code'] === $sID ) { if ( ! $bOverride ) return; else unset( $aWPSettingsErrors[ $iIndex ] ); } } add_settings_error( $this->oProp->sOptionKey, $sID, $sMsg, $sType ); } public function addSettingSections( $aSection1, $aSection2=null, $_and_more=null ) { foreach( func_get_args() as $asSection ) $this->addSettingSection( $asSection ); } public function addSettingSection( $asSection ) { static $__sTargetPageSlug; static $__sTargetTabSlug; if ( ! is_array( $asSection ) ) { $__sTargetPageSlug = is_string( $asSection ) ? $asSection : $__sTargetPageSlug; return; } $aSection = $asSection; $__sTargetPageSlug = isset( $aSection['page_slug'] ) ? $aSection['page_slug'] : $__sTargetPageSlug; $__sTargetTabSlug = isset( $aSection['tab_slug'] ) ? $aSection['tab_slug'] : $__sTargetTabSlug; $aSection = $this->oUtil->uniteArrays( $aSection, self::$_aStructure_Section, array( 'page_slug' => $__sTargetPageSlug, 'tab_slug' => $__sTargetTabSlug ) ); if ( ! isset( $aSection['section_id'], $aSection['page_slug'] ) ) return; $aSection['section_id'] = $this->oUtil->sanitizeSlug( $aSection['section_id'] ); $aSection['page_slug'] = $this->oUtil->sanitizeSlug( $aSection['page_slug'] ); $aSection['tab_slug'] = $this->oUtil->sanitizeSlug( $aSection['tab_slug'] ); $this->oProp->aSections[ $aSection['section_id'] ] = $aSection; } public function removeSettingSections( $sSectionID1=null, $sSectionID2=null, $_and_more=null ) { foreach( func_get_args() as $sSectionID ) if ( isset( $this->oProp->aSections[ $sSectionID ] ) ) unset( $this->oProp->aSections[ $sSectionID ] ); } public function addSettingFields( $aField1, $aField2=null, $_and_more=null ) { foreach( func_get_args() as $aField ) $this->addSettingField( $aField ); } public function addSettingField( $asField ) { static $__sTargetSectionID; if ( ! is_array( $asField ) ) { $__sTargetSectionID = is_string( $asField ) ? $asField : $__sTargetSectionID; return; } $__sTargetSectionID = isset( $asField['section_id'] ) ? $asField['section_id'] : $__sTargetSectionID; $aField = $this->oUtil->uniteArrays( $asField, self::$_aStructure_Field, array( 'section_id' => $__sTargetSectionID ) ); if ( ! isset( $aField['field_id'], $aField['section_id'], $aField['type'] ) ) return; $aField['field_id'] = $this->oUtil->sanitizeSlug( $aField['field_id'] ); $aField['section_id'] = $this->oUtil->sanitizeSlug( $aField['section_id'] ); $this->oProp->aFields[ $aField['field_id'] ] = $aField; } public function removeSettingFields( $sFieldID1, $sFieldID2=null, $_and_more ) { foreach( func_get_args() as $sFieldID ) if ( isset( $this->oProp->aFields[ $sFieldID ] ) ) unset( $this->oProp->aFields[ $sFieldID ] ); } public function setFieldErrors( $aErrors, $sID=null, $nSavingDuration=300 ) { $sID = isset( $sID ) ? $sID : ( isset( $_POST['page_slug'] ) ? $_POST['page_slug'] : ( isset( $_GET['page'] ) ? $_GET['page'] : $this->oProp->sClassName ) ); set_transient( md5( $this->oProp->sClassName . '_' . $sID ), $aErrors, $nSavingDuration ); } public function getFieldValue( $sFieldID, $sSectionID='' ) { $_aOptions = $this->oUtil->uniteArrays( $this->oProp->aOptions, $this->oProp->getDefaultOptions() ); if ( ! $sSectionID ) { if ( array_key_exists( $sFieldID, $_aOptions ) ) return $_aOptions[ $sFieldID ]; foreach( $_aOptions as $aOptions ) { if ( array_key_exists( $sFieldID, $aOptions ) ) return $aOptions[ $sFieldID ]; } } if ( $sSectionID ) if ( array_key_exists( $sSectionID, $_aOptions ) && array_key_exists( $sFieldID, $_aOptions[ $sSectionID ] ) ) return $_aOptions[ $sSectionID ][ $sFieldID ]; return null; } protected function _doValidationCall( $sMethodName, $aInput ) { $sTabSlug = isset( $_POST['tab_slug'] ) ? $_POST['tab_slug'] : ''; $sPageSlug = isset( $_POST['page_slug'] ) ? $_POST['page_slug'] : ''; $sPressedFieldID = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'field_id' ) : ''; $sPressedInputID = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'input_id' ) : ''; $sPressedInputName = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'name' ) : ''; $bIsReset = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'is_reset' ) : ''; $sKeyToReset = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'reset_key' ) : ''; $sSubmitSectionID = isset( $_POST['__submit'] ) ? $this->_getPressedSubmitButtonData( $_POST['__submit'], 'section_id' ) : ''; $this->oUtil->addAndDoActions( $this, array( "submit_{$this->oProp->sClassName}_{$sPressedInputID}", $sSubmitSectionID ? "submit_{$this->oProp->sClassName}_{$sSubmitSectionID}_{$sPressedFieldID}" : "submit_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSubmitSectionID ? "submit_{$this->oProp->sClassName}_{$sSubmitSectionID}" : null, isset( $_POST['tab_slug'] ) ? "submit_{$this->oProp->sClassName}_{$sPageSlug}_{$sTabSlug}" : null, "submit_{$this->oProp->sClassName}_{sPageSlug}", "submit_{$this->oProp->sClassName}", ) ); if ( isset( $_POST['__import']['submit'], $_FILES['__import'] ) ) return $this->_importOptions( $this->oProp->aOptions, $sPageSlug, $sTabSlug ); if ( isset( $_POST['__export']['submit'] ) ) die( $this->_exportOptions( $this->oProp->aOptions, $sPageSlug, $sTabSlug ) ); if ( $bIsReset ) return $this->_askResetOptions( $sPressedInputName, $sPageSlug, $sSubmitSectionID ); if ( isset( $_POST['__submit'] ) && $sLinkURL = $this->_getPressedSubmitButtonData( $_POST['__submit'], 'link_url' ) ) die( wp_redirect( $sLinkURL ) ); if ( isset( $_POST['__submit'] ) && $sRedirectURL = $this->_getPressedSubmitButtonData( $_POST['__submit'], 'redirect_url' ) ) $this->_setRedirectTransients( $sRedirectURL ); $aInput = $this->_getFilteredOptions( $aInput, $sPageSlug, $sTabSlug ); if ( $sKeyToReset ) $aInput = $this->_resetOptions( $sKeyToReset, $aInput ); $bEmpty = empty( $aInput ); $this->setSettingNotice( $bEmpty ? $this->oMsg->__( 'option_cleared' ) : $this->oMsg->__( 'option_updated' ), $bEmpty ? 'error' : 'updated', $this->oProp->sOptionKey, false ); return $aInput; } private function _askResetOptions( $sPressedInputName, $sPageSlug, $sSectionID ) { $aNameKeys = explode( '|', $sPressedInputName ); $sFieldID = $sSectionID ? $aNameKeys[ 2 ] : $aNameKeys[ 1 ]; $aErrors = array(); if ( $sSectionID ) $aErrors[ $sSectionID ][ $sFieldID ] = $this->oMsg->__( 'reset_options' ); else $aErrors[ $sFieldID ] = $this->oMsg->__( 'reset_options' ); $this->setFieldErrors( $aErrors ); set_transient( md5( "reset_confirm_" . $sPressedInputName ), $sPressedInputName, 60*2 ); $this->setSettingNotice( $this->oMsg->__( 'confirm_perform_task' ) ); return $this->_getPageOptions( $this->oProp->aOptions, $sPageSlug ); } private function _resetOptions( $sKeyToReset, $aInput ) { if ( $sKeyToReset == 1 || $sKeyToReset === true ) { delete_option( $this->oProp->sOptionKey ); $this->setSettingNotice( $this->oMsg->__( 'option_been_reset' ) ); return array(); } unset( $this->oProp->aOptions[ trim( $sKeyToReset ) ], $aInput[ trim( $sKeyToReset ) ] ); update_option( $this->oProp->sOptionKey, $this->oProp->aOptions ); $this->setSettingNotice( $this->oMsg->__( 'specified_option_been_deleted' ) ); return $aInput; } private function _setRedirectTransients( $sURL ) { if ( empty( $sURL ) ) return; $sTransient = md5( trim( "redirect_{$this->oProp->sClassName}_{$_POST['page_slug']}" ) ); return set_transient( $sTransient, $sURL , 60*2 ); } private function _getPressedSubmitButtonData( $aPostElements, $sTargetKey='field_id' ) { foreach( $aPostElements as $sInputID => $aSubElements ) { $aNameKeys = explode( '|', $aSubElements[ 'name' ] ); if ( count( $aNameKeys ) == 2 && isset( $_POST[ $aNameKeys[0] ][ $aNameKeys[1] ] ) ) return $aSubElements[ $sTargetKey ]; if ( count( $aNameKeys ) == 3 && isset( $_POST[ $aNameKeys[0] ][ $aNameKeys[1] ][ $aNameKeys[2] ] ) ) return $aSubElements[ $sTargetKey ]; if ( count( $aNameKeys ) == 4 && isset( $_POST[ $aNameKeys[0] ][ $aNameKeys[1] ][ $aNameKeys[2] ][ $aNameKeys[3] ] ) ) return $aSubElements[ $sTargetKey ]; } return null; } private function _importOptions( $aStoredOptions, $sPageSlug, $sTabSlug ) { $oImport = new AdminPageFramework_ImportOptions( $_FILES['__import'], $_POST['__import'] ); $sSectionID = $oImport->getSiblingValue( 'section_id' ); $sPressedFieldID = $oImport->getSiblingValue( 'field_id' ); $sPressedInputID = $oImport->getSiblingValue( 'input_id' ); $bMerge = $oImport->getSiblingValue( 'is_merge' ); if ( $oImport->getError() > 0 ) { $this->setSettingNotice( $this->oMsg->__( 'import_error' ) ); return $aStoredOptions; } $aMIMEType = $this->oUtil->addAndApplyFilters( $this, array( "import_mime_types_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_mime_types_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_mime_types_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_mime_types_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_mime_types_{$sPageSlug}_{$sTabSlug}" : null, "import_mime_types_{$sPageSlug}", "import_mime_types_{$this->oProp->sClassName}" ), array( 'text/plain', 'application/octet-stream' ), $sPressedFieldID, $sPressedInputID ); $_sType = $oImport->getType(); if ( ! in_array( $oImport->getType(), $aMIMEType ) ) { $this->setSettingNotice( sprintf( $this->oMsg->__( 'uploaded_file_type_not_supported' ), $_sType ) ); return $aStoredOptions; } $vData = $oImport->getImportData(); if ( $vData === false ) { $this->setSettingNotice( $this->oMsg->__( 'could_not_load_importing_data' ) ); return $aStoredOptions; } $sFormatType = $this->oUtil->addAndApplyFilters( $this, array( "import_format_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_format_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_format_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_format_{$sPageSlug}_{$sTabSlug}" : null, "import_format_{$sPageSlug}", "import_format_{$this->oProp->sClassName}" ), $oImport->getFormatType(), $sPressedFieldID, $sPressedInputID ); $oImport->formatImportData( $vData, $sFormatType ); $sImportOptionKey = $this->oUtil->addAndApplyFilters( $this, array( "import_option_key_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_option_key_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_option_key_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_option_key_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_option_key_{$sPageSlug}_{$sTabSlug}" : null, "import_option_key_{$sPageSlug}", "import_option_key_{$this->oProp->sClassName}" ), $oImport->getSiblingValue( 'option_key' ), $sPressedFieldID, $sPressedInputID ); $vData = $this->oUtil->addAndApplyFilters( $this, array( "import_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "import_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "import_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "import_{$this->oProp->sClassName}_{$sSectionID}" : null, $sTabSlug ? "import_{$sPageSlug}_{$sTabSlug}" : null, "import_{$sPageSlug}", "import_{$this->oProp->sClassName}" ), $vData, $aStoredOptions, $sPressedFieldID, $sPressedInputID, $sFormatType, $sImportOptionKey, $bMerge ); $bEmpty = empty( $vData ); $this->setSettingNotice( $bEmpty ? $this->oMsg->__( 'not_imported_data' ) : $this->oMsg->__( 'imported_data' ), $bEmpty ? 'error' : 'updated', $this->oProp->sOptionKey, false ); if ( $sImportOptionKey != $this->oProp->sOptionKey ) { update_option( $sImportOptionKey, $vData ); return $aStoredOptions; } return $bMerge ? $this->oUtil->unitArrays( $vData, $aStoredOptions ) : $vData; } private function _exportOptions( $vData, $sPageSlug, $sTabSlug ) { $oExport = new AdminPageFramework_ExportOptions( $_POST['__export'], $this->oProp->sClassName ); $sSectionID = $oExport->getSiblingValue( 'section_id' ); $sPressedFieldID = $oExport->getSiblingValue( 'field_id' ); $sPressedInputID = $oExport->getSiblingValue( 'input_id' ); $vData = $oExport->getTransientIfSet( $vData ); $vData = $this->oUtil->addAndApplyFilters( $this, array( "export_{$this->oProp->sClassName}_{$sPressedInputID}", $sSectionID ? "export_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_{$sPageSlug}_{$sTabSlug}" : null, "export_{$sPageSlug}", "export_{$this->oProp->sClassName}" ), $vData, $sPressedFieldID, $sPressedInputID ); $sFileName = $this->oUtil->addAndApplyFilters( $this, array( "export_name_{$this->oProp->sClassName}_{$sPressedInputID}", "export_name_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "export_name_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_name_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_name_{$sPageSlug}_{$sTabSlug}" : null, "export_name_{$sPageSlug}", "export_name_{$this->oProp->sClassName}" ), $oExport->getFileName(), $sPressedFieldID, $sPressedInputID ); $sFormatType = $this->oUtil->addAndApplyFilters( $this, array( "export_format_{$this->oProp->sClassName}_{$sPressedInputID}", "export_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sSectionID ? "export_format_{$this->oProp->sClassName}_{$sSectionID}_{$sPressedFieldID}" : "export_format_{$this->oProp->sClassName}_{$sPressedFieldID}", $sTabSlug ? "export_format_{$sPageSlug}_{$sTabSlug}" : null, "export_format_{$sPageSlug}", "export_format_{$this->oProp->sClassName}" ), $oExport->getFormat(), $sPressedFieldID, $sPressedInputID ); $oExport->doExport( $vData, $sFileName, $sFormatType ); exit; } private function _getFilteredOptions( $aInput, $sPageSlug, $sTabSlug ) { $_aDefaultOptions = $this->oProp->getDefaultOptions(); $_aOptions = $this->oUtil->uniteArrays( $this->oProp->aOptions, $_aDefaultOptions ); $_aInput = $aInput; $aInput = $this->oUtil->uniteArrays( $aInput, $_aDefaultOptions ); foreach( $_aInput as $sID => $aSectionOrFields ) { if ( $this->_isSection( $sID ) ) { foreach( $aSectionOrFields as $sFieldID => $aFields ) $aInput[ $sID ][ $sFieldID ] = $this->oUtil->addAndApplyFilter( $this, "validation_{$this->oProp->sClassName}_{$sID}_{$sFieldID}", $aInput[ $sID ][ $sFieldID ], isset( $_aOptions[ $sID ][ $sFieldID ] ) ? $_aOptions[ $sID ][ $sFieldID ] : null ); } $aInput[ $sID ] = $this->oUtil->addAndApplyFilter( $this, "validation_{$this->oProp->sClassName}_{$sID}", $aInput[ $sID ], isset( $_aOptions[ $sID ] ) ? $_aOptions[ $sID ] : null ); } if ( $sTabSlug && $sPageSlug ) { $aInput = $this->oUtil->addAndApplyFilter( $this, "validation_{$sPageSlug}_{$sTabSlug}", $aInput, $this->_getTabOptions( $_aOptions, $sPageSlug, $sTabSlug ) ); $aInput = $this->oUtil->uniteArrays( $aInput, $this->_getOtherTabOptions( $_aOptions, $sPageSlug, $sTabSlug ) ); } if ( $sPageSlug ) { $aInput = $this->oUtil->addAndApplyFilter( $this, "validation_{$sPageSlug}", $aInput, $this->_getPageOptions( $_aOptions, $sPageSlug ) ); $aInput = $this->oUtil->uniteArrays( $aInput, $this->_getOtherPageOptions( $_aOptions, $sPageSlug ) ); } $aInput = $this->oUtil->addAndApplyFilter( $this, "validation_{$this->oProp->sClassName}", $aInput, $_aOptions ); return $aInput; } private function _isSection( $sID ) { if ( ! array_key_exists( $sID, $this->oProp->aSections ) ) return false; if ( ! array_key_exists( $sID, $this->oProp->aFields ) ) return true; if ( isset( $this->oProp->aFields[ $sID ]['section_id'] ) && $this->oProp->aFields[ $sID ]['section_id'] ) return $this->oProp->aFields[ $sID ]['section_id'] == $sID ? true : false; return false; } private function _getTabOptions( $aOptions, $sPageSlug, $sTabSlug='' ) { $_aStoredOptionsOfTheTab = array(); if ( ! $sTabSlug ) return $_aStoredOptionsOfTheTab; foreach( $this->oProp->aFields as $_aField ) { if ( ! isset( $_aField['page_slug'], $_aField['tab_slug'] ) ) continue; if ( $_aField['page_slug'] != $sPageSlug ) continue; if ( $_aField['tab_slug'] != $sTabSlug ) continue; if ( isset( $_aField['section_id'] ) && $_aField['section_id'] ) { if ( array_key_exists( $_aField['section_id'], $aOptions ) ) $_aStoredOptionsOfTheTab[ $_aField['section_id'] ] = $aOptions[ $_aField['section_id'] ]; continue; } if ( array_key_exists( $_aField['field_id'], $aOptions ) ) $_aStoredOptionsOfTheTab[ $_aField['field_id'] ] = $aOptions[ $_aField['field_id'] ]; } return $_aStoredOptionsOfTheTab; } private function _getPageOptions( $aOptions, $sPageSlug ) { $_aStoredOptionsOfThePage = array(); foreach( $this->oProp->aFields as $_aField ) { if ( ! isset( $_aField['page_slug'] ) || $_aField['page_slug'] != $sPageSlug ) continue; if ( isset( $_aField['section_id'] ) && $_aField['section_id'] ) { if ( array_key_exists( $_aField['section_id'], $aOptions ) ) $_aStoredOptionsOfThePage[ $_aField['section_id'] ] = $aOptions[ $_aField['section_id'] ]; continue; } if ( array_key_exists( $_aField['field_id'], $aOptions ) ) $_aStoredOptionsOfThePage[ $_aField['field_id'] ] = $aOptions[ $_aField['field_id'] ]; } return $_aStoredOptionsOfThePage; } private function _getOtherTabOptions( $aOptions, $sPageSlug, $sTabSlug ) { $_aStoredOptionsNotOfTheTab = array(); foreach( $this->oProp->aFields as $_aField ) { if ( ! isset( $_aField['page_slug'], $_aField['tab_slug'] ) ) continue; if ( $_aField['page_slug'] != $sPageSlug ) continue; if ( $_aField['tab_slug'] == $sTabSlug ) continue; if ( isset( $_aField['section_id'] ) && $_aField['section_id'] ) { if ( array_key_exists( $_aField['section_id'], $aOptions ) ) $_aStoredOptionsNotOfTheTab[ $_aField['section_id'] ] = $aOptions[ $_aField['section_id'] ]; continue; } if ( array_key_exists( $_aField['field_id'], $aOptions ) ) $_aStoredOptionsNotOfTheTab[ $_aField['field_id'] ] = $aOptions[ $_aField['field_id'] ]; } return $_aStoredOptionsNotOfTheTab; } private function _getOtherPageOptions( $aOptions, $sPageSlug ) { $_aStoredOptionsNotOfThePage = array(); foreach( $this->oProp->aFields as $_aField ) { if ( ! isset( $_aField['page_slug'] ) ) continue; if ( $_aField['page_slug'] == $sPageSlug ) continue; if ( isset( $_aField['section_id'] ) && $_aField['section_id'] ) { if ( array_key_exists( $_aField['section_id'], $aOptions ) ) $_aStoredOptionsNotOfThePage[ $_aField['section_id'] ] = $aOptions[ $_aField['section_id'] ]; continue; } if ( array_key_exists( $_aField['field_id'], $aOptions ) ) $_aStoredOptionsNotOfThePage[ $_aField['field_id'] ] = $aOptions[ $_aField['field_id'] ]; } return $_aStoredOptionsNotOfThePage; } protected function _renderSettingField( $sFieldID, $sPageSlug ) { if ( ! isset( $this->oProp->aFields[ $sFieldID ] ) ) return; $aField = $this->oProp->aFields[ $sFieldID ]; $this->aFieldErrors = isset( $this->aFieldErrors ) ? $this->aFieldErrors : $this->_getFieldErrors( $sPageSlug ); $sFieldType = isset( $this->oProp->aFieldTypeDefinitions[ $aField['type'] ]['hfRenderField'] ) && is_callable( $this->oProp->aFieldTypeDefinitions[ $aField['type'] ]['hfRenderField'] ) ? $aField['type'] : 'default'; $oField = new AdminPageFramework_InputField( $aField, $this->oProp->aOptions, $this->aFieldErrors, $this->oProp->aFieldTypeDefinitions, $this->oMsg ); $sFieldOutput = $oField->_getInputFieldOutput(); unset( $oField ); echo $this->oUtil->addAndApplyFilters( $this, array( isset( $aField['section_id'] ) ? 'field_' . $this->oProp->sClassName . '_' . $aField['section_id'] . '_' . $sFieldID : 'field_' . $this->oProp->sClassName . '_' . $sFieldID, ), $sFieldOutput, $aField ); } private function _getFieldErrors( $sPageSlug, $bDelete=true ) { if ( ! isset( $_GET['settings-updated'] ) ) return null; $sTransient = md5( $this->oProp->sClassName . '_' . $sPageSlug ); $aFieldErrors = get_transient( $sTransient ); if ( $bDelete ) delete_transient( $sTransient ); return $aFieldErrors; } protected function _renderSectionDescription( $sMethodName ) { $sSectionID = substr( $sMethodName, strlen( 'section_pre_' ) ); if ( ! isset( $this->oProp->aSections[ $sSectionID ] ) ) return; echo $this->oUtil->addAndApplyFilters( $this, array( 'section_' . $this->oProp->sClassName . '_' . $sSectionID ), '<p>' . $this->oProp->aSections[ $sSectionID ]['description'] . '</p>', $this->oProp->aSections[ $sSectionID ]['description'] ); } public function _replyToCheckRedirects() { if ( ! ( isset( $_GET['page'] ) ) || ! $this->oProp->isPageAdded( $_GET['page'] ) ) return; if ( ! ( isset( $_GET['settings-updated'] ) && ! empty( $_GET['settings-updated'] ) ) ) return; $aError = $this->_getFieldErrors( $_GET['page'], false ); if ( ! empty( $aError ) ) return; $sTransient = md5( trim( "redirect_{$this->oProp->sClassName}_{$_GET['page']}" ) ); $sURL = get_transient( $sTransient ); if ( $sURL === false ) return; delete_transient( $sTransient ); die( wp_redirect( $sURL ) ); } public function _replyToRegisterSettings() { $this->_formatSectionArrays( $this->oProp->aSections ); $this->_formatFieldArrays( $this->oProp->aFields, $this->oProp->aSections ); $_aSections = $this->_applyConditionsForSections( $this->oProp->aSections ); $_aFields = $this->_applyConditionsForFields( $this->oProp->aFields, $_aSections ); if ( $GLOBALS['pagenow'] != 'options.php' && ( count( $_aSections ) == 0 || count( $_aFields ) == 0 ) ) return; new AdminPageFramework_FieldTypeRegistration( $this->oProp->aFieldTypeDefinitions, $this->oProp->sClassName, $this->oMsg ); $this->oProp->aFieldTypeDefinitions = $this->oUtil->addAndApplyFilter( $this, 'field_types_' . $this->oProp->sClassName, $this->oProp->aFieldTypeDefinitions ); uasort( $_aSections, array( $this, '_sortByOrder' ) ); foreach( $_aSections as $aSection ) { add_settings_section( $aSection['section_id'], "<a id='{$aSection['section_id']}'></a>" . $aSection['title'], array( $this, 'section_pre_' . $aSection['section_id'] ), $aSection['page_slug'] ); if ( ! empty( $aSection['help'] ) ) $this->addHelpTab( array( 'page_slug' => $aSection['page_slug'], 'page_tab_slug' => $aSection['tab_slug'], 'help_tab_title' => $aSection['title'], 'help_tab_id' => $aSection['section_id'], 'help_tab_content' => $aSection['help'], 'help_tab_sidebar_content' => $aSection['help_aside'] ? $aSection['help_aside'] : "", ) ); } uasort( $_aFields, array( $this, '_sortByOrder' ) ); foreach( $_aFields as $aField ) { add_settings_field( $aField['section_id'] . '_' . $aField['field_id'], "<a id='{$aField['section_id']}_{$aField['field_id']}'></a><span title='{$aField['tip']}'>{$aField['title']}</span>", array( $this, 'field_pre_' . $aField['field_id'] ), $this->_getPageSlugBySectionID( $aField['section_id'] ), $aField['section_id'], $aField['field_id'] ); AdminPageFramework_FieldTypeRegistration::_setFieldHeadTagElements( $aField, $this->oProp, $this->oHeadTag ); if ( ! empty( $aField['help'] ) ) $this->addHelpTab( array( 'page_slug' => $aField['page_slug'], 'page_tab_slug' => $aField['tab_slug'], 'help_tab_title' => $aField['section_title'], 'help_tab_id' => $aField['section_id'], 'help_tab_content' => "<span class='contextual-help-tab-title'>" . $aField['title'] . "</span> - " . PHP_EOL . $aField['help'], 'help_tab_sidebar_content' => $aField['help_aside'] ? $aField['help_aside'] : "", ) ); } $this->oProp->bEnableForm = true; register_setting( $this->oProp->sOptionKey, $this->oProp->sOptionKey, array( $this, 'validation_pre_' . $this->oProp->sClassName ) ); } private function _getPageSlugBySectionID( $sSectionID ) { return isset( $this->oProp->aSections[ $sSectionID ]['page_slug'] ) ? $this->oProp->aSections[ $sSectionID ]['page_slug'] : null; } private function _formatSectionArrays( &$aSections ) { $aSections = $this->oUtil->addAndApplyFilter( $this, "sections_{$this->oProp->sClassName}", $aSections ); $_aNewSectionArray = array(); foreach( $aSections as $aSection ) { if ( ! is_array( $aSection ) ) continue; $aSection = $aSection + self::$_aStructure_Section; $aSection['section_id'] = $this->oUtil->sanitizeSlug( $aSection['section_id'] ); $aSection['page_slug'] = $this->oUtil->sanitizeSlug( $aSection['page_slug'] ); $aSection['tab_slug'] = $this->oUtil->sanitizeSlug( $aSection['tab_slug'] ); if ( ! isset( $aSection['section_id'], $aSection['page_slug'] ) ) continue; $aSection['order'] = is_numeric( $aSection['order'] ) ? $aSection['order'] : count( $_aNewSectionArray ) + 10; $_aNewSectionArray[ $aSection['section_id'] ] = $aSection; } $aSections = $_aNewSectionArray; } private function _applyConditionsForSections( $aSections ) { $_sCurrentPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : null; $_aNewSectionArray = array(); foreach( $aSections as $_aSection ) { if ( $GLOBALS['pagenow'] != 'options.php' && ! $_sCurrentPageSlug || $_sCurrentPageSlug != $_aSection['page_slug'] ) continue; if ( ! $this->_isSettingSectionOfCurrentTab( $_aSection ) ) continue; $_aSection['capability'] = isset( $_aSection['capability'] ) ? $_aSection['capability'] : $this->oProp->sCapability; if ( ! current_user_can( $_aSection['capability'] ) ) continue; if ( $_aSection['if'] !== true ) continue; $_aNewSectionArray[ $_aSection['section_id'] ] = $_aSection; } return $_aNewSectionArray; } private function _isSettingSectionOfCurrentTab( $aSection ) { if ( ! isset( $aSection['tab_slug'] ) ) return true; $sCurrentTab = isset( $_GET['tab'] ) ? $_GET['tab'] : null; if ( $aSection['tab_slug'] == $sCurrentTab ) return true; $sPageSlug = $aSection['page_slug']; if ( ! isset( $_GET['tab'] ) && isset( $this->oProp->aInPageTabs[ $sPageSlug ] ) ) { $sDefaultTabSlug = isset( $this->oProp->aDefaultInPageTabs[ $sPageSlug ] ) ? $this->oProp->aDefaultInPageTabs[ $sPageSlug ] : ''; if ( $sDefaultTabSlug == $aSection['tab_slug'] ) return true; } return false; } private function _formatFieldArrays( &$aFields, &$aSections ) { $aFields = $this->oUtil->addAndApplyFilter( $this, "fields_{$this->oProp->sClassName}", $aFields ); $_aNewFieldArrays = array(); foreach( $aFields as $_aField ) { if ( ! is_array( $_aField ) ) continue; $_aField = array( '_field_type' => 'page' ) + $_aField + self::$_aStructure_Field; $_aField['field_id'] = $this->oUtil->sanitizeSlug( $_aField['field_id'] ); $_aField['section_id'] = $this->oUtil->sanitizeSlug( $_aField['section_id'] ); if ( ! isset( $_aField['field_id'], $_aField['section_id'], $_aField['type'] ) ) continue; $_aField['order'] = is_numeric( $_aField['order'] ) ? $_aField['order'] : count( $_aNewFieldArrays ) + 10; $_aField['tip'] = strip_tags( isset( $_aField['tip'] ) ? $_aField['tip'] : $_aField['description'] ); $_aField['option_key'] = $this->oProp->sOptionKey; $_aField['class_name'] = $this->oProp->sClassName; $_aField['page_slug'] = isset( $aSections[ $_aField['section_id'] ]['page_slug'] ) ? $aSections[ $_aField['section_id'] ]['page_slug'] : null; $_aField['tab_slug'] = isset( $aSections[ $_aField['section_id'] ]['tab_slug'] ) ? $aSections[ $_aField['section_id'] ]['tab_slug'] : null; $_aField['section_title'] = isset( $aSections[ $_aField['section_id'] ]['title'] ) ? $aSections[ $_aField['section_id'] ]['title'] : null; $_aNewFieldArrays[ $_aField['field_id'] ] = $_aField; } $aFields = $_aNewFieldArrays; } private function _applyConditionsForFields( $aFields, $aSections ) { $_aNewFieldArrays = array(); foreach( $aFields as $_aField ) { if ( ! isset( $aSections[ $_aField['section_id'] ] ) ) continue; $_aField['capability'] = isset( $_aField['capability'] ) ? $_aField['capability'] : $this->oProp->sCapability; if ( ! current_user_can( $_aField['capability'] ) ) continue; if ( $_aField['if'] !== true ) continue; $_aNewFieldArrays[ $_aField['field_id'] ] = $_aField; } return $_aNewFieldArrays; } } endif;if ( ! class_exists( 'AdminPageFramework' ) ) : abstract class AdminPageFramework extends AdminPageFramework_Setting { public function __construct( $sOptionKey=null, $sCallerPath=null, $sCapability=null, $sTextDomain='admin-page-framework' ){ parent::__construct( $sOptionKey, $sCallerPath ? $sCallerPath : AdminPageFramework_Utility::getCallerScriptPath( __FILE__ ), $sCapability, $sTextDomain ); $this->oUtil->addAndDoAction( $this, 'start_' . $this->oProp->sClassName ); } public function setUp() {} public function addHelpTab( $aHelpTab ) { $this->oHelpPane->_addHelpTab( $aHelpTab ); } public function enqueueStyles( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyles( $aSRCs, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueStyle( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyle( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueScripts( $aSRCs, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScripts( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function enqueueScript( $sSRC, $sPageSlug='', $sTabSlug='', $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScript( $sSRC, $sPageSlug, $sTabSlug, $aCustomArgs ); } public function addLinkToPluginDescription( $sTaggedLinkHTML1, $sTaggedLinkHTML2=null, $_and_more=null ) { $this->oLink->_addLinkToPluginDescription( func_get_args() ); } public function addLinkToPluginTitle( $sTaggedLinkHTML1, $sTaggedLinkHTML2=null, $_and_more=null ) { $this->oLink->_addLinkToPluginTitle( func_get_args() ); } public function setCapability( $sCapability ) { $this->oProp->sCapability = $sCapability; } public function setFooterInfoLeft( $sHTML, $bAppend=true ) { $this->oProp->aFooterInfo['sLeft'] = $bAppend ? $this->oProp->aFooterInfo['sLeft'] . PHP_EOL . $sHTML : $sHTML; } public function setFooterInfoRight( $sHTML, $bAppend=true ) { $this->oProp->aFooterInfo['sRight'] = $bAppend ? $this->oProp->aFooterInfo['sRight'] . PHP_EOL . $sHTML : $sHTML; } public function setAdminNotice( $sMessage, $sClassSelector='error', $sID='' ) { $sID = $sID ? $sID : md5( $sMessage ); $this->oProp->aAdminNotices[ md5( $sMessage ) ] = array( 'sMessage' => $sMessage, 'sClassSelector' => $sClassSelector, 'sID' => $sID, ); add_action( 'admin_notices', array( $this, '_replyToPrintAdminNotices' ) ); } public function _replyToPrintAdminNotices() { foreach( $this->oProp->aAdminNotices as $aAdminNotice ) echo "<div class='{$aAdminNotice['sClassSelector']}' id='{$aAdminNotice['sID']}' ><p>" . $aAdminNotice['sMessage'] . "</p></div>"; } public function setDisallowedQueryKeys( $asQueryKeys, $bAppend=true ) { if ( ! $bAppend ) { $this->oProp->aDisallowedQueryKeys = ( array ) $asQueryKeys; return; } $aNewQueryKeys = array_merge( ( array ) $asQueryKeys, $this->oProp->aDisallowedQueryKeys ); $aNewQueryKeys = array_filter( $aNewQueryKeys ); $aNewQueryKeys = array_unique( $aNewQueryKeys ); $this->oProp->aDisallowedQueryKeys = $aNewQueryKeys; } public function __call( $sMethodName, $aArgs=null ) { $sPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : null; $sTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->oProp->getDefaultInPageTab( $sPageSlug ); if ( substr( $sMethodName, 0, strlen( 'section_pre_' ) ) == 'section_pre_' ) return $this->_renderSectionDescription( $sMethodName ); if ( substr( $sMethodName, 0, strlen( 'field_pre_' ) ) == 'field_pre_' ) return $this->_renderSettingField( $aArgs[ 0 ], $sPageSlug ); if ( substr( $sMethodName, 0, strlen( 'validation_pre_' ) ) == 'validation_pre_' ) return $this->_doValidationCall( $sMethodName, $aArgs[ 0 ] ); if ( substr( $sMethodName, 0, strlen( 'load_pre_' ) ) == 'load_pre_' ) return $this->_doPageLoadCall( substr( $sMethodName, strlen( 'load_pre_' ) ), $sTabSlug, $aArgs[ 0 ] ); if ( $sMethodName == $this->oProp->sClassHash . '_page_' . $sPageSlug ) return $this->_renderPage( $sPageSlug, $sTabSlug ); if ( $this->_isFrameworkCallbackMethod( $sMethodName ) ) return isset( $aArgs[0] ) ? $aArgs[0] : null; } private function _isFrameworkCallbackMethod( $sMethodName ) { foreach( self::$_aHookPrefixes as $sPrefix ) if ( substr( $sMethodName, 0, strlen( $sPrefix ) ) == $sPrefix ) return true; return false; } protected function _doPageLoadCall( $sPageSlug, $sTabSlug, $aArg ) { $this->oUtil->addAndDoActions( $this, $this->oUtil->getFilterArrayByPrefix( "load_", $this->oProp->sClassName, $sPageSlug, $sTabSlug, true ) ); } } endif;if ( ! class_exists( 'AdminPageFramework_PostType' ) ) : abstract class AdminPageFramework_PostType { protected $oUtil; protected $oLink; public function __construct( $sPostType, $aArgs=array(), $sCallerPath=null, $sTextDomain='admin-page-framework' ) { $this->oUtil = new AdminPageFramework_WPUtility; $this->oProp = new AdminPageFramework_Property_PostType( $this, $sCallerPath ? trim( $sCallerPath ) : AdminPageFramework_Utility::getCallerScriptPath( __FILE__ ), get_class( $this ) ); $this->oMsg = AdminPageFramework_Message::instantiate( $sTextDomain ); $this->oHeadTag = new AdminPageFramework_HeadTag_PostType( $this->oProp ); $this->oPageLoadInfo = AdminPageFramework_PageLoadInfo_PostType::instantiate( $this->oProp, $this->oMsg ); $this->oDebug = new AdminPageFramework_Debug; $this->oProp->sPostType = $this->oUtil->sanitizeSlug( $sPostType ); $this->oProp->aPostTypeArgs = $aArgs; $this->oProp->aColumnHeaders = array( 'cb' => '<input type="checkbox" />', 'title' => $this->oMsg->__( 'title' ), 'author' => $this->oMsg->__( 'author' ), 'comments' => '<div class="comment-grey-bubble"></div>', 'date' => $this->oMsg->__( 'date' ), ); add_action( 'init', array( $this, '_replyToRegisterPostType' ), 999 ); if ( $this->oProp->sPostType != '' && $this->oProp->bIsAdmin ) { add_action( 'admin_enqueue_scripts', array( $this, '_replyToDisableAutoSave' ) ); add_filter( "manage_{$this->oProp->sPostType}_posts_columns", array( $this, '_replyToSetColumnHeader' ) ); add_filter( "manage_edit-{$this->oProp->sPostType}_sortable_columns", array( $this, '_replyToSetSortableColumns' ) ); add_action( "manage_{$this->oProp->sPostType}_posts_custom_column", array( $this, '_replyToSetColumnCell' ), 10, 2 ); add_action( 'restrict_manage_posts', array( $this, '_replyToAddAuthorTableFilter' ) ); add_action( 'restrict_manage_posts', array( $this, '_replyToAddTaxonomyTableFilter' ) ); add_filter( 'parse_query', array( $this, '_replyToSetTableFilterQuery' ) ); add_action( 'admin_head', array( $this, '_replyToAddStyle' ) ); $this->oLink = new AdminPageFramework_Link_PostType( $this->oProp, $this->oMsg ); add_action( 'wp_loaded', array( $this, 'setUp' ) ); } $this->oUtil->addAndDoAction( $this, "start_{$this->oProp->sClassName}" ); } public function setUp() {} public function enqueueStyles( $aSRCs, $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyles( $aSRCs, array( $this->oProp->sPostType ), $aCustomArgs ); } public function enqueueStyle( $sSRC, $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueStyle( $sSRC, array( $this->oProp->sPostType ), $aCustomArgs ); } public function enqueueScripts( $aSRCs, $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScripts( $aSRCs, array( $this->oProp->sPostType ), $aCustomArgs ); } public function enqueueScript( $sSRC, $aCustomArgs=array() ) { return $this->oHeadTag->_enqueueScript( $sSRC, array( $this->oProp->sPostType ), $aCustomArgs ); } protected function setAutoSave( $bEnableAutoSave=True ) { $this->oProp->bEnableAutoSave = $bEnableAutoSave; } protected function addTaxonomy( $sTaxonomySlug, $aArgs ) { $sTaxonomySlug = $this->oUtil->sanitizeSlug( $sTaxonomySlug ); $this->oProp->aTaxonomies[ $sTaxonomySlug ] = $aArgs; if ( isset( $aArgs['show_table_filter'] ) && $aArgs['show_table_filter'] ) $this->oProp->aTaxonomyTableFilters[] = $sTaxonomySlug; if ( isset( $aArgs['show_in_sidebar_menus'] ) && ! $aArgs['show_in_sidebar_menus'] ) $this->oProp->aTaxonomyRemoveSubmenuPages[ "edit-tags.php?taxonomy={$sTaxonomySlug}&amp;post_type={$this->oProp->sPostType}" ] = "edit.php?post_type={$this->oProp->sPostType}"; if ( count( $this->oProp->aTaxonomyTableFilters ) == 1 ) add_action( 'init', array( $this, '_replyToRegisterTaxonomies' ) ); if ( count( $this->oProp->aTaxonomyRemoveSubmenuPages ) == 1 ) add_action( 'admin_menu', array( $this, '_replyToRemoveTexonomySubmenuPages' ), 999 ); } protected function setAuthorTableFilter( $bEnableAuthorTableFileter=false ) { $this->oProp->bEnableAuthorTableFileter = $bEnableAuthorTableFileter; } protected function setPostTypeArgs( $aArgs ) { $this->oProp->aPostTypeArgs = $aArgs; } protected function setFooterInfoLeft( $sHTML, $bAppend=true ) { if ( isset( $this->oLink ) ) $this->oLink->aFooterInfo['sLeft'] = $bAppend ? $this->oLink->aFooterInfo['sLeft'] . $sHTML : $sHTML; } protected function setFooterInfoRight( $sHTML, $bAppend=true ) { if ( isset( $this->oLink ) ) $this->oLink->aFooterInfo['sRight'] = $bAppend ? $this->oLink->aFooterInfo['sRight'] . $sHTML : $sHTML; } private function getStylesForPostTypeScreenIcon( $sSRC ) { $sNone = 'none'; $sSRC = $this->oUtil->resolveSRC( $sSRC ); return "#post-body-content {
				margin-bottom: 10px;
			}
			#edit-slug-box {
				display: {$sNone};
			}
			#icon-edit.icon32.icon32-posts-" . $this->oProp->sPostType . " {
				background: url('" . $sSRC . "') no-repeat;
				background-size: 32px 32px;
			}			
		"; } public function _replyToSetColumnHeader( $aHeaderColumns ) { return $this->oUtil->addAndApplyFilter( $this, "columns_{$this->oProp->sPostType}", $aHeaderColumns ); } public function _replyToSetSortableColumns( $aColumns ) { return $this->oUtil->addAndApplyFilter( $this, "sortable_columns_{$this->oProp->sPostType}", $aColumns ); } public function _replyToAddStyle() { if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != $this->oProp->sPostType ) return; if ( isset( $this->oProp->aPostTypeArgs['screen_icon'] ) && $this->oProp->aPostTypeArgs['screen_icon'] ) $this->oProp->sStyle .= $this->getStylesForPostTypeScreenIcon( $this->oProp->aPostTypeArgs['screen_icon'] ); $this->oProp->sStyle = $this->oUtil->addAndApplyFilters( $this, "style_{$this->oProp->sClassName}", $this->oProp->sStyle ); if ( ! empty( $this->oProp->sStyle ) ) echo "<style type='text/css' id='admin-page-framework-style-post-type'>" . $this->oProp->sStyle . "</style>"; } public function _replyToRegisterPostType() { register_post_type( $this->oProp->sPostType, $this->oProp->aPostTypeArgs ); $bIsPostTypeSet = get_option( "post_type_rules_flased_{$this->oProp->sPostType}" ); if ( $bIsPostTypeSet !== true ) { flush_rewrite_rules( false ); update_option( "post_type_rules_flased_{$this->oProp->sPostType}", true ); } } public function _replyToRegisterTaxonomies() { foreach( $this->oProp->aTaxonomies as $sTaxonomySlug => $aArgs ) register_taxonomy( $sTaxonomySlug, $this->oProp->sPostType, $aArgs ); } public function _replyToRemoveTexonomySubmenuPages() { foreach( $this->oProp->aTaxonomyRemoveSubmenuPages as $sSubmenuPageSlug => $sTopLevelPageSlug ) remove_submenu_page( $sTopLevelPageSlug, $sSubmenuPageSlug ); } public function _replyToDisableAutoSave() { if ( $this->oProp->bEnableAutoSave ) return; if ( $this->oProp->sPostType != get_post_type() ) return; wp_dequeue_script( 'autosave' ); } public function _replyToAddAuthorTableFilter() { if ( ! $this->oProp->bEnableAuthorTableFileter ) return; if ( ! ( isset( $_GET['post_type'] ) && post_type_exists( $_GET['post_type'] ) && in_array( strtolower( $_GET['post_type'] ), array( $this->oProp->sPostType ) ) ) ) return; wp_dropdown_users( array( 'show_option_all' => 'Show all Authors', 'show_option_none' => false, 'name' => 'author', 'selected' => ! empty( $_GET['author'] ) ? $_GET['author'] : 0, 'include_selected' => false )); } public function _replyToAddTaxonomyTableFilter() { if ( $GLOBALS['typenow'] != $this->oProp->sPostType ) return; $oPostCount = wp_count_posts( $this->oProp->sPostType ); if ( $oPostCount->publish + $oPostCount->future + $oPostCount->draft + $oPostCount->pending + $oPostCount->private + $oPostCount->trash == 0 ) return; foreach ( get_object_taxonomies( $GLOBALS['typenow'] ) as $sTaxonomySulg ) { if ( ! in_array( $sTaxonomySulg, $this->oProp->aTaxonomyTableFilters ) ) continue; $oTaxonomy = get_taxonomy( $sTaxonomySulg ); if ( wp_count_terms( $oTaxonomy->name ) == 0 ) continue; wp_dropdown_categories( array( 'show_option_all' => $this->oMsg->__( 'show_all' ) . ' ' . $oTaxonomy->label, 'taxonomy' => $sTaxonomySulg, 'name' => $oTaxonomy->name, 'orderby' => 'name', 'selected' => intval( isset( $_GET[ $sTaxonomySulg ] ) ), 'hierarchical' => $oTaxonomy->hierarchical, 'show_count' => true, 'hide_empty' => false, 'hide_if_empty' => false, 'echo' => true, ) ); } } public function _replyToSetTableFilterQuery( $oQuery=null ) { if ( 'edit.php' != $GLOBALS['pagenow'] ) return $oQuery; if ( ! isset( $GLOBALS['typenow'] ) ) return $oQuery; foreach ( get_object_taxonomies( $GLOBALS['typenow'] ) as $sTaxonomySlug ) { if ( ! in_array( $sTaxonomySlug, $this->oProp->aTaxonomyTableFilters ) ) continue; $sVar = &$oQuery->query_vars[ $sTaxonomySlug ]; if ( ! isset( $sVar ) ) continue; $oTerm = get_term_by( 'id', $sVar, $sTaxonomySlug ); if ( is_object( $oTerm ) ) $sVar = $oTerm->slug; } return $oQuery; } public function _replyToSetColumnCell( $sColumnTitle, $iPostID ) { echo $this->oUtil->addAndApplyFilter( $this, "cell_{$this->oProp->sPostType}_{$sColumnTitle}", $sCell='', $iPostID ); } public function __call( $sMethodName, $aArgs=null ) { if ( substr( $sMethodName, 0, strlen( "cell_" ) ) == "cell_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "sortable_columns_" ) ) == "sortable_columns_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "columns_" ) ) == "columns_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "style_ie_common_" ) )== "style_ie_common_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "style_common_" ) )== "style_common_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "style_ie_" ) )== "style_ie_" ) return $aArgs[0]; if ( substr( $sMethodName, 0, strlen( "style_" ) )== "style_" ) return $aArgs[0]; } } endif;if ( ! class_exists( 'AdminPageFramework_Message' ) ) : class AdminPageFramework_Message { public $aMessages = array(); private static $_oInstance; protected $_sTextDomain = 'admin-page-framework'; public static function instantiate( $sTextDomain='admin-page-framework' ) { if ( ! isset( self::$_oInstance ) && ! ( self::$_oInstance instanceof AdminPageFramework_Message ) ) self::$_oInstance = new AdminPageFramework_Message( $sTextDomain ); return self::$_oInstance; } public function __construct( $sTextDomain='admin-page-framework' ) { $this->_sTextDomain = $sTextDomain; $this->aMessages = array( 'option_updated' => __( 'The options have been updated.', 'admin-page-framework' ), 'option_cleared' => __( 'The options have been cleared.', 'admin-page-framework' ), 'export' => __( 'Export', 'admin-page-framework' ), 'export_options' => __( 'Export Options', 'admin-page-framework' ), 'import_options' => __( 'Import', 'admin-page-framework' ), 'import_options' => __( 'Import Options', 'admin-page-framework' ), 'submit' => __( 'Submit', 'admin-page-framework' ), 'import_error' => __( 'An error occurred while uploading the import file.', 'admin-page-framework' ), 'uploaded_file_type_not_supported' => __( 'The uploaded file type is not supported: %1$s', 'admin-page-framework' ), 'could_not_load_importing_data' => __( 'Could not load the importing data.', 'admin-page-framework' ), 'imported_data' => __( 'The uploaded file has been imported.', 'admin-page-framework' ), 'not_imported_data' => __( 'No data could be imported.', 'admin-page-framework' ), 'add' => __( 'Add', 'admin-page-framework' ), 'remove' => __( 'Remove', 'admin-page-framework' ), 'upload_image' => __( 'Upload Image', 'admin-page-framework' ), 'use_this_image' => __( 'Use This Image', 'admin-page-framework' ), 'reset_options' => __( 'Are you sure you want to reset the options?', 'admin-page-framework' ), 'confirm_perform_task' => __( 'Please confirm if you want to perform the specified task.', 'admin-page-framework' ), 'option_been_reset' => __( 'The options have been reset.', 'admin-page-framework' ), 'specified_option_been_deleted' => __( 'The specified options have been deleted.', 'admin-page-framework' ), 'title' => __( 'Title', 'admin-page-framework' ), 'author' => __( 'Author', 'admin-page-framework' ), 'categories' => __( 'Categories', 'admin-page-framework' ), 'tags' => __( 'Tags', 'admin-page-framework' ), 'comments' => __( 'Comments', 'admin-page-framework' ), 'date' => __( 'Date', 'admin-page-framework' ), 'show_all' => __( 'Show All', 'admin-page-framework' ), 'powered_by' => __( 'Powered by', 'admin-page-framework' ), 'settings' => __( 'Settings', 'admin-page-framework' ), 'manage' => __( 'Manage', 'admin-page-framework' ), 'select_image' => __( 'Select Image', 'admin-page-framework' ), 'upload_file' => __( 'Upload File', 'admin-page-framework' ), 'use_this_file' => __( 'Use This File', 'admin-page-framework' ), 'select_file' => __( 'Select File', 'admin-page-framework' ), 'queries_in_seconds' => __( '%s queries in %s seconds.', 'admin-page-framework' ), 'out_of_x_memory_used' => __( '%s out of %s MB (%s) memory used.', 'admin-page-framework' ), 'peak_memory_usage' => __( 'Peak memory usage %s MB.', 'admin-page-framework' ), 'initial_memory_usage' => __( 'Initial memory usage  %s MB.', 'admin-page-framework' ), 'allowed_maximum_number_of_fields' => __( 'The allowed maximum number of fields is {0}.', 'admin-page-framework' ), 'allowed_minimum_number_of_fields' => __( 'The allowed minimum number of fields is {0}.', 'admin-page-framework' ), ); } public function __( $sKey ) { return isset( $this->aMessages[ $sKey ] ) ? __( $this->aMessages[ $sKey ], $this->_sTextDomain ) : ''; } public function _e( $sKey ) { if ( isset( $this->aMessages[ $sKey ] ) ) _e( $this->aMessages[ $sKey ], $this->_sTextDomain ); } } endif;if ( ! class_exists( 'AdminPageFramework_Property_Base' ) ) : abstract class AdminPageFramework_Property_Base { private static $_aStructure_CallerInfo = array( 'sPath' => null, 'sType' => null, 'sName' => null, 'sURI' => null, 'sVersion' => null, 'sThemeURI' => null, 'sScriptURI' => null, 'sAuthorURI' => null, 'sAuthor' => null, 'sDescription' => null, ); static public $_aLibraryData; protected $oCaller; public $sCallerPath; public $aScriptInfo; public $sClassName; public $sClassHash; public $sScript = ''; public $sStyle = ''; public $sStyleIE = ''; public $_bAddedStyle = false; public $_bAddedScript = false; public $aFieldTypeDefinitions = array(); public static $_sDefaultScript = ""; public static $_sDefaultStyle = "
		/* Settings Notice */
		.wrap div.updated, 
		.wrap div.settings-error { 
			clear: both; 
			margin-top: 16px;
		} 		
				
		/* Contextual Help Page */
		.contextual-help-description {
			clear: left;	
			display: block;
			margin: 1em 0;
		}
		.contextual-help-tab-title {
			font-weight: bold;
		}
		
		/* Page Meta Boxes */
		.admin-page-framework-content {
			margin-bottom: 1.48em;		
			display: inline-table;	/* Fixes the bottom margin gets placed at the top. */
			width: 100%;	/* This allows float:right elements to go to the very right end of the page. */
		}
		
		/* Heading - the meta box container element affects the styles of regular main content output. So it needs to be fixed. */
		#poststuff .admin-page-framework-content h3 {
			font-weight: bold;
			font-size: 1.3em;
			margin: 1em 0;
			padding: 0;
			font-family: 'Open Sans', sans-serif;
		}
		
		/* Form Elements */
		/* Fields Container */
		.admin-page-framework-fields {
			display: table;	/* the block property does not give the element the solid height */
			width: 100%;
		}
		
		/* Disabled */
		.admin-page-framework-fields .disabled,
		.admin-page-framework-fields .disabled input,
		.admin-page-framework-fields .disabled textarea,
		.admin-page-framework-fields .disabled select,
		.admin-page-framework-fields .disabled option {
			color: #BBB;
		}
		
		/* HR */
		.admin-page-framework-fields hr {
			border: 0; 
			height: 0;
			border-top: 1px solid #dfdfdf; 
		}
		
		/* Delimiter */
		.admin-page-framework-fields .delimiter {
			display: inline;
		}
		
		/* Description */
		.admin-page-framework-fields-description {
			margin-bottom: 0;
		}
		/* Field Container */
		.admin-page-framework-field {
			float: left;
			clear: both;
			display: inline-block;
			margin: 1px 0;
		}
		.admin-page-framework-field label{
			display: inline-block;	/* for WordPress v3.7.x or below */
			width: 100%;
		}
		.admin-page-framework-field .admin-page-framework-input-label-container {
			margin-bottom: 0.25em;
		}
		@media only screen and ( max-width: 780px ) {	/* For WordPress v3.8 or greater */
			.admin-page-framework-field .admin-page-framework-input-label-container {
				margin-bottom: 0.5em;
			}
		}			
		
		.admin-page-framework-field .admin-page-framework-input-label-string {
			padding-right: 1em;	/* for checkbox label strings, a right padding is needed */
		}
		.admin-page-framework-field .admin-page-framework-input-button-container {
			padding-right: 1em; 
		}
		.admin-page-framework-field .admin-page-framework-input-container {
			display: inline-block;
			vertical-align: middle;
		}
		.admin-page-framework-field-image .admin-page-framework-input-label-container {			
			vertical-align: middle;
		}
		
		.admin-page-framework-field .admin-page-framework-input-label-container,
		.admin-page-framework-field .admin-page-framework-input-label-string
		{
			display: inline-block;		
			vertical-align: middle; 
		}
		
		/* Repeatable Fields */		
		.repeatable .admin-page-framework-field {
			clear: both;
			display: block;
		}
		.admin-page-framework-repeatable-field-buttons {
			float: right;		
			margin: 0.1em 0 0.5em 0.3em;
			vertical-align: middle;
		}
		.admin-page-framework-repeatable-field-buttons .repeatable-field-button {
			margin: 0 0.1em;
			font-weight: normal;
			vertical-align: middle;
			text-align: center;
		}

		/* Sortable Fields */
		.sortable .admin-page-framework-field {
			clear: both;
			float: left;
			display: inline-block;
			padding: 1em 1.2em 0.72em;
			margin: 1px 0 0 0;
			border-top-width: 1px;
			border-bottom-width: 1px;
			border-bottom-style: solid;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;			
			text-shadow: #fff 0 1px 0;
			-webkit-box-shadow: 0 1px 0 #fff;
			box-shadow: 0 1px 0 #fff;
			-webkit-box-shadow: inset 0 1px 0 #fff;
			box-shadow: inset 0 1px 0 #fff;
			-webkit-border-radius: 3px;
			border-radius: 3px;
			background: #f1f1f1;
			background-image: -webkit-gradient(linear, left bottom, left top, from(#ececec), to(#f9f9f9));
			background-image: -webkit-linear-gradient(bottom, #ececec, #f9f9f9);
			background-image:    -moz-linear-gradient(bottom, #ececec, #f9f9f9);
			background-image:      -o-linear-gradient(bottom, #ececec, #f9f9f9);
			background-image: linear-gradient(to top, #ececec, #f9f9f9);
			border: 1px solid #CCC;
			background: #F6F6F6;	
		}		
		.admin-page-framework-fields.sortable {
			margin-bottom: 1.2em;	/* each sortable field does not have a margin bottom so this rule gives a margin between the fields and the description */
		}
		
		/* Page Load Stats */
		#admin-page-framework-page-load-stats {
			clear: both;
			display: inline-block;
			width: 100%
		}
		#admin-page-framework-page-load-stats li{
			display: inline;
			margin-right: 1em;
		}		
		
		/* To give the footer area more space */
		#wpbody-content {
			padding-bottom: 140px;
		}
		"; public static $_sDefaultStyleIE = ''; public $aEnqueuingScripts = array(); public $aEnqueuingStyles = array(); public $iEnqueuedScriptIndex = 0; public $iEnqueuedStyleIndex = 0; public $bIsAdmin; public $bIsMinifiedVersion; function __construct( $oCaller, $sCallerPath, $sClassName ) { $this->oCaller = $oCaller; $this->sCallerPath = $sCallerPath ? $sCallerPath : AdminPageFramework_Utility::getCallerScriptPath( __FILE__ ); $this->sClassName = $sClassName; $this->sClassHash = md5( $sClassName ); $this->aScriptInfo = $this->getCallerInfo( $this->sCallerPath ); $GLOBALS['aAdminPageFramework'] = isset( $GLOBALS['aAdminPageFramework'] ) && is_array( $GLOBALS['aAdminPageFramework'] ) ? $GLOBALS['aAdminPageFramework'] : array(); $this->bIsAdmin = is_admin(); $this->bIsMinifiedVersion = ! class_exists( 'AdminPageFramework_Bootstrap' ); if ( ! isset( self::$_aLibraryData ) ) { $_sLibraryMainClassName = ( $this->bIsMinifiedVersion ) ? 'AdminPageFramework' : 'AdminPageFramework_Bootstrap'; $oRC = new ReflectionClass( $_sLibraryMainClassName ); self::_setLibraryData( $oRC->getFileName() ); } } public function _getCallerObject() { return $this->oCaller; } static public function _setLibraryData( $sLibraryFilePath ) { self::$_aLibraryData = AdminPageFramework_WPUtility::getScriptData( $sLibraryFilePath, 'library' ); } static public function _getLibraryData( $sLibraryFilePath=null ) { if ( isset( self::$_aLibraryData ) ) return self::$_aLibraryData; if ( $sLibraryFilePath ) self::_setLibraryData( $sLibraryFilePath ); return self::$_aLibraryData; } protected function getCallerInfo( $sCallerPath=null ) { $aCallerInfo = self::$_aStructure_CallerInfo; $aCallerInfo['sPath'] = $sCallerPath; $aCallerInfo['sType'] = $this->_getCallerType( $aCallerInfo['sPath'] ); if ( $aCallerInfo['sType'] == 'unknown' ) return $aCallerInfo; if ( $aCallerInfo['sType'] == 'plugin' ) return AdminPageFramework_WPUtility::getScriptData( $aCallerInfo['sPath'], $aCallerInfo['sType'] ) + $aCallerInfo; if ( $aCallerInfo['sType'] == 'theme' ) { $oTheme = wp_get_theme(); return array( 'sName' => $oTheme->Name, 'sVersion' => $oTheme->Version, 'sThemeURI' => $oTheme->get( 'ThemeURI' ), 'sURI' => $oTheme->get( 'ThemeURI' ), 'sAuthorURI' => $oTheme->get( 'AuthorURI' ), 'sAuthor' => $oTheme->get( 'Author' ), ) + $aCallerInfo; } } private function _getCallerType( $sScriptPath ) { if ( preg_match( '/[\/\\\\]themes[\/\\\\]/', $sScriptPath, $m ) ) return 'theme'; if ( preg_match( '/[\/\\\\]plugins[\/\\\\]/', $sScriptPath, $m ) ) return 'plugin'; return 'unknown'; } public function isPostDefinitionPage( $asPostTypes=array() ) { $_aPostTypes = ( array ) $asPostTypes; if ( ! in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php', ) ) ) return false; if ( empty( $_aPostTypes ) ) return true; if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $_aPostTypes ) ) return true; $this->_sCurrentPostType = isset( $this->_sCurrentPostType ) ? $this->_sCurrentPostType : ( isset( $_GET['post'] ) ? get_post_type( $_GET['post'] ) : '' ); if ( isset( $_GET['post'], $_GET['action'] ) && in_array( $this->_sCurrentPostType, $_aPostTypes ) ) return true; return false; } } endif;if ( ! class_exists( 'AdminPageFramework_Property_MetaBox' ) ) : class AdminPageFramework_Property_MetaBox extends AdminPageFramework_Property_Base { public $_sPropertyType = 'post_meta_box'; public $sMetaBoxID =''; public $sTitle = ''; public $aPostTypes = array(); public $aPages = array(); public $sContext = 'normal'; public $sPriority = 'default'; public $sClassName = ''; public $sCapability = 'edit_posts'; public $aFields = array(); public $aOptions = array(); public $sThickBoxTitle = ''; public $sThickBoxButtonUseThis = ''; public $aHelpTabText = array(); public $aHelpTabTextSide = array(); public static $_aStructure_Field = array( 'field_id' => null, 'type' => null, 'title' => null, 'description' => null, 'capability' => null, 'tip' => null, 'value' => null, 'default' => null, 'label' => '', 'if' => true, 'help' => null, 'help_aside' => null, 'show_title_column' => true, 'repeatable' => null, 'sortable' => null, ); function __construct( $oCaller, $sClassName, $sCapability ) { parent::__construct( $oCaller, null, $sClassName ); $this->sCapability = $sCapability; } } endif;if ( ! class_exists( 'AdminPageFramework_Property_Page' ) ) : class AdminPageFramework_Property_Page extends AdminPageFramework_Property_Base { public $_sPropertyType = 'page'; public $sClassName; public $sClassHash; public $sCapability = 'manage_options'; public $sPageHeadingTabTag = 'h2'; public $sInPageTabTag = 'h3'; public $sDefaultPageSlug; public $aPages = array(); public $aHiddenPages = array(); public $aRegisteredSubMenuPages = array(); public $aRootMenu = array( 'sTitle' => null, 'sPageSlug' => null, 'sIcon16x16' => null, 'iPosition' => null, 'fCreateRoot' => null, ); public $aInPageTabs = array(); public $aDefaultInPageTabs = array(); public $aPluginDescriptionLinks = array(); public $aPluginTitleLinks = array(); public $aFooterInfo = array( 'sLeft' => '', 'sRight' => '', ); public $sOptionKey = ''; public $aSections = array(); public $aFields = array(); public $aHelpTabs = array(); public $sFormEncType = 'multipart/form-data'; public $sThickBoxButtonUseThis = ''; public $bEnableForm = false; public $bShowPageTitle = true; public $bShowPageHeadingTabs = true; public $bShowInPageTabs = true; public $aAdminNotices = array(); public $aDisallowedQueryKeys = array( 'settings-updated' ); public function __construct( $oCaller, $sCallerPath, $sClassName, $sOptionKey, $sCapability='manage_options' ) { parent::__construct( $oCaller, $sCallerPath, $sClassName ); $this->sOptionKey = $sOptionKey ? $sOptionKey : $sClassName; $this->sCapability = empty( $sCapability ) ? $this->sCapability : $sCapability; $GLOBALS['aAdminPageFramework']['aPageClasses'] = isset( $GLOBALS['aAdminPageFramework']['aPageClasses'] ) && is_array( $GLOBALS['aAdminPageFramework']['aPageClasses'] ) ? $GLOBALS['aAdminPageFramework']['aPageClasses'] : array(); $GLOBALS['aAdminPageFramework']['aPageClasses'][ $sClassName ] = $oCaller; add_filter( "option_page_capability_{$this->sOptionKey}", array( $this, '_replyToGetCapability' ) ); } public function &__get( $sName ) { if ( $sName == 'aOptions' ) { $this->aOptions = get_option( $this->sOptionKey, array() ); return $this->aOptions; } return null; } public function isPageAdded( $sPageSlug='' ) { $sPageSlug = $sPageSlug ? $sPageSlug : ( isset( $_GET['page'] ) ? $_GET['page'] : '' ); return ( array_key_exists( trim( $sPageSlug ), $this->aPages ) ) ? true : false; } public function getDefaultInPageTab( $sPageSlug ) { if ( ! $sPageSlug ) return ''; return isset( $this->aDefaultInPageTabs[ $sPageSlug ] ) ? $this->aDefaultInPageTabs[ $sPageSlug ] : ''; } public function getDefaultOptions() { $_aDefaultOptions = array(); foreach( $this->aFields as $_sFieldID => $_aFields ) { $_vDefault = $this->_getDefautValue( $_aFields ); if ( isset( $_aField['section_id'] ) && $_aField['section_id'] ) $_aDefaultOptions[ $_aField['section_id'] ][ $_sFieldID ] = $_vDefault; else $_aDefaultOptions[ $_sFieldID ] = $_vDefault; } return $_aDefaultOptions; } private function _getDefautValue( $aFields ) { $_aSubFields = AdminPageFramework_Utility::getNumericElements( $aFields ); if ( count( $_aSubFields ) == 0 ) { $_aField = $aFields; return isset( $_aField['value'] ) ? $_aField['value'] : ( isset( $_aField['default'] ) ? $_aField['default'] : null ); } $_aDefault = array(); array_unshift( $_aSubFields, $aFields ); foreach( $_aSubFields as $_iIndex => $_aField ) $_aDefault[ $_iIndex ] = isset( $_aField['value'] ) ? $_aField['value'] : ( isset( $_aField['default'] ) ? $_aField['default'] : null ); return $_aDefault; } public function _replyToGetCapability() { return $this->sCapability; } } endif;if ( ! class_exists( 'AdminPageFramework_Property_PostType' ) ) : class AdminPageFramework_Property_PostType extends AdminPageFramework_Property_Base { public $_sPropertyType = 'post_type'; public $sPostType = ''; public $aPostTypeArgs = array(); public $sClassName = ''; public $aColumnHeaders = array( 'cb' => '<input type="checkbox" />', 'title' => 'Title', 'author' => 'Author', 'comments' => '<div class="comment-grey-bubble"></div>', 'date' => 'Date', ); public $aColumnSortable = array( 'title' => true, 'date' => true, ); public $sCallerPath = ''; public $aTaxonomies; public $aTaxonomyTableFilters = array(); public $aTaxonomyRemoveSubmenuPages = array(); public $bEnableAutoSave = true; public $bEnableAuthorTableFileter = false; } endif;if ( ! class_exists( 'AdminPageFramework_Property_MetaBox_Page' ) ) : class AdminPageFramework_Property_MetaBox_Page extends AdminPageFramework_Property_MetaBox { public $_sPropertyType = 'page_meta_box'; public $aPageSlugs = array(); public $oAdminPage; public $aHelpTabs = array(); function __construct( $oCaller, $sClassName, $sCapability='manage_options' ) { add_action( 'admin_menu', array( $this, '_replyToSetUpProperties' ), 100 ); parent::__construct( $oCaller, $sClassName, $sCapability ); $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] = isset( $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] ) && is_array( $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] ) ? $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'] : array(); $GLOBALS['aAdminPageFramework']['aMetaBoxForPagesClasses'][ $sClassName ] = $oCaller; } public function _replyToSetUpProperties() { if ( ! isset( $_GET['page'] ) ) return; $this->oAdminPage = $this->_getOwnerClass( $_GET['page'] ); if ( ! $this->oAdminPage ) return; $this->aHelpTabs = $this->oAdminPage->oProp->aHelpTabs; $this->oAdminPage->oProp->bEnableForm = true; $this->aOptions = $this->oAdminPage->oProp->aOptions; } public function _getScreenIDOfPage( $sPageSlug ) { return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) ) ? $oAdminPage->oProp->aPages[ $sPageSlug ]['_page_hook'] : ''; } public function isPageAdded( $sPageSlug='' ) { return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) ) ? $oAdminPage->oProp->isPageAdded( $sPageSlug ) : false; } public function isCurrentTab( $sTabSlug ) { $sCurrentPageSlug = isset( $_GET['page'] ) ? $_GET['page'] : ''; if ( ! $sCurrentPageSlug ) return false; $sCurrentTabSlug = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->getDefaultInPageTab( $sCurrentPageSlug ); return ( $sTabSlug == $sCurrentTabSlug ); } public function getDefaultInPageTab( $sPageSlug ) { if ( ! $sPageSlug ) return ''; return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) ) ? $oAdminPage->oProp->getDefaultInPageTab( $sPageSlug ) : ''; } public function getOptionKey( $sPageSlug ) { if ( ! $sPageSlug ) return ''; return ( $oAdminPage = $this->_getOwnerClass( $sPageSlug ) ) ? $oAdminPage->oProp->sOptionKey : ''; } private function _getOwnerClass( $sPageSlug ) { foreach( $GLOBALS['aAdminPageFramework']['aPageClasses'] as $oClass ) if ( $oClass->oProp->isPageAdded( $sPageSlug ) ) return $oClass; return null; } } endif;if ( ! class_exists( 'AdminPageFramework_Property_TaxonomyField' ) ) : class AdminPageFramework_Property_TaxonomyField extends AdminPageFramework_Property_MetaBox { public $_sPropertyType = 'taxonomy_field'; public $aTaxonomySlugs; public $sOptionKey; } endif;if ( ! class_exists( 'AdminPageFramework_CustomSubmitFields' ) ) : abstract class AdminPageFramework_CustomSubmitFields { public function __construct( $aPostElement ) { $this->aPost = $aPostElement; $this->sInputID = $this->getInputID( $aPostElement['submit'] ); } protected function getElement( $aElement, $sInputID, $sElementKey='format' ) { return ( isset( $aElement[ $sInputID ][ $sElementKey ] ) ) ? $aElement[ $sInputID ][ $sElementKey ] : null; } public function getSiblingValue( $sKey ) { return $this->getElement( $this->aPost, $this->sInputID, $sKey ); } public function getInputID( $aSubmitElement ) { foreach( $aSubmitElement as $sInputID => $v ) { $this->sInputID = $sInputID; return $this->sInputID; } } } endif;if ( ! class_exists( 'AdminPageFramework_ExportOptions' ) ) : class AdminPageFramework_ExportOptions extends AdminPageFramework_CustomSubmitFields { public function __construct( $aPostExport, $sClassName ) { parent::__construct( $aPostExport ); $this->sClassName = $sClassName; $this->sFileName = $this->getElement( $aPostExport, $this->sInputID, 'file_name' ); $this->sFormatType = $this->getElement( $aPostExport, $this->sInputID, 'format' ); $this->bIsDataSet = $this->getElement( $aPostExport, $this->sInputID, 'transient' ); } public function getTransientIfSet( $vData ) { if ( $this->bIsDataSet ) { $_tmp = get_transient( md5( "{$this->sClassName}_{$this->sInputID}" ) ); if ( $_tmp !== false ) { $vData = $_tmp; } } return $vData; } public function getFileName() { return $this->sFileName; } public function getFormat() { return $this->sFormatType; } public function doExport( $vData, $sFileName=null, $sFormatType=null ) { $sFileName = isset( $sFileName ) ? $sFileName : $this->sFileName; $sFormatType = isset( $sFormatType ) ? $sFormatType : $this->sFormatType; header( 'Content-Description: File Transfer' ); header( 'Content-Disposition: attachment; filename=' . $sFileName ); switch ( strtolower( $sFormatType ) ) { case 'text': if ( is_array( $vData ) || is_object( $vData ) ) die( AdminPageFramework_Debug::getArray( $vData, null, false ) ); die( $vData ); case 'json': die( json_encode( ( array ) $vData ) ); case 'array': default: die( serialize( ( array ) $vData )); } } } endif;if ( ! class_exists( 'AdminPageFramework_ImportOptions' ) ) : class AdminPageFramework_ImportOptions extends AdminPageFramework_CustomSubmitFields { public function __construct( $aFilesImport, $aPostImport ) { parent::__construct( $aPostImport ); $this->aFilesImport = $aFilesImport; } private function getElementInFilesArray( $aFilesImport, $sInputID, $sElementKey='error' ) { $sElementKey = strtolower( $sElementKey ); return isset( $aFilesImport[ $sElementKey ][ $sInputID ] ) ? $aFilesImport[ $sElementKey ][ $sInputID ] : null; } public function getError() { return $this->getElementInFilesArray( $this->aFilesImport, $this->sInputID, 'error' ); } public function getType() { return $this->getElementInFilesArray( $this->aFilesImport, $this->sInputID, 'type' ); } public function getImportData() { $sFilePath = $this->getElementInFilesArray( $this->aFilesImport, $this->sInputID, 'tmp_name' ); $vData = file_exists( $sFilePath ) ? file_get_contents( $sFilePath, true ) : false; return $vData; } public function formatImportData( &$vData, $sFormatType=null ) { $sFormatType = isset( $sFormatType ) ? $sFormatType : $this->getFormatType(); switch ( strtolower( $sFormatType ) ) { case 'text': return; case 'json': $vData = json_decode( ( string ) $vData, true ); return; case 'array': default: $vData = maybe_unserialize( trim( $vData ) ); return; } } public function getFormatType() { $this->sFormatType = isset( $this->sFormatType ) && $this->sFormatType ? $this->sFormatType : $this->getElement( $this->aPost, $this->sInputID, 'format' ); return $this->sFormatType; } } endif;class AdminPageFramework_RegisterClasses { protected $_aClasses = array(); function __construct( $sClassDirPath, & $aClasses=array(), $aAllowedExtensions=array( 'php', 'inc' ) ) { $this->_aClasses = $aClasses + $this->composeClassArray( $sClassDirPath, $aAllowedExtensions ); $this->registerClasses(); } protected function composeClassArray( $sClassDirPath, $aAllowedExtensions ) { $sClassDirPath = rtrim( $sClassDirPath, '\\/' ) . DIRECTORY_SEPARATOR; $aFilePaths = $this->doRecursiveGlob( $sClassDirPath . '*.' . $this->getGlobPatternExtensionPart( $aAllowedExtensions ), GLOB_BRACE ); $aClasses = array(); foreach( $aFilePaths as $sFilePath ) $aClasses[ pathinfo( $sFilePath, PATHINFO_FILENAME ) ] = $sFilePath; return $aClasses; } protected function getGlobPatternExtensionPart( $aExtensions=array( 'php', 'inc' ) ) { return empty( $aExtensions ) ? '*' : '{' . implode( ',', $aExtensions ) . '}'; } protected function doRecursiveGlob( $sPathPatten, $iFlags=0 ) { $aFiles = glob( $sPathPatten, $iFlags ); foreach ( glob( dirname( $sPathPatten ) . '/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $sDirPath ) $aFiles = array_merge( $aFiles, $this->doRecursiveGlob( $sDirPath . '/' . basename( $sPathPatten ), $iFlags ) ); return $aFiles; } protected function registerClasses() { spl_autoload_register( array( $this, 'replyToAutoLoader' ) ); } public function replyToAutoLoader( $sCalledUnknownClassName ) { if ( array_key_exists( $sCalledUnknownClassName, $this->_aClasses ) && file_exists( $this->_aClasses[ $sCalledUnknownClassName ] ) ) include_once( $this->_aClasses[ $sCalledUnknownClassName ] ); } }if ( ! class_exists( 'AdminPageFramework_Utility' ) ) : abstract class AdminPageFramework_Utility { public static function sanitizeSlug( $sSlug ) { return preg_replace( '/[^a-zA-Z0-9_\x7f-\xff]/', '_', trim( $sSlug ) ); } public static function sanitizeString( $sString ) { return preg_replace( '/[^a-zA-Z0-9_\x7f-\xff\-]/', '_', $sString ); } public static function getCorrespondingArrayValue( $vSubject, $sKey, $sDefault='', $bBlankToDefault=false ) { if ( ! isset( $vSubject ) ) return $sDefault; if ( $bBlankToDefault && $vSubject == '' ) return $sDefault; if ( ! is_array( $vSubject ) ) return ( string ) $vSubject; if ( isset( $vSubject[ $sKey ] ) ) return $vSubject[ $sKey ]; return $sDefault; } public static function getArrayDimension( $array ) { return ( is_array( reset( $array ) ) ) ? self::getArrayDimension( reset( $array ) ) + 1 : 1; } public static function uniteArrays( $aPrecedence, $aDefault1 ) { $aArgs = array_reverse( func_get_args() ); $aArray = array(); foreach( $aArgs as $aArg ) $aArray = self::uniteArraysRecursive( $aArg, $aArray ); return $aArray; } public static function uniteArraysRecursive( $aPrecedence, $aDefault ) { if ( is_null( $aPrecedence ) ) $aPrecedence = array(); if ( ! is_array( $aDefault ) || ! is_array( $aPrecedence ) ) return $aPrecedence; foreach( $aDefault as $sKey => $v ) { if ( ! array_key_exists( $sKey, $aPrecedence ) || is_null( $aPrecedence[ $sKey ] ) ) $aPrecedence[ $sKey ] = $v; else { if ( is_array( $aPrecedence[ $sKey ] ) && is_array( $v ) ) $aPrecedence[ $sKey ] = self::uniteArraysRecursive( $aPrecedence[ $sKey ], $v ); } } return $aPrecedence; } static public function getQueryValueInURLByKey( $sURL, $sQueryKey ) { $aURL = parse_url( $sURL ); parse_str( $aURL['query'], $aQuery ); return isset( $aQuery[ $sQueryKey ] ) ? $aQuery[ $sQueryKey ] : null; } static public function fixNumber( $nToFix, $nDefault, $nMin="", $nMax="" ) { if ( ! is_numeric( trim( $nToFix ) ) ) return $nDefault; if ( $nMin !== "" && $nToFix < $nMin ) return $nMin; if ( $nMax !== "" && $nToFix > $nMax ) return $nMax; return $nToFix; } static public function getRelativePath( $from, $to ) { $from = is_dir( $from ) ? rtrim( $from, '\/') . '/' : $from; $to = is_dir( $to ) ? rtrim( $to, '\/') . '/' : $to; $from = str_replace( '\\', '/', $from ); $to = str_replace( '\\', '/', $to ); $from = explode( '/', $from ); $to = explode( '/', $to ); $relPath = $to; foreach( $from as $depth => $dir ) { if( $dir === $to[ $depth ] ) { array_shift( $relPath ); } else { $remaining = count( $from ) - $depth; if( $remaining > 1 ) { $padLength = ( count( $relPath ) + $remaining - 1 ) * -1; $relPath = array_pad( $relPath, $padLength, '..' ); break; } else { $relPath[ 0 ] = './' . $relPath[ 0 ]; } } } return implode( '/', $relPath ); } static public function getCallerScriptPath( $asRedirectedFiles=array( __FILE__ ) ) { $aRedirectedFiles = ( array ) $asRedirectedFiles; $aRedirectedFiles[] = __FILE__; $sCallerFilePath = ''; foreach( debug_backtrace() as $aDebugInfo ) { $sCallerFilePath = $aDebugInfo['file']; if ( in_array( $sCallerFilePath, $aRedirectedFiles ) ) continue; break; } return $sCallerFilePath; } static public function isLastElement( array $aArray, $sKey ) { end( $aArray ); return $sKey === key( $aArray ); } static public function generateAttributes( array $aAttributes ) { $aOutput = array(); foreach( $aAttributes as $sAttribute => $sProperty ) { if ( empty( $sProperty ) && $sProperty !== 0 ) continue; if ( is_array( $sProperty ) || is_object( $sProperty ) ) continue; $aOutput[] = "{$sAttribute}='{$sProperty}'"; } return implode( ' ', $aOutput ); } static public function minifyCSS( $sCSSRules ) { return str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $sCSSRules ) ); } static public function getNumericElements( $aParse ) { foreach ( $aParse as $isKey => $v ) { if ( ! is_int( $isKey ) ) unset( $aParse[ $isKey ] ); } return $aParse; } } endif;if ( ! class_exists( 'AdminPageFramework_WPUtility' ) ) : class AdminPageFramework_WPUtility extends AdminPageFramework_Utility { public function doActions( $aActionHooks, $vArgs1=null, $vArgs2=null, $_and_more=null ) { $aArgs = func_get_args(); $aActionHooks = $aArgs[ 0 ]; foreach( ( array ) $aActionHooks as $sActionHook ) { $aArgs[ 0 ] = $sActionHook; call_user_func_array( 'do_action' , $aArgs ); } } public function addAndDoActions( $oCallerObject, $aActionHooks, $vArgs1=null, $vArgs2=null, $_and_more=null ) { $aArgs = func_get_args(); $oCallerObject = $aArgs[ 0 ]; $aActionHooks = $aArgs[ 1 ]; foreach( ( array ) $aActionHooks as $sActionHook ) { if ( ! $sActionHook ) continue; $aArgs[ 1 ] = $sActionHook; call_user_func_array( array( $this, 'addAndDoAction' ) , $aArgs ); } } public function addAndDoAction( $oCallerObject, $sActionHook, $vArgs1=null, $vArgs2=null, $_and_more=null ) { $iArgs = func_num_args(); $aArgs = func_get_args(); $oCallerObject = $aArgs[ 0 ]; $sActionHook = $aArgs[ 1 ]; if ( ! $sActionHook ) return; add_action( $sActionHook, array( $oCallerObject, $sActionHook ), 10, $iArgs - 2 ); unset( $aArgs[ 0 ] ); call_user_func_array( 'do_action' , $aArgs ); } public function addAndApplyFilters() { $aArgs = func_get_args(); $oCallerObject = $aArgs[ 0 ]; $aFilters = $aArgs[ 1 ]; $vInput = $aArgs[ 2 ]; foreach( ( array ) $aFilters as $sFilter ) { if ( ! $sFilter ) continue; $aArgs[ 1 ] = $sFilter; $aArgs[ 2 ] = $vInput; $vInput = call_user_func_array( array( $this, 'addAndApplyFilter' ) , $aArgs ); } return $vInput; } public function addAndApplyFilter() { $iArgs = func_num_args(); $aArgs = func_get_args(); $oCallerObject = $aArgs[ 0 ]; $sFilter = $aArgs[ 1 ]; if ( ! $sFilter ) return $aArgs[ 2 ]; add_filter( $sFilter, array( $oCallerObject, $sFilter ), 10, $iArgs - 2 ); unset( $aArgs[ 0 ] ); return call_user_func_array( 'apply_filters', $aArgs ); } public function getFilterArrayByPrefix( $sPrefix, $sClassName, $sPageSlug, $sTabSlug, $bReverse=false ) { $aFilters = array(); if ( $sTabSlug && $sPageSlug ) $aFilters[] = "{$sPrefix}{$sPageSlug}_{$sTabSlug}"; if ( $sPageSlug ) $aFilters[] = "{$sPrefix}{$sPageSlug}"; if ( $sClassName ) $aFilters[] = "{$sPrefix}{$sClassName}"; return $bReverse ? array_reverse( $aFilters ) : $aFilters; } static public function getScriptData( $sPath, $sType='plugin' ) { $aData = get_file_data( $sPath, array( 'sName' => 'Name', 'sURI' => 'URI', 'sScriptName' => 'Script Name', 'sLibraryName' => 'Library Name', 'sLibraryURI' => 'Library URI', 'sPluginName' => 'Plugin Name', 'sPluginURI' => 'Plugin URI', 'sThemeName' => 'Theme Name', 'sThemeURI' => 'Theme URI', 'sVersion' => 'Version', 'sDescription' => 'Description', 'sAuthor' => 'Author', 'sAuthorURI' => 'Author URI', 'sTextDomain' => 'Text Domain', 'sDomainPath' => 'Domain Path', 'sNetwork' => 'Network', '_sitewide' => 'Site Wide Only', ), in_array( $sType, array( 'plugin', 'theme' ) ) ? $sType : 'plugin' ); switch ( trim( $sType ) ) { case 'theme': $aData['sName'] = $aData['sThemeName']; $aData['sURI'] = $aData['sThemeURI']; break; case 'library': $aData['sName'] = $aData['sLibraryName']; $aData['sURI'] = $aData['sLibraryURI']; break; case 'script': $aData['sName'] = $aData['sScriptName']; break; case 'plugin': $aData['sName'] = $aData['sPluginName']; $aData['sURI'] = $aData['sPluginURI']; break; default: break; } return $aData; } public function getCurrentAdminURL() { $sRequestURI = $GLOBALS['is_IIS'] ? $_SERVER['PATH_INFO'] : $_SERVER["REQUEST_URI"]; $sPageURL = ( @$_SERVER["HTTPS"] == "on" ) ? "https://" : "http://"; if ( $_SERVER["SERVER_PORT"] != "80" ) $sPageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $sRequestURI; else $sPageURL .= $_SERVER["SERVER_NAME"] . $sRequestURI; return $sPageURL; } public function getQueryAdminURL( $aAddingQueries, $aRemovingQueryKeys=array(), $sSubjectURL='' ) { $sSubjectURL = $sSubjectURL ? $sSubjectURL : add_query_arg( $_GET, admin_url( $GLOBALS['pagenow'] ) ); return $this->getQueryURL( $aAddingQueries, $aRemovingQueryKeys, $sSubjectURL ); } public function getQueryURL( $aAddingQueries, $aRemovingQueryKeys, $sSubjectURL ) { $sSubjectURL = empty( $aRemovingQueryKeys ) ? $sSubjectURL : remove_query_arg( ( array ) $aRemovingQueryKeys, $sSubjectURL ); $sSubjectURL = add_query_arg( $aAddingQueries, $sSubjectURL ); return $sSubjectURL; } static public function getSRCFromPath( $sFilePath ) { $oWPStyles = new WP_Styles(); $sRelativePath = AdminPageFramework_Utility::getRelativePath( ABSPATH, $sFilePath ); $sRelativePath = preg_replace( "/^\.[\/\\\]/", '', $sRelativePath, 1 ); $sHref = trailingslashit( $oWPStyles->base_url ) . $sRelativePath; unset( $oWPStyles ); return esc_url( $sHref ); } static public function resolveSRC( $sSRC, $bReturnNullIfNotExist=false ) { if ( ! $sSRC ) return $bReturnNullIfNotExist ? null : $sSRC; if ( filter_var( $sSRC, FILTER_VALIDATE_URL ) ) return $sSRC; if ( file_exists( realpath( $sSRC ) ) ) return self::getSRCFromPath( $sSRC ); if ( $bReturnNullIfNotExist ) return null; return $sSRC; } static public function generateAttributes( array $aAttributes ) { foreach( $aAttributes as $sAttribute => &$asProperty ) { if ( is_array( $asProperty ) || is_object( $asProperty ) ) unset( $aAttributes[ $sAttribute ] ); if ( is_string( $asProperty ) ) $asProperty = esc_attr( $asProperty ); } return parent::generateAttributes( $aAttributes ); } } endif;if ( ! class_exists( 'AdminPageFramework_InputField' ) ) : class AdminPageFramework_InputField extends AdminPageFramework_WPUtility { private $_bIsMetaBox = false; public function __construct( &$aField, &$aOptions, $aErrors, &$aFieldTypeDefinitions, &$oMsg ) { $aFieldTypeDefinition = isset( $aFieldTypeDefinitions[ $aField['type'] ] ) ? $aFieldTypeDefinitions[ $aField['type'] ] : $aFieldTypeDefinitions['default']; $aFieldTypeDefinition['aDefaultKeys']['attributes'] = array( 'fieldset' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['fieldset'], 'fields' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['fields'], 'field' => $aFieldTypeDefinition['aDefaultKeys']['attributes']['field'], ); $this->aField = $this->uniteArrays( $aField, $aFieldTypeDefinition['aDefaultKeys'] ); $this->aFieldTypeDefinitions = $aFieldTypeDefinitions; $this->aOptions = $aOptions; $this->aErrors = $aErrors ? $aErrors : array(); $this->oMsg = $oMsg; $this->_loadScripts(); } private function _loadScripts() { $GLOBALS['aAdminPageFramework']['aFieldFlags'] = isset( $GLOBALS['aAdminPageFramework']['aFieldFlags'] ) ? $GLOBALS['aAdminPageFramework']['aFieldFlags'] : array(); if ( ! isset( $GLOBALS['aAdminPageFramework']['bEnqueuedUtilityPluins'] ) ) { add_action( 'admin_footer', array( $this, '_replyToAddUtilityPlugins' ) ); add_action( 'admin_footer', array( $this, '_replyToAddAttributeUpdaterjQueryPlugin' ) ); $GLOBALS['aAdminPageFramework']['bEnqueuedUtilityPluins'] = true; } if ( ! isset( $GLOBALS['aAdminPageFramework']['bEnqueuedRepeatableFieldScript'] ) ) { add_action( 'admin_footer', array( $this, '_replyToAddRepeatableFieldjQueryPlugin' ) ); $GLOBALS['aAdminPageFramework']['bEnqueuedRepeatableFieldScript'] = true; } if ( ! isset( $GLOBALS['aAdminPageFramework']['bEnqueuedSortableFieldScript'] ) ) { add_action( 'admin_footer', array( $this, '_replyToAddSortableFieldPlugin' ) ); $GLOBALS['aAdminPageFramework']['bEnqueuedSortableFieldScript'] = true; } if ( ! isset( $GLOBALS['aAdminPageFramework']['bEnqueuedRegisterCallbackScript'] ) ) { add_action( 'admin_footer', array( $this, '_replyToAddRegisterCallbackjQueryPlugin' ) ); $GLOBALS['aAdminPageFramework']['bEnqueuedRegisterCallbackScript'] = true; } } private function _getInputName( $aField=null, $sKey='' ) { $sKey = ( string ) $sKey; $aField = isset( $aField ) ? $aField : $this->aField; $sSectionDimension = isset( $aField['section_id'] ) && $aField['section_id'] ? "[{$aField['section_id']}]" : ''; return ( isset( $aField['option_key'] ) ? "{$aField['option_key']}{$sSectionDimension}[{$aField['field_id']}]" : $aField['field_id'] ) . ( $sKey !== '0' && empty( $sKey ) ? '' : "[{$sKey}]" ); } protected function _getFlatInputName( &$aField, $sKey='' ) { $sKey = ( string ) $sKey; $sSectionDimension = isset( $aField['section_id'] ) && $aField['section_id'] ? "|{$aField['section_id']}" : ''; return ( isset( $aField['option_key'] ) ? "{$aField['option_key']}{$sSectionDimension}|{$aField['field_id']}" : $aField['field_id'] ) . ( $sKey !== '0' && empty( $sKey ) ? "" : "|{$sKey}" ); } private function _getInputFieldValue( &$aField, $aOptions ) { switch( $aField['_field_type'] ) { default: case 'page': case 'page_meta_box': case 'taxonomy': if ( isset( $aField['section_id'] ) && $aField['section_id'] ) return isset( $aOptions[ $aField['section_id'] ][ $aField['field_id'] ] ) ? $aOptions[ $aField['section_id'] ][ $aField['field_id'] ] : null; return isset( $aOptions[ $aField['field_id'] ] ) ? $aOptions[ $aField['field_id'] ] : null; case 'post_meta_box': if ( ! isset( $_GET['action'], $_GET['post'] ) ) return null; if ( ! isset( $aField['section_id'] ) || ! $aField['section_id'] ) return get_post_meta( $_GET['post'], $aField['field_id'], true ); $aSectionValues = get_post_meta( $_GET['post'], $aField['section_id'], true ); return isset( $aSectionValues[ $aField['field_id'] ] ) ? $aSectionValues[ $aField['field_id'] ] : null; } return null; } private function _getInputID( $aField, $sIndex ) { return isset( $aField['section_id'] ) ? $aField['section_id'] . '_' . $aField['field_id'] . '_' . $sIndex : $aField['field_id'] . '_' . $sIndex ; } private function _getInputTagID( $aField ) { return isset( $aField['section_id'] ) ? $aField['section_id'] . '_' . $aField['field_id'] : $aField['field_id']; } public function _getInputFieldOutput() { $aFieldsOutput = array(); $aExtraOutput = array(); if ( isset( $this->aField['section_id'], $this->aErrors[ $this->aField['section_id'] ], $this->aErrors[ $this->aField['section_id'] ][ $this->aField['field_id'] ] ) ) $aFieldsOutput[] = "<span style='color:red;'>*&nbsp;{$this->aField['error_message']}" . $this->aErrors[ $this->aField['section_id'] ][ $this->aField['field_id'] ] . "</span><br />"; else if ( isset( $this->aErrors[ $this->aField['field_id'] ] ) ) $aFieldsOutput[] = "<span style='color:red;'>*&nbsp;{$this->aField['error_message']}" . $this->aErrors[ $this->aField['field_id'] ] . "</span><br />"; $this->aField['tag_id'] = $this->_getInputTagID( $this->aField ); $aFields = $this->_composeFieldsArray( $this->aField, $this->aOptions ); foreach( $aFields as $sKey => $aField ) { $aFieldTypeDefinition = isset( $this->aFieldTypeDefinitions[ $aField['type'] ] ) ? $this->aFieldTypeDefinitions[ $aField['type'] ] : $this->aFieldTypeDefinitions['default']; $aField['_index'] = $sKey; $aField['input_id'] = $this->_getInputID( $aField, $sKey ); $aField['_input_name'] = $this->_getInputName( $this->aField, $aField['_is_multiple_fields'] ? $sKey : '' ); $aField['_input_name_flat'] = $this->_getFlatInputName( $this->aField, $aField['_is_multiple_fields'] ? $sKey : '' ); $aField['_field_container_id'] = "field-{$aField['input_id']}"; $aField['_fields_container_id'] = "fields-{$this->aField['tag_id']}"; $aField['_fieldset_container_id'] = "fieldset-{$this->aField['tag_id']}"; $aField['attributes'] = $this->uniteArrays( ( array ) $aField['attributes'], array( 'id' => $aField['input_id'], 'name' => $aField['_input_name'], 'value' => $aField['value'], 'type' => $aField['type'], 'disabled' => null, ), ( array ) $aFieldTypeDefinition['aDefaultKeys']['attributes'] ); $_aFieldAttributes = array( 'id' => $aField['_field_container_id'], 'class' => "admin-page-framework-field admin-page-framework-field-{$aField['type']}" . ( $aField['attributes']['disabled'] ? ' disabled' : '' ), 'data-type' => "{$aField['type']}", ) + $aField['attributes']['field']; $aFieldsOutput[] = is_callable( $aFieldTypeDefinition['hfRenderField'] ) ? $aField['before_field'] . "<div " . $this->generateAttributes( $_aFieldAttributes ) . ">" . call_user_func_array( $aFieldTypeDefinition['hfRenderField'], array( $aField ) ) . ( ( $sDelimiter = $aField['delimiter'] ) ? "<div " . $this->generateAttributes( array( 'class' => 'delimiter', 'id' => "delimiter-{$aField['input_id']}", 'style' => $this->isLastElement( $aFields, $sKey ) ? "display:none;" : "", ) ) . ">{$sDelimiter}</div>" : "" ) . "</div>" . $aField['after_field'] : ""; } $aExtraOutput[] = ( isset( $this->aField['description'] ) && trim( $this->aField['description'] ) != '' ) ? "<p class='admin-page-framework-fields-description'><span class='description'>{$this->aField['description']}</span></p>" : ''; $aExtraOutput[] = $this->aField['repeatable'] ? $this->_getRepeaterFieldEnablerScript( 'fields-' . $this->aField['tag_id'], count( $aFields ), $this->aField['repeatable'] ) : ''; $aExtraOutput[] = $this->aField['sortable'] && ( count( $aFields ) > 1 || $this->aField['repeatable'] ) ? $this->_getSortableFieldEnablerScript( 'fields-' . $this->aField['tag_id'] ) : ''; $_aFieldsSetAttributes = array( 'id' => 'fieldset-' . $this->aField['tag_id'], 'class' => 'admin-page-framework-fieldset', 'data-field_id' => $this->aField['tag_id'], ) + $this->aField['attributes']['fieldset']; $_aFieldsContainerAttributes = array( 'id' => 'fields-' . $this->aField['tag_id'], 'class' => 'admin-page-framework-fields' . ( $this->aField['repeatable'] ? ' repeatable' : '' ) . ( $this->aField['sortable'] ? ' sortable' : '' ), 'data-type' => $this->aField['type'], ) + $this->aField['attributes']['fields']; return "<fieldset " . $this->generateAttributes( $_aFieldsSetAttributes ) . ">" . $this->_getTableRowIDSetterScript( $this->aField['tag_id'] ) . "<div " . $this->generateAttributes( $_aFieldsContainerAttributes ) . ">" . $this->aField['before_fields'] . implode( PHP_EOL, $aFieldsOutput ) . $this->aField['after_fields'] . "</div>" . implode( PHP_EOL, $aExtraOutput ) . "</fieldset>"; } protected function _composeFieldsArray( $aField, $aOptions ) { $vSavedValue = $this->_getInputFieldValue( $aField, $aOptions ); $aFirstField = array(); $aSubFields = array(); foreach( $aField as $nsIndex => $vFieldElement ) { if ( is_numeric( $nsIndex ) ) $aSubFields[] = $vFieldElement; else $aFirstField[ $nsIndex ] = $vFieldElement; } if ( $aField['repeatable'] ) foreach( ( array ) $vSavedValue as $iIndex => $vValue ) { if ( $iIndex == 0 ) continue; $aSubFields[ $iIndex - 1 ] = isset( $aSubFields[ $iIndex - 1 ] ) && is_array( $aSubFields[ $iIndex - 1 ] ) ? $aSubFields[ $iIndex - 1 ] : array(); } foreach( $aSubFields as &$aSubField ) { $aLabel = isset( $aSubField['label'] ) ? $aSubField['label'] : ( isset( $aFirstField['label'] ) ? $aFirstField['label'] : null ); $aSubField = $this->uniteArrays( $aSubField, $aFirstField ); $aSubField['label'] = $aLabel; } $aFields = array_merge( array( $aFirstField ), $aSubFields ); if ( count( $aSubFields ) > 0 || $aField['repeatable'] || $aField['sortable'] ) { foreach( $aFields as $iIndex => &$aThisField ) { $aThisField['_saved_value'] = isset( $vSavedValue[ $iIndex ] ) ? $vSavedValue[ $iIndex ] : null; $aThisField['_is_multiple_fields'] = true; } } else { $aFields[ 0 ]['_saved_value'] = $vSavedValue; $aFields[ 0 ]['_is_multiple_fields'] = false; } unset( $aThisField ); foreach( $aFields as &$aThisField ) { $aThisField['_is_value_set_by_user'] = isset( $aThisField['value'] ); $aThisField['value'] = isset( $aThisField['value'] ) ? $aThisField['value'] : ( isset( $aThisField['_saved_value'] ) ? $aThisField['_saved_value'] : ( isset( $aThisField['default'] ) ? $aThisField['default'] : null ) ); } return $aFields; } public function isMetaBox( $bTrueOrFalse=null ) { if ( isset( $bTrueOrFalse ) ) $this->_bIsMetaBox = $bTrueOrFalse; return $this->_bIsMetaBox; } private $bIsRepeatableScriptCalled = false; private function _getRepeaterFieldEnablerScript( $sFieldsContainerID, $iFieldCount, $aSettings ) { $_sAdd = $this->oMsg->__( 'add' ); $_sRemove = $this->oMsg->__( 'remove' ); $_sVisibility = $iFieldCount <= 1 ? " style='display:none;'" : ""; $_sSettingsAttributes = $this->_generateDataAttributes( ( array ) $aSettings ); $_sButtons = "<div class='admin-page-framework-repeatable-field-buttons' {$_sSettingsAttributes} >" . "<a class='repeatable-field-add button-secondary repeatable-field-button button button-small' href='#' title='{$_sAdd}' data-id='{$sFieldsContainerID}'>+</a>" . "<a class='repeatable-field-remove button-secondary repeatable-field-button button button-small' href='#' title='{$_sRemove}' {$_sVisibility} data-id='{$sFieldsContainerID}'>-</a>" . "</div>"; $aJSArray = json_encode( $aSettings ); return "<script type='text/javascript'>
				jQuery( document ).ready( function() {
					nodePositionIndicators = jQuery( '#{$sFieldsContainerID} .admin-page-framework-field .repeatable-field-buttons' );
					if ( nodePositionIndicators.length > 0 ) {	/* If the position of inserting the buttons is specified in the field type definition, replace the pointer element with the created output */
						nodePositionIndicators.replaceWith( \"{$_sButtons}\" );						
					} else {	/* Otherwise, insert the button element at the beginning of the field tag */
						jQuery( '#{$sFieldsContainerID} .admin-page-framework-field' ).prepend( \"{$_sButtons}\" );	// Adds the buttons
					}					
					jQuery( '#{$sFieldsContainerID}' ).updateAPFRepeatableFields( {$aJSArray} );	// Update the fields			
				});
			</script>"; } private function _generateDataAttributes( array $aArray ) { $aNewArray = array(); foreach( $aArray as $sKey => $v ) $aNewArray[ "data-{$sKey}" ] = $v; return $this->generateAttributes( $aNewArray ); } public function _replyToAddRepeatableFieldjQueryPlugin() { $sCannotAddMore = $this->oMsg->__( 'allowed_maximum_number_of_fields' ); $sCannotRemoveMore = $this->oMsg->__( 'allowed_minimum_number_of_fields' ); $sScript = "		
		(function ( $ ) {
		
			$.fn.updateAPFRepeatableFields = function( aSettings ) {
				
				var nodeThis = this;	// it can be from a fields container or a cloned field container.
				var sFieldsContainerID = nodeThis.find( '.repeatable-field-add' ).first().data( 'id' );
				
				/* Store the fields specific options in an array  */
				if( ! $.fn.aAPFRepeatableFieldsOptions ) $.fn.aAPFRepeatableFieldsOptions = [];
				if ( ! $.fn.aAPFRepeatableFieldsOptions.hasOwnProperty( sFieldsContainerID ) ) {		
					$.fn.aAPFRepeatableFieldsOptions[ sFieldsContainerID ] = $.extend({	
						max: 0,	// These are the defaults.
						min: 0,
						}, aSettings );
				}
				var aOptions = $.fn.aAPFRepeatableFieldsOptions[ sFieldsContainerID ];
				
				/* The Add button behaviour - if the tag id is given, multiple buttons will be selected. 
				 * Otherwise, a field node is given and single button will be selected. */
				$( nodeThis ).find( '.repeatable-field-add' ).click( function() {
					$( this ).addAPFRepeatableField();
					return false;	// will not click after that
				});
				
				/* The Remove button behaviour */
				$( nodeThis ).find( '.repeatable-field-remove' ).click( function() {
					$( this ).removeAPFRepeatableField();
					return false;	// will not click after that
				});		
				
				/* If the number of fields is less than the set minimum value, add fields and vice versa. */
				var sFieldID = nodeThis.find( '.repeatable-field-add' ).first().closest( '.admin-page-framework-field' ).attr( 'id' );
				var nCurrentFieldCount = jQuery( '#' + sFieldsContainerID ).find( '.admin-page-framework-field' ).length;
				if ( aOptions['min'] > 0 && nCurrentFieldCount > 0 ) {
					if ( ( aOptions['min'] - nCurrentFieldCount ) > 0 ) 
						$( '#' + sFieldID ).addAPFRepeatableField( sFieldID );				 
				}
				// if ( aOptions['max'] > 0 && nCurrentFieldCount > 0 ) {
					// if ( nCurrentFieldCount - aOptions['max'] < 0 ) {
						// $( '#' + sFieldID ).removeAPFRepeatableField( sFieldID );
					// }
				// }
				
			};
			
			/**
			 * Adds a repeatable field.
			 */
			$.fn.addAPFRepeatableField = function( sFieldContainerID ) {
				if ( typeof sFieldContainerID === 'undefined' ) {
					var sFieldContainerID = $( this ).closest( '.admin-page-framework-field' ).attr( 'id' );	
				}

				var nodeFieldContainer = $( '#' + sFieldContainerID );
				var nodeNewField = nodeFieldContainer.clone();	// clone without bind events.
				var nodeFieldsContainer = nodeFieldContainer.closest( '.admin-page-framework-fields' );
				var sFieldsContainerID = nodeFieldsContainer.attr( 'id' );
				
				/* If the set maximum number of fields already exists, do not add */
				var sMaxNumberOfFields = $.fn.aAPFRepeatableFieldsOptions[ sFieldsContainerID ]['max'];
				if ( sMaxNumberOfFields != 0 && nodeFieldsContainer.find( '.admin-page-framework-field' ).length >= sMaxNumberOfFields ) {
					var nodeLastRepeaterButtons = nodeFieldContainer.find( '.admin-page-framework-repeatable-field-buttons' ).last();
					var sMessage = $( this ).formatPrintText( '{$sCannotAddMore}', sMaxNumberOfFields );
					var nodeMessage = $( '<span class=\"repeatable-error\" id=\"repeatable-error-' + sFieldsContainerID + '\" style=\"float:right;color:red;margin-left:1em;\">' + sMessage + '</span>' );
					if ( nodeFieldsContainer.find( '#repeatable-error-' + sFieldsContainerID ).length > 0 )
						nodeFieldsContainer.find( '#repeatable-error-' + sFieldsContainerID ).replaceWith( nodeMessage );
					else
						nodeLastRepeaterButtons.before( nodeMessage );
					nodeMessage.delay( 2000 ).fadeOut( 1000 );
					return;		
				}
				
				nodeNewField.find( 'input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea' ).val( '' );	// empty the value		
				nodeNewField.find( '.repeatable-error' ).remove();	// remove error messages.
				
				/* Add the cloned new field element */
				nodeNewField.insertAfter( nodeFieldContainer );	

				/* Rebind the click event to the buttons - important to update AFTER inserting the clone to the document node since the update method need to count fields. */
				nodeNewField.updateAPFRepeatableFields();				
				
				/* Increment the names and ids of the next following siblings. */
				nodeFieldContainer.nextAll().each( function() {
					$( this ).incrementIDAttribute( 'id' );
					$( this ).find( 'label' ).incrementIDAttribute( 'for' );
					$( this ).find( 'input,textarea,select' ).incrementIDAttribute( 'id' );
					$( this ).find( 'input,textarea,select' ).incrementNameAttribute( 'name' );
				});
				
				/* It seems radio buttons of the original field need to be reassigned. Otherwise, the checked items will be gone. */
				nodeFieldContainer.find( 'input[type=radio][checked=checked]' ).attr( 'checked', 'Checked' );	
				
				/* Call the registered callback functions */
				nodeNewField.callBackAddRepeatableField( nodeNewField.data( 'type' ), nodeNewField.attr( 'id' ) );					
				
				/* If more than one fields are created, show the Remove button */
				var nodeRemoveButtons =  nodeFieldsContainer.find( '.repeatable-field-remove' );
				if ( nodeRemoveButtons.length > 1 ) nodeRemoveButtons.show();				
									
				/* Return the newly created element */
				return nodeNewField;	// media uploader needs this 
				
			};
				
			$.fn.removeAPFRepeatableField = function() {
				
				/* Need to remove the element: the field container */
				var nodeFieldContainer = $( this ).closest( '.admin-page-framework-field' );
				var nodeFieldsContainer = $( this ).closest( '.admin-page-framework-fields' );
				var sFieldsContainerID = nodeFieldsContainer.attr( 'id' );
				
				/* If the set minimum number of fields already exists, do not remove */
				var sMinNumberOfFields = $.fn.aAPFRepeatableFieldsOptions[ sFieldsContainerID ]['min'];
				if ( sMinNumberOfFields != 0 && nodeFieldsContainer.find( '.admin-page-framework-field' ).length <= sMinNumberOfFields ) {
					var nodeLastRepeaterButtons = nodeFieldContainer.find( '.admin-page-framework-repeatable-field-buttons' ).last();
					var sMessage = $( this ).formatPrintText( '{$sCannotRemoveMore}', sMinNumberOfFields );
					var nodeMessage = $( '<span class=\"repeatable-error\" id=\"repeatable-error-' + sFieldsContainerID + '\" style=\"float:right;color:red;margin-left:1em;\">' + sMessage + '</span>' );
					if ( nodeFieldsContainer.find( '#repeatable-error-' + sFieldsContainerID ).length > 0 )
						nodeFieldsContainer.find( '#repeatable-error-' + sFieldsContainerID ).replaceWith( nodeMessage );
					else
						nodeLastRepeaterButtons.before( nodeMessage );
					nodeMessage.delay( 2000 ).fadeOut( 1000 );
					return;		
				}				
				
				/* Decrement the names and ids of the next following siblings. */
				nodeFieldContainer.nextAll().each( function() {
					$( this ).decrementIDAttribute( 'id' );
					$( this ).find( 'label' ).decrementIDAttribute( 'for' );
					$( this ).find( 'input,textarea,select' ).decrementIDAttribute( 'id' );
					$( this ).find( 'input,textarea,select' ).decrementNameAttribute( 'name' );																	
				});

				/* Call the registered callback functions */
				nodeFieldContainer.callBackRemoveRepeatableField( nodeFieldContainer.data( 'type' ), nodeFieldContainer.attr( 'id' ) );	
			
				/* Remove the field */
				nodeFieldContainer.remove();
				
				/* Count the remaining Remove buttons and if it is one, disable the visibility of it */
				var nodeRemoveButtons = nodeFieldsContainer.find( '.repeatable-field-remove' );
				if ( nodeRemoveButtons.length == 1 ) nodeRemoveButtons.css( 'display', 'none' );
					
			};
				
		}( jQuery ));	
		"; echo "<script type='text/javascript' class='admin-page-framework-repeatable-fields-plugin'>{$sScript}</script>"; } public function _replyToAddAttributeUpdaterjQueryPlugin() { $sScript = "
		/**
		 * Attribute increment/decrement jQuery Plugin
		 */		
		(function ( $ ) {
		
			/**
			 * Increments a first found digit with the prefix of underscore in a specified attribute value.
			 */
			$.fn.incrementIDAttribute = function( sAttribute ) {				
				return this.attr( sAttribute, function( iIndex, sValue ) {	
					return updateID( iIndex, sValue, 1 );
				}); 
			};
			/**
			 * Increments a first found digit enclosed in [] in a specified attribute value.
			 */
			$.fn.incrementNameAttribute = function( sAttribute ) {				
				return this.attr( sAttribute, function( iIndex, sValue ) {	
					return updateName( iIndex, sValue, 1 );
				}); 
			};
	
			/**
			 * Decrements a first found digit with the prefix of underscore in a specified attribute value.
			 */
			$.fn.decrementIDAttribute = function( sAttribute ) {
				return this.attr( sAttribute, function( iIndex, sValue ) {
					return updateID( iIndex, sValue, -1 );
				}); 
			};			
			/**
			 * Decrements a first found digit enclosed in [] in a specified attribute value.
			 */
			$.fn.decrementNameAttribute = function( sAttribute ) {
				return this.attr( sAttribute, function( iIndex, sValue ) {
					return updateName( iIndex, sValue, -1 );
				}); 
			};				
			
			/* Sets the current index to the ID attribute */
			$.fn.setIndexIDAttribute = function( sAttribute, iIndex ){
				return this.attr( sAttribute, function( i, sValue ) {
					return updateID( iIndex, sValue, 0 );
				});
			};
			/* Sets the current index to the name attribute */
			$.fn.setIndexNameAttribute = function( sAttribute, iIndex ){
				return this.attr( sAttribute, function( i, sValue ) {
					return updateName( iIndex, sValue, 0 );
				});
			};		
			
			/* Local Function Literals */
			var updateID = function( iIndex, sID, bIncrement ) {
				if ( typeof sID === 'undefined' ) return sID;
				return sID.replace( /_((\d+))(?=(_|$))/, function ( fullMatch, n ) {
					if ( bIncrement === 1 )
						return '_' + ( Number(n) + 1 );
					else if ( bIncrement === -1 )
						return '_' + ( Number(n) - 1 );
					else 
						return '_' + ( iIndex );
					// return '_' + ( Number(n) + ( bIncrement === 1 ? 1 : -1 ) );
				});
			}
			var updateName = function( iIndex, sName, bIncrement ) {
				if ( typeof sName === 'undefined' ) return sName;
				return sName.replace( /\[((\d+))(?=\])/, function ( fullMatch, n ) {	
					if ( bIncrement === 1 )
						return '[' + ( Number(n) + 1 );
					else if ( bIncrement === -1 )
						return '[' + ( Number(n) - 1 );
					else 
						return '[' + ( iIndex );
					// return '[' + ( Number(n) + ( bIncrement === 1 ? 1 : -1 ) );
				});
			}
				
		}( jQuery ));"; echo "<script type='text/javascript' class='admin-page-framework-attribute-updater'>{$sScript}</script>"; } public function _replyToAddRegisterCallbackjQueryPlugin() { $sScript = "
			(function ( $ ) {
				
				// The method that gets triggered when a repeatable field add button is pressed.
				$.fn.callBackAddRepeatableField = function( sFieldType, sID ) {
					var nodeThis = this;
					if ( ! $.fn.aAPFAddRepeatableFieldCallbacks ) $.fn.aAPFAddRepeatableFieldCallbacks = [];
					$.fn.aAPFAddRepeatableFieldCallbacks.forEach( function( hfCallback ) {
						if ( jQuery.isFunction( hfCallback ) ) hfCallback( nodeThis, sFieldType, sID );
					});
				};
				
				// The method that gets triggered when a repeatable field remove button is pressed.
				$.fn.callBackRemoveRepeatableField = function( sFieldType, sID ) {
					var nodeThis = this;
					if ( ! $.fn.aAPFRemoveRepeatableFieldCallbacks ) $.fn.aAPFRemoveRepeatableFieldCallbacks = [];
					$.fn.aAPFRemoveRepeatableFieldCallbacks.forEach( function( hfCallback ) {
						if ( jQuery.isFunction( hfCallback ) ) hfCallback( nodeThis, sFieldType, sID );
					});
				};

				// The method that gets triggered when a sortable field is dropped and the sort event occurred
				$.fn.callBackSortedFields = function( sFieldType, sID ) {
					var nodeThis = this;
					if ( ! $.fn.aAPFSortedFieldsCallbacks ) $.fn.aAPFSortedFieldsCallbacks = [];
					$.fn.aAPFSortedFieldsCallbacks.forEach( function( hfCallback ) {
						if ( jQuery.isFunction( hfCallback ) ) hfCallback( nodeThis, sFieldType, sID );
					});
				};
				
				// The method that registers callbacks. This will be called in field type definition class.
				$.fn.registerAPFCallback = function( oOptions ) {
					
					// This is the easiest way to have default options.
					var oSettings = $.extend({
						// The user specifies the settings with the following options.
						added_repeatable_field: function() {},
						removed_repeatable_field: function() {},
						sorted_fields: function() {},
					}, oOptions );

					// Set up arrays to store callback functions
					if( ! $.fn.aAPFAddRepeatableFieldCallbacks ) $.fn.aAPFAddRepeatableFieldCallbacks = [];
					if( ! $.fn.aAPFRemoveRepeatableFieldCallbacks ) $.fn.aAPFRemoveRepeatableFieldCallbacks = [];
					if( ! $.fn.aAPFSortedFieldsCallbacks ) $.fn.aAPFSortedFieldsCallbacks = [];

					// Store the callback functions
					$.fn.aAPFAddRepeatableFieldCallbacks.push( oSettings.added_repeatable_field );
					$.fn.aAPFRemoveRepeatableFieldCallbacks.push( oSettings.removed_repeatable_field );
					$.fn.aAPFSortedFieldsCallbacks.push( oSettings.sorted_fields );
					
					return;

				};
				
			}( jQuery ));"; echo "<script type='text/javascript' class='admin-page-framework-register-callback'>{$sScript}</script>"; } public function _replyToAddUtilityPlugins() { echo "<script type='text/javascript' class='admin-page-framework-utility-plugins'>
			(function($) {
				$.fn.reverse = [].reverse;
			
				$.fn.formatPrintText = function() {
					var aArgs = arguments;
					return aArgs[ 0 ].replace( /{(\d+)}/g, function( match, number ) {
						return typeof aArgs[ parseInt( number ) + 1 ] != 'undefined'
							? aArgs[ parseInt( number ) + 1 ]
							: match
					;});
				};
			})(jQuery);		
		</script>"; } public function _replyToAddSortableFieldPlugin() { wp_enqueue_script( 'jquery-ui-sortable' ); echo "<script type='text/javascript' class='admin-page-framework-sortable-field-plugin'>
			(function($) {
			var dragging, placeholders = $();
			$.fn.sortable = function(options) {
				var method = String(options);
				options = $.extend({
					connectWith: false
				}, options);
				return this.each(function() {
					if (/^enable|disable|destroy$/.test(method)) {
						var items = $(this).children($(this).data('items')).attr('draggable', method == 'enable');
						if (method == 'destroy') {
							items.add(this).removeData('connectWith items')
								.off('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s');
						}
						return;
					}
					var isHandle, index, items = $(this).children(options.items);
					var placeholder = $('<' + (/^ul|ol$/i.test(this.tagName) ? 'li' : 'div') + ' class=\"sortable-placeholder\">');
					items.find(options.handle).mousedown(function() {
						isHandle = true;
					}).mouseup(function() {
						isHandle = false;
					});
					$(this).data('items', options.items)
					placeholders = placeholders.add(placeholder);
					if (options.connectWith) {
						$(options.connectWith).add(this).data('connectWith', options.connectWith);
					}
					items.attr('draggable', 'true').on('dragstart.h5s', function(e) {
						if (options.handle && !isHandle) {
							return false;
						}
						isHandle = false;
						var dt = e.originalEvent.dataTransfer;
						dt.effectAllowed = 'move';
						dt.setData('Text', 'dummy');
						index = (dragging = $(this)).addClass('sortable-dragging').index();
					}).on('dragend.h5s', function() {
						dragging.removeClass('sortable-dragging').show();
						placeholders.detach();
						if (index != dragging.index()) {
							items.parent().trigger('sortupdate', {item: dragging});
						}
						dragging = null;
					}).not('a[href], img').on('selectstart.h5s', function() {
						this.dragDrop && this.dragDrop();
						return false;
					}).end().add([this, placeholder]).on('dragover.h5s dragenter.h5s drop.h5s', function(e) {
						if (!items.is(dragging) && options.connectWith !== $(dragging).parent().data('connectWith')) {
							return true;
						}
						if (e.type == 'drop') {
							e.stopPropagation();
							placeholders.filter(':visible').after(dragging);
							return false;
						}
						e.preventDefault();
						e.originalEvent.dataTransfer.dropEffect = 'move';
						if (items.is(this)) {
							if (options.forcePlaceholderSize) {
								placeholder.height(dragging.outerHeight());
							}
							dragging.hide();
							$(this)[placeholder.index() < $(this).index() ? 'after' : 'before'](placeholder);
							placeholders.not(placeholder).detach();
						} else if (!placeholders.is(this) && !$(this).children(options.items).length) {
							placeholders.detach();
							$(this).append(placeholder);
						}
						return false;
					});
				});
			};
			})(jQuery);
		</script>"; } private function _getSortableFieldEnablerScript( $strFieldsContainerID ) { return "<script type='text/javascript' class='admin-page-framework-sortable-field-enabler-script'>
				jQuery( document ).ready( function() {

					jQuery( '#{$strFieldsContainerID}.sortable' ).sortable(
						{	items: '> div:not( .disabled )', }	// the options for the sortable plugin
					).bind( 'sortupdate', function() {
						
						/* Rename the ids and names */
						var nodeFields = jQuery( this ).children( 'div' );
						var iCount = 1;
						var iMaxCount = nodeFields.length;

						jQuery( jQuery( this ).children( 'div' ).reverse() ).each( function() {	// reverse is needed for radio buttons since they loose the selections when updating the IDs

							var iIndex = ( iMaxCount - iCount );
							jQuery( this ).setIndexIDAttribute( 'id', iIndex );
							jQuery( this ).find( 'label' ).setIndexIDAttribute( 'for', iIndex );
							jQuery( this ).find( 'input,textarea,select' ).setIndexIDAttribute( 'id', iIndex );
							jQuery( this ).find( 'input,textarea,select' ).setIndexNameAttribute( 'name', iIndex );

							/* Radio buttons loose their selections when IDs and names are updated, so reassign them */
							jQuery( this ).find( 'input[type=radio]' ).each( function() {	
								var sAttr = jQuery( this ).prop( 'checked' );
								if ( typeof sAttr !== 'undefined' && sAttr !== false) 
									jQuery( this ).attr( 'checked', 'Checked' );
							});
								
							iCount++;
						});
						
						/* It seems radio buttons need to be taken cared of again. Otherwise, the checked items will be gone. */
						jQuery( this ).find( 'input[type=radio][checked=checked]' ).attr( 'checked', 'Checked' );	
						
						/* Callback the registered functions */
						jQuery( this ).callBackSortedFields( jQuery( this ).data( 'type' ), jQuery( this ).attr( 'id' ) );
						
					}); 		
					
				});
			</script>"; } private function _getTableRowIDSetterScript( $sTagID ) { return "<script type='text/javascript' class='admin-page-framework-table-row-id-setter-script'>
			jQuery( '#fieldset-{$sTagID}' ).closest( 'tr' ).attr( 'id', 'fieldrow-{$sTagID}' );
		</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_Base' ) ) : abstract class AdminPageFramework_FieldType_Base extends AdminPageFramework_WPUtility { public $_sFieldSetType = ''; public $aFieldTypeSlugs = array( 'default' ); protected $aDefaultKeys = array(); protected static $_aDefaultKeys = array( 'value' => null, 'default' => null, 'repeatable' => false, 'sortable' => false, 'label' => '', 'delimiter' => '', 'before_input' => '', 'after_input' => '', 'before_label' => null, 'after_label' => null, 'before_field' => null, 'after_field' => null, 'label_min_width' => 140, 'field_id' => null, 'page_slug' => null, 'section_id' => null, 'before_fields' => null, 'after_fields' => null, 'attributes' => array( 'disabled' => '', 'class' => '', 'fieldset' => array(), 'fields' => array(), 'field' => array(), ), ); protected $oMsg; function __construct( $asClassName, $asFieldTypeSlug=null, $oMsg=null, $bAutoRegister=true ) { $this->aFieldTypeSlugs = empty( $asFieldTypeSlug ) ? $this->aFieldTypeSlugs : ( array ) $asFieldTypeSlug; $this->oMsg = $oMsg ? $oMsg : AdminPageFramework_Message::instantiate(); if ( $bAutoRegister ) { foreach( ( array ) $asClassName as $sClassName ) add_filter( "field_types_{$sClassName}", array( $this, 'replyToRegisterInputFieldType' ) ); } } public function replyToRegisterInputFieldType( $aFieldDefinitions ) { foreach ( $this->aFieldTypeSlugs as $sFieldTypeSlug ) $aFieldDefinitions[ $sFieldTypeSlug ] = $this->getDefinitionArray( $sFieldTypeSlug ); return $aFieldDefinitions; } public function getDefinitionArray( $sFieldTypeSlug='' ) { return array( 'sFieldTypeSlug' => $sFieldTypeSlug, 'aFieldTypeSlugs' => $this->aFieldTypeSlugs, 'hfRenderField' => array( $this, "_replyToGetField" ), 'hfGetScripts' => array( $this, "_replyToGetScripts" ), 'hfGetStyles' => array( $this, "_replyToGetStyles" ), 'hfGetIEStyles' => array( $this, "_replyToGetInputIEStyles" ), 'hfFieldLoader' => array( $this, "_replyToFieldLoader" ), 'hfFieldSetTypeSetter' => array( $this, "_replyToFieldTypeSetter" ), 'aEnqueueScripts' => $this->_replyToGetEnqueuingScripts(), 'aEnqueueStyles' => $this->_replyToGetEnqueuingStyles(), 'aDefaultKeys' => $this->uniteArrays( $this->aDefaultKeys, self::$_aDefaultKeys ), ); } public function _replyToGetField( $aField ) { return ''; } public function _replyToGetScripts() { return ''; } public function _replyToGetInputIEStyles() { return ''; } public function _replyToGetStyles() { return ''; } public function _replyToFieldLoader() {} public function _replyToFieldTypeSetter( $sFieldSetType='' ) { $this->_sFieldSetType = $sFieldSetType; } protected function _replyToGetEnqueuingScripts() { return array(); } protected function _replyToGetEnqueuingStyles() { return array(); } protected function getFieldElementByKey( $asElement, $sKey, $asDefault='' ) { if ( ! is_array( $asElement ) || ! isset( $sKey ) ) return $asElement; $aElements = &$asElement; return isset( $aElements[ $sKey ] ) ? $aElements[ $sKey ] : $asDefault; } protected function enqueueMediaUploader() { add_filter( 'media_upload_tabs', array( $this, '_replyToRemovingMediaLibraryTab' ) ); wp_enqueue_script( 'jquery' ); wp_enqueue_script( 'thickbox' ); wp_enqueue_style( 'thickbox' ); if ( function_exists( 'wp_enqueue_media' ) ) wp_enqueue_media(); else wp_enqueue_script( 'media-upload' ); if ( in_array( $GLOBALS['pagenow'], array( 'media-upload.php', 'async-upload.php', ) ) ) add_filter( 'gettext', array( $this, '_replyToReplaceThickBoxText' ) , 1, 2 ); } public function _replyToReplaceThickBoxText( $sTranslated, $sText ) { if ( ! in_array( $GLOBALS['pagenow'], array( 'media-upload.php', 'async-upload.php' ) ) ) return $sTranslated; if ( $sText != 'Insert into Post' ) return $sTranslated; if ( $this->getQueryValueInURLByKey( wp_get_referer(), 'referrer' ) != 'admin_page_framework' ) return $sTranslated; if ( isset( $_GET['button_label'] ) ) return $_GET['button_label']; return $this->oProp->sThickBoxButtonUseThis ? $this->oProp->sThickBoxButtonUseThis : $this->oMsg->__( 'use_this_image' ); } public function _replyToRemovingMediaLibraryTab( $aTabs ) { if ( ! isset( $_REQUEST['enable_external_source'] ) ) return $aTabs; if ( ! $_REQUEST['enable_external_source'] ) unset( $aTabs['type_url'] ); return $aTabs; } protected function _getScript_CustomMediaUploaderObject() { if ( ! function_exists( 'wp_enqueue_media' ) ) return ""; $GLOBALS['aAdminPageFramework']['aLoadedCustomMediaUploaderObject'] = isset( $GLOBALS['aAdminPageFramework']['aLoadedCustomMediaUploaderObject'] ) ? $GLOBALS['aAdminPageFramework']['aLoadedCustomMediaUploaderObject'] : array(); if ( isset( $GLOBALS['aAdminPageFramework']['aLoadedCustomMediaUploaderObject'][ $this->_sFieldSetType ] ) ) return ''; $GLOBALS['aAdminPageFramework']['aLoadedCustomMediaUploaderObject'][ $this->_sFieldSetType ] = true; return "
			getAPFCustomMediaUploaderSelectObject = function() {
				return wp.media.view.MediaFrame.Select.extend({

					initialize: function() {
						wp.media.view.MediaFrame.prototype.initialize.apply( this, arguments );

						_.defaults( this.options, {
							multiple:  true,
							editing:   false,
							state:    'insert'
						});

						this.createSelection();
						this.createStates();
						this.bindHandlers();
						this.createIframeStates();
					},

					createStates: function() {
						var options = this.options;

						// Add the default states.
						this.states.add([
							// Main states.
							new wp.media.controller.Library({
								id:         'insert',
								title:      'Insert Media',
								priority:   20,
								toolbar:    'main-insert',
								filterable: 'image',
								library:    wp.media.query( options.library ),
								multiple:   options.multiple ? 'reset' : false,
								editable:   true,

								// If the user isn't allowed to edit fields,
								// can they still edit it locally?
								allowLocalEdits: true,

								// Show the attachment display settings.
								displaySettings: true,
								// Update user settings when users adjust the
								// attachment display settings.
								displayUserSettings: true
							}),

							// Embed states.
							new wp.media.controller.Embed(),
						]);


						if ( wp.media.view.settings.post.featuredImageId ) {
							this.states.add( new wp.media.controller.FeaturedImage() );
						}
					},

					bindHandlers: function() {
						// from Select
						this.on( 'router:create:browse', this.createRouter, this );
						this.on( 'router:render:browse', this.browseRouter, this );
						this.on( 'content:create:browse', this.browseContent, this );
						this.on( 'content:render:upload', this.uploadContent, this );
						this.on( 'toolbar:create:select', this.createSelectToolbar, this );
						//

						this.on( 'menu:create:gallery', this.createMenu, this );
						this.on( 'toolbar:create:main-insert', this.createToolbar, this );
						this.on( 'toolbar:create:main-gallery', this.createToolbar, this );
						this.on( 'toolbar:create:featured-image', this.featuredImageToolbar, this );
						this.on( 'toolbar:create:main-embed', this.mainEmbedToolbar, this );

						var handlers = {
								menu: {
									'default': 'mainMenu'
								},

								content: {
									'embed':          'embedContent',
									'edit-selection': 'editSelectionContent'
								},

								toolbar: {
									'main-insert':      'mainInsertToolbar'
								}
							};

						_.each( handlers, function( regionHandlers, region ) {
							_.each( regionHandlers, function( callback, handler ) {
								this.on( region + ':render:' + handler, this[ callback ], this );
							}, this );
						}, this );
					},

					// Menus
					mainMenu: function( view ) {
						view.set({
							'library-separator': new wp.media.View({
								className: 'separator',
								priority: 100
							})
						});
					},

					// Content
					embedContent: function() {
						var view = new wp.media.view.Embed({
							controller: this,
							model:      this.state()
						}).render();

						this.content.set( view );
						view.url.focus();
					},

					editSelectionContent: function() {
						var state = this.state(),
							selection = state.get('selection'),
							view;

						view = new wp.media.view.AttachmentsBrowser({
							controller: this,
							collection: selection,
							selection:  selection,
							model:      state,
							sortable:   true,
							search:     false,
							dragInfo:   true,

							AttachmentView: wp.media.view.Attachment.EditSelection
						}).render();

						view.toolbar.set( 'backToLibrary', {
							text:     'Return to Library',
							priority: -100,

							click: function() {
								this.controller.content.mode('browse');
							}
						});

						// Browse our library of attachments.
						this.content.set( view );
					},

					// Toolbars
					selectionStatusToolbar: function( view ) {
						var editable = this.state().get('editable');

						view.set( 'selection', new wp.media.view.Selection({
							controller: this,
							collection: this.state().get('selection'),
							priority:   -40,

							// If the selection is editable, pass the callback to
							// switch the content mode.
							editable: editable && function() {
								this.controller.content.mode('edit-selection');
							}
						}).render() );
					},

					mainInsertToolbar: function( view ) {
						var controller = this;

						this.selectionStatusToolbar( view );

						view.set( 'insert', {
							style:    'primary',
							priority: 80,
							text:     'Select Image',
							requires: { selection: true },

							click: function() {
								var state = controller.state(),
									selection = state.get('selection');

								controller.close();
								state.trigger( 'insert', selection ).reset();
							}
						});
					},

					featuredImageToolbar: function( toolbar ) {
						this.createSelectToolbar( toolbar, {
							text:  'Set Featured Image',
							state: this.options.state || 'upload'
						});
					},

					mainEmbedToolbar: function( toolbar ) {
						toolbar.view = new wp.media.view.Toolbar.Embed({
							controller: this,
							text: 'Insert Image'
						});
					}		
				});
			}
		"; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType' ) ) : abstract class AdminPageFramework_FieldType extends AdminPageFramework_FieldType_Base { public function _replyToFieldLoader() { $this->setUp(); } public function _replyToGetScripts() { return $this->getScripts(); } public function _replyToGetInputIEStyles() { return $this->getIEStyles(); } public function _replyToGetStyles() { return $this->getStyles(); } public function _replyToGetField( $aField ) { return $this->getField( $aField ); } protected function _replyToGetEnqueuingScripts() { return $this->getEnqueuingScripts(); } protected function _replyToGetEnqueuingStyles() { return $this->getEnqueuingStyles(); } public $aFieldTypeSlugs = array(); protected $aDefaultKeys = array(); protected function setUp() {} protected function getScripts() { return ''; } protected function getIEStyles() { return ''; } protected function getStyles() { return ''; } protected function getField( $aField ) { return ''; } protected function getEnqueuingScripts() { return array(); } protected function getEnqueuingStyles() { return array(); } } endif;if ( ! class_exists( 'AdminPageFramework_Link_Base' ) ) : abstract class AdminPageFramework_Link_Base extends AdminPageFramework_WPUtility { protected function _setFooterInfoLeft( $aScriptInfo, &$sFooterInfoLeft ) { $sDescription = empty( $aScriptInfo['sDescription'] ) ? "" : "&#13;{$aScriptInfo['sDescription']}"; $sVersion = empty( $aScriptInfo['sVersion'] ) ? "" : "&nbsp;{$aScriptInfo['sVersion']}"; $sPluginInfo = empty( $aScriptInfo['sURI'] ) ? $aScriptInfo['sName'] : "<a href='{$aScriptInfo['sURI']}' target='_blank' title='{$aScriptInfo['sName']}{$sVersion}{$sDescription}'>{$aScriptInfo['sName']}</a>"; $sAuthorInfo = empty( $aScriptInfo['sAuthorURI'] ) ? $aScriptInfo['sAuthor'] : "<a href='{$aScriptInfo['sAuthorURI']}' target='_blank'>{$aScriptInfo['sAuthor']}</a>"; $sAuthorInfo = empty( $aScriptInfo['sAuthor'] ) ? $sAuthorInfo : ' by ' . $sAuthorInfo; $sFooterInfoLeft = $sPluginInfo . $sAuthorInfo; } protected function _setFooterInfoRight( $aScriptInfo, &$sFooterInfoRight ) { $sDescription = empty( $aScriptInfo['sDescription'] ) ? "" : "&#13;{$aScriptInfo['sDescription']}"; $sVersion = empty( $aScriptInfo['sVersion'] ) ? "" : "&nbsp;{$aScriptInfo['sVersion']}"; $sLibraryInfo = empty( $aScriptInfo['sURI'] ) ? $aScriptInfo['sName'] : "<a href='{$aScriptInfo['sURI']}' target='_blank' title='{$aScriptInfo['sName']}{$sVersion}{$sDescription}'>{$aScriptInfo['sName']}</a>"; $sFooterInfoRight = $this->oMsg->__( 'powered_by' ) . '&nbsp;' . $sLibraryInfo . ", <a href='http://wordpress.org' target='_blank' title='WordPress {$GLOBALS['wp_version']}'>WordPress</a>"; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_checkbox' ) ) : class AdminPageFramework_FieldType_checkbox extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'checkbox' ); protected $aDefaultKeys = array( ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Checkbox field type */
			.admin-page-framework-field input[type='checkbox'] {
				margin-right: 0.5em;
			}			
			.admin-page-framework-field-checkbox .admin-page-framework-input-label-container {
				padding-right: 1em;
			}			
		"; } public function _replyToGetField( $aField ) { $aOutput = array(); $asValue = $aField['attributes']['value']; foreach( ( array ) $aField['label'] as $sKey => $sLabel ) { $aInputAttributes = array( 'type' => 'checkbox', 'id' => $aField['input_id'] . '_' . $sKey, 'checked' => $this->getCorrespondingArrayValue( $asValue, $sKey, null ) == 1 ? 'checked' : '', 'value' => 1, 'name' => is_array( $aField['label'] ) ? "{$aField['attributes']['name']}[{$sKey}]" : $aField['attributes']['name'], ) + $this->getFieldElementByKey( $aField['attributes'], $sKey, $aField['attributes'] ) + $aField['attributes']; $aLabelAttributes = array( 'for' => $aInputAttributes['id'], 'class' => $aInputAttributes['disabled'] ? 'disabled' : '', ); $aOutput[] = $this->getFieldElementByKey( $aField['before_label'], $sKey ) . "<div class='admin-page-framework-input-label-container admin-page-framework-checkbox-label' style='min-width: {$aField['label_min_width']}px;'>" . "<label " . $this->generateAttributes( $aLabelAttributes ) . ">" . $this->getFieldElementByKey( $aField['before_input'], $sKey ) . "<span class='admin-page-framework-input-container'>" . "<input type='hidden' name='{$aInputAttributes['name']}' value='0' />" . "<input " . $this->generateAttributes( $aInputAttributes ) . " />" . "</span>" . "<span class='admin-page-framework-input-label-string'>" . $sLabel . "</span>" . $this->getFieldElementByKey( $aField['after_input'], $sKey ) . "</label>" . "</div>" . $this->getFieldElementByKey( $aField['after_label'], $sKey ); } return implode( PHP_EOL, $aOutput ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_color' ) ) : class AdminPageFramework_FieldType_color extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'color' ); protected $aDefaultKeys = array( 'attributes' => array( 'size' => 10, 'maxlength' => 400, 'value' => 'transparent', ), ); public function _replyToFieldLoader() { if ( version_compare( $GLOBALS['wp_version'], '3.5', '>=' ) ) { wp_enqueue_style( 'wp-color-picker' ); wp_enqueue_script( 'wp-color-picker' ); } else { wp_enqueue_style( 'farbtastic' ); wp_enqueue_script( 'farbtastic' ); } } public function _replyToGetStyles() { return "/* Color Picker */
			.repeatable .colorpicker {
				display: inline;
			}
			.admin-page-framework-field-color .wp-picker-container {
				vertical-align: middle;
			}
			.admin-page-framework-field-color .ui-widget-content {
				border: none;
				background: none;
				color: transparent;
			}
			.admin-page-framework-field-color .ui-slider-vertical {
				width: inherit;
				height: auto;
				margin-top: -11px;
			}
			.admin-page-framework-field-color .admin-page-framework-field .admin-page-framework-input-label-container {
				vertical-align: top; 
			}
			.admin-page-framework-field-color .admin-page-framework-repeatable-field-buttons {
				margin-top: 0;
			}
			" . PHP_EOL; } public function _replyToGetScripts() { $aJSArray = json_encode( $this->aFieldTypeSlugs ); return "
			registerAPFColorPickerField = function( sInputID ) {
				'use strict';
				/* This if statement checks if the color picker element exists within jQuery UI
				 If it does exist then we initialize the WordPress color picker on our text input field */
				if( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ){
					var myColorPickerOptions = {
						defaultColor: false,	// you can declare a default color here, or in the data-default-color attribute on the input				
						change: function(event, ui){},	// a callback to fire whenever the color changes to a valid color. reference : http://automattic.github.io/Iris/			
						clear: function() {},	// a callback to fire when the input is emptied or an invalid color
						hide: true,	// hide the color picker controls on load
						palettes: true	// show a group of common colors beneath the square or, supply an array of colors to customize further
					};			
					jQuery( '#' + sInputID ).wpColorPicker( myColorPickerOptions );
				}
				else {
					/* We use farbtastic if the WordPress color picker widget doesn't exist */
					jQuery( '#color_' + sInputID ).farbtastic( '#' + sInputID );
				}
			}
			
			/*	The below function will be triggered when a new repeatable field is added. Since the APF repeater script does not
				renew the color piker element (while it does on the input tag value), the renewal task must be dealt here separately. */
			jQuery( document ).ready( function(){
				jQuery().registerAPFCallback( {				
					added_repeatable_field: function( node, sFieldType, sFieldTagID ) {
			
						/* If it is not the color field type, do nothing. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;
						
						/* If the input tag is not found, do nothing  */
						var nodeNewColorInput = node.find( 'input.input_color' );
						if ( nodeNewColorInput.length <= 0 ) return;
						
						var sInputID = nodeNewColorInput.attr( 'id' );
		
						/* Reset the value of the color picker */
						var sInputValue = nodeNewColorInput.val() ? nodeNewColorInput.val() : 'transparent';	// For WP 3.4.x or below
						var sInputStyle = sInputValue != 'transparent' && nodeNewColorInput.attr( 'style' ) ? nodeNewColorInput.attr( 'style' ) : '';
						nodeNewColorInput.val( sInputValue );	// set the default value	
						nodeNewColorInput.attr( 'style', sInputStyle );	// remove the background color set to the input field ( for WP 3.4.x or below )						 
						
						/* Replace the old color picker elements with the new one */
						nodeIris = node.find( '#' + sInputID ).closest( '.wp-picker-container' );	
						if ( nodeIris.length > 0 ) {	// WP 3.5+
							jQuery( nodeIris ).replaceWith( nodeNewColorInput );
						} 
						else {	// WP 3.4.x -				
							node.find( '.colorpicker' ).replaceWith( '<div class=\'colorpicker\' id=\'color_' + sInputID + '\'></div>' );	
						}
					
						/* Bind the color picker script */					
						registerAPFColorPickerField( sInputID );						
						
					}
				});
			});
		"; } public function _replyToGetField( $aField ) { $aField['attributes'] = array( 'color' => $aField['value'], 'type' => 'text', 'class' => trim( 'input_color ' . $aField['attributes']['class'] ), ) + $aField['attributes']; return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . "<input " . $this->generateAttributes( $aField['attributes'] ) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "<div class='colorpicker' id='color_{$aField['input_id']}'></div>" . $this->_getColorPickerEnablerScript( "{$aField['input_id']}" ) . "</div>" . $aField['after_label']; } private function _getColorPickerEnablerScript( $sInputID ) { return "<script type='text/javascript' class='color-picker-enabler-script'>
					jQuery( document ).ready( function(){
						registerAPFColorPickerField( '{$sInputID}' );
					});
				</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_default' ) ) : class AdminPageFramework_FieldType_default extends AdminPageFramework_FieldType_Base { public $aDefaultKeys = array( ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return ""; } public function _replyToGetField( $aField ) { return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . $aField['value'] . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label'] ; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_hidden' ) ) : class AdminPageFramework_FieldType_hidden extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'hidden' ); protected $aDefaultKeys = array(); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return ""; } public function _replyToGetField( $aField ) { return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . "<input " . $this->generateAttributes( $aField['attributes'] ) . " />" . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label']; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_image' ) ) : class AdminPageFramework_FieldType_image extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'image', ); protected $aDefaultKeys = array( 'attributes_to_store' => array(), 'show_preview' => true, 'allow_external_source' => true, 'attributes' => array( 'input' => array( 'size' => 40, 'maxlength' => 400, ), 'button' => array( ), 'preview' => array( ), ), ); public function _replyToFieldLoader() { $this->enqueueMediaUploader(); } public function _replyToGetScripts() { return $this->_getScript_CustomMediaUploaderObject() . PHP_EOL . $this->_getScript_ImageSelector( "admin_page_framework", $this->oMsg->__( 'upload_image' ), $this->oMsg->__( 'use_this_image' ) ) . PHP_EOL . $this->_getScript_RegisterCallbacks(); } protected function _getScript_RegisterCallbacks() { $aJSArray = json_encode( $this->aFieldTypeSlugs ); return"
			jQuery( document ).ready( function(){
		
				jQuery().registerAPFCallback( {				
					added_repeatable_field: function( node, sFieldType, sFieldTagID ) {
						
						/* If it is not the image field type, do nothing. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;
											
						/* If the uploader buttons are not found, do nothing */
						if ( node.find( '.select_image' ).length <= 0 )  return;
						
						/* Remove the value of the cloned preview element */
						node.find( '.image_preview' ).hide();					// for the image field type, hide the preview element
						node.find( '.image_preview img' ).attr( 'src', '' );	// for the image field type, empty the src property for the image uploader field
						
						/* Increment the ids of the next all (including this one) uploader buttons and the preview elements ( the input values are already dealt by the framework repeater script ) */
						var nodeFieldContainer = node.closest( '.admin-page-framework-field' );
						nodeFieldContainer.nextAll().andSelf().each( function() {

							nodeButton = jQuery( this ).find( '.select_image' );							
							nodeButton.incrementIDAttribute( 'id' );
							jQuery( this ).find( '.image_preview' ).incrementIDAttribute( 'id' );
							jQuery( this ).find( '.image_preview img' ).incrementIDAttribute( 'id' );
							
							/* Rebind the uploader script to each button. The previously assigned ones also need to be renewed; 
							 * otherwise, the script sets the preview image in the wrong place. */						
							var nodeImageInput = jQuery( this ).find( '.image-field input' );
							if ( nodeImageInput.length <= 0 ) return true;
							
							var fExternalSource = jQuery( nodeButton ).attr( 'data-enable_external_source' );
							setAPFImageUploader( nodeImageInput.attr( 'id' ), true, fExternalSource );	
							
						});
					},
					removed_repeatable_field: function( node, sFieldType, sFieldTagID ) {
						
						/* If it is not the color field type, do nothing. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;
											
						/* If the uploader buttons are not found, do nothing */
						if ( node.find( '.select_image' ).length <= 0 )  return;						
						
						/* Decrement the ids of the next all (including this one) uploader buttons and the preview elements. ( the input values are already dealt by the framework repeater script ) */
						var nodeFieldContainer = node.closest( '.admin-page-framework-field' );
						nodeFieldContainer.nextAll().andSelf().each( function() {
							
							nodeButton = jQuery( this ).find( '.select_image' );							
							nodeButton.decrementIDAttribute( 'id' );
							jQuery( this ).find( '.image_preview' ).decrementIDAttribute( 'id' );
							jQuery( this ).find( '.image_preview img' ).decrementIDAttribute( 'id' );
							
							/* Rebind the uploader script to each button. The previously assigned ones also need to be renewed; 
							 * otherwise, the script sets the preview image in the wrong place. */						
							var nodeImageInput = jQuery( this ).find( '.image-field input' );
							if ( nodeImageInput.length <= 0 ) return true;
							
							var fExternalSource = jQuery( nodeButton ).attr( 'data-enable_external_source' );
							setAPFImageUploader( nodeImageInput.attr( 'id' ), true, fExternalSource );	
							
						});
						
					},
					sorted_fields : function( node, sFieldType, sFieldsTagID ) {	// on contrary to repeatable callbacks, the _fields_ container node and its ID will be passed.

						/* 1. Return if it is not the type. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;	/* If it is not the color field type, do nothing. */						
						if ( node.find( '.select_image' ).length <= 0 )  return;	/* If the uploader buttons are not found, do nothing */
						
						/* 2. Update the Select File button */
						var iCount = 0;
						node.children( '.admin-page-framework-field' ).each( function() {
							
							nodeButton = jQuery( this ).find( '.select_image' );
							
							/* 2-1. Set the current iteration index to the button ID, and the image preview elements */
							nodeButton.setIndexIDAttribute( 'id', iCount );	
							jQuery( this ).find( '.image_preview' ).setIndexIDAttribute( 'id', iCount );
							jQuery( this ).find( '.image_preview img' ).setIndexIDAttribute( 'id', iCount );
							
							/* 2-2. Rebuind the uploader script to the button */
							var nodeImageInput = jQuery( this ).find( '.image-field input' );
							if ( nodeImageInput.length <= 0 ) return true;
							setAPFImageUploader( nodeImageInput.attr( 'id' ), true, jQuery( nodeButton ).attr( 'data-enable_external_source' ) );
	
							iCount++;
						});
					},					
				});
			});" . PHP_EOL; } private function _getScript_ImageSelector( $sReferrer, $sThickBoxTitle, $sThickBoxButtonUseThis ) { if ( ! function_exists( 'wp_enqueue_media' ) ) return "
					jQuery( document ).ready( function(){
						/**
						 * Bind/rebinds the thickbox script the given selector element.
						 * The fMultiple parameter does not do anything. It is there to be consistent with the one for the WordPress version 3.5 or above.
						 */
						setAPFImageUploader = function( sInputID, fMultiple, fExternalSource ) {
							jQuery( '#select_image_' + sInputID ).unbind( 'click' );	// for repeatable fields
							jQuery( '#select_image_' + sInputID ).click( function() {
								var sPressedID = jQuery( this ).attr( 'id' );
								window.sInputID = sPressedID.substring( 13 );	// remove the select_image_ prefix and set a property to pass it to the editor callback method.
								window.original_send_to_editor = window.send_to_editor;
								window.send_to_editor = hfAPFSendToEditorImage;
								var fExternalSource = jQuery( this ).attr( 'data-enable_external_source' );
								tb_show( '{$sThickBoxTitle}', 'media-upload.php?post_id=1&amp;enable_external_source=' + fExternalSource + '&amp;referrer={$sReferrer}&amp;button_label={$sThickBoxButtonUseThis}&amp;type=image&amp;TB_iframe=true', false );
								return false;	// do not click the button after the script by returning false.									
							});	
						}			
						
						var hfAPFSendToEditorImage = function( sRawHTML ) {

							var sHTML = '<div>' + sRawHTML + '</div>';	// This is for the 'From URL' tab. Without the wrapper element. the below attr() method don't catch attributes.
							var src = jQuery( 'img', sHTML ).attr( 'src' );
							var alt = jQuery( 'img', sHTML ).attr( 'alt' );
							var title = jQuery( 'img', sHTML ).attr( 'title' );
							var width = jQuery( 'img', sHTML ).attr( 'width' );
							var height = jQuery( 'img', sHTML ).attr( 'height' );
							var classes = jQuery( 'img', sHTML ).attr( 'class' );
							var id = ( classes ) ? classes.replace( /(.*?)wp-image-/, '' ) : '';	// attachment ID	
							var sCaption = sRawHTML.replace( /\[(\w+).*?\](.*?)\[\/(\w+)\]/m, '$2' )
								.replace( /<a.*?>(.*?)<\/a>/m, '' );
							var align = sRawHTML.replace( /^.*?\[\w+.*?\salign=([\'\"])(.*?)[\'\"]\s.+$/mg, '$2' );	//\'\" syntax fixer
							var link = jQuery( sHTML ).find( 'a:first' ).attr( 'href' );

							// Escape the strings of some of the attributes.
							var sCaption = jQuery( '<div/>' ).text( sCaption ).html();
							var sAlt = jQuery( '<div/>' ).text( alt ).html();
							var title = jQuery( '<div/>' ).text( title ).html();						
				
							// If the user wants to save relevant attributes, set them.
							var sInputID = window.sInputID;	// window.sInputID should be assigned when the thickbox is opened.
				
							jQuery( '#' + sInputID ).val( src );	// sets the image url in the main text field. The url field is mandatory so it does not have the suffix.
							jQuery( '#' + sInputID + '_id' ).val( id );
							jQuery( '#' + sInputID + '_width' ).val( width );
							jQuery( '#' + sInputID + '_height' ).val( height );
							jQuery( '#' + sInputID + '_caption' ).val( sCaption );
							jQuery( '#' + sInputID + '_alt' ).val( sAlt );
							jQuery( '#' + sInputID + '_title' ).val( title );						
							jQuery( '#' + sInputID + '_align' ).val( align );						
							jQuery( '#' + sInputID + '_link' ).val( link );						
							
							// Update the preview
							jQuery( '#image_preview_' + sInputID ).attr( 'alt', alt );
							jQuery( '#image_preview_' + sInputID ).attr( 'title', title );
							jQuery( '#image_preview_' + sInputID ).attr( 'data-classes', classes );
							jQuery( '#image_preview_' + sInputID ).attr( 'data-id', id );
							jQuery( '#image_preview_' + sInputID ).attr( 'src', src );	// updates the preview image
							jQuery( '#image_preview_container_' + sInputID ).css( 'display', '' );	// updates the visibility
							jQuery( '#image_preview_' + sInputID ).show()	// updates the visibility
							
							// restore the original send_to_editor
							window.send_to_editor = window.original_send_to_editor;

							// close the thickbox
							tb_remove();	

						}
					});
				"; return "jQuery( document ).ready( function(){

				// Global Function Literal 
				/**
				 * Binds/rebinds the uploader button script to the specified element with the given ID.
				 */
				setAPFImageUploader = function( sInputID, fMultiple, fExternalSource ) {

					jQuery( '#select_image_' + sInputID ).unbind( 'click' );	// for repeatable fields
					jQuery( '#select_image_' + sInputID ).click( function( e ) {
						
						window.wpActiveEditor = null;						
						e.preventDefault();
						
						// If the uploader object has already been created, reopen the dialog
						if ( custom_uploader ) {
							custom_uploader.open();
							return;
						}					
						
						// Store the original select object in a global variable
						oAPFOriginalImageUploaderSelectObject = wp.media.view.MediaFrame.Select;
						
						// Assign a custom select object.
						wp.media.view.MediaFrame.Select = fExternalSource ? getAPFCustomMediaUploaderSelectObject() : oAPFOriginalImageUploaderSelectObject;
						var custom_uploader = wp.media({
							title: '{$sThickBoxTitle}',
							button: {
								text: '{$sThickBoxButtonUseThis}'
							},
							library     : { type : 'image' },
							multiple: fMultiple  // Set this to true to allow multiple files to be selected
						});
			
						// When the uploader window closes, 
						custom_uploader.on( 'close', function() {

							var state = custom_uploader.state();
							
							// Check if it's an external URL
							if ( typeof( state.props ) != 'undefined' && typeof( state.props.attributes ) != 'undefined' ) 
								var image = state.props.attributes;	
							
							// If the image variable is not defined at this point, it's an attachment, not an external URL.
							if ( typeof( image ) !== 'undefined'  ) {
								setPreviewElement( sInputID, image );
							} else {
								
								var selection = custom_uploader.state().get( 'selection' );
								selection.each( function( attachment, index ) {
									attachment = attachment.toJSON();
									if( index == 0 ){	
										// place first attachment in field
										setPreviewElement( sInputID, attachment );
									} else{
										
										var field_container = jQuery( '#' + sInputID ).closest( '.admin-page-framework-field' );
										var new_field = jQuery( this ).addAPFRepeatableField( field_container.attr( 'id' ) );
										var sInputIDOfNewField = new_field.find( 'input' ).attr( 'id' );
										setPreviewElement( sInputIDOfNewField, attachment );
			
									}
								});				
								
							}
							
							// Restore the original select object.
							wp.media.view.MediaFrame.Select = oAPFOriginalImageUploaderSelectObject;
											
						});
						
						// Open the uploader dialog
						custom_uploader.open();											
						return false;       
					});	
				
					var setPreviewElement = function( sInputID, image ) {

						// Escape the strings of some of the attributes.
						var sCaption = jQuery( '<div/>' ).text( image.caption ).html();
						var sAlt = jQuery( '<div/>' ).text( image.alt ).html();
						var title = jQuery( '<div/>' ).text( image.title ).html();
						
						// If the user want the attributes to be saved, set them in the input tags.
						jQuery( 'input#' + sInputID ).val( image.url );		// the url field is mandatory so it does not have the suffix.
						jQuery( 'input#' + sInputID + '_id' ).val( image.id );
						jQuery( 'input#' + sInputID + '_width' ).val( image.width );
						jQuery( 'input#' + sInputID + '_height' ).val( image.height );
						jQuery( 'input#' + sInputID + '_caption' ).val( sCaption );
						jQuery( 'input#' + sInputID + '_alt' ).val( sAlt );
						jQuery( 'input#' + sInputID + '_title' ).val( title );
						jQuery( 'input#' + sInputID + '_align' ).val( image.align );
						jQuery( 'input#' + sInputID + '_link' ).val( image.link );
						
						// Update up the preview
						jQuery( '#image_preview_' + sInputID ).attr( 'data-id', image.id );
						jQuery( '#image_preview_' + sInputID ).attr( 'data-width', image.width );
						jQuery( '#image_preview_' + sInputID ).attr( 'data-height', image.height );
						jQuery( '#image_preview_' + sInputID ).attr( 'data-caption', sCaption );
						jQuery( '#image_preview_' + sInputID ).attr( 'alt', sAlt );
						jQuery( '#image_preview_' + sInputID ).attr( 'title', title );
						jQuery( '#image_preview_' + sInputID ).attr( 'src', image.url );
						jQuery( '#image_preview_container_' + sInputID ).show();				
						
					}
				}		
			});
			"; } public function _replyToGetStyles() { return "/* Image Field Preview Container */
			.admin-page-framework-field .image_preview {
				border: none; 
				clear:both; 
				margin-top: 0.4em;
				margin-bottom: 0.8em;
				display: block; 
				
			}		
			@media only screen and ( max-width: 1200px ) {
				.admin-page-framework-field .image_preview {
					max-width: 600px;
				}
			} 
			@media only screen and ( max-width: 900px ) {
				.admin-page-framework-field .image_preview {
					max-width: 440px;
				}
			}	
			@media only screen and ( max-width: 600px ) {
				.admin-page-framework-field .image_preview {
					max-width: 300px;
				}
			}		
			@media only screen and ( max-width: 480px ) {
				.admin-page-framework-field .image_preview {
					max-width: 240px;
				}
			}
			@media only screen and ( min-width: 1200px ) {
				.admin-page-framework-field .image_preview {
					max-width: 600px;
				}
			}		 
			.admin-page-framework-field .image_preview img {		
				width: auto;
				height: auto; 
				max-width: 100%;
				display: block;
			}
			/* Image Uploader Input Field */
			.admin-page-framework-field-image input {
				margin-right: 0.5em;
				vertical-align: middle;	
			}
			/* Image Uploader Button */
			.select_image.button.button-small {
				margin-top: 0.1em;				
			}
		" . PHP_EOL; } public function _replyToGetField( $aField ) { $aOutput = array(); $iCountAttributes = count( ( array ) $aField['attributes_to_store'] ); $sCaptureAttribute = $iCountAttributes ? 'url' : ''; $sImageURL = $sCaptureAttribute ? ( isset( $aField['attributes']['value'][ $sCaptureAttribute ] ) ? $aField['attributes']['value'][ $sCaptureAttribute ] : "" ) : $aField['attributes']['value']; $aBaseAttributes = $aField['attributes']; unset( $aBaseAttributes['input'], $aBaseAttributes['button'], $aBaseAttributes['preview'], $aBaseAttributes['name'], $aBaseAttributes['value'], $aBaseAttributes['type'] ); $aInputAttributes = array( 'name' => $aField['attributes']['name'] . ( $iCountAttributes ? "[url]" : "" ), 'value' => $sImageURL, 'type' => 'text', ) + $aField['attributes']['input'] + $aBaseAttributes; $aButtonAtributes = $aField['attributes']['button'] + $aBaseAttributes; $aPreviewAtrributes = $aField['attributes']['preview'] + $aBaseAttributes; $aOutput[] = $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-input-container {$aField['type']}-field'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . "<input " . $this->generateAttributes( $aInputAttributes ) . " />" . $this->getExtraInputFields( $aField ) . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label'] . $this->_getPreviewContainer( $aField, $sImageURL, $aPreviewAtrributes ) . $this->_getUploaderButtonScript( $aField['input_id'], $aField['repeatable'], $aField['allow_external_source'], $aButtonAtributes ); ; return implode( PHP_EOL, $aOutput ); } protected function getExtraInputFields( &$aField ) { $aOutputs = array(); foreach( ( array ) $aField['attributes_to_store'] as $sAttribute ) $aOutputs[] = "<input " . $this->generateAttributes( array( 'id' => "{$aField['input_id']}_{$sAttribute}", 'type' => 'hidden', 'name' => "{$aField['_input_name']}[{$sAttribute}]", 'disabled' => isset( $aField['attributes']['diabled'] ) && $aField['attributes']['diabled'] ? 'Disabled' : '', 'value' => isset( $aField['attributes']['value'][ $sAttribute ] ) ? $aField['attributes']['value'][ $sAttribute ] : '', ) ) . "/>"; return implode( PHP_EOL, $aOutputs ); } protected function _getPreviewContainer( $aField, $sImageURL, $aPreviewAtrributes ) { if ( ! $aField['show_preview'] ) return ''; $sImageURL = $this->resolveSRC( $sImageURL, true ); return "<div " . $this->generateAttributes( array( 'id' => "image_preview_container_{$aField['input_id']}", 'class' => 'image_preview ' . ( isset( $aPreviewAtrributes['class'] ) ? $aPreviewAtrributes['class'] : '' ), 'style' => ( $sImageURL ? '' : "display: none; " ). ( isset( $aPreviewAtrributes['style'] ) ? $aPreviewAtrributes['style'] : '' ), ) + $aPreviewAtrributes ) . ">" . "<img src='{$sImageURL}' " . "id='image_preview_{$aField['input_id']}' " . "/>" . "</div>"; } protected function _getUploaderButtonScript( $sInputID, $bRpeatable, $bExternalSource, array $aButtonAttributes ) { $sButton = "<a " . $this->generateAttributes( array( 'id' => "select_image_{$sInputID}", 'href' => '#', 'class' => 'select_image button button-small ' . ( isset( $aButtonAttributes['class'] ) ? $aButtonAttributes['class'] : '' ), 'data-uploader_type' => function_exists( 'wp_enqueue_media' ) ? 1 : 0, 'data-enable_external_source' => $bExternalSource ? 1 : 0, ) + $aButtonAttributes ) . ">" . $this->oMsg->__( 'select_image' ) ."</a>"; $sScript = "
				if ( jQuery( 'a#select_image_{$sInputID}' ).length == 0 ) {
					jQuery( 'input#{$sInputID}' ).after( \"{$sButton}\" );
				}
				jQuery( document ).ready( function(){			
					setAPFImageUploader( '{$sInputID}', '{$bRpeatable}', '{$bExternalSource}' );
				});" . PHP_EOL; return "<script type='text/javascript' class='admin-page-framework-image-uploader-button'>" . $sScript . "</script>". PHP_EOL; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_radio' ) ) : class AdminPageFramework_FieldType_radio extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'radio' ); protected $aDefaultKeys = array( 'label' => array(), 'attributes' => array( ), ); public function _replyToFieldLoader() { } public function _replyToGetStyles() { return "/* Radio Field Type */
			.admin-page-framework-field input[type='radio'] {
				margin-right: 0.5em;
			}		
			.admin-page-framework-field-radio .admin-page-framework-input-label-container {
				padding-right: 1em;
			}			
			.admin-page-framework-field-radio .admin-page-framework-input-container {
				display: inline;
			}			
		"; } public function _replyToGetScripts() { $aJSArray = json_encode( $this->aFieldTypeSlugs ); return "			
			jQuery( document ).ready( function(){
				jQuery().registerAPFCallback( {				
					added_repeatable_field: function( nodeField, sFieldType, sFieldTagID ) {
			
						/* If it is not the color field type, do nothing. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;
													
						/* the checked state of radio buttons somehow lose their values so re-check them again */	
						nodeField.closest( '.admin-page-framework-fields' )
							.find( 'input[type=radio][checked=checked]' )
							.attr( 'checked', 'checked' );
							
						/* Rebind the checked attribute updater */
						nodeField.find( 'input[type=radio]' ).change( function() {
							jQuery( this ).closest( '.admin-page-framework-field' )
								.find( 'input[type=radio]' )
								.attr( 'checked', false );
							jQuery( this ).attr( 'checked', 'Checked' );
						});

					}
				});
			});
		"; } public function _replyToGetField( $aField ) { $aOutput = array(); $sValue = $aField['attributes']['value']; foreach( $aField['label'] as $sKey =>$sLabel ) { $aInputAttributes = array( 'type' => 'radio', 'checked' => $sValue == $sKey ? 'checked' : '', 'value' => $sKey, 'id' => $aField['input_id'] . '_' . $sKey, 'data-default' => $aField['default'], ) + $this->getFieldElementByKey( $aField['attributes'], $sKey, $aField['attributes'] ) + $aField['attributes']; $aLabelAttributes = array( 'for' => $aInputAttributes['id'], 'class' => $aInputAttributes['disabled'] ? 'disabled' : '', ); $aOutput[] = $this->getFieldElementByKey( $aField['before_label'], $sKey ) . "<div class='admin-page-framework-input-label-container admin-page-framework-radio-label' style='min-width: {$aField['label_min_width']}px;'>" . "<label " . $this->generateAttributes( $aLabelAttributes ) . ">" . $this->getFieldElementByKey( $aField['before_input'], $sKey ) . "<span class='admin-page-framework-input-container'>" . "<input " . $this->generateAttributes( $aInputAttributes ) . " />" . "</span>" . "<span class='admin-page-framework-input-label-string'>" . $sLabel . "</span>" . $this->getFieldElementByKey( $aField['after_input'], $sKey ) . "</label>" . "</div>" . $this->getFieldElementByKey( $aField['after_label'], $sKey ) ; } $aOutput[] = $this->_getUpdateCheckedScript( $aField['_field_container_id'] ); return implode( PHP_EOL, $aOutput ); } private function _getUpdateCheckedScript( $sFieldContainerID ) { return "<script type='text/javascript' class='radio-button-checked-attribute-updater'>
					jQuery( document ).ready( function(){
						jQuery( '#{$sFieldContainerID} input[type=radio]' ).change( function() {
							jQuery( this ).closest( '.admin-page-framework-field' ).find( 'input[type=radio]' ).attr( 'checked', false );
							jQuery( this ).attr( 'checked', 'Checked' );
						});
					});				
				</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_select' ) ) : class AdminPageFramework_FieldType_select extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'select' ); protected $aDefaultKeys = array( 'is_multiple' => '', 'attributes' => array( 'select' => array( 'size' => 1, 'autofocusNew' => '', 'multiple' => '', 'required' => '', ), 'optgroup' => array(), 'option' => array(), ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Select Field Type */
			.admin-page-framework-field-select .admin-page-framework-input-label-container {
				vertical-align: top; 
			}
			.admin-page-framework-field-select .admin-page-framework-input-label-container {
				padding-right: 1em;
			}
		"; } public function _replyToGetField( $aField ) { $aSelectAttributes = array( 'id' => $aField['input_id'], 'multiple' => $aField['is_multiple'] ? 'multiple' : $aField['attributes']['select']['multiple'], ) + $aField['attributes']['select']; $aSelectAttributes['name'] = empty( $aSelectAttributes['multiple'] ) ? $aField['_input_name'] : "{$aField['_input_name']}[]"; return $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-select-label' style='min-width: {$aField['label_min_width']}px;'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . "<span class='admin-page-framework-input-container'>" . "<select " . $this->generateAttributes( $aSelectAttributes ) . " >" . $this->_getOptionTags( $aField['input_id'], $aField['attributes'], $aField['label'] ) . "</select>" . "</span>" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label']; } protected function _getOptionTags( $sInputID, &$aAttributes, $aLabel ) { $aOutput = array(); $aValue = ( array ) $aAttributes['value']; foreach( $aLabel as $sKey => $asLabel ) { if ( is_array( $asLabel ) ) { $aOptGroupAttributes = isset( $aAttributes['optgroup'][ $sKey ] ) && is_array( $aAttributes['optgroup'][ $sKey ] ) ? $aAttributes['optgroup'][ $sKey ] + $aAttributes['optgroup'] : $aAttributes['optgroup']; $aOutput[] = "<optgroup label='{$sKey}'" . $this->generateAttributes( $aOptGroupAttributes ) . ">" . $this->_getOptionTags( $sInputID, $aAttributes, $asLabel ) . "</optgroup>"; continue; } $aValue = isset( $aAttributes['option'][ $sKey ]['value'] ) ? $aAttributes['option'][ $sKey ]['value'] : $aValue; $aOptionAttributes = array( 'id' => $sInputID . '_' . $sKey, 'value' => $sKey, 'selected' => in_array( ( string ) $sKey, $aValue ) ? 'Selected' : '', ) + ( isset( $aAttributes['option'][ $sKey ] ) && is_array( $aAttributes['option'][ $sKey ] ) ? $aAttributes['option'][ $sKey ] + $aAttributes['option'] : $aAttributes['option'] ); $aOutput[] = "<option " . $this->generateAttributes( $aOptionAttributes ) . " >" . $asLabel . "</option>"; } return implode( PHP_EOL, $aOutput ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_submit' ) ) : class AdminPageFramework_FieldType_submit extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'submit', ); protected $aDefaultKeys = array( 'redirect_url' => null, 'href' => null, 'reset' => null, 'attributes' => array( 'class' => 'button button-primary', ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Submit Buttons */
		.admin-page-framework-field input[type='submit'] {
			margin-bottom: 0.5em;
		}" . PHP_EOL; } public function _replyToGetField( $aField ) { $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->__( 'submit' ); $aInputAttributes = array( 'type' => 'submit', 'value' => ( $sValue = $this->_getInputFieldValueFromLabel( $aField ) ), ) + $aField['attributes'] + array( 'title' => $sValue, ); $aLabelAttributes = array( 'style' => $aField['label_min_width'] ? "min-width:{$aField['label_min_width']}px;" : null, 'for' => $aInputAttributes['id'], 'class' => $aInputAttributes['disabled'] ? 'disabled' : '', ); $aLabelContainerAttributes = array( 'style' => $aField['label_min_width'] ? "min-width:{$aField['label_min_width']}px;" : null, 'class' => 'admin-page-framework-input-label-container admin-page-framework-input-button-container admin-page-framework-input-container', ); return $aField['before_label'] . "<div " . $this->generateAttributes( $aLabelContainerAttributes ) . ">" . $this->_getExtraFieldsBeforeLabel( $aField ) . "<label " . $this->generateAttributes( $aLabelAttributes ) . ">" . $aField['before_input'] . $this->_getExtraInputFields( $aField ) . "<input " . $this->generateAttributes( $aInputAttributes ) . " />" . $aField['after_input'] . "</label>" . "</div>" . $aField['after_label']; } protected function _getExtraFieldsBeforeLabel( &$aField ) { return ''; } protected function _getExtraInputFields( &$aField ) { return "<input type='hidden' " . "name='__submit[{$aField['input_id']}][input_id]' " . "value='{$aField['input_id']}'" . "/>" . "<input type='hidden' " . "name='__submit[{$aField['input_id']}][field_id]' " . "value='{$aField['field_id']}'" . "/>" . "<input type='hidden' " . "name='__submit[{$aField['input_id']}][name]' " . "value='{$aField['_input_name_flat']}'" . "/>" . "<input type='hidden' " . "name='__submit[{$aField['input_id']}][section_id]' " . "value='" . ( isset( $aField['section_id'] ) && $aField['section_id'] ? $aField['section_id'] : '' ) . "'" . "/>" . ( $aField['redirect_url'] ? "<input type='hidden' " . "name='__submit[{$aField['input_id']}][redirect_url]' " . "value='{$aField['redirect_url']}'" . "/>" : "" ) . ( $aField['href'] ? "<input type='hidden' " . "name='__submit[{$aField['input_id']}][link_url]' " . "value='{$aField['href']}'" . "/>" : "" ) . ( $aField['reset'] && ( ! ( $bResetConfirmed = $this->_checkConfirmationDisplayed( $aField['reset'], $aField['_input_name_flat'] ) ) ) ? "<input type='hidden' " . "name='__submit[{$aField['input_id']}][is_reset]' " . "value='1'" . "/>" : "" ) . ( $aField['reset'] && $bResetConfirmed ? "<input type='hidden' " . "name='__submit[{$aField['input_id']}][reset_key]' " . "value='{$aField['reset']}'" . "/>" : "" ); } private function _checkConfirmationDisplayed( $sResetKey, $sFlatFieldName ) { if ( ! $sResetKey ) return false; $bResetConfirmed = get_transient( md5( "reset_confirm_" . $sFlatFieldName ) ) !== false ? true : false; if ( $bResetConfirmed ) delete_transient( md5( "reset_confirm_" . $sFlatFieldName ) ); return $bResetConfirmed; } protected function _getInputFieldValueFromLabel( $aField ) { if ( isset( $aField['value'] ) && $aField['value'] != '' ) return $aField['value']; if ( isset( $aField['label'] ) ) return $aField['label']; if ( isset( $aField['default'] ) ) return $aField['default']; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_export' ) ) : class AdminPageFramework_FieldType_export extends AdminPageFramework_FieldType_submit { public $aFieldTypeSlugs = array( 'export', ); protected $aDefaultKeys = array( 'data' => null, 'format' => 'json', 'file_name' => null, 'attributes' => array( 'class' => 'button button-primary', ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return ""; } public function _replyToGetField( $aField ) { if ( isset( $aField['data'] ) ) set_transient( md5( "{$aField['class_name']}_{$aField['input_id']}" ), $aField['data'], 60*2 ); $aField['attributes']['name'] = "__export[submit][{$aField['input_id']}]"; $aField['file_name'] = $aField['file_name'] ? $aField['file_name'] : $this->_generateExportFileName( $aField['option_key'] ? $aField['option_key'] : $aField['class_name'], $aField['format'] ); $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->__( 'export' ); return parent::_replyToGetField( $aField ); } protected function _getExtraInputFields( &$aField ) { $_aAttributes = array( 'type' => 'hidden' ); return "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][input_id]", 'value' => $aField['input_id'], ) + $_aAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][field_id]", 'value' => $aField['field_id'], ) + $_aAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][section_id]", 'value' => isset( $aField['section_id'] ) ? $aField['section_id'] : '', ) + $_aAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][file_name]", 'value' => $aField['file_name'], ) + $_aAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][format]", 'value' => $aField['format'], ) + $_aAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__export[{$aField['input_id']}][transient]", 'value' => isset( $aField['data'] ), ) + $_aAttributes ) . "/>" ; } private function _generateExportFileName( $sOptionKey, $sExportFormat='json' ) { switch ( trim( strtolower( $sExportFormat ) ) ) { case 'text': $sExt = "txt"; break; case 'json': $sExt = "json"; break; case 'array': default: $sExt = "txt"; break; } return $sOptionKey . '_' . date("Ymd") . '.' . $sExt; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_import' ) ) : class AdminPageFramework_FieldType_import extends AdminPageFramework_FieldType_submit { public $aFieldTypeSlugs = array( 'import', ); protected $aDefaultKeys = array( 'option_key' => null, 'format' => 'json', 'is_merge' => false, 'attributes' => array( 'class' => 'button button-primary', 'file' => array( 'accept' => 'audio/*|video/*|image/*|MIME_type', 'class' => 'import', 'type' => 'file', ), 'submit' => array( 'class' => 'import button button-primary', 'type' => 'submit', ), ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Import Field */
		.admin-page-framework-field-import input {
			margin-right: 0.5em;
		}
		.admin-page-framework-field-import label,
		.form-table td fieldset.admin-page-framework-fieldset .admin-page-framework-field-import label {	/* for Wordpress 3.8 or above */
			display: inline;	/* to display the submit button in the same line to the file input tag */
		}" . PHP_EOL; } public function _replyToGetField( $aField ) { $aField['attributes']['name'] = "__import[submit][{$aField['input_id']}]"; $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->__( 'import' ); return parent::_replyToGetField( $aField ); } protected function _getExtraFieldsBeforeLabel( &$aField ) { return "<input " . $this->generateAttributes( array( 'id' => "{$aField['input_id']}_file", 'type' => 'file', 'name' => "__import[{$aField['input_id']}]", ) + $aField['attributes']['file'] ) . " />"; } protected function _getExtraInputFields( &$aField ) { $aHiddenAttributes = array( 'type' => 'hidden', ); return "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][input_id]", 'value' => $aField['input_id'], ) + $aHiddenAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][field_id]", 'value' => $aField['field_id'], ) + $aHiddenAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][section_id]", 'value' => isset( $aField['section_id'] ) ? $aField['section_id'] : '', ) + $aHiddenAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][is_merge]", 'value' => $aField['is_merge'], ) + $aHiddenAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][option_key]", 'value' => $aField['option_key'], ) + $aHiddenAttributes ) . "/>" . "<input " . $this->generateAttributes( array( 'name' => "__import[{$aField['input_id']}][format]", 'value' => $aField['format'], ) + $aHiddenAttributes ) . "/>" ; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_taxonomy' ) ) : class AdminPageFramework_FieldType_taxonomy extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'taxonomy', ); protected $aDefaultKeys = array( 'taxonomy_slugs' => 'category', 'height' => '250px', 'max_width' => '100$', 'attributes' => array( ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { $aJSArray = json_encode( $this->aFieldTypeSlugs ); return "	
			jQuery( document ).ready( function() {
				/* For tabs */
				var enableAPFTabbedBox = function( nodeTabBoxContainer ) {
					jQuery( nodeTabBoxContainer ).each( function() {
						jQuery( this ).find( '.tab-box-tab' ).each( function( i ) {
							
							if ( i == 0 )
								jQuery( this ).addClass( 'active' );
								
							jQuery( this ).click( function( e ){
									 
								// Prevents jumping to the anchor which moves the scroll bar.
								e.preventDefault();
								
								// Remove the active tab and set the clicked tab to be active.
								jQuery( this ).siblings( 'li.active' ).removeClass( 'active' );
								jQuery( this ).addClass( 'active' );
								
								// Find the element id and select the content element with it.
								var thisTab = jQuery( this ).find( 'a' ).attr( 'href' );
								active_content = jQuery( this ).closest( '.tab-box-container' ).find( thisTab ).css( 'display', 'block' ); 
								active_content.siblings().css( 'display', 'none' );
								
							});
						});		
					});
				}		
				enableAPFTabbedBox( jQuery( '.tab-box-container' ) );

				/*	The repeatable event */
				jQuery().registerAPFCallback( {				
					added_repeatable_field: function( node, sFieldType, sFieldTagID ) {
			
						/* If it is not the color field type, do nothing. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;
						
						var fIncrementOrDecrement = 1;
						var updateID = function( index, name ) {
							
							if ( typeof name === 'undefined' ) {
								return name;
							}
							return name.replace( /_((\d+))(?=(_|$))/, function ( fullMatch, n ) {						
								return '_' + ( Number(n) + ( fIncrementOrDecrement == 1 ? 1 : -1 ) );
							});
							
						}
						var updateName = function( index, name ) {
							
							if ( typeof name === 'undefined' ) {
								return name;
							}
							return name.replace( /\[((\d+))(?=\])/, function ( fullMatch, n ) {				
								return '[' + ( Number(n) + ( fIncrementOrDecrement == 1 ? 1 : -1 ) );
							});
							
						}
						node.find( 'div' ).attr( 'id', function( index, name ){ return updateID( index, name ) } );
						node.find( 'li.tab-box-tab a' ).attr( 'href', function( index, name ){ return updateID( index, name ) } );
						
						enableAPFTabbedBox( node.find( '.tab-box-container' ) );
						
					}
				});
			});			
		"; } public function _replyToGetStyles() { return "/* Taxonomy Field Type */
			.admin-page-framework-field .taxonomy-checklist li { 
				margin: 8px 0 8px 20px; 
			}
			.admin-page-framework-field div.taxonomy-checklist {
				padding: 8px 0 8px 10px;
				margin-bottom: 20px;
			}
			.admin-page-framework-field .taxonomy-checklist ul {
				list-style-type: none;
				margin: 0;
			}
			.admin-page-framework-field .taxonomy-checklist ul ul {
				margin-left: 1em;
			}
			.admin-page-framework-field .taxonomy-checklist-label {
				/* margin-left: 0.5em; */
			}		
		/* Tabbed box */
			.admin-page-framework-field .tab-box-container.categorydiv {
				max-height: none;
			}
			.admin-page-framework-field .tab-box-tab-text {
				display: inline-block;
			}
			.admin-page-framework-field .tab-box-tabs {
				line-height: 12px;
				margin-bottom: 0;
			
			}
			.admin-page-framework-field .tab-box-tabs .tab-box-tab.active {
				display: inline;
				border-color: #dfdfdf #dfdfdf #fff;
				margin-bottom: 0;
				padding-bottom: 1px;
				background-color: #fff;
			}
			.admin-page-framework-field .tab-box-container { 
				position: relative; 
				width: 100%; 
				clear: both;
				margin-bottom: 1em;
			}
			.admin-page-framework-field .tab-box-tabs li a { color: #333; text-decoration: none; }
			.admin-page-framework-field .tab-box-contents-container {  
				padding: 0 2em 0 1.8em;
				border: 1px solid #dfdfdf; 
				background-color: #fff;
			}
			.admin-page-framework-field .tab-box-contents { 
				overflow: hidden; 
				overflow-x: hidden; 
				position: relative; 
				top: -1px; 
				height: 300px;  
			}
			.admin-page-framework-field .tab-box-content { 
				height: 300px;
				display: none; 
				overflow: auto; 
				display: block; 
				position: relative; 
				overflow-x: hidden;
			}
			.admin-page-framework-field .tab-box-content:target, 
			.admin-page-framework-field .tab-box-content:target, 
			.admin-page-framework-field .tab-box-content:target { 
				display: block; 
			}			
		" . PHP_EOL; } public function _replyToGetInputIEStyles() { return ".tab-box-content { display: block; }
			.tab-box-contents { overflow: hidden;position: relative; }
			b { position: absolute; top: 0px; right: 0px; width:1px; height: 251px; overflow: hidden; text-indent: -9999px; }
		"; } public function _replyToGetField( $aField ) { $aTabs = array(); $aCheckboxes = array(); foreach( ( array ) $aField['taxonomy_slugs'] as $sKey => $sTaxonomySlug ) { $aInputAttributes = isset( $aField['attributes'][ $sKey ] ) && is_array( $aField['attributes'][ $sKey ] ) ? $aField['attributes'][ $sKey ] + $aField['attributes'] : $aField['attributes']; $aTabs[] = "<li class='tab-box-tab'>" . "<a href='#tab_{$aField['input_id']}_{$sKey}'>" . "<span class='tab-box-tab-text'>" . $this->_getLabelFromTaxonomySlug( $sTaxonomySlug ) . "</span>" ."</a>" ."</li>"; $aCheckboxes[] = "<div id='tab_{$aField['input_id']}_{$sKey}' class='tab-box-content' style='height: {$aField['height']};'>" . $this->getFieldElementByKey( $aField['before_label'], $sKey ) . "<ul class='list:category taxonomychecklist form-no-clear'>" . wp_list_categories( array( 'walker' => new AdminPageFramework_WalkerTaxonomyChecklist, 'name' => is_array( $aField['taxonomy_slugs'] ) ? "{$aField['_input_name']}[{$sTaxonomySlug}]" : $aField['_input_name'], 'selected' => $this->_getSelectedKeyArray( $aField['value'], $sKey ), 'title_li' => '', 'hide_empty' => 0, 'echo' => false, 'taxonomy' => $sTaxonomySlug, 'input_id' => $aField['input_id'], 'attributes' => $aInputAttributes, ) ) . "</ul>" . "<!--[if IE]><b>.</b><![endif]-->" . $this->getFieldElementByKey( $aField['after_label'], $sKey ) . "</div>"; } $sTabs = "<ul class='tab-box-tabs category-tabs'>" . implode( PHP_EOL, $aTabs ) . "</ul>"; $sContents = "<div class='tab-box-contents-container'>" . "<div class='tab-box-contents' style='height: {$aField['height']};'>" . implode( PHP_EOL, $aCheckboxes ) . "</div>" . "</div>"; return '' . "<div id='tabbox-{$aField['field_id']}' class='tab-box-container categorydiv' style='max-width:{$aField['max_width']};'>" . $sTabs . PHP_EOL . $sContents . PHP_EOL . "</div>" ; } private function _getSelectedKeyArray( $vValue, $sKey ) { $vValue = ( array ) $vValue; $iArrayDimension = $this->getArrayDimension( $vValue ); if ( $iArrayDimension == 1 ) $aKeys = $vValue; else if ( $iArrayDimension == 2 ) $aKeys = ( array ) $this->getCorrespondingArrayValue( $vValue, $sKey, false ); return array_keys( $aKeys, true ); } private function _getLabelFromTaxonomySlug( $sTaxonomySlug ) { $oTaxonomy = get_taxonomy( $sTaxonomySlug ); return isset( $oTaxonomy->label ) ? $oTaxonomy->label : null; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_text' ) ) : class AdminPageFramework_FieldType_text extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'text', 'password', 'date', 'datetime', 'datetime-local', 'email', 'month', 'search', 'tel', 'url', 'week', ); protected $aDefaultKeys = array( 'attributes' => array( 'size' => 30, 'maxlength' => 400, ), ); public function _replyToGetStyles() { return "/* Text Field Type */
				.admin-page-framework-field-text .admin-page-framework-field .admin-page-framework-input-label-container {
					vertical-align: top; 
				}
			" . PHP_EOL; } public function _replyToGetField( $aField ) { return $aField['before_label'] . "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . "<input " . $this->generateAttributes( $aField['attributes'] ) . " />" . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label']; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_file' ) ) : class AdminPageFramework_FieldType_file extends AdminPageFramework_FieldType_text { public $aFieldTypeSlugs = array( 'file', ); protected $aDefaultKeys = array( 'attributes' => array( 'accept' => 'audio/*|video/*|image/*|MIME_type', ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return ""; } public function _replyToGetField( $aField ) { return parent::_replyToGetField( $aField ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_number' ) ) : class AdminPageFramework_FieldType_number extends AdminPageFramework_FieldType_text { public $aFieldTypeSlugs = array( 'number', 'range' ); protected $aDefaultKeys = array( 'attributes' => array( 'size' => 30, 'maxlength' => 400, 'class' => '', 'min' => '', 'max' => '', 'step' => '', 'readonly' => '', 'required' => '', 'placeholder' => '', 'list' => '', 'autofocus' => '', 'autocomplete' => '', ), ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return ""; } public function _replyToGetField( $aField ) { return parent::_replyToGetField( $aField ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_textarea' ) ) : class AdminPageFramework_FieldType_textarea extends AdminPageFramework_FieldType_Base { public $aFieldTypeSlugs = array( 'textarea' ); protected $aDefaultKeys = array( 'rich' => false, 'attributes' => array( 'autofocus' => '', 'cols' => 60, 'disabled' => '', 'formNew' => '', 'maxlength' => '', 'placeholder' => '', 'readonly' => '', 'required' => '', 'rows' => 4, 'wrap' => '', ), ); public function _replyToGetStyles() { return "/* Textarea Field Type */
			.admin-page-framework-field-textarea .admin-page-framework-input-label-string {
				vertical-align: top;
				margin-top: 2px;
			}		
			/* Rich Text Editor */
			.admin-page-framework-field-textarea .wp-core-ui.wp-editor-wrap {
				margin-bottom: 0.5em;
			}
			.admin-page-framework-field-textarea.admin-page-framework-field .admin-page-framework-input-label-container {
				vertical-align: top; 
			} 
			
		" . PHP_EOL; } public function _replyToGetField( $aField ) { return "<div class='admin-page-framework-input-label-container'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . ( ! empty( $aField['rich'] ) && version_compare( $GLOBALS['wp_version'], '3.3', '>=' ) && function_exists( 'wp_editor' ) ? wp_editor( $aField['value'], $aField['attributes']['id'], $this->uniteArrays( ( array ) $aField['rich'], array( 'wpautop' => true, 'media_buttons' => true, 'textarea_name' => $aField['attributes']['name'], 'textarea_rows' => $aField['attributes']['rows'], 'tabindex' => '', 'tabfocus_elements' => ':prev,:next', 'editor_css' => '', 'editor_class' => $aField['attributes']['class'], 'teeny' => false, 'dfw' => false, 'tinymce' => true, 'quicktags' => true ) ) ) . $this->_getScriptForRichEditor( $aField['attributes']['id'] ) : "<textarea " . $this->generateAttributes( $aField['attributes'] ) . " >" . $aField['value'] . "</textarea>" ) . "<div class='repeatable-field-buttons'></div>" . $aField['after_input'] . "</label>" . "</div>" ; } private function _getScriptForRichEditor( $sIDSelector ) { return "<script type='text/javascript'>
				jQuery( '#wp-{$sIDSelector}-wrap' ).hide();
				jQuery( document ).ready( function() {
					jQuery( '#wp-{$sIDSelector}-wrap' ).appendTo( '#field-{$sIDSelector}' );
					jQuery( '#wp-{$sIDSelector}-wrap' ).show();
				})
			</script>"; } } endif;if ( ! class_exists( 'AdminPageFramework_Link_Page' ) ) : class AdminPageFramework_Link_Page extends AdminPageFramework_Link_Base { private $oProp; public function __construct( &$oProp, $oMsg=null ) { if ( ! is_admin() ) return; $this->oProp = $oProp; $this->oMsg = $oMsg; add_filter( 'update_footer', array( $this, '_replyToAddInfoInFooterRight' ), 11 ); add_filter( 'admin_footer_text' , array( $this, '_replyToAddInfoInFooterLeft' ) ); $this->_setFooterInfoLeft( $this->oProp->aScriptInfo, $this->oProp->aFooterInfo['sLeft'] ); $aLibraryData = AdminPageFramework_Property_Base::_getLibraryData(); $aLibraryData['sVersion'] = $this->oProp->bIsMinifiedVersion ? $aLibraryData['sVersion'] . '.min' : $aLibraryData['sVersion']; $this->_setFooterInfoRight( $aLibraryData, $this->oProp->aFooterInfo['sRight'] ); if ( $this->oProp->aScriptInfo['sType'] == 'plugin' ) add_filter( 'plugin_action_links_' . plugin_basename( $this->oProp->aScriptInfo['sPath'] ) , array( $this, '_replyToAddSettingsLinkInPluginListingPage' ) ); } public function _addLinkToPluginDescription( $linkss ) { if ( !is_array( $linkss ) ) $this->oProp->aPluginDescriptionLinks[] = $linkss; else $this->oProp->aPluginDescriptionLinks = array_merge( $this->oProp->aPluginDescriptionLinks , $linkss ); add_filter( 'plugin_row_meta', array( $this, '_replyToAddLinkToPluginDescription' ), 10, 2 ); } public function _addLinkToPluginTitle( $linkss ) { if ( !is_array( $linkss ) ) $this->oProp->aPluginTitleLinks[] = $linkss; else $this->oProp->aPluginTitleLinks = array_merge( $this->oProp->aPluginTitleLinks, $linkss ); add_filter( 'plugin_action_links_' . plugin_basename( $this->oProp->aScriptInfo['sPath'] ), array( $this, '_replyToAddLinkToPluginTitle' ) ); } public function _replyToAddInfoInFooterLeft( $sLinkHTML='' ) { if ( ! isset( $_GET['page'] ) || ! $this->oProp->isPageAdded( $_GET['page'] ) ) return $sLinkHTML; if ( empty( $this->oProp->aScriptInfo['sName'] ) ) return $sLinkHTML; return $this->oProp->aFooterInfo['sLeft']; } public function _replyToAddInfoInFooterRight( $sLinkHTML='' ) { if ( ! isset( $_GET['page'] ) || ! $this->oProp->isPageAdded( $_GET['page'] ) ) return $sLinkHTML; return $this->oProp->aFooterInfo['sRight']; } public function _replyToAddSettingsLinkInPluginListingPage( $aLinks ) { if ( count( $this->oProp->aPages ) < 1 ) return $aLinks; $sLinkURL = preg_match( '/^.+\.php/', $this->oProp->aRootMenu['sPageSlug'] ) ? add_query_arg( array( 'page' => $this->oProp->sDefaultPageSlug ), admin_url( $this->oProp->aRootMenu['sPageSlug'] ) ) : "admin.php?page={$this->oProp->sDefaultPageSlug}"; array_unshift( $aLinks, '<a href="' . $sLinkURL . '">' . $this->oMsg->__( 'settings' ) . '</a>' ); return $aLinks; } public function _replyToAddLinkToPluginDescription( $aLinks, $sFile ) { if ( $sFile != plugin_basename( $this->oProp->aScriptInfo['sPath'] ) ) return $aLinks; $aAddingLinks = array(); foreach( $this->oProp->aPluginDescriptionLinks as $linksHTML ) if ( is_array( $linksHTML ) ) $aAddingLinks = array_merge( $linksHTML, $aAddingLinks ); else $aAddingLinks[] = ( string ) $linksHTML; return array_merge( $aLinks, $aAddingLinks ); } public function _replyToAddLinkToPluginTitle( $aLinks ) { $aAddingLinks = array(); foreach( $this->oProp->aPluginTitleLinks as $linksHTML ) if ( is_array( $linksHTML ) ) $aAddingLinks = array_merge( $linksHTML, $aAddingLinks ); else $aAddingLinks[] = ( string ) $linksHTML; return array_merge( $aLinks, $aAddingLinks ); } } endif;if ( ! class_exists( 'AdminPageFramework_Link_PostType' ) ) : class AdminPageFramework_Link_PostType extends AdminPageFramework_Link_Base { public $aFooterInfo = array( 'sLeft' => '', 'sRight' => '', ); public function __construct( $oProp, $oMsg=null ) { if ( ! is_admin() ) return; $this->oProp = $oProp; $this->oMsg = $oMsg; $this->sSettingPageLinkTitle = $this->oMsg->__( 'manage' ); add_filter( 'update_footer', array( $this, '_replyToAddInfoInFooterRight' ), 11 ); add_filter( 'admin_footer_text' , array( $this, '_replyToAddInfoInFooterLeft' ) ); $this->_setFooterInfoLeft( $this->oProp->aScriptInfo, $this->aFooterInfo['sLeft'] ); $aLibraryData = $this->oProp->_getLibraryData(); $aLibraryData['sVersion'] = $this->oProp->bIsMinifiedVersion ? $aLibraryData['sVersion'] . '.min' : $aLibraryData['sVersion']; $this->_setFooterInfoRight( $aLibraryData, $this->aFooterInfo['sRight'] ); if ( $this->oProp->aScriptInfo['sType'] == 'plugin' ) add_filter( 'plugin_action_links_' . plugin_basename( $this->oProp->aScriptInfo['sPath'] ), array( $this, '_replyToAddSettingsLinkInPluginListingPage' ), 20 ); if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->oProp->sPostType ) add_action( 'get_edit_post_link', array( $this, '_replyToAddPostTypeQueryInEditPostLink' ), 10, 3 ); } public function _replyToAddPostTypeQueryInEditPostLink( $sURL, $iPostID=null, $sContext=null ) { return add_query_arg( array( 'post' => $iPostID, 'action' => 'edit', 'post_type' => $this->oProp->sPostType ), $sURL ); } public function _replyToAddSettingsLinkInPluginListingPage( $aLinks ) { array_unshift( $aLinks, "<a href='edit.php?post_type={$this->oProp->sPostType}'>" . $this->sSettingPageLinkTitle . "</a>" ); return $aLinks; } public function _replyToAddInfoInFooterLeft( $sLinkHTML='' ) { if ( ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != $this->oProp->sPostType ) && ! $this->oProp->isPostDefinitionPage( $this->oProp->sPostType ) ) return $sLinkHTML; if ( empty( $this->oProp->aScriptInfo['sName'] ) ) return $sLinkHTML; return $this->aFooterInfo['sLeft']; } public function _replyToAddInfoInFooterRight( $sLinkHTML='' ) { if ( ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != $this->oProp->sPostType ) && ! $this->oProp->isPostDefinitionPage( $this->oProp->sPostType ) ) return $sLinkHTML; return $this->aFooterInfo['sRight']; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_posttype' ) ) : class AdminPageFramework_FieldType_posttype extends AdminPageFramework_FieldType_checkbox { public $aFieldTypeSlugs = array( 'posttype', ); protected $aDefaultKeys = array( 'slugs_to_remove' => null, 'attributes' => array( 'size' => 30, 'maxlength' => 400, ), ); protected $aDefaultRemovingPostTypeSlugs = array( 'revision', 'attachment', 'nav_menu_item', ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Posttype Field Type */
			.admin-page-framework-field input[type='checkbox'] {
				margin-right: 0.5em;
			}			
			.admin-page-framework-field-posttype .admin-page-framework-input-label-container {
				padding-right: 1em;
			}	
		"; } public function _replyToGetField( $aField ) { $aField['label'] = $this->_getPostTypeArrayForChecklist( isset( $aField['slugs_to_remove'] ) ? $aField['slugs_to_remove'] : $this->aDefaultRemovingPostTypeSlugs ); return parent::_replyToGetField( $aField ); } private function _getPostTypeArrayForChecklist( $aRemoveNames, $aPostTypes=array() ) { foreach( get_post_types( '','objects' ) as $oPostType ) if ( isset( $oPostType->name, $oPostType->label ) ) $aPostTypes[ $oPostType->name ] = $oPostType->label; return array_diff_key( $aPostTypes, array_flip( $aRemoveNames ) ); } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_media' ) ) : class AdminPageFramework_FieldType_media extends AdminPageFramework_FieldType_image { public $aFieldTypeSlugs = array( 'media', ); protected $aDefaultKeys = array( 'attributes_to_store' => array(), 'show_preview' => true, 'allow_external_source' => true, 'attributes' => array( 'input' => array( 'size' => 40, 'maxlength' => 400, ), 'button' => array( ), 'preview' => array( ), ), ); public function _replyToFieldLoader() { parent::_replyToFieldLoader(); } public function _replyToGetScripts() { return $this->_getScript_CustomMediaUploaderObject() . PHP_EOL . $this->_getScript_MediaUploader( "admin_page_framework", $this->oMsg->__( 'upload_file' ), $this->oMsg->__( 'use_this_file' ) ) . PHP_EOL . $this->_getScript_RegisterCallbacks(); } protected function _getScript_RegisterCallbacks() { $aJSArray = json_encode( $this->aFieldTypeSlugs ); return"
			jQuery( document ).ready( function(){
						
				jQuery().registerAPFCallback( {	
				
					added_repeatable_field: function( node, sFieldType, sFieldTagID ) {
						
						/* 1. Return if it is not the type. */						
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;	/* If it is not the media field type, do nothing. */
						if ( node.find( '.select_media' ).length <= 0 )  return;	/* If the uploader buttons are not found, do nothing */
						
						/* 2. Increment the ids of the next all (including this one) uploader buttons  */
						var nodeFieldContainer = node.closest( '.admin-page-framework-field' );
						nodeFieldContainer.nextAll().andSelf().each( function() {

							/* 2-1. Increment the button ID */
							nodeButton = jQuery( this ).find( '.select_media' );
							nodeButton.incrementIDAttribute( 'id' );
							
							/* 2-2. Rebind the uploader script to each button. The previously assigned ones also need to be renewed; 
							 * otherwise, the script sets the preview image in the wrong place. */						
							var nodeMediaInput = jQuery( this ).find( '.media-field input' );
							if ( nodeMediaInput.length <= 0 ) return true;
							setAPFMediaUploader( nodeMediaInput.attr( 'id' ), true, jQuery( nodeButton ).attr( 'data-enable_external_source' ) );
							
						});						
					},
					removed_repeatable_field: function( node, sFieldType, sFieldTagID ) {
						
						/* 1. Return if it is not the type. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;	/* If it is not the color field type, do nothing. */
						if ( node.find( '.select_media' ).length <= 0 )  return;	/* If the uploader buttons are not found, do nothing */
						
						/* 2. Decrement the ids of the next all (including this one) uploader buttons. ( the input values are already dealt by the framework repeater script ) */
						var nodeFieldContainer = node.closest( '.admin-page-framework-field' );
						nodeFieldContainer.nextAll().andSelf().each( function() {
							
							/* 2-1. Decrement the button ID */
							nodeButton = jQuery( this ).find( '.select_media' );						
							nodeButton.decrementIDAttribute( 'id' );
														
							/* 2-2. Rebind the uploader script to each button. */
							var nodeMediaInput = jQuery( this ).find( '.media-field input' );
							if ( nodeMediaInput.length <= 0 ) return true;
							setAPFMediaUploader( nodeMediaInput.attr( 'id' ), true, jQuery( nodeButton ).attr( 'data-enable_external_source' ) );	
							
						});
					},
					
					sorted_fields : function( node, sFieldType, sFieldsTagID ) {	// on contrary to repeatable callbacks, the _fields_ container node and its ID will be passed.

						/* 1. Return if it is not the type. */
						if ( jQuery.inArray( sFieldType, {$aJSArray} ) <= -1 ) return;	/* If it is not the color field type, do nothing. */						
						if ( node.find( '.select_media' ).length <= 0 )  return;	/* If the uploader buttons are not found, do nothing */
						
						/* 2. Update the Select File button */
						var iCount = 0;
						node.children( '.admin-page-framework-field' ).each( function() {
							
							nodeButton = jQuery( this ).find( '.select_media' );
							
							/* 2-1. Set the current iteration index to the button ID */
							nodeButton.setIndexIDAttribute( 'id', iCount );	
							
							/* 2-2. Rebuind the uploader script to the button */
							var nodeMediaInput = jQuery( this ).find( '.media-field input' );
							if ( nodeMediaInput.length <= 0 ) return true;
							setAPFMediaUploader( nodeMediaInput.attr( 'id' ), true, jQuery( nodeButton ).attr( 'data-enable_external_source' ) );
	
							iCount++;
						});
					},
					
				});
			});" . PHP_EOL; } private function _getScript_MediaUploader( $sReferrer, $sThickBoxTitle, $sThickBoxButtonUseThis ) { if ( ! function_exists( 'wp_enqueue_media' ) ) return "
					jQuery( document ).ready( function(){
						
						/**
						 * Bind/rebinds the thickbox script the given selector element.
						 * The fMultiple parameter does not do anything. It is there to be consistent with the one for the WordPress version 3.5 or above.
						 */
						setAPFMediaUploader = function( sInputID, fMultiple, fExternalSource ) {
							jQuery( '#select_media_' + sInputID ).unbind( 'click' );	// for repeatable fields
							jQuery( '#select_media_' + sInputID ).click( function() {
								var sPressedID = jQuery( this ).attr( 'id' );
								window.sInputID = sPressedID.substring( 13 );	// remove the select_media_ prefix and set a property to pass it to the editor callback method.
								window.original_send_to_editor = window.send_to_editor;
								window.send_to_editor = hfAPFSendToEditorMedia;
								var fExternalSource = jQuery( this ).attr( 'data-enable_external_source' );
								tb_show( '{$sThickBoxTitle}', 'media-upload.php?post_id=1&amp;enable_external_source=' + fExternalSource + '&amp;referrer={$sReferrer}&amp;button_label={$sThickBoxButtonUseThis}&amp;type=image&amp;TB_iframe=true', false );
								return false;	// do not click the button after the script by returning false.									
							});	
						}			
														
						var hfAPFSendToEditorMedia = function( sRawHTML, param ) {

							var sHTML = '<div>' + sRawHTML + '</div>';	// This is for the 'From URL' tab. Without the wrapper element. the below attr() method don't catch attributes.
							var src = jQuery( 'a', sHTML ).attr( 'href' );
							var classes = jQuery( 'a', sHTML ).attr( 'class' );
							var id = ( classes ) ? classes.replace( /(.*?)wp-image-/, '' ) : '';	// attachment ID	
						
							// If the user wants to save relavant attributes, set them.
							var sInputID = window.sInputID;
							jQuery( '#' + sInputID ).val( src );	// sets the image url in the main text field. The url field is mandatory so it does not have the suffix.
							jQuery( '#' + sInputID + '_id' ).val( id );			
								
							// restore the original send_to_editor
							window.send_to_editor = window.original_send_to_editor;
							
							// close the thickbox
							tb_remove();	

						}
					});
				"; return "
			jQuery( document ).ready( function(){		
				
				// Global Function Literal 
				/**
				 * Binds/rebinds the uploader button script to the specified element with the given ID.
				 */				
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
										var new_field = jQuery( this ).addAPFRepeatableField( field_container.attr( 'id' ) );
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
				
			});"; } public function _replyToGetStyles() { return "/* Media Uploader Button */
			.admin-page-framework-field-media input {
				margin-right: 0.5em;
				vertical-align: middle;	
			}
			.select_media.button.button-small {
				margin-top: 0.1em;
			}
		"; } public function _replyToGetField( $aField ) { return parent::_replyToGetField( $aField ); } protected function _getPreviewContainer( $aField, $sImageURL, $aPreviewAtrributes ) { return ""; } protected function _getUploaderButtonScript( $sInputID, $bRpeatable, $bExternalSource, array $aButtonAttributes ) { $sButton = "<a " . $this->generateAttributes( array( 'id' => "select_media_{$sInputID}", 'href' => '#', 'class' => 'select_media button button-small ' . ( isset( $aButtonAttributes['class'] ) ? $aButtonAttributes['class'] : '' ), 'data-uploader_type' => function_exists( 'wp_enqueue_media' ) ? 1 : 0, 'data-enable_external_source' => $bExternalSource ? 1 : 0, ) + $aButtonAttributes ) . ">" . $this->oMsg->__( 'select_file' ) ."</a>"; $sScript = "
				if ( jQuery( 'a#select_media_{$sInputID}' ).length == 0 ) {
					jQuery( 'input#{$sInputID}' ).after( \"{$sButton}\" );
				}
				jQuery( document ).ready( function(){			
					setAPFMediaUploader( '{$sInputID}', '{$bRpeatable}', '{$bExternalSource}' );
				});" . PHP_EOL; return "<script type='text/javascript' class='admin-page-framework-media-uploader-button'>" . $sScript . "</script>". PHP_EOL; } } endif;if ( ! class_exists( 'AdminPageFramework_FieldType_size' ) ) : class AdminPageFramework_FieldType_size extends AdminPageFramework_FieldType_select { public $aFieldTypeSlugs = array( 'size', ); protected $aDefaultKeys = array( 'is_multiple' => false, 'units' => null, 'attributes' => array( 'size' => array( 'size' => 10, 'maxlength' => 400, 'min' => '', 'max' => '', ), 'unit' => array( 'multiple' => '', 'size' => 1, 'autofocusNew' => '', 'multiple' => '', 'required' => '', ), 'optgroup' => array(), 'option' => array(), ), ); protected $aDefaultUnits = array( 'px' => 'px', '%' => '%', 'em' => 'em', 'ex' => 'ex', 'in' => 'in', 'cm' => 'cm', 'mm' => 'mm', 'pt' => 'pt', 'pc' => 'pc', ); public function _replyToFieldLoader() { } public function _replyToGetScripts() { return ""; } public function _replyToGetStyles() { return "/* Size Field Type */
		.admin-page-framework-field-size input {
			text-align: right;
		}
		.admin-page-framework-field-size select.size-field-select {
			vertical-align: 0px;			
		}
		.admin-page-framework-field-size label {
			width: auto;
		} 
		" . PHP_EOL; } public function _replyToGetField( $aField ) { $aField['units'] = isset( $aField['units'] ) ? $aField['units'] : $this->aDefaultUnits; $aBaseAttributes = $aField['attributes']; unset( $aBaseAttributes['unit'], $aBaseAttributes['size'] ); $aSizeAttributes = array( 'type' => 'number', 'id' => $aField['input_id'] . '_' . 'size', 'name' => $aField['_input_name'] . '[size]', 'value' => isset( $aField['value']['size'] ) ? $aField['value']['size'] : '', ) + $this->getFieldElementByKey( $aField['attributes'], 'size', $this->aDefaultKeys['attributes']['size'] ) + $aBaseAttributes; $aSizeLabelAttributes = array( 'for' => $aSizeAttributes['id'], 'class' => $aSizeAttributes['disabled'] ? 'disabled' : '', ); $aUnitAttributes = array( 'type' => 'select', 'id' => $aField['input_id'] . '_' . 'unit', 'multiple' => $aField['is_multiple'] ? 'Multiple' : $aField['attributes']['unit']['multiple'], 'value' => isset( $aField['value']['unit'] ) ? $aField['value']['unit'] : '', ) + $this->getFieldElementByKey( $aField['attributes'], 'unit', $this->aDefaultKeys['attributes']['unit'] ) + $aBaseAttributes; $aUnitAttributes['name'] = empty( $aUnitAttributes['multiple'] ) ? "{$aField['_input_name']}[unit]" : "{$aField['_input_name']}[unit][]"; $aUnitLabelAttributes = array( 'for' => $aUnitAttributes['id'], 'class' => $aUnitAttributes['disabled'] ? 'disabled' : '', ); return $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-select-label' style='min-width: {$aField['label_min_width']}px;'>" . "<label " . $this->generateAttributes( $aSizeLabelAttributes ) . ">" . $this->getFieldElementByKey( $aField['before_label'], 'size' ) . ( $aField['label'] && ! $aField['repeatable'] ? "<span class='admin-page-framework-input-label-string' style='min-width:" . $aField['label_min_width'] . "px;'>" . $aField['label'] . "</span>" : "" ) . "<input " . $this->generateAttributes( $aSizeAttributes ) . " />" . $this->getFieldElementByKey( $aField['after_input'], 'size' ) . "</label>" . "<label " . $this->generateAttributes( $aUnitLabelAttributes ) . ">" . $this->getFieldElementByKey( $aField['before_label'], 'unit' ) . "<span class='admin-page-framework-input-container'>" . "<select " . $this->generateAttributes( $aUnitAttributes ) . " >" . $this->_getOptionTags( $aUnitAttributes['id'], $aBaseAttributes, $aField['units'] ) . "</select>" . "</span>" . $this->getFieldElementByKey( $aField['after_input'], 'unit' ) . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>" . $aField['after_label']; } } endif;