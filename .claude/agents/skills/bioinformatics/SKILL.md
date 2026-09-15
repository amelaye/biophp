---
name: bioinformatics
description: Apply scientific guardrails when implementing or reviewing BioPHP/BioTools logic for biological sequences, translation, alignment, PCR and melting temperature, restriction enzymes, molecular properties, CGR/FCGR, skews, or biological file parsers. Do not load for styling, routing, deployment, or generic PHP refactors that cannot change scientific results.
---

# Bioinformatics guardrails

Use this skill to keep BioPHP and BioTools scientifically correct while preserving deliberate public API compatibility.

## Core rules

1. Treat the current implementation and passing legacy tests as evidence of existing behavior, not proof of scientific correctness.
2. Before changing a calculation, identify its biological model, conventions, units, parameter set, and valid domain. If the code does not make them explicit, surface that ambiguity.
3. Separate these outcomes:
   - software defect: implementation contradicts its documented model;
   - scientific defect: the documented or inherited model is invalid for the claimed use;
   - compatibility difference: legacy output is intentional but differs from a preferred convention;
   - underspecified behavior: several defensible results exist because a convention is missing.
4. Never silently replace one scientific model with another, even when the replacement is more modern. Preserve compatibility or make the change explicit and tested.
5. Prefer an authoritative specification, primary paper, or trusted independent implementation as an oracle. Do not use another function in the same code path as the only oracle.
6. Add golden examples, boundary cases, and metamorphic properties. A test that merely copies the implementation's formula is weak evidence.
7. Keep biological validation separate from PHP quality, architecture, security, and performance review.

## Workflow

1. Locate the public contract, implementation, callers, existing tests, and any legacy compatibility notes.
2. State the scientific interpretation before editing: molecule type, strand direction, coordinates, alphabet, scoring model, physical assumptions, and output convention as applicable.
3. Read only the relevant reference files below.
4. Make the smallest coherent change. Do not broaden accepted inputs or alter normalization incidentally.
5. Test against an independent expected result and at least one property that should remain true across many inputs.
6. Report scientific assumptions, deliberate compatibility decisions, and anything that still requires external validation.
7. For changes that can alter scientific output, ask the `bioinfo-reviewer` subagent for an independent read-only pass.

## Reference routing

- DNA/RNA alphabets, complements, reverse complements, transcription, codons, ORFs, and translation: read [sequences-and-translation.md](references/sequences-and-translation.md).
- Global/local alignment, scoring matrices, gap penalties, traceback, and identity: read [alignments.md](references/alignments.md).
- Primers, PCR, melting temperature, restriction sites, and cut coordinates: read [pcr-tm-restriction.md](references/pcr-tm-restriction.md).
- Nucleic-acid/protein mass, pI, charge, extinction, and hydropathy: read [molecular-properties.md](references/molecular-properties.md).
- CGR, FCGR, k-mers, genomic signatures, GC/AT skew, and sliding windows: read [signatures-and-skews.md](references/signatures-and-skews.md).
- FASTA, GenBank, EMBL, UniProt/Swiss-Prot, PDB, and KEGG parsing: read [file-formats.md](references/file-formats.md).
- Test design, oracle choice, tolerances, regression coverage, and review severity: read [testing-strategy.md](references/testing-strategy.md).

## Required review questions

- Are inputs DNA, RNA, or protein, and are ambiguous symbols legal?
- Is every sequence represented and returned in a documented 5′→3′ direction?
- Are coordinates zero- or one-based, and are interval ends inclusive or exclusive?
- Are units and physical parameters visible at the API boundary or in documentation?
- Do empty, length-one, ambiguous, lowercase, invalid, and very large inputs behave deliberately?
- Are floating-point comparisons tolerant in a scientifically justified way?
- Does a trusted oracle agree for representative cases?
- Do invariants still hold after normalization, parsing, or round-tripping?

## Source hierarchy

Prefer, in order: an official format/specification; the primary algorithm or parameter paper; a maintained domain authority such as NCBI, EMBL-EBI, UniProt, or wwPDB; then a trusted independent implementation whose exact version and options are recorded. Blog posts and legacy code are not scientific authorities.

