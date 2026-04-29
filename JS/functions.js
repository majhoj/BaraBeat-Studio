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
    fill: "#f0f0f0",
    fillOpacity: 0.8,
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

function getChooserPosition(chooserGruppe) {
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
      "Djembe 1",
      "Djembe 2",
      "Djembe 3",
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
    onSelect: function (name) {
      if (name !== "Solo" && name !== "Begleitpattern") {
        return name;
      }
      let promptText =
        'Bezeichnung für "' +
        name +
        '" anpassen.\nZum Beispiel: "Solo 1", "1. Solo", "Begleitpattern 2".';
      let configuredName = prompt(promptText, name);
      if (configuredName === null) {
        return null;
      }
      configuredName = configuredName.trim();
      return configuredName === "" ? name : configuredName;
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

  chooserText.click(function (event) {
    if (chooserGruppe.data("warDrag")) {
      chooserGruppe.data("warDrag", false);
      event.stopPropagation();
      return;
    }
    let sichtbar = menuGruppe.attr("display") !== "none";
    if (!sichtbar && typeof chooserGruppe.toFront === "function") {
      chooserGruppe.toFront();
    }
    menuGruppe.attr({ display: sichtbar ? "none" : "inline" });
    event.stopPropagation();
  });

  menuGruppe.selectAll("text").forEach(function (eintrag) {
    eintrag.click(function (event) {
      let name = eintrag.attr("text");
      let selectedName = onSelect ? onSelect(name, chooserGruppe, chooserText) : name;
      if (selectedName === null) {
        menuGruppe.attr({ display: "none" });
        event.stopPropagation();
        return;
      }
      chooserText.attr({ text: selectedName, fill: "#333" });
      menuGruppe.attr({ display: "none" });
      event.stopPropagation();
    });
  });

  function chooser_sel_start(x, y, event) {
    chooserGruppe.data("warDrag", false);
    if (typeof chooserGruppe.toFront === "function") {
      chooserGruppe.toFront();
    }
    sel_start.call(this, x, y, event);
  }

  function chooser_move(dx, dy, px, py, event) {
    if (!chooserGruppe.data("warDrag")) {
      if (Math.abs(dx) < dragSchwelle && Math.abs(dy) < dragSchwelle) {
        return;
      }
      chooserGruppe.data("warDrag", true);
      menuGruppe.attr({ display: "none" });
    }
    move.call(this, dx, dy, px, py, event);
  }

  chooserGruppe.drag(chooser_move, chooser_sel_start);
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
    let configuredName = prompt(promptText, name);
    if (configuredName === null) {
      return null;
    }
    configuredName = configuredName.trim();
    return configuredName === "" ? name : configuredName;
  });
}
