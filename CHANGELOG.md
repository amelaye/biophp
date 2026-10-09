# Changelog

All notable changes to BioPHP are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

Changes on `develop` since `master`.

### Added
- AAINDEX : the correlated entries (C, `getCorrelations()`) and the index values themselves (I,
  `getIndex()`, one per amino acid, null for "NA") are read ; they used to be left aside.
- Swiss-Prot : `getTaxonomyId()`, the NCBI taxonomy identifier of the OX line.
- PDB : HELIX and SHEET keep the insertion codes of their first and last residues
  (`getInitICode()`, `getEndICode()`).
- GenBank import keeps the /organism and /mol_type of the source feature in the plasmid's metadata,
  and the writer writes them back.

#### Plasmids and cloning
- `CircularDnaSequence` for origin-crossing rotation and slicing.
- `Plasmid` aggregate and `PlasmidFeature` annotations.
- Typed restriction enzyme catalog composing the Type II, IIb and IIs adapters.
- Circular restriction digestion and end-compatibility checking.
- GenBank-to-plasmid mapper, with feature and plasmid metadata.
- Circular-aware overlap queries between `PlasmidFeature` annotations.
- Gibson assembly homology-arm design and junction checking.

#### Alignment
- Needleman-Wunsch global pairwise alignment.
- Smith-Waterman local alignment with PAM250 substitution scoring.
- Semi-global (overlap) pairwise alignment.
- `PairwiseAlignmentResult::getIdentityOverLength()`, the identity BLAST and EMBOSS report (gap
  columns counted) ; `getIdentity()` keeps its over-aligned-positions convention, now documented.

#### File formats
- Standalone readers: FASTQ (Phred+33 quality decoding), GFF3, BED and VCF.
- Writers: FASTA, GenBank, GFF3 and BED.

#### Phylogenetics
- Newick parsing and neighbor-joining tree construction.
- UPGMA tree construction, reusing `DistanceMatrix`.

#### Sequence tools
- Codon Adaptation Index calculator.
- Codon usage table builder and codon optimizer.
- Coordinate-returning ORF finder.
- Primer GC% and nearest-neighbor melting temperature (Tm) calculation.
- Base-composition skew calculation and CpG island finder.
- Vertebrate mitochondrial genetic code table translation.

#### Persistence
- Doctrine entities `PlasmidRecord`, `PlasmidFeatureRecord` (feature order preserved) and
  `VcfVariantRecord`, with `PlasmidRecordMapper` and `VcfVariantRecordMapper` converting to and from
  the immutable `Plasmid` / `VcfVariant` domain objects, which stay independent of Doctrine.
  Rebuilding a domain object re-runs its validation.

#### CDS phase
- `PlasmidFeature` carries an optional CDS phase (0, 1 or 2), persisted in `plasmid_feature.phase`.
  It is read from and written to GFF3 column 8 and GenBank `/codon_start` (phase + 1). GFF3 output
  now always gives a CDS a phase, as the specification requires (0 when unknown).

#### Feature locations
- `Feature::getFtLocation()` keeps a feature's location exactly as the record wrote it
  (`join(265..402,673..781,...)`), in a new nullable `feature.ft_location` column : `ftFrom` and
  `ftTo` only keep its outer bounds. Filled by the GenBank and EMBL parsers.

### Fixed
- Semi-global alignment : two sequences whose best overlap scored zero or less threw an exception
  (an empty alignment) ; that overlap is now reported with its own score.
- Newick : blanks and newlines between tokens no longer end up in the names, [comments] and NHX
  annotations are skipped, quoted labels ('Homo sapiens (human)', '' for a quote) are read, and
  `toNewick()` quotes a name that needs it, so its output reads back.
- INSDC locations : order() and a segment of another entry ("J00194.1:100..202") gave from = 0, the
  other entry's coordinates were taken as this sequence's, and "102.110" gave 102..102. A join()
  crossing the origin of a circular sequence (join(4900..5000,1..100)) came through as 1..5000, the
  whole plasmid ; it now gives 4900..100, crossing the origin, as GenbankPlasmidMapper expects.
  Only on a record whose LOCUS/ID line says circular : on a linear one, a join() listed out of
  order (trans-spliced plant organelle genes) keeps its lowest start and highest end. A location
  lying on both strands (join(complement(a..b),c..d)) has a null strand rather than "-", and a
  complement() around another entry's segment no longer makes the feature "-".
  `Feature::isPartial()` tells a location marked "<" or ">".
