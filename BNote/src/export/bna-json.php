<?php
/*
 * INTERFACE for BNote App
 * This interface can be called from anywhere in the web.
 * Parameters are given with the URL, results are returned as JSON.
 * 
 * Usage
 * -----
 * bna-json.php?pin=<pin>&func=<function name>[&parameter=value]*
 * 
 * @author Matti Maier
 */

require_once("bna-interface.php");

/*********************************************************
 * IMPLEMENTATION										 *
 *********************************************************/

class BNAjson extends AbstractBNA {
	
	/**
	 * Globally unique identifiers.
	 * @var boolean
	 */
	private $global_on = false;
	
	/**
	 * The URL where this BNote instance runs.
	 * @var string
	 */
	private $instanceUrl = "";
	
	function init() {
		if(isset($_GET["global"]) && $_GET["global"] != "") {
			$this->global_on = true;
		}
		
		$this->instanceUrl = $this->sysdata->getSystemURL();
		if(Data::startsWith($this->instanceUrl, "http://")) {
			$this->instanceUrl = substr($this->instanceUrl, 7); // cut prefix
		}
		
		header('Content-type: application/json; charset=utf-8');
	}
	
	function documentBegin() {
		echo "{ \"data\" : [ ";
	}
	
	function documentEnd() {
		echo "] }";
	}
	
	function beginOutputWith() {
		echo "{\n";
	}
	
	function endOutputWith() {
		echo "}";
	}
	
	function entitySeparator() {
		return ",";
	}
	
	function printEntities($selection, $line_node) {
		$this->beginOutputWith();
		echo '"' . $line_node . 's" : [';
		for($i = 1; $i < count($selection); $i++) {
			$e = $selection[$i];
			if($i > 1) echo $this->entitySeparator();
			echo "{";
			$j = 0;
			foreach($e as $index => $value) {
				if(is_numeric($index)) continue;
				if($j > 0) echo $this->entitySeparator();
				
				// conversions for globally unique identifiers
				if($this->global_on && $index == "id") {
					// singluar type
					echo '"type" : "' . $line_node . '"' . $this->entitySeparator() . ' ';
					$value = $this->instanceUrl . "/$line_node/$value"; 
				}
				
				echo "\"$index\" : \"" . $value . "\"";
				
				$j++;
			}
			echo "}";
		}
		echo ']';
		
		$this->endOutputWith();
	}
	
	function printVotes($votes) {
		$this->beginOutputWith();
		
		echo '"votes" : [';
		foreach($votes as $i => $vote) {
			if($i > 1) echo $this->entitySeparator();
			$this->beginOutputWith();
			
			$cnt = 0;
			if($this->global_on) {
				echo '"type": "vote"';
				$cnt++;
			}
			
			foreach($vote as $field => $value) {
				if($cnt > 0) echo $this->entitySeparator();
				
				if($field == "options") {
					echo '"options": [';
					$oc = 0;
					foreach($value as $j => $opt) {
						if($oc > 0) echo $this->entitySeparator();
						echo "{";
						
						if($this->global_on) {
							echo '"type": "vote_option",';
							echo '"id" : "' . $this->instanceUrl . "/vote_option/" . $opt["id"] . '", ';
						}
						else {
							echo '"id" : "' . $opt["id"] . '", ';
						}
						echo '"name": "' . $opt["name"] . '"';
						
						echo "}\n";
						$oc++;
					}
					echo "]";
				}
				else {
					if($field != "0" && $field == "id" && $this->global_on) {
						$value = $this->instanceUrl . "/vote/$value";
					}
					echo '"' . $field . '" : "' . $value . '"';
				}
				$cnt++;
			}
			
			$this->endOutputWith();
		}		
		echo ']';
		$this->endOutputWith();
	}
	
