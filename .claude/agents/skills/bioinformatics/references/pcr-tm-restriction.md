# PCR, melting temperature, and restriction enzymes

## Primers and PCR

- Store and display primers 5′→3′ unless an API explicitly says otherwise.
- A reverse primer is the reverse complement of the sequence at the opposite amplicon boundary, not merely its complement.
- Define whether the returned amplicon includes primer sequences.
- Specify mismatch policy, ambiguous bases, multiple binding sites, circular templates, minimum/maximum product length, and whether matches are case-sensitive after normalization.
- For a unique linear amplicon, the forward site must precede the reverse-primer binding site in template coordinates. Multiple sites can produce zero, one, or several products depending on the API.

Test primer direction with a deliberately non-palindromic sequence; palindromic fixtures can conceal orientation bugs.

## Melting temperature

There is no context-free “correct Tm.” A result is defined by a formula or nearest-neighbor parameter set plus concentrations and correction choices.

Before changing Tm code, record:

- model and parameter source;
- oligonucleotide and complementary-strand concentrations;
- monovalent salt, magnesium, and dNTP assumptions;
- DNA/DNA, RNA/RNA, or hybrid duplex;
- self-complementarity treatment;
- mismatch, dangling-end, terminal, and solvent corrections;
- output unit and rounding.

The Wallace `2(A+T)+4(G+C)` rule is only a rough short-oligo estimate. Do not silently substitute it for a nearest-neighbor model or compare the two as if they share a contract. Preserve a legacy model behind a clearly named method if compatibility requires it.

Floating-point tests should use tolerances derived from the model and public rounding, not exact binary equality.

## Restriction sites

- Expand IUPAC ambiguity codes correctly in recognition sequences.
- Search both orientations when the enzyme/site is non-palindromic.
- Keep recognition-match coordinates separate from top- and bottom-strand cut coordinates.
- State coordinate origin, inclusive/exclusive ends, and whether negative/outside-site cuts are possible.
- For circular molecules, test a site spanning the origin and normalize cut positions modulo sequence length.
- Methylation sensitivity, star activity, partial digestion, and buffer compatibility are not implied by sequence matching; simulate them only when explicitly modeled.

Useful properties include rotation invariance for circular digestion and fragment-length conservation: fragments from a complete digest must sum to the original molecule length.

## Authoritative sources

- [SantaLucia, unified DNA nearest-neighbor thermodynamics](https://doi.org/10.1073/pnas.95.4.1460)
- [Owczarzy et al., magnesium correction](https://doi.org/10.1021/bi034621r)
- [REBASE restriction enzyme database](https://rebase.neb.com/rebase/rebase.html)