- GenBank writer : a feature crossing the origin made the whole write throw ; it is written as
  join(start..length,1..end), or its complement(), and reads back as the same feature.
- Restriction digest : a custom enzyme (`parseEnzyme(..., "custom")`), described by its upper-strand
  cut only, was also searched on the other strand and cut there at the same offset, wrong for a
  Type IIS enzyme such as BsaI. Its site is now only searched as written ; reference enzymes, whose
  cuts are symmetric within their site, are still searched on both strands.
- EMBL : with the ID line of the layout before release 87, the entry name (HSERPG) was stored as
  the primary accession and the AC line's accession was lost. The accession is now the primary
  accession and the name the entry name.
- GenBank and Entrez LOCUS lines are read word by word : NCBI shifts every field when the name is
  longer than 16 characters (WGS contigs), and the fixed columns read the length as 1. ACCESSION and
  KEYWORDS are read over their continuation lines ; "REGION: 1..1000" is no secondary accession ;
  the period closing KEYWORDS and the lineage of ORGANISM is no longer kept on the last keyword or
  rank ("RefSeq", "Homo"). A GenBank or EMBL record cut short (no "//") no longer crashes.
- PRINTS : a record fetched from a file of several entries ran into the next ones, whose name and
  description replaced or extended its own.
- KEGG : PATHWAY lines are read one by one, whatever the identifier length (ec00010, ko00010) and
  with or without "PATH:" ; a GENES, REACTION... line wrapped onto an indented one continues its
  item ; ORTHOLOGY (the 2008 format) is read as ORTHOLOG ; a GENOME ENTRY "T01001  Complete  Genome"
  gives T01001.
- TRANSFAC : a sequence over several SQ lines gained a blank at each line, and its closing period ;
  a frequency matrix (0.25) was cast to integers, all zeros.
- ExPASy ENZYME : an alternate name (AN) wrapped over two lines is one name, not two.
- PDB : SEQRES reads DNA (DA, DC, DG, DT) and RNA chains, selenomethionine (MSE, as M),
  selenocysteine (U), pyrrolysine (O), ASX and GLX ; all gave X.
- PROSITE : the current DT layout ("01-APR-1990 CREATED;") is read, and a qualifier written several
  times in CC (/SITE) keeps all its values, as a list.
- NCBI journals : "ISSN (Print)" and "ISSN (Online)", the labels of today's J_Entrez.txt, are read.
- `isPalindrome()` and `findPalindrome()` found nothing in a lower-case sequence (any GenBank or
  EMBL record) ; `isPalindrome()` now pairs ambiguous symbols as `findPalindrome()` does (ACRYGT).
- Translation : a codon with an ambiguous base translates into the residue all its readings code
  for (TAR, a stop ; GAR, E ; YTR, L) and into X only when they disagree.
- `SequenceBuilder::molwt()` with no molecule type failed on a parsed record ("mRNA", "ss-DNA") :
  any RNA type is weighed as RNA, its T read as U, any DNA type as DNA.
- `ProteinManager::molwt()` returned FALSE for U, O, lower case and the stop ending a translated ORF.
- Alignment consensus : a column of gaps only gave "A" or "?" and was reported variant.
- `RestrictionEnzymeCatalog::findByRecognitionSequence()` reads both strands and either side of an
  "X or Y" site, as `RestrictionEnzymeManager::findRestEn()` does.
- `complement()` accepts X ; `symFreq()` ignores the case of the symbol ; `countCodons()` reads the
  codon_start of the first CDS only, not of the last one.
- GenBank writer : a feature with no metadata is written with its name as /label, which was lost ;
  the DEFINITION no longer gains a period at each round trip. GenBank import : features sharing a
  location are no longer merged, and a spliced feature (join of separate exons), imported as one
  span, is warned about.
