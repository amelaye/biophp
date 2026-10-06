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

### Changed
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

### CI
- Codecov project and patch coverage checks are now informational.
