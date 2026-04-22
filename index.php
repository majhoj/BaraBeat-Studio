<?php
$jsSnap = @filemtime(__DIR__ . '/JS/snapNEU.svg.js') ?: 1;
$jsJq = @filemtime(__DIR__ . '/JS/jquery.min.js') ?: 1;
$jsSel = @filemtime(__DIR__ . '/JS/selection_drag_7.js') ?: 1;
$jsFn = @filemtime(__DIR__ . '/JS/functions.js') ?: 1;
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<meta name="viewport" content="initial-scale=1.0">
<title><BaraBeat-Studio></title>
<script src="JS/snapNEU.svg.js?v=<?php echo $jsSnap; ?>"></script>
<script src="JS/jquery.min.js?v=<?php echo $jsJq; ?>"></script>
<script src="JS/selection_drag_7.js?v=<?php echo $jsSel; ?>"></script>
<script src="JS/functions.js?v=<?php echo $jsFn; ?>"></script>
</head>

<body style="margin-top: 20px;">
<?php
$file_name = $_GET["file"];
$datei = "<script> datei_name = '" . $file_name . "' ; </script>";
echo $datei;
?>


    <div  style="position: fixed; top: 0; ">
        <form action=""  name="uploadForm">
            <span id="auswahl" name="auswahl"></span>
            <input type="hidden" size="40" id="iofield" name="iofield" />
            <input type="button" id="button" value="Datei speichern" />
            <input type="button"  id="button2" value="Als SVG speichern" />
            <input type="button"  id="button3" value="Noten lesen" />
            <input type="button"  id="button4" value="Binäres Notenblatt" />
            <input type="button"  id="button5" value="Tenäres Notenblatt" />
            <input type="button"  id="button6" value="Scroll" />
            <input type="button"  id="button7" value="Instrument" />
        </form>

    </div>

<script>
var edit_title;
var edit_text;
var y = 172;
var z = 202;
var k = 0;
var yN = 172;
var gridSize = (850 / 34) / 2;
var gridSizeY = 5;
var gridSizeX = 29;
var repeatMarkerGridOffsetX = 24;
var paletteOriginX;
var paletteOriginY;
var paletteFrame;
var ton;
var bass;
var slap;
var flam_ton;
var flam_slap;
var flam_bass_slap;
var ton_g;
var slap_g;
var In;
var Out;
var text_z_g;
var repeatMarkerGroup;
var ton_c;
var bass_c;
var slap_c;
var flam_ton_c;
var flam_slap_c;
var flam_bass_slap_c;
var ton_g_c;
var slap_g_c;
var In_c;
var Out_c;
var wz_g_c;
var paletteGroup;
var isDragging = false;
var paletteDragDeltaX = 0;
var paletteDragDeltaY = 0;
var paletteOffsetX = 0;
var paletteOffsetY = 0;
var textTouchStartX;
var textTouchStartY;
var textTouchEndX;
var textTouchEndY;
var loadedTitle = '';
var chooser;
var x;
var e;
var slap_a;
var slap_b;
var flam_ton_a;
var flam_ton_b;
var slap_0;
var slap_a1;
var slap_a2;
var slap_b1;
var slap_b2;
var flam_bass_0;
var flam_bass;
var slap_a3;
var slap_a4;
var ton_g_a;
var ton_g_b;
var slap_a5;
var slap_a6;
var slap_g_b;
var in_c;
var in_a;
var in_b;
var out_c;
var out_a;
var out_b;
var dt;
var text_z;
var text_z1;
var repeatMarkerHitbox;
var repeatMarkerDotTop;
var repeatMarkerDotBottom;
var repeatMarkerCountText;
var tx;
var ty;
var bx;
var by;
var sx;
var sy;
var ftx;
var fty;
var fsx;
var fsy;
var sgx;
var sgy;
var ix;
var iy;
var ox;
var oy;
var px;
var py;
var insertTone;
var insertBass;
var insertSlap;
var insertMuffledTone;
var insertMuffledSlap;
var insertFlamTone;
var insertFlamSlap;
var insertFlamBassSlap;
var insertInMarker;
var insertOutMarker;
var captureTextTouchStart;
var handleTextTouchEnd;
var insertTextField;
var cycleRepeatCount;
var insertRepeatMarker;

