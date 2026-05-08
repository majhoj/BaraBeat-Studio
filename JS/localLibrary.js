(function (global) {
  "use strict";

  const DB_NAME = "BaraBeatLocalLibrary";
  const DB_VERSION = 1;
  const ROOT_FOLDER_ID = "root";
  const DEFAULT_SYNC_STATE = "local-only";

  let dbPromise = null;

  function nowIso() {
    return new Date().toISOString();
  }

  function normalizeParentId(parentId) {
    return parentId || ROOT_FOLDER_ID;
  }

  function normalizeName(value, fallback) {
    const normalized = String(value || "").trim();
    return normalized || fallback;
  }

  function normalizeSortName(value) {
    return String(value || "")
      .trim()
      .toLocaleLowerCase("de-DE");
  }

  function createId(prefix) {
    if (global.crypto && typeof global.crypto.randomUUID === "function") {
      return prefix + "_" + global.crypto.randomUUID();
    }
    return prefix + "_" + Date.now().toString(36) + "_" + Math.random().toString(36).slice(2);
  }

  function promisifyRequest(request) {
    return new Promise(function (resolve, reject) {
      request.onsuccess = function () {
        resolve(request.result);
      };
      request.onerror = function () {
        reject(request.error);
      };
    });
  }

  function openDb() {
    if (dbPromise) {
      return dbPromise;
    }

    dbPromise = new Promise(function (resolve, reject) {
      if (!global.indexedDB) {
        reject(new Error("IndexedDB wird von diesem Browser nicht unterstützt."));
        return;
      }

      const request = global.indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = function (event) {
        const db = event.target.result;

        if (!db.objectStoreNames.contains("folders")) {
          const folders = db.createObjectStore("folders", { keyPath: "id" });
          folders.createIndex("parentId", "parentId", { unique: false });
          folders.createIndex("path", "path", { unique: true });
          folders.createIndex("sortName", "sortName", { unique: false });
          folders.createIndex("updatedAt", "updatedAt", { unique: false });
        }

        if (!db.objectStoreNames.contains("scores")) {
          const scores = db.createObjectStore("scores", { keyPath: "id" });
          scores.createIndex("folderId", "folderId", { unique: false });
          scores.createIndex("title", "title", { unique: false });
          scores.createIndex("sortName", "sortName", { unique: false });
          scores.createIndex("isPublished", "isPublished", { unique: false });
          scores.createIndex("syncState", "syncState", { unique: false });
          scores.createIndex("serverPath", "serverPath", { unique: false });
          scores.createIndex("updatedAt", "updatedAt", { unique: false });
        }
      };

      request.onsuccess = function () {
        const db = request.result;
        db.onversionchange = function () {
          db.close();
        };
        resolve(db);
      };

      request.onerror = function () {
        reject(request.error);
      };
    });

    return dbPromise;
  }

  async function withStore(storeNames, mode, callback) {
    const db = await openDb();
    const names = Array.isArray(storeNames) ? storeNames : [storeNames];

    return new Promise(function (resolve, reject) {
      const transaction = db.transaction(names, mode);
      const stores = names.reduce(function (result, name) {
        result[name] = transaction.objectStore(name);
        return result;
      }, {});
      let callbackResult;

      transaction.oncomplete = function () {
        resolve(callbackResult);
      };
      transaction.onerror = function () {
        reject(transaction.error);
      };
      transaction.onabort = function () {
        reject(transaction.error || new Error("IndexedDB-Transaktion wurde abgebrochen."));
      };

      Promise.resolve()
        .then(function () {
          return callback(stores);
        })
        .then(function (result) {
          callbackResult = result;
        })
        .catch(function (error) {
          transaction.abort();
          reject(error);
        });
    });
  }

  async function getByKey(storeName, id) {
    const db = await openDb();
    const transaction = db.transaction(storeName, "readonly");
    return promisifyRequest(transaction.objectStore(storeName).get(id));
  }

  function getAllFromIndex(store, indexName, value) {
    if (typeof value === "undefined") {
      return promisifyRequest(store.getAll());
    }
    return promisifyRequest(store.index(indexName).getAll(value));
  }

  function sortBySortName(items) {
    return items.sort(function (first, second) {
      return String(first.sortName || first.name || first.title || "").localeCompare(
        String(second.sortName || second.name || second.title || ""),
        "de-DE",
        { sensitivity: "base" }
      );
    });
  }

  async function buildFolderPath(parentId, name) {
    const normalizedParentId = normalizeParentId(parentId);
    if (normalizedParentId === ROOT_FOLDER_ID) {
      return "/" + name;
    }

    const parentFolder = await getByKey("folders", normalizedParentId);
    if (!parentFolder) {
      throw new Error("Der Zielordner wurde nicht gefunden: " + normalizedParentId);
    }
    return parentFolder.path + "/" + name;
  }

  async function createFolder(name, parentId) {
    const folderName = normalizeName(name, "Neuer Ordner");
    const createdAt = nowIso();
    const normalizedParentId = normalizeParentId(parentId);
    const folder = {
      id: createId("folder"),
      parentId: normalizedParentId,
      name: folderName,
      path: await buildFolderPath(normalizedParentId, folderName),
      sortName: normalizeSortName(folderName),
      createdAt: createdAt,
      updatedAt: createdAt
    };

    return withStore("folders", "readwrite", async function (stores) {
      await promisifyRequest(stores.folders.add(folder));
      return folder;
    });
  }

  async function listFolders(parentId) {
    const normalizedParentId = normalizeParentId(parentId);
    const db = await openDb();
    const transaction = db.transaction("folders", "readonly");
    const folders = await getAllFromIndex(transaction.objectStore("folders"), "parentId", normalizedParentId);
    return sortBySortName(folders);
  }

  async function renameFolder(folderId, name) {
    const normalizedFolderId = normalizeParentId(folderId);
    if (normalizedFolderId === ROOT_FOLDER_ID) {
      throw new Error("Der lokale Hauptordner kann nicht umbenannt werden.");
    }

    const folder = await getByKey("folders", normalizedFolderId);
    if (!folder) {
      throw new Error("Der Ordner wurde nicht gefunden: " + normalizedFolderId);
    }

    const folderName = normalizeName(name, folder.name || "Neuer Ordner");
    const renamedFolder = Object.assign({}, folder, {
      name: folderName,
      path: await buildFolderPath(folder.parentId, folderName),
      sortName: normalizeSortName(folderName),
      updatedAt: nowIso()
    });

    return withStore("folders", "readwrite", async function (stores) {
      await promisifyRequest(stores.folders.put(renamedFolder));
      return renamedFolder;
    });
  }

  async function saveScore(score) {
    if (!score || typeof score !== "object") {
      throw new Error("saveScore erwartet ein Notenblatt-Objekt.");
    }

    const existingScore = score.id ? await getByKey("scores", score.id) : null;
    const timestamp = nowIso();
    const title = normalizeName(score.title || score.name || (existingScore && existingScore.title), "Unbenannt");
    const isPublished = Boolean(
      typeof score.isPublished !== "undefined"
        ? score.isPublished
        : existingScore && existingScore.isPublished
    );
    const syncState = score.syncState ||
      (existingScore && existingScore.isPublished ? "modified-local" : DEFAULT_SYNC_STATE);

    const savedScore = Object.assign({}, existingScore || {}, score, {
      id: score.id || createId("score"),
      folderId: normalizeParentId(score.folderId || (existingScore && existingScore.folderId)),
      title: title,
      sortName: normalizeSortName(title),
      isPublished: isPublished,
      syncState: syncState,
      createdAt: existingScore && existingScore.createdAt ? existingScore.createdAt : timestamp,
      updatedAt: timestamp,
      localUpdatedAt: timestamp
    });

    return withStore("scores", "readwrite", async function (stores) {
      await promisifyRequest(stores.scores.put(savedScore));
      return savedScore;
    });
  }

  function getScore(id) {
    return getByKey("scores", id);
  }

  function getFolder(id) {
    if (normalizeParentId(id) === ROOT_FOLDER_ID) {
      return Promise.resolve({
        id: ROOT_FOLDER_ID,
        parentId: null,
        name: "Lokal",
        path: "/",
        sortName: "",
        createdAt: null,
        updatedAt: null
      });
    }
    return getByKey("folders", id);
  }

  async function listScores(folderId) {
    const db = await openDb();
    const transaction = db.transaction("scores", "readonly");
    const scores = await getAllFromIndex(
      transaction.objectStore("scores"),
      "folderId",
      typeof folderId === "undefined" ? undefined : normalizeParentId(folderId)
    );
    return sortBySortName(scores);
  }

  async function findScoreByServerPath(serverPath) {
    const normalizedServerPath = String(serverPath || "").trim();
    if (!normalizedServerPath) {
      return null;
    }

    const scores = await listScores();
    return scores.find(function (score) {
      return score.serverPath === normalizedServerPath;
    }) || null;
  }

  async function moveScore(scoreId, folderId) {
    const score = await getByKey("scores", scoreId);
    if (!score) {
      throw new Error("Das Notenblatt wurde nicht gefunden: " + scoreId);
    }
    return saveScore(Object.assign({}, score, { folderId: normalizeParentId(folderId) }));
  }

  async function deleteScore(scoreId) {
    return withStore("scores", "readwrite", async function (stores) {
      await promisifyRequest(stores.scores.delete(scoreId));
      return true;
    });
  }

  async function deleteFolder(folderId) {
    const normalizedFolderId = normalizeParentId(folderId);
    if (normalizedFolderId === ROOT_FOLDER_ID) {
      throw new Error("Der lokale Hauptordner kann nicht gelöscht werden.");
    }

    const folder = await getByKey("folders", normalizedFolderId);
    if (!folder) {
      throw new Error("Der Ordner wurde nicht gefunden: " + normalizedFolderId);
    }

    const childFolders = await listFolders(normalizedFolderId);
    if (childFolders.length > 0) {
      throw new Error("Der Ordner enthält Unterordner und kann nicht gelöscht werden.");
    }

    const childScores = await listScores(normalizedFolderId);
    if (childScores.length > 0) {
      throw new Error("Der Ordner enthält Notenblätter und kann nicht gelöscht werden.");
    }

    return withStore("folders", "readwrite", async function (stores) {
      await promisifyRequest(stores.folders.delete(normalizedFolderId));
      return true;
    });
  }

  async function markPublished(scoreId, serverPath, publishToken) {
    const score = await getByKey("scores", scoreId);
    if (!score) {
      throw new Error("Das Notenblatt wurde nicht gefunden: " + scoreId);
    }

    const timestamp = nowIso();
    const publishedScore = Object.assign({}, score, {
      isPublished: true,
      serverPath: normalizeName(serverPath, score.serverPath || score.title + ".txt"),
      publishToken: publishToken || score.publishToken || null,
      syncState: "published",
      publishedAt: score.publishedAt || timestamp,
      serverVersion: timestamp,
      updatedAt: timestamp
    });

    return withStore("scores", "readwrite", async function (stores) {
      await promisifyRequest(stores.scores.put(publishedScore));
      return publishedScore;
    });
  }

  async function unmarkPublished(scoreId) {
    const score = await getByKey("scores", scoreId);
    if (!score) {
      throw new Error("Das Notenblatt wurde nicht gefunden: " + scoreId);
    }

    const timestamp = nowIso();
    const localScore = Object.assign({}, score, {
      isPublished: false,
      serverPath: null,
      publishToken: null,
      syncState: DEFAULT_SYNC_STATE,
      serverVersion: null,
      updatedAt: timestamp
    });

    return withStore("scores", "readwrite", async function (stores) {
      await promisifyRequest(stores.scores.put(localScore));
      return localScore;
    });
  }

  async function listLocalOnlyScores() {
    const scores = await listScores();
    return scores.filter(function (score) {
      return !score.isPublished || score.syncState === DEFAULT_SYNC_STATE;
    });
  }

  async function listPublishedScores() {
    const scores = await listScores();
    return scores.filter(function (score) {
      return score.isPublished;
    });
  }

  global.localLibrary = {
    dbName: DB_NAME,
    rootFolderId: ROOT_FOLDER_ID,
    openDb: openDb,
    createFolder: createFolder,
    listFolders: listFolders,
    getFolder: getFolder,
    renameFolder: renameFolder,
    saveScore: saveScore,
    getScore: getScore,
    listScores: listScores,
    findScoreByServerPath: findScoreByServerPath,
    moveScore: moveScore,
    deleteScore: deleteScore,
    deleteFolder: deleteFolder,
    markPublished: markPublished,
    unmarkPublished: unmarkPublished,
    listLocalOnlyScores: listLocalOnlyScores,
    listPublishedScores: listPublishedScores
  };
})(window);
