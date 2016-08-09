CREATE TABLE IF NOT EXISTS `form_urinalysis_report` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    collection_date datetime default NULL,
    test_date datetime default NULL,
    physician int(11) default NULL,
    testers_initials varchar(255),
    exam_color varchar(255),
    exam_appearance varchar(255),
    chemical_exam_specific_gravity varchar(255),
    chemical_exam_ph varchar(255),
    chemical_exam_leukocytes varchar(255),
    chemical_exam_nitrate varchar(255),
    chemical_exam_protein varchar(255),
    chemical_exam_glucose varchar(255),
    chemical_exam_ketones varchar(255),
    chemical_exam_urobilinogen varchar(255),
    chemical_exam_bilirubin varchar(255),
    chemical_exam_blood varchar(255),
    chemical_exam_hemoglobin varchar(255),
    comments TEXT,
    PRIMARY KEY (id)
);
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Spec_Grav',
    title='Specific Gravity';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='1',
    title='1.000',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='2',
    title='1.005',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='3',
    title='1.010',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='4',
    title='1.015',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='5',
    title='1.020',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='6',
    title='1.025',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Spec_Grav',
    option_id='7',
    title='1.030',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Ph',
    title='pH';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ph',
    option_id='1',
    title='5',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ph',
    option_id='2',
    title='6',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ph',
    option_id='3',
    title='7',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ph',
    option_id='4',
    title='8',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ph',
    option_id='5',
    title='9',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Leukocytes',
    title='Leukocytes';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Leukocytes',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Leukocytes',
    option_id='2',
    title='Trace',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Leukocytes',
    option_id='3',
    title='+',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Leukocytes',
    option_id='4',
    title='++',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Nitrate',
    title='Nitrate';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Nitrate',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Nitrate',
    option_id='2',
    title='Pos',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Protein',
    title='Protein';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Protein',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Protein',
    option_id='2',
    title='Trace',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Protein',
    option_id='3',
    title='+/30',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Protein',
    option_id='4',
    title='++/100',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Protein',
    option_id='5',
    title='+++/500',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Glucose',
    title='Glucose';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='1',
    title='Normal',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='2',
    title='50',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='3',
    title='100',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='4',
    title='250',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='5',
    title='500',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Glucose',
    option_id='5',
    title='1000.000',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Ketones',
    title='Ketones';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ketones',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ketones',
    option_id='2',
    title='+ Small',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ketones',
    option_id='3',
    title='++ Med',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Ketones',
    option_id='4',
    title='+++ Large',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Urobilinog',
    title='Urobilinogen';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Urobilinog',
    option_id='1',
    title='Normal',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Urobilinog',
    option_id='2',
    title='1',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Urobilinog',
    option_id='3',
    title='4',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Urobilinog',
    option_id='4',
    title='8',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Urobilinog',
    option_id='5',
    title='12',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Bilirubin',
    title='Bilirubin';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Bilirubin',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Bilirubin',
    option_id='2',
    title='+',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Bilirubin',
    option_id='3',
    title='++',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Bilirubin',
    option_id='4',
    title='+++',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Blood',
    title='Blood';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Blood',
    option_id='1',
    title='Neg',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Blood',
    option_id='2',
    title='Trace',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Blood',
    option_id='3',
    title='50',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Blood',
    option_id='4',
    title='250',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Chem_Exam_Hemoglobin',
    title='Hemoglobin';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Hemoglobin',
    option_id='1',
    title='10',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Hemoglobin',
    option_id='2',
    title='50',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Chem_Exam_Hemoglobin',
    option_id='3',
    title='250',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Physical_Exam_Color',
    title='Urinalysis Physical Exam Color';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Color',
    option_id='1',
    title='Colorless',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Color',
    option_id='2',
    title='Yellow',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Color',
    option_id='3',
    title='Amber',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Color',
    option_id='4',
    title='Other',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinalysis_Physical_Exam_Appear',
    title='Urinalysis Physical Exam Appearance';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Appear',
    option_id='1',
    title='Clear',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Appear',
    option_id='2',
    title='Hazy',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Appear',
    option_id='3',
    title='Cloudy',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Appear',
    option_id='4',
    title='Turbid',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinalysis_Physical_Exam_Appear',
    option_id='5',
    title='Other',
    seq='5';

