# Sequence alignments

## Identify the algorithm and scoring contract

- Needleman-Wunsch is global: the complete input sequences participate in the alignment unless a semi-global/free-end variant is explicitly implemented.
- Smith-Waterman is local: the dynamic-programming score has a zero floor and traceback starts from a highest-scoring cell.
- Linear and affine gaps are different models. For affine gaps, record whether a gap of length `k` costs `open + (k - 1) * extend` or `open + k * extend`.
- Record whether scores are rewards/penalties or costs, whether larger or smaller is better, how terminal gaps are treated, and how ties are resolved.
- Protein alignment must state the exact substitution matrix and version, such as BLOSUM62, rather than only saying “BLOSUM.”

## Correctness checks

- Removing gaps from the two rendered alignment rows must recover the relevant original sequence segments.
- Recomputing the displayed alignment score from its columns must equal the reported dynamic-programming optimum.
- Traceback indices must remain inside matrix bounds and terminate according to the selected algorithm.
- With a symmetric substitution matrix and identical gap handling, swapping the two sequences should preserve the optimal score, although an equally optimal rendered alignment may differ.
- A local-alignment score must be nonnegative. A global alignment of two empty sequences has score zero under ordinary conventions.
- Identity, similarity, and coverage are distinct. Define the denominator and the handling of gaps and ambiguous residues.

Do not freeze one textual alignment when several optimal traceback paths exist. Assert score and structural invariants unless tie-breaking is part of the API contract.

## Differential testing

Compare with a trusted implementation using identical parameters. Record every option: local/global mode, matrix, gap open/extension, end-gap policy, alphabet, and ambiguity handling. A difference without matched options is not evidence of a bug.

## Primary sources

- [Needleman and Wunsch, 1970](https://doi.org/10.1016/0022-2836(70)90057-4)
- [Smith and Waterman, 1981](https://doi.org/10.1016/0022-2836(81)90087-5)
- [Gotoh affine-gap algorithm, 1982](https://doi.org/10.1016/0022-2836(82)90398-9)
- [NCBI BLAST substitution matrices](https://www.ncbi.nlm.nih.gov/books/NBK279684/)

