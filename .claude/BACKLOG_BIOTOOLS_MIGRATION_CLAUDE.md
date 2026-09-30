# Backlog — migration de logique biotools vers BioPHP + nouvelles features

Suite du backlog BioRuby/Biogo/BioPython (`.claude/BACKLOG_ALIGNMENT_CLONING_CLAUDE.md`, terminé).
Ce lot-ci a été déclenché par un deuxième brainstorm, puis recadré après vérification : `biotools`
(projet frère, dépend de BioPHP) contenait déjà 4 des 8 idées proposées, mais dans le mauvais
répertoire architecturalement — `biotools` ne doit être que formulaires/UI, la logique algorithmique
appartient à BioPHP. Décision utilisateur : migrer cette logique vers BioPHP plutôt que la dupliquer
ou l'ignorer, puis alléger `biotools` pour qu'il délègue.

Ordre de traitement : celui de la liste d'origine.

## 1. Writers (FASTA, GenBank, GFF3, BED) — FAIT

BioPHP n'avait que des lecteurs.

`FastaWriter` (Domain/Sequence) : pur formatage, wrap à 70 colonnes (convention NCBI), aucune
validation d'alphabet (déjà faite en amont par les VO comme `DnaSequence`).

`GffFeatureWriter`/`BedFeatureWriter` (Domain/Cloning) : miroirs exacts de `GffFeatureReader`/
`BedFeatureReader`. Les deux rejettent une feature qui franchit l'origine (`start > end`) — ni GFF3
ni BED ne savent représenter ça, même logique que le rejet documenté côté lecture. `GffFeatureWriter`
réutilise `metadata["gffType"]` d'une feature lue depuis GFF3 pour un aller-retour sans perte, sinon
retombe sur `"sequence_feature"` (vrai terme générique Sequence Ontology) plutôt qu'une supposition.
Testés par aller-retour complet via les lecteurs existants, y compris l'échappement/décodage des
caractères réservés GFF3 (`;`, `=`, `,`, `%`).

`GenbankWriter` (Domain/Cloning) : couvre LOCUS/DEFINITION/ACCESSION/FEATURES/ORIGIN, pas
REFERENCE/COMMENT/VERSION/division/date (Plasmid ne les porte pas). Feature reverse-strand écrite
`complement(start..end)` (spec-correct), origin-crossing rejetée (même raison que GFF3/BED). Réutilise
`metadata["genbankKey"]` si présent, sinon retombe sur `"misc_feature"` pour les `FeatureType` sans
équivalent GenBank direct. Limitation pré-existante notée : relire un `complement()` via le parser
GenBank actuel du projet (`ParseDbAbstractManager::parseLocationBounds()`) ne restaure pas les
coordonnées d'origine — déjà documenté sur `GenbankPlasmidMapper`, non modifié ici. Testé par
assertions exactes tracées à la main (format LOCUS/FEATURES/ORIGIN), toutes passées du premier coup.

## 2. VCF reader — FAIT

Nouveau domaine `Domain/Variants/` : un variant VCF (position + REF/ALT) n'est pas une
`PlasmidFeature` (région nommée avec type/strand), donc pas forcé dans le modèle `Domain/Cloning`.
`VcfVariant` garde REF/ALT en chaînes brutes, délibérément PAS enveloppées dans `DnaSequence` : la
colonne ALT peut légitimement contenir une notation symbolique (`<DEL>`) ou de breakend
(`]13:123456]T`) pour une variante structurale, ce qui casserait la validation d'alphabet ADN sur du
VCF pourtant valide. `VcfReader` même discipline que GFF3/BED (tolérant, warning + skip, jamais de
crash) ; colonnes FORMAT/génotypes (9e colonne et après) ignorées — modéliser les génotypes est un
chantier bien plus lourd qu'un lecteur tabulaire, hors scope comme BED12.

## 3. ORF finder — FAIT côté BioPHP, biotools pas encore branché

