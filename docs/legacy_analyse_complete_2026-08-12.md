# BioPHP — Analyse complète du dossier Legacy et état de la refonte

Date de synthèse : 2026-08-12
Fait suite à : [legacy_refacto_memo.md](legacy_refacto_memo.md) (2026-08-11), dont ce document corrige et approfondit plusieurs affirmations trop optimistes.

## Contexte

BioPHP est une librairie PHP de bioinformatique historiquement écrite en style procédural
mono-fichier (dossier `Legacy/`), en cours de refonte vers une architecture orientée services
Symfony (`Domain/`, `Api/`). La veille de cette analyse (2026-08-11), une session de maintenance
a modernisé les dépendances du projet (PHP 7.2 → 8.1, Symfony 4 → 6.4, PHPUnit 7.5 → 8.5,
retrait des paquets abandonnés, migration des entités Doctrine vers les attributs PHP natifs).
Cette modernisation a porté uniquement sur l'infrastructure (composer.json, tests API, mapping
Doctrine) et n'a touché ni le contenu du dossier `Legacy/`, ni l'avancement du portage fonctionnel.
Voir `docs/compte_rendu_maj_2026-08-11.md` et sa partie 2 pour le détail de cette session.

Le memo existant (`legacy_refacto_memo.md`) donne une table de correspondance Legacy → refonte,
mais s'avère **globalement optimiste** : il affirme par exemple que `genome.inc.php`,
`kegg.inc.php` (1672 lignes), `unigene.inc.php` et `transfac.inc.php` (732 lignes) sont couverts
par des `Parse*Manager`, alors qu'en réalité **aucun de ces quatre fichiers n'a d'équivalent**
dans la refonte : `Domain/Database/Factory/DatabaseReaderFactory.php` ne reconnaît que les formats
`"GENBANK"` et `"SWISSPROT"`, tout le reste levant une exception `Unknown database format !`.

Ce document remplace le memo précédent comme référence : chacun des 26 fichiers `Legacy/*.php`
a été lu intégralement et chaque correspondance annoncée vérifiée en lisant le fichier de la
refonte concerné, pas seulement son nom.

## Bilan chiffré

