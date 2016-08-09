CREATE TABLE IF NOT EXISTS `form_dub_soap` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    menstrual_history varchar(255),
    previous_sonogram varchar(255),
    previous_blood_work varchar(255),
    previous_needs_rx varchar(255),
    other TEXT,
    dub_objective varchar(255),
    a_dub varchar(255),
    plan_lab_work varchar(255),
    plan_tests_procedures varchar(255),
    plan_medications varchar(255),
    PRIMARY KEY (id)
) TYPE=InnoDB;
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Menstrual_History',
    title='DUB Menstrual History';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='1',
    title='Bleeding Interval',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='2',
    title='Duration',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='3',
    title='Flow',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='4',
    title='Menorrhagia',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='5',
    title='Menometrorrhagia',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='6',
    title='IM Bleeding',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='7',
    title='IM Pain',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='8',
    title='Dysmenorrhea',
    seq='8';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='9',
    title='Dyspareunia',
    seq='9';
INSERT IGNORE INTO list_options SET list_id='DUB_Menstrual_History',
    option_id='10',
    title='Other',
    seq='10';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Objective',
    title='DUB Objective';
INSERT IGNORE INTO list_options SET list_id='DUB_Objective',
    option_id='1',
    title='Abdomen',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Objective',
    option_id='2',
    title='Uterus',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Objective',
    option_id='3',
    title='Adnexae',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Objective',
    option_id='4',
    title='Cervix',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Objective',
    option_id='5',
    title='Other',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Diagnosis',
    title='DUB Diagnosis';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='1',
    title='DUB',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='2',
    title='Fibroids',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='3',
    title='Oral Contraception Breakthrough',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='4',
    title='Dysmenorrhea',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='5',
    title='Endometriosis',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='6',
    title='Polyps',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='7',
    title='Perimenopause',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='8',
    title='PMB',
    seq='8';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='9',
    title='PCOS',
    seq='9';
INSERT IGNORE INTO list_options SET list_id='DUB_Diagnosis',
    option_id='10',
    title='Other',
    seq='10';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Lab_Work',
    title='DUB Lab Work';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='1',
    title='CBC',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='2',
    title='TSH',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='3',
    title='CA125',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='4',
    title='FSH/Estradiol(E2)',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='5',
    title='PCOS Panel',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='6',
    title='beta-hcG',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='DUB_Lab_Work',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Tests_Procedures',
    title='DUB Tests Procedures';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='1',
    title='Sonogram',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='2',
    title='Endometria',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='3',
    title='D and C',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='4',
    title='Ablation',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='5',
    title='Hysteroscopy',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='6',
    title='Myomectomy',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='DUB_Tests_Procedures',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='DUB_Medications',
    title='DUB Tests Procedures';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='1',
    title='Provera/Prometrium',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='2',
    title='Oral Contraception',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='3',
    title='Lupron',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='4',
    title='Anti-inflammatories',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='5',
    title='Lysteda',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='6',
    title='Mirena',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='7',
    title='Change OCs',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='DUB_Medications',
    option_id='8',
    title='Other',
    seq='8';
