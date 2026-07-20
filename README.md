# Dataset Builder for AI Fine-Tuning

> Une application web PHP + MongoDB pour créer, gérer et exporter facilement des jeux de données destinés au fine-tuning de modèles d'intelligence artificielle (Llama, Qwen, Mistral, Gemma, et plus encore).

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-Database-47A248?logo=mongodb&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)
![Status](https://img.shields.io/badge/status-active-success)

---

## 📑 Table des matières

- [ Description](#-description)
- [ Fonctionnalités](#-fonctionnalités)
- [ Formats d'export](#-formats-dexport)
- [ Structure d'un exemple](#-structure-dun-exemple)
- [ Technologies](#️-technologies)
- [ Architecture](#️-architecture)
- [ Base de données](#️-base-de-données)
- [ API interne](#-api-interne)
- [ Installation](#️-installation)
- [ Export du dataset](#-export-du-dataset)
- [ Objectif du projet](#-objectif-du-projet)
- [ Pistes d'évolution (Bonus)](#-pistes-dévolution-bonus)
- [ Contribuer](#-contribuer)
- [ Licence](#-licence)

---

## 📖 Description

**Dataset Builder for AI Fine-Tuning** est une application web développée en **PHP** avec une base de données **MongoDB**, conçue pour permettre à n'importe qui — développeur, data scientist, chercheur ou passionné d'IA — de créer facilement des jeux de données de haute qualité destinés au **fine-tuning de modèles de langage (LLM)**.

L'application propose une interface simple et intuitive dans laquelle l'utilisateur peut :

1. Créer un projet de dataset,
2. Ajouter des exemples d'entraînement en remplissant des champs dédiés (instruction, contexte, réponse, conversation, etc.),
3. Enregistrer automatiquement chaque exemple dans MongoDB,
4. Exporter l'ensemble du dataset dans un format directement exploitable pour l'entraînement d'un modèle.

Le projet est pensé comme un outil **léger, rapide et pratique**, destiné à toute personne souhaitant produire ses propres datasets pour des modèles tels que **Llama**, **Qwen**, **Mistral**, **Gemma**, ou tout autre LLM open-source ou propriétaire.

---

## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
|  **Création d'un projet de dataset** | Initialiser un nouveau projet pour regrouper un ensemble cohérent d'exemples d'entraînement. |
|  **Ajout d'exemples d'entraînement** | Ajouter autant d'exemples que nécessaire via un formulaire adapté au format choisi. |
|  **Modification d'un exemple** | Éditer le contenu d'un exemple existant sans avoir à le recréer. |
|  **Suppression d'un exemple** | Retirer un exemple devenu obsolète ou incorrect. |
|  **Liste de tous les exemples** | Visualiser l'ensemble des exemples d'un projet sous forme de liste paginée. |
|  **Recherche d'exemples** | Rechercher rapidement un exemple par mot-clé, instruction ou contenu. |
|  **Sauvegarde automatique** | Chaque ajout ou modification est enregistré instantanément dans MongoDB. |
|  **Export du dataset** | Générer un fichier prêt à l'emploi dans le format sélectionné. |
|  **Prévisualisation avant export** | Vérifier visuellement le rendu final du dataset avant de le télécharger. |
| ⬇ **Téléchargement du dataset** | Télécharger le fichier final (JSON, JSONL...) en un clic. |

---

## 📤 Formats d'export

L'application prend en charge nativement plusieurs formats standards utilisés dans l'écosystème du fine-tuning :

| Format | Usage typique |
|---|---|
| **Alpaca** | Format classique `instruction / input / output`, très répandu pour le fine-tuning instruction-based. |
| **ShareGPT** | Format conversationnel multi-tours, largement utilisé par les outils de fine-tuning communautaires. |
| **OpenAI Messages** | Format `messages[]` compatible avec l'API Chat Completions d'OpenAI et de nombreux frameworks. |
| **Instruction / Output** | Format simplifié à deux champs, sans contexte additionnel. |
| **JSON** | Export générique structuré, adapté aux traitements personnalisés. |
| **JSONL** | Une entrée JSON par ligne — le standard pour l'entraînement à grande échelle. |

> 🧩 **Extensibilité** : le système d'export est conçu autour d'un pattern de type *Strategy/Factory*, ce qui permet d'ajouter facilement un nouveau format en créant simplement une nouvelle classe d'export sans modifier le cœur de l'application (voir [`exports/`](#️-architecture)).

---

## 🧱 Structure d'un exemple

Selon le format d'export choisi, un exemple d'entraînement peut suivre différentes structures.

### Format Alpaca / Instruction-Output

```json
{
  "instruction": "Traduis la phrase suivante en anglais.",
  "input": "Le chat dort sur le canapé.",
  "output": "The cat is sleeping on the couch."
}
```

### Format ShareGPT / OpenAI Messages

```json
{
  "messages": [
    { "role": "system", "content": "Tu es un assistant utile et concis." },
    { "role": "user", "content": "Quelle est la capitale de la France ?" },
    { "role": "assistant", "content": "La capitale de la France est Paris." }
  ]
}
```

### Structure libre

L'application reste flexible : chaque format peut définir ses propres champs, tant que la structure finale reste compatible avec les exigences de sortie du format sélectionné.

---

## 🛠️ Technologies

| Catégorie | Technologie |
|---|---|
| Langage backend | **PHP 8+** |
| Base de données | **MongoDB** |
| Driver | **MongoDB PHP Driver** (extension `mongodb`) |
| Gestion des dépendances | **Composer** |
| Structure des vues | **HTML5** |
| Style | **CSS3**, **Bootstrap** |
| Interactivité | **JavaScript**, **AJAX** |

---

## 🏗️ Architecture

Le projet suit une architecture **MVC (Modèle-Vue-Contrôleur)** claire et modulaire, facilitant la maintenance et l'extension du code.

```
dataset-builder/
├── controllers/        # Logique métier : gestion des requêtes utilisateur
├── models/              # Interaction avec MongoDB (projets, exemples)
├── views/                 # Templates HTML/PHP affichés à l'utilisateur
├── routes/               # Définition des routes de l'application
├── config/               # Configuration (connexion MongoDB, variables d'env)
├── exports/              # Classes d'export (Alpaca, ShareGPT, OpenAI, JSON, JSONL...)
├── public/                # Point d'entrée web (index.php), assets statiques
└── storage/               # Fichiers exportés, logs, fichiers temporaires
```

### Rôle de chaque dossier

- **`controllers/`** : reçoit les requêtes HTTP, orchestre les appels aux modèles et retourne une vue ou une réponse JSON.
- **`models/`** : encapsule toute la logique d'accès aux données (CRUD MongoDB).
- **`views/`** : contient les fichiers d'affichage (formulaires, listes, prévisualisation).
- **`routes/`** : centralise la définition des endpoints de l'application.
- **`config/`** : contient les paramètres de connexion à MongoDB et les constantes globales.
- **`exports/`** : contient une classe par format d'export, chacune implémentant une interface commune (`ExporterInterface`).
- **`public/`** : dossier exposé publiquement au serveur web (front controller, CSS, JS).
- **`storage/`** : espace de stockage local pour les fichiers générés lors de l'export.

---

## 🗄️ Base de données

L'application utilise **MongoDB** pour stocker les projets et les exemples d'entraînement sous forme de documents JSON/BSON, ce qui correspond naturellement à la structure flexible des données de fine-tuning.

### Exemple de document stocké

```json
{
  "_id": "665f1c2e8a1b2c3d4e5f6789",
  "project_id": "664e0a1b8a1b2c3d4e5f1234",
  "format": "alpaca",
  "content": {
    "instruction": "Résume le texte suivant en une phrase.",
    "input": "L'intelligence artificielle transforme de nombreux secteurs...",
    "output": "L'IA révolutionne de nombreux domaines d'activité."
  },
  "tags": ["résumé", "français"],
  "created_at": "2026-07-15T10:32:00Z",
  "updated_at": "2026-07-15T10:32:00Z"
}
```

### Collections principales

| Collection | Description |
|---|---|
| `projects` | Contient les métadonnées de chaque projet de dataset (nom, format cible, description). |
| `examples` | Contient les exemples d'entraînement, liés à un projet via `project_id`. |

---

## 🔌 API interne

L'application expose un ensemble de routes internes utilisées par les vues et les appels AJAX du frontend.

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/examples/create` | Crée un nouvel exemple d'entraînement. |
| `PUT` / `POST` | `/examples/update/{id}` | Modifie un exemple existant. |
| `DELETE` / `POST` | `/examples/delete/{id}` | Supprime un exemple. |
| `GET` | `/examples/list` | Récupère la liste des exemples d'un projet. |
| `GET` | `/examples/search?q=...` | Recherche des exemples selon un mot-clé. |
| `GET` | `/export/{format}` | Génère et télécharge le dataset dans le format demandé. |
| `GET` | `/export/preview/{format}` | Retourne un aperçu du dataset avant export final. |

> ℹ️ Les routes utilisent des réponses JSON pour permettre les interactions AJAX depuis le frontend, sans rechargement de page.

---

## ⚙️ Installation

### 1. Prérequis

- **PHP 8.0** ou supérieur
- **Composer**
- **MongoDB** (serveur local ou instance distante, ex. MongoDB Atlas)
- Extension PHP `mongodb` activée

### 2. Installer PHP

```bash
# Sur Ubuntu/Debian
sudo apt update
sudo apt install php php-cli php-mbstring unzip

# Vérifier la version installée
php -v
```

### 3. Installer Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

### 4. Installer MongoDB

```bash
# Sur Ubuntu/Debian
sudo apt install -y mongodb-org
sudo systemctl start mongod
sudo systemctl enable mongod
```

### 5. Installer l'extension MongoDB PHP Driver

```bash
sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee -a /etc/php/8.0/cli/php.ini
```

### 6. Cloner le projet et installer les dépendances

```bash
git clone https://github.com/Gbelsalvador/Dataset-Builder-for-AI-Fine-Tuning.git
cd dataset-builder
composer install
```

### 7. Configuration

Créez un fichier `.env` à la racine du projet (ou modifiez `config/config.php`) :

```env
MONGO_URI=mongodb://127.0.0.1:27017
MONGO_DB_NAME=dataset_builder
APP_ENV=local
APP_PORT=8000
```

### 8. Lancer le serveur

```bash
php -S localhost:8000 -t public
```

L'application est alors accessible sur : [http://localhost:8000](http://localhost:8000)

---

## 📦 Export du dataset

Une fois les exemples ajoutés, l'utilisateur peut :

1. **Prévisualiser** le rendu final du dataset dans le format choisi,
2. **Sélectionner** le format d'export (Alpaca, ShareGPT, OpenAI Messages, JSON, JSONL...),
3. **Télécharger** directement un fichier **prêt à l'emploi**, sans aucune transformation supplémentaire.

Le fichier généré peut être utilisé immédiatement comme jeu de données d'entraînement dans des frameworks de fine-tuning tels que **Axolotl**, **LLaMA-Factory**, **Unsloth**, **Hugging Face `transformers`/`trl`**, etc.

```bash
# Exemple de fichier exporté : dataset.jsonl
{"instruction": "...", "input": "...", "output": "..."}
{"instruction": "...", "input": "...", "output": "..."}
{"instruction": "...", "input": "...", "output": "..."}
```

---

## 🎯 Objectif du projet

L'objectif de **Dataset Builder for AI Fine-Tuning** est de simplifier et d'accélérer la création de jeux de données destinés au fine-tuning de modèles d'intelligence artificielle.

Plutôt que de rédiger manuellement des fichiers JSON/JSONL ou de jongler avec des scripts, l'utilisateur dispose d'une interface claire lui permettant de se concentrer sur l'essentiel : **la qualité et la pertinence du contenu des exemples**, tout en garantissant une sortie parfaitement conforme aux standards attendus par les principaux frameworks de fine-tuning.

---

## 🚀 Pistes d'évolution (Bonus)

Le projet est conçu pour évoluer. Voici quelques pistes d'amélioration envisageables :

-  **Authentification** des utilisateurs
-  **Gestion des utilisateurs** et des rôles (admin, contributeur...)
-  **Projets multiples** par utilisateur
-  **Versioning des datasets** (historique des versions exportées)
-  **Import d'un dataset existant** (JSON, JSONL, CSV) pour l'enrichir
-  **Génération automatique de variantes** d'exemples (paraphrase, augmentation de données)
-  **Validation des données** (contrôle de format, détection de doublons)
-  **Statistiques** sur les projets (nombre d'exemples, répartition par tags, etc.)
-  **Historique des modifications** de chaque exemple
-  **API REST** publique et documentée (OpenAPI/Swagger)
-  **Dockerisation** complète de l'application (PHP + MongoDB)
-  **Déploiement** simplifié (CI/CD, hébergement cloud)
-  **Génération de datasets via IA**, en s'appuyant sur un LLM pour proposer automatiquement de nouveaux exemples

---

## 🤝 Contribuer

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Commitez vos modifications (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Poussez la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

---

## 📄 Licence

Ce projet est distribué sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

---

<p align="center">Fait avec ❤️ pour la communauté IA — construisez vos propres datasets, entraînez vos propres modèles.</p>