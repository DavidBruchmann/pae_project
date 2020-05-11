#
# Table structure for table 'tx_paeproject_projectelement_parent_mm'
# 
#
CREATE TABLE tx_paeproject_projectelement_parent_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_paeproject_projectelement_dependencies_mm'
# 
#
CREATE TABLE tx_paeproject_projectelement_dependencies_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_paeproject_projectelement_administrators_mm'
# 
#
CREATE TABLE tx_paeproject_projectelement_administrators_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_paeproject_projectelement_workers_mm'
# 
#
CREATE TABLE tx_paeproject_projectelement_workers_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_paeproject_projectelement'
#
CREATE TABLE tx_paeproject_projectelement (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	description text NOT NULL,
	worked_days varchar(28) DEFAULT '' NOT NULL,
	estimated_start_date int(11) DEFAULT '0' NOT NULL,
	estimated_duration float DEFAULT '0' NOT NULL,
	estimated_cost float DEFAULT '0' NOT NULL,
	real_start_date int(11) DEFAULT '0' NOT NULL,
	real_duration float DEFAULT '0' NOT NULL,
	cost_per_day float DEFAULT '0' NOT NULL,
	progress int(11) DEFAULT '0' NOT NULL,
	parent int(11) DEFAULT '0' NOT NULL,
	dependencies int(11) DEFAULT '0' NOT NULL,
	administrators int(11) DEFAULT '0' NOT NULL,
    workers int(11) DEFAULT '0' NOT NULL,
	documents blob NOT NULL,
	
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);




#
# Table structure for table 'tx_paeproject_exception_project_elements_mm'
# 
#
CREATE TABLE tx_paeproject_exception_project_elements_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_paeproject_exception'
#
CREATE TABLE tx_paeproject_exception (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	disable tinyint(3) DEFAULT '0' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	end_date int(11) DEFAULT '0' NOT NULL,
	project_elements int(11) DEFAULT '0' NOT NULL,
	affect_estimation tinyint(3) DEFAULT '0' NOT NULL,
	affect_real tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);