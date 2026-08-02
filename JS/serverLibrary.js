(function (global) {
  "use strict";

  const ENDPOINT_BASE = "PHP/";
  const ENDPOINTS = {
    list: "auswahlliste.php",
    load: "dateiladen.php",
    publish: "server_publish_score.php",
    update: "server_update_score.php",
    delete: "server_delete_score.php"
  };

  function encodeForm(payload) {
    const formData = new FormData();
    Object.keys(payload || {}).forEach(function (key) {
      formData.append(key, payload[key]);
    });
    return formData;
  }

  function getFreshEndpointUrl(endpoint) {
    const separator = String(endpoint || "").indexOf("?") === -1 ? "?" : "&";
    return ENDPOINT_BASE + endpoint + separator + "_=" + Date.now();
  }

  async function postEndpoint(endpoint, payload) {
    const response = await fetch(getFreshEndpointUrl(endpoint), {
      method: "POST",
      body: encodeForm(payload),
      cache: "no-store",
      headers: {
        "Cache-Control": "no-cache"
      }
    });

    if (!response.ok) {
      throw new Error("Serveranfrage fehlgeschlagen: " + response.status);
    }

    return response.text();
  }

  async function postJsonEndpoint(endpoint, payload) {
    const response = await fetch(getFreshEndpointUrl(endpoint), {
      method: "POST",
      body: encodeForm(payload),
      cache: "no-store",
      headers: {
        "Cache-Control": "no-cache"
      }
    });
    const responseText = await response.text();
    let data = null;

    try {
      data = responseText ? JSON.parse(responseText) : null;
    } catch (error) {
      throw new Error("Serverantwort konnte nicht gelesen werden.");
    }

    if (!response.ok || !data || data.success === false) {
      throw new Error(data && data.message ? data.message : "Serveranfrage fehlgeschlagen: " + response.status);
    }

    return data;
  }

  function stripScoreExtension(fileName) {
    return String(fileName || "").replace(/\.(bbs|txt)$/i, "");
  }

  function getScoreFormat(fileName) {
    return /\.txt$/i.test(String(fileName || "")) ? "txt" : "bbs";
  }

  function listScores() {
    return postEndpoint(ENDPOINTS.list).then(function (html) {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      return Array.prototype.slice.call(doc.querySelectorAll("option"))
        .map(function (option) {
          const fileName = option.textContent.trim();
          return {
            fileName: fileName,
            serverUpdatedAt: option.getAttribute("data-modified") || "",
            serverModifiedTs: Number(option.getAttribute("data-modified-ts")) || 0
          };
        })
        .filter(function (score) {
          return score.fileName && score.fileName !== "Datei laden:";
        })
        .map(function (score) {
          return {
            fileName: score.fileName,
            title: stripScoreExtension(score.fileName),
            serverPath: score.fileName,
            format: getScoreFormat(score.fileName),
            serverUpdatedAt: score.serverUpdatedAt,
            serverModifiedTs: score.serverModifiedTs
          };
        });
    });
  }

  function getPublishBaseName(score) {
    return stripScoreExtension(score.fileName || score.serverPath || score.title || score.name || "Unbenannt");
  }

  function getPublishContent(score) {
    if (typeof score.content === "string") {
      return score.content;
    }
    if (typeof score.data === "string") {
      return score.data;
    }
    if (score.data) {
      return JSON.stringify(score.data);
    }
    return "";
  }

  async function publishScore(score) {
    if (!score || typeof score !== "object") {
      throw new Error("publishScore erwartet ein Notenblatt-Objekt.");
    }

    const baseName = getPublishBaseName(score);
    return postJsonEndpoint(ENDPOINTS.publish, {
      title: baseName,
      content: getPublishContent(score)
    });
  }

  async function updatePublishedScore(score) {
    if (!score || typeof score !== "object") {
      throw new Error("updatePublishedScore erwartet ein Notenblatt-Objekt.");
    }

    const baseName = getPublishBaseName(score);
    if (!score.publishToken) {
      throw new Error("Diese lokale Kopie hat kein Publish-Token und kann die Serverdatei nicht aktualisieren.");
    }

    return postJsonEndpoint(ENDPOINTS.update, {
      serverPath: score.serverPath || baseName + ".bbs",
      content: getPublishContent(score),
      publishToken: score.publishToken
    });
  }

  function importScore(serverPath) {
    const fileName = String(serverPath || "").trim();
    if (!fileName) {
      return Promise.reject(new Error("importScore erwartet einen Serverpfad."));
    }

    return postEndpoint(ENDPOINTS.load, { b: fileName }).then(function (content) {
      return {
        title: stripScoreExtension(fileName),
        fileName: fileName,
        serverPath: fileName,
        format: getScoreFormat(fileName),
        content: content,
        serverUpdatedAt: "",
        serverModifiedTs: 0
      };
    });
  }

  async function deletePublishedScore(score) {
    if (!score || typeof score !== "object") {
      throw new Error("deletePublishedScore erwartet ein Notenblatt-Objekt.");
    }

    if (!score.serverPath) {
      throw new Error("Diese lokale Kopie ist keiner Serverdatei zugeordnet.");
    }

    if (!score.publishToken) {
      throw new Error("Diese lokale Kopie hat kein Publish-Token und kann die Serverdatei nicht löschen.");
    }

    return postJsonEndpoint(ENDPOINTS.delete, {
      serverPath: score.serverPath,
      publishToken: score.publishToken
    });
  }

  global.serverLibrary = {
    listScores: listScores,
    publishScore: publishScore,
    updatePublishedScore: updatePublishedScore,
    importScore: importScore,
    deletePublishedScore: deletePublishedScore
  };
})(window);