Sur 11 223 lignes de code Legacy, environ **4 386 lignes** ont un équivalent réel et vérifié dans
la refonte (`seq.php`, `seqalign.php`, `seqdb.php`, `resten.php`, une partie d'`etc.php`), soit
**≈ 39 % du volume** — concentré exclusivement sur le noyau séquence / alignement /
GenBank-Swissprot / restriction enzymes.

Les **≈ 61 % restants** — la totalité des 19 parseurs de bases de données externes tierces
(aaindex, blocks, embl, entrez, epd, expasy, genome, hgbase, kegg, lit, motif, pdb, pdbstr, pir,
pmd, prf, prints, prodom, transfac, unigene) — sont **purement et simplement absents** de la
refonte à ce jour.

| Statut | Fichiers | Lignes |
|---|---|---|
| COMPLET | seq.php, seqalign.php, seqdb.php, resten.php | 4 179 |
| PARTIEL | etc.php, refseq.inc.php (doublon de seqalign.php) | 479 |
| ABSENT | 19 parseurs de bases externes + entrez.inc.php (doublon interne) | ≈ 6 554 |
| OBSOLÈTE (superséde par `Tests/`) | test.php | 11 |

## Forces générales du Legacy

- **Couverture fonctionnelle très large à l'origine** : 20 formats de bases biologiques
  différents étaient parsés (GenBank, Swissprot, EMBL, PDB, KEGG, TRANSFAC, PIR, PRF, PMD,
  PROSITE, PRINTS, ProDom, BLOCKS, AAINDEX, EPD, ExPASy ENZYME, HGBase, UniGene, "DOGS" genome,
  littérature NCBI). La refonte n'en couvre aujourd'hui que 2 (GenBank, Swissprot).
- **Logique métier de séquence robuste et éprouvée** (`seq.php`, `seqalign.php`, `etc.php`,
  `resten.php`) : ce noyau, utilisé pendant des années sur des données réelles, est justement
  celui qui a été le mieux et le plus fidèlement porté.
- **Documentation inline dense** : presque chaque fonction Legacy est accompagnée d'un exemple
  concret de ligne de données réelle — ce style a globalement été conservé dans la refonte.

## Faiblesses générales du Legacy

- Style procédural avec classes « à l'ancienne » (constructeur `function NomDeLaClasse()`),
  aucun typage, propriétés publiques par défaut (`var $x`).
- Usage systématique d'`each()` pour itérer les tableaux de lignes — **fonction supprimée depuis
  PHP 8.0** : aucun des 26 fichiers Legacy ne s'exécuterait tel quel sous PHP 8.1, la cible
  actuelle du projet.
- Usage de fonctions POSIX regex obsolètes (`eregi_replace`, `ereg_replace`, supprimées depuis
  PHP 7.0) dans `etc.php`, `hgbase.inc.php`, `seq.php`.
- Aucun test automatisé (`test.php` n'en est pas un — script exploratoire sans assertion).
- Couplage fort au système de fichiers (`fopen`/`fseek`/positions d'octets fixes) et aux
  variables globales (`$GLOBALS`, `global $RestEn_DB`, `global $chemgrp_matrix`).
- Fonctionnalités **annoncées mais jamais finies dans le Legacy lui-même** : blocs `if` vides
  dans `hgbase.inc.php` et `prf.inc.php`, propriétés jamais renseignées dans `pmd.inc.php`,
  fonctions vides dans `kegg.inc.php` (`parse_mol_kegg`, `parse_ligand_kegg`).
- Usage risqué d'`eval()` dans `entrez.inc.php` pour dispatcher dynamiquement des sous-parseurs.

## Table de correspondance complète

| Fichier Legacy | Lignes | Statut | Fichier(s) refonte concerné(s) |
|---|---|---|---|
| aaindex.inc.php | 83 | ABSENT | — |
| blocks.inc.php | 84 | ABSENT | — |
| embl.inc.php | 211 | ABSENT | — |
| entrez.inc.php | 373 | ABSENT *(doublon interne de seqdb.php)* | couvert par ricochet via ParseGenbankManager.php |
| epd.inc.php | 232 | ABSENT | — |
| etc.php | 335 | **PARTIEL** | SequenceMatchManager.php, FormatsTrait.php |
| expasy.inc.php | 215 | ABSENT | — |
| genome.inc.php | 294 | ABSENT | — |
| hgbase.inc.php | 159 | ABSENT | — |
| kegg.inc.php | 1672 | ABSENT | — |
| lit.inc.php | 138 | ABSENT | — |
| motif.inc.php | 288 | ABSENT | — |
| pdb.inc.php | 1845 | ABSENT | — |
| pdbstr.inc.php | 56 | ABSENT | — |
| pir.inc.php | 320 | ABSENT | — |
| pmd.inc.php | 150 | ABSENT | — |
| prf.inc.php | 229 | ABSENT | — |
| prints.inc.php | 77 | ABSENT | — |
| prodom.inc.php | 118 | ABSENT | — |
| refseq.inc.php | 144 | **PARTIEL** *(nom trompeur, contenu réel = doublon de seqalign.php)* | SequenceAlignmentManager.php |
| resten.php | 795 | **COMPLET** (1 bug trouvé) | RestrictionEnzymeManager.php, TypeIIEndonucleaseApi.php, Entity/Enzyme.php |
| seq.php | 937 | **COMPLET** (1 bug auto-documenté) | SequenceManager.php, ProteinManager.php |
| seqalign.php | 548 | **COMPLET** | SequenceAlignmentManager.php |
| seqdb.php | 1099 | **COMPLET** *(GenBank + Swissprot uniquement)* | DatabaseManager.php, ParseGenbankManager.php, ParseSwissprotManager.php |
| test.php | 11 | OBSOLÈTE | Tests/*.php (61 fichiers) |
| transfac.inc.php | 732 | ABSENT | — |
| unigene.inc.php | 78 | ABSENT | — |

## Fiches détaillées par fichier

### Noyau métier — portage COMPLET

**seq.php (937 l.)** — Cœur historique : `complement()`, `revcomp()`, `halfstr()`, `get_bridge()`,
`expand_na()` (codes IUPAC → regex), classe `protein::molwt()`, classe `seq` géante
(`molwt`, `count_codons`, `subseq`, `patpos`/`patfreq`/`findpattern`, `symfreq`, `translate`,
`charge`, `chemgrp`, `is_mirror`/`is_palindrome`). Porté vers `SequenceManager.php` de façon
quasi 1:1, avec externalisation pertinente des tables codées en dur (poids atomiques, table des
codons) via des adapters API. **Point faible refonte** : `SequenceManager::getBridge()` porte
encore le commentaire `@todo : Correct it - does not seems to work :/` de l'autrice — bug connu
non corrigé. **Point faible Legacy** : la classe `seq` cumulait déjà plus de 20 responsabilités
(God Object), défaut hérité tel quel par `SequenceManager`.

**seqalign.php (548 l.)** — Classe `SeqAlign` : alignements multi-séquences FASTA/CLUSTAL,
navigation, statistiques, conversion colonne↔résidu, consensus. Porté fidèlement vers
`SequenceAlignmentManager.php`, avec une vraie amélioration : les pointeurs manuels
`seqptr`/`first()`/`next()` sont remplacés par un `\ArrayIterator` natif PHP. **Point faible
commun** : `resVar()`/`consensus()` recalculent en O(n×m) à chaque appel, sans mémoïsation, dans
le Legacy comme dans la refonte.

**seqdb.php (1099 l.)** — Moteur de base Legacy : `process_ft`, `get_entryid`, `line2r`,
`bsrch_tabfile` (recherche dichotomique sur fichier `.IDX`), classe `SeqDB::fetch()`, et les deux
parseurs `parse_swissprot()` et `parse_id()` (GenBank). Porté vers `DatabaseManager.php` +
`DatabaseReaderFactory`/`DatabaseRecorderFactory` + `ParseGenbankManager.php` +
`ParseSwissprotManager.php`, avec une amélioration architecturale nette : le mécanisme de
fichiers `.IDX`/`.DIR` bas niveau est remplacé par une indexation Doctrine (`Collection`,
`CollectionElement`). **Limite** : ne couvre que GenBank et Swissprot, pas les 19 autres formats
externes que gérait le Legacy dans son ensemble.

**resten.php (795 l.)** — Base de ~620 enzymes de restriction codées en dur, classe `RestEn`
(`CutSeq`, `FindRestEn`, `GetPattern`, `GetCutPos`). Meilleur portage du dépôt :
`RestrictionEnzymeManager.php` reproduit chaque méthode fidèlement, et remplace la base codée en
dur par un appel HTTP via `TypeIIEndonucleaseApi` (bonne modernisation). **Bug trouvé dans la
refonte** : `RestrictionEnzymeManager::fetchCutposAndPlen()` référence une variable
`$RestEn_List[]` qui n'existe pas dans la méthode (c'est `$aEnzymes` qui est utilisé et retourné
partout ailleurs) — la méthode renvoie donc **toujours un tableau vide**, régression silencieuse
par rapport au Legacy.

### Cas particuliers — noms trompeurs

**entrez.inc.php (373 l.)** ne contacte pas Entrez : c'est un parseur GenBank-like quasi
identique à `parse_id()` de `seqdb.php`, avec en plus un `eval()` dangereux pour dispatcher les
sous-parseurs. Fonctionnellement redondant avec `seqdb.php`, donc son absence de portage dédié
n'est pas une perte réelle.

**refseq.inc.php (144 l.)** ne parse pas RefSeq : il contient uniquement le constructeur de
`SeqAlign()` (lecture FASTA/CLUSTAL), copié-collé exact des lignes 407-548 de `seqalign.php`. Son
contenu réel est donc déjà couvert par `SequenceAlignmentManager`, mais aucun vrai parseur RefSeq
(dbVar, accessions NM_/NP_/NR_) n'a jamais existé, ni dans le Legacy ni dans la refonte.

**genome.inc.php (294 l.)**, malgré son nom proche d'`entrez.inc.php`, est un format
entièrement différent (« DOGS » — statistiques de séquençage par organisme), sans lien avec
`kegg.inc.php` qui contient lui aussi une classe `Genome` (KEGG). Trois notions distinctes
partagent un vocabulaire proche, source de confusion à lever dans toute future refonte.

### Parseurs de bases externes — ABSENTS de la refonte

Dix-neuf fichiers, tous des parseurs de formats plats propres à une base de données biologique
externe, n'ont **aucun équivalent** dans `Domain/` ni `Api/` :

| Fichier | Lignes | Base ciblée | Point notable |
|---|---|---|---|
| pdb.inc.php | 1845 | Protein Data Bank (structures 3D) | Plus gros fichier du dépôt ; classe monolithique `Protein_PDB`, une seule fonction géante, aucune décomposition |
| kegg.inc.php | 1672 | KEGG (voies métaboliques) | Fichier le plus riche fonctionnellement (6 classes) ; `parse_mol_kegg`/`parse_ligand_kegg` restées vides même dans le Legacy |
| transfac.inc.php | 732 | TRANSFAC (facteurs de transcription) | 5 sous-parseurs homogènes, dupliquant le même bloc de gestion des dates |
| pir.inc.php | 320 | PIR (Protein Information Resource) | Pattern `#clé valeur` bien factorisé sur 4 champs |
| motif.inc.php | 288 | PROSITE (motifs protéiques) | Parsing à 3 niveaux d'imbrication du champ `MA` ; variable `$linedata` utilisée avant définition |
| genome.inc.php | 294 | « DOGS » (statistiques de séquençage) | Bug ligne 161 : `$in_ref_flag == TRUE;` (comparaison au lieu d'affectation) |
| prf.inc.php | 229 | PRF/NBRF | Champs SOURCE/KEYWORD/CROSSREF/SEQUENCE jamais implémentés |
| epd.inc.php | 232 | EPD (promoteurs eucaryotes) | Copié-collé de la structure d'embl.inc.php, variables mortes |
| expasy.inc.php | 215 | ExPASy ENZYME (nomenclature EC) | À ne pas confondre avec les enzymes de restriction déjà couvertes |
| embl.inc.php | 211 | EMBL (nucléique) | Format proche GenBank — portage le plus facile à mécaniser en priorité |
| pmd.inc.php | 150 | PMD (Protein Mutant Database) | Moitié de la classe jamais renseignée, même dans le Legacy d'origine |
| hgbase.inc.php | 159 | HGBase (mutations humaines) | Champs ALLELE/ISINBLOCK avec blocs `if` vides |
| lit.inc.php | 138 | Revues biomédicales NCBI | Format le plus simple ; seconde moitié du fichier commentée (code mort) |
| prodom.inc.php | 118 | ProDom (domaines protéiques) | Exemple de données réel inclus en commentaire |
| aaindex.inc.php | 83 | AAINDEX1 (indices physico-chimiques) | Utilise `each()`, obsolète depuis PHP 8.0 |
| blocks.inc.php | 84 | BLOCKS (motifs conservés) | Aucune garde sur les tokens absents |
| prints.inc.php | 77 | PRINTS (motifs protéiques) | Seul parseur incapable de traiter plusieurs entrées dans un même flux |
| unigene.inc.php | 78 | UniGene (clusters d'ESTs) | En-tête de commentaire encore `//pdbstr.inc.php` — jamais relu depuis sa création |
| pdbstr.inc.php | 56 | Familles structurales PDB dérivées | Plus court et plus simple de tous |

## Bugs à corriger en priorité dans la refonte existante

Ces trois points ne relèvent pas d'un nouveau portage mais de la correction de régressions déjà
introduites lors de la refonte du noyau pourtant marqué COMPLET :

1. **`RestrictionEnzymeManager::fetchCutposAndPlen()`** — variable `$RestEn_List[]` inexistante à
   la place de `$aEnzymes` : la méthode renvoie toujours un tableau vide.
2. **`SequenceManager::getBridge()`** — bug auto-documenté par un `@todo` de l'autrice
   (« does not seems to work »), sans test dédié dans `SequenceManagerTest.php`.
3. **Duplication `complement()`/`revcomp()`** — deux implémentations différentes coexistent
   (`SequenceManager` via `NucleotidApiAdapter`, et `compDNA()`/`revCompDNA()` dans
   `SequenceTrait`), risque de divergence de résultats selon le point d'entrée utilisé.

## Feuille de route priorisée

**Priorité 1 — corriger les régressions (rapide, fort impact)**
1. Corriger `RestrictionEnzymeManager::fetchCutposAndPlen()`.
2. Corriger `SequenceManager::getBridge()` et ajouter un test dédié.
3. Clarifier/fusionner les deux implémentations de complément/reverse-complement.

**Priorité 2 — combler les parseurs externes les plus utiles (effort modéré, indépendants)**
4. `ParseEmblManager.php` + ajout de `"EMBL"` dans `DatabaseReaderFactory`/`DatabaseRecorderFactory`
   — format proche GenBank, portage facilité par réutilisation de `ParseGenbankManager`.
5. Parseur PDB (`Api/PdbApi.php` ou `Domain/Database/Service/ParsePdbManager.php` + DTO) — format
   externe le plus demandé en bioinformatique structurale, et plus gros trou de couverture.
6. Parseur PROSITE (`motif.inc.php`) — complément naturel des outils de recherche de motifs.
7. Parseur ExPASy ENZYME (`expasy.inc.php`) — nomenclature EC, complémentaire des enzymes de
   restriction déjà couvertes.

**Priorité 3 — bases plus spécialisées, à traiter à la demande**
8. KEGG (1672 l.) — à décomposer en services distincts (Compound, Enzyme KEGG, Reaction,
   Ortholog, Genome KEGG) plutôt qu'un portage monolithique.
9. TRANSFAC (732 l.) — 5 sous-parseurs à factoriser, contrairement au Legacy qui duplique le code
   des dates 5 fois.
10. PIR, PRF, PMD, PRINTS, ProDom, BLOCKS, AAINDEX, EPD, HGBase, UniGene, littérature NCBI, "DOGS"
    genome — formats plus petits ou moins utilisés, à traiter au cas par cas.

**Priorité 4 — dette de tests indépendante du périmètre**
11. Tests unitaires pour `DatabaseManager.php` (aucun test direct trouvé, seuls
    `ParseGenbankManagerTest`/`ParseSwissprotManagerTest` existent).
12. Tests pour `GeneticsFunctions.php` et `MathematicsFunctions.php` (aucun trouvé, contrairement
    à leur voisin `OligosManager` qui a `OligosManagerTest.php`).
13. `GeneticsFunctions`, `OligosManager`, `MathematicsFunctions` sont des **ajouts neufs** de la
    refonte, sans équivalent Legacy — à documenter comme gain net, pas comme dette de portage.

## Points de vigilance hérités de la session du 2026-08-11

Rappel des points laissés ouverts par la modernisation des dépendances (non traités dans cette
analyse fonctionnelle, mais toujours d'actualité) :
- `doctrine/cache` reste un paquet abandonné, non contournable sans passer à `doctrine/orm` 3.x.
- `OligosManagerTest::testFindZScore` et `PKApiTest` : deux problèmes préexistants non corrigés.
- `CLAUDE.md` décrit encore le projet comme PHP 7.2 / Symfony 4 alors que `composer.json` cible
  désormais PHP 8.1 / Symfony 6.4 — à mettre à jour pour éviter toute confusion future.
