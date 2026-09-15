# Biological file formats

## General parser rules

- Identify the exact format variant and version; file extensions alone are insufficient.
- Keep parsing, semantic validation, and normalization separate.
- Never discard unknown fields, qualifiers, alternate locations, or feature structure silently when round-trip preservation is claimed.
- Define whether malformed records fail the entire file, produce partial records with diagnostics, or are skipped.
- Test LF and CRLF, wrapped lines, missing final newline, multiple records, empty records, non-ASCII descriptions, and large inputs.
- Preserve raw identifiers as strings. Numeric-looking accessions and residue identifiers can contain leading zeros, letters, or punctuation.

## FASTA

The first nonempty record line begins with `>`. Everything after it is a description unless the project intentionally applies a documented identifier convention. Sequence lines may be wrapped. Decide deliberately whether spaces, digits, `*`, gaps, lowercase, and comments are allowed for each alphabet.

Test multiple records and a final record without a trailing newline. Avoid treating the first whitespace-delimited header token as universally equivalent to the full description.

## GenBank and EMBL

- Locations are biologically one-based and inclusive in the flat-file notation, but internal code may use zero-based half-open intervals. Convert exactly once at the boundary.
- Preserve compound locations such as `join`, `order`, `complement`, fuzzy bounds, between-base sites, remote accessions, and strand information.
- A textual `complement(join(...))` cannot safely be reduced to a single start/end pair.
- Feature qualifiers can repeat and can contain wrapped quoted text.
- Test multiple records terminated by `//` and records with no feature table.

## UniProtKB/Swiss-Prot

Distinguish reviewed Swiss-Prot records from the broader UniProtKB flat-file family. Preserve repeated fields, evidence tags, feature coordinates, sequence length/checksum information, and record termination. Do not assume every `ID`, accession, or feature fits a single token.

## PDB

Legacy PDB is fixed-column, not whitespace-delimited. Respect alternate-location indicators, insertion codes, chain identifiers, occupancy, MODEL/ENDMDL boundaries, ATOM versus HETATM, negative coordinates, and blank fields. Do not collapse residue identity to an integer sequence number alone.

mmCIF is a different format and should not be parsed by a legacy PDB fixed-column parser.

## KEGG

KEGG flat files use field-oriented records with continuation lines and `///` termination. Treat database-specific schemas separately; do not infer one universal KEGG record shape.

## Authoritative specifications

- [NCBI GenBank flat-file release notes and format](https://www.ncbi.nlm.nih.gov/genbank/release/)
- [ENA flat-file format](https://ena-docs.readthedocs.io/en/latest/submit/fileprep/flat-file-example.html)
- [UniProt text format](https://www.uniprot.org/help/text-file)
- [wwPDB legacy PDB format](https://www.wwpdb.org/documentation/file-format)
- [wwPDB PDBx/mmCIF dictionary](https://mmcif.wwpdb.org/)
- [KEGG database entry format](https://www.kegg.jp/kegg/document/help_bget.html)

