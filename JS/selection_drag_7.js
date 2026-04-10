// JavaScript Document

// Die auswählbaren Elemente müssen der Klasse .shp angehören.
// Entweder mit element.addClass('shp'); oder .attr({class: 'shp'}); hinzufügen

dx2 = 0;
dy2 = 0;
dxx1 = 0;
dyy1 = 0;

function UnGroup() {
  if (selections) {
    /*Prüfen ob die Auswahl bewegt wurde.
    Wenn nicht, drag-Methode wieder hinzufügen und <br>
    Elemente wieder zu s hinzugügen. */

    chd = selections.selectAll(".shp, #instrumentChooser");
    if (typeof dx1 == "undefined") {
      chd.forEach(function (ele) {
        ele.drag(move, sel_start, stop_m);
        s.append(ele);
      });
    }
    dx2 = dx2 + dxx1;
    dy2 = dy2 + dyy1;
    chd.forEach(function (ele) {
      transf = ele.matrix.toTransformString();

      /*Prüfen ob die Auswahl zum ersten Mal verschoben wird.
      Dann ist trans undefiniert */
      if (transf === "") {
        dx3 = dx2;
        dy3 = dy2;
        ele.drag(sel_move, sel_start, stop_m);
        s.append(ele);
      } else {
        transf = transf.split(",");
        dx3 = dx2 + Number(transf[0].substr(1));
        dy3 = dy2 + Number(transf[1]);
      }
      ele.transform("t" + dx3 + ", " + dy3);
      ele.drag(move, sel_start, stop_m);
      s.append(ele);
    });
  }
  dx2 = dx2 - dxx1;
  dy2 = dy2 - dyy1;
  dx1 = 0;
  dy1 = 0;
  dxx1 = 0;
  dyy1 = 0;
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
  g_data = [];
  idx = 0;
  key_ged = event.key;
  s.selectAll(".shp, #instrumentChooser").forEach(function (el) {
    var mybounds = el.getBBox();
    if (Snap.path.isBBoxIntersect(mybounds, bounds)) {
      idx++;
      g_data.push(el);
    }
  });
  if (idx > 0) {
    selections = s.g();
    g_data.forEach(function (el) {
      el.undrag();
    });
    selections.add(g_data);
    selections.drag(sel_move, sel_start, stop_m);
    selections.attr({ opacity: 0.5 });
  }
}

var gridSize = 850 / 26 / 2;
var gridSize1 = 10;

function entfernen() {
  let key_ged = event.key;

  //(event.key + " " + event.metaKey)
  let key_ged_meta = event.metaKey;
  if (key_ged == "x" && key_ged_meta) {
    //	if(key_ged && key_ged_meta){
    ele2 = selections.selectAll(".shp, #instrumentChooser");

    ele2.forEach(function (ele) {
      ele2.remove();
    });
  }
}

function sel_move(dx, dy) {
  var dx = Snap.snapTo(gridSize, dx, 50);
  var dy = Snap.snapTo(gridSize1, dy, 50);

  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    this.data("alreadyCloned", true);
    //selection = this.clone();
    chd1 = this.selectAll(".shp, #instrumentChooser");

    chd1.forEach(function (ele) {
      ele2 = ele.clone();
      id_alt = ele.attr("id");
      ele2.attr({ id: id_alt });
      // if(ele2.attr("id")=="wiederholung") {
      //    ele2.drag(move,sel_start,edit_text_wz_1);
      //    ele2.dblclick(edit_text_wz);
      //  };
      if (ele2.attr("id") == "edit_text") {
        ele2.dblclick(edit_text);
      }
      ele2.drag(move, sel_start, stop_m);
      //ele2.attr({"fill": "red"});
      s.append(ele2);
    });
  }

  this.attr({
    transform:
      this.data("origTransform") +
      (this.data("origTransform") ? "T" : "t") +
      [dx, dy],
  });
  // dx, dy sind die Werte um die die Gruppe verschoben wurde.
  dx1 = dx;
  dy1 = dy;
}

function move(dx, dy) {
  var dx = Snap.snapTo(gridSize, dx, 50);
  var dy = Snap.snapTo(gridSize1, dy, 50);
  this.attr({
    transform:
      this.data("origTransform") +
      (this.data("origTransform") ? "T" : "t") +
      [dx, dy],
  });
  if (this.data("cloneThisDrag") && !this.data("alreadyCloned")) {
    this.data("alreadyCloned", true);

    //  this.data("alreadyCloned", true);

    if (this.attr("id") != "instrumentChooser") {
      ele1 = this.clone();

      if (this.attr("id") == "wiederholung") {
        ele1.dblclick(edit_text_wz);
      }
    }

    /*if(this.attr("id")=="instrumentChooser") {
			ax = this.data('startX');
			ay = this.data('startY') + 13;
			let chooser = createInstrumentChooser(s, ax, ay).attr({ id: "instrumentChooser" });
		}*/

    if (this.attr("id") == "instrumentChooser") {
      ax = this.data("startX");
      ay = this.data("startY") + 13;

      let altesTextElement = this.select("text");
      let alterText = altesTextElement.attr("text");
      let alteFarbe = altesTextElement.attr("fill");

      //let chooser = createInstrumentChooser(s, ax, ay).attr({ id: "instrumentChooser" });
      let chooser = createInstrumentChooser(s, ax, ay, alterText, alteFarbe).attr({
        class: "shp",
        id: "instrumentChooser",
      });

      /*  chooser.select("text").attr({
	        text: alterText,
	        fill: "#333"
    });
		*/
    } 

    id_alt = this.attr("id");
    class_alt = this.attr("class");
    //ele1.attr({ id: id_alt, class: class_alt });
    if (this.attr("id") == "edit_text") {
      ele1.dblclick(edit_text);
    }
    ele1.drag(move, sel_start);
  }
}

function start(event) {
  key_ged = event.key;
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
}

function stop_m() {
  // Die Verschiebungswerte werden mit jeder Verschiebung der Gruppe addiert, bis die Gruppe aufgelöst wird.
  dxx1 += dx1;
  dyy1 += dy1;
}
