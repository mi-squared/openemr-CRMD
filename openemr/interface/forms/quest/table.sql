--
-- Table structure for table `form_quest`
--

CREATE TABLE IF NOT EXISTS `form_quest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `pid` bigint(20) NOT NULL,
  `user` varchar(255) NOT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `status` varchar(16) DEFAULT NULL,
  `priority` varchar(16) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `lab_id` varchar(25) DEFAULT NULL,
  `pat_fname` varchar(255) DEFAULT NULL,
  `pat_mname` varchar(255) DEFAULT NULL,
  `pat_lname` varchar(255) DEFAULT NULL,
  `pat_DOB` date DEFAULT NULL COMMENT 'used in result matching',
  `pat_ss` varchar(31) DEFAULT NULL,
  `pat_pubpid` varchar(31) DEFAULT NULL,
  `doc_npi` varchar(255) DEFAULT NULL,
  `doc_lname` varchar(255) DEFAULT NULL,
  `doc_fname` varchar(255) DEFAULT NULL,
  `doc_mname` varchar(255) DEFAULT NULL,
  `ins_primary` bigint(20) DEFAULT '0',
  `ins_secondary` bigint(20) DEFAULT '0',
  `diagnoses` text,
  `order_number` varchar(225) NOT NULL,
  `order_type` varchar(255) DEFAULT NULL,
  `order_psc` tinyint(4) DEFAULT NULL,
  `order_pending` datetime DEFAULT NULL,
  `order_req_id` varchar(255) DEFAULT NULL,
  `order_abn_id` varchar(255) DEFAULT NULL,
  `order_abn_signed` char(1) DEFAULT NULL,
  `order_notes` text,
  `work_flag` char(1) DEFAULT NULL,
  `work_insurance` varchar(25) DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `work_employer` varchar(25) DEFAULT NULL,
  `work_case` varchar(25) DEFAULT NULL,
  `request_billing` varchar(5) DEFAULT NULL,
  `request_account` varchar(25) DEFAULT NULL,
  `request_handling` varchar(255) DEFAULT NULL,
  `request_notes` text,
  `result_doc_id` varchar(255) DEFAULT NULL,
  `result_datetime` datetime DEFAULT NULL,
  `result_abnormal` int(5) DEFAULT '0',
  `reviewed_datetime` datetime DEFAULT NULL,
  `reviewed_id` int(11) DEFAULT NULL,
  `notified_datetime` datetime DEFAULT NULL,
  `notified_person` varchar(255) DEFAULT NULL,
  `notified_id` bigint(20) DEFAULT NULL,
  `review_notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_idx` (`order_number`),
  KEY `pid_idx` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_batch`
--

