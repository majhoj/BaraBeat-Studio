function createMenuChooser(s, x, y, config) {
  let chooserGruppe = s.g();
  chooserGruppe.addClass("chooser-node");
  chooserGruppe.addClass(config.chooserClass);
  let menuGruppe = s.g().attr({ display: "none" });

  let chooserText = s.text(0, 0, config.startText).attr({
    class: config.labelClass,
    fill: config.startFill,
    "font-size": 16,
    "font-family": "sans-serif",
    cursor: "pointer",
  });

  let zeilenHoehe = 22;
  let menuBreite = config.menuWidth;
  let menuHoehe = config.options.length * zeilenHoehe + 10;

  let menuBg = s.rect(-5, 5, menuBreite, menuHoehe, 4, 4).attr({
    fill: "#f7f7f7",
    fillOpacity: 0.92,
    stroke: "#999",
    "stroke-width": 1,
  });

  menuGruppe.add(menuBg);

  config.options.forEach(function (name, index) {
    let eintrag = s.text(5, 22 + index * zeilenHoehe, name).attr({
      fill: "#333",
      "font-size": 14,
      "font-family": "sans-serif",
      cursor: "pointer",
    });
    menuGruppe.add(eintrag);
  });

  chooserGruppe.add(chooserText, menuGruppe);
  chooserGruppe.transform("translate(" + x + "," + y + ")");

  bindChooserInteraction(chooserGruppe, chooserText, menuGruppe, config.onSelect);

  return chooserGruppe;
}

function requestChooserLabel(defaultName, promptText) {
  return new Promise(function (resolve) {
    let overlay = document.createElement("div");
    overlay.className = "chooser-dialog-backdrop";

    let dialog = document.createElement("div");
    dialog.className = "chooser-dialog";

    let message = document.createElement("p");
    message.className = "chooser-dialog-text";
    message.textContent = promptText;

    let input = document.createElement("input");
    input.type = "text";
    input.className = "chooser-dialog-input";
    input.value = defaultName;

    let actions = document.createElement("div");
    actions.className = "chooser-dialog-actions";

    let cancelButton = document.createElement("button");
    cancelButton.type = "button";
    cancelButton.textContent = "Abbrechen";

    let okButton = document.createElement("button");
    okButton.type = "button";
    okButton.textContent = "OK";

    function closeDialog(result) {
      overlay.remove();
      resolve(result);
    }

    cancelButton.addEventListener("click", function () {
      closeDialog(null);
    });

    okButton.addEventListener("click", function () {
      let configuredName = input.value.trim();
      closeDialog(configuredName === "" ? defaultName : configuredName);
    });

    input.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        okButton.click();
      }
      if (event.key === "Escape") {
        event.preventDefault();
        cancelButton.click();
      }
    });

    overlay.addEventListener("click", function (event) {
      if (event.target === overlay) {
        cancelButton.click();
      }
    });

    actions.append(cancelButton, okButton);
    dialog.append(message, input, actions);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    window.requestAnimationFrame(function () {
      input.focus();
      let endPosition = input.value.length;
      input.setSelectionRange(endPosition, endPosition);
    });
  });
}

function getChooserLabelSeed(name, chooserText) {
  let currentText = chooserText && typeof chooserText.attr === "function"
    ? String(chooserText.attr("text") || "").trim()
    : "";

  if (!currentText || currentText === "Funktion") {
    return name;
  }

  if (currentText === name || currentText.indexOf(name) === 0 || currentText.indexOf(name + " ") !== -1) {
    return currentText;
  }

  return name;
}

function getChooserPosition(chooserGruppe) {
  if (chooserGruppe && chooserGruppe.node && chooserGruppe.node.transform && chooserGruppe.node.transform.baseVal) {
    let consolidatedTransform = chooserGruppe.node.transform.baseVal.consolidate();
    let matrix = consolidatedTransform && consolidatedTransform.matrix ? consolidatedTransform.matrix : null;
    if (matrix) {
      return {
        x: matrix.e,
        y: matrix.f,
      };
    }
  }
  let transformState = typeof chooserGruppe.transform === "function" ? chooserGruppe.transform() : null;
  let localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
  if (localMatrix) {
    return {
      x: localMatrix.e,
      y: localMatrix.f,
    };
  }
  let bbox = chooserGruppe.getBBox();
  return {
    x: bbox.x,
    y: bbox.y,
  };
}

