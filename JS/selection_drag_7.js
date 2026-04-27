// JavaScript Document

var instrumentChooserIdSeq = 0;
var box = null;
var selections = null;
var lastKeyPressed = null;
var selectionDragState = {
  currentDx: undefined,
  currentDy: undefined,
  accumulatedDx: 0,
  accumulatedDy: 0,
  dragOffsetDx: 0,
  dragOffsetDy: 0,
};

function nextInstrumentChooserId() {
  instrumentChooserIdSeq += 1;
  return "instrumentChooser-" + instrumentChooserIdSeq;
}

function isInstrumentChooserNode(el) {
  if (!el || typeof el.attr !== "function") {
    return false;
  }
  if (typeof el.hasClass === "function" && el.hasClass("instrument-chooser")) {
    return true;
  }
  var id = el.attr("id");
  return (
    id === "instrumentChooser" ||
    (typeof id === "string" && id.indexOf("instrumentChooser-") === 0)
  );
}

function suppressChooserClickAfterDrag(chooserElement) {
  if (!isInstrumentChooserNode(chooserElement)) {
    return;
  }

  chooserElement.data("warDrag", true);
  var chooserChildren = chooserElement.children();
  if (chooserChildren[1] && chooserChildren[1].type === "g") {
    chooserChildren[1].attr({ display: "none" });
  }
}

function createChooserClone(sourceElement) {
  var startX = sourceElement.data("startX");
  var startY = sourceElement.data("startY") + 13;
  var textElement = sourceElement.select("text");
  var chooserText = textElement.attr("text");
  var chooserColor = textElement.attr("fill");

  return createInstrumentChooser(s, startX, startY, chooserText, chooserColor)
    .addClass("shp")
    .attr({
      id: nextInstrumentChooserId(),
    });
}

function createElementClone(sourceElement) {
  if (isInstrumentChooserNode(sourceElement)) {
    return createChooserClone(sourceElement);
  }

  return sourceElement.clone().attr({
    id: sourceElement.attr("id"),
    class: sourceElement.attr("class"),
  });
}

function bindClonedElement(clonedElement) {
  if (isInstrumentChooserNode(clonedElement)) {
    clonedElement.selectAll("g").forEach(function (sub) {
      sub.attr({ display: "none" });
    });
    suppressChooserClickAfterDrag(clonedElement);
    rewireInstrumentChooser(clonedElement);
    return clonedElement;
  }

  if (clonedElement.attr("id") == "wiederholung") {
    clonedElement.dblclick(cycleRepeatCount);
  }
  if (clonedElement.attr("id") == "edit_text") {
    clonedElement.dblclick(edit_text);
  }
  clonedElement.drag(move, sel_start, stop_m);
  return clonedElement;
}

function appendBoundClone(sourceElement) {
  var clonedElement = createElementClone(sourceElement);
  bindClonedElement(clonedElement);
  s.append(clonedElement);
  return clonedElement;
}

// Die auswählbaren Elemente müssen der Klasse .shp angehören.
// Entweder mit element.addClass('shp'); oder .attr({class: 'shp'}); hinzufügen

var selectableElementSelector = ".shp, .instrument-chooser";

function bindElementDrag(element, dragMoveHandler) {
  if (isInstrumentChooserNode(element)) {
    rewireInstrumentChooser(element);
    return element;
  }

  element.drag(dragMoveHandler, sel_start, stop_m);
  return element;
}

function appendUngroupedElement(element) {
  bindElementDrag(element, move);
  s.append(element);
  return element;
}

function getSelectedElements(groupElement) {
  return groupElement.selectAll(selectableElementSelector);
}

function getSelectableCanvasElements() {
  return s.selectAll(selectableElementSelector);
}

function UnGroup() {
  if (selections) {
    /*Prüfen ob die Auswahl bewegt wurde.
    Wenn nicht, drag-Methode wieder hinzufügen und <br>
    Elemente wieder zu s hinzugügen. */

    const selectedElements = getSelectedElements(selections);
    if (typeof selectionDragState.currentDx == "undefined") {
      selectedElements.forEach(function (ele) {
        appendUngroupedElement(ele);
      });
    }
    selectionDragState.accumulatedDx += selectionDragState.dragOffsetDx;
    selectionDragState.accumulatedDy += selectionDragState.dragOffsetDy;
    selectedElements.forEach(function (ele) {
      var transformString = ele.matrix.toTransformString();
      var nextDx;
      var nextDy;

      /*Prüfen ob die Auswahl zum ersten Mal verschoben wird.
      Dann ist trans undefiniert */
      if (transformString === "") {
        nextDx = selectionDragState.accumulatedDx;
        nextDy = selectionDragState.accumulatedDy;
      } else {
        transformString = transformString.split(",");
        nextDx = selectionDragState.accumulatedDx + Number(transformString[0].substr(1));
        nextDy = selectionDragState.accumulatedDy + Number(transformString[1]);
      }
      ele.transform("t" + nextDx + ", " + nextDy);
      appendUngroupedElement(ele);
    });
  }
  selectionDragState.accumulatedDx -= selectionDragState.dragOffsetDx;
  selectionDragState.accumulatedDy -= selectionDragState.dragOffsetDy;
  selectionDragState.currentDx = 0;
  selectionDragState.currentDy = 0;
  selectionDragState.dragOffsetDx = 0;
  selectionDragState.dragOffsetDy = 0;
}