`biotools::findORF()` n'était en réalité pas un chercheur d'ORF à coordonnées : il traduisait les
cadres en peptides puis mettait en forme l'affichage (majuscule/minuscule, "*"-split), pour un rendu
Twig direct (`Twig/BioToolsExtension.php`). Pas une migration ligne à ligne possible. Implémenté à la
place un vrai chercheur d'ORF dans `Domain/Tools/` : `OrfFinder`/`OrfFinderInterface` +
`OpenReadingFrame` (VO : frame -3..-1/1..3, start/end 1-based ascendant en coordonnées de la séquence
originale même pour un ORF brin inverse — convention `complement()` de GenBank —, peptide, hasStopCodon
pour un ORF encore "ouvert" en fin de séquence). Réutilise `translateCodon()` (même raison que CAI).
Coordonnées brin inverse dérivées à la main et vérifiées par un test dédié (séquence choisie pour que
son reverse-complement soit exactement connu). 6/6 tests passés du premier coup.

**Décision utilisateur** : ne pas toucher `biotools` maintenant (`DnaToProteinManager` +
`Twig/BioToolsExtension.php` nécessiteraient aussi un nouveau rendu Twig, pas un simple branchement
mécanique) — une passe dédiée regroupera les 4 branchements biotools (ORF, Tm/GC%, UPGMA, skews) une
fois tout le reste de cette liste terminé côté BioPHP.

## 4. Tm / GC% de primers (nearest-neighbor thermodynamics) — FAIT côté BioPHP

Migré depuis `biotools/Service/MeltingTemperatureManager.php` vers `Domain/Tools/` :
`MeltingTemperatureCalculator`/`MeltingTemperatureInterface` + `NearestNeighborTmResult` (VO).
`calculateMinimumTm()`/`calculateMaximumTm()` : la même astuce que l'original — chaque base
dégénérée est ramenée à "A" (faible) ou "G" (forte) selon qu'elle puisse être A/T ou soit forcément
C/G/S, puis règle de Wallace (2×faible+4×forte sous 14 bases, formule empirique longue au-delà).
`calculateNearestNeighborTm()` : thermodynamique SantaLucia 1998 + correction sel/Mg de von Ahsen
1999, portée telle quelle (vraie science publiée, pas de "amélioration" inventée). Seul changement
délibéré : une amorce dégénérée lève une exception plutôt que de renvoyer un tableau sentinelle de
`null` + message (l'ancien pattern biotools) — pas de calcul possible, donc pas de résultat à
retourner. `molwt()` pas migré : déjà correctement dans BioPHP (`SequenceManager::molwt()`), biotools
ne faisait qu'un wrapper fin dessus.

Valeurs de test croisées via un script indépendant (nombreux `log()` enchaînés, risque d'erreur à la
main) utilisant les vraies constantes SantaLucia 1998 publiées (mêmes valeurs que
`TmBaseStackingDTO` cite dans son propre docblock, PMC19045 table T2). 7/7 tests passés.

biotools pas touché (voir point 3 pour la décision de séquencement).

## 5. UPGMA — FAIT côté BioPHP

Migré vers `Domain/Phylogenetics/` : `UpgmaTreeBuilder`/`UpgmaTreeBuilderInterface`, réutilisant
`DistanceMatrix` et `PhylogeneticNode` déjà construits pour neighbor-joining.

**Correction scientifique délibérée** : l'implémentation legacy de `biotools`
(`DistanceAmongSequencesManager::newArray()`) fait toujours une moyenne simple 50/50 entre les deux
distances fusionnées, sans tenir compte du nombre de taxa que chaque cluster représente déjà déjà —
c'est en réalité du WPGMA (malgré le nom, une moyenne non pondérée à chaque étape), pas du vrai
UPGMA, qui pondère par la taille des clusters : `(|x|*d[k][x] + |y|*d[k][y]) / (|x|+|y|)`. Implémenté
le vrai UPGMA pondéré. Vérifié sur l'exemple classique à 5 taxa (a,b,c,d,e) des manuels, dérivé à la
main PUIS recroisé avec un script indépendant avant d'écrire le test (risque d'erreur élevé sur 4
étapes de fusion enchaînées) — l'étape où les poids 2 et 1 divergent d'une simple moyenne 50/50 est
spécifiquement couverte par le test. Même artefact d'ordre des enfants que `NeighborJoiningTreeBuilder`
(nouveau cluster ajouté en fin de liste active) — sans impact sur la topologie/les hauteurs, juste
l'ordre d'affichage, documenté dans les tests.

biotools pas touché (voir point 3 pour la décision de séquencement).

## 6. Codon usage table builder + optimisation de codons — FAIT