- Circular digest : the N spacer of a Type IIS site (GGTCTCN'NNNN_) no longer flags every BsaI digest
  as matched by an ambiguous pattern. Overhangs are compared whatever their case.
- FASTQ : a record cut short no longer takes the next one with it, and a quality line too long is
  reported with its length.
- Codon usage tables leave out a codon with an ambiguous base (one N made the build throw) ;
  skews read U as T ; nearest-neighbor Tm refuses a primer under two bases ; `Median([])` and an
  undefined Almeida distance throw instead of warnings and a DivisionByZeroError.
- `CircularDnaSequence::subSequence()` returns a linear DnaSequence, which may be empty : a piece
  was a circular molecule of its own, and an empty one threw - a one-base Gibson overlap with a
  circular vector failed on it.
- BLOCKS : a sequence line whose protein name begins like a label (IDHP_HUMAN, ACON_YEAST) was read
  as an ID or AC line, opening a new entry.
- GenBank writer : DEFINITION and qualifier lines wrap at 79 characters. A Strand::NONE feature,
  which INSDC cannot write, came back on the direct strand : it is marked with /biophp_strand="none",
  a qualifier of this library's own that GenbankPlasmidMapper reads back (any other reader takes the
  plain range as the direct strand, as INSDC defines it). Two features sharing a location are also
  kept apart when a qualifier held once (/gene, /label, /codon_start...) comes again.
- Newick : an internal node named "" reads back as "" (written ''), not as a node with no name.
- Circular digest : two cuts at one upper-strand position but different lower-strand ones kept the
  first enzyme's end ; the end is UNKNOWN, with a warning.
- FASTQ : a file that looks Phred+64 encoded is warned about rather than read silently 31 too high.
- Newick : an unnamed leaf raises InvalidNewickException, not InvalidPhylogeneticTreeException ; a
  comment may follow ";" ; a label holding a blank is reported as such ; branch lengths are written
  in full (17 digits) and non-finite ones refused. `DistanceMatrix` reads rows keyed by label.
- PDB : the insertion code (column 27, "52A") and the MODEL of each atom (NMR ensembles) are read.
- PROSITE : a DR line's code is kept (`getCategory()` : T, N, P, ? or F) ; `isFamilyMember()` no
  longer counts a false negative (N), a member of the family, as a stranger to it.
- The Doctrine entities of parsed records could not be stored : every one but Sequence mapped its
  string primAcc as a relation, and flushing failed. primAcc is now a plain column, the entities
  holding several rows per sequence (Feature, Reference, Author, Accession, Keyword, SpDatabank)
  have their own generated id (one author per reference and one DR line per entry were all the
  former keys allowed), and the columns are wide enough for real data (NM_031438 overflowed
  prim_acc, "LINEAR" topology, "NM_031438.4" version).
- GC content had three definitions (N counted in the length or not, S counted or not). All now use
  `AbstractNucleicSequence::gcFraction()` : (G+C+S) over (A+C+G+T/U+S+W), as Biopython's
  gc_fraction ; "GCNN" gives 100 %, not 50 %.
- FASTQ records wrapped over several lines, allowed by the Sanger format, are read ; a quality line
  starting with "@" or "+" is still quality.
- VCF : REF must be bases (A, C, G, T, N, other IUPAC codes tolerated) ; vcf_variant.position is a
  64-bit column, some chromosomes being longer than 2^31 bases.
- GenBank : a reference ending with a JOURNAL wrapped onto a second line, as in every NCBI direct
  submission, made the parser step over the next section : FEATURES (every feature lost) or the next
  REFERENCE. The sequence no longer keeps the spaces between its blocks of ten (data/human.seq gave
  3836 characters for 3488 bp). Authors are no longer cut at every comma with their periods removed :
  "Roemer,T., Madden,K." gives "Roemer,T." and "Madden,K.", and the PubMed style of RefSeq records
  ("Sahni N, Yi S") is read as well. The consortia of a CONSRTM line, skipped so far, are authors.
- Swiss-Prot : a current UniProtKB entry (data/Q5K4E3.txt) crashed the parser. Both the original
  layout and the UniProt one are now read (ID, DT, structured DE and GN, RX "PubMed=", the 2019_11
  feature table) ; RT is the title and RL the journal, RP, RA and RL may span several lines, RG is
  kept as an author, FT continuation lines are no longer features, DR isoform tags are dropped,
  references are numbered from 1 as their RN line, the molecule type is "PRT" (not "PRT;"), and the
  fragment flag, which compared the description against two endings at once, was always 0.
