<?php

/**
 * View of the share module.
 * @author matti
 *
 */
class ShareView extends CrudView {
	
	/**
	 * Main widget to manage files and folders.
	 * @var Filebrowser
	 */
	private $filebrowser;
	
	/**
	 * Create the repertoire view.
	 */
	function __construct($ctrl) {
		$this->setController($ctrl);
		$this->setEntityName("Dokumentenart");
	}
	
	private function initFilebrowser() {
		if($this->filebrowser == null) {
			$this->filebrowser = new Filebrowser($GLOBALS["DATA_PATHS"]["share"], $this->getData()->getSysdata(), $this->getData()->adp());
		}
	}
	
	function start() {
		$this->initFilebrowser();
		$viewMode = $this->getData()->getSysdata()->getDynamicConfigParameter("share_nonadmin_viewmode");
		if($viewMode == "1" && !$this->getData()->getSysdata()->isUserSuperUser()
				&& !$this->getData()->adp()->isGroupMember(1)) {
			$this->filebrowser->viewMode(true);
		}
		
		$this->filebrowser->write();
	}
	
	function startOptions() {
		$this->initFilebrowser();
		$this->filebrowser->showOptions();
		
		if($this->getData()->getSysdata()->isUserAdmin()) {
			$docType = new Link($this->modePrefix() . "docType", "Dokumentenarten");
			$docType->addIcon("documenttype");
			$docType->write();
		}
	}
	
	function docType() {
		if(!$this->getData()->getSysdata()->isUserAdmin()) {
			new BNoteError("Permission denied.");
		}
		parent::start();
	}
	
	function docTypeOptions() {
		$this->backToStart();
		$add = new Link($this->modePrefix() . "addEntity", "Dokumentenart hinzufügen");
		$add->addIcon("plus");
		$add->write();
	}
	
}

?>