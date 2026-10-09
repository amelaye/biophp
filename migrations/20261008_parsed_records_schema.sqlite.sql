-- BioPHP schema upgrade : parsed-record tables (CHANGELOG, "Breaking (schema)")
-- From : the mapping of the last release, 1.5.1 (fc3ecb5)
-- To   : the current mapping of Domain/{Database,Sequence,Cloning,Variants}/Entity
-- Generated with Doctrine DBAL's schema Comparator from both mappings (naming strategy
-- underscore_number_aware, as the bundle configures it), as generated : SQLite rebuilds each
-- table. Checked : a database created with the former mapping and migrated with this script has
-- the same schema as one created with the current mapping.
-- plasmid, plasmid_feature and vcf_variant are new tables. The parsed-record tables could hold no
-- row before the change (flushing a parsed record failed), so no data is migrated. Copy these
-- statements into a migration of the application using the bundle.

CREATE TABLE plasmid (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sequence CLOB NOT NULL, description CLOB DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, metadata CLOB NOT NULL);
CREATE TABLE plasmid_feature (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position INTEGER NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, start_position INTEGER NOT NULL, end_position INTEGER NOT NULL, strand VARCHAR(8) NOT NULL, color VARCHAR(7) DEFAULT NULL, note CLOB DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, metadata CLOB NOT NULL, phase SMALLINT DEFAULT NULL, plasmid_id INTEGER NOT NULL, CONSTRAINT FK_4E96F8CF63598003 FOREIGN KEY (plasmid_id) REFERENCES plasmid (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_4E96F8CF63598003 ON plasmid_feature (plasmid_id);
CREATE TABLE vcf_variant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chrom VARCHAR(255) NOT NULL, position BIGINT NOT NULL, variant_id VARCHAR(255) DEFAULT NULL, reference CLOB NOT NULL, alternates CLOB NOT NULL, quality DOUBLE PRECISION DEFAULT NULL, filter VARCHAR(255) DEFAULT NULL, info CLOB NOT NULL);
CREATE INDEX idx_vcf_variant_locus ON vcf_variant (chrom, position);
CREATE TEMPORARY TABLE __temp__keyword AS SELECT keywords, prim_acc FROM keyword;
DROP TABLE keyword;
CREATE TABLE keyword (keywords VARCHAR(255) NOT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);
INSERT INTO keyword (keywords, prim_acc) SELECT keywords, prim_acc FROM __temp__keyword;
DROP TABLE __temp__keyword;
CREATE UNIQUE INDEX uniq_keyword ON keyword (prim_acc, keywords);
CREATE TEMPORARY TABLE __temp__src_form AS SELECT entry, prim_acc FROM src_form;
DROP TABLE src_form;
CREATE TABLE src_form (entry CLOB NOT NULL, prim_acc VARCHAR(50) NOT NULL, PRIMARY KEY (prim_acc));
INSERT INTO src_form (entry, prim_acc) SELECT entry, prim_acc FROM __temp__src_form;
DROP TABLE __temp__src_form;
CREATE TEMPORARY TABLE __temp__sp_databank AS SELECT db_name, pid1, pid2, prim_acc FROM sp_databank;
DROP TABLE sp_databank;
CREATE TABLE sp_databank (db_name VARCHAR(50) DEFAULT NULL, pid1 VARCHAR(100) DEFAULT NULL, pid2 VARCHAR(100) DEFAULT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);
INSERT INTO sp_databank (db_name, pid1, pid2, prim_acc) SELECT db_name, pid1, pid2, prim_acc FROM __temp__sp_databank;
DROP TABLE __temp__sp_databank;
CREATE INDEX sp_databank_prim_acc ON sp_databank (prim_acc);
CREATE TEMPORARY TABLE __temp__feature AS SELECT ft_key, ft_from, ft_to, ft_qual, ft_value, ft_desc, strand, prim_acc FROM feature;
DROP TABLE feature;
CREATE TABLE feature (ft_key VARCHAR(20) NOT NULL, ft_from INTEGER DEFAULT NULL, ft_to INTEGER DEFAULT NULL, ft_qual VARCHAR(60) NOT NULL, ft_value CLOB NOT NULL, ft_desc CLOB NOT NULL, strand VARCHAR(1) DEFAULT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ft_location CLOB DEFAULT NULL);
INSERT INTO feature (ft_key, ft_from, ft_to, ft_qual, ft_value, ft_desc, strand, prim_acc) SELECT ft_key, ft_from, ft_to, ft_qual, ft_value, ft_desc, strand, prim_acc FROM __temp__feature;
DROP TABLE __temp__feature;
CREATE INDEX feature_prim_acc ON feature (prim_acc);
CREATE TEMPORARY TABLE __temp__sequence AS SELECT prim_acc, entry_name, seq_length, start, "end", mol_type, date, source, sequence, description, organism, fragment FROM sequence;
DROP TABLE sequence;
CREATE TABLE sequence (prim_acc VARCHAR(50) NOT NULL, entry_name VARCHAR(50) NOT NULL, seq_length INTEGER DEFAULT NULL, start INTEGER DEFAULT NULL, "end" INTEGER DEFAULT NULL, mol_type VARCHAR(20) DEFAULT NULL, date VARCHAR(11) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, sequence CLOB NOT NULL, description CLOB DEFAULT NULL, organism CLOB DEFAULT NULL, fragment INTEGER DEFAULT NULL, PRIMARY KEY (prim_acc));
INSERT INTO sequence (prim_acc, entry_name, seq_length, start, "end", mol_type, date, source, sequence, description, organism, fragment) SELECT prim_acc, entry_name, seq_length, start, "end", mol_type, date, source, sequence, description, organism, fragment FROM __temp__sequence;
DROP TABLE __temp__sequence;
CREATE INDEX locus_name ON sequence (entry_name, mol_type);
CREATE TEMPORARY TABLE __temp__reference AS SELECT refno, base_range, title, medline, pubmed, remark, journal, comments, prim_acc FROM reference;
DROP TABLE reference;
CREATE TABLE reference (refno INTEGER DEFAULT 0 NOT NULL, base_range VARCHAR(255) DEFAULT NULL, title CLOB DEFAULT NULL, medline VARCHAR(20) DEFAULT NULL, pubmed VARCHAR(20) DEFAULT NULL, remark CLOB DEFAULT NULL, journal CLOB NOT NULL, comments CLOB DEFAULT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);
INSERT INTO reference (refno, base_range, title, medline, pubmed, remark, journal, comments, prim_acc) SELECT refno, base_range, title, medline, pubmed, remark, journal, comments, prim_acc FROM __temp__reference;
DROP TABLE __temp__reference;
CREATE UNIQUE INDEX uniq_reference ON reference (prim_acc, refno);
CREATE TEMPORARY TABLE __temp__gb_sequence AS SELECT strands, topology, division, segment_no, segment_count, version, ncbi_gi_id, prim_acc FROM gb_sequence;
DROP TABLE gb_sequence;
CREATE TABLE gb_sequence (strands VARCHAR(10) DEFAULT NULL, topology VARCHAR(10) DEFAULT NULL, division VARCHAR(10) DEFAULT NULL, segment_no INTEGER DEFAULT NULL, segment_count INTEGER DEFAULT NULL, version VARCHAR(50) DEFAULT NULL, ncbi_gi_id VARCHAR(30) DEFAULT NULL, prim_acc VARCHAR(50) NOT NULL, PRIMARY KEY (prim_acc));
INSERT INTO gb_sequence (strands, topology, division, segment_no, segment_count, version, ncbi_gi_id, prim_acc) SELECT strands, topology, division, segment_no, segment_count, version, ncbi_gi_id, prim_acc FROM __temp__gb_sequence;
DROP TABLE __temp__gb_sequence;
CREATE TEMPORARY TABLE __temp__accession AS SELECT accession, prim_acc FROM accession;
DROP TABLE accession;
CREATE TABLE accession (accession VARCHAR(50) NOT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);
INSERT INTO accession (accession, prim_acc) SELECT accession, prim_acc FROM __temp__accession;
DROP TABLE __temp__accession;
CREATE UNIQUE INDEX uniq_accession ON accession (prim_acc, accession);
CREATE TEMPORARY TABLE __temp__author AS SELECT refno, author, prim_acc FROM author;
DROP TABLE author;
CREATE TABLE author (refno INTEGER DEFAULT 0 NOT NULL, author VARCHAR(255) NOT NULL, prim_acc VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);
INSERT INTO author (refno, author, prim_acc) SELECT refno, author, prim_acc FROM __temp__author;
DROP TABLE __temp__author;
CREATE INDEX author_prim_acc ON author (prim_acc, refno);
