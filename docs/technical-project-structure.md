# Fonctionnement technique : projets et structures de dataset

## 1. Vue d'ensemble

L'application sépare deux notions :

- **Le projet** : son nom, sa description, son format d'export et son schéma.
- **La donnée** : une valeur saisie par l'utilisateur et enregistrée dans `examples.content`.

Le schéma est défini une seule fois. Il sert ensuite de contrat au formulaire généré dans l'atelier du projet.

```text
Projet
├── métadonnées
├── format d'export
└── schema[]
    ├── champ racine
    ├── champ enfant
    └── champ répétitif

Données du projet
└── examples[]
    └── content : objet reconstruit depuis le formulaire
```

## 2. Création d'un projet

L'utilisateur ouvre la page d'accueil et remplit le formulaire de création :

- `name` : nom du projet ;
- `description` : description facultative ;
- `format` : format de sortie choisi.

Le navigateur envoie une requête `POST /projects/create`. Le contrôleur vérifie le nom et le format, puis `Project::create()` crée le document MongoDB avec un schéma vide :

```json
{
  "name": "Dataset Medical",
  "description": "Informations médicales structurées",
  "format": "json",
  "schema": [],
  "tags": [],
  "examples_count": 0,
  "created_at": "date",
  "updated_at": "date"
}
```

Le schéma est ensuite défini depuis la page de l'atelier. Cette séparation permet de créer le projet avant de connaître tous ses champs et reste compatible avec les anciens projets qui ne possèdent pas encore de champ `schema`.

## 3. Définition du schéma

Dans l'atelier, l'utilisateur ajoute une ligne par champ. Une définition contient :

| Propriété | Valeurs | Rôle |
|---|---|---|
| `name` | texte | Nom technique du champ |
| `type` | `string`, `integer`, `number`, `boolean` | Type de la valeur |
| `parent` | vide ou nom d'un champ | Objet parent du champ |
| `repeat_on` | vide ou nom d'un champ entier | Compteur de répétition |
| `required` | `true` ou `false` | Indique un champ obligatoire dans l'interface |

Exemple de schéma :

```json
[
  { "name": "nom", "type": "string", "parent": "", "repeat_on": "", "required": true },
  { "name": "numero", "type": "integer", "parent": "", "repeat_on": "", "required": false },
  { "name": "nom_femme", "type": "string", "parent": "famille", "repeat_on": "", "required": false },
  { "name": "nombre_d_enfants", "type": "integer", "parent": "famille", "repeat_on": "", "required": false },
  { "name": "nom_enfant", "type": "string", "parent": "famille", "repeat_on": "nombre_d_enfants", "required": false }
]
```

Le bouton **Enregistrer la structure** envoie le schéma avec `PUT /projects/update/{id}`. Le contrôleur normalise les valeurs et refuse les types inconnus ou les champs sans nom.

## 4. Champs imbriqués

Un champ dont `parent` vaut `famille` est rendu sous l'objet `famille`.

La saisie suivante :

```text
Nom de la femme : Marie
Nombre d'enfants : 2
```

est reconstruite côté navigateur en :

```json
{
  "famille": {
    "nom_femme": "Marie",
    "nombre_d_enfants": 2
  }
}
```

Le navigateur utilise un chemin de champ comme `famille.nom_femme`, puis reconstruit automatiquement les objets avant l'envoi à l'API.

## 5. Champs répétitifs

Un champ peut dépendre d'un autre champ entier avec `repeat_on`.

Dans l'exemple précédent, `nom_enfant` dépend de `nombre_d_enfants`. Si l'utilisateur saisit `3`, le formulaire crée :

```text
Nom enfant 1
Nom enfant 2
Nom enfant 3
```

Les clés envoyées sont ensuite :

```json
{
  "famille": {
    "nombre_d_enfants": 3,
    "nom_enfant_1": "Paul",
    "nom_enfant_2": "David",
    "nom_enfant_3": "Sarah"
  }
}
```

Le formulaire est régénéré lorsque la valeur du compteur change. Les valeurs déjà saisies sont conservées autant que possible pendant cette régénération.

## 6. Génération et sauvegarde des données

Depuis le bouton d'ajout, l'utilisateur choisit le nombre de données à remplir. Le formulaire affiche un bloc indépendant pour chaque donnée, avec un maximum de 100 blocs par ouverture.

À la validation :

1. le navigateur lit les valeurs des champs générés ;
2. il reconstruit les objets parents ;
3. il convertit les types numériques et booléens ;
4. il envoie une requête `POST /examples/create` pour chaque donnée ;
5. l'API enregistre le résultat dans `examples.content` ;
6. le compteur `examples_count` du projet est incrémenté.

Une donnée est stockée sous cette forme :

```json
{
  "project_id": "ObjectId(...) ",
  "format": "json",
  "content": {
    "nom": "Jean",
    "numero": 1234,
    "famille": {
      "nom_femme": "Marie",
      "nombre_d_enfants": 2,
      "nom_enfant_1": "Paul",
      "nom_enfant_2": "David"
    }
  },
  "created_at": "date",
  "updated_at": "date"
}
```

## 7. Export

Les exporteurs ne lisent pas directement le schéma. Ils lisent les données déjà reconstruites dans `examples.content`.

- `json` produit un tableau JSON contenant chaque `content` ;
- `jsonl` produit une donnée JSON par ligne ;
- les exporteurs spécialisés transforment le contenu selon leur contrat, par exemple Alpaca ou OpenAI Messages.

Le format choisi à la création est le format par défaut du projet, mais le panneau d'export peut proposer plusieurs formats compatibles.

## 8. Fichiers responsables du fonctionnement

| Fichier | Responsabilité |
|---|---|
| `views/projects/index.php` | Création d'un projet |
| `views/projects/show.php` | Atelier du projet et conteneurs d'interface |
| `public/assets/js/projects.js` | Liste et création des projets |
| `public/assets/js/examples.js` | Schéma, formulaire généré et données |
| `src/Models/Project.php` | Persistance des projets et du schéma |
| `src/Models/Example.php` | Persistance des données |
| `src/Controllers/Projectcontroller.php` | Validation et API des projets |
| `src/Controllers/Examplecontroller.php` | API des données |
| `src/Exports/Exporterfactory.php` | Sélection de l'exporteur |
| `database/init-db.js` | Validation MongoDB et index |

## 9. Compatibilité et évolution

Les projets existants sans `schema` sont lus comme ayant un schéma vide. Ils doivent recevoir une structure avant de pouvoir ajouter une nouvelle donnée depuis le formulaire généré.

Pour ajouter un nouveau type de champ, il faut mettre à jour :

1. la liste des types autorisés dans `ProjectController` ;
2. le rendu du champ dans `examples.js` ;
3. la conversion de valeur avant sauvegarde ;
4. la validation MongoDB dans `database/init-db.js`.

Pour ajouter un format d'export, il faut créer un exporteur implémentant `ExporterInterface`, puis l'enregistrer dans `ExporterFactory`.

## 10. Démarrage local

Depuis la racine du projet :

```bash
composer install
php -S localhost:8000 -t public
```

L'application est disponible à l'adresse `http://localhost:8000`, avec MongoDB lancé et les variables d'environnement configurées.
