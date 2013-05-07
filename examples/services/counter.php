<?php
class CounterService {
	public function getDefinition() {
		return array(
			array(
					"__type" => "SProfileDescriptor",
					"identifier" => "ribcasette",
					"name" => "RibCasette",
					"description" => "Vervang vloeren door RibCasettes",
					"publicProfile" => true
			),
			array(
					"__type" => "SProfileDescriptor",
					"identifier" => "vloeren",
					"name" => "Vloeren",
					"description" => "Vervang vloeren door andere vloeren",
					"publicProfile" => true
			)
		);
	}
	
	public function getIdentifier() {
		return "counter";
	}

	public function getPrivateProfiles($token) {
		return null;
	}

	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Count all objects",
				"identifier" => "default",
				"publicProfile" => true				
			)
		);
	}
	
	public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
		$bimServerApi = new BimServerApi($apiUrl, $token);
		$service = $bimServerApi->getService($soid);
		$revision = $bimServerApi->getRevision($roid);
		$user = $bimServerApi->getUserByUoid($revision["userId"]);
		$start = time() * 1000;
		$end = null;
		$title = "Counting objects on revision " . $revision["id"];
		$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running PHP Quantizator");
		$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
		$response = $bimServerApi->getRevisionSummary($roid);
		
		$html = "<table><tr><th>Type</th><th>Amount</th></tr>";
		foreach ($response["list"] as $container) {
			$html .= "<tr><td colspan=\"2\">" . $container["name"] . "</td></tr>";
			foreach ($container["types"] as $type) {
				$html .= "<tr><td>" . $type["name"] . "</td><td>" . $type["count"] . "</td></tr>";
			}
		}
		$html .= "</table>";
		
		$extendedDataSchema = $bimServerApi->getExtendedDataSchemaByNamespace("http://extend.bimserver.org/htmlsummary");
		
		$bimServerApi->addExtendedDataToRevision($roid, "HTML Summary", $html, $extendedDataSchema["oid"]);
		
		$end = time() * 1000;
		$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
		$bimServerApi->unregisterProgressTopic($topicId);
	}
}
?>