- EMBL reads its feature table with GenBank's code (`ParseDbAbstractManager::parseInsdcFeature()`) :
  a location wrapped over several lines is kept whole and every GenBank qualifier fix applies. A
  reference is read whole (RC, multi-line RA and RL, RG, RX MEDLINE), author initials keep their
  period ("Smith J."), and the ID line of the layout before release 87 is read.
- Nearest-neighbor Tm (SantaLucia 1998), checked against Biopython's Tm_NN : the concentration term
  is ln(Ct/4), not ln(Ct/2), and ln(Ct) with the -1.4 e.u. symmetry term for a self-complementary
  primer ; Mg2+ counts as 120 x sqrt([Mg2+]) mM of Na+ (von Ahsen et al. 2001), not 140 x [Mg2+] ;
  a lower-case primer is no longer read as holding no base (Tm and GC% 0) ; an impossible
  concentration is rejected.
- Vertebrate mitochondrial code : an RNA codon (AUA, UGA, AGA, AGG) was translated with the
  standard code, as if the table did not apply to an mRNA.
- Keto skew is now (G+T-A-C)/N, keto bases (G, T) against amino ones (A, C). The formula taken from
  the legacy skews tool, (G+C-A-T)/N, opposed strong bases to weak ones : that is only
  2 x GC content - 1. biotools' skew plot still uses it.
- `countCodons()` counted the introns of a spliced CDS : data/demo.seq's 8-exon CDS now gives 484
  codons (its 483 residues plus the stop), not 863.
- Proteins accept selenocysteine (U), pyrrolysine (O) and the ambiguity codes B, Z and J, so a
  selenoprotein is no longer rejected ; `charge()` and `chemicalGroup()` classify them (J as I/L, O
  as neutral, the others as undetermined X).
- `translate()` no longer turns one or two bases left over at the end into a trailing "X".
- Gibson : each primer tail carried the whole overlap, so the two PCR products shared 2k bases for
  an overlap of k. The overlap is now split between the two tails, and `getOverlapLength()` is their
  sum.
- Reference data checked against REBASE (emboss files v404) and Dayhoff 1978, corrected in the test
  samples mirroring bioapi's DataFixtures (bioapi was corrected and reloaded the same way):
  - PAM250 W/H was +3 instead of -3.
  - Wrong cut fields or sites : Psp124BI/SacI (overhang -44 -> -4), BstKTI (GAT^C, 3' overhang),
    AbsI (CC^TCGAGG), AcoI (Y^GGCCR, was YCCGGR), HpyAV (CCTTC(6/5), pattern was SapI/HgaI),
    AbaSI (C(11/9), off by one), AjuI (extra cut mark), BauI (bottom-strand site never searched).
  - Neoschizomers listed as isoschizomers, so a lookup by those names returned another enzyme's
    cut : BspOI, BlsI, BssKI/BstSCI/StyD4I, FaeI/Hin1II/Hsp92II/NlaIII, Mly113I/NarI, SspDI and
    BtsCI now have their own entries, and CviAII, BmrFI and DinI their own cut.
  - Type IIS PleI had no forward entry (PpsI added as isoschizomer); ArsI, which cuts on both sides
    of its site, moved from Type IIS to Type IIB.
  - Type IIB AlfI was a copy of BcgI, CspCI's reverse orientation was wrong and Hin4I missed
    GAGNNNNNGTC.
- `Tests/Api/ReferenceDataConsistencyTest` guards this data : every entry must agree with its own
  annotated site, every search pattern must cover both orientations and every IUPAC expansion, and
  the corrected entries are checked against REBASE.
- Genetic code, pK, reduced alphabet, amino acid and nucleotide reference data, served by bioapi and
  mirrored by the test samples, checked against the NCBI genetic code tables, Solomon, IMGT
  (Pommié et al. 2004), ExPASy and OligoCalc :
  - The triplet list held GCG twice and no GGC.
  - Vertebrate mitochondrial code (NCBI 2) : ATA was in the Ile group instead of Met.
  - Degenerate back-translation codons : Thr was WSN (invertebrate mitochondrial) and WCN
    (echinoderm mitochondrial) instead of ACN, Asn was ATH (Ile codons) instead of AAH (flatworm
    mitochondrial), and the Scenedesmus obliquus stops were TVR instead of TVA.
  - Solomon pK of Arg was 125 instead of 12.5.
  - 3IMG hydropathy alphabet : M was neutral and H in no class ; neutral is G H P S T Y.
  - bioapi only : Gln's 3-letter code was "Gin" and Pyl's "Pyr", the weight ranges of Z (Glx) and X
    were swapped, B's upper weight was Asn's instead of Asp's, and nitrogen was named "nitrate".
  - Molecular weights have a single reference, Biopython's `Bio.SeqUtils.molecular_weight` (average
    masses) : `SequenceManager::molwt()` on canonical bases and `ProteinManager::molwt()` on a
    sequence without ambiguity code now equal it, and their tests pin values Biopython 1.88
    computed (ATGC 1253.8027 instead of 1253.945). Neither convention changes : a 5'-phosphate,
    3'-hydroxyl nucleic acid strand, and free amino acids less one water per peptide bond.
    - Nucleotide residue weights are Biopython's nucleoside monophosphates less one water (dA
      313.2065, dC 289.1818, dG 329.2059, dT 304.1932 ; A 329.2059, C 305.1812, G 345.2053,
      U 306.166) : each base weighed about 0.035 Da too much.
    - Amino acid weights are Biopython's `protein_weights`, with four decimals : Arg, Cys, Ile,
      Leu, Met and Trp were off by about 0.01, and residue weights, truncated (Cys 103.10 and Asn
      114.08 plain wrong), are now the free weights less one water ; B, Z, O, U and "*" get one.
    - Water weighs 18.0153, Biopython's average, in both molwt() methods.
    - bioapi stores amino acid weights as DECIMAL(8,4) instead of DECIMAL(5,2) (migration
      `Version20261009120000`).