const elem = document.querySelector('body');
elem.addEventListener("keydown", shadow_end );
const elem1 = document.querySelector('body');
elem1.addEventListener("keydown", start);
const elem2 = document.querySelector('body');
elem2.addEventListener("keydown", entfernen);
/*
elem.addEventListener ("keydown", function (event) {
	console.log (event.key + " " + event.metaKey)
});
*/

// Funktionen
edit_title = function(){
    const text_a = this.attr('text');
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {return;}
    this.attr({text: text_i});
}

edit_text = function(){
    const text_a = this.attr('text');
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {return;}
    this.attr({text: text_i});
}


// Zeichenfläche und Titel festlegen
var s = Snap(1050,1480).attr({ id: "myRect1" });
var canv = s.rect(0,0,1050,1480).attr({fill:"white", stroke:"black", strokeWidth: 0.5, opacity: 0.300, id: "myRect2" });
canv.drag(shadow_move, shadow_start, shadow_end);

function clear_all()
{
    const elementsToClear = s.selectAll("#notenlinien, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung");
    elementsToClear.forEach(function(el) {
        el.remove();
    });
}

var titel = s.text(100, y-100, "Enter the name of the Rhythm").attr({ id: 'basis', 'font-size':24, 'font-family':'sans-serif', 'font-weight':'bold' });
titel.dblclick(edit_title);

let zeilenAnzahl = 10;
let rhythm = "binaer";

