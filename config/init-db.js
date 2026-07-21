/**
 * init-db.js
 * ---------------------------------------------------------------
 * Script d'initialisation de la base de données MongoDB pour le
 * projet "Dataset Builder for AI Fine-Tuning".
 *
 * Il crée :
 *   - la collection "projects"  (projets de dataset)
 *   - la collection "examples"  (exemples d'entraînement)
 * avec validation de schéma (JSON Schema) et index de performance.
 *
 * Usage :
 *   mongosh "mongodb://127.0.0.1:27017/builder-data" database/init-db.js
 *
 * Ou en environnement Atlas :
 *   mongosh "mongodb+srv://<user>:<password>@<cluster>/builder-data" database/init-db.js
 * ---------------------------------------------------------------
 */

const dbName = "builder-data";
const database = db.getSiblingDB(dbName);

// Formats supportés nativement (extensible côté application via exports/)
const SUPPORTED_FORMATS = [
  "alpaca",
  "sharegpt",
  "openai_messages",
  "instruction_output",
  "json",
  "jsonl",
];

// ---------------------------------------------------------------
// Nettoyage optionnel (décommenter pour repartir de zéro)
// ---------------------------------------------------------------
// database.projects.drop();
// database.examples.drop();

// ---------------------------------------------------------------
// Collection : projects
// ---------------------------------------------------------------
database.createCollection("projects", {
  validator: {
    $jsonSchema: {
      bsonType: "object",
      required: ["name", "format", "created_at"],
      properties: {
        name: {
          bsonType: "string",
          minLength: 1,
          description: "Nom du projet — requis",
        },
        description: {
          bsonType: "string",
          description: "Description libre du projet",
        },
        format: {
          enum: SUPPORTED_FORMATS,
          description: "Format d'export cible du projet — requis",
        },
        tags: {
          bsonType: "array",
          items: { bsonType: "string" },
          description: "Étiquettes libres pour organiser les projets",
        },
        examples_count: {
          bsonType: "int",
          minimum: 0,
          description: "Compteur d'exemples, maintenu par l'application",
        },
        created_at: { bsonType: "date" },
        updated_at: { bsonType: "date" },
      },
    },
  },
  validationLevel: "moderate",
  validationAction: "error",
});

database.projects.createIndex({ name: 1 });
database.projects.createIndex({ format: 1 });
database.projects.createIndex({ created_at: -1 });

// ---------------------------------------------------------------
// Collection : examples
// ---------------------------------------------------------------
database.createCollection("examples", {
  validator: {
    $jsonSchema: {
      bsonType: "object",
      required: ["project_id", "format", "content", "created_at"],
      properties: {
        project_id: {
          bsonType: "objectId",
          description: "Référence vers projects._id — requis",
        },
        format: {
          enum: SUPPORTED_FORMATS,
          description: "Format de l'exemple (hérité du projet en général)",
        },
        content: {
          bsonType: "object",
          description:
            "Contenu de l'exemple. Structure libre selon le format : " +
            "{instruction, input, output} ou {messages: [...]}, etc.",
        },
        tags: {
          bsonType: "array",
          items: { bsonType: "string" },
        },
        created_at: { bsonType: "date" },
        updated_at: { bsonType: "date" },
      },
    },
  },
  validationLevel: "moderate",
  validationAction: "error",
});

database.examples.createIndex({ project_id: 1 });
database.examples.createIndex({ format: 1 });
database.examples.createIndex({ project_id: 1, created_at: -1 });

// Index texte pour la fonctionnalité de recherche d'exemples
database.examples.createIndex(
  {
    "content.instruction": "text",
    "content.input": "text",
    "content.output": "text",
    "content.messages.content": "text",
    tags: "text",
  },
  { name: "examples_search_index", default_language: "french" }
);

// ---------------------------------------------------------------
// Résumé
// ---------------------------------------------------------------
print("✅ Base de données '" + dbName + "' initialisée avec succès.");
print("   → Collection 'projects' créée avec validation de schéma");
print("   → Collection 'examples' créée avec validation de schéma");
print("   → Index de recherche et de performance mis en place");