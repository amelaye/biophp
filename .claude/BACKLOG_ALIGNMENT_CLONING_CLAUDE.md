# Backlog — features inspirées de BioRuby / Biogo / BioPython

Idées de features à ajouter au projet, classées par proximité avec ce qui existe déjà
(`Domain/Alignment/`, `Domain/Cloning/`). Chaque point garde le contexte qui a motivé sa position
dans la liste, pour que la priorité reste compréhensible même après coup.

## 1. Alignement semi-global (overlap) — FAIT

Troisième variante classique à côté de Needleman-Wunsch (global) et Smith-Waterman (local) déjà
implémentés : pas de pénalité sur les gaps terminaux, sur aucune des deux séquences. Utile pour
vérifier qu'un primer s'aligne entièrement dans un vecteur, ou trouver un chevauchement
suffixe/préfixe entre deux fragments. Réutilise `SubstitutionScoringInterface` et
`PairwiseAlignmentResult` tels quels.

## 2. Interval tree / chevauchement de features — FAIT (en O(n²), pas un vrai arbre — voir note)

S'appuie directement sur `PlasmidFeature` (Domain/Cloning) : détecter si un site de restriction
tombe dans une CDS, si deux annotations se chevauchent sur un plasmide. Biogo est très fort
là-dessus.

Implémenté en `FeatureOverlapManager`/`FeatureOverlapInterface`, gère le franchissement de l'origine
en découpant chaque feature en 1 ou 2 intervalles linéaires. Pas un vrai arbre d'intervalles équilibré
(O(n²) pour toutes les paires) : un plasmide compte rarement plus de quelques dizaines de features,
donc la structure plus simple est aussi claire et au moins aussi rapide en pratique — décision
documentée dans le docblock de l'interface plutôt qu'ajoutée en cachette.

## 3. Parser GFF3/BED — FAIT