function setChooserText(chooserGruppe, textValue, fillValue = "#333") {
  let textNode = chooserGruppe.select("text");
  if (!textNode) {
    return;
  }
  textNode.attr({
    text: textValue,
    fill: fillValue,
  });
}

function bringChooserToFront(chooserGruppe, menuGruppe) {
  if (typeof s !== "undefined" && s && typeof s.append === "function") {
    s.append(chooserGruppe);
  }
  if (typeof chooserGruppe.toFront === "function") {
    chooserGruppe.toFront();
  }
  if (menuGruppe && typeof menuGruppe.toFront === "function") {
    menuGruppe.toFront();
  }
}

function isChooserMenuVisible(menuGruppe) {
  return menuGruppe.attr("display") !== "none";
}

function setChooserMenuVisible(menuGruppe, visible) {
  menuGruppe.attr({ display: visible ? "block" : "none" });
}

function isEventInsideChooserMenu(event, menuGruppe) {
  if (!event || !event.target || !menuGruppe || !menuGruppe.node) {
    return false;
  }
  return menuGruppe.node === event.target || menuGruppe.node.contains(event.target);
}

function findLinkedFunctionChooser(instrumentChooserGruppe) {
  let instrumentPosition = getChooserPosition(instrumentChooserGruppe);
  let bestFunctionChooser = null;
  let bestDistance = Infinity;

  s.selectAll(".function-chooser").forEach(function (functionChooser) {
    if (functionChooser === instrumentChooserGruppe) {
      return;
    }
    let functionPosition = getChooserPosition(functionChooser);
    let deltaY = Math.abs(functionPosition.y - instrumentPosition.y);
    let deltaX = functionPosition.x - instrumentPosition.x;
    if (deltaY > 25 || deltaX < 0) {
      return;
    }
    if (deltaX < bestDistance) {
      bestDistance = deltaX;
      bestFunctionChooser = functionChooser;
    }
  });

  return bestFunctionChooser;
}

function createInstrumentChooser(s, x, y, startText = "Instrument", startFill = "gray") {
  return createMenuChooser(s, x, y, {
    chooserClass: "instrument-chooser",
    labelClass: "instrument-label",
    startText: startText,
    startFill: startFill,
    menuWidth: 120,
    options: [
      "Djembe",
      "Djembe 1",
      "Djembe 2",
      "Djembe 3",
      "Bässe",
      "Kenkeni",
      "Sangban",
      "Dununba",
      "Dreierbass",
      "Leer",
    ],
    onSelect: function (name, chooserGruppe) {
      if (name === "Leer") {
        let linkedFunctionChooser = findLinkedFunctionChooser(chooserGruppe);
        if (linkedFunctionChooser) {
          setChooserText(linkedFunctionChooser, "Leer");
        }
      }
      return name;
    },
  });
}

function createFunctionChooser(s, x, y, startText = "Funktion", startFill = "gray") {
  return createMenuChooser(s, x, y, {
    chooserClass: "function-chooser",
    labelClass: "function-label",
    startText: startText,
    startFill: startFill,
    menuWidth: 140,
    options: ["Call", "Intro", "Echauffement", "Begleitpattern", "Solo", "Outro", "Leer"],
    onSelect: function (name, chooserGruppe, chooserText) {
      if (name !== "Solo" && name !== "Begleitpattern") {
        return name;
      }
      let promptText =
        'Bezeichnung für "' +
        name +
        '" anpassen.\nZum Beispiel: "Solo 1", "1. Solo", "Begleitpattern 2".';
      return requestChooserLabel(getChooserLabelSeed(name, chooserText), promptText);
    },
  });
}

