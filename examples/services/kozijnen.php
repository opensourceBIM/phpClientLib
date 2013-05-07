<?php
class KozijnenService {
	public function getDefinition() {
		return null;
	}
	
	public function getIdentifier() {
		return "kozijnen";
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg kozijnen toe",
				"identifier" => "default",
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
			if ($profileIdentifier == "default") {
				$title = "Kozijnen toevoegen revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Kozijnen toevoegen");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
				$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
				
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
				
				$mail->AddAddress($user["username"], $user["name"]);
				$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
				$mail->AddCC("demo@bimserver.org", "Demo");
	
				$mail->Subject = "Kozijnen toegevoegd";
				$mail->AltBody = "Kozijnen toegevoegd";
				$mail->MsgHTML("Kozijnen toegevoegd");
				
				$mail->AddAttachment(getcwd() . "/files/_kozijnen.ifc");
	
				if(!$mail->Send()) {
				  error_log("Mailer Error: " . $mail->ErrorInfo);
				}
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "_kozijnen.ifc", $deserializer["oid"], getcwd() . "/files/_kozijnen.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
		}
	}
}
?>