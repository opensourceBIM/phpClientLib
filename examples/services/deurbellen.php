<?php
include_once "replacer.php";

class DeurbellenService extends Replacer  {
	public function getTitle($revision) {
		return "Deurbellen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "deurbellen.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "deurbellen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg deurbellen toe",
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