// Notenlinien anlegen für binären Rhythmus
function viererNoten()
    {
    rhythm = 'binaer';
        //Grid festlegen
    gridSize = (850/34)/2;
    gridSizeY = 5;
    gridSizeX = 29;
    repeatMarkerGridOffsetX = 24;

    clear_all();
    //titel.attr({text: "Enter the name of the Rhythm"});

    const countSyllables = ["Ja","Pi","Du","Pa"];
    k = 0;

    //let zeilenAnzahl = 10;

    for (var j=0; j < zeilenAnzahl; j++){
        s.rect(100, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        s.rect(525, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        s.rect(950, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
		s.text(90, yN+30+j*120, j+1).attr({ id: 'notenlinien', 'font-size':24, 'font-family':'sans-serif', 'font-weight':'bold', 'fill':"#a0a0a0", 'text-anchor':'end' });
        for (var i=1; i < 34; i++){
            const x = 100 + (850/34)*i;
            if (i != 17){
	               s.text( x-3, yN+j*120-4, countSyllables[k]).attr({ id: 'notenlinien', 'font-size':10});
	               k++;
	                if (k==4){k=0}
	                s.rect(x, yN+j*120, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (i == 1 || i == 5 || i == 9 || i == 13 || i == 18 || i == 22 || i == 26 || i == 30){
				let beatNumber = Math.trunc((i+4) / 4);
					if(beatNumber>4){beatNumber-=4};
					s.text( x-3, yN+j*120-14, beatNumber).attr({ id: 'notenlinien', 'font-size':10});
	                const b = (850/34)*3;
	                s.rect(x, yN+j*120, b, 1.5).attr({ id: "notenlinien" });
	                s.rect(x, yN+j*120+5, b, 1.5).attr({ id: "notenlinien" });
            }
        }
    }
//    let chooser = createInstrumentChooser(s, 125, 140).attr({ class: "shp", id: "instrumentChooser" });
}

function dreierNoten(){
    rhythm = 'tenaer';
  //Grid festlegen
    gridSize = (850/26)/2;
    gridSizeX = 34; // Verschiebung der Noten in X-Richtung
    repeatMarkerGridOffsetX = 26; // Verschiebung der Wiederholung in X-Richtung
    gridSizeY = 5; // Verschiebung der Noten in Y-Richtung




  clear_all();
   const countSyllables = ["Ja","Pi","Du"];
   k = 0;
    //titel.attr({text: "Enter the name of the Rhythm"});

     for (var j=0; j < 10; j++){
        s.rect(100, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        s.rect(525, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        s.rect(950, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
		s.text(90, yN+30+j*120, j+1).attr({id: "notenlinien", 'font-size':24, 'font-family':'sans-serif', 'font-weight':'bold', 'fill':"#a0a0a0", 'text-anchor':'end' });
        for (var i=1; i < 26; i++){
            const x = 100 + (850/26)*i;
            if (i != 13){
	               s.text( x-3, yN+j*120-4, countSyllables[k]).attr({id: "notenlinien", 'font-size':10});
	               k++;
	                if (k==3){k=0}
	                s.rect(x, yN+j*120, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (i == 1 || i == 4 || i == 7 || i == 10 || i == 14 || i == 17 || i == 20 || i == 23){
				let beatNumber = Math.trunc((i+3) / 3);
					if(beatNumber>4){beatNumber-=4};
					s.text( x-3, yN+j*120-16, beatNumber).attr({id: "notenlinien", 'font-size':10});
	                const b = (850/39)*3;
	                s.rect(x, yN+j*120, b, 1.5).attr({ id: "notenlinien" });
	                s.rect(x, yN+j*120+5, b, 1.5).attr({ id: "notenlinien" });
            }
        }
    }
}

// Noten zeichnen und initialisieren

//	Anfangskoordinaten
    paletteOriginX = 33, paletteOriginY = z-30;

 //	Kartusche
    paletteFrame = s.rect(paletteOriginX-12,paletteOriginY-14,26,262,3,3).attr({fill:"lightgrey", stroke:"black", strokeWidth: 0.5 });

//	Tone
    ton = s.circle(paletteOriginX+1,paletteOriginY+1,7);

//	Bass
    x = paletteOriginX-6; y = paletteOriginY+15;
    bass = s.rect(x+1,y,12,12);

 //	Slap
    x = paletteOriginX-5, y = paletteOriginY+47;
   // slap = s.polygon(x,y,x+8,y-14,x+16,y);
   	slap_c = s.rect(x,y-12,12,12).attr({ opacity: 0.001 })
   	slap_a = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	  slap_b = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	  slap = s.g(slap_a,slap_b,slap_c);

 //	Flam Ton
    x = paletteOriginX+4, y = paletteOriginY+62;
	  flam_ton_a = s.circle(x,y,6).attr({fill:"white", stroke:"black", strokeWidth: 2 });
    x = paletteOriginX-2;
	flam_ton_b = s.circle(x,y,6).attr({fill:"black", stroke:"black", strokeWidth: 2 });
	flam_ton = s.g(flam_ton_a,flam_ton_b);


//	Flam Slap
  x = paletteOriginX-8, y = paletteOriginY+87;
   // slap = s.polygon(x,y,x+8,y-14,x+16,y);
    slap_0 = s.rect(x,y-12,20,12).attr({ opacity: 0.001 })
    slap_a1 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a2 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	x = paletteOriginX-2;
	slap_b1 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_b2 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	flam_slap = s.g(slap_0,slap_a1,slap_a2,slap_b1,slap_b2);

//	Flam Bass_Slap
	x = paletteOriginX-8; y = paletteOriginY+95;
	flam_bass_0 = s.rect(x,y-12,12,12).attr({ opacity: 0.001 })
	flam_bass= s.rect(x+1,y,12,12);
	x = paletteOriginX-2; y = paletteOriginY+107;
	slap_a3 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a4 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	flam_bass_slap = s.g(flam_bass_0,flam_bass,slap_a3,slap_a4).attr({ fill:"white", stroke:"black", strokeWidth: 2 });

//	Tone gedämpft
	x=paletteOriginX-5; y = paletteOriginY+125;
  ton_g_c = s.rect(x,y-12,12,14).attr({ opacity: 0.001 })
 	x=paletteOriginX-50; y = paletteOriginY-88;
	ton_g_a = ton.clone().attr({transform: "t" + 0 + "," + 120});
	x=paletteOriginX-6; y = paletteOriginY+130;
	ton_g_b = s.line(x,y,x+15,y).attr({ stroke:"black", strokeWidth: 2 });
  ton_g = s.g(ton_g_a,ton_g_b,ton_g_c);

//	Slap gedämpft
 	x=paletteOriginX-5; y = paletteOriginY+147;
  slap_g_c = s.rect(x,y-12,12,14).attr({ opacity: 0.001 })
	slap_a5 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a6 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	x=paletteOriginX-6; y = paletteOriginY+150;
	slap_g_b = s.line(x,y,x+15,y).attr({ stroke:"black", strokeWidth: 2 });
  slap_g = s.g(slap_a5,slap_a6,slap_g_b,slap_g_c);

//	In
  x = paletteOriginX+1; y = paletteOriginY+156;
	in_c = s.rect(x-6,y,12,20).attr({ opacity: 0.001})
  in_a = s.line(x,y,x,y+12).attr({ stroke:"black", strokeWidth: 3 });
  x = paletteOriginX-5; y=paletteOriginY+168;
  in_b = s.polygon(x,y,x+6,y+7,x+12,y);
  In = s.g(in_a,in_b,in_c);

//	Out
  x = paletteOriginX+1; y = paletteOriginY+185;
	out_c = s.rect(x-6,y-8,12,20).attr({ opacity: 0.001 })
  out_a = s.line(x,y,x,y+12).attr({ stroke:"black", strokeWidth: 3 });
  x = paletteOriginX-5;
  out_b = s.polygon(x,y,x+6,y-7,x+12,y);
  Out = s.g(out_a,out_b,out_c);

//	Text
  x = paletteOriginX-6, y= paletteOriginY +230;
  dt = s.rect(x,y-26,14,15).attr({fill:"white", stroke:"black", strokeWidth: 1 });
  text_z = s.line(x+3,y-21,x+11,y-21).attr({ stroke:"black", strokeWidth: 2.5 });
  text_z1 = s.line(x+7,y-21,x+7,y-14).attr({ stroke:"black", strokeWidth: 2.5 });
  text_z_g= s.g(dt,text_z1,text_z);

	y+=20;

//	Wiederholungszeichen
	repeatMarkerHitbox = s.rect(paletteOriginX-4,y-27,10,20).attr({ opacity: 0.001 })
  repeatMarkerDotTop = s.circle(paletteOriginX+1,paletteOriginY+228,2.5);
	repeatMarkerDotBottom = s.circle(paletteOriginX+1,paletteOriginY+236,2.5);
  repeatMarkerCountText = s.text(paletteOriginX+1, paletteOriginY+252, " ").attr({ 'font-size':12, 'font-family':'sans-serif', 'font-weight':'bold', 'text-anchor':'middle' });
	repeatMarkerGroup= s.g(repeatMarkerHitbox,repeatMarkerDotTop,repeatMarkerDotBottom,repeatMarkerCountText);


  // Legende schreiben
  
  function addLegendEntry(symbol, label, symbolX, symbolY, labelOffsetX, labelOffsetY) {
    const legendClone = symbol.clone().attr({id: "basis", transform: "t" + symbolX + "," + symbolY});
    s.text(symbolX + labelOffsetX, symbolY + labelOffsetY, label).attr({
      id: "basis",
      'font-size':15,
      'font-family':'sans-serif'
    });
    return legendClone;
  }
  ton_c = addLegendEntry(ton, "Tone", 92, 1166, 45, 178);
  bass_c = addLegendEntry(bass, "Bass", 157, 1146, 46, 198);
  slap_c = addLegendEntry(slap, "Slap/Glocke", 222, 1126, 45, 218);
  flam_ton_c = addLegendEntry(flam_ton, "Flam mit Tones", 337, 1105, 49, 240);
  flam_slap_c = addLegendEntry(flam_slap, "Flam mit Slaps", 475, 1087, 49, 259);
  flam_bass_slap_c = addLegendEntry(flam_bass_slap, "Flam mit Bass und Slaps", 613, 1069, 49, 279);

  ton_g_c = addLegendEntry(ton_g, "gedämpfter Tone", 92, 1078, 48, 299);
  slap_g_c = addLegendEntry(slap_g, "gedämpfter Slap", 240, 1058, 48, 319);
  In_c = addLegendEntry(In, "In", 382, 1034, 44, 343);
  Out_c = addLegendEntry(Out, "Out", 424, 1011, 44, 366);
  wz_g_c = addLegendEntry(repeatMarkerGroup, "Wiederholung", 475, 968, 44, 409);


// Funktionen zum Verschieben
var move1 = function(dx,dy,x,y) {
    var dx = Snap.snapTo(gridSize, dx, 50);
    var dy = Snap.snapTo(gridSizeY, dy, 50);
    this.attr({
        transform: this.data('origTransform') + (this.data('origTransform') ? "T" : "t") + [dx, dy]
    });
    paletteDragDeltaX = dx; paletteDragDeltaY = dy;
}

var stop1 = function() {
    paletteOffsetX += paletteDragDeltaX; paletteOffsetY += paletteDragDeltaY;
    paletteDragDeltaX = 0; paletteDragDeltaY = 0;
    //document.getElementById('auswahl').innerHTML = "paletteOffsetX = " + paletteOffsetX + ", paletteOffsetY = " + paletteOffsetY;
   // console.log('finished dragging');
}

//	Kartusche zeichnen
	paletteGroup = s.g(paletteFrame,ton,bass,slap,ton_g,slap_g,flam_ton,flam_slap,flam_bass_slap,In,Out,text_z_g,repeatMarkerGroup);
	paletteGroup.drag(move1,sel_start,stop1);

// Duplicate der Noten erzeugen
   insertTone = function () {
		e = ton_c.clone().attr({ class: 'shp',  id: "tone", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
	ton.click(insertTone);
	ton.touchstart(insertTone);

	insertBass = function () {
		e = bass_c.clone().attr({ class: 'shp',  id: "bass", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    bass.click(insertBass);
    bass.touchstart(insertBass);

	insertSlap = function () {
		e = slap_c.clone().attr({ class: 'shp',  id: "slap", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    slap.click(insertSlap);
    slap.touchstart(insertSlap);

	insertMuffledTone = function () {
		e = ton_g_c.clone().attr({ class: 'shp',  id: "tone_muffled", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    ton_g.click(insertMuffledTone);
	ton_g.touchstart(insertMuffledTone);

	insertMuffledSlap = function () {
		e = slap_g_c.clone().attr({ class: 'shp',  id: "slap_muffled", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    slap_g.click(insertMuffledSlap);
    slap_g.touchstart(insertMuffledSlap);

	insertFlamTone = function () {
		e = flam_ton_c.clone().attr({ class: 'shp',  id: "tone_flam", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    flam_ton.touchstart(insertFlamTone);
    flam_ton.click(insertFlamTone);

	insertFlamSlap = function () {
		e = flam_slap_c.clone().attr({ class: 'shp',  id: "slap_flam", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    flam_slap.click(insertFlamSlap);
    flam_slap.touchstart(insertFlamSlap);

	insertFlamBassSlap = function () {
		e = flam_bass_slap_c.clone().attr({ class: 'shp',  id: "bass_slap_flam", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    flam_bass_slap.click(insertFlamBassSlap);
    flam_bass_slap.touchstart(insertFlamBassSlap);

	insertInMarker = function () {
		e = In_c.clone().attr({ class: 'shp',  id: "in", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    In.click(insertInMarker);
    In.touchstart(insertInMarker);

    insertOutMarker = function () {
		e = Out_c.clone().attr({ class: 'shp',  id: "out", transform: "t" + (paletteOffsetX + gridSizeX) +"," + paletteOffsetY} );
		e.drag(move,sel_start);}
    Out.click(insertOutMarker);
    Out.touchstart(insertOutMarker);


	captureTextTouchStart = function(){
	textTouchStartX = this.getBBox().x
    textTouchStartY = this.getBBox().y
			}

	handleTextTouchEnd = function(){
			textTouchEndX = this.getBBox().x
			textTouchEndY = this.getBBox().y
			if (textTouchEndX == textTouchStartX && textTouchEndY == textTouchStartY){
				const text_a = this.attr('text');
				const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
				if (text_i == null) {return;}
				this.attr({text: text_i});
			}
		}

		    insertTextField = function () {
		        const elx = this.getBBox().cx + paletteOffsetX+19;
		        const ely = this.getBBox().y + paletteOffsetY+12;
		        const text_i = prompt('Gib hier bitte den gewünschten Text ein!', '');
		      var t = s.text(elx+3.5, ely, text_i).attr({ class: 'shp',  id: 'edit_text' , 'font-size':14, 'font-family': 'sans-serif'});
			t.drag(move,sel_start);
	        t.dblclick(edit_text);
			t.touchstart(captureTextTouchStart);
			t.touchend(handleTextTouchEnd);
		}
    text_z_g.click(insertTextField);
    text_z_g.touchstart(insertTextField);


cycleRepeatCount = function () {
     let textEl = this.select('text');
     let wert = textEl.node.textContent.trim();

     let zahl = parseInt(wert, 10);
        if (isNaN(zahl)) {
            zahl = 1;
        } else {
            zahl++;
            if (zahl > 4) {
                zahl = 0;
            }
        }
        textEl.attr({ text: zahl === 0 ? '' : String(zahl) });
	 }

		insertRepeatMarker = function () {
		e = wz_g_c.clone().attr({class: 'shp',  id: "wiederholung", transform: "t" + (paletteOffsetX + repeatMarkerGridOffsetX) +"," + (paletteOffsetY + 2)} );
        e.drag(move,sel_start);
        e.dblclick(cycleRepeatCount);
		 }
   repeatMarkerGroup.click(insertRepeatMarker);
   repeatMarkerGroup.touchstart(insertRepeatMarker);




// Als SVG speichern

function callPHPScript2()
{   let svgContent = "";
    let elementsToExport = s.selectAll("#notenlinien, #basis, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
 // Noten im Abseits löschen
    elementsToExport.forEach(function(el) {
        const ax = el.getBBox().cx;
        const ay = el.getBBox().cy;
        if(ax<0 || ax >1050 || ay<0 || ay>1480){el.remove();}
        //svgContent += el.toString();
			});

     elementsToExport = s.selectAll("#notenlinien, #basis, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    elementsToExport.forEach(function(el) {
        svgContent += el.toString();
	});

//var svgContent = document.getElementById('myRect1').innerHTML;

//svgContent = w.toString();
	const svgWithoutStylePrefix = svgContent.replaceAll('style="f', 'f');
	const svgWithFont10 = svgWithoutStylePrefix.replaceAll('font-size: 10px;', 'font-size="10px"');
	const svgWithFont12 = svgWithFont10.replaceAll('font-size: 12px;', 'font-size="12px"');
	const svgWithFont24 = svgWithFont12.replaceAll('font-size: 24px;', 'font-size="24px"');
	const svgWithFont15 = svgWithFont24.replaceAll('font-size: 15px;', 'font-size="15px"');
	const svgWithFont14 = svgWithFont15.replaceAll('font-size: 14px;', 'font-size="14px"');
	const svgWithTahoma = svgWithFont14.replaceAll('font-family: sans-serif;', 'font-family="Tahoma"');
	const svgWithBoldText = svgWithTahoma.replaceAll('font-weight: bold;', 'font-weight="bold"');
	//svgContent = svgWithBoldText.replaceAll('text-anchor: end;', 'text-anchor="end"');
	svgContent = '<svg height="1480" version="1.1" width="1050" xmlns="http://www.w3.org/2000/svg" id="myRect1"><desc>Created with Snap</desc><defs></defs>' + svgWithBoldText + '</svg>';


    //var textdatei = s.text( 45, z+820, text1).attr({'font-size':10});
	    const name = titel.attr('text');
   //name1 = name + ".txt";
    pruf(name);
    function pruf(dateiname)
    {
        dateiname = dateiname + ".svg";
        $.post("PHP/dateivorhanden_svg.php",
           {
        b: dateiname
    },
            function (data) {
// die textausgabe zurück ins feld schreiben

        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
        if(iofield == "true"){
	        let l = dateiname.length-4;
	        dateiname = dateiname.substr(0,l);
	            const check = prompt('Die Datei "'+dateiname + '" existiert schon!\nGib einen anderen Dateinamen ein!', '');
	            if (check == null) {return;}
	            pruf(check);
	        }
	        else{
	            const l = dateiname.length-4;
	            dateiname = dateiname.substr(0,l);
            $.post("dateispeichern_svg.php",
                   {
                a: svgContent,
                b: dateiname
            },
           function (data) {
        // die textausgabe zurück ins feld schreiben
        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
        document.getElementById('auswahl').innerHTML = iofield;
           });
        }
        setTimeout(callPHPScript1, 1000);
    });
    }
}


// Auslesen


const toene = ['tone','bass','slap','tone_muffled','slap_muffled','slap_muffled','tone_flam','slap_flam', 'bass_slap_flam'];
const steuerung = ['in','out','wiederholung'];

let notenText = "eee";


function callPHPScript_lesen(anzahl){
  let takteAnzahl = anzahl * 2;

  // Ein Array für Beschreibungen
  let instrument = '';
  let strukturelement = '';
  let beschreibungVonTakt = new Array(takteAnzahl);
  for (var i = 0; i < beschreibungVonTakt.length; i++) {
    beschreibungVonTakt[i] = [instrument,strukturelement,[false, 0]];
  }
  // Ein Array für die Takte sangban_begleitung
  let notenInTakt = new Array(takteAnzahl);
  // Die einzelnen Elemente mit Pausen füllen
  let f = 'f';
  if(rhythm == 'binaer'){
    notenText = "binär";
    for (var i = 0; i < notenInTakt.length; i++) {
      notenInTakt[i] = [f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f];
    }
  }
  else {
    notenText = "tenär";
    for (var i = 0; i < notenInTakt.length; i++) {
      notenInTakt[i] = [f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f,f];
    }
  }
  const editall = s.selectAll("#edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
  editall.forEach(function(el) {
    let zeilenPlus = 0;
    let zeilenMinus = 0;
    let ax = el.getBBox().cx;
    if(rhythm == 'binaer'){
      ax = Math.round(((ax-25)/12.5)-7);
      if (ax > 32) {
        zeilenPlus = 1;
      };
    }
    else{
      ax = Math.round(((ax-34)/16.5)-5);
       if (ax > 24) {
         zeilenPlus = 1;
       };
     }
    const inhalt = el.attr('text');
    let ay = el.getBBox().cy;
    if(el.attr('id') == "wiederholung"){
      zeilenMinus = 2;
    }
    ay = Math.round((ay-237) / 120 + 1)*2 + zeilenPlus - zeilenMinus
    if(inhalt != null){
      alert(inhalt + " = " + ay);
    }
    if(el.attr('id') == "wiederholung"){
      alert(el.attr('id') + " = " + ay);
      let textEl = el.select('text');
      let basisText = textEl ? textEl.node.textContent.trim() : '';
      alert("wbasis = " + basisText);
//hier soll zusätzlich der Textinhalt des Elementes mit der ID "wbasis" das innerhalb des Elementes mit der ID "Wiederholung" liegt ausgegeben werden.
    }

  });

  //alert(beschreibungVonTakt);
}

// Speichern




function callPHPScript()
{
  //var bodelem = document.querySelector('body');
  //simulatedClick(bodelem, altKey);



    if(rhythm=='binaer'){
      var serializedRhythm = '<binaer id="rhythmus"/>';
    }
    else{
      var serializedRhythm = '<tenaer id="rhythmus"/>';
    }

    let elementsToSave = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
 // Noten im Abseits löschen
    elementsToSave.forEach(function(el) {
        const ax = el.getBBox().cx;
        const ay = el.getBBox().cy;
        if(ax<70 || ax >1050 || ay<0 || ay>1480){el.remove();}
        //serializedRhythm += el.toString();
			});

    elementsToSave = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    elementsToSave.forEach(function(el) {
        serializedRhythm += el.toString();
	});

    //var textdatei = s.text( 45, z+820, text1).attr({'font-size':10});
	    const name = titel.attr('text');
   //name1 = name + ".txt";
    pruf(name);
     function pruf(dateiname)
    {
        dateiname = dateiname + ".txt";
        $.post("PHP/dateivorhanden.php",
           {
        b: dateiname
    },
            function (data) {
// die textausgabe zurück ins feld schreiben

        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
        if(iofield == "true"){
	        let l = dateiname.length-4;
	        dateiname = dateiname.substr(0,l);
	            const check = confirm('Die Datei "'+dateiname + '" existiert schon!\nSoll die Datei überschrieben werden?', '');
	            if (check == true) {

            $.post("dateispeichern.php",
                   {
                a: serializedRhythm,
                b: dateiname
            },
           function (data) {
        // die textausgabe zurück ins feld schreiben
        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
        document.getElementById('auswahl').innerHTML = iofield;
       setTimeout(callPHPScript1, 1000);

           });
        }

        }
             else{
	            const l = dateiname.length-4;
	            dateiname = dateiname.substr(0,l);
            $.post("dateispeichern.php",
                   {
                a: serializedRhythm,
                b: dateiname
            },
           function (data) {
        // die textausgabe zurück ins feld schreiben
        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
        document.getElementById('auswahl').innerHTML = iofield;
        setTimeout(callPHPScript1, 1000);

           });
        }

    });
    }

}

let scrollOn = false;

document.addEventListener('DOMContentLoaded', function () {
document.querySelector('#button').addEventListener('click',callPHPScript);
document.querySelector('#button2').addEventListener('click',callPHPScript2);
//document.querySelector('#button3').addEventListener('click',callPHPScript_lesen(zeilenAnzahl));
document.querySelector('#button3').addEventListener('click', () => {callPHPScript_lesen(zeilenAnzahl);});
document.querySelector('#button4').addEventListener('click',viererNoten);
document.querySelector('#button5').addEventListener('click',dreierNoten);
document.querySelector('#button7').addEventListener('click', ev => {
chooser = createInstrumentChooser(s, 125, 140).addClass("shp").attr({ id: nextInstrumentChooserId() })
});

document.querySelector('#button6').addEventListener('click', ev => {
	scrollOn = !scrollOn;
	if (scrollOn) { // start playing
    canv.attr({fill: "none"});
	}
	else {
		canv.attr({fill: "white"});
  }
});


});




// Laden

callPHPScript1();

function callPHPScript1()
{

    $.post("PHP/auswahlliste.php",
    function (data) {
      $('#iofield').val(data);
        //console.log ($('#iofield'));
        //console.log ($('input[name=iofield]').val());

        const fileListMarkup = $('input[name=iofield]').val();
        document.getElementById('auswahl').innerHTML = fileListMarkup;
     });
    //alert("Ja");
}

function onSVGLoaded(data) {
    const loadedElementsSelector = "#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser";

    if(data.select("#rhythmus")=='<binaer id="rhythmus"/>'){
        viererNoten();
    }
    else{
        dreierNoten();
    }
    //geom_note = data.select("#rhythmus");
   // alert(geom_note);
	    let loadedElements = data.selectAll(loadedElementsSelector);

	    s.append(loadedElements);
	    loadedElements.forEach(function(el) {
	        if (isInstrumentChooserNode(el)) {
	          return;
	        }
	        el.attr({class: "shp"});
	        el.drag(move,sel_start);
	      });

    const loadedTextElements = s.selectAll("#edit_text");
    loadedTextElements.forEach(function(el) {
	   el.dblclick(edit_text);
	});

    const loadedRepeatElements = s.selectAll("#wiederholung");
    loadedRepeatElements.forEach(function(el) {
	   el.dblclick(cycleRepeatCount);
	});

	    const loadedInstrumentChoosers = s.selectAll(".instrument-chooser, #instrumentChooser");
	    loadedInstrumentChoosers.forEach(function(el) {
	      el.addClass("shp");
	      el.addClass("instrument-chooser");
	      el.attr({ id: nextInstrumentChooserId() });
	      el.selectAll("g").forEach(function(sub) {
	        sub.attr({ display: "none" });
	      });
	      rewireInstrumentChooser(el);
	    });

	    titel.attr({text: loadedTitle});

}

function get_value(e)
{
	    const loadedElementsToRemove = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    loadedElementsToRemove.forEach(function(el) {
        el.remove();
    });

let selectedFileName;
if(e){
 selectedFileName = e.options[e.selectedIndex].text;
}
if(datei_name!=""){
    selectedFileName = datei_name;
}


    const selectedFilePath = '../Noten/' + selectedFileName;
    const fileNameLengthWithoutExtension = selectedFileName.length-4;
    loadedTitle = selectedFileName.substr(0,fileNameLengthWithoutExtension);
      $.post("PHP/dateiladen.php",
    {
        b: selectedFilePath
    },

    function (data) {
        // die textausgabe zurück ins feld schreiben
        $('#iofield').val(data);
        const loadedSvgMarkup = $('input[name=iofield]').val();
         // alert(iofield);
		Snap.loadStr(loadedSvgMarkup, onSVGLoaded);
    });
}


get_value();

</script>


<br>


</body>
</html>
