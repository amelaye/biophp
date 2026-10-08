-- BioPHP schema upgrade : parsed-record tables (CHANGELOG, "Breaking (schema)")
-- From : the mapping of 1db8945 (before bed2e10, 7 October 2026)
-- To   : the current mapping of Domain/{Database,Sequence,Cloning,Variants}/Entity
-- Generated with Doctrine DBAL's schema Comparator from both mappings (naming strategy
-- underscore_number_aware, as the bundle configures it), then reordered by hand : every foreign
-- key is dropped first, and each new id column is declared the primary key in the statement adding it
-- (MySQL refuses an AUTO_INCREMENT column that is not a key yet, error 1075).
-- Not run against a live server : no MySQL or PostgreSQL instance was reachable when this was
-- written. The same comparison was checked on SQLite (see the .sqlite.sql script).
-- These tables could hold no row before the change (flushing a parsed record failed), so no data
-- is migrated. Copy these statements into a migration of the application using the bundle.

ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713BBCCCD4F0;
ALTER TABLE src_form DROP FOREIGN KEY FK_7BE58FA5BCCCD4F0;
ALTER TABLE sp_databank DROP FOREIGN KEY FK_E340167DBCCCD4F0;
ALTER TABLE feature DROP FOREIGN KEY FK_1FD77566BCCCD4F0;
ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913BCCCD4F0;
ALTER TABLE gb_sequence DROP FOREIGN KEY FK_5D3560FFBCCCD4F0;
ALTER TABLE accession DROP FOREIGN KEY FK_D983B075BCCCD4F0;
ALTER TABLE author DROP FOREIGN KEY FK_BDAFD8C8BCCCD4F0;
DROP INDEX IDX_5A93713BBCCCD4F0 ON keyword;
DROP INDEX `primary` ON keyword;
ALTER TABLE keyword ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE keywords keywords VARCHAR(255) NOT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
DROP INDEX uniq_src_form ON src_form;
ALTER TABLE src_form CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
ALTER TABLE sp_databank DROP INDEX uniq_sp_databank, ADD INDEX sp_databank_prim_acc (prim_acc);
DROP INDEX `primary` ON sp_databank;
ALTER TABLE sp_databank ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE db_name db_name VARCHAR(50) DEFAULT NULL, CHANGE pid1 pid1 VARCHAR(100) DEFAULT NULL, CHANGE pid2 pid2 VARCHAR(100) DEFAULT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
DROP INDEX uniq_feature ON feature;
DROP INDEX `primary` ON feature;
ALTER TABLE feature ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE ft_key ft_key VARCHAR(20) NOT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
ALTER TABLE feature RENAME INDEX idx_1fd77566bcccd4f0 TO feature_prim_acc;
ALTER TABLE sequence CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL, CHANGE entry_name entry_name VARCHAR(50) NOT NULL, CHANGE mol_type mol_type VARCHAR(20) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL;
DROP INDEX IDX_AEA34913BCCCD4F0 ON reference;
DROP INDEX `primary` ON reference;
ALTER TABLE reference ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE base_range base_range VARCHAR(255) DEFAULT NULL, CHANGE title title LONGTEXT DEFAULT NULL, CHANGE medline medline VARCHAR(20) DEFAULT NULL, CHANGE remark remark LONGTEXT DEFAULT NULL, CHANGE comments comments LONGTEXT DEFAULT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
CREATE UNIQUE INDEX uniq_reference ON reference (prim_acc, refno);
DROP INDEX uniq_gb_sequence ON gb_sequence;
ALTER TABLE gb_sequence CHANGE strands strands VARCHAR(10) DEFAULT NULL, CHANGE topology topology VARCHAR(10) DEFAULT NULL, CHANGE division division VARCHAR(10) DEFAULT NULL, CHANGE version version VARCHAR(50) DEFAULT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
DROP INDEX IDX_D983B075BCCCD4F0 ON accession;
DROP INDEX `primary` ON accession;
ALTER TABLE accession ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE accession accession VARCHAR(50) NOT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
DROP INDEX IDX_BDAFD8C8BCCCD4F0 ON author;
DROP INDEX `primary` ON author;
ALTER TABLE author ADD id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, CHANGE author author VARCHAR(255) NOT NULL, CHANGE prim_acc prim_acc VARCHAR(50) NOT NULL;
CREATE INDEX author_prim_acc ON author (prim_acc, refno);
ALTER TABLE vcf_variant CHANGE position position BIGINT NOT NULL;
