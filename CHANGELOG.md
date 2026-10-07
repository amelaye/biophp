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

### Fixed
- Reference data checked against REBASE (emboss files v404) and Dayhoff 1978, corrected in the test
  samples mirroring bioapi's DataFixtures (bioapi itself must be corrected and reloaded the same way):
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
