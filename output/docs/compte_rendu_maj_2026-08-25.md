# Compte rendu — Des chaînes de caractères aux Value Objects (25 août 2026)

**Date** : 25 août 2026
**Branche** : `master`
**Fait suite à** : [compte_rendu_maj_2026-08-13.md](compte_rendu_maj_2026-08-13.md)
**Périmètre** : trois chantiers enchaînés — introduction de la notion de *value object* pour les éléments les plus caractéristiques du domaine (les séquences ADN, ARN et protéiques), correction des bugs que ces value objects ont mis en évidence, puis extraction des parseurs dans `Domain/Parser/` et remise à plat de la factory qui les résout.

> Ce compte rendu est volontairement pédagogique : il explique **pourquoi** ces objets ont été introduits, pas seulement ce qui a été écrit. Si vous débutez en conception orientée objet, la section « C'est quoi un value object ? » se lit indépendamment du reste.

---

## 1. Le problème : la « chaîne à tout faire »

Depuis le BioPHP d'origine, une séquence biologique est **une simple `string`**. `"ATGCGT"`, c'est de l'ADN. `"AUGCGU"`, c'est de l'ARN. `"GAVLI"`, c'est une protéine. Pour PHP, ce sont trois chaînes de caractères, strictement interchangeables. On appelle ça du *stringly-typed* : on utilise le type `string` là où le domaine a en réalité trois concepts distincts.

Ça marche… jusqu'au moment où ça ne marche plus. Voici trois problèmes réels du code, **vérifiés en exécutant le code**, pas seulement en le lisant.

> **Note** : cette section décrit le code **tel qu'il était au début de la session**. Ces trois bugs ont été corrigés dans la foulée — voir la [section 6](#6-les-bugs-corrigés-dans-la-foulée). Ils sont conservés ici parce qu'ils sont la motivation même des value objects, et qu'ils illustrent bien ce que le typage fort évite.

### Problème 1 — Une erreur de saisie donne un résultat faux, en silence

[`SequenceManager::molwt()`](../../Domain/Sequence/Service/SequenceManager.php) calcule le poids moléculaire d'une séquence. Sa première ligne appelle `cleanSequence()`, dont le rôle est justement de détecter les symboles invalides :

```php
public function molwt(string $sLimit, string $sSequence, string $sMolType, int $iNALen) : float
{
    $this->cleanSequence($sSequence, $sMolType);   // <- la valeur de retour est jetée
    ...
    $aMwt[$iLowLimit] += $na_wts[$sNABase];       // <- symbole inconnu = clé absente = 0
```

`cleanSequence()` **retourne bien `false`** quand elle trouve un symbole invalide. Mais personne ne lit ce retour. Résultat mesuré :

| Appel | Résultat |
|---|---|
| `molwt("upperlimit", "ATGC", "DNA", 4)` | `1253.945` |
| `molwt("upperlimit", "ATGZ", "DNA", 4)` | `964.73` |

Le `Z` — qui n'est pas une base azotée — est compté comme pesant **zéro**. Le poids est faux de 289 daltons, et **rien** ne le signale : pas d'exception, pas de `false`, pas de log. Vous publiez un chiffre faux.

### Problème 2 — La validation ne couvre pas tous les cas

Toujours `cleanSequence()`, dans [`SequenceTrait`](../../Domain/Sequence/Traits/SequenceTrait.php) :

```php
if ($sMolType == "DNA") { ... return false; }
elseif ($sMolType == "RNA") { ... return false; }
// et sinon ? rien.
```

`cleanSequence("GAVLI", "PROTEIN")` retourne **`NULL`**. Aucune validation n'existe pour les protéines. La fonction *a l'air* de protéger, mais elle ne protège que deux cas sur trois.

### Problème 3 — Les données réelles ne rentrent pas telles quelles

Un enregistrement GenBank stocke sa séquence **en minuscules, découpée en blocs de dix séparés par des espaces** :

```
aagactgcat ccggctccag gaaaagcgag tgggatatcc
```

Passez ça directement à `complement()` :

```php
$oManager->complement("aagactgcat", "DNA");
// Exception: Unrecognized nucleotide symbol "a" at position 0.
```