	function printVoteResult($vote) {
		$this->beginOutputWith();
		
		$cnt = 0;
		if($this->global_on) {
			echo '"type": "vote"';
			$cnt++;
		}
		
		foreach($vote as $voteField => $voteValue) {
			if($cnt > 0) echo $this->entitySeparator();

			if($voteField == "id" && $voteField != "0" && $this->global_on) {
				echo '"id": "' . $this->instanceUrl . "/vote/" . $vote["id"] . '"';
			}
			else if($voteField == "options") {
				echo '"options": [';
				$optCnt = 0;
				foreach($voteValue as $j => $opt) {
					if($optCnt > 0) echo $this->entitySeparator();
					$this->beginOutputWith();
					
					if($this->global_on) {
						echo '"type": "vote_option",';
						echo '"id": "' . $this->instanceUrl . "/vote_option/" . $opt["id"] . '",';
					}
					else {
						echo '"id": "' . $opt["id"] . '",';
					}
					echo '"name": "' . $opt["name"] . '",';
					
					echo '"choice": {';
					echo ' "0": "' . $opt["choice"]["0"] . '", ';
					echo ' "1": "' . $opt["choice"]["1"] . '", ';
					echo ' "2": "' . $opt["choice"]["2"] . '"';
					echo '}';
					
					$this->endOutputWith();
					$optCnt++;
				}
				echo ']';
			}
			else {
				echo '"' . $voteField . '": "' . $voteValue . '"';
			}
			
			$cnt++;
		}
		
		$this->endOutputWith();
	}
	
	function printRehearsals($rehs) {
		$this->beginOutputWith();
		echo '"rehearsals" : [';
		
		foreach($rehs as $i => $reh) {
			if($i > 1) echo $this->entitySeparator();
			echo "{";
			
			$rehC = 0;
			foreach($reh as $rehK => $rehV) {
				if($rehC > 0) echo $this->entitySeparator();
				
				if($rehK == "id" && $rehK != "0" && $this->global_on) {
					echo '"type": "rehearsal",';
					echo '"id": "' . $this->instanceUrl . "/rehearsal/" . $rehV . '"';
				}
				else if($rehK == "location") {
					echo '"location": {';
					$cntL = 0;
					foreach($rehV as $locK => $locV) {
						if($cntL > 0) echo $this->entitySeparator();
						
						if($locK == "id" && $locK != "0" && $this->global_on) {
							echo '"type": "location",';
							echo '"id": "' . $this->instanceUrl . "/location/" . $locV . '"';
						}
						else {
							echo "\"$locK\":\"$locV\"";
						}
						
						$cntL++;
					}
					echo '}';
				}
				else if($rehK == "participantsYes") {
					echo '"participantsYes": [';
					
					foreach($rehV as $j => $contact) {
						// TODO check why $j needs to be > 0 (and not 1)
						if($j > 0) echo $this->entitySeparator();
						echo "{";
						$cntC = 0;
						foreach($contact as $conK => $conV) {
							if($cntC > 0) echo $this->entitySeparator();
						
							if($conK == "id" && $conK != "0" && $this->global_on) {
								echo '"type": "contact",';
								echo '"id": "' . $this->instanceUrl . "/contact/" . $conV . '"';
							}
							else {
								echo "\"$conK\":\"$conV\"";
							}
						
							$cntC++;
						}
						echo "}";
					}
					echo ']';
				}
				else if($rehK == "participantsMaybe") {
					echo '"participantsMaybe": [';
					
					foreach($rehV as $j => $contact) {
						if($j > 1) echo $this->entitySeparator();
						echo "{";
						$cntC = 0;
						foreach($contact as $conK => $conV) {
							if($cntC > 0) echo $this->entitySeparator();
						
							if($conK == "id" && $conK != "0" && $this->global_on) {
								echo '"type": "contact",';
								echo '"id": "' . $this->instanceUrl . "/contact/" . $conV . '"';
							}
							else {
								echo "\"$conK\":\"$conV\"";
							}
						
							$cntC++;
						}
						echo "}";
					}
					echo ']';
				}
				else if($rehK == "participantsNo") {
					echo '"participantsNo": [';
					
					foreach($rehV as $j => $contact) {
						if($j > 1) echo $this->entitySeparator();
						echo "{";
						$cntC = 0;
						foreach($contact as $conK => $conV) {
							if($cntC > 0) echo $this->entitySeparator();
						
							if($conK == "id" && $conK != "0" && $this->global_on) {
								echo '"type": "contact",';
								echo '"id": "' . $this->instanceUrl . "/contact/" . $conV . '"';
							}
							else {
								echo "\"$conK\":\"$conV\"";
							}
						
							$cntC++;
						}
						echo "}";
					}
					echo ']';
				}
				else if($rehK == "participantsNoRepsonse") {
					echo '"participantsNoRepsonse": [';
					
					foreach($rehV as $j => $contact) {
						if($j > 1) echo $this->entitySeparator();
						echo "{";
						$cntC = 0;
						foreach($contact as $conK => $conV) {
							if($cntC > 0) echo $this->entitySeparator();
						
							if($conK == "id" && $conK != "0" && $this->global_on) {
								echo '"type": "contact",';
								echo '"id": "' . $this->instanceUrl . "/contact/" . $conV . '"';
							}
							else {
								echo "\"$conK\":\"$conV\"";
							}
						
							$cntC++;
						}
						echo "}";
					}
					echo ']';
				}
				else if($rehK == "participate") 
					{
						if ($rehV == "")
						{
							$rehV = "-1";
						}
						echo "\"$rehK\" : $rehV ";
					}
					
				
					else
					{
						echo "\"$rehK\" : \"" . $rehV . "\"";
					}
				
				$rehC++;
			}
			echo "}";
		}
		
		echo "]";
		$this->endOutputWith();
	}
	