/**
 * Klick- und Drag-Verhalten für Menü-Chooser-Gruppen (neu oder nach DOM-Klon).
 * Chooser verwenden bewusst kein separates dragEnd wie stop_m,
 * damit geladenes und neu erzeugtes Verhalten identisch bleibt.
 */
function bindChooserInteraction(chooserGruppe, chooserText, menuGruppe, onSelect) {
  let dragSchwelle = 5;
  let toggleSuppressDuration = 300;
  let chooserDragRebindTimer = null;
  let chooserDragReleaseHandler = null;
  let nativeChooserDragMoveHandler = null;
  let nativeChooserDragEndHandler = null;

  function ungroupSelectionBeforeChooserAction() {
    if (typeof selections === "undefined" || !selections || !selections.node || !chooserGruppe.node) {
      return;
    }
    if (selections.node.contains(chooserGruppe.node)) {
      UnGroup();
      chooserGruppe.data("warDrag", false);
      chooserGruppe.data("chooserDragMoved", false);
      chooserGruppe.data("suppressChooserDrag", false);
    }
  }

  function stopChooserEvent(event) {
    if (event && typeof event.preventDefault === "function") {
      event.preventDefault();
    }
    if (event && typeof event.stopPropagation === "function") {
      event.stopPropagation();
    }
  }

  function stopMenuDragEvent(event) {
    chooserGruppe.data("suppressChooserDrag", true);
    stopChooserEvent(event);
  }

  function rebindChooserDragAfterMenuAction() {
    chooserGruppe.data("warDrag", false);
    chooserGruppe.data("chooserDragMoved", false);
    chooserGruppe.data("suppressChooserDrag", true);
    chooserGruppe.undrag();

    if (chooserDragRebindTimer) {
      window.clearTimeout(chooserDragRebindTimer);
    }

    chooserDragRebindTimer = window.setTimeout(function () {
      chooserGruppe.data("suppressChooserDrag", false);
      installNativeChooserDrag();
    }, 300);
  }

  function removeChooserDragReleaseFallback() {
    if (!chooserDragReleaseHandler) {
      return;
    }
    window.removeEventListener("mouseup", chooserDragReleaseHandler, true);
    window.removeEventListener("touchend", chooserDragReleaseHandler, true);
    window.removeEventListener("touchcancel", chooserDragReleaseHandler, true);
    chooserDragReleaseHandler = null;
  }

  function rebindChooserDragAfterPointerRelease() {
    chooserGruppe.undrag();

    if (chooserDragRebindTimer) {
      window.clearTimeout(chooserDragRebindTimer);
    }

    chooserDragRebindTimer = window.setTimeout(function () {
      installNativeChooserDrag();
    }, 0);
  }

  function forceChooserDragEnd() {
    let wasDrag = !!chooserGruppe.data("chooserDragMoved");
    removeChooserDragReleaseFallback();
    if (wasDrag) {
      chooserGruppe.data("suppressChooserToggleUntil", Date.now() + toggleSuppressDuration);
    }
    chooserGruppe.data("warDrag", false);
    chooserGruppe.data("chooserDragMoved", false);
    chooserGruppe.data("suppressChooserDrag", false);
    rebindChooserDragAfterPointerRelease();
  }

  function snapChooserToFinalPosition() {
    let rawDx = Number(chooserGruppe.data("currentChooserDragDx"));
    let rawDy = Number(chooserGruppe.data("currentChooserDragDy"));
    let startX = Number(chooserGruppe.data("origChooserX"));
    let startY = Number(chooserGruppe.data("origChooserY"));
    if (!Number.isFinite(rawDx)) {
      rawDx = 0;
    }
    if (!Number.isFinite(rawDy)) {
      rawDy = 0;
    }
    if (!Number.isFinite(startX) || !Number.isFinite(startY)) {
      let chooserPosition = getChooserPosition(chooserGruppe);
      startX = chooserPosition.x;
      startY = chooserPosition.y;
    }

    let snappedDx = typeof Snap !== "undefined" && typeof gridSize !== "undefined"
      ? Snap.snapTo(gridSize, rawDx, 50)
      : rawDx;
    let targetY = startY + rawDy;
    let snappedY = typeof snapToVerticalTargets === "function"
      ? snapToVerticalTargets(targetY, chooserGruppe)
      : targetY;

    chooserGruppe.attr({
      transform: "t" + (startX + snappedDx) + "," + snappedY,
    });
  }

  function finishChooserDrag() {
    if (chooserGruppe.data("chooserDragMoved")) {
      snapChooserToFinalPosition();
    }
    forceChooserDragEnd();
  }

  function installChooserDragReleaseFallback() {
    removeChooserDragReleaseFallback();
    chooserDragReleaseHandler = function () {
      finishChooserDrag();
    };
    window.addEventListener("mouseup", chooserDragReleaseHandler, true);
    window.addEventListener("touchend", chooserDragReleaseHandler, true);
    window.addEventListener("touchcancel", chooserDragReleaseHandler, true);
  }

  function getChooserDragPoint(event) {
    let sourceEvent = event && (event.touches ? event.touches[0] : (event.changedTouches ? event.changedTouches[0] : event));
    if (typeof getSvgPointerPosition === "function") {
      return getSvgPointerPosition(event, sourceEvent && sourceEvent.clientX, sourceEvent && sourceEvent.clientY);
    }
    return {
      x: sourceEvent && typeof sourceEvent.clientX === "number" ? sourceEvent.clientX : 0,
      y: sourceEvent && typeof sourceEvent.clientY === "number" ? sourceEvent.clientY : 0,
    };
  }

  function removeNativeChooserDragListeners() {
    if (nativeChooserDragMoveHandler) {
      window.removeEventListener("mousemove", nativeChooserDragMoveHandler, true);
      window.removeEventListener("touchmove", nativeChooserDragMoveHandler, true);
      nativeChooserDragMoveHandler = null;
    }
    if (nativeChooserDragEndHandler) {
      window.removeEventListener("mouseup", nativeChooserDragEndHandler, true);
      window.removeEventListener("touchend", nativeChooserDragEndHandler, true);
      window.removeEventListener("touchcancel", nativeChooserDragEndHandler, true);
      nativeChooserDragEndHandler = null;
    }
  }

  if (menuGruppe.node) {
    if (menuGruppe.node.__chooserMenuDragStopHandler) {
      menuGruppe.node.removeEventListener("mousedown", menuGruppe.node.__chooserMenuDragStopHandler);
      menuGruppe.node.removeEventListener("touchstart", menuGruppe.node.__chooserMenuDragStopHandler);
    }
    menuGruppe.node.__chooserMenuDragStopHandler = stopMenuDragEvent;
    menuGruppe.node.addEventListener("mousedown", menuGruppe.node.__chooserMenuDragStopHandler);
    menuGruppe.node.addEventListener("touchstart", menuGruppe.node.__chooserMenuDragStopHandler, { passive: false });
  }

  function toggleChooserMenu(event) {
    ungroupSelectionBeforeChooserAction();

    let eventType = event && event.type ? event.type : "";
    let now = Date.now();
    let suppressToggleUntil = chooserGruppe.data("suppressChooserToggleUntil");
    if (suppressToggleUntil && now < suppressToggleUntil) {
      stopChooserEvent(event);
      return;
    }
    if (eventType === "click" && chooserGruppe.data("lastNativeChooserEventAt")) {
      if (now - chooserGruppe.data("lastNativeChooserEventAt") < 450) {
        stopChooserEvent(event);
        return;
      }
    }
    if (eventType === "mouseup" || eventType === "touchend") {
      chooserGruppe.data("lastNativeChooserEventAt", now);
    }

    if (chooserGruppe.data("warDrag")) {
      chooserGruppe.data("warDrag", false);
      stopChooserEvent(event);
      return;
    }
    let sichtbar = isChooserMenuVisible(menuGruppe);
    if (!sichtbar) {
      bringChooserToFront(chooserGruppe, menuGruppe);
    }
    setChooserMenuVisible(menuGruppe, !sichtbar);
    rebindChooserDragAfterMenuAction();
    stopChooserEvent(event);
  }

  chooserText.click(toggleChooserMenu);

  if (chooserText.node) {
    if (chooserText.node.__chooserToggleHandler) {
      chooserText.node.removeEventListener("mouseup", chooserText.node.__chooserToggleHandler);
      chooserText.node.removeEventListener("touchend", chooserText.node.__chooserToggleHandler);
    }
    chooserText.node.__chooserToggleHandler = toggleChooserMenu;
    chooserText.node.addEventListener("mouseup", chooserText.node.__chooserToggleHandler);
    chooserText.node.addEventListener("touchend", chooserText.node.__chooserToggleHandler, { passive: false });
  }

  function selectChooserEntry(eintrag, event) {
    let eventType = event && event.type ? event.type : "";
    let now = Date.now();
    if (eventType === "click" && eintrag.data && eintrag.data("lastNativeChooserEventAt")) {
      if (now - eintrag.data("lastNativeChooserEventAt") < 450) {
        stopChooserEvent(event);
        return;
      }
    }
    if ((eventType === "mouseup" || eventType === "touchend") && eintrag.data) {
      eintrag.data("lastNativeChooserEventAt", now);
    }

    let beforeSelectionSnapshot = typeof getCurrentHistorySnapshot === "function"
      ? getCurrentHistorySnapshot()
      : null;
    let name = eintrag.attr("text");
    let selectedName = onSelect ? onSelect(name, chooserGruppe, chooserText) : name;
    Promise.resolve(selectedName).then(function (resolvedName) {
      if (resolvedName === null) {
        setChooserMenuVisible(menuGruppe, false);
        rebindChooserDragAfterMenuAction();
        return;
      }
      chooserText.attr({ text: resolvedName, fill: "#333" });
      setChooserMenuVisible(menuGruppe, false);
      rebindChooserDragAfterMenuAction();
      if (beforeSelectionSnapshot &&
          typeof getCurrentHistorySnapshot === "function" &&
          typeof pushHistorySnapshot === "function" &&
          !areHistorySnapshotsEqual(beforeSelectionSnapshot, getCurrentHistorySnapshot())) {
        pushHistorySnapshot(beforeSelectionSnapshot);
      }
    });
    stopChooserEvent(event);
  }

  function bindChooserEntry(eintrag) {
    eintrag.click(function (event) {
      selectChooserEntry(eintrag, event);
    });

    if (eintrag.node) {
      if (eintrag.node.__chooserEntryHandler) {
        eintrag.node.removeEventListener("mouseup", eintrag.node.__chooserEntryHandler);
        eintrag.node.removeEventListener("touchend", eintrag.node.__chooserEntryHandler);
      }
      eintrag.node.__chooserEntryHandler = function (event) {
        selectChooserEntry(eintrag, event);
      };
      eintrag.node.addEventListener("mouseup", eintrag.node.__chooserEntryHandler);
      eintrag.node.addEventListener("touchend", eintrag.node.__chooserEntryHandler, { passive: false });
    }
  }

  menuGruppe.selectAll("text").forEach(function (eintrag) {
    bindChooserEntry(eintrag);
  });

  function chooser_sel_start(x, y, event) {
    if (chooserGruppe.data("suppressChooserDrag") || isChooserMenuVisible(menuGruppe) || isEventInsideChooserMenu(event, menuGruppe)) {
      stopChooserEvent(event);
      return;
    }
    chooserGruppe.data("warDrag", false);
    chooserGruppe.data("chooserDragMoved", false);
    chooserGruppe.data("suppressChooserDrag", false);
    chooserGruppe.data("alreadyCloned", false);
    chooserGruppe.data("historyCaptured", false);
    chooserGruppe.data("currentChooserDragDx", 0);
    chooserGruppe.data("currentChooserDragDy", 0);
    installChooserDragReleaseFallback();
    bringChooserToFront(chooserGruppe, menuGruppe);
    let ev = event && (event.originalEvent || event);
    chooserGruppe.data("cloneThisDrag", !!((ev && ev.altKey) || (typeof altKeyIsDown !== "undefined" && altKeyIsDown)));
    let chooserPosition = getChooserPosition(chooserGruppe);
    chooserGruppe.data("origChooserX", chooserPosition.x);
    chooserGruppe.data("origChooserY", chooserPosition.y);
  }

  function moveChooserDirectly(dx, dy) {
    let rawDx = Number(dx);
    let rawDy = Number(dy);
    if (!Number.isFinite(rawDx)) {
      rawDx = 0;
    }
    if (!Number.isFinite(rawDy)) {
      rawDy = 0;
    }

    if (chooserGruppe.data("cloneThisDrag") && !chooserGruppe.data("alreadyCloned")) {
      if (typeof captureHistoryForEditorDrag === "function") {
        captureHistoryForEditorDrag(chooserGruppe);
      }
    } else if (rawDx !== 0 || rawDy !== 0) {
      if (typeof captureHistoryForEditorDrag === "function") {
        captureHistoryForEditorDrag(chooserGruppe);
      }
    }

    let startX = Number(chooserGruppe.data("origChooserX"));
    let startY = Number(chooserGruppe.data("origChooserY"));
    if (!Number.isFinite(startX) || !Number.isFinite(startY)) {
      let chooserPosition = getChooserPosition(chooserGruppe);
      startX = chooserPosition.x;
      startY = chooserPosition.y;
    }
    chooserGruppe.data("currentChooserDragDx", rawDx);
    chooserGruppe.data("currentChooserDragDy", rawDy);
    chooserGruppe.attr({
      transform: "t" + (startX + rawDx) + "," + (startY + rawDy),
    });

    if (chooserGruppe.data("cloneThisDrag") && !chooserGruppe.data("alreadyCloned")) {
      chooserGruppe.data("alreadyCloned", true);
      if (typeof appendBoundClone === "function") {
        appendBoundClone(chooserGruppe);
      }
    }
  }

  function chooser_move(dx, dy, px, py, event) {
    if (chooserGruppe.data("suppressChooserDrag") || isChooserMenuVisible(menuGruppe) || isEventInsideChooserMenu(event, menuGruppe)) {
      return;
    }
    if (!chooserGruppe.data("warDrag")) {
      if (Math.abs(dx) < dragSchwelle && Math.abs(dy) < dragSchwelle) {
        return;
      }
      chooserGruppe.data("warDrag", true);
      chooserGruppe.data("chooserDragMoved", true);
      chooserGruppe.data("suppressChooserToggleUntil", Date.now() + toggleSuppressDuration);
      setChooserMenuVisible(menuGruppe, false);
    }
    moveChooserDirectly(dx, dy);
  }

  function chooser_drag_end() {
    finishChooserDrag();
  }

  function nativeChooserDragStart(event) {
    if (chooserGruppe.data("suppressChooserDrag") || isChooserMenuVisible(menuGruppe) || isEventInsideChooserMenu(event, menuGruppe)) {
      stopChooserEvent(event);
      return;
    }
    chooserGruppe.data("warDrag", false);
    chooserGruppe.data("chooserDragMoved", false);
    chooserGruppe.data("suppressChooserDrag", false);
    chooserGruppe.data("alreadyCloned", false);
    chooserGruppe.data("historyCaptured", false);
    chooserGruppe.data("currentChooserDragDx", 0);
    chooserGruppe.data("currentChooserDragDy", 0);
    let ev = event && (event.originalEvent || event);
    chooserGruppe.data("cloneThisDrag", !!((ev && ev.altKey) || (typeof altKeyIsDown !== "undefined" && altKeyIsDown)));
    bringChooserToFront(chooserGruppe, menuGruppe);

    let startPoint = getChooserDragPoint(event);
    let chooserPosition = getChooserPosition(chooserGruppe);
    chooserGruppe.data("origChooserX", chooserPosition.x);
    chooserGruppe.data("origChooserY", chooserPosition.y);

    removeNativeChooserDragListeners();
    nativeChooserDragMoveHandler = function (moveEvent) {
      if (chooserGruppe.data("suppressChooserDrag") || isChooserMenuVisible(menuGruppe) || isEventInsideChooserMenu(moveEvent, menuGruppe)) {
        return;
      }
      let nextPoint = getChooserDragPoint(moveEvent);
      let dx = nextPoint.x - startPoint.x;
      let dy = nextPoint.y - startPoint.y;
      if (!chooserGruppe.data("warDrag")) {
        if (Math.abs(dx) < dragSchwelle && Math.abs(dy) < dragSchwelle) {
          return;
        }
        chooserGruppe.data("warDrag", true);
        chooserGruppe.data("chooserDragMoved", true);
        chooserGruppe.data("suppressChooserToggleUntil", Date.now() + toggleSuppressDuration);
        setChooserMenuVisible(menuGruppe, false);
      }
      moveChooserDirectly(dx, dy);
      stopChooserEvent(moveEvent);
    };
    nativeChooserDragEndHandler = function () {
      removeNativeChooserDragListeners();
      finishChooserDrag();
    };
    window.addEventListener("mousemove", nativeChooserDragMoveHandler, true);
    window.addEventListener("touchmove", nativeChooserDragMoveHandler, { capture: true, passive: false });
    window.addEventListener("mouseup", nativeChooserDragEndHandler, true);
    window.addEventListener("touchend", nativeChooserDragEndHandler, true);
    window.addEventListener("touchcancel", nativeChooserDragEndHandler, true);
  }

  function installNativeChooserDrag() {
    chooserGruppe.undrag();
    removeNativeChooserDragListeners();
    if (!chooserGruppe.node) {
      return;
    }
    if (chooserGruppe.node.__nativeChooserDragStart) {
      chooserGruppe.node.removeEventListener("mousedown", chooserGruppe.node.__nativeChooserDragStart, true);
      chooserGruppe.node.removeEventListener("touchstart", chooserGruppe.node.__nativeChooserDragStart, true);
    }
    chooserGruppe.node.__nativeChooserDragStart = nativeChooserDragStart;
    chooserGruppe.node.addEventListener("mousedown", chooserGruppe.node.__nativeChooserDragStart, true);
    chooserGruppe.node.addEventListener("touchstart", chooserGruppe.node.__nativeChooserDragStart, { capture: true, passive: false });
  }

  installNativeChooserDrag();
}

