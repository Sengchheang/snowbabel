#
# Add field to table 'be_groups'
#
CREATE TABLE be_groups (
	tx_snowbabel_extensions tinytext,
	tx_snowbabel_languages tinytext
);


#
# Add field to table 'be_users'
#
CREATE TABLE be_users (
	tx_snowbabel_extensions tinytext,
	tx_snowbabel_languages tinytext
);

CREATE TABLE tx_snowbabel_users (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	be_users_uid int(11) DEFAULT '0' NOT NULL,
	SelectedLanguages tinytext NOT NULL,
	ShowColumnLabel tinyint(4) DEFAULT '1' NOT NULL,
	ShowColumnDefault tinyint(4) DEFAULT '1' NOT NULL,
	ShowColumnPath tinyint(4) DEFAULT '0' NOT NULL,
	ShowColumnLocation tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);