	function printConcerts($concerts) {
		$this->beginOutputWith();
		
		echo "\"concerts\": [";
		
		foreach($concerts as $i => $concert) {
			if($i > 1) echo $this->entitySeparator();
			echo "{";
			$this->printEntityId($concert, "concert");
			
			foreach($concert as $conK => $conV) {
				if($conK == "id") continue;
				echo $this->entitySeparator();
				
				if($conK == "location") {
					echo '"location" : ';
					$this->writeEntity($conV, "location");
				}
				else if($conK == "contact") {
					echo '"contact" : ';
					$this->writeEntity($conV, "contact");
				}
				else if($conK == "program") {
					echo '"program" : ';
					$this->writeEntity($conV, "program");
				}
				else if($conK == "contacts") {
					echo '"contacts": [';
						
					foreach($conV as $j => $contact) {
						if($j > 1) echo $this->entitySeparator();
						echo "{";
						$cntC = 0;
						foreach($contact as $conK => $conV) {
							if($cntC > 0) echo $this->entitySeparator();
					
							if($conK == "id" && $conK != "0" && $this->global_on) {
								echo '"type": "contact",';
								echo '"id": "' . $this->instanceUrl . "/contact/" . $conV . '"';
							}
							else {
								echo "\"$conK\":\"$conV\"";
							}
					
							$cntC++;
						}
						echo "}";
					}
					echo ']';
				}
				else {
					echo "\"$conK\":\"$conV\"";
				}
			}
			
			echo "}";
		}
		
		echo "]\n";
		
		$this->endOutputWith();
	}
	
	private function printEntityId($entity, $entityName) {
		if($this->global_on) {
			echo '"type" : "' . $entityName . '",';
			echo '"id" : "' . $this->instanceUrl . "/$entityName/" . $entity["id"] . '"';
		}
		else {
			echo '"id" : "' . $entity["id"] . '"';
		}
	}
	
	function writeEntity($entity, $type) {
		$this->beginOutputWith();
		
		$this->printEntityId($entity, $type);
		
		foreach($entity as $attribute => $value) {
			if($attribute == "id") continue;
			echo $this->entitySeparator();
			echo "\"$attribute\":\"$value\"";
			$i++;
		}
		
		$this->endOutputWith();
	}
}

// run
new BNAjson();
