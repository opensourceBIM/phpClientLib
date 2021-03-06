<?php
include_once "replacer.php";

class TrappenService extends Replacer  {
	public function getTitle($revision) {
		return "Trappen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "trappen.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "trappen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg trappen toe",
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