Pont d'import pour les `PlasmidFeature`. Finalement PAS via `Domain/Parser/`/
`DatabaseParserFactory` : cette machinerie est construite pour des bases GenBank-like (une entrée =
un LOCUS...//, indexée via Doctrine dans `DatabaseManager::recording()`), incompatible avec l'esprit
persistence-independent de `Domain/Cloning` et avec la structure de GFF3 (une feature par ligne, pas
de délimiteurs d'entrée). Implémenté à la place comme lecteur autonome `GffFeatureReader` /
`GffFeatureReaderInterface` dans `Domain/Cloning/Service/`, qui rend directement des `PlasmidFeature`
(pas de `Feature` entity GenBank), tolérant aux lignes invalides (warning + skip, jamais de crash).

BED implémenté ensuite (`BedFeatureReader`/`BedFeatureReaderInterface`), même esprit autonome.
Particularité : BED est 0-based demi-ouvert (`[chromStart, chromEnd)`), converti une seule fois vers
la convention 1-based inclusive du reste du projet (`start = chromStart + 1`, `end = chromEnd`).
Un intervalle de longueur nulle (`chromStart == chromEnd`) est rejeté plutôt que naïvement converti :
ça produirait `start > end`, la convention déjà réservée aux features franchissant l'origine — une
mauvaise réinterprétation silencieuse évitée. BED n'a pas de colonne "type" comme GFF3 ; toute
feature est `MISC_FEATURE`, chromosome et score étant conservés en métadonnées. Seules les 6
premières colonnes (BED6 : chrom, chromStart, chromEnd, name, score, strand) sont lues ; les colonnes
`thickStart`/`thickEnd`/`itemRgb`/blocs (BED12, structure exons) ne sont pas reconstruites — même
simplification déjà documentée pour un `join()` GenBank ou une relation Parent/ID GFF3.

## 4. Assemblage Gibson / primers de jonction — FAIT (sans réutiliser l'aligneur, voir note)

Suite logique de la digestion + compatibilité de ligature déjà construites dans
`Domain/Cloning/Service/`.

Implémenté en `GibsonAssemblyManager`/`GibsonAssemblyInterface` : `checkJunction()` détecte le plus
long chevauchement EXACT (ancré, borné par min/max) entre la fin d'un fragment amont et le début d'un
fragment aval ; `designHomologyArms()` calcule les tails d'amorces à ajouter quand aucun chevauchement
n'existe déjà (convention standard NEBuilder). Décision assumée : je n'ai **pas** réutilisé
`SemiGlobalAligner` (point 1) comme prévu initialement dans ce backlog — Gibson a besoin d'une
identité quasi parfaite dans la zone d'homologie (chew-back exonucléase + appariement), pas d'un
alignement tolérant les substitutions/gaps ; une recherche de correspondance exacte est à la fois plus
juste scientifiquement et plus simple qu'un aligneur configuré pour approximer ça. Documenté dans le
docblock de la classe.

## 5. Codon Adaptation Index (CAI) — FAIT

Utile pour l'optimisation d'expression d'un insert cloné. Implémenté dans `Domain/Tools` :
`CodonAdaptationIndexCalculator`/`CodonAdaptationIndexInterface` + `CodonUsageTable` (table de
référence validée) + `CaiResult`. Réutilise `SequenceInterface::translateCodon()` (le code génétique
déjà câblé du projet) plutôt que de dupliquer une deuxième table de code génétique. Exclut les codons
stop et les acides aminés sans synonyme (Met, Trp) du calcul, en suivant la définition originale de
Sharp & Li (1987) — les inclure gonflerait artificiellement le score. `Domain/Tools` n'avait jamais eu
son propre `Resources/config/services.xml` (les classes existantes comme `GeneticsFunctions` ne sont
pas câblées en DI) ; j'en ai créé un, sans toucher aux classes legacy existantes.

## 6. FASTQ + décodage Phred — FAIT

Plus NGS-orienté (spécialité de Biogo), moins connecté au reste du projet actuel. Ouvrirait la
porte au contrôle qualité de séquençage.

Implémenté dans un nouveau domaine `Domain/Sequencing/` (le concept "read de séquençage +
qualité" ne rentrait dans aucun domaine existant) : `FastqReader`/`FastqReaderInterface` lit un
fichier FASTQ classique (groupes de 4 lignes, pas de wrapping multi-ligne comme FASTA - ce n'est
pas un format FASTQ valide), tolérant aux enregistrements malformés (warning + skip, jamais de
crash, même logique que `GffFeatureReader`). `FastqRecord` réutilise `DnaSequence` pour la partie
séquence plutôt que de dupliquer une validation d'alphabet, et décode la qualité en Phred+33
(Sanger / Illumina 1.8+ uniquement - l'ancien encodage Phred+64 n'a plus été produit par un
séquenceur depuis 2011, hors scope). `getMeanPhredScore()` donne une métrique QC simple par read.

## 7. Newick + phylogénétique basique (neighbor-joining) — FAIT

Présent partout (BioRuby `Bio::Tree`), mais plus lourd et le moins connecté au reste du projet —
en dernier.

Implémenté dans un nouveau domaine `Domain/Phylogenetics/` : `PhylogeneticNode` (VO récursif
immuable, feuille = pas d'enfants + nom obligatoire), `NewickReader`/`NewickReaderInterface`
(parseur récursif-descendant, une arborescence par appel, pas de labels quotés ni de commentaires
NHX — hors scope, non utilisés ailleurs dans le projet), `DistanceMatrix` (VO validé : carrée,
diagonale nulle, symétrique, distances non négatives) et `NeighborJoiningTreeBuilder`/
`NeighborJoiningInterface` (Saitou & Nei 1987). La réduction s'arrête à 3 clusters actifs (pas 2 —
la formule classique a besoin d'un r_i calculé sur au moins un autre cluster actif) et les 3
derniers sont résolus directement en une racine non enracinée à 3 enfants (trifurcation), la
représentation standard d'un arbre NJ. Fixture de test hand-vérifiée : matrice de distances
parfaitement additive dérivée d'un arbre connu — NJ est garanti de retrouver exactement la même
topologie et les mêmes longueurs de branches sur une matrice additive, propriété reproduite pas à
pas dans le docblock de la classe et dans le test. Les longueurs de branches négatives (un artefact
documenté de NJ sur des distances légèrement non additives) sont acceptées sans validation par
`PhylogeneticNode`, volontairement — ce n'est pas une erreur à rejeter.
