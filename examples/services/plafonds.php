<?php
include_once "replacer.php";

class PlafondsService extends Replacer  {
	public function getTitle($revision) {
		return "Plafonds toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "plafonds.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "plafonds";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg plafonds toe",
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