// JavaScript Document

var instrumentChooserIdSeq = 0;
var functionChooserIdSeq = 0;
var box = null;
var selections = null;
var lastKeyPressed = null;
var altKeyIsDown = false;
var selectionDragReleaseHandler = null;
var editorClipboardMarkup = "";
var editorClipboardSourceAction = "";
var editorClipboardStorageKey = "barabeat.editorClipboard";
var editorClipboardPayloadStorageKey = "barabeat.editorClipboardPayload";
var editorClipboardSystemType = "barabeat.editorSelection";
var editorClipboardTabId = "tab-" + Date.now() + "-" + Math.random().toString(36).slice(2);
var selectionDragState = {
  currentDx: undefined,
  currentDy: undefined,
  accumulatedDx: 0,
  accumulatedDy: 0,
  dragOffsetDx: 0,
  dragOffsetDy: 0,
  selectionWasDragged: false,
  selectionWasCloned: false,
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

function removeSelectionDragReleaseFallback() {
  if (!selectionDragReleaseHandler) {
    return;
  }
  window.removeEventListener("mouseup", selectionDragReleaseHandler, true);
  window.removeEventListener("touchend", selectionDragReleaseHandler, true);
  window.removeEventListener("touchcancel", selectionDragReleaseHandler, true);
  selectionDragReleaseHandler = null;
}

function selectionShouldUngroupAfterDrag(selectionGroup) {
  if (!selectionGroup || selectionGroup !== selections) {
    return false;
  }
  var currentDx = Number.isFinite(selectionDragState.currentDx) ? selectionDragState.currentDx : 0;
  var currentDy = Number.isFinite(selectionDragState.currentDy) ? selectionDragState.currentDy : 0;
  return currentDx !== 0 ||
    currentDy !== 0 ||
    selectionDragState.selectionWasDragged ||
    selectionDragState.selectionWasCloned ||
    selectionGroup.data("alreadyCloned");
}

function scheduleSelectionUngroupAfterDrag(selectionGroup) {
  if (!selectionShouldUngroupAfterDrag(selectionGroup)) {
    return;
  }
  window.setTimeout(function () {
    if (selections === selectionGroup) {
      UnGroup();
    }
  }, 0);
}

function installSelectionDragReleaseFallback(selectionGroup) {
  removeSelectionDragReleaseFallback();
  selectionDragReleaseHandler = function () {
    removeSelectionDragReleaseFallback();
    if (selectionShouldUngroupAfterDrag(selectionGroup)) {
      snapSelectionElementsAndKeepSelection(selectionGroup);
    }
  };
  window.addEventListener("mouseup", selectionDragReleaseHandler, true);
  window.addEventListener("touchend", selectionDragReleaseHandler, true);
  window.addEventListener("touchcancel", selectionDragReleaseHandler, true);
}

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

  var suppressUntil = Date.now() + 700;
  chooserElement.data("warDrag", true);
  chooserElement.data("suppressChooserToggleUntil", suppressUntil);
  var chooserChildren = chooserElement.children();
  if (chooserChildren[1] && chooserChildren[1].type === "g") {
    chooserChildren[1].attr({ display: "none" });
  }
}

