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

var elem = document.querySelector('body');
elem.addEventListener("keydown", shadow_end );
var elem1 = document.querySelector('body');
elem1.addEventListener("keydown", start);
var elem2 = document.querySelector('body');
elem2.addEventListener("keydown", entfernen);
/*
elem.addEventListener ("keydown", function (event) {
	console.log (event.key + " " + event.metaKey)
});
*/


let myObjectString = "TEST";
// Funktionen
edit_title = function(){
    text_a = (this.attr('text'));
    text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {return;}
    this.attr({text: text_i});
}

edit_text = function(){
    text_a = (this.attr('text'));
    text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {return;}
    this.attr({text: text_i});
}


// Zeichenfläche und Titel festlegen
var s = Snap(1050,1480).attr({ id: "myRect1" });
var canv = s.rect(0,0,1050,1480).attr({fill:"white", stroke:"black", strokeWidth: 0.5, opacity: 0.300, id: "myRect2" });
canv.drag(shadow_move, shadow_start, shadow_end);

    y = 172;
	  z = 202;
    k = 0;
    yN = 172;

function clear_all()
{
    all = s.selectAll("#notenlinien, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung");
    all.forEach(function(el) {
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
    gridSize1 = 10;
    gridSize2 = 25;
    gridSize_Wz = 24;

    clear_all();
    //titel.attr({text: "Enter the name of the Rhythm"});

    k1 = ["Ja","Pi","Du","Pa"];

    //let zeilenAnzahl = 10;

    for (var j=0; j < zeilenAnzahl; j++){
        c = s.rect(100, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        c = s.rect(525, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        c = s.rect(950, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
		zaehl = s.text(90, yN+30+j*120, j+1).attr({ id: 'notenlinien', 'font-size':24, 'font-family':'sans-serif', 'font-weight':'bold', 'fill':"#a0a0a0", 'text-anchor':'end' });
        for (var i=1; i < 34; i++){
            x = 100 + (850/34)*i
            if (i != 17){
               var text = s.text( x-3, yN+j*120-4, k1[k]).attr({ id: 'notenlinien', 'font-size':10});
               k++;
                if (k==4){k=0}
                c = s.rect(x, yN+j*120, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (i == 1 || i == 5 || i == 9 || i == 13 || i == 18 || i == 22 || i == 26 || i == 30){
				k2 = Math.trunc((i+4) / 4);
				if(k2>4){k2-=4};
				var text = s.text( x-3, yN+j*120-14, k2).attr({ id: 'notenlinien', 'font-size':10});
                b = (850/34)*3
                c = s.rect(x, yN+j*120, b, 1.5).attr({ id: "notenlinien" });
                c = s.rect(x, yN+j*120+5, b, 1.5).attr({ id: "notenlinien" });
            }
        }
    }
//    let chooser = createInstrumentChooser(s, 125, 140).attr({ class: "shp", id: "instrumentChooser" });
}

function dreierNoten(){
    rhythm = 'tenaer';
  //Grid festlegen
  gridSize = (850/26)/2;
  gridSize1 = 10;
  gridSize2 = 34;
  gridSize_Wz = 26;


  clear_all();
   k1 = ["Ja","Pi","Du"];
    //titel.attr({text: "Enter the name of the Rhythm"});

     for (var j=0; j < 10; j++){
        c = s.rect(100, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        c = s.rect(525, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
        c = s.rect(950, yN-10+j*120, 3, 60).attr({ id: "notenlinien" });
		zaehl = s.text(90, yN+30+j*120, j+1).attr({id: "notenlinien", 'font-size':24, 'font-family':'sans-serif', 'font-weight':'bold', 'fill':"#a0a0a0", 'text-anchor':'end' });
        for (var i=1; i < 26; i++){
            x = 100 + (850/26)*i
            if (i != 13){
               var text = s.text( x-3, yN+j*120-4, k1[k]).attr({id: "notenlinien", 'font-size':10});
               k++;
                if (k==3){k=0}
                c = s.rect(x, yN+j*120, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (i == 1 || i == 4 || i == 7 || i == 10 || i == 14 || i == 17 || i == 20 || i == 23){
				k2 = Math.trunc((i+3) / 3);
				if(k2>4){k2-=4};
				var text = s.text( x-3, yN+j*120-16, k2).attr({id: "notenlinien", 'font-size':10});
                b = (850/39)*3
                c = s.rect(x, yN+j*120, b, 1.5).attr({ id: "notenlinien" });
                c = s.rect(x, yN+j*120+5, b, 1.5).attr({ id: "notenlinien" });
            }
        }
    }
}

// Noten zeichnen und initialisieren

//	Anfangskoordinaten
    cx = 33, cy = z-30;

 //	Kartusche
    dc = s.rect(cx-12,cy-14,26,262,3,3).attr({fill:"lightgrey", stroke:"black", strokeWidth: 0.5 });

//	Tone
    ton = s.circle(cx+1,cy+1,7);

//	Bass
    x = cx-6; y = cy+15;
    bass = s.rect(x+1,y,12,12);

 //	Slap
    x = cx-5, y = cy+47;
   // slap = s.polygon(x,y,x+8,y-14,x+16,y);
   	slap_c = s.rect(x,y-12,12,12).attr({ opacity: 0.001 })
   	slap_a = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	  slap_b = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	  slap = s.g(slap_a,slap_b,slap_c);

 //	Flam Ton
    x = cx+4, y = cy+62;
	  flam_ton_a = s.circle(x,y,6).attr({fill:"white", stroke:"black", strokeWidth: 2 });
    x = cx-2;
	flam_ton_b = s.circle(x,y,6).attr({fill:"black", stroke:"black", strokeWidth: 2 });
	flam_ton = s.g(flam_ton_a,flam_ton_b);


//	Flam Slap
  x = cx-8, y = cy+88;
   // slap = s.polygon(x,y,x+8,y-14,x+16,y);
  slap_0 = s.rect(x,y-12,20,12).attr({ opacity: 0.001 })
  slap_a1 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a2 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	x = cx-2;
	slap_b1 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_b2 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	flam_slap = s.g(slap_0,slap_a1,slap_a2,slap_b1,slap_b2);

//	Flam Bass_Slap
	x = cx-8; y = cy+95;
	flam_bass_0 = s.rect(x,y-12,12,12).attr({ opacity: 0.001 })
	flam_bass= s.rect(x+1,y,12,12);
	x = cx-2; y = cy+107;
	slap_a3 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a4 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	flam_bass_slap = s.g(flam_bass_0,flam_bass,slap_a3,slap_a4).attr({ fill:"white", stroke:"black", strokeWidth: 2 });

//	Tone gedämpft
	x=cx-5; y = cy+125;
  ton_g_c = s.rect(x,y-12,12,14).attr({ opacity: 0.001 })
 	x=cx-50; y = cy-88;
	ton_g_a = ton.clone().attr({transform: "t" + 0 + "," + 120});
	x=cx-6; y = cy+130;
	ton_g_b = s.line(x,y,x+15,y).attr({ stroke:"black", strokeWidth: 2 });
  ton_g = s.g(ton_g_a,ton_g_b,ton_g_c);

//	Slap gedämpft
 	x=cx-5; y = cy+147;
  slap_g_c = s.rect(x,y-12,12,14).attr({ opacity: 0.001 })
	slap_a5 = s.line(x,y,x+12,y-12).attr({ stroke:"black", strokeWidth: 2 });
	slap_a6 = s.line(x,y-12,x+12,y).attr({ stroke:"black", strokeWidth: 2 });
	x=cx-6; y = cy+150;
	slap_g_b = s.line(x,y,x+15,y).attr({ stroke:"black", strokeWidth: 2 });
  slap_g = s.g(slap_a5,slap_a6,slap_g_b,slap_g_c);

//	In
  x = cx+1; y = cy+156;
	in_c = s.rect(x-6,y,12,20).attr({ opacity: 0.001})
  in_a = s.line(x,y,x,y+12).attr({ stroke:"black", strokeWidth: 3 });
  x = cx-5; y=cy+168;
  in_b = s.polygon(x,y,x+6,y+7,x+12,y);
  In = s.g(in_a,in_b,in_c);

//	Out
  x = cx+1; y = cy+185;
	out_c = s.rect(x-6,y-8,12,20).attr({ opacity: 0.001 })
  out_a = s.line(x,y,x,y+12).attr({ stroke:"black", strokeWidth: 3 });
  x = cx-5;
  out_b = s.polygon(x,y,x+6,y-7,x+12,y);
  Out = s.g(out_a,out_b,out_c);

//	Text
  x = cx-6, y= cy +230;
  dt = s.rect(x,y-26,14,15).attr({fill:"white", stroke:"black", strokeWidth: 1 });
  text_z = s.line(x+3,y-21,x+11,y-21).attr({ stroke:"black", strokeWidth: 2.5 });
  text_z1 = s.line(x+7,y-21,x+7,y-14).attr({ stroke:"black", strokeWidth: 2.5 });
  text_z_g= s.g(dt,text_z1,text_z);

	y+=20;

//	Wiederholungszeichen
	wz_c = s.rect(cx-4,y-27,10,20).attr({ opacity: 0.001 })
  wz_a = s.circle(cx+1,cy+228,2.5);
	wz_b = s.circle(cx+1,cy+236,2.5);
  wz_d = s.text(cx+1, cy+252, " ").attr({ 'font-size':12, 'font-family':'sans-serif', 'font-weight':'bold', 'text-anchor':'middle' });
	wz_g= s.g(wz_c,wz_a,wz_b,wz_d);


  // Legende schreiben

    cx = 122, cy = 1380;

	tx = cx-70; ty = cy-214;
    ton_c = ton.clone().attr({id: "basis", transform: "t" + tx + "," + ty});
    s.text( cx-5, cy, "Tone").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 88;
	bx = cx-81; by = cy-234;
    bass_c = bass.clone().attr({id: "basis", transform: "t" + bx + "," + by});
	cx -= 15;
    s.text( cx, cy, "Bass").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 82;
	sx = cx-76; sy = cy-254;
    slap_c = slap.clone().attr({id: "basis", transform: "t" + sx + "," + sy});
	cx -= 12;
    s.text( cx, cy, "Slap/Glocke").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 140;
	ftx = cx-81; fty = cy-274;
    flam_ton_c = flam_ton.clone().attr({id: "basis", transform: "t" + ftx + "," + fty});
	cx -= 12;
    s.text( cx, cy, "Flam mit Tones").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 152;
	fsx = cx-78; fsy = cy-294;
    flam_slap_c = flam_slap.clone().attr({id: "basis", transform: "t" + fsx + "," + fsy});
	cx -= 10;
    s.text( cx, cy, "Flam mit Slaps").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

	cx += 155;
	fsx = cx-78; fsy = cy-314;
    flam_bass_slap_c = flam_bass_slap.clone().attr({id: "basis", transform: "t" + fsx + "," + fsy});
	cx -= 10;
    s.text( cx, cy, "Flam mit Bass und Slaps").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx = 130, cy = 1410;
	sgx = cx-78; sgy = cy-332;
    ton_g_c = ton_g.clone().attr({id: "basis", transform: "t" + sgx + "," + sgy});
	cx -= 10;
    s.text( cx, cy, "gedämpfter Tone").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 170;
	sgx = cx-78; sgy = cy-355;
    slap_g_c = slap_g.clone().attr({id: "basis", transform: "t" + sgx + "," + sgy});
	cx -= 10;
    s.text( cx, cy, "gedämpfter Slap").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 160;
	ix = cx-73; iy = cy-377;
    In_c = In.clone().attr({id: "basis", transform: "t" + ix + "," + iy});
	cx -= 10;
    s.text( cx, cy, "In").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

    cx += 55;
	ox = cx-73; oy = cy-400;
	Out_c = Out.clone().attr({id: "basis", transform: "t" + ox + "," + oy});
	cx -= 10;
    s.text( cx, cy, "Out").attr({id: "basis", 'font-size':15, 'font-family':'sans-serif'});

	//wz_d.attr({text: "11"});
	px = cx-5; py = cy-450;
	wz_g_c = wz_g.clone().attr({transform: "t" + px + "," + py});
    s.text( px+60, cy, "Wiederholung").attr({'font-size':15, 'font-family':'sans-serif'});






// Funktionen zum Verschieben
flag_move = false; ddx=0; ddy=0; dddx=0; dddy=0; //mdx=0; mdy=0;

var move1 = function(dx,dy,x,y) {
    var dx = Snap.snapTo(gridSize, dx, 50);
    var dy = Snap.snapTo(gridSize1, dy, 50);
    this.attr({
        transform: this.data('origTransform') + (this.data('origTransform') ? "T" : "t") + [dx, dy]
    });
    ddx = dx; ddy = dy;
}

var stop1 = function() {
    dddx += ddx; dddy += ddy;
    ddx = 0; ddy = 0;
    //document.getElementById('auswahl').innerHTML = "dddx = " + dddx + ", dddy = " + dddy;
   // console.log('finished dragging');
}

//	Kartusche zeichnen
	dall = s.g(dc,ton,bass,slap,ton_g,slap_g,flam_ton,flam_slap,flam_bass_slap,In,Out,text_z_g,wz_g);
	dall.drag(move1,sel_start,stop1);

// Duplicate der Noten erzeugen
   edit_ton = function () {
		e = ton_c.clone().attr({ class: 'shp',  id: "tone", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
	ton.click(edit_ton);
	ton.touchstart(edit_ton);

	edit_bass = function () {
		e = bass_c.clone().attr({ class: 'shp',  id: "bass", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    bass.click(edit_bass);
    bass.touchstart(edit_bass);

	edit_slap = function () {
		e = slap_c.clone().attr({ class: 'shp',  id: "slap", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    slap.click(edit_slap);
    slap.touchstart(edit_slap);

	edit_ton_g = function () {
		e = ton_g_c.clone().attr({ class: 'shp',  id: "tone_muffled", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    ton_g.click(edit_ton_g);
	ton_g.touchstart(edit_ton_g);

	edit_slap_g = function () {
		e = slap_g_c.clone().attr({ class: 'shp',  id: "slap_muffled", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    slap_g.click(edit_slap_g);
    slap_g.touchstart(edit_slap_g);

	edit_flam = function () {
		e = flam_ton_c.clone().attr({ class: 'shp',  id: "tone_flam", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    flam_ton.touchstart(edit_flam);
    flam_ton.click(edit_flam);

	edit_flam_slap = function () {
		e = flam_slap_c.clone().attr({ class: 'shp',  id: "slap_flam", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    flam_slap.click(edit_flam_slap);
    flam_slap.touchstart(edit_flam_slap);

	edit_flam_bass_slap = function () {
		e = flam_bass_slap_c.clone().attr({ class: 'shp',  id: "bass_slap_flam", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    flam_bass_slap.click(edit_flam_bass_slap);
    flam_bass_slap.touchstart(edit_flam_bass_slap);

	edit_in = function () {
		e = In_c.clone().attr({ class: 'shp',  id: "in", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    In.click(edit_in);
    In.touchstart(edit_in);

    edit_out = function () {
		e = Out_c.clone().attr({ class: 'shp',  id: "out", transform: "t" + (dddx + gridSize2) +"," + dddy} );
		e.drag(move,sel_start);}
    Out.click(edit_out);
    Out.touchstart(edit_out);


	edit_text_a = function(){
	eltx1 = this.getBBox().x
    elty1 = this.getBBox().y
	}

	edit_text_b = function(){
		eltx2 = this.getBBox().x
		elty2 = this.getBBox().y
		if (eltx2 == eltx1 && elty2 == elty1){
			text_a = (this.attr('text'));
			text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
			if (text_i == null) {return;}
			this.attr({text: text_i});
		}
	}

    edit_text_z_g = function () {
        elx = this.getBBox().cx + dddx+19;
        ely = this.getBBox().y + dddy+12;
        text_i = prompt('Gib hier bitte den gewünschten Text ein!', '');
      var t = s.text(elx+3.5, ely, text_i).attr({ class: 'shp',  id: 'edit_text' , 'font-size':14, 'font-family': 'sans-serif'});
		t.drag(move,sel_start);
        t.dblclick(edit_text);
		t.touchstart(edit_text_a);
		t.touchend(edit_text_b);
	}
    text_z_g.click(edit_text_z_g);
    text_z_g.touchstart(edit_text_z_g);



/*
    wz_g.click(edit_wz_g);
    text_z_g.touchstart(edit_wz_g);

    */
/*
	edit_text_wz = function(){
        elx = this.getBBox().x ;
        ely = this.getBBox().y ;
      //  console.log("elx = "+ elx + ", ely = " + ely);
      //  console.log(this.attr("id"));
            this.remove();
            var temp = window.alert;
            window.alert = function() {};
            if(elx == 515 || elx == 940){
                text_i = prompt('Gib hier die Anzahl der Wiederholungen ein!', '');
            }
            else{
                text_i = " ";
            }
             var titlen = Snap.parse('<g id="wiederholung_clon" transform="matrix(1,0,0,1,19,-48)" class="shp" xmlns="http://www.w3.org/2000/svg"><rect x="-5" y="-14.425" width="10" height="20" style="opacity: 0.101;"></rect><circle cx="0" cy="-9.925" r="2.5"></circle><circle cx="0" cy="-1.925" r="2.5"></circle><text x="0" y="14.425" style="font-size: 12px; font-family: sans-serif; font-weight: bold; text-anchor: middle;">'+text_i+'</text></g>');
             window.alert = temp;

            s.append( titlen );
            geome = s.selectAll("#wiederholung_clon");
            geome.forEach(function(el) {
            el.attr({id: "wiederholung", transform: "t" + (elx+5) +"," + (ely+14.425)} );
            el.attr({class: "shp"});
            el.drag(move,sel_start,edit_text_wz_1);
            el.dblclick(edit_text_wz);
           });
    }

    edit_text_wz_1 = function(){
        elx = this.getBBox().x ;
        ely = this.getBBox().y ;
      //  console.log("elx_1 = "+ elx + ", ely_1 = " + ely);
      //  console.log(this.attr("id"));
        //console.log("www_1 = " + wiederholungszeichen(this));
            this.remove();
            var temp = window.alert;
            window.alert = function() {};
            if(elx == 515 || elx == 940){
                text_i = " "; // prompt('Gib hier die Anzahl der Wiederholungen ein!', '');
            }
            else{
                text_i = " ";
            }
             var titlen = Snap.parse('<g id="wiederholung_clon" transform="matrix(1,0,0,1,19,-48)" class="shp" xmlns="http://www.w3.org/2000/svg"><rect x="-5" y="-14.425" width="10" height="20" style="opacity: 0.501;"></rect><circle cx="0" cy="-9.925" r="2.5"></circle><circle cx="0" cy="-1.925" r="2.5"></circle><text x="0" y="14.425" style="font-size: 12px; font-family: sans-serif; font-weight: bold; text-anchor: middle;">'+text_i+'</text></g>');
             window.alert = temp;

            s.append( titlen );
            geome = s.selectAll("#wiederholung_clon");
            geome.forEach(function(el) {
            el.attr({id: "wiederholung", transform: "t" + (elx+5) +"," + (ely+14.425)} );
            el.attr({class: "shp"});
            el.drag(move,sel_start);
            el.dblclick(edit_text_wz);
           });
    }
*/

edit_text_wz = function () {
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

	edit_wz_g = function () {
		e = wz_g_c.clone().attr({class: 'shp',  id: "wiederholung", transform: "t" + (dddx + gridSize_Wz) +"," + (dddy + 2)} );
        e.drag(move,sel_start);
        e.dblclick(edit_text_wz);
	 }
   wz_g.click(edit_wz_g);
   wz_g.touchstart(edit_wz_g);




// Als SVG speichern

function callPHPScript2()
{   text1 = "";
    editall = s.selectAll("#notenlinien, #basis, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
 // Noten im Abseits löschen
    editall.forEach(function(el) {
        ax = el.getBBox().cx;
        ay = el.getBBox().cy;
        if(ax<0 || ax >1050 || ay<0 || ay>1480){el.remove();}
        //text1 += el.toString();
	});

     editall = s.selectAll("#notenlinien, #basis, #edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    editall.forEach(function(el) {
        text1 += el.toString();
	});

//var text1 = document.getElementById('myRect1').innerHTML;

//text1 = w.toString();
text2 =   text1.replaceAll('style="f', 'f');
text3 =   text2.replaceAll('font-size: 10px;', 'font-size="10px"');
text4 =   text3.replaceAll('font-size: 12px;', 'font-size="12px"');
text5 =   text4.replaceAll('font-size: 24px;', 'font-size="24px"');
text6 =   text5.replaceAll('font-size: 15px;', 'font-size="15px"');
text7 =   text6.replaceAll('font-size: 14px;', 'font-size="14px"');
text8 =   text7.replaceAll('font-family: sans-serif;', 'font-family="Tahoma"');
text9 =   text8.replaceAll('font-weight: bold;', 'font-weight="bold"');
//text1 =   text9.replaceAll('text-anchor: end;', 'text-anchor="end"');
text1 = '<svg height="1480" version="1.1" width="1050" xmlns="http://www.w3.org/2000/svg" id="myRect1"><desc>Created with Snap</desc><defs></defs>' + text1 + '</svg>';


    //var textdatei = s.text( 45, z+820, text1).attr({'font-size':10});
    name = (titel.attr('text'));
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
        l = dateiname.length-4;
        dateiname = dateiname.substr(0,l);
            check = prompt('Die Datei "'+dateiname + '" existiert schon!\nGib einen anderen Dateinamen ein!', '');
            if (check == null) {return;}
            pruf(check);
        }
        else{
            l = dateiname.length-4;
            dateiname = dateiname.substr(0,l);
            $.post("dateispeichern_svg.php",
                   {
                a: text1,
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
  editall = s.selectAll("#edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
  editall.forEach(function(el) {
    zeilenPlus = 0;
    zeilenMinus = 0;
    ax = el.getBBox().cx;
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
    inhalt = el.attr('text');
    ay = el.getBBox().cy;
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
      text1 = '<binaer id="rhythmus"/>';
    }
    else{
      text1 = '<tenaer id="rhythmus"/>';
    }

    editall = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
 // Noten im Abseits löschen
    editall.forEach(function(el) {
        ax = el.getBBox().cx;
        ay = el.getBBox().cy;
        if(ax<70 || ax >1050 || ay<0 || ay>1480){el.remove();}
        //text1 += el.toString();
	});

    editall = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    editall.forEach(function(el) {
        text1 += el.toString();
	});

    //var textdatei = s.text( 45, z+820, text1).attr({'font-size':10});
    name = (titel.attr('text'));
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
        l = dateiname.length-4;
        dateiname = dateiname.substr(0,l);
            check = confirm('Die Datei "'+dateiname + '" existiert schon!\nSoll die Datei überschrieben werden?', '');
            if (check == true) {

            $.post("dateispeichern.php",
                   {
                a: text1,
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
            l = dateiname.length-4;
            dateiname = dateiname.substr(0,l);
            $.post("dateispeichern.php",
                   {
                a: text1,
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


'use strict';

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

        var iofield = $('input[name=iofield]').val();
        document.getElementById('auswahl').innerHTML = iofield;
     });
    //alert("Ja");
}

function onSVGLoaded(data) {
    if(data.select("#rhythmus")=='<binaer id="rhythmus"/>'){
        viererNoten();
    }
    else{
        dreierNoten();
    }
    //geom_note = data.select("#rhythmus");
   // alert(geom_note);
    geom = data.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");

    s.append(geom);
    geom.forEach(function(el) {
        el.attr({class: "shp"});
        el.drag(move,sel_start);
      });
    geom = s.selectAll("#edit_text");
    geom.forEach(function(el) {
	   el.dblclick(edit_text);
	});
    geom = s.selectAll("#wiederholung");
    geom.forEach(function(el) {
	   el.dblclick(edit_text_wz);
	});

    geom = s.selectAll(".instrument-chooser, #instrumentChooser");
    geom.forEach(function(el) {

      ax = el.getBBox().cx-35;
      ay = el.getBBox().cy+5;
    //  alert(ax);
      let altesTextElement = el.select("text");
      let alterText = altesTextElement.attr("text");
      let alteFarbe = altesTextElement.attr("fill");
      let chooser = createInstrumentChooser(s, ax, ay, alterText, alteFarbe).addClass("shp").attr({ id: nextInstrumentChooserId() });
      el.remove();


});

    titel.attr({text: t2});

}

function get_value(e)
{
    all = s.selectAll("#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung, .instrument-chooser, #instrumentChooser");
    all.forEach(function(el) {
        el.remove();
    });

if(e){
 var t = e.options[e.selectedIndex].text;
}
if(datei_name!=""){
    var t = datei_name;
}


    t1 = '../Noten/' + t;
    l = t.length-4;
    t2 = t.substr(0,l);
      $.post("PHP/dateiladen.php",
    {
        b: t1
    },

    function (data) {
        // die textausgabe zurück ins feld schreiben
        $('#iofield').val(data);
        var iofield = $('input[name=iofield]').val();
         // alert(iofield);
		Snap.loadStr(iofield, onSVGLoaded);
    });
}


get_value();

</script>


<br>


</body>
</html>
