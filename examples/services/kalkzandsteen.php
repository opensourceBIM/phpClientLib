<?php
include_once "replacer.php";

class KalkzandsteenService extends Replacer  {
	public function getTitle($revision) {
		return "Kalkzandsteen toegevoegd aan revisie " . $revision["id"];
	}
	
	public function getFilename() {
		return "kalkzandsteen.ifc";
	}
	
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "kalkzandsteen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg kalkzandsteen toe",
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