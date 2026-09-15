# Molecular properties

## Molecular mass

Mass depends on what chemical object the API claims to represent. Before editing, identify:

- average or monoisotopic mass;
- DNA, RNA, peptide, or protein;
- single- or double-stranded nucleic acid;
- neutral molecule, ion, or salt form;
- terminal hydroxyl/phosphate assumptions;
- linear or cyclic polymer;
- modifications, disulfides, selenocysteine, ambiguous residues, and unknown symbols.

For an ordinary linear unmodified polypeptide, summing free amino-acid masses requires subtracting water for each peptide bond; equivalently, sum residue masses and add one water molecule. Do not mix those two tables or apply the water correction twice.

For nucleic acids, use a coherent nucleotide/residue table and terminal convention. A length-zero or length-one input is a strong test for incorrect polymerization corrections.

## Isoelectric point and charge

- Name the pKa parameter set and the ionizable groups included.
- Make terminal-group assumptions explicit.
- Compute pI as a numerical root of net charge only when a bracket and convergence rule are defined.
- Test the returned pI by evaluating net charge near that pH, not by duplicating the solver.
- Ambiguous residues and post-translational modifications require an explicit policy.

## Other protein properties

- Extinction coefficients depend on wavelength, residue composition, disulfide state, and the selected published model.
- Hydropathy requires a named scale and window policy. Define edge handling and whether a window is centered or left-aligned.
- “Molecular weight,” “mass,” and “m/z” are not interchangeable.
- Rounding belongs at the display/API boundary; retain adequate precision internally.

## Testing guidance

Use one trusted calculator or library as an external oracle with its version and options recorded. Add decomposition properties where valid: changing one unmodified residue should alter mass by the difference between the two residue masses under the same table.

## Authoritative sources

- [UniProt sequence annotation and molecular properties](https://www.uniprot.org/help/sequence_annotation)
- [ExPASy ProtParam documentation](https://web.expasy.org/protparam/protparam-doc.html)
- [Kyte and Doolittle hydropathy scale](https://doi.org/10.1016/0022-2836(82)90515-0)

