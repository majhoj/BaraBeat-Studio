function createInstrumentChooser(s, x, y, startText = "Instrument", startFill = "gray") {
  let instrumente = [
    "Djembe 1",
    "Djembe 2",
    "Djembe 3",
    "Kenkeni",
    "Sangban",
    "Dununba",
    "Dreierbass",
  ];

  let chooserGruppe = s.g();
  chooserGruppe.addClass("instrument-chooser");
  let menuGruppe = s.g().attr({ display: "none" });

  let instrumentText = s.text(0, 0, startText).attr({
    class: "instrument-label",
    fill: startFill,
    "font-size": 16,
    "font-family": "sans-serif",
    cursor: "pointer",
  });

  let zeilenHoehe = 22;
  let menuBreite = 120;
  let menuHoehe = instrumente.length * zeilenHoehe + 10;

  let menuBg = s.rect(-5, 5, menuBreite, menuHoehe, 4, 4).attr({
    fill: "#f0f0f0",
    stroke: "#999",
    "stroke-width": 1,
  });

  menuGruppe.add(menuBg);

  instrumente.forEach(function (name, index) {
    let eintrag = s.text(5, 22 + index * zeilenHoehe, name).attr({
      fill: "#333",
      "font-size": 14,
      "font-family": "sans-serif",
      cursor: "pointer",
    });
    menuGruppe.add(eintrag);
  });

  chooserGruppe.add(instrumentText, menuGruppe);
  chooserGruppe.transform("translate(" + x + "," + y + ")");

  bindInstrumentChooserInteraction(chooserGruppe, instrumentText, menuGruppe);

  return chooserGruppe;
}

/**
 * Klick- und Drag-Verhalten für eine InstrumentChooser-Gruppe (neu oder nach DOM-Klon).
 * Instrument-Chooser verwenden bewusst kein separates dragEnd wie stop_m,
 * damit geladenes und neu erzeugtes Verhalten identisch bleibt.
 */
function bindInstrumentChooserInteraction(chooserGruppe, instrumentText, menuGruppe) {
  let dragSchwelle = 5;

  instrumentText.click(function (event) {
    if (chooserGruppe.data("warDrag")) {
      chooserGruppe.data("warDrag", false);
      event.stopPropagation();
      return;
    }
    let sichtbar = menuGruppe.attr("display") !== "none";
    menuGruppe.attr({ display: sichtbar ? "none" : "inline" });
    event.stopPropagation();
  });

  menuGruppe.selectAll("text").forEach(function (eintrag) {
    eintrag.click(function (event) {
      let name = eintrag.attr("text");
      instrumentText.attr({ text: name, fill: "#333" });
      menuGruppe.attr({ display: "none" });
      event.stopPropagation();
    });
  });

  function chooser_sel_start(x, y, event) {
    chooserGruppe.data("warDrag", false);
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