function shadow_start(x, y, event) {
  box = s.rect(x - 9, y - 21, 0, 0).attr("stroke", "#3366ff");
  if (selections) {
    UnGroup();
  }
}

function shadow_move(dx, dy, x, y, event) {
  var xoffset = 0,
    yoffset = 0;
  if (dx < 0) {
    xoffset = dx;
    dx = -1 * dx;
  }
  if (dy < 0) {
    yoffset = dy;
    dy = -1 * dy;
  }
  box.transform("T" + xoffset + "," + yoffset);
  box.attr("width", dx);
  box.attr("height", dy);
  box.attr("fill", "none");
}

function shadow_end(event) {
  var bounds = box.getBBox();
  box.remove();
  box = null;
  var matchedElements = [];
  lastKeyPressed = event.key;
  getSelectableCanvasElements().forEach(function (el) {
    var mybounds = el.getBBox();
    if (Snap.path.isBBoxIntersect(mybounds, bounds)) {
      matchedElements.push(el);
    }
  });
  if (matchedElements.length > 0) {
    selections = s.g();
    matchedElements.forEach(function (el) {
      el.undrag();
    });
    selections.add(matchedElements);
    selections.drag(sel_move, sel_start, stop_m);
    selections.attr({ opacity: 0.5 });
  }
}

var gridSize = 850 / 26 / 2;
var gridSize1 = 5;
var noteGridYOffset = -4;

function getElementSnapReferenceY(element, bbox) {
  var elementId = element && typeof element.attr === "function" ? element.attr("id") : "";
  var snapReferenceY = bbox.cy;

  if (elementId === "tone_muffled") {
    return bbox.y + 8;
  }
  if (elementId === "slap_muffled") {
    return bbox.y + 6;
  }
  if (elementId === "in") {
    return bbox.y + 8;
  }
  if (elementId === "out") {
    return bbox.y + 9;
  }
  if (elementId === "flam_ton") {
    return snapReferenceY + 2;
  }
  if (elementId === "bass_slap_flam") {
    return snapReferenceY + 1;
  }
  return snapReferenceY;
}

function snapDeltaWithYOffset(startY, deltaY) {
  var snappedY = Snap.snapTo(gridSize1, startY + deltaY - noteGridYOffset, 50) + noteGridYOffset;
  return snappedY - startY;
}

function entfernen() {
  let pressedKey = event.key;

  //(event.key + " " + event.metaKey)
  let pressedMetaKey = event.metaKey;
  if (pressedKey == "x" && pressedMetaKey && selections) {
    //	if(key_ged && key_ged_meta){
    const selectedElements = getSelectedElements(selections);

    selectedElements.forEach(function (ele) {
      ele.remove();
    });
  }
}

function sel_move(dx, dy) {
  var dx = Snap.snapTo(gridSize, dx, 50);
  var dy = snapDeltaWithYOffset(this.data("startSnapY"), dy);

  this.selectAll(".instrument-chooser").forEach(function (chooserElement) {
    suppressChooserClickAfterDrag(chooserElement);
  });

  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    this.data("alreadyCloned", true);
    getSelectedElements(this).forEach(function (ele) {
      appendBoundClone(ele);
    }.bind(this));
  }

  this.attr({
    transform:
      this.data("origTransform") +
      (this.data("origTransform") ? "T" : "t") +
      [dx, dy],
  });
  // dx, dy sind die Werte um die die Gruppe verschoben wurde.
  selectionDragState.currentDx = dx;
  selectionDragState.currentDy = dy;
}

function move(dx, dy) {
  var dx = Snap.snapTo(gridSize, dx, 50);
  var dy = snapDeltaWithYOffset(this.data("startSnapY"), dy);
  this.attr({
    transform:
      this.data("origTransform") +
      (this.data("origTransform") ? "T" : "t") +
      [dx, dy], 
  });
  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    this.data("alreadyCloned", true);
    appendBoundClone(this);
  }
}

function start(event) {
  lastKeyPressed = event.key;
  this.data("origTransform", this.transform().local);
}

function sel_start(x, y, event) {
  var ev = event && (event.originalEvent || event);
  this.data("cloneThisDrag", !!(ev && ev.altKey));
  this.data("origTransform", this.transform().local);
  this.data("alreadyCloned", false);

  let bbox = this.getBBox();
  this.data("startX", bbox.x);
  this.data("startY", bbox.y);
  this.data("startSnapY", getElementSnapReferenceY(this, bbox));
}

function stop_m() {
  // Die Verschiebungswerte werden mit jeder Verschiebung der Gruppe addiert, bis die Gruppe aufgelöst wird.
  selectionDragState.dragOffsetDx += selectionDragState.currentDx;
  selectionDragState.dragOffsetDy += selectionDragState.currentDy;
}
