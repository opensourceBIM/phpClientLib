<?php
include_once "replacer.php";

class DakkappellenService extends Replacer  {
	public function getTitle($revision) {
		return "Dakkappellen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "dakkappellen.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "dakkappellen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg dakkappellen toe",
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