Ça plante. Il faut donc penser à normaliser à la main, **à chaque appel**, dans chaque application cliente. Personne ne le fait systématiquement.

### Et le problème de fond, derrière les trois

```php
$sequenceManager->complement($sSequence, "DNA");
```

Le type de molécule est passé **à côté** de la séquence, en second paramètre. Rien n'empêche de passer `"DNA"` sur une séquence ARN. Vous n'aurez pas d'erreur : vous aurez un résultat biologiquement faux. L'information « cette chaîne est de l'ADN » n'est portée par rien, elle circule dans la tête du développeur.

---

## 2. C'est quoi un value object ?

Un **objet de valeur** (*value object*, VO) est un petit objet qui représente **une valeur**, pas **une chose**. Trois critères le définissent :

**1. Il n'a pas d'identité.** Deux billets de 10 € sont interchangeables. Deux séquences `"ATGC"` aussi. À l'inverse, deux patients qui s'appellent tous les deux « Dupont » restent deux personnes différentes : eux ont une identité.

**2. Il est immuable.** Une fois créé, il ne change plus. `5` ne devient pas `6` ; on obtient `6` en calculant `5 + 1`, ce qui crée une *nouvelle* valeur. Pareil ici : `$oAdn->complement()` ne modifie pas `$oAdn`, il renvoie un nouvel objet.

**3. Son égalité se fait sur le contenu.** Deux objets contenant `"ATGC"` sont égaux, même s'il s'agit de deux instances distinctes en mémoire.

### Le test qui tranche, dans BioPHP

| Concept | Identité ? | Catégorie |
|---|---|---|
| L'enregistrement `NM_031438` | Oui : son numéro d'accession `primAcc` | **Entité** — [`Domain/Sequence/Entity/Sequence.php`](../../Domain/Sequence/Entity/Sequence.php) |
| La suite de symboles `"ATGCGT"` | Non : deux `"ATGCGT"` sont la même chose | **Value object** — nouveau |

C'est la distinction clé, et elle explique pourquoi les VO **ne remplacent pas** l'entité `Sequence`. Deux enregistrements différents peuvent parfaitement porter la même séquence : ils restent deux enregistrements. `Sequence` garde donc son identité, sa table, son ORM. Le VO ne s'occupe que des symboles.

### Le troisième intérêt : le type devient une garantie

C'est le bénéfice le moins évident et le plus puissant. Quand vous écrivez :

```php
public function analyser(DnaSequence $oAdn) { ... }
```

…il est **impossible** d'y faire entrer une séquence ARN. Pas « déconseillé » : impossible, PHP refuse. Et si l'objet existe, c'est qu'il a passé la validation à la construction. Autrement dit : **on valide une fois, au moment d'entrer dans le domaine, et plus jamais ensuite.** Le reste du code peut faire confiance au type. C'est ce qu'on appelle rendre les états invalides *irreprésentables*.

### Ne pas confondre avec les voisins

BioPHP a maintenant quatre familles d'objets, et il vaut mieux savoir les distinguer :

| Dossier | Nature | Muable ? | Identité ? |
|---|---|---|---|
| `Domain/*/Entity/` | Entités Doctrine persistées | Oui | Oui |
| `Domain/Model/` | Structures de données non persistées (`PdbAtom`, `PdbHelix`…) | Oui (setters) | Non |
| `Api/DTO/` | Frontière HTTP, désérialisation JMS | Oui | Non |
| **`Domain/Sequence/ValueObject/`** | **Valeurs du domaine, avec règles métier** | **Non** | **Non** |

Nuance à corriger au passage : le compte rendu du 13 août appelait `PdbAtom`, `PdbHelix` et `PdbSheet` des « objets de valeur ». Ce sont en réalité des **structures muables** avec getters/setters, sans règle de validation. Utiles, mais ce ne sont pas des VO au sens strict. Le vocabulaire est maintenant réservé au nouveau dossier.

---

## 3. Ce qui a été créé

Nouveau namespace [`Amelaye\BioPHP\Domain\Sequence\ValueObject`](../../Domain/Sequence/ValueObject/).

