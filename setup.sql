#
# Table structure for table 'forum_posting'
#
CREATE TABLE forum_posting (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  text text,
  ls_id int(11) DEFAULT '0',
  name varchar(100) DEFAULT '0',
  email varchar(200) DEFAULT '0',
  topic int(11) DEFAULT '0' NOT NULL,
  flags int(11) DEFAULT '0',
  PRIMARY KEY (id),
  KEY date_index (date)
);


#
# Table structure for table 'forum_topic'
#
CREATE TABLE forum_topic (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  forum int(11) DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '',
  pcount int(11) DEFAULT '0',
  PRIMARY KEY (id)
);

#
# Table structure for table 'guest'
#
CREATE TABLE guest (
  id int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  user int(11),
  flags int(11),
  PRIMARY KEY (id)
);

#
# Table structure for table 'news'
#
CREATE TABLE news (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  author int(11) DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  msg text,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  options int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
);

#
# Table structure for table 'orga'
#
CREATE TABLE orga (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  user int(11) DEFAULT '0' NOT NULL,
  rights int(11) DEFAULT '0' NOT NULL,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY (id)
);

#
# Table structure for table 'seat_block'
#
CREATE TABLE seat_block (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  party int(11) DEFAULT '0',
  rows int(11) DEFAULT '0',
  cols int(11) DEFAULT '0',
  orientation int(11) DEFAULT '0',
  name varchar(255) DEFAULT '',
  text_tl varchar(255) DEFAULT '',
  text_tc varchar(255) DEFAULT '',
  text_tr varchar(255) DEFAULT '',
  text_lt varchar(255) DEFAULT '',
  text_lc varchar(255) DEFAULT '',
  text_lb varchar(255) DEFAULT '',
  text_rt varchar(255) DEFAULT '',
  text_rc varchar(255) DEFAULT '',
  text_rb varchar(255) DEFAULT '',
  text_bl varchar(255) DEFAULT '',
  text_bc varchar(255) DEFAULT '',
  text_br varchar(255) DEFAULT '',
  color varchar(100) DEFAULT '0',
  PRIMARY KEY (id)
);

#
# Table structure for table 'seat_sep'
#
CREATE TABLE seat_sep (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  block int(11) DEFAULT '0',
  orientation int(11) DEFAULT '0',
  value int(11) DEFAULT '0',
  PRIMARY KEY (id)
);

#
# Table structure for table 'seats'
#
CREATE TABLE seats (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  block int(11) DEFAULT '0' NOT NULL,
  col int(11) DEFAULT '0' NOT NULL,
  row int(11) DEFAULT '0' NOT NULL,
  status int(11) DEFAULT '0' NOT NULL,
  guest int(11) DEFAULT '0',
  PRIMARY KEY (id)
);

#
# Table structure for table 'tourney_matches'
#
CREATE TABLE tourney_matches (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  tourney int(11),
  col int(11),
  row int(11),
  op1 int(11),
  op2 int(11),
  flags int(11),
  extratime int(11),
  score1 int(11) DEFAULT '0' NOT NULL,
  score2 int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
);

#
# Table structure for table 'tourney_results'
#
CREATE TABLE tourney_results (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  tourney int(11) DEFAULT '0' NOT NULL,
  matchid int(11) DEFAULT '0' NOT NULL,
  round int(11) DEFAULT '0' NOT NULL,
  score1 float(10,2) DEFAULT '0.00' NOT NULL,
  score2 float(10,2) DEFAULT '0.00' NOT NULL,
  flags int(11) DEFAULT '0' NOT NULL,
  map varchar(255),
  PRIMARY KEY (id)
);

#
# Table structure for table 'tourney_settings'
#
CREATE TABLE tourney_settings (
  mtch int(11),
  team int(11),
  map varchar(255),
  teamtype varchar(255),
  flags int(11),
  tourney int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tourney_teammember'
#
CREATE TABLE tourney_teammember (
  team int(11),
  user int(11)
);

#
# Table structure for table 'tourney_teams'
#
CREATE TABLE tourney_teams (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  tourney int(11),
  name varchar(255),
  leader int(11),
  def_map varchar(255),
  def_teamtype varchar(255),
  def_settings int(11),
  PRIMARY KEY (id)
);

#
# Dumping data for table 'tourney_teams'
#

INSERT INTO tourney_teams VALUES (9,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (7,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (8,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (6,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (5,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (4,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (3,0,'Reserved',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (2,0,'Freilos',1,NULL,NULL,NULL);
INSERT INTO tourney_teams VALUES (1,0,'Frei',1,NULL,NULL,NULL);
#
# Table structure for table 'tourneys'
#
CREATE TABLE tourneys (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  name varchar(255),
  teamsize int(11),
  maxteams int(11),
  endtime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  maplist longblob,
  teamtypes longblob,
  scorename1 varchar(255),
  scorename2 varchar(255),
  grp int(11),
  flags int(11),
  rules varchar(255),
  settings longblob,
  icon varchar(255),
  rounds int(11),
  roundtime int(11),
  roundpause int(11),
  doubletimecols int(11),
  drawhandling int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
);

#
# Table structure for table 'user'
#
CREATE TABLE user (
  id int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  clan varchar(255),
  name varchar(255),
  email varchar(255),
  pwd varchar(255) binary,
  realname1 varchar(255),
  realname2 varchar(255),
  homepage varchar(255),
  hometown varchar(255),
  birthyear int(11),
  flags int(11) DEFAULT '0',
  infotext varchar(255),
  forum_pagecount int(11) DEFAULT '0' NOT NULL,
  forum_signature text NOT NULL,
  PRIMARY KEY (id)
);

