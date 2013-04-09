<?php

/**
 * Manages System Data
**/
include "dirs.php";

require("regex.php");
require("database.php");

class Systemdata {

 public $dbcon;
 public $regex;

 private $cfg_system;
 private $cfg_company;
 private $current_modid;
 
 private $user_module_permission;
 private $modulearray;

 function __construct() {
  if(!isset($_GET["mod"])) $this->current_modid = 0;
  else $this->current_modid = $_GET["mod"];

  $this->cfg_system = new XmlData("config/config.xml", "Software");
  $this->cfg_company = new XmlData("config/company.xml", "Company");

  $this->dbcon = new Database();
  $this->regex = new Regex();

  if($_SESSION["user"] > 0) {
   $this->user_module_permission = $this->getUserModulePermissions();
  }
 }

 /* GETTER */
 /**
  * Return the current module's id
  */
 public function getModuleId() {
  return $this->current_modid;
 }

 /**
  * Return the current module's title
  */
 public function getModuleTitle() {
  return $this->dbcon->getCell("module", "name", "id = " . $this->current_modid);
 }

 public function getModuleDescriptor() {
  return $this->dbcon->getCell("module", "descriptor", "modulId = " . $this->current_modid);
 }

 /**
  * Return the title of the company who owns the system
  */
 public function getCompany() {
  return $this->cfg_company->getParameter("Name");
 }

 /**
  * Returns the full name of the current user.
  */
 public function getUsername() {
 	$query = "SELECT surname, name FROM contact c, user u WHERE u.id = " . $_SESSION["user"];
 	$query .= " AND c.id = u.contact";
 	$un = $this->dbcon->getRow($query);
 	return $un["name"] . " " . $un["surname"];
 }
 
 /**
  * Returns an array with the module-ids the current user has permission for
  */
 public function getUserModulePermissions() {
 	$ret = array();
 	
 	$query = "SELECT module FROM privilege WHERE user = " . $_SESSION["user"];
 	$res = mysql_query($query);
 	if(!$res) new Error("The database query to retrieve the privileges failed.");
 	if(mysql_num_rows($res) == 0) new Error("You don't have sufficient privileges to access this system. Please contact your system administrator.");
 	
 	while($row = mysql_fetch_array($res)) {
 		array_push($ret, $row["module"]);
 	}
 	return $ret;
 }
 
 public function userHasPermission($modulId) {
 	return in_array($modulId, $this->user_module_permission);
 }

 /**
  * Returns an array with all modules: id => name
  */
 public function getModuleArray() {
 	if(isset($this->modulearray) && count($this->modulearray) > 0) {
 		return $this->modulearray;
 	}

  $mods = array();

  $query = "SELECT id, name FROM module ORDER BY id";
  $res = mysql_query($query);
  if(!$res) new Error("Die Datenbankabfrage schlug fehl.");
  if(mysql_num_rows($res) == 0) new Error("Datenbankfehler. Es muss mindestens ein Modul eingetragen sein.");

  while($row = mysql_fetch_array($res)) {
   $mods[$row["id"]] = $row["name"];
  }

  $this->modulearray = $mods;
  return $mods;
 }
 
 /**
  * Returns the name of the module with the given id
  */
 public function nameOfModule($id) {
 	return $this->modulearray[$id];
 }
 
 /**
  * Returns an array with the company's full information
  */
 public function getCompanyInformation() {
 	return $this->cfg_company->getArray();
 }
 
 public function getApplicationName() {
 	return $this->cfg_system->getParameter("Name");
 }
 
 public function getStartModuleId() {
 	return "" . $this->cfg_system->getParameter("StartModule");
 }

 /**
  * Holds the permissions a user has when created.
  * @return The IDs of the modules.
  */
 public function getDefaultUserCreatePermissions() {
 	// get default privileges from configuration
 	$defaultMods = explode(",", $this->cfg_system->getParameter("DefaultPrivileges"));
 	array_push($defaultMods, $this->getStartModuleId());
 	
 	// make sure at least one permission is specified -> start module
 	if(count($defaultMods) < 1) {
 		return array($this->getStartModuleId());
 	}
 	
 	return $defaultMods;
 }
 
 /**
  * Returns the URL where the system is reachable.
  */
 public function getSystemURL() {
 	return $this->cfg_system->getParameter("URL");
 }
 
 /**
  * Returns the full manual path.
  */
 public function getManualPath() {
 	return $this->cfg_system->getParameter("Manual");
 }
 
 /**
  * Returns true when DemoMode is on, otherwise false.
  */
 public function inDemoMode() {
 	$demoMode = $this->cfg_system->getParameter("DemoMode");
 	if(strtolower($demoMode) == "true") return true;
 	else return false;
 }
 
 /**
  * Returns true when users should be able to activate their accounts
  * by clicking on a link in an email after registration.
  */
 public function autoUserActivation() {
 	$mua = $this->cfg_system->getParameter("ManualUserActivation");
 	if(strtolower($mua) == "true") return false;
 	else return true;
 }
 
 /**
  * @return All super users from the configuration.
  */
 private function getSuperUsers() {
 	$su = $this->cfg_system->getParameter("SuperUsers");
 	$sus = explode(",", $su);
 	if($sus[0] == "") return array();
 	return $sus;
 }
 
 /**
  * Checks whether the current user is a super user or not.
  * @return True when the current user is a super user, otherwise false.
  */
 public function isUserSuperUser() {
 	return in_array($_SESSION["user"], $this->getSuperUsers());
 }
 
 /**
  * Creates a fraction of an SQL statement to add to a statement for the
  * user table. For example your statement is "SELECT * FROM user", then
  * you could add "WHERE " . createSuperUserSQLWhereStatement() to get
  * all super users from your selection. Make sure you add the statement
  * in parenthesis and concat it with an OR statement.
  * @return String with where statement.
  */
 private function createSuperUserSQLWhereStatement() {
 	$superUsers = $this->getSuperUsers();
 	$sql = "";
	foreach($superUsers as $i => $uid) {
		if($i > 0) $sql .= " OR ";
		$sql .= $this->dbcon->getUserTable() . ".id = $uid";
	}
	return $sql;
 }

 /**
  * @return An array with the IDs of the super user's contacts.
  */
 public function getSuperUserContactIDs() {
 	if(count($this->getSuperUsers()) == 0) return array();
 	
 	$query = "SELECT contact FROM " . $this->dbcon->getUserTable();
 	$query .= " WHERE " . $this->createSuperUserSQLWhereStatement();
 	$su = $this->dbcon->getSelection($query);
 	$contacts = array();
 	for($i = 1; $i < count($su); $i++) {
 		array_push($contacts, $su[$i]["contact"]);
 	}
 	return $contacts;
 }
 
 /**
  * @return The name of the group who can edit the share module.
  */
 public function getShareEditGroup() {
 	return "" . $this->cfg_system->getParameter("ShareEditGroup");
 }
}

?>