function bindInstrumentChooserInteraction(chooserGruppe, instrumentText, menuGruppe) {
  bindChooserInteraction(chooserGruppe, instrumentText, menuGruppe, function (name) {
    return name;
  });
}

/**
 * Nach ele.clone(): alte Snap-Handler entfernen und an die echten Kindknoten neu binden.
 */
function rewireInstrumentChooser(chooserGruppe) {
  var kids = chooserGruppe.children();
  var instrumentText = kids[0];
  var menuGruppe = kids[1];
  if (!instrumentText || instrumentText.type !== "text") {
    return;
  }
  if (!menuGruppe || menuGruppe.type !== "g") {
    return;
  }

  chooserGruppe.undrag();
  instrumentText.unclick();
  menuGruppe.selectAll("text").forEach(function (t) {
    t.unclick();
  });

  bindInstrumentChooserInteraction(chooserGruppe, instrumentText, menuGruppe);
}

function rewireFunctionChooser(chooserGruppe) {
  var kids = chooserGruppe.children();
  var functionText = kids[0];
  var menuGruppe = kids[1];
  if (!functionText || functionText.type !== "text") {
    return;
  }
  if (!menuGruppe || menuGruppe.type !== "g") {
    return;
  }

  chooserGruppe.undrag();
  functionText.unclick();
  menuGruppe.selectAll("text").forEach(function (t) {
    t.unclick();
  });

  bindChooserInteraction(chooserGruppe, functionText, menuGruppe, function (name) {
    if (name !== "Solo" && name !== "Begleitpattern") {
      return name;
    }
    let promptText =
      'Bezeichnung für "' +
      name +
      '" anpassen.\nZum Beispiel: "Solo 1", "1. Solo", "Begleitpattern 2".';
    return requestChooserLabel(getChooserLabelSeed(name, functionText), promptText);
  });
}
