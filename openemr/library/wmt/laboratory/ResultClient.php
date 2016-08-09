<?php
/** **************************************************************************
 *	ResultClient.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package mdts
 *  @subpackage laboratory
 *  @version 2.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once 'ParserHL7v23.php';
require_once 'ParserHL7v251.php';

// must have phpseclib in path
$current_path = get_include_path();
if (strpos($current_path, 'phpseclib') === false)
	set_include_path($current_path . PATH_SEPARATOR . "{$GLOBALS['srcdir']}/phpseclib");

// include necessary libraries
include('Net/SSH2.php');
include('Net/SFTP.php');

if (!class_exists("ResultClient")) {
	/**
	 * class ResultClient submits lab order (HL7 messages) in an HL7 order
	 *	
	 */
	class ResultClient {
		private $STATUS; // D=development/training, V=validation, P=production
		private $ENDPOINT;
		private $USERNAME;
		private $PASSWORD;
		private $PROTOCOL;
		private $SENDING_APPLICATION;
		private $SENDING_FACILITY;
		private $RECEIVING_APPLICATION;
		private $RECEIVING_FACILITY;
		
		// data storage   	
    	private $request = null;
    	private $response = null;
    	private $messages = array();
    	private $documents = array();
    	
		private $DEBUG = false;
		
    	/**
		 * Constructor for the 'result client' class.
		 */
		public function __construct($lab_id) {
			// retrieve processor data
			$this->lab_id = $lab_id;
			$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($lab_id));
			if (!$lab_data['ppid'])
				throw new Exception("Missing processor information data.");			
			
			$this->lab_data = $lab_data;
			$this->REPOSITORY = $GLOBALS['oer_config']['documents']['repository'];
			
			// validate labs repository 
			if (!file_exists($GLOBALS["OE_SITE_DIR"]."/labs")) {
				mkdir($GLOBALS["OE_SITE_DIR"]."/labs");
			}
				
			$this->WORK_DIRECTORY = $GLOBALS["OE_SITE_DIR"]."/labs/".$lab_id."/";

			// validate work directory
			if (!file_exists($this->WORK_DIRECTORY)) {
				mkdir($this->WORK_DIRECTORY);
			}
				
			// validate backup directory
			if (!file_exists($this->WORK_DIRECTORY."backups/")) {
				mkdir($this->WORK_DIRECTORY."backups/");
			}

			$this->STATUS = 'D'; // default training
			if ($lab_data['DorP'] == 'P') $this->STATUS = 'P'; // production
			$this->SENDING_APPLICATION = $lab_data['send_app_id'];
			$this->SENDING_FACILITY = $lab_data['send_fac_id'];
			$this->RECEIVING_APPLICATION = $lab_data['recv_app_id'];
			$this->RECEIVING_FACILITY = $lab_data['recv_fac_id'];
			$this->RESULTS_PATH = $lab_data['results_path'];
			$this->ENDPOINT = $lab_data['remote_host'];
			$this->PROTOCOL = $lab_data['protocol'];
			$this->PORT = $lab_data['remote_port'];
			$this->USERNAME = $lab_data['login'];
			$this->PASSWORD = $lab_data['password'];
				
			$category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?",array($lab_data['name']));
			$this->DOCUMENT_CATEGORY = $category['id'];
				
			// sanity check
			if ($lab_data['protocol'] == 'DL' || $lab_data['protocol'] == 'FSC' || $lab_data['protocol'] == 'FC2' || $lab_data['protocol'] == 'WS') {
				if ( !$this->DOCUMENT_CATEGORY ||
						!$this->RECEIVING_APPLICATION ||
						!$this->RECEIVING_FACILITY ||
						!$this->SENDING_APPLICATION ||
						!$this->SENDING_FACILITY ||
						!$this->USERNAME ||
						!$this->PASSWORD ||
						!$this->ENDPOINT ||
						!$this->STATUS ||
						!$this->REPOSITORY )
					die ("Result Interface Not Properly Configured [".$lab_data['protocol']."]!!\n\n<pre>".var_dump($this)."</pre>\n\n");
			}
			elseif ($lab_data['protocol'] != 'INT') {
				if ( !$this->DOCUMENT_CATEGORY ||
						!$this->RECEIVING_APPLICATION ||
						!$this->RECEIVING_FACILITY ||
						!$this->SENDING_APPLICATION ||
						!$this->SENDING_FACILITY ||
						!$this->RESULTS_PATH ||
						!$this->STATUS ||
						!$this->REPOSITORY )
					die ("Result Interface Not Properly Configured [".$lab_data['protocol']."]!!\n\n<pre>".var_dump($this)."</pre>\n\n");
			}
			else { // internal only
				if ( !$this->DOCUMENT_CATEGORY ||
						!$this->STATUS ||
						!$this->REPOSITORY )
					die ("Order Interface Not Properly Configured [".$lab_data['protocol']."]!!\n\n<pre>".var_dump($this)."</pre>\n\n");
			}
			
			return;
		}

		/**
	 	 * Retrieve result 
	 	 * This routine dispatches to the correct retrieval routine based on
	 	 * the protocol type specified for the current processor (lab).
		 */
		public function getResults($max = 1, $DEBUG = false) {
			$response = null;
			$results = array();
			$this->messages = array();

			switch ($this->lab_data['protocol']) {
				case 'FSS': // file server
				case 'FS2': // file server
					$this->getFSSResults($max, $DEBUG);
					break;
				case 'FSC': // sFTP 2.3 client
				case 'FC2': // sFTP 2.5.1 client
					$this->getFSCResults($max, $DEBUG);
					break;
				default:
					throw new Exception("Lab Protocol Not Implemented");
			}
			
			return $this->messages;
		}
		
		/**
		 *
	 	 * The ackResult() method mark the result file processed by
	 	 * calling the 
	 	 *
		 */
		public function ackResult($message, $DEBUG = false) {
			try {
				switch ($message->file_type) {
					case 'FSS':
					case 'FS2':
						$this->ackFSSResult($message, $DEBUG);
						break;
					case 'FSC':
					case 'FC2':
						$this->ackFSCResult($message, $DEBUG);
						break;
					default:
						// processing from backup, do nothing
						break;
				}
			}
			catch (Exception $e) {
				echo ("\n\nFATAL ERROR: " . $e->getMessage());
			}
					
			return;
		}
				
		/**
		 * File Server (Drop Box) Interface
		 * The client machine provides an sFTP server which allows
		 * the lab to pick up and drop off order/result files.
		 */
		private function getFSSResults($max, $DEBUG) {
			// result directory
			$rdir = $this->RESULTS_PATH;
			$results = array();

			try {
				$new = 0;
				// anything waiting?
				$files = scandir($rdir); // return all contents
				if ($files) {
					foreach ($files AS $fname) {
						// allow either .hl7 or .txt as extensions
						if ( (strpos(strtoupper($fname),'.HL7') === false)
								&& (strpos(strtoupper($fname),'.TXT') === false) 
								&& (strpos(strtoupper($fname),'.GL7') === false) 
								&& (strpos(strtoupper($fname),'.DAT') === false)
						) continue;

						if ($new < $max) {
							// store the contents of the result file
							$new++;
							$results[] = $fname;
						}
						else { // stop fetching and just count records
							$more_results = true;
							break;
						}
					}
				}
				
				echo "\n".$new." Records Available";
				if ($more_results) echo " (MORE RESULTS)";
				
				if ($DEBUG) {
					if (count($results)) echo "\nHL7 Messages:";
				}
				
				if (count($results) > 0) {
					foreach ($results as $fname) {
						// check for empty files
						$size = filesize($rdir.$fname);
						if ($size == 0) continue; // skip empty files
						
						// retrieve the result file from the server
						$result = file_get_contents($rdir.$fname);
						if ($result === false) {
							throw new Exception("Failed to read '$fname' from results directory!!");
						}
					
						$options = '';
						if ($DEBUG) {
							echo "\n" . $result;
							$options = array('debug'=>true);
						}
					
						if ($this->PROTOCOL == 'FSS') $parser = new Parser_HL7v23($result,$options);
						else $parser = new Parser_HL7v251($result,$options);
						
						$parser->parse();
						$message = $parser->getMessage();
					
						$message->message_id = $result->resultId;
						$message->response_id = $response_id;
						$message->file_path = $rdir;
						$message->file_name = $fname;
						$message->file_type = $this->PROTOCOL;
						$message->hl7data = $result->HL7Message;
						
						// add the message to the results
						$this->messages[] = $message;
					}
				}
			} 
			catch (Exception $e) {
				die("\n\nFATAL ERROR: " . $e->getMessage());
			}
			
			return;
		}
		
		/**
		 * Private file server ack processing moves result file from 
		 * sFTP server space to private backup location.
		 * 
		 * @param string $path
		 * @param string $file
		 * @param boolean $DEBUG
		 * @throws Exception
		 */
		private function ackFSSResult($message, $DEBUG) {
			$rdir = $message->file_path;
			$ldir = $this->WORK_DIRECTORY;
			$bdir = $ldir."backups/";
			$fname = $message->file_name;
			
			if ($message->file_type != 'BACKUP') { 
				if (file_exists($bdir.$fname)) unlink($bdir.$fname); // make sure no old version
				$status = copy ($rdir.$fname, $bdir.$fname);
				if ($status) $status = unlink ($rdir.$fname);
//				$status = rename ($rdir.$fname, $bdir.$fname);
				if ($status === false)
					throw new Exception("Acknowledging and archiving ('$rdir.$fname')");
			}

			return;
		}
		
		
		/**
		 * File Client (pull) Interface
		 * The lab machine provides an sFTP server which allows
		 * the client to pick up and drop off order/result files.
		 */
		private function getFSCResults($max, $DEBUG) {
			$response = null;
			$results = array();
			$more_results = false;
			$this->messages = array();
			
			// result directories
			$rdir = $this->RESULTS_PATH;
			$ldir = $this->WORK_DIRECTORY;
			$bdir = $ldir."backups/";
				
			try {
				$new = 0;
				$old = 0;
				
				// validate directory
				if (!file_exists($ldir) || !file_exists($bdir)) {
					throw new Exception("Missing working lab results directory!!");
				}
						
				// anything waiting?
				$files = scandir($ldir); // requeue old records
				if ($files) {
					foreach ($files AS $fname) {
						// allow either .hl7 or .txt as extensions
						if ( (strpos(strtoupper($fname),'.HL7') === false)
								&& (strpos(strtoupper($fname),'.TXT') === false) 
								&& (strpos(strtoupper($fname),'.GL7') === false) 
								&& (strpos(strtoupper($fname),'.DAT') === false)
						) continue;
						
						$results[] = $fname;
						$old++;
					}
				}
				
				echo "\n".$old." Existing Records";
				
				// scanity check before doing anything
				if ( isset($this->USERNAME) && isset($this->PASSWORD) && isset($this->ENDPOINT)) {
					$sftp = new Net_SFTP($this->ENDPOINT,$this->PORT);
					if (!$sftp->login($this->USERNAME, $this->PASSWORD)) {
						throw new Exception("sFTP session did not initialize!!");
					}
			
					// get result content list
					$sftp->chdir($rdir);
					$newdir = $sftp->pwd();
					$rlist = $sftp->rawlist();
					
					// get results
					if (count($rlist) > 0) {
						foreach ($rlist AS $fname => $fattr) {
							// allow either .hl7 or .txt as extensions
							if ( (strpos(strtoupper($fname),'.HL7') === false)
									&& (strpos(strtoupper($fname),'.TXT') === false) 
									&& (strpos(strtoupper($fname),'.GL7') === false) 
									&& (strpos(strtoupper($fname),'.DAT') === false)
							) continue;
							
							if ($new < $max) {
								// store the contents of the result file
								$new++;
								$results[] = $fname;
								if ($sftp->get($fname,$ldir.$fname) === false) {
									throw new Exception("Encountered while retrieving '$fname' from server!!");
								}

								// have local copy so delete remote original
								$sftp->delete($fname);
							}
							else { // stop fetching and just count records
								$more_results = true;
							}
						}
					}
				}
				
				echo "\n".$new." Results Returned";
				if ($more_results) echo " (MORE RESULTS)";

				if ($DEBUG) {
					if (count($results) > 0) echo "\nHL7 Messages:";
				}

				// loop through each result record
				if (count($results) > 0) {
					foreach ($results as $fname) {
						// check for empty files
						$size = filesize($ldir.$fname);
						if ($size == 0) continue; // skip empty files
						
						$result = file_get_contents($ldir.$fname);
						if ($result === false) {
							throw new Exception("Failed to read '$fname' from results directory!!");
						}
					
						$options = '';
						if ($DEBUG) {
							echo "\n" . $result;
							$options = array('debug'=>true);
						}
					
						if ($this->PROTOCOL == 'FSC') $parser = new Parser_HL7v23($result,$options);
						else $parser = new Parser_HL7v251($result,$options);
						
						$parser->parse();
						$message = $parser->getMessage();
					
						$message->message_id = $result->resultId;
						$message->response_id = $response_id;
						$message->file_path = $ldir;
						$message->file_name = $fname;
						$message->file_type = $this->PROTOCOL;
						$message->hl7data = $result->HL7Message;
						
						// add the message to the results
						$this->messages[] = $message;
					}
				}
			} 
			catch (Exception $e) {
				die("\n\nFATAL ERROR: " . $e->getMessage());
			}
			
			return;
		}
		
		/**
		 * Private file client ack processing moves result file from 
		 * sFTP server space to private backup location.
		 * 
		 * @param string $path
		 * @param string $file
		 * @param boolean $DEBUG
		 * @throws Exception
		 */
		private function ackFSCResult($message, $DEBUG) {
			$ldir = $message->file_path;
			$fname = $message->file_name;
			
			if ($message->file_type != 'BACKUP') { 
				if (file_exists($ldir.'backups/'.$fname)) unlink($ldir.'backups/'.$fname); // make sure no old version
				$status = rename ($ldir.$fname, $ldir.'/backups/'.$fname);
				if ($status === false)
					throw new Exception("Acknowledging and archiving ('$ldir.$fname')");
			}

			return;
		}
		
		
		/**
		 *
	 	 * Repeat processing of result 
		 *
		 */
		public function repeatResults($max = 1, $from = FALSE, $thru = FALSE, $DEBUG = FALSE) {
			$response = null;
			$results = array();
			$more_results = false;
			$this->messages = array();

			// local backup result directory
			$bdir = $this->WORK_DIRECTORY."/backups/";
				
			try {
				// scanity check before doing anything
				if (!file_exists($bdir)) {
					throw new Exception("Missing backup lab results directory!!");
				}
						
				// get result content list
				$rlist = scandir($bdir); // get dir content list
					
				// get results
				$count = 0;
				if (count($rlist) > 0) {
					foreach ($rlist AS $fname) {
						// allow either .hl7 or .txt as extensions
						if ( (strpos(strtoupper($fname),'.HL7') === false) && (strpos(strtoupper($fname),'.TXT') === false) ) continue;

						$fdate = filemtime($bdir.$fname);
						$last = date('Ymd',$fdate);
						if ($last < $from || $last > $thru) continue; // not in selected range
						
						// store the contents of the result file
						$results[$count++] = $fname;
					}
				}
				
				echo "\n".count($results)." Results Qualified";
				if ($more_results) echo " (MORE RESULTS)";
				if ($DEBUG) {
					if (count($results)) echo "\n\nHL7 Messages:";
				}
				
				foreach ($results as $fname) {
					$result = file_get_contents($bdir.$fname);
					if ($result === false) {
						throw new Exception("Failed to read '$fname' from backup lab directory!!");
					}
					
					if ($DEBUG) echo "\n" . $result;
					
					if ($this->PROTOCOL == 'FS2' || $this->PROTOCOL == 'FC2') 
						$parser = new Parser_HL7v251($result);
					else 
						$parser = new Parser_HL7v23($result);
						
					$parser->parse();
					$message = $parser->getMessage();
					
					$message->message_id = $result->resultId;
					$message->response_id = $response_id;
					$message->file_path = $bdir;
					$message->file_name = $fname;
					$message->file_type = 'BACKUP';
					$message->hl7data = $result;
						
					// add the message to the results
					$this->messages[] = $message;
				}
			} 
			catch (Exception $e) {
				die("\n\nFATAL ERROR: " . $e->getMessage());
			}
			
			return $this->messages;
		}
		

		public function getProviderAccounts() {
			$results = array();
			try {
				$results = $this->service->getProviderAccounts();
				echo "\n".count($results)." Results Returned";
				
				echo "\nProviders:";
				var_dump($results);
			} 
			catch (Exception $e) {
				echo "\n\n";
				echo($e->getMessage());
			}
			
			return;
		}
	}
}
