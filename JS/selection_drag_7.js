// JavaScript Document

var instrumentChooserIdSeq = 0;
var functionChooserIdSeq = 0;
var box = null;
var selections = null;
var lastKeyPressed = null;
var altKeyIsDown = false;
var selectionDragState = {
  currentDx: undefined,
  currentDy: undefined,
  accumulatedDx: 0,
  accumulatedDy: 0,
  dragOffsetDx: 0,
  dragOffsetDy: 0,
  shadowStartX: 0,
  shadowStartY: 0,
};

window.addEventListener("keydown", function (event) {
  if (event.key === "Alt") {
    altKeyIsDown = true;
  }
});

window.addEventListener("keyup", function (event) {
  if (event.key === "Alt") {
    altKeyIsDown = false;
  }
});

window.addEventListener("blur", function () {
  altKeyIsDown = false;
});

function nextInstrumentChooserId() {
  instrumentChooserIdSeq += 1;
  return "instrumentChooser-" + instrumentChooserIdSeq;
}

function nextFunctionChooserId() {
  functionChooserIdSeq += 1;
  return "functionChooser-" + functionChooserIdSeq;
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

function isFunctionChooserNode(el) {
  if (!el || typeof el.attr !== "function") {
    return false;
  }
  if (typeof el.hasClass === "function" && el.hasClass("function-chooser")) {
    return true;
  }
  var id = el.attr("id");
  return (
    id === "functionChooser" ||
    (typeof id === "string" && id.indexOf("functionChooser-") === 0)
  );
}

function isChooserNode(el) {
  return isInstrumentChooserNode(el) || isFunctionChooserNode(el);
}

function suppressChooserClickAfterDrag(chooserElement) {
  if (!isChooserNode(chooserElement)) {
    return;
  }

  chooserElement.data("warDrag", true);
  var chooserChildren = chooserElement.children();
  if (chooserChildren[1] && chooserChildren[1].type === "g") {
    chooserChildren[1].attr({ display: "none" });
  }
}

function createChooserClone(sourceElement) {
  var chooserBounds = sourceElement.getBBox();
  var startX = chooserBounds.x;
  var startY = chooserBounds.y + 13;
  var textElement = sourceElement.select("text");
  var chooserText = textElement.attr("text");
  var chooserColor = textElement.attr("fill");

  if (isFunctionChooserNode(sourceElement)) {
    return createFunctionChooser(s, startX, startY, chooserText, chooserColor)
      .addClass("shp")
      .attr({
        id: nextFunctionChooserId(),
      });
  }

  return createInstrumentChooser(s, startX, startY, chooserText, chooserColor)
    .addClass("shp")
    .attr({
      id: nextInstrumentChooserId(),
    });
}

function createElementClone(sourceElement) {
  if (isChooserNode(sourceElement)) {
    return createChooserClone(sourceElement);
  }

  return sourceElement.clone().attr({
    id: sourceElement.attr("id"),
    class: sourceElement.attr("class"),
  });
}

function bindClonedElement(clonedElement) {
  if (isChooserNode(clonedElement)) {
    clonedElement.selectAll("g").forEach(function (sub) {
      sub.attr({ display: "none" });
    });
    suppressChooserClickAfterDrag(clonedElement);
    if (isFunctionChooserNode(clonedElement)) {
      rewireFunctionChooser(clonedElement);
    } else {
      rewireInstrumentChooser(clonedElement);
    }
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

var selectableElementSelector = ".shp, .instrument-chooser, .function-chooser";

function bindElementDrag(element, dragMoveHandler) {
  if (isChooserNode(element)) {
    if (isFunctionChooserNode(element)) {
      rewireFunctionChooser(element);
    } else {
      rewireInstrumentChooser(element);
    }
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

function getElementTranslate(element) {
  if (!element || typeof element.transform !== "function") {
    return { x: 0, y: 0 };
  }

  var transformState = element.transform();
  var localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
  return {
    x: localMatrix && typeof localMatrix.e === "number" ? localMatrix.e : 0,
    y: localMatrix && typeof localMatrix.f === "number" ? localMatrix.f : 0,
  };
}

function getSelectedElements(groupElement) {
  return groupElement.selectAll(selectableElementSelector);
}

function getSelectableCanvasElements() {
  return s.selectAll(selectableElementSelector);
}

function resetSelectionDragState() {
  selectionDragState.accumulatedDx = 0;
  selectionDragState.accumulatedDy = 0;
  selectionDragState.currentDx = undefined;
  selectionDragState.currentDy = undefined;
  selectionDragState.dragOffsetDx = 0;
  selectionDragState.dragOffsetDy = 0;
}

function UnGroup() {
  if (selections) {
    /*Prüfen ob die Auswahl bewegt wurde.
    Wenn nicht, drag-Methode wieder hinzufügen und <br>
    Elemente wieder zu s hinzugügen. */

    const selectedElements = getSelectedElements(selections);
    var selectionWasMoved =
      typeof selectionDragState.currentDx !== "undefined" &&
      (selectionDragState.currentDx !== 0 ||
        selectionDragState.currentDy !== 0 ||
        selectionDragState.dragOffsetDx !== 0 ||
        selectionDragState.dragOffsetDy !== 0);

    if (!selectionWasMoved) {
      selectedElements.forEach(function (ele) {
        appendUngroupedElement(ele);
      });
      selections.remove();
      selections = null;
      resetSelectionDragState();
      return;
    }
    var groupTranslate = getElementTranslate(selections);
    selectedElements.forEach(function (ele) {
      var childTranslate = getElementTranslate(ele);
      var nextDx = childTranslate.x + groupTranslate.x;
      var nextDy = childTranslate.y + groupTranslate.y;
      ele.transform("t" + nextDx + ", " + nextDy);
      appendUngroupedElement(ele);
    });
    selections.remove();
    selections = null;
  }
  resetSelectionDragState();
}

function getSvgPointerPosition(event, fallbackX, fallbackY) {
  var svgNode = s && s.node;
  var sourceEvent = event && (event.touches ? event.touches[0] : (event.changedTouches ? event.changedTouches[0] : event));

  if (!svgNode || !sourceEvent || typeof sourceEvent.clientX !== "number" || typeof sourceEvent.clientY !== "number") {
    return {
      x: Number(fallbackX) || 0,
      y: Number(fallbackY) || 0,
    };
  }

  if (typeof svgNode.createSVGPoint === "function" && svgNode.getScreenCTM && svgNode.getScreenCTM()) {
    var point = svgNode.createSVGPoint();
    point.x = sourceEvent.clientX;
    point.y = sourceEvent.clientY;
    var transformedPoint = point.matrixTransform(svgNode.getScreenCTM().inverse());
    return {
      x: transformedPoint.x,
      y: transformedPoint.y,
    };
  }

  var svgBounds = svgNode.getBoundingClientRect();
  return {
    x: sourceEvent.clientX - svgBounds.left,
    y: sourceEvent.clientY - svgBounds.top,
  };
}

function shadow_start(x, y, event) {
  var pointerPosition = getSvgPointerPosition(event, x, y);
  if (selections) {
    UnGroup();
  }
  if (box) {
    box.remove();
    box = null;
  }
  selectionDragState.shadowStartX = pointerPosition.x;
  selectionDragState.shadowStartY = pointerPosition.y;
  box = s
    .rect(pointerPosition.x, pointerPosition.y, 0, 0)
    .attr({ stroke: "#3366ff", fill: "none", pointerEvents: "none" });
}

function shadow_move(dx, dy, x, y, event) {
  if (!box) {
    return;
  }
  var pointerPosition = getSvgPointerPosition(event, x, y);
  var rectX = Math.min(selectionDragState.shadowStartX, pointerPosition.x);
  var rectY = Math.min(selectionDragState.shadowStartY, pointerPosition.y);
  var rectWidth = Math.abs(pointerPosition.x - selectionDragState.shadowStartX);
  var rectHeight = Math.abs(pointerPosition.y - selectionDragState.shadowStartY);

  box.attr("x", rectX);
  box.attr("y", rectY);
  box.attr("width", rectWidth);
  box.attr("height", rectHeight);
}

function shadow_end(event) {
  if (!box) {
    return;
  }
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
// Feste vertikale Snap-Stufen pro System, relativ zu staffStartY:
// 0: Chooser-Reihe
// 1: obere Parkposition
// 2: obere Noten-/Glockenlage
// 3: untere Notenlage
// 4: untere Parkposition
var verticalSnapOffsets = [-32, -2, 15, 31, 42];
// Eigene vertikale Stufen fuer Notenzeichen:
// 0: eine Position oberhalb der Grundlinie
// 1: Grundlinienposition
// 2: erste Position unterhalb der Grundlinie
// 3: zweite Position unterhalb der Grundlinie
var noteSymbolVerticalSnapOffsets = [17, 32, 47, 62];
// Eigene vertikale Stufen fuer editierbare Textfelder:
// -100: Titel-Zeile, -32: Chooser-Zeile, danach die normalen Park-/Notenlinien-Ziele.
var editableTextVerticalSnapOffsets = [-100, -32, -2, 15, 31, 42];
//var verticalSnapOffsets = [-32, -12, 15, 36, 70];

function getElementTranslateY(element) {
  if (!element || typeof element.transform !== "function") {
    return 0;
  }

  var transformState = element.transform();
  var localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
  return localMatrix && typeof localMatrix.f === "number" ? localMatrix.f : 0;
}

function getTextElementBaselineY(element, bbox) {
  var attrY = element && typeof element.attr === "function" ? Number(element.attr("y")) : NaN;
  if (Number.isFinite(attrY)) {
    return attrY + getElementTranslateY(element);
  }
  return bbox.cy;
}

function getElementSnapReferenceY(element, bbox) {
  var elementId = element && typeof element.attr === "function" ? element.attr("id") : "";
  var elementClasses = element && typeof element.attr === "function" ? String(element.attr("class") || "") : "";
  var snapReferenceY = bbox.cy;

  if (elementClasses.indexOf("chooser-node") !== -1 && typeof element.transform === "function") {
    var chooserTransform = element.transform();
    var chooserMatrix = chooserTransform && chooserTransform.localMatrix ? chooserTransform.localMatrix : null;
    if (chooserMatrix && typeof chooserMatrix.f === "number") {
      return chooserMatrix.f;
    }
  }

  if (elementId === "edit_text") {
    return getTextElementBaselineY(element, bbox);
  }

  if (elementId === "slap" || elementId === "slap_flam") {
    return bbox.y + bbox.height - 6;
  }
  if (elementId === "tone") {
    return bbox.y + bbox.height - 7;
  }
  if (
    elementId === "bass" ||
    elementId === "tone_flam" ||
    elementId === "bass_slap_flam"
  ) {
    return bbox.y + bbox.height - 6;
  }
  if (elementId === "tone_muffled") {
    return bbox.y + bbox.height - 9;
  }
  if (elementId === "slap_muffled") {
    return bbox.y + bbox.height - 9;
  }
  if (elementId === "in") {
    return bbox.y + bbox.height - 22;
  }
  if (elementId === "out") {
    return bbox.y + bbox.height - 22;
  }
  if (elementId === "wiederholung") {
    return bbox.y + bbox.height - 4;
  }
  return snapReferenceY;
}

function getVerticalSnapTargets(offsetList) {
  var lineCount = typeof zeilenAnzahl === "number" && zeilenAnzahl > 0 ? zeilenAnzahl : 10;
  var baseY = typeof staffStartY === "number" ? staffStartY : 172;
  var targets = [];
  var resolvedOffsets = Array.isArray(offsetList) && offsetList.length ? offsetList : verticalSnapOffsets;

  for (var lineIndex = 0; lineIndex <= lineCount; lineIndex++) {
    resolvedOffsets.forEach(function (offsetY) {
      targets.push(baseY + offsetY + lineIndex * 120);
    });
  }

  return targets;
}

function snapToVerticalTargets(targetY, element) {
  var elementId = element && typeof element.attr === "function" ? element.attr("id") : "";
  var usesNoteSymbolTargets =
    elementId === "tone" ||
    elementId === "bass" ||
    elementId === "slap" ||
    elementId === "tone_muffled" ||
    elementId === "slap_muffled" ||
    elementId === "tone_flam" ||
    elementId === "slap_flam" ||
    elementId === "bass_slap_flam";
  var usesEditableTextTargets = elementId === "edit_text";
  var snapTargets =
    usesNoteSymbolTargets
      ? getVerticalSnapTargets(noteSymbolVerticalSnapOffsets)
      : usesEditableTextTargets
        ? getVerticalSnapTargets(editableTextVerticalSnapOffsets)
      : getVerticalSnapTargets();
  var nearestTargetY = snapTargets[0];
  var smallestDistance = Math.abs(targetY - nearestTargetY);

  snapTargets.forEach(function (candidateY) {
    var candidateDistance = Math.abs(targetY - candidateY);
    if (candidateDistance < smallestDistance) {
      smallestDistance = candidateDistance;
      nearestTargetY = candidateY;
    }
  });

  return nearestTargetY;
}

function snapDeltaWithYOffset(startY, deltaY, element) {
  var snappedY = snapToVerticalTargets(startY + deltaY, element);
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
  var dy = snapDeltaWithYOffset(this.data("startSnapY"), dy, this);

  this.selectAll(".instrument-chooser, .function-chooser").forEach(function (chooserElement) {
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
  var dy = snapDeltaWithYOffset(this.data("startSnapY"), dy, this);
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
  if (!this || typeof this.data !== "function" || typeof this.transform !== "function") {
    return;
  }
  this.data("origTransform", this.transform().local);
}

function sel_start(x, y, event) {
  var ev = event && (event.originalEvent || event);
  this.data("cloneThisDrag", !!((ev && ev.altKey) || altKeyIsDown));
  this.data("origTransform", this.transform().local);
  this.data("alreadyCloned", false);

  let bbox = this.getBBox();
  this.data("startX", bbox.x);
  this.data("startY", bbox.y);
  var startReferenceY = getElementSnapReferenceY(this, bbox);
  var isSelectionGroup = this === selections;
  this.data("startSnapY", isSelectionGroup ? snapToVerticalTargets(startReferenceY, this) : startReferenceY);
}

function stop_m() {
  // Die Verschiebungswerte werden mit jeder Verschiebung der Gruppe addiert, bis die Gruppe aufgelöst wird.
  var currentDx = Number.isFinite(selectionDragState.currentDx) ? selectionDragState.currentDx : 0;
  var currentDy = Number.isFinite(selectionDragState.currentDy) ? selectionDragState.currentDy : 0;
  selectionDragState.dragOffsetDx += currentDx;
  selectionDragState.dragOffsetDy += currentDy;
}