function createChooserClone(sourceElement) {
  var chooserBounds = sourceElement.getBBox();
  var chooserPosition = typeof getChooserPosition === "function"
    ? getChooserPosition(sourceElement)
    : getElementTranslate(sourceElement);
  var startX = Number.isFinite(chooserPosition.x) ? chooserPosition.x : chooserBounds.x;
  var startY = Number.isFinite(chooserPosition.y) ? chooserPosition.y : chooserBounds.y + 13;
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

function unbindChooserDragForSelection(element) {
  if (!element || typeof element.undrag !== "function") {
    return;
  }
  if (isChooserNode(element) && element.node && element.node.__nativeChooserDragStart) {
    element.node.removeEventListener("mousedown", element.node.__nativeChooserDragStart, true);
    element.node.removeEventListener("touchstart", element.node.__nativeChooserDragStart, true);
  }
  element.undrag();
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

  if (element.node && element.node.transform && element.node.transform.baseVal) {
    var consolidatedTransform = element.node.transform.baseVal.consolidate();
    var domMatrix = consolidatedTransform && consolidatedTransform.matrix ? consolidatedTransform.matrix : null;
    if (domMatrix) {
      return {
        x: typeof domMatrix.e === "number" ? domMatrix.e : 0,
        y: typeof domMatrix.f === "number" ? domMatrix.f : 0,
      };
    }
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
  selectionDragState.selectionWasDragged = false;
  selectionDragState.selectionWasCloned = false;
  removeSelectionDragReleaseFallback();
}

function getSnappedTranslateForSelectionElement(element, groupTranslate) {
  var childTranslate = getElementTranslate(element);
  var nextDx = childTranslate.x + groupTranslate.x;
  var nextDy = childTranslate.y + groupTranslate.y;
  if (isChooserNode(element)) {
    nextDy = snapToVerticalTargets(nextDy, element);
  } else if (element.attr("id") === "wiederholung") {
    nextDx = snapRepeatMarkerTranslateX(element, nextDx, childTranslate.x);
    nextDy = snapElementTranslateYToVerticalTargets(element, nextDy, childTranslate.y);
  } else if (isNoteSymbolElement(element) || isNoteLineControlElement(element)) {
    nextDx = snapNoteSymbolTranslateX(element, nextDx, childTranslate.x);
    nextDy = snapElementTranslateYToVerticalTargets(element, nextDy, childTranslate.y);
  } else {
    nextDy = snapElementTranslateYToVerticalTargets(element, nextDy, childTranslate.y);
  }
  return {
    x: nextDx,
    y: nextDy,
  };
}

function snapSelectionElementsAndKeepSelection(selectionGroup) {
  if (!selectionGroup || selectionGroup !== selections) {
    return;
  }
  var selectedElements = getSelectedElements(selectionGroup);
  var groupTranslate = getElementTranslate(selectionGroup);
  selectedElements.forEach(function (ele) {
    var snappedTranslate = getSnappedTranslateForSelectionElement(ele, groupTranslate);
    ele.transform("t" + snappedTranslate.x + ", " + snappedTranslate.y);
  });
  selectionGroup.transform("t0,0");
  selectionDragState.currentDx = undefined;
  selectionDragState.currentDy = undefined;
  selectionDragState.dragOffsetDx = 0;
  selectionDragState.dragOffsetDy = 0;
  selectionDragState.selectionWasDragged = false;
  selectionDragState.selectionWasCloned = false;
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
      var snappedTranslate = getSnappedTranslateForSelectionElement(ele, groupTranslate);
      ele.transform("t" + snappedTranslate.x + ", " + snappedTranslate.y);
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
      unbindChooserDragForSelection(el);
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
var chooserVerticalSnapOffsets = [-32];
// Eigene vertikale Stufen fuer Notenzeichen:
// 0: eine Position oberhalb der Grundlinie
// 1: Grundlinienposition
// 2: erste Position unterhalb der Grundlinie
// 3: zweite Position unterhalb der Grundlinie
var noteSymbolVerticalSnapOffsets = [17, 32, 47, 62];
var noteSymbolHorizontalSnapOffsetX = 1;
// Eigene vertikale Stufen fuer editierbare Textfelder:
// -100: Titel-Zeile, -32: Chooser-Zeile, danach die normalen Park-/Notenlinien-Ziele.
var editableTextVerticalSnapOffsets = [-100, -32, -2, 15, 31, 42];
// ShortBar ist eine Taktsteuerung und soll nicht auf jede Notenebene springen.
var shortBarVerticalSnapOffsets = [18];
var repeatMarkerVerticalSnapOffsets = [18];
//var verticalSnapOffsets = [-32, -12, 15, 36, 70];

function isNoteSymbolElement(element) {
  if (!element || typeof element.attr !== "function") {
    return false;
  }
  var elementId = element.attr("id");
  return (
    elementId === "tone" ||
    elementId === "bass" ||
    elementId === "slap" ||
    elementId === "tone_muffled" ||
    elementId === "slap_muffled" ||
    elementId === "tone_flam" ||
    elementId === "slap_flam" ||
    elementId === "bass_slap_flam"
  );
}

function isNoteLineControlElement(element) {
  if (!element || typeof element.attr !== "function") {
    return false;
  }
  var elementId = element.attr("id");
  return elementId === "in" || elementId === "out";
}

function getElementTranslateY(element) {
  if (!element || typeof element.transform !== "function") {
    return 0;
  }

  if (element.node && element.node.transform && element.node.transform.baseVal) {
    var consolidatedTransform = element.node.transform.baseVal.consolidate();
    var domMatrix = consolidatedTransform && consolidatedTransform.matrix ? consolidatedTransform.matrix : null;
    if (domMatrix && typeof domMatrix.f === "number") {
      return domMatrix.f;
    }
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

function getShortBarMarkerAnchorY(element) {
  var markerLine = element && element.select ? element.select(".shortbar-marker-line") : null;
  if (!markerLine || typeof markerLine.attr !== "function") {
    return null;
  }
  var explicitAnchorY = Number(element.attr("data-shortbar-anchor-y"));
  if (Number.isFinite(explicitAnchorY)) {
    return explicitAnchorY + getElementTranslateY(element);
  }
  var y1 = Number(markerLine.attr("y1"));
  var y2 = Number(markerLine.attr("y2"));
  if (!Number.isFinite(y1) || !Number.isFinite(y2)) {
    return null;
  }
  return ((y1 + y2) / 2) + getElementTranslateY(element);
}

function getRepeatMarkerDotCenterY(element) {
  if (!element || typeof element.selectAll !== "function") {
    return null;
  }
  var dots = element.selectAll("circle");
  if (!dots || dots.length < 2) {
    return null;
  }
  var yValues = [];
  dots.forEach(function (dot) {
    var cy = Number(dot.attr("cy"));
    if (Number.isFinite(cy)) {
      yValues.push(cy);
    }
  });
  if (!yValues.length) {
    return null;
  }
  var sum = yValues.reduce(function (total, value) {
    return total + value;
  }, 0);
  return (sum / yValues.length) + getElementTranslateY(element);
}

function getRepeatMarkerDotCenterX(element) {
  if (!element || typeof element.selectAll !== "function") {
    return null;
  }
  var dots = element.selectAll("circle");
  if (!dots || !dots.length) {
    return null;
  }
  var firstDotX = Number(dots[0].attr("cx"));
  if (!Number.isFinite(firstDotX)) {
    return null;
  }
  return firstDotX + getElementTranslate(element).x;
}

function getRepeatMarkerHorizontalSnapTargets() {
  var repeatOffsetLeft = 6;
  var repeatOffsetRight = 8;
  return [
    100 - repeatOffsetLeft,
    100 + repeatOffsetRight,
    525 - repeatOffsetLeft,
    525 + repeatOffsetRight,
    950 - repeatOffsetLeft,
    950 + repeatOffsetRight
  ];
}

function snapRepeatMarkerDeltaX(startX, deltaX) {
  if (!Number.isFinite(startX)) {
    return Snap.snapTo(gridSize, deltaX, 50);
  }
  var targetX = startX + deltaX;
  var snapTargets = getRepeatMarkerHorizontalSnapTargets();
  var nearestTargetX = snapTargets[0];
  var smallestDistance = Math.abs(targetX - nearestTargetX);
  snapTargets.forEach(function (candidateX) {
    var candidateDistance = Math.abs(targetX - candidateX);
    if (candidateDistance < smallestDistance) {
      smallestDistance = candidateDistance;
      nearestTargetX = candidateX;
    }
  });
  return nearestTargetX - startX;
}

function getNearestValue(targetValue, snapTargets) {
  if (!Array.isArray(snapTargets) || !snapTargets.length) {
    return targetValue;
  }
  var nearestTarget = snapTargets[0];
  var smallestDistance = Math.abs(targetValue - nearestTarget);
  snapTargets.forEach(function (candidate) {
    var candidateDistance = Math.abs(targetValue - candidate);
    if (candidateDistance < smallestDistance) {
      smallestDistance = candidateDistance;
      nearestTarget = candidate;
    }
  });
  return nearestTarget;
}

function getHorizontalGridOriginX() {
  var configuredOriginX = typeof paletteInsertTargetX !== "undefined"
    ? Number(paletteInsertTargetX)
    : NaN;
  return Number.isFinite(configuredOriginX) ? configuredOriginX : 100;
}

function snapNoteSymbolDeltaX(startX, deltaX) {
  if (!Number.isFinite(startX)) {
    return Snap.snapTo(gridSize, deltaX, 50);
  }
  var snapStepX = Number(gridSize);
  if (!Number.isFinite(snapStepX) || snapStepX <= 0) {
    return deltaX;
  }

  var targetX = startX + deltaX;
  var originX = getHorizontalGridOriginX();
  var adjustedOriginX = originX + noteSymbolHorizontalSnapOffsetX;
  var nearestTargetX = adjustedOriginX + Math.round((targetX - adjustedOriginX) / snapStepX) * snapStepX;
  return nearestTargetX - startX;
}

function getElementSnapReferenceX(element, bbox) {
  if (isNoteSymbolElement(element) || isNoteLineControlElement(element)) {
    return bbox.cx;
  }
  if (element && typeof element.attr === "function" && element.attr("id") === "wiederholung") {
    return getRepeatMarkerDotCenterX(element);
  }
  return bbox.cx;
}

function snapElementTranslateYToVerticalTargets(element, nextTranslateY, currentTranslateY) {
  var bbox = element.getBBox();
  var currentReferenceY = getElementSnapReferenceY(element, bbox);
  if (!Number.isFinite(currentReferenceY)) {
    return nextTranslateY;
  }
  var localReferenceY = currentReferenceY - currentTranslateY;
  var nextReferenceY = localReferenceY + nextTranslateY;
  var snappedReferenceY = snapToVerticalTargets(nextReferenceY, element);
  return nextTranslateY + (snappedReferenceY - nextReferenceY);
}

function snapNoteSymbolTranslateX(element, nextTranslateX, currentTranslateX) {
  var bbox = element.getBBox();
  var currentReferenceX = getElementSnapReferenceX(element, bbox);
  if (!Number.isFinite(currentReferenceX)) {
    return nextTranslateX;
  }
  var snapStepX = Number(gridSize);
  if (!Number.isFinite(snapStepX) || snapStepX <= 0) {
    return nextTranslateX;
  }
  var localReferenceX = currentReferenceX - currentTranslateX;
  var nextReferenceX = localReferenceX + nextTranslateX;
  var originX = getHorizontalGridOriginX();
  var adjustedOriginX = originX + noteSymbolHorizontalSnapOffsetX;
  var snappedReferenceX = adjustedOriginX + Math.round((nextReferenceX - adjustedOriginX) / snapStepX) * snapStepX;
  return nextTranslateX + (snappedReferenceX - nextReferenceX);
}

function snapRepeatMarkerTranslateX(element, nextTranslateX, currentTranslateX) {
  var currentReferenceX = getRepeatMarkerDotCenterX(element);
  if (!Number.isFinite(currentReferenceX)) {
    return nextTranslateX;
  }
  var localReferenceX = currentReferenceX - currentTranslateX;
  var nextReferenceX = localReferenceX + nextTranslateX;
  var snappedReferenceX = getNearestValue(nextReferenceX, getRepeatMarkerHorizontalSnapTargets());
  return nextTranslateX + (snappedReferenceX - nextReferenceX);
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
    var chooserText = element.select ? element.select("text") : null;
    var chooserTextTransform = chooserText && typeof chooserText.transform === "function"
      ? chooserText.transform()
      : null;
    var chooserTextGlobalMatrix = chooserTextTransform && chooserTextTransform.globalMatrix
      ? chooserTextTransform.globalMatrix
      : null;
    if (chooserTextGlobalMatrix && typeof chooserTextGlobalMatrix.f === "number") {
      return chooserTextGlobalMatrix.f;
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
  if (elementId === "shortbar") {
    var shortBarAnchorY = getShortBarMarkerAnchorY(element);
    if (Number.isFinite(shortBarAnchorY)) {
      return shortBarAnchorY;
    }
  }
  if (elementId === "wiederholung") {
    var repeatMarkerAnchorY = getRepeatMarkerDotCenterY(element);
    if (Number.isFinite(repeatMarkerAnchorY)) {
      return repeatMarkerAnchorY;
    }
    return bbox.y + bbox.height - 4;
  }
  return snapReferenceY;
}

function getVerticalSnapTargets(offsetList, includeExtraLine) {
  var lineCount = typeof zeilenAnzahl === "number" && zeilenAnzahl > 0 ? zeilenAnzahl : 10;
  var baseY = typeof staffStartY === "number" ? staffStartY : 172;
  var lineStepY = typeof sheetLineStepY === "number" ? sheetLineStepY : 120;
  var targets = [];
  var resolvedOffsets = Array.isArray(offsetList) && offsetList.length ? offsetList : verticalSnapOffsets;
  var maxLineIndex = includeExtraLine === false ? lineCount - 1 : lineCount;

  for (var lineIndex = 0; lineIndex <= maxLineIndex; lineIndex++) {
    var lineBaseY = typeof getSheetLineBaseY === "function"
      ? (lineIndex < lineCount
        ? getSheetLineBaseY(lineIndex)
        : getSheetLineBaseY(lineCount - 1) + lineStepY)
      : baseY + lineIndex * lineStepY;
    resolvedOffsets.forEach(function (offsetY) {
      targets.push(lineBaseY + offsetY);
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
  var usesShortBarTargets = elementId === "shortbar";
  var usesRepeatMarkerTargets = elementId === "wiederholung";
  var usesChooserTargets = isChooserNode(element);
  var snapTargets =
    usesChooserTargets
      ? getVerticalSnapTargets(chooserVerticalSnapOffsets, false)
      : usesNoteSymbolTargets
      ? getVerticalSnapTargets(noteSymbolVerticalSnapOffsets)
      : usesShortBarTargets
        ? getVerticalSnapTargets(shortBarVerticalSnapOffsets)
      : usesRepeatMarkerTargets
        ? getVerticalSnapTargets(repeatMarkerVerticalSnapOffsets)
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

function getSnappedDragDx(element, deltaX) {
  var rawDeltaX = Number(deltaX);
  if (!Number.isFinite(rawDeltaX)) {
    rawDeltaX = 0;
  }

  if (element && typeof element.attr === "function" && element.attr("id") === "wiederholung") {
    return snapRepeatMarkerDeltaX(Number(element.data("startSnapX")), rawDeltaX);
  }
  if (isNoteSymbolElement(element) || isNoteLineControlElement(element)) {
    return snapNoteSymbolDeltaX(Number(element.data("startSnapX")), rawDeltaX);
  }

  return Snap.snapTo(gridSize, rawDeltaX, 50);
}

function getSnappedDragDy(element, deltaY) {
  var rawDeltaY = Number(deltaY);
  if (!Number.isFinite(rawDeltaY)) {
    rawDeltaY = 0;
  }
  return snapDeltaWithYOffset(element.data("startSnapY"), rawDeltaY, element);
}

function applyDragTransform(element, deltaX, deltaY) {
  element.attr({
    transform:
      element.data("origTransform") +
      (element.data("origTransform") ? "T" : "t") +
      [deltaX, deltaY],
  });
}

function snapDraggedElementToFinalPosition(element) {
  var rawDeltaX = Number(element.data("currentDragDx"));
  var rawDeltaY = Number(element.data("currentDragDy"));
  if (!Number.isFinite(rawDeltaX)) {
    rawDeltaX = 0;
  }
  if (!Number.isFinite(rawDeltaY)) {
    rawDeltaY = 0;
  }
  if (rawDeltaX === 0 && rawDeltaY === 0) {
    return { dx: 0, dy: 0 };
  }

  var snappedDeltaX = getSnappedDragDx(element, rawDeltaX);
  var snappedDeltaY = getSnappedDragDy(element, rawDeltaY);
  applyDragTransform(element, snappedDeltaX, snappedDeltaY);
  return { dx: snappedDeltaX, dy: snappedDeltaY };
}

function getEditorClipboardShortcut(event) {
  if (!event || !event.metaKey) {
    return "";
  }
  var keyValue = String(event.key || "").toLowerCase();
  if (keyValue === "c" || event.code === "KeyC") {
    return "copy";
  }
  if (keyValue === "x" || event.code === "KeyX") {
    return "cut";
  }
  if (keyValue === "v" || event.code === "KeyV") {
    return "paste";
  }
  return "";
}

function shouldIgnoreEditorClipboardEvent(event) {
  var targetName = event && event.target && event.target.tagName
    ? event.target.tagName.toLowerCase()
    : "";
  return targetName === "input" || targetName === "textarea" || targetName === "select";
}

function getSelectedElementMarkup() {
  if (!selections) {
    return "";
  }
  var markup = "";
  getSelectedElements(selections).forEach(function (ele) {
    markup += ele.toString();
  });
  return markup;
}

function writeEditorClipboard(markup, sourceAction) {
  editorClipboardMarkup = markup || "";
  editorClipboardSourceAction = sourceAction || "";
  var payload = createEditorClipboardPayload(editorClipboardMarkup, editorClipboardSourceAction);
  try {
    window.localStorage.setItem(editorClipboardStorageKey, editorClipboardMarkup);
    window.localStorage.setItem(editorClipboardPayloadStorageKey, JSON.stringify(payload));
  } catch (error) {
    // localStorage can be unavailable in private or restricted browser contexts.
  }
  writeEditorClipboardToSystemClipboard(payload);
}

function readEditorClipboard() {
  var storedPayload = readStoredEditorClipboardPayload();
  if (storedPayload && storedPayload.markup) {
    editorClipboardMarkup = storedPayload.markup;
    editorClipboardSourceAction = storedPayload.sourceAction || "copy";
    return editorClipboardMarkup;
  }
  if (editorClipboardMarkup) {
    return editorClipboardMarkup;
  }
  try {
    editorClipboardMarkup = window.localStorage.getItem(editorClipboardStorageKey) || "";
  } catch (error) {
    editorClipboardMarkup = "";
  }
  return editorClipboardMarkup;
}

function createEditorClipboardPayload(markup, sourceAction) {
  return {
    app: "BaraBeat-Studio",
    type: editorClipboardSystemType,
    version: 1,
    createdAt: Date.now(),
    sourceTabId: editorClipboardTabId,
    sourceAction: sourceAction || "copy",
    markup: markup || "",
  };
}

function parseEditorClipboardPayload(text) {
  if (!text || typeof text !== "string") {
    return null;
  }
  try {
    var payload = JSON.parse(text);
    if (
      payload &&
      payload.type === editorClipboardSystemType &&
      typeof payload.markup === "string" &&
      payload.markup
    ) {
      return payload;
    }
  } catch (error) {
    return null;
  }
  return null;
}

function readStoredEditorClipboardPayload() {
  try {
    var payloadText = window.localStorage.getItem(editorClipboardPayloadStorageKey) || "";
    var payload = parseEditorClipboardPayload(payloadText);
    if (payload) {
      return payload;
    }

    var legacyMarkup = window.localStorage.getItem(editorClipboardStorageKey) || "";
    if (legacyMarkup) {
      return createEditorClipboardPayload(legacyMarkup, editorClipboardSourceAction || "copy");
    }
  } catch (error) {
    return null;
  }
  return null;
}

function writeEditorClipboardToSystemClipboard(payload) {
  if (
    !payload ||
    !payload.markup ||
    typeof navigator === "undefined" ||
    !navigator.clipboard ||
    typeof navigator.clipboard.writeText !== "function"
  ) {
    return;
  }

  var payloadText = JSON.stringify(payload);
  navigator.clipboard.writeText(payloadText).catch(function () {
    // The local editor clipboard is still available when browser permissions block this.
  });
}

function readEditorClipboardFromSystemClipboard() {
  if (
    typeof navigator === "undefined" ||
    !navigator.clipboard ||
    typeof navigator.clipboard.readText !== "function"
  ) {
    return Promise.resolve(null);
  }

  return navigator.clipboard.readText()
    .then(parseEditorClipboardPayload)
    .catch(function () {
      return null;
    });
}

function copySelectedElementsToEditorClipboard() {
  var markup = getSelectedElementMarkup();
  if (!markup) {
    return false;
  }
  writeEditorClipboard(markup, "copy");
  return true;
}

function offsetPastedElements(pastedSelectableElements, offsetX, offsetY) {
  pastedSelectableElements.forEach(function (ele) {
    var currentTranslate = getElementTranslate(ele);
    ele.transform("t" + (currentTranslate.x + offsetX) + ", " + (currentTranslate.y + offsetY));
  });
}

function selectPastedElements(pastedSelectableElements) {
  if (!pastedSelectableElements.length) {
    return;
  }

  selections = s.g();
  pastedSelectableElements.forEach(function (ele) {
    unbindChooserDragForSelection(ele);
  });
  selections.add(pastedSelectableElements);
  selections.drag(sel_move, sel_start, stop_m);
  selections.attr({ opacity: 0.5 });
  resetSelectionDragState();
}

function clearSelectionBox() {
  if (box) {
    box.remove();
    box = null;
  }
}

function pasteActiveCopiedSelection() {
  if (!selections) {
    return false;
  }

  var sourceElements = [];
  getSelectedElements(selections).forEach(function (ele) {
    sourceElements.push(ele);
  });
  if (!sourceElements.length) {
    return false;
  }

  if (typeof recordHistorySnapshot === "function") {
    recordHistorySnapshot();
  }

  clearSelectionBox();
  UnGroup();

  var pastedSelectableElements = sourceElements.map(function (ele) {
    return appendBoundClone(ele);
  });
  offsetPastedElements(pastedSelectableElements, gridSize * 2, 0);

  selectPastedElements(pastedSelectableElements);
  return true;
}

function cutSelectedElementsToEditorClipboard() {
  var markup = getSelectedElementMarkup();
  if (!markup) {
    return false;
  }
  writeEditorClipboard(markup, "cut");
  if (typeof recordHistorySnapshot === "function") {
    recordHistorySnapshot();
  }
  getSelectedElements(selections).forEach(function (ele) {
    ele.remove();
  });
  selections.remove();
  selections = null;
  resetSelectionDragState();
  return true;
}

function pasteEditorClipboardElements(markupOverride, options) {
  var pasteOptions = options || {};
  var markup = typeof markupOverride === "string" ? markupOverride : readEditorClipboard();
  var sourceAction = pasteOptions.sourceAction || editorClipboardSourceAction;
  if (!markup || typeof Snap === "undefined" || !s) {
    return false;
  }
  if (!pasteOptions.skipActiveSelection && sourceAction === "copy" && pasteActiveCopiedSelection()) {
    return true;
  }

  var pastedElements;
  try {
    pastedElements = Snap.parseStr(markup);
  } catch (error) {
    return false;
  }
  var pastedSelectableElements = [];
  if (typeof pastedElements.selectAll === "function") {
    pastedElements.selectAll(selectableElementSelector).forEach(function (ele) {
      pastedSelectableElements.push(ele);
    });
  }
  var shouldOffsetPaste = sourceAction === "copy" && selections && pastedSelectableElements.length;
  if (typeof recordHistorySnapshot === "function") {
    recordHistorySnapshot();
  }
  if (typeof resetSelectionArtifacts === "function") {
    resetSelectionArtifacts();
  }
  s.append(pastedElements);
  if (typeof bindLoadedScoreElements === "function") {
    bindLoadedScoreElements();
  }
  if (shouldOffsetPaste) {
    offsetPastedElements(pastedSelectableElements, gridSize * 2, 0);
  }
  selectPastedElements(pastedSelectableElements);
  return true;
}

function pasteEditorClipboardElementsFromSystemOrLocal() {
  readEditorClipboardFromSystemClipboard().then(function (payload) {
    if (payload && payload.markup) {
      editorClipboardMarkup = payload.markup;
      editorClipboardSourceAction = payload.sourceAction || "copy";
      pasteEditorClipboardElements(payload.markup, {
        sourceAction: editorClipboardSourceAction,
        skipActiveSelection: payload.sourceTabId !== editorClipboardTabId,
      });
      return;
    }

    var storedPayload = readStoredEditorClipboardPayload();
    if (storedPayload && storedPayload.markup) {
      editorClipboardMarkup = storedPayload.markup;
      editorClipboardSourceAction = storedPayload.sourceAction || "copy";
      pasteEditorClipboardElements(storedPayload.markup, {
        sourceAction: editorClipboardSourceAction,
        skipActiveSelection: storedPayload.sourceTabId !== editorClipboardTabId,
      });
      return;
    }

    pasteEditorClipboardElements();
  });
}

function captureHistoryForEditorDrag(dragElement) {
  if (!dragElement || typeof dragElement.data !== "function" || dragElement.data("historyCaptured")) {
    return;
  }
  if (typeof recordHistorySnapshot === "function") {
    recordHistorySnapshot();
  }
  dragElement.data("historyCaptured", true);
}

function entfernen(event) {
  //(event.key + " " + event.metaKey)
  var shortcutAction = getEditorClipboardShortcut(event);
  if (shouldIgnoreEditorClipboardEvent(event) || !shortcutAction) {
    return;
  }

  var handledClipboardAction = false;
  if (shortcutAction === "copy") {
    handledClipboardAction = copySelectedElementsToEditorClipboard();
  } else if (shortcutAction === "cut") {
    handledClipboardAction = cutSelectedElementsToEditorClipboard();
  } else if (shortcutAction === "paste") {
    pasteEditorClipboardElementsFromSystemOrLocal();
    handledClipboardAction = true;
  }

  if (handledClipboardAction) {
    if (event && typeof event.preventDefault === "function") {
      event.preventDefault();
    }
    if (event && typeof event.stopPropagation === "function") {
      event.stopPropagation();
    }
  }
}

function sel_move(dx, dy) {
  var rawDx = Number(dx);
  var rawDy = Number(dy);
  if (!Number.isFinite(rawDx)) {
    rawDx = 0;
  }
  if (!Number.isFinite(rawDy)) {
    rawDy = 0;
  }
  var isSelectionGroup = this === selections;

  this.selectAll(".instrument-chooser, .function-chooser").forEach(function (chooserElement) {
    suppressChooserClickAfterDrag(chooserElement);
  });

  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    captureHistoryForEditorDrag(this);
    this.data("alreadyCloned", true);
    if (isSelectionGroup) {
      selectionDragState.selectionWasCloned = true;
    }
    getSelectedElements(this).forEach(function (ele) {
      appendBoundClone(ele);
    }.bind(this));
  }

  if ((rawDx !== 0 || rawDy !== 0)) {
    captureHistoryForEditorDrag(this);
  }
  applyDragTransform(this, rawDx, rawDy);
  // dx, dy sind die Werte um die die Gruppe verschoben wurde.
  this.data("currentDragDx", rawDx);
  this.data("currentDragDy", rawDy);
  selectionDragState.currentDx = rawDx;
  selectionDragState.currentDy = rawDy;
  if (isSelectionGroup && (rawDx !== 0 || rawDy !== 0)) {
    selectionDragState.selectionWasDragged = true;
  }
}

function move(dx, dy) {
  var rawDx = Number(dx);
  var rawDy = Number(dy);
  if (!Number.isFinite(rawDx)) {
    rawDx = 0;
  }
  if (!Number.isFinite(rawDy)) {
    rawDy = 0;
  }
  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    captureHistoryForEditorDrag(this);
  } else if (rawDx !== 0 || rawDy !== 0) {
    captureHistoryForEditorDrag(this);
  }
  this.data("currentDragDx", rawDx);
  this.data("currentDragDy", rawDy);
  applyDragTransform(this, rawDx, rawDy);
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
  this.data("historyCaptured", false);
  this.data("currentDragDx", 0);
  this.data("currentDragDy", 0);
  if (this === selections) {
    selectionDragState.currentDx = undefined;
    selectionDragState.currentDy = undefined;
    selectionDragState.selectionWasDragged = false;
    selectionDragState.selectionWasCloned = false;
    installSelectionDragReleaseFallback(this);
  }

  let bbox = this.getBBox();
  this.data("startX", bbox.x);
  this.data("startY", bbox.y);
  var startReferenceY = getElementSnapReferenceY(this, bbox);
  var isSelectionGroup = this === selections;
  this.data("startSnapY", isSelectionGroup ? snapToVerticalTargets(startReferenceY, this) : startReferenceY);
  if (this.attr("id") === "wiederholung" || isNoteSymbolElement(this) || isNoteLineControlElement(this)) {
    this.data("startSnapX", getElementSnapReferenceX(this, bbox));
  }
}

function stop_m() {
  if (this !== selections) {
    snapDraggedElementToFinalPosition(this);
    return;
  }

  // Die Verschiebungswerte werden mit jeder Verschiebung der Gruppe addiert, bis die Gruppe aufgelöst wird.
  var currentDx = Number.isFinite(selectionDragState.currentDx) ? selectionDragState.currentDx : 0;
  var currentDy = Number.isFinite(selectionDragState.currentDy) ? selectionDragState.currentDy : 0;
  selectionDragState.dragOffsetDx += currentDx;
  selectionDragState.dragOffsetDy += currentDy;

  if (this === selections) {
    removeSelectionDragReleaseFallback();
    if (selectionShouldUngroupAfterDrag(this)) {
      snapSelectionElementsAndKeepSelection(this);
    }
  }
}