CREATE TABLE IF NOT EXISTS `procedure_batch` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `pid` bigint(20) NOT NULL,
  `user` varchar(255) NOT NULL,
  `groupname` varchar(255) NOT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `order_number` varchar(60) NOT NULL,
  `order_datetime` datetime DEFAULT NULL,
  `facility_id` varchar(255) NOT NULL,
  `provider_id` varchar(255) NOT NULL,
  `provider_npi` varchar(255) NOT NULL,
  `pat_dob` date DEFAULT NULL,
  `pat_first` varchar(255) NOT NULL,
  `pat_middle` varchar(255) NOT NULL,
  `pat_last` varchar(255) NOT NULL,
  `lab_number` varchar(60) NOT NULL,
  `lab_status` varchar(20) NOT NULL,
  `result_output` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_specimen`
--

CREATE TABLE IF NOT EXISTS `procedure_specimen` (
  `procedure_specimen_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `procedure_report_id` bigint(20) NOT NULL COMMENT 'references procedure_report.procedure_report_id',
  `specimen_number` varchar(255) DEFAULT '',
  `specimen_type` varchar(255) DEFAULT '',
  `type_modifier` varchar(255) DEFAULT '',
  `specimen_additive` varchar(255) DEFAULT NULL,
  `collection_method` varchar(255) DEFAULT '',
  `source_site` varchar(255) DEFAULT NULL,
  `source_quantifier` varchar(255) DEFAULT '',
  `specimen_volume` varchar(255) DEFAULT '',
  `specimen_condition` varchar(255) DEFAULT NULL,
  `specimen_rejected` varchar(255) DEFAULT NULL,
  `collected_datetime` datetime DEFAULT NULL,
  `received_datetime` datetime DEFAULT NULL,
  `detail_notes` text COMMENT 'OBX data content',
  PRIMARY KEY (`procedure_specimen_id`),
  KEY `procedure_report_id` (`procedure_report_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_facility`
--

CREATE TABLE IF NOT EXISTS `procedure_facility` (
  `code` varchar(31) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `street2` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `npi` varchar(31) DEFAULT NULL,
  `clia` varchar(25) DEFAULT NULL,
  `lab_id` bigint(20) NOT NULL COMMENT 'procedure_provider.ppid',
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `procedure_providers`
--

CREATE TABLE IF NOT EXISTS `procedure_providers` (
  `ppid` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `npi` varchar(15) NOT NULL DEFAULT '',
  `send_app_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'Sending application ID (MSH-3.1)',
  `send_fac_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'Sending facility ID (MSH-4.1)',
  `recv_app_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'Receiving application ID (MSH-5.1)',
  `recv_fac_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'Receiving facility ID (MSH-6.1)',
  `DorP` char(1) NOT NULL DEFAULT 'D' COMMENT 'Debugging or Production (MSH-11)',
  `type` varchar(12) NOT NULL DEFAULT 'INT',
  `protocol` varchar(15) NOT NULL DEFAULT 'DL',
  `remote_host` varchar(255) NOT NULL DEFAULT '',
  `remote_port` varchar(5) NOT NULL DEFAULT '22',
  `login` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `orders_path` varchar(255) NOT NULL DEFAULT '',
  `results_path` varchar(255) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  PRIMARY KEY (`ppid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_type`
--

CREATE TABLE IF NOT EXISTS `procedure_type` (
  `procedure_type_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references procedure_type.procedure_type_id',
  `name` varchar(63) NOT NULL DEFAULT '' COMMENT 'name for this category, procedure or result type',
  `lab_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references procedure_providers.ppid, 0 means default to parent',
  `procedure_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'code identifying this procedure',
  `procedure_type` varchar(31) NOT NULL DEFAULT '' COMMENT 'see list proc_type',
  `body_site` varchar(31) NOT NULL DEFAULT '' COMMENT 'where to do injection, e.g. arm, buttok',
  `specimen` varchar(31) NOT NULL DEFAULT '' COMMENT 'blood, urine, saliva, etc.',
  `transport` varchar(31) NOT NULL DEFAULT '',
  `route_admin` varchar(31) NOT NULL DEFAULT '' COMMENT 'oral, injection',
  `laterality` varchar(31) NOT NULL DEFAULT '' COMMENT 'left, right, ...',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'descriptive text for procedure_code',
  `standard_code` varchar(255) NOT NULL DEFAULT '' COMMENT 'industry standard code type and code (e.g. CPT4:12345)',
  `related_code` varchar(255) NOT NULL DEFAULT '' COMMENT 'suggested code(s) for followup services if result is abnormal',
  `units` varchar(31) NOT NULL DEFAULT '' COMMENT 'default for procedure_result.units',
  `range` varchar(255) NOT NULL DEFAULT '' COMMENT 'default for procedure_result.range',
  `seq` int(11) NOT NULL DEFAULT '0' COMMENT 'sequence number for ordering',
  `activity` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=active, 0=inactive',
  `notes` text NOT NULL COMMENT 'additional notes to enhance description',
  PRIMARY KEY (`procedure_type_id`),
  KEY `parent` (`parent`),
  KEY `lab_idx` (`lab_id`),
  KEY `code_idx` (`procedure_code`),
  FULLTEXT KEY `name_idx` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_order`
--

CREATE TABLE IF NOT EXISTS `procedure_order` (
  `procedure_order_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `provider_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references users.id, the ordering provider',
  `patient_id` bigint(20) NOT NULL COMMENT 'references patient_data.pid',
  `encounter_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references form_encounter.encounter',
  `date_collected` datetime DEFAULT NULL COMMENT 'time specimen collected',
  `date_ordered` datetime DEFAULT NULL,
  `order_priority` varchar(31) DEFAULT '',
  `order_status` varchar(31) DEFAULT '' COMMENT 'pending,routed,complete,canceled',
  `patient_instructions` text,
  `activity` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 if deleted',
  `control_id` varchar(25) DEFAULT NULL COMMENT 'This is the CONTROL ID that is sent back from lab',
  `lab_id` bigint(20) DEFAULT '0' COMMENT 'references procedure_providers.ppid',
  `specimen_type` varchar(31) DEFAULT '' COMMENT 'from the Specimen_Type list',
  `specimen_location` varchar(31) DEFAULT '' COMMENT 'from the Specimen_Location list',
  `specimen_volume` varchar(31) DEFAULT NULL COMMENT 'from a text input field',
  `specimen_fasting` varchar(31) DEFAULT NULL COMMENT 'YES / NO / blank',
  `specimen_duration` varchar(31) DEFAULT NULL COMMENT 'fasting duration text',
  `specimen_transport` varchar(31) DEFAULT NULL COMMENT 'required transport',
  `specimen_source` varchar(255) DEFAULT NULL,
  `date_transmitted` datetime DEFAULT NULL COMMENT 'time of order transmission, null if unsent',
  `clinical_hx` text COMMENT 'clinical history text that may be relevant to the order',
  PRIMARY KEY (`procedure_order_id`),
  KEY `datepid` (`date_ordered`,`patient_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_order_code`
--

CREATE TABLE IF NOT EXISTS `procedure_order_code` (
  `procedure_order_id` bigint(20) NOT NULL COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11) NOT NULL AUTO_INCREMENT COMMENT 'supports multiple tests per order',
  `procedure_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'like procedure_type.procedure_code',
  `procedure_name` varchar(255) DEFAULT '' COMMENT 'descriptive name of the procedure code',
  `procedure_source` char(1) DEFAULT '1' COMMENT '1=original order, 2=added after order sent',
  `procedure_type` varchar(31) DEFAULT NULL,
  `reflex_code` varchar(31) DEFAULT NULL,
  `reflex_set` varchar(31) DEFAULT NULL,
  `reflex_name` varchar(255) DEFAULT NULL,
  `diagnoses` text COMMENT 'diagnoses and maybe other coding (e.g. ICD9:111.11)',
  `do_not_send` tinyint(1) DEFAULT '0' COMMENT '0 = normal, 1 = do not transmit to lab',
  `labcorp_zseg` varchar(31) DEFAULT NULL,
  PRIMARY KEY (`procedure_order_id`,`procedure_order_seq`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_questions`
--

CREATE TABLE IF NOT EXISTS `procedure_questions` (
  `lab_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references procedure_providers.ppid to identify the lab',
  `procedure_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'references procedure_type.procedure_code to identify this order type',
  `question_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'code identifying this question',
  `seq` int(11) NOT NULL DEFAULT '0' COMMENT 'sequence number for ordering',
  `question_text` varchar(255) NOT NULL DEFAULT '' COMMENT 'descriptive text for question_code',
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = required, 0 = not',
  `maxsize` int(11) NOT NULL DEFAULT '0' COMMENT 'maximum length if text input field',
  `fldtype` char(1) NOT NULL DEFAULT 'T' COMMENT 'Text, Number, Select, Multiselect, Date, Gestational-age',
  `options` text NOT NULL COMMENT 'choices for fldtype S and T',
  `tips` varchar(255) NOT NULL DEFAULT '' COMMENT 'Additional instructions for answering the question',
  `activity` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = active, 0 = inactive',
  `section` varchar(31) NOT NULL DEFAULT '',
  PRIMARY KEY (`lab_id`,`procedure_code`,`question_code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `procedure_answers`
--

CREATE TABLE IF NOT EXISTS `procedure_answers` (
  `procedure_order_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11) NOT NULL DEFAULT '0' COMMENT 'references procedure_order_code.procedure_order_seq',
  `procedure_code` varchar(31) NOT NULL DEFAULT '',
  `question_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'references procedure_questions.question_code',
  `answer_seq` int(11) NOT NULL AUTO_INCREMENT COMMENT 'supports multiple-choice questions',
  `answer` varchar(255) DEFAULT '' COMMENT 'answer data',
  PRIMARY KEY (`procedure_order_id`,`procedure_order_seq`,`question_code`,`answer_seq`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_report`
--

CREATE TABLE IF NOT EXISTS `procedure_report` (
  `procedure_report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `procedure_order_id` bigint(20) DEFAULT NULL COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11) NOT NULL DEFAULT '1' COMMENT 'references procedure_order_code.procedure_order_seq',
  `date_collected` datetime DEFAULT NULL,
  `date_report` datetime DEFAULT NULL,
  `lab_id` bigint(20) DEFAULT NULL,
  `source` bigint(20) DEFAULT '0' COMMENT 'references users.id, who entered this data',
  `specimen_num` varchar(63) DEFAULT '',
  `report_status` varchar(31) DEFAULT '' COMMENT 'received,complete,error',
  `review_status` varchar(31) DEFAULT 'received' COMMENT 'pending review status: received,reviewed',
  `report_notes` text COMMENT 'notes from the lab',
  PRIMARY KEY (`procedure_report_id`),
  KEY `procedure_order_id` (`procedure_order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `procedure_result`
--

CREATE TABLE IF NOT EXISTS `procedure_result` (
  `procedure_result_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `procedure_report_id` bigint(20) NOT NULL COMMENT 'references procedure_report.procedure_report_id',
  `result_data_type` varchar(31) NOT NULL DEFAULT 'ST',
  `result_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'LOINC code, might match a procedure_type.procedure_code',
  `result_set` varchar(31) DEFAULT NULL,
  `result_text` varchar(255) DEFAULT NULL COMMENT 'Description of result_code',
  `date` datetime DEFAULT NULL COMMENT 'lab-provided date specific to this result',
  `facility` varchar(255) DEFAULT '' COMMENT 'lab-provided testing facility ID',
  `units` varchar(31) DEFAULT '',
  `result` text,
  `range` varchar(255) DEFAULT '',
  `abnormal` varchar(31) DEFAULT '' COMMENT 'no,yes,high,low',
  `comments` text COMMENT 'comments from the lab',
  `document_id` bigint(20) DEFAULT '0' COMMENT 'references documents.id if this result is a document',
  `result_status` varchar(31) DEFAULT '' COMMENT 'preliminary, cannot be done, final, corrected, incompete...etc.',
  PRIMARY KEY (`procedure_result_id`),
  KEY `procedure_report_id` (`procedure_report_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

