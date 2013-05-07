<?php
class TrappenService {
	public function getDefinition() {
		return null;
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
	
	public function getIdentifier() {
		return "trappen";
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
				$title = "Vervangen van de trappen door andere trappen voor revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Trappen vervangen");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
				$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
				
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
				
				$mail->AddAddress($user["username"], $user["name"]);
				$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
				$mail->AddCC("demo@bimserver.org", "Demo");
	
				$mail->Subject = "Trappen toegevoegd";
				$mail->AltBody = "Trappen toegevoegd";
				$mail->MsgHTML("Trappen toegevoegd");
				
				$mail->AddAttachment(getcwd() . "/files/_trappen.ifc");
	
				if(!$mail->Send()) {
				  error_log("Mailer Error: " . $mail->ErrorInfo);
				}
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "_trappen.ifc", $deserializer["oid"], getcwd() . "/files/_trappen.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
		}
	}
}
?>