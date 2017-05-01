<?php

class chinoData {

	private $repId = [repository ID];
	private $cusId = [customer ID];
	private $cusKey = [customer key];
	
	private $saltKey;
	private $httpHeader;
	private $debugData = array();
	
	public function __construct($salt = NULL, $rep = NULL, $cus = NULL, $ckey = NULL) {
		$this->dbg("Starting <b>CHINO.IO</b> repository engine class.");
		$this->repId = ($rep != NULL) ? $rep : $this->repId;
		$this->cusId = ($cus != NULL) ? $cus : $this->cusId;
		$this->cusKey = ($ckey != NULL) ? $ckey : $this->cusKey;
		$this->saltKey = $salt;
		$this->httpHeader = array("Content-Type: application/json");
	}
	
	private function dbg($msg) {
		$this->debugData[] = array(time() => $msg);
	}
	public function dbgData() {
		$retval = "<style type='text/css'>
						.row span {
							margin: 2px 10px 2px 2px;
						}
						.row {
							width: 800px;
							display: block;
							overflow: hidden;
							word-wrap: break-word;
						}
						.timestamp {
							font-size: 10px;
							color: red;
						}
					</style>";
		foreach ($this->debugData as $data) {
			foreach ($data as $timestamp => $debugdata) {
				$retval .= "<p class='row'><span class='timestamp'><i>$timestamp</i></span><span>$debugdata</span></p>";
			}
		}
		return $retval;
	}
	
	private function chino_init($url, $options = NULL, $post = TRUE, $upd = FALSE, $del = FALSE) {
		$this->dbg("Initializing <b>CHINO.IO</b> curl request...");
		$chino = curl_init();
		curl_setopt($chino, CURLOPT_URL, $url);
		curl_setopt($chino, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($chino, CURLOPT_HEADER, FALSE);
		if ($post == TRUE) {
			curl_setopt($chino, CURLOPT_POST, TRUE);
			curl_setopt($chino, CURLOPT_POSTFIELDS, json_encode($options));
		}
		if ($upd == TRUE) {
			curl_setopt($chino, CURLOPT_CUSTOMREQUEST, "PUT");
		}
		if ($del == TRUE) {
			curl_setopt($chino, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		curl_setopt($chino, CURLOPT_HTTPHEADER, $this->httpHeader);
		curl_setopt($chino, CURLOPT_USERPWD, $this->cusId . ":" . $this->cusKey);

		$this->dbg("Executing...");
		$response = curl_exec($chino);
		curl_close($chino);
		$this->dbg("Done.");
		return json_decode($response, true);
	}
	
	public function createSchema($description, $fields) {
			$this->dbg("Creating schema with ".count($fields)." fields.");
			$options['description'] = $description;
			$options['structure'] = array('fields' => $fields);
			return $this->chino_init("https://api.chino.io/v1/repositories/".$this->repId."/schemas", $options);
	}

	public function createRepository($description = "FUCK DESCRIPTIONS!") {
		$this->dbg("Creating repository with description: '$description'");
		$options['description'] = $description;
		print_r($this->chino_init("https://api.chino.io/v1/repositories", $options));
	}
	
	public function putChino($schema, $data) {
		$this->dbg("Initializing <b>CHINO.IO</b> data entry.");
		$options['content'] = $data;
		return $this->chino_init("https://api.chino.io/v1/schemas/$schema/documents", $options);
	}
	
	public function getChino($docid) {
		$this->dbg("<span style='color: red;'>Extraction <b>CHINO.IO</b> data entry...</span>");
		return $this->chino_init("https://api.chino.io/v1/documents/$docid", NULL, FALSE);
	}
	public function updChino($docid, $data) {
		$this->dbg("<span style='color: orange;'>Updating <b>CHINO.IO</b> data entry...</span>");
		$options['content'] = $data;
		return $this->chino_init("https://api.chino.io/v1/documents/$docid", $options, TRUE, TRUE);
	}
	public function delChino($docid) {
		$this->dbg("<span style='color: magenta;'>Deleting <b>CHINO.IO</b> data entry...</span>");
		return $this->chino_init("https://api.chino.io/v1/documents/$docid", NULL, FALSE, FALSE, TRUE);
	}
	
}

?>