- `Tests/Api/BiologicalReferenceDataTest` guards this data : 64 distinct codons, every species'
  groups and degenerate codons derived from its NCBI table, every reduced alphabet a partition of
  the 20 amino acids, pK values per source, IUPAC 3-letter codes, B/Z/X weight ranges, residue and
  nucleotide weights.
- `RestrictionEnzymeManager::cutSeq()` :
  - a degenerate site (AvaII, GGWCC) no longer yields every fragment twice ;
  - a pattern with alternatives (AciI, BbvCI, BssSI : "SITE1 or SITE2") now cuts ;
  - a non-palindromic site is also found on the other strand (AccBSI cuts GAGCGG) ;
  - option "O" now cuts every overlapping site and no longer loops forever on an enzyme cutting
    before its site (MboI, Sau3AI) ;
  - a sequence holding no site now gives one fragment, the whole sequence (option "N" gave none,
    option "O" a truncated one), and an option other than "N" or "O" is rejected.
- `findRestEn()` finds an enzyme by either alternative of its pattern or by its site read on the
  other strand, and `getLength()` gives the site length of a pattern with alternatives (4 for AciI,
  not 12). BssSI now appears among the 6 bp sites.
- `SequenceManager::patPoso()` rejects a cut position below 1, which never advanced the search.
- `CircularRestrictionDigestManager` finds overlapping sites : HhaI cuts GCGCGCGC three times, not
  twice.
- `RestrictionEnzymeCatalog` no longer registers the `@` rows of the Type IIS data (FokI@...),
  which are the reverse orientation of an enzyme's site, not enzymes.
- `RestrictionEndCompatibilityManager` reports two overhangs holding an ambiguous base (NNNN) as
  indeterminate instead of compatible.
- `ParseGenbankManager` only kept 5 feature keys (source, gene, exon, CDS, misc_feature) and
  silently dropped every other annotation (rep_origin, oriT, promoter, terminator, primer_bind,
  regulatory...). It now reads every INSDC feature key, the ones deprecated on 15-DEC-2014 included.
