<?php
include_once "replacer.php";

class DakkapService extends Replacer {
	public function getTitle($revision) {
		return "Dakkappen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "dakkap.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "dakkap";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg dakkap toe",
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