| Fichier | Rôle |
|---|---|
| [`AbstractMolecularSequence.php`](../../Domain/Sequence/ValueObject/AbstractMolecularSequence.php) | Base commune : normalisation, validation, immuabilité, égalité par valeur. Implémente `\Stringable`. |
| [`AbstractNucleicSequence.php`](../../Domain/Sequence/ValueObject/AbstractNucleicSequence.php) | Ce que l'ADN et l'ARN partagent : `complement()`, `reverseComplement()`, `getGcContent()`. |
| [`DnaSequence.php`](../../Domain/Sequence/ValueObject/DnaSequence.php) | Alphabet `ACGTMRWSYKVHDBXN`. Plus `toRna()` (transcription). |
| [`RnaSequence.php`](../../Domain/Sequence/ValueObject/RnaSequence.php) | Alphabet `ACGUMRWSYKVHDBXN`. Plus `toDna()` (transcription inverse). |
| [`AminoAcidSequence.php`](../../Domain/Sequence/ValueObject/AminoAcidSequence.php) | Alphabet `ACDEFGHIKLMNPQRSTVWYX*`. Plus `hasStop()`, `truncateAtStop()`, `hasUnknownResidue()`. |
| [`MolecularSequenceFactory.php`](../../Domain/Sequence/ValueObject/MolecularSequenceFactory.php) | Fabrique le bon VO à partir d'un `molType` brut de parseur. |
| [`InvalidSequenceException.php`](../../Domain/Sequence/ValueObject/InvalidSequenceException.php) | Erreur explicite : symbole fautif **et** sa position. |

API commune : `getValue()`, `getMolType()`, `getAlphabet()`, `getLength()`, `isEmpty()`, `equals()`, `subSequence()`, `reverse()`, `countSymbol()`, et `isValid()` en statique.

### Avant / après

```php
// AVANT — le type de molécule voyage à côté, la normalisation est à votre charge
$sSequence = "aagactgcat ccggctccag";
$sSequence = strtoupper(str_replace(" ", "", $sSequence));   // à ne pas oublier
$sComplement = $sequenceManager->complement($sSequence, "DNA"); // "DNA" : à ne pas se tromper

// APRÈS — le type porte l'information, la normalisation et la validation sont acquises
$oAdn = new DnaSequence("aagactgcat ccggctccag");
$sComplement = $oAdn->complement()->getValue();
$oArn        = $oAdn->toRna();          // transcription, sans risque de confusion T/U
$fGc         = $oAdn->getGcContent();   // taux GC
```

Et sur une saisie fautive, l'erreur arrive **immédiatement et nommément**, au lieu de produire un poids moléculaire faux 200 lignes plus loin :

```php
new DnaSequence("ATGUC");
// InvalidSequenceException: Invalid DNA symbol "U" at position 3.
```

---

## 4. Deux décisions à expliquer

### Le piège de l'ARN messager GenBank

En principe, `molType = "mRNA"` devrait donner un `RnaSequence`. En pratique, **GenBank et EMBL écrivent leurs entrées mRNA avec de la thymine**, suivant la convention ADNc. La fixture `NM_031438` du dépôt en est l'exemple : elle est déclarée `mRNA` et commence par `aagactgcat…`.

Un mapping naïf `mRNA → RnaSequence` aurait donc planté sur les données réelles du projet. La règle retenue dans la fabrique : **quand les symboles contredisent le type déclaré, les symboles gagnent.** Présence de `T` → ADN, présence de `U` → ARN, et à défaut de l'un comme de l'autre, le type déclaré tranche. C'est documenté dans le docblock et couvert par un test dédié.

### Les alphabets ne sortent pas de l'IUPAC, mais du code existant

Deux choix délibérés :

- **ADN/ARN** : alphabet repris exactement de `SequenceTrait::cleanSequence()` — les bases canoniques plus les codes dégénérés `MRWSYKVHDBXN`. Cohérence avec ce que la bibliothèque tolère déjà.
- **Protéines** : les 20 acides aminés, plus `X` (résidu inconnu) et `*` (codon stop) — soit exactement ce qu'acceptent `charge()` et `chemicalGroup()`. L'IUPAC étendu (`B`, `Z`) a été **volontairement exclu** : un VO déclaré valide qui ferait ensuite exploser `charge()` serait pire qu'inutile, il serait mensonger.