- `ParseGenbankManager` qualifier values : a "/" or "=" inside a value is no longer stripped or cut
  (`UniProtKB/Swiss-Prot`, `Km=2 mM`), a doubled `""` is unescaped, a flag qualifier (`/pseudo`) no
  longer crashes the whole record, a feature with no qualifier no longer swallows the next one, and
  a wrapped `/translation` no longer gains a space at each line break. A quoted value wrapped onto a
  line starting with "/" is no longer cut into a bogus extra qualifier.
- `GenbankPlasmidMapper` reads the INSDC `regulatory` + `/regulatory_class` form (promoter,
  terminator) ; `GenbankWriter` writes a promoter or terminator that way instead of the deprecated
  keys, and its LOCUS line now follows NCBI's fixed columns, so its output reads back.
- GenBank `oriT` (origin of transfer) was imported as an origin of replication ; it now stays a
  `MISC_FEATURE`, with its key kept in metadata.
- GFF3 origin-crossing features : the reader folds a feature written as end + landmark length on an
  `Is_circular=true` landmark back to start > end, and `GffFeatureWriter::write()` takes an optional
  sequence length to write one (with its circular landmark) instead of rejecting it.
- VCF : POS 0 is accepted (a telomere, per VCF 4.3), and percent-encoded INFO values are decoded
  (`%2C` excepted, to keep list commas unambiguous).

### Changed
- **Schema upgrade scripts :** `migrations/20261008_parsed_records_schema.{mysql,postgresql,sqlite}.sql`
  take an existing database from the former mapping of the parsed-record tables to the current one ;
  copy them into a migration of the application using the bundle. The SQLite script is checked (a
  migrated database matches a fresh one) ; the MySQL and PostgreSQL ones were not run on a server.
- ExPASy ENZYME : the description (DE) no longer keeps the period closing it, as AN and CA did not.
- VCF : each ALT allele is validated (bases, "*", <symbolic>, breakend, single breakend) ; an empty
  ALT column, which gave the allele "", is reported and the record skipped. A record stored before
  this check with that empty allele still reads back (`VcfVariantRecordMapper::toVariant()` drops it).
- `DistanceMatrix` refuses an infinite or NaN distance, from which UPGMA built NaN branch lengths.
- **Breaking (schema) :** the parsed-record tables (feature, reference, author, accession, keyword,
  sp_databank) gain an `id` primary key, `prim_acc` is a 50-character indexed column without foreign
  key, and several columns are widened ; generate a migration. No row could be stored before.
- `PdbAtomInterface` and `PrositeDbRefInterface` gained methods (insertion code, model, DR code),
  `PdbHelixInterface` and `PdbSheetInterface` the insertion codes of their ends.
- `AbstractMolecularSequence::subSequence()` declares `AbstractMolecularSequence` instead of
  `static`, so that `CircularDnaSequence` can return a linear piece ; every other class still
  returns its own kind. A subclass overriding it with `static` is unaffected.
- `molwt()` documents its convention : a 5'-phosphate, 3'-OH strand, 79.98 above a synthetic
  5'-OH oligonucleotide.
- `GffFeatureWriterInterface::write()` gained an optional `$iSequenceLength` parameter ; custom
  implementations of the interface must add it.
- **Breaking:** classes moved into role-based sub-namespaces (update your `use` statements ; service
  IDs and interface aliases are unchanged) :
  - `Domain\Cloning\Service\Reader` : `BedFeatureReader`, `GffFeatureReader`.
  - `Domain\Cloning\Service\Writer` : `BedFeatureWriter`, `GffFeatureWriter`, `GenbankWriter`.
  - `Domain\Cloning\Service\Mapper` : `GenbankPlasmidMapper`, `PlasmidRecordMapper`.
  - `Domain\Tools\Service\Codon` : `CodonAdaptationIndexCalculator`, `CodonOptimizer`,
    `CodonUsageTableBuilder`, `AlternateGeneticCodeTranslator`.
- Value Objects are now separated from Aggregates and Result DTOs; concrete
  classes are marked `final`.
- `strict_types` and native PHP 8 typing added across `Domain`, `Api` and
  `DependencyInjection`. Callers passing loosely typed values may need updating.
- `Domain/Tools` services are wired into the dependency injection container.
- The bioapi client now calls `https://api.amelayes-biophp.net` directly instead of being
  redirected from `http://`.

### CI
- Codecov project and patch coverage checks are now informational.
