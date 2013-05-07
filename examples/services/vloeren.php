<?php
class VloerenService {
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "vloeren";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg standaard vloeren toe",
				"identifier" => "default",
				"publicProfile" => true
			),
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg rib-casette vloeren toe",
				"identifier" => "ribcasette",
				"publicProfile" => true
			)
		);
	}
	
	public function getPrivateProfiles($serviceName, $token) {
		return null;
	}
	
	public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
		$bimServerApi = new BimServerApi($apiUrl, $token);
		$service = $bimServerApi->getService($soid);
		$revision = $bimServerApi->getRevision($roid);
		$user = $bimServerApi->getUserByUoid($revision["userId"]);
		$start = time() * 1000;
		$end = null;
		if ($revision["comment"] == "_start.ifc") {
			if ($profileIdentifier == "ribcasette") {
				$title = "Vervangen van de vloeren door ribcasettes voor revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Toevoegen Rib-Casette");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
				$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
				
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
				
				$mail->AddAddress($user["username"], $user["name"]);
				$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
				$mail->AddCC("demo@bimserver.org", "Demo");
	
				$mail->Subject = "RibCasette toegevoegd";
				$mail->AltBody = "RibCasette toegevoegd";
				$mail->MsgHTML("RibCasette toegevoegd");
				
				$mail->AddAttachment(getcwd() . "/files/_broodjes.ifc");
	
				if(!$mail->Send()) {
				  error_log("Mailer Error: " . $mail->ErrorInfo);
				}
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "_broodjes.ifc", $deserializer["oid"], getcwd() . "/files/_broodjes.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			} else if ($profileIdentifier == "default") {
				$title = "Vervangen van de vloeren door andere vloeren voor revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Vloeren vervangen");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
				$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
				
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
				
				$mail->AddAddress($user["username"], $user["name"]);
				$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
				$mail->AddCC("demo@bimserver.org", "Demo");
	
				$mail->Subject = "Vloeren vervangen";
				$mail->AltBody = "Vloeren vervangen";
				$mail->MsgHTML("Vloeren vervangen");
				
				$mail->AddAttachment(getcwd() . "/files/_vloeren.ifc");
	
				if(!$mail->Send()) {
				  error_log("Mailer Error: " . $mail->ErrorInfo);
				}
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "_vloeren.ifc", $deserializer["oid"], getcwd() . "/files/_vloeren.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
		}
	}
}
?>