Principe général : un VO ne doit jamais promettre plus que ce que le reste du code sait traiter.

---

## 5. Intégration : additif, rien de cassé

Un seul point de contact avec l'existant : [`SequenceBuilder::getMolecularSequence()`](../../Domain/Sequence/Builder/SequenceBuilder.php), qui enveloppe l'entité injectée dans le VO correspondant à son type de molécule.

Cette méthode a été ajoutée **sur la classe uniquement, pas sur `SequenceInterface`**. Ajouter une méthode à une interface publique casserait tous ses implémenteurs — et `amelaye/biotools` consomme directement ces classes. Aucune signature existante n'a été touchée, aucun service XML n'a eu besoin de changer (les VO ne sont pas des services, la fabrique est statique).

Autrement dit : **le code existant continue de fonctionner exactement comme avant.** Les VO sont une porte d'entrée supplémentaire, pas un remplacement imposé.

---

## 6. Les bugs corrigés dans la foulée

Une fois les VO en place, les trois bugs de la section 1 sautaient aux yeux. Ils ont été corrigés — et deux autres du même genre ont été trouvés au passage. Tous relèvent de la même maladie : **rendre un résultat faux plutôt que dire qu'on ne sait pas.**

### Bug 1 — `cleanSequence()` ne validait rien d'utilisable

Fichier : [`Domain/Sequence/Traits/SequenceTrait.php`](../../Domain/Sequence/Traits/SequenceTrait.php)

Trois défauts d'un coup. La fonction retournait `false` sur erreur mais **`NULL` en cas de succès** — impossible d'écrire `if (cleanSequence(...))` de façon fiable. Elle ne connaissait que l'ADN et l'ARN, laissant les protéines sans validation. Et elle était sensible à la casse, donc une séquence GenBank en minuscules était déclarée invalide alors qu'elle est parfaitement correcte.

Corrigé : table d'alphabets couvrant les trois types de molécules, comparaison insensible à la casse, et un vrai booléen — `true` valide, `false` invalide **ou** type de molécule non vérifiable.

### Bug 2 — `molwt()` ignorait la validation

Fichier : [`Domain/Sequence/Service/SequenceManager.php`](../../Domain/Sequence/Service/SequenceManager.php)

La valeur de retour de `cleanSequence()` est maintenant lue, et un symbole inconnu lève une exception au lieu de peser zéro. `molwt("upperlimit", "ATGZ", "DNA", 4)` ne renvoie plus `964.73` : il dit clairement `Unrecognized DNA symbol in input sequence.`

### Bug 3 — `complement()` refusait les données réelles

Même fichier. La séquence est normalisée en entrée (majuscules, espaces retirés), donc un enregistrement GenBank passe désormais tel quel :

```php
$oManager->complement("aagac tgcat", "DNA");   // "TTCTGACGTA"
```

### Bug 4 (trouvé en chemin) — les symboles dégénérés pesaient zéro

C'était le bug 2 en plus vicieux. `cleanSequence()` accepte les codes IUPAC dégénérés (`N`, `R`, `Y`, `S`…), mais la base de données de l'API ne fournit le poids que des **quatre bases canoniques**. Une séquence contenant un `N` — cas parfaitement banal — passait la validation, puis heurtait une clé absente du tableau des poids et comptait ce `N` pour **zéro**.

Pire : c'est ce qui rendait les paramètres `lowerlimit` et `upperlimit` totalement décoratifs. Les deux accumulateurs recevaient exactement la même valeur, donc les deux limites étaient toujours égales.

