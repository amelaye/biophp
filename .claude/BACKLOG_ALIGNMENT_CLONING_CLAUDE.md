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

## 3. Parser GFF3/BED — GFF3 FAIT, BED pas fait

Pont d'import pour les `PlasmidFeature`. Finalement PAS via `Domain/Parser/`/
`DatabaseParserFactory` : cette machinerie est construite pour des bases GenBank-like (une entrée =
un LOCUS...//, indexée via Doctrine dans `DatabaseManager::recording()`), incompatible avec l'esprit
persistence-independent de `Domain/Cloning` et avec la structure de GFF3 (une feature par ligne, pas
de délimiteurs d'entrée). Implémenté à la place comme lecteur autonome `GffFeatureReader` /
`GffFeatureReaderInterface` dans `Domain/Cloning/Service/`, qui rend directement des `PlasmidFeature`
(pas de `Feature` entity GenBank), tolérant aux lignes invalides (warning + skip, jamais de crash).
BED n'a pas été fait dans ce lot (format plus simple, à la demande si besoin).

## 4. Assemblage Gibson / primers de jonction

Suite logique de la digestion + compatibilité de ligature déjà construites dans
`Domain/Cloning/Service/`. Pourrait réutiliser l'aligneur (point 1/semi-global) pour vérifier
l'homologie des zones de chevauchement.

## 5. Codon Adaptation Index (CAI)

Utile pour l'optimisation d'expression d'un insert cloné. Petit chantier dans `Domain/Tools`.

## 6. FASTQ + décodage Phred

Plus NGS-orienté (spécialité de Biogo), moins connecté au reste du projet actuel. Ouvrirait la
porte au contrôle qualité de séquençage.

## 7. Newick + phylogénétique basique (neighbor-joining)

Présent partout (BioRuby `Bio::Tree`), mais plus lourd et le moins connecté au reste du projet —
en dernier.
