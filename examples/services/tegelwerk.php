<?php
include_once "replacer.php";

class TegelwerkService extends Replacer  {
	public function getTitle($revision) {
		return "Tegelwerk toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "tegelwerk.ifc";
	}
		
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "tegelwerk";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg tegelwerk toe",
				"identifier" => "default",
				"publicProfile" => true
			)
		);
	}
	
	public function getPrivateProfiles($serviceName, $token) {
		return null;
	}
}
?>