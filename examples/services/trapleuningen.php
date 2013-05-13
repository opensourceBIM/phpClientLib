<?php
include_once "replacer.php";

class TrapleuningenService extends Replacer  {
	public function getTitle($revision) {
		return "Trapleuningen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "trapleuningen.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "trapleuningen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg trapleuningen toe",
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