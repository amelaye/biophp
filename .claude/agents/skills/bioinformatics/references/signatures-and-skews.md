# Genomic signatures, CGR/FCGR, and skews

## K-mers

- Define overlapping versus non-overlapping windows.
- For a linear sequence of length `n`, there are `max(0, n-k+1)` overlapping k-mer windows before filtering invalid or ambiguous windows.
- State whether reverse complements are distinct, merged into canonical k-mers, or counted separately.
- State how ambiguous symbols affect a window: reject, skip, reset, distribute fractional counts, or expand. Do not let a regex silently bridge an invalid symbol.
- State whether outputs are counts, frequencies, percentages, or transformed values and define the denominator.

## CGR and FCGR

For standard midpoint chaos-game representation, with current point `p` and the vertex `v(b)` assigned to base `b`:

```text
p_next = (p + v(b)) / 2
```

The vertex assignment, image orientation, initial point, pixel indexing, and boundary rounding are part of the result and must remain documented and tested.

An FCGR for k-mers is normally a `2^k × 2^k` matrix under a stated nucleotide-to-quadrant mapping. For unambiguous linear DNA with all overlapping windows counted, the sum of matrix cells must equal `max(0, n-k+1)`. If invalid or ambiguous windows are skipped, the sum must equal the number of accepted windows instead.

Do not compare two FCGR images until their quadrant mapping and row-axis direction match; rotations and reflections can encode the same counts under different conventions.

## GC and AT skew

Common definitions are:

```text
GC skew = (G - C) / (G + C)
AT skew = (A - T) / (A + T)
```

Define behavior when the denominator is zero. Also distinguish per-window skew from cumulative skew, and specify window length, step, circular wrapping, partial final windows, and coordinate attached to each result.

Useful invariants:

- each defined skew lies in `[-1, 1]`;
- swapping G and C negates GC skew;
- complementing unambiguous DNA negates both GC and AT skew under the same window coordinates;
- count conservation holds across accepted k-mer windows.

## Primary sources

- [Jeffrey, DNA sequence representation by chaos game, 1990](https://doi.org/10.1093/nar/18.8.2163)
- [Deschavanne et al., genomic signature, 1999](https://doi.org/10.1093/oxfordjournals.molbev.a026048)

