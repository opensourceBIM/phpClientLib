<?
	try {
		include "../api/bimserverapi.php";
		include "phpmailer.inc.php";
		include "services/vloeren.php";
		include "services/dak.php";
		include "services/kalkzandsteen.php";
		include "services/kozijnen.php";
		include "services/trappen.php";
		include "services/counter.php";
		include "services/logger.php";
		include "services/bcfmailer.php";

		$data = file_get_contents('php://input');
		$bimServerApi = new BimServerApi("", null);

		class RemoteServiceHandler {
			private $services = array();

			public function __construct() {
				$this->registerService(new VloerenService());
				$this->registerService(new DakService());
				$this->registerService(new KalkzandsteenService());
				$this->registerService(new KozijnenService());
				$this->registerService(new TrappenService());
				$this->registerService(new CounterService());
				$this->registerService(new LoggerService());
				$this->registerService(new BcfMailerService());
			}

			function registerService($service) {
				$this->services[$service->getIdentifier()] = $service;
			}

			public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
				if (array_key_exists($serviceIdentifier, $this->services)) {
					return $this->services[$serviceIdentifier]->newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token, $apiUrl);
				}
				throw new Exception("Service " + $serviceName + " not found");
			}

			public function newExtendedData($roid, $edid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
				if (array_key_exists($serviceIdentifier, $this->services)) {
					return $this->services[$serviceIdentifier]->newExtendedData($roid, $edid, $serviceIdentifier, $profileIdentifier, $token, $apiUrl);
				}
				throw new Exception("Service " + $serviceName + " not found");
			}
	
			public function getPublicProfiles($serviceIdentifier) {
				if (array_key_exists($serviceIdentifier, $this->services)) {
					return $this->services[$serviceIdentifier]->getPublicProfiles();
				}
				throw new Exception("Service " + $serviceIdentifier + " not found");
			}
			
			public function getPrivateProfiles($serviceIdentifier, $token) {
				if (array_key_exists($serviceIdentifier, $this->services)) {
					return $this->services[$serviceIdentifier]->getPrivateProfiles($token);
				}
				throw new Exception("Service " + $serviceIdentifier + " not found");
			}
			
			public function getService($serviceIdentifier) {
				if (array_key_exists($serviceIdentifier, $this->services)) {
					return $this->services[$serviceIdentifier];
				}
				throw new Exception("Service " + $serviceIdentifier + " not found");
			}
		}

		echo $bimServerApi->processIncoming($data, new RemoteServiceHandler());
	} catch (Exception $e) {
		$response = array("response" => array(
			"exception" => array(
				"__type" => "UserException",
				"message" => $e->getMessage()
			)
		));
		echo json_encode($response);
	}
?>