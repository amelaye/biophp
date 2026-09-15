# Sequences and translation

## Alphabet and normalization

Keep molecule type explicit. `U` is not silently interchangeable with `T` unless the API documents normalization between RNA and DNA. Preserve or normalize case and whitespace only according to the public contract.

The IUPAC nucleic-acid ambiguity mapping is:

| Code | Bases | DNA complement |
| --- | --- | --- |
| A | A | T |
| C | C | G |
| G | G | C |
| T | T | A |
| R | A or G | Y |
| Y | C or T | R |
| S | C or G | S |
| W | A or T | W |
| K | G or T | M |
| M | A or C | K |
| B | C, G, or T | V |
| D | A, G, or T | H |
| H | A, C, or T | D |
| V | A, C, or G | B |
| N | A, C, G, or T | N |

`X` is not an IUPAC nucleotide ambiguity code. Accept it as an alias for `N` only where BioPHP/BioTools deliberately promises legacy compatibility.

Regression requirement: exercise every ambiguity code individually. In particular, `R` must expand to `[AG]` and `V` to `[ACG]`; do not let a duplicated `R` pattern hide a missing `V` case.

## Complement and reverse complement

Complement changes symbols without changing order. Reverse complement complements and then reverses. For DNA:

```text
ARYKBDHVN -> NBDHVMRYT
```

The operation should be an involution for valid normalized sequences:

```text
reverseComplement(reverseComplement(s)) == s
```

Test ambiguous codes, mixed case if supported, empty input, and length one. Decide whether invalid symbols cause an exception, replacement, or omission; never drop them accidentally.

## Transcription and translation

- State the source strand and direction. Coding DNA converted to RNA replaces `T` with `U`; template-strand handling also requires complementing and orientation.
- State the genetic code. The NCBI standard code is table 1, but mitochondrial and organism-specific codes differ.
- Define frame semantics precisely. Array offsets `0,1,2` are not the same notation as biological frames `+1,+2,+3`; reverse-strand frames require reverse complementation.
- Define stop representation (`*`, termination, or omission), incomplete terminal codons, invalid symbols, and whether the initial codon is treated specially.
- For an ambiguous codon, emit a definite amino acid only if every represented codon maps to that same amino acid under the selected code. Otherwise use the documented unknown/error behavior. For example, under the standard code `GCN` is alanine, while `TAN` is not uniquely determined.

A useful standard-code fixture is:

```text
ATGGCCATTGTAATGGGCCGCTGAAAGGGTGCCCGATAG
MAIVMGR*KGAR*
```

Do not assume translation begins at the first `ATG` or ends at the first stop unless the API specifically implements ORF finding.

## Authoritative sources

- [NCBI nucleotide ambiguity symbols](https://www.ncbi.nlm.nih.gov/staff/tao/URLAPI/ambiguity.html)
- [NCBI genetic codes](https://www.ncbi.nlm.nih.gov/Taxonomy/Utils/wprintgc.cgi)
- [IUPAC-IUB abbreviations for nucleic-acid bases](https://doi.org/10.1111/j.1432-1033.1985.tb08884.x)

