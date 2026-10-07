# Changelog

All notable changes to BioPHP are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

Changes on `develop` since `master`.

### Added

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
  `Feature::isPartial()` tells a location marked "<" or ">".
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
- **Breaking (schema) :** the parsed-record tables (feature, reference, author, accession, keyword,
  sp_databank) gain an `id` primary key, `prim_acc` is a 50-character indexed column without foreign
  key, and several columns are widened ; generate a migration. No row could be stored before.
- `PdbAtomInterface` and `PrositeDbRefInterface` gained methods (insertion code, model, DR code).
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
