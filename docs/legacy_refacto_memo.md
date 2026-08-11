# Mémo de migration Legacy → Refacto BioPHP

Date de synthèse : 2026-08-11

## Contexte

Le dossier Legacy contient une batterie de modules historiques écrits en PHP procédure et en mono-fichier,
à l’origine conçue pour des opérations de parsing, de séquençage et de manipulation biologique.
La structure actuelle du dépôt est une refacto orientée architecture : elle délègue les responsabilités
à des services Domain, des entités, des fabriques, des adapters API et des DTO de réponse.

## Comparaison fonctionnelle

| Ancien Legacy | Fonction historique | Remplacement dans la refacto |
| --- | --- | --- |
| Legacy/seq.php | Fonctions nucléotidiques de base (complement, revcomp, halfstr, get_bridge, expand_na) | Domain/Sequence/Service/SequenceManager.php + Domain/Sequence/Builder/SequenceBuilder.php ; l’API public de façade s’appuie sur SequenceManager et les traits de formatage |
| Legacy/protein class within seq.php | Calcul de la masse molaire et des propriétés d’un bloc protéique | Domain/Sequence/Entity/Protein.php et Domain/Sequence/Service/ProteinManager.php |
| Legacy/seqalign.php | Structures d’alignement multi-séquences et parcours de résultats | Domain/Sequence/Service/SequenceAlignmentManager.php et interface SequenceAlignmentInterface |
| Legacy/seqdb.php | Indexeur et lecteur de bases de séquences, parsing des fichiers d’entrées, gestion des points d’entrée | Domain/Database/Service/DatabaseManager.php plus factories DatabaseReaderFactory / DatabaseRecorderFactory ; Domain/Database/Service/ParseGenbankManager.php et ParseSwissprotManager.php |
| Legacy/genome.inc.php, Legacy/kegg.inc.php, Legacy/unigene.inc.php, Legacy/transfac.inc.php | Parseurs de catalogues de données biologiques et taxonomiques | Domain/Database/Service/*Parse*Manager.php + entités Domain/Database/Entity/* et le support de l’ORM Doctrine |
| Legacy/resten.php | Base de données de restriction endonucléases, parsing d’arguments et dépôt de motifs | Domain/Sequence/Service/RestrictionEnzymeManager.php, Domain/Sequence/Entity/Enzyme.php, Domain/Sequence/Interfaces/RestrictionEnzymeInterface.php |
| Legacy/entrez.inc.php, Legacy/pdb.inc.php, Legacy/aaindex.inc.php, etc. | Accès/distribution de formats et tables de données extérieures | API adapters sous Api/*, DTOs sous Api/DTO/ et interfaces Api/Interfaces ; service résolus via DependencyInjection |

## Remplacement par couches

### 1. Couches de séquence
Le Legacy exposait des fonctions globales du type complement() et revcomp(). Elles étaient structurales et dépendaient de la globalisation d’un espace de fonctions procédural. Dans la refacto, ce rôle est concentré dans SequenceManager, lui-même consommé par SequenceBuilder pour servir de façade
d’état. Les entités Sequence, Protein, Enzyme et les traits FormatsTrait/SequenceTrait gardent un contrat de propriétés et de calculs immuable.

### 2. Couches de base de données
Le Legacy utilisait SeqDB / process_ft / get_entryid / line2r afin de lire des fichiers d’entrées.
Dans la refacto, ces responsabilités sont transférées à DatabaseManager, DatabaseReaderFactory,
DatabaseRecorderFactory et aux parsers ciblés ParseGenbankManager / ParseSwissprotManager.
L’indexation est aujourd’hui persistée via Doctrine comme collections d’éléments (Collection, CollectionElement).

### 3. API et DTO
Le Legacy est hérité, peu modulable et fortement couplé aux fichiers physiques. La version refacto introduit
un niveau d’abstraction API : Bioapi comme base commune, les classes Api/* qui exploitent Guzzle et JMS Serializer,
ainsi que des DTOs de réponse sous Api/DTO de façon à normaliser le flux depuis le service distant.

### 4. Services de calcul et outils
Les anciennes fonctions utilitaires de séquences et d’assemblage apparaissent dans les fichiers Legacy en standalone. La refacto les redistribue dans :
- Domain/Tools/Service/GeneticsFunctions.php
- Domain/Tools/Service/MathematicsFunctions.php
- Domain/Tools/Service/OligosManager.php
- Domain/Sequence/Service/SequenceMatchManager.php
- Domain/Sequence/Service/SequenceAlignmentManager.php

## Résumé de compatibilité

Le Legacy conservait un modèle historique de « fonctions en feu libre », utile pour la compréhension de la logique, mais fragile au niveau des dépendances entre modules.
La refacto garde la même logique métier, mais en la replaçant par des interfaces et services statés,
avec des injections de dépendances à travers le conteneur Symfony et Doctrine.

### Référence des fichiers actuels
- Domain/Sequence/Service/SequenceManager.php
- Domain/Sequence/Builder/SequenceBuilder.php
- Domain/Sequence/Service/RestrictionEnzymeManager.php
- Domain/Database/Service/DatabaseManager.php
- Domain/Database/Service/ParseGenbankManager.php
- Domain/Database/Service/ParseSwissprotManager.php
- Api/Bioapi.php
- Api/*Api.php
- Api/DTO/*DTO.php
