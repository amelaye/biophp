# Scientific testing strategy

## Build evidence in layers

1. **Golden examples:** small cases with results taken from an authoritative specification, primary source, or independent implementation.
2. **Boundary cases:** empty, one symbol/residue, exactly one window/codon/site, incomplete final unit, and maximum supported size.
3. **Alphabet cases:** lowercase if accepted, every ambiguity symbol, invalid symbols, and mixed alphabets.
4. **Metamorphic properties:** relationships that must hold without knowing one exact answer, such as reverse-complement involution or fragment-length conservation.
5. **Differential tests:** compare against an independent tool with exact version, options, and parameter set recorded.
6. **Regression test:** reproduce the defect first and show the correction fails without the code change.

## Avoid circular tests

A test is circular when it calculates the expected value with the same formula, lookup table, parser, or helper as production. Use literal values from a source, a separately implemented oracle, or a mathematical invariant.

For floating-point results:

- compare within a justified absolute or relative tolerance;
- test units and monotonic behavior where applicable;
- assert public rounding separately from internal precision;
- reject NaN and infinity unless the API explicitly permits them.

## High-value fixtures for BioPHP/BioTools

- IUPAC table coverage, including `V = [ACG]` and the `B ↔ V`, `D ↔ H`, `R ↔ Y`, `K ↔ M` complement pairs.
- Reverse-complement involution across ambiguous DNA.
- Standard and nonstandard genetic-code examples, ambiguous codons, stop codons, and incomplete codons.
- Alignment score reconstruction and input recovery after gap removal.
- Non-palindromic primer and restriction-site fixtures that expose orientation errors.
- Circular restriction site spanning the coordinate origin.
- Tm fixtures with all concentrations/model metadata recorded.
- Length-zero and length-one molecular-mass inputs to catch terminal correction errors.
- FCGR cell-sum conservation and skew sign transformations.
- Parser fixtures with multiple records, wrapped qualifiers, compound feature locations, and PDB insertion/alternate-location fields.

## Review severity

- **Critical:** can silently produce a materially false biological result for ordinary valid input.
- **High:** wrong strand, coordinate, genetic code, units, scoring model, or formula; or common valid input is corrupted.
- **Medium:** incorrect edge case, ambiguous-input policy, metadata loss, undocumented compatibility divergence, or misleading name/documentation.
- **Low:** missing evidence, fragile test, or clarity issue with no demonstrated output error.

Every finding must cite a file and line when possible, describe the scientific consequence, and propose the smallest confirming test. Separate confirmed defects from suspicions that require a source or oracle.

