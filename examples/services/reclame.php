<?php
include_once "replacer.php";

class ReclameService extends Replacer  {
	public function getTitle($revision) {
		return "Reclame toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "reclame.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "reclame";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg reclame toe",
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