Le [BioPHP d'origine](../../Legacy/seq.php) faisait pourtant les choses correctement : sa table associait à **chaque** symbole une paire `[limite basse, limite haute]`. Un `N` peut être n'importe quelle base, donc il pèse au minimum le poids d'une cytosine et au maximum celui d'une guanine. **C'est là tout le sens de ces deux limites**, perdu lors de la refonte.

Rétabli, mais en calculant les paires plutôt qu'en recopiant la table du Legacy : pour chaque code dégénéré, on prend le minimum et le maximum des bases qu'il représente. Vérifié : ce calcul reproduit exactement la table historique, pour l'ADN comme pour l'ARN.

```php
$oManager->molwt("lowerlimit", "N", "DNA", 1);   // 307.23  (cytosine + eau)
$oManager->molwt("upperlimit", "N", "DNA", 1);   // 347.26  (guanine + eau)
```

### Bug 5 (trouvé en chemin) — la limite demandée était ignorée

Fichier : [`Domain/Sequence/Builder/SequenceBuilder.php`](../../Domain/Sequence/Builder/SequenceBuilder.php)

```php
// la signature attend  molwt($sLimit, $sSequence, $sMolType, $iNALen)
return $this->sequenceManager->molwt($sMolType, $sSequence, $sMolType, $iNALen);
//                                   ^^^^^^^^^ le type de molécule à la place de la limite
```

Le paramètre `$sLimit` du builder n'était **jamais transmis**. Demander `upperlimit` ou `lowerlimit` ne changeait rien. Le bug était invisible tant que les deux limites étaient égales (bug 4) — corriger l'un rendait l'autre observable. Bel exemple de bugs qui se couvrent mutuellement.

Et un dernier détail au passage : dans `molwt()`, `lowerlimit` lisait la case `1` du tableau et `upperlimit` la case `0`, alors que la case `0` est celle de la limite basse. **Les deux limites étaient inversées.** Invisible pour la même raison. Corrigé aussi.

### Ce que ça change pour vous

| Appel | Avant | Après |
|---|---|---|
| `molwt(..., "ATGZ", "DNA", 4)` | `964.73` (faux) | Exception explicite |
| `molwt("lowerlimit"/"upperlimit", "N", ...)` | `18.015` / `18.015` | `307.23` / `347.26` |
| `complement("aagactgcat", "DNA")` | Exception | `"TTCTGACGTA"` |
| `cleanSequence("ACGTN", "DNA")` | `NULL` | `true` |
| `cleanSequence("GAVLIJ", "PROTEIN")` | `NULL` | `false` |
| `molwt(..., "ATGC", "DNA", 4)` | `1253.945` | `1253.945` (inchangé) |

**Aucune signature n'a changé.** Un code qui passait des données propres obtient exactement les mêmes résultats qu'avant — la dernière ligne du tableau le montre, et c'est le test `testMolWT` existant, laissé intact, qui le garantit. Ce qui change, c'est le sort des données sales : elles produisent maintenant une erreur au lieu d'un chiffre faux.

## 7. Changement d'architecture : un dossier `Parser`

Dernier chantier de la session, indépendant des deux premiers.

### Le constat

`Domain/Database/Service/` contenait huit classes qui ne faisaient pas le même métier :

- `DatabaseManager`, le service qui lit un fichier et le découpe en enregistrements ;
- `ParseDbAbstractManager`, la classe de base qui porte les entités communes ;
- **six parseurs de formats** — GenBank, Swiss-Prot, EMBL, PDB, PROSITE, ExPASy ENZYME.

Un dossier nommé `Service` qui contient six classes de parsing et deux services, c'est un dossier qui a grossi sans qu'on lui redemande son avis. Les quatre parseurs ajoutés le 13 août ont fait basculer la balance : le parsing est devenu la responsabilité dominante du dossier, alors que le nom du dossier ne l'évoque même pas.

### Ce qui a bougé

| Avant | Après |
|---|---|
| `Domain/Database/Service/Parse{Genbank,Swissprot,Embl,Pdb,Prosite,ExpasyEnzyme}Manager.php` | `Domain/Parser/` |
| `Domain/Database/Service/ParseDbAbstractManager.php` | **inchangé** |
| `Domain/Database/Service/DatabaseManager.php` | **inchangé** |

Namespace : `Amelaye\BioPHP\Domain\Parser`.

La classe abstraite reste volontairement du côté `Database`. Ce n'est pas un parseur : c'est le socle d'entités (`Sequence`, `GbSequence`, `SrcForm`…) partagé par ceux qui produisent des enregistrements de base de données. Trois des six parseurs en héritent, trois autres — PDB, PROSITE, ExPASy — ne s'en servent pas du tout et implémentent directement `ParseDatabaseInterface`.

Conséquence à noter pour qui débute : tant que les parseurs partageaient le namespace de leur classe mère, PHP la trouvait tout seul. Maintenant qu'ils ont déménagé, les trois héritiers doivent l'**importer explicitement** :

```php
use Amelaye\BioPHP\Domain\Database\Service\ParseDbAbstractManager;
```

### Les répercussions

Un déplacement de fichiers n'est jamais qu'un déplacement de fichiers. Il a fallu suivre :

- [`DatabaseReaderFactory`](../../Domain/Database/Factory/DatabaseReaderFactory.php) : six `use` réécrits. C'est le seul endroit du code qui instancie les parseurs.
- **Le câblage Symfony** : les deux définitions de services qui restaient ont été supprimées (voir plus bas).
- **Les tests** suivent la production : les six `*Test.php` sont passés dans `Tests/Domain/Parser/`, convention du dépôt oblige. `DatabaseManagerTest` reste côté `Database`.
- [`CLAUDE.md`](../../CLAUDE.md) : carte du dépôt mise à jour.

Le tout en `git mv`, pour que l'historique de chaque fichier suive le déplacement plutôt que d'apparaître comme une suppression suivie d'une création.

### Puis rendre la factory logique

Le déplacement a rendu visible un défaut plus profond. Le savoir sur **un** format était éclaté sur **trois `switch`** dans **deux** classes :

| Où | Ce que ça savait |
|---|---|
| `DatabaseReaderFactory::readDatabase()` | quelle classe instancier |
| `DatabaseRecorderFactory::getEntryStart()` | comment reconnaître un début d'entrée |
| `DatabaseRecorderFactory::getEntryId()` | comment extraire l'identifiant |
| le parseur lui-même | comment parser |

Ajouter un format demandait de toucher quatre endroits, dont trois étaient le *même* `switch` sur la *même* chaîne. C'est de la chirurgie au fusil à pompe. Et rien ne rattrapait l'oubli : un `case` manquant compile parfaitement et n'échoue qu'à l'exécution, sans dire lequel des trois manque.

Deux noms mentaient aussi. `DatabaseReaderFactory::readDatabase()` ne fabrique pas : elle fabrique **et** lance le parsing. `DatabaseRecorderFactory` ne fabriquait rien du tout — deux fonctions de découpage de fichier baptisées « factory ».

**Le principe appliqué** : comment reconnaître un début d'entrée GenBank, c'est du savoir GenBank. Ça appartient à `ParseGenbankManager`, pas à une factory. Chaque parseur déclare donc maintenant, en statique, son nom de format et son découpage d'entrée :

```php
public static function getFormat() : string      { return "GENBANK"; }
public static function isEntryStart(string $sLine) : bool
{
    return substr($sLine, 0, 5) == "LOCUS";
}
public static function getEntryId(array $aFlines, string $sLine) : string { ... }
```

Et une nouvelle [`DatabaseParserFactory`](../../Domain/Database/Factory/DatabaseParserFactory.php) devient le seul endroit qui sait quels parseurs existent — une liste de six lignes qui remplace les trois `switch`. Les deux anciennes factories gardent **exactement** leurs signatures et délèguent au registre.

Bilan : 160 lignes de `switch` tombent à 23 lignes de délégation. Ajouter un format, c'est désormais écrire la classe et ajouter une ligne à la liste.

Une étape reste possible plus tard, mais elle est cassante : déclarer ces trois méthodes dans `ParseDatabaseInterface`. À partir de là, un parseur incomplet ne compilerait plus — PHP garantirait ce qu'aucun `switch` ne pouvait garantir. À réserver à une version majeure, puisque ça casserait tout implémenteur externe de l'interface.

### Un `prev()` mort depuis toujours

En déplaçant `getEntryId()`, un détail a sauté aux yeux : les cas Swiss-Prot et PROSITE appelaient `prev($flines)` pour reculer le pointeur du tableau. Or `$flines` n'était **pas** passé par référence à la factory — le `prev()` ne touchait qu'une copie locale. Et même s'il l'avait touchée, `DatabaseManager` parcourt le fichier avec `foreach`, qui en PHP 7+ n'utilise pas le pointeur interne. Doublement mort. Recopier cet appel dans du code neuf aurait figé le malentendu : il a été supprimé.

Autre effet du typage : ces deux méthodes retournaient implicitement `null` quand aucune ligne `AC` n'était trouvée. Elles déclarent maintenant `: string` et renvoient une chaîne vide, avec un test pour chacune.

### Deux choses apprises en chemin

**Les définitions de services des parseurs étaient décoratives — elles ont été supprimées.** `DatabaseReaderFactory` instancie les parseurs avec `new`, pas par injection : deux d'entre eux étaient déclarés dans le XML sans que rien ne s'en serve. Après un aller-retour, la décision a été de les retirer plutôt que de passer la factory à l'injection, pour trois raisons.

D'abord, `readDatabase()` est **statique**, appelée statiquement depuis `DatabaseManager` : passer à l'injection change l'API publique de la factory, donc casse les consommateurs.

Ensuite, **les parseurs sont à état** : `parseDataFile()` remplit un état interne que les getters relisent ensuite. Or les services Symfony sont des singletons partagés. Injecter un parseur, c'est faire partager cet état entre deux lectures successives ; il faudrait `shared="false"` sur chacun — un piège facile à oublier et parfaitement silencieux quand on l'oublie. Le `new` de la factory garantit gratuitement ce que `shared="false"` devrait rattraper.

Enfin, le bénéfice immédiat est nul : personne n'injecte ces classes et il n'existe aucun point d'extension pour une application hôte.

Le jour où l'extensibilité deviendra l'objectif — permettre à une appli hôte d'ajouter son propre format — la bonne forme ne sera de toute façon pas « enregistrer les six ». Ce sera un registre de services tagués sur `ParseDatabaseInterface`, indexé par nom de format, avec `shared="false"`. Les deux entrées supprimées n'auraient pas été un marchepied vers ça.

Conséquence : `Domain/Parser/` n'a **pas** de dossier `Resources/config/`, contrairement à `Domain/Database/`, `Domain/Sequence/` et `Api/`. C'est volontaire : un fichier de configuration vide serait du bruit.

**Un service privé et non référencé n'existe pas dans le conteneur compilé.** En voulant ajouter au test d'intégration une assertion `$container->has(ParseGenbankManager::class)` pour verrouiller le nouveau câblage, elle est revenue rouge. Rien à voir avec le déplacement : `<defaults public="false" />` rend ces services privés, et Symfony **supprime à la compilation** tout service privé que personne n'injecte. L'assertion a donc été retirée — elle testait quelque chose qui n'a jamais été vrai. Le test existant couvre déjà le cas de toute façon : si le chemin du nouveau XML était faux, `$loader->load()` ferait échouer la compilation du conteneur.

## 8. Récapitulatif des fichiers

| Fichier | Changement |
|---|---|
| `Domain/Sequence/ValueObject/AbstractMolecularSequence.php` | Créé |
| `Domain/Sequence/ValueObject/AbstractNucleicSequence.php` | Créé |
| `Domain/Sequence/ValueObject/DnaSequence.php` | Créé |
| `Domain/Sequence/ValueObject/RnaSequence.php` | Créé |
| `Domain/Sequence/ValueObject/AminoAcidSequence.php` | Créé |
| `Domain/Sequence/ValueObject/MolecularSequenceFactory.php` | Créé |
| `Domain/Sequence/ValueObject/InvalidSequenceException.php` | Créé |
| `Domain/Sequence/Builder/SequenceBuilder.php` | +14 lignes : `getMolecularSequence()` et ses imports — **et** correction du bug 5 |
| `Domain/Sequence/Traits/SequenceTrait.php` | `cleanSequence()` réécrite (bug 1) |
| `Domain/Sequence/Service/SequenceManager.php` | `molwt()` réécrite (bugs 2 et 4), `complement()` normalise son entrée (bug 3), nouvelle méthode privée `getNucleotidWeightLimits()` |
| `Tests/Domain/Sequence/ValueObject/` (4 fichiers) | Créés |
| `Tests/Domain/Sequence/Builder/SequenceBuilderTest.php` | Créé — le builder n'avait aucun test jusqu'ici |
| `Tests/Domain/Sequence/Traits/SequenceTraitTest.php` | 3 tests mis à jour (ils figeaient le comportement bugué), 4 ajoutés |
| `Tests/Domain/Sequence/Service/SequenceManagerTest.php` | 8 tests de non-régression ajoutés pour les bugs 2 à 5 |

Et pour le changement d'architecture :

| Fichier | Changement |
|---|---|
| `Domain/Parser/Parse{Genbank,Swissprot,Embl,Pdb,Prosite,ExpasyEnzyme}Manager.php` | Déplacés depuis `Domain/Database/Service/`, namespace réécrit |
| `Tests/Domain/Parser/` (6 fichiers) | Déplacés depuis `Tests/Domain/Database/Service/` |
| `Domain/Database/Resources/config/services.xml` | Deux définitions de parseurs supprimées |
| `Domain/Database/Factory/DatabaseParserFactory.php` | Créé — registre unique des parseurs |
| `Domain/Database/Factory/DatabaseReaderFactory.php` | `switch` remplacé par une délégation au registre |
| `Domain/Database/Factory/DatabaseRecorderFactory.php` | Deux `switch` remplacés par une délégation ; `prev()` mort supprimé |
| `Domain/Parser/Parse*Manager.php` (6) | `getFormat()`, `isEntryStart()`, `getEntryId()` ajoutés |
| `Tests/Domain/Database/Factory/DatabaseParserFactoryTest.php` | Créé |
| `Tests/Domain/Parser/Parse{Swissprot,Prosite}ManagerTest.php` | 3 tests ajoutés chacun |
| `CLAUDE.md` | Carte du dépôt : `Domain/Parser/` et `Domain/Sequence/ValueObject/` |

**51 nouveaux tests.** Cas couverts : séquence vide, normalisation casse/espaces/retours ligne, symboles dégénérés, `U` refusé en ADN et `T` refusé en ARN, aller-retour transcription ADN→ARN→ADN, immuabilité (l'entité source n'est pas modifiée), et les trois `molType` bruts réellement produits par les parseurs (`"mRNA"`, `"ss-DNA"`, `"PRT;"`).

## 9. État final

- **Suite complète** : **319 tests / 790 assertions, 0 échec, 0 erreur** (contre 231 avant la session).
- `php -l` passé sur tous les fichiers créés ou modifiés.
- Les en-têtes `Last modified` des fichiers de production modifiés ont été passés au 25 août 2026.
- **Aucune signature publique n'a changé** sur les trois chantiers. Les seuls consommateurs à retoucher sont ceux qui référencent un parseur par son nom complet : le namespace a changé, la classe non.
- PHP 8.5 en local ; rien au-delà de PHP 8.0 dans le code ajouté, donc compatible avec le minimum 8.2 de la CI.
- **Rien n'a été committé** : tout reste dans l'arbre de travail.

## 10. Ce qui reste ouvert

- **Les services ne consomment pas encore les VO.** `SequenceManager` continue de travailler sur des `string`. Faire accepter un `AbstractMolecularSequence` en entrée serait la suite logique — mais c'est un changement de signature, donc cassant pour `amelaye/biotools`. À planifier pour une version majeure, pas à glisser dans une alpha.
- **Le type de molécule des parseurs reste ambigu pour `molwt()`.** `molwt()` n'accepte que `"DNA"` et `"RNA"`, alors que les parseurs stockent `"mRNA"`, `"ss-DNA"` ou `"PRT;"`. Passer un enregistrement GenBank au builder lève donc maintenant une exception claire — au lieu d'un avertissement PHP suivi d'un résultat vide. `MolecularSequenceFactory` sait déjà résoudre cette ambiguïté ; la faire servir ici est la suite naturelle.
- **Le poids moléculaire des protéines n'est toujours pas implémenté.** Le Legacy déléguait à une classe `Protein` ; la refonte n'a pas repris ce chemin. `molwt()` le dit maintenant explicitement au lieu d'échouer bizarrement.
- **D'autres candidats VO** existent dans le domaine : le codon (trio de bases), l'intervalle `start`/`end` d'une `Feature`, le numéro d'accession, le poids moléculaire avec son unité. Aucun n'a été traité ici — la session s'est volontairement limitée aux séquences, l'élément le plus caractéristique du domaine.
