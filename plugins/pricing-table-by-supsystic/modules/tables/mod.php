<?php
class tablesPts extends modulePts {
	private $_assetsUrl = '';
	private $_oldAssetsUrl = 'https://supsystic.com/_assets/tables/';
	
	public function init() {
		dispatcherPts::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_filter('wp_footer', array($this, 'assignRenderedTables'));
		add_shortcode(PTS_SHORTCODE, array($this, 'showPriceTable'));
	}
	public function addAdminTab($tabs) {
		$tabs[ $this->getCode(). '_add_new' ] = array(
			'label' => __('Add New Table', PTS_LANG_CODE), 'callback' => array($this, 'getAddNewTabContent'), 'fa_icon' => 'fa-plus-circle', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode(). '_edit' ] = array(
			'label' => __('Edit', PTS_LANG_CODE), 'callback' => array($this, 'getEditTabContent'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() ] = array(
			'label' => __('Show All Tables', PTS_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-list', 'sort_order' => 20, //'is_main' => true,
		);
		return $tabs;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getAddNewTabContent() {
		return $this->getView()->getAddNewTabContent();
	}
	public function getEditTabContent() {
		$id = (int) reqPts::getVar('id', 'get');
		return $this->getView()->getEditTabContent( $id );
	}
	public function getEditLink($id) {
		$link = framePts::_()->getModule('options')->getTabUrl( $this->getCode(). '_edit' );
		$link .= '&id='. $id;
		return $link;
	}
	public function getAssetsUrl() {
		if(empty($this->_assetsUrl)) {
			$this->_assetsUrl = framePts::_()->getModule('templates')->getCdnUrl(). '_assets/tables/';
		}
		return $this->_assetsUrl;
	}
	public function getOldAssetsUrl() {
		return $this->_oldAssetsUrl;
	}
	public function assignRenderedTables() {
		$tables = $this->getView()->getRenderedTables();
		if(!empty($tables)) {
			framePts::_()->addJSVar('frontend.tables', 'ptsTables', $tables);
		}
	}
	public function showPriceTable($params) {
		return do_shortcode($this->getView()->showTable($params));
	}
}