Complément de `CodonAdaptationIndexCalculator`, net-new (absent des deux projets).
`CodonUsageTableBuilder` : pur comptage de codons sur un set de CDS (`DnaSequence[]`), sans dépendance
au code génétique (juste la longueur/découpage) ; ne compte que les codons complets finaux, même
convention que le CAI. `CodonOptimizer` : l'inverse du CAI — pour chaque résidu d'une protéine cible,
choisit le codon synonyme avec le plus fort usage dans la table de référence, en réutilisant
`translateCodon()` (même raison que CAI et OrfFinder). Égalité (typiquement quand la table n'a aucune
observation pour un résidu) départagée par l'ordre d'énumération des 64 codons — déterministe,
documenté, testé explicitement.

## 7. Stats de composition : GC-skew/AT-skew, k-mers, îlots CpG — FAIT

GC-skew/AT-skew/KETO-skew/GC% migrés depuis `biotools/Service/SkewsManager.php::computeImage()` vers
`SkewCalculator`/`SkewCalculatorInterface` + `SkewResult` (VO) dans `Domain/Tools/` — le rendu SVG
reste dans biotools, seul le calcul migre. Amélioration délibérée : un skew avec dénominateur nul
(ex. fenêtre 100% A/T pour le GC-skew) renvoie 0.0 au lieu de planter — sous PHP 8, une division
int/int par zéro lève une `DivisionByZeroError`, ce que le code legacy aurait réellement heurté sur
une fenêtre tout-A/T. Le calcul de distance oligo-skew (formule Almeida et al. 2001, plus
spécialisée) n'a **pas** été migré dans ce lot — reste dans biotools, hors scope ici.

Comptage de k-mers déjà correctement dans BioPHP (`Domain/Tools/Service/OligosManager.php`, rien à
migrer).

Îlots CpG (`CpGIslandFinder`/`CpGIslandFinderInterface` + `CpGIsland` VO) : net-new, absent des deux
projets. Critères classiques Gardiner-Garden & Frommer (1987) : GC% et ratio observé/attendu de CpG
(`(nb CpG × longueur) / (nb C × nb G)`) tous deux au-dessus d'un seuil, sur fenêtre glissante, fenêtres
qualifiantes fusionnées en îlots. Réutilise `SkewCalculator` pour le GC%. Fixture de test (80 bases :
20×A + 20×"CG" + 20×A) vérifiée deux fois indépendamment — à la main fenêtre par fenêtre, puis recroisée
avec un script autonome implémentant le même algorithme — avant d'écrire l'assertion (risque d'erreur
élevé sur une fenêtre glissante avec fusion).

biotools pas touché (voir point 3 pour la décision de séquencement).

## 8. Tables de code génétique alternatives (mitochondrial, etc.) — FAIT

Net-new. `SequenceManager::translateCodon()` confirmé n'avoir que le code standard, aucun paramètre
de table. Plutôt que de modifier cette méthode partagée (signature publique déjà réutilisée par CAI,
OrfFinder, CodonOptimizer — risque de régression), nouveau service autonome
`AlternateGeneticCodeTranslator`/`AlternateGeneticCodeTranslatorInterface` + `GeneticCodeTable`
(constantes, numérotation NCBI). Chaque table alternative stockée comme un **overlay** des
différences par rapport au code standard (réutilisé via `translateCodon()`, jamais dupliqué) — la
même façon dont NCBI documente lui-même chaque table alternative.

**Scope délibérément restreint** : seule la table mitochondriale des vertébrés (table NCBI 2) est
incluse — ses 4 différences (AGA/AGG deviennent stop au lieu d'Arg, ATA/TGA deviennent Met/Trp au
lieu d'Ile/Stop) sont un fait de biologie moléculaire extrêmement établi, pas une science incertaine.
Les autres tables NCBI ont été volontairement laissées de côté plutôt que codées depuis un souvenir
moins sûr — ajouter une table plus tard ne demande qu'une entrée d'overlay + une constante.

biotools pas concerné par ce point (pas de logique équivalente à migrer).

---

Les 8 points de cette liste sont maintenant tous faits côté BioPHP (896 tests, 0 échec). Reste la
passe dédiée de branchement biotools (ORF, Tm/GC%, UPGMA, skews) décidée au point 3.
