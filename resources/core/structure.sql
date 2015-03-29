#Create localusers table
DROP TABLE IF EXISTS `localusers`;
CREATE TABLE IF NOT EXISTS `localusers`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(32) NOT NULL,
email VARCHAR(64) NULL,
password VARCHAR(128) NOT NULL,
name VARCHAR(64) NOT NULL,
created DATETIME NOT NULL);

#Create adconnectionsettings table
DROP TABLE IF EXISTS `adconnectionsettings`;
CREATE TABLE IF NOT EXISTS `adconnectionsettings`(
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
setting VARCHAR(64) NOT NULL,
settingvalue VARCHAR(256) NOT NULL);

#Create emailreset table
DROP TABLE IF EXISTS `emailreset`;
CREATE TABLE IF NOT EXISTS `emailreset`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
userguid VARCHAR(64) NOT NULL,
code VARCHAR(32) NOT NULL,
createtime DATETIME NOT NULL);

#Create admingroups table
DROP TABLE IF EXISTS `admingroups`;
CREATE TABLE IF NOT EXISTS `admingroups`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
groupguid VARCHAR(64) NOT NULL);

#Create resetgroups table
DROP TABLE IF EXISTS `resetgroups`;
CREATE TABLE IF NOT EXISTS `resetgroups`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
groupguid VARCHAR(64) NOT NULL);

#Create emailsettings table
DROP TABLE IF EXISTS `emailsettings`;
CREATE TABLE IF NOT EXISTS `emailsettings`(
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
setting VARCHAR(64) NOT NULL,
settingvalue VARCHAR(256) NOT NULL);

#Create othersettings table
DROP TABLE IF EXISTS `othersettings`;
CREATE TABLE IF NOT EXISTS `othersettings`(
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
setting VARCHAR(64) NOT NULL,
settingvalue TEXT NOT NULL);

#Create secretquestions table
DROP TABLE IF EXISTS `secretquestions`;
CREATE TABLE IF NOT EXISTS `secretquestions`(
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
secretquestion TEXT NOT NULL,
enabled BIT NOT NULL);

#Create usersecretquestions table
DROP TABLE IF EXISTS `usersecretquestions`;
CREATE TABLE IF NOT EXISTS `usersecretquestions`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
userguid VARCHAR(64) NOT NULL,
secretquestion_id INT UNSIGNED NOT NULL,
secretanswer TEXT NOT NULL,
FOREIGN KEY fk_question(secretquestion_id) REFERENCES secretquestions(id));

#Create sqfailedattempts table
DROP TABLE IF EXISTS `sqfailedattempts`;
CREATE TABLE IF NOT EXISTS `sqfailedattempts`(
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
userguid VARCHAR(64) NOT NULL,
failuretime DATETIME NOT NULL);