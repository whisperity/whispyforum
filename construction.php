<?php
// Disable caching.
//header("Cache-Control: no-cache, must-revalidate");
//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<meta name="description" content="A Nagy László Gimnázium Diákönkormányzatának Szabadegyetem Szervezési oldala">
	<meta name="keywords" content="nlg,nagylászló,szabadegyetem,dök,diákönkormányzat,idb,esemény,előadás,tanár,diák,móka,kacagás,szervezés,autonómia">
	<title>Szabadegyetem Szervezési Site</title>
	
	
	<style type="text/css">
		body {
			padding-top: 30px;
			background-color: #CCCCCC;
			overflow: hidden;
		}
		
		img.source-image {
			height: 100%;
			position: absolute;
			top: 0;
			left: 0%;
			
			z-index: -1;
		}
		
		a { font-size: 14px; font-family: Verdana; color: blue; font-weight: normal; text-decoration: none; }
		a:hover { font-size: 14px; font-family: Verdana; color: purple; font-weight: normal; text-decoration: underline; }
		
		table.messagebox {
			font-weight: bold;
			color: DarkOrange;
			background-color: white;
			border: 1.5px dashed red;
			padding: 3px;
			line-height: 125%;
			margin: 1em auto;
		}
		
		td.messagetitle {
			font-family: MS Sans Serif;
			font-size: 14pt;
			text-decoration: underline;
			font-weight: bold;
		}

		td.messagepicture {
		}
		
		td.messagebody {
		}
		
		td.big {
			font-size: 64px;
			font-weight: bold;
		}
		
		/*
		
		Styling the counter itself.
		Set image paths accordingly.
		
		THIS YOU NEED TO KEEP.
		
		So you can have multiple counters on a page, just make sure each one has a class of "flip-counter".
		
		You can download the background sprite here:
		http://cnanney.com/journal/demo/apple-counter-revisited/img/digits.png
		
		*/
		
		.flip-counter ul.cd{float:left;list-style-type:none;margin:0;padding:0}
		.flip-counter li{background:url(digits.png) 0 0 no-repeat}
		.flip-counter li.t{background-position:0 0;width:53px;height:39px}
		.flip-counter li.b{background-position:0 0;width:53px;height:64px}
		.flip-counter li.s{background-position:-53px -1030px;width:14px;height:103px}
	</style>
	
	<script type="text/javascript">
		/**
		 * Apple-Style Flip Counter
		 * Version 0.5.3 - May 7, 2011 
		 *
		 * Copyright (c) 2010 Chris Nanney
		 * http://cnanney.com/journal/code/apple-style-counter-revisited/
		 *
		 * Licensed under MIT
		 * http://www.opensource.org/licenses/mit-license.php
		 */
		 
		var flipCounter = function(d, options){
		
			// Default values
			var defaults = {
				value: 0,
				inc: 1,
				pace: 1000,
				auto: true,
				tFH: 39,
				bFH: 64,
				fW: 53,
				bOffset: 390
			};
			
			var o = options || {},
			doc = window.document,
			divId = typeof d !== 'undefined' && d !== '' ? d : 'flip-counter',
			div = doc.getElementById(divId);
			
			for (var opt in defaults) o[opt] = (opt in o) ? o[opt] : defaults[opt];
		
			var digitsOld = [], digitsNew = [], subStart, subEnd, x, y, nextCount = null, newDigit, newComma,
			best = {
				q: null,
				pace: 0,
				inc: 0
			};
			
			/**
			 * Sets the value of the counter and animates the digits to new value.
			 * 
			 * Example: myCounter.setValue(500); would set the value of the counter to 500,
			 * no matter what value it was previously.
			 *
			 * @param {int} n
			 *   New counter value
			 */
			this.setValue = function(n){
				if (isNumber(n)){
					x = o.value;
					y = n;
					o.value = n;
					digitCheck(x,y);
				}
				return this;
			};
			
			/**
			 * Sets the increment for the counter. Does NOT animate digits.
			 */
			this.setIncrement = function(n){
				o.inc = isNumber(n) ? n : defaults.inc;
				return this;
			};
			
			/**
			 * Sets the pace of the counter. Only affects counter when auto == true.
			 *
			 * @param {int} n
			 *   New pace for counter in milliseconds
			 */
			this.setPace = function(n){
				o.pace = isNumber(n) ? n : defaults.pace;
				return this;
			};
			
			/**
			 * Sets counter to auto-incrememnt (true) or not (false).
			 *
			 * @param {bool} a
			 *   Should counter auto-increment, true or false
			 */
			this.setAuto = function(a){
				if (a && ! o.atuo){
					o.auto = true;
					doCount();
				}
				if (! a && o.auto){
					if (nextCount) clearNext();
					o.auto = false;
				}
				return this;
			};
			
			/**
			 * Increments counter by one animation based on set 'inc' value.
			 */
			this.step = function(){
				if (! o.auto) doCount();
				return this;
			};
			
			/**
			 * Adds a number to the counter value, not affecting the 'inc' or 'pace' of the counter.
			 *
			 * @param {int} n
			 *   Number to add to counter value
			 */
			this.add = function(n){
				if (isNumber(n)){
					x = o.value;
					o.value += n;
					y = o.value;
					digitCheck(x,y);
				}
				return this;
			};
			
			/**
			 * Subtracts a number from the counter value, not affecting the 'inc' or 'pace' of the counter.
			 *
			 * @param {int} n
			 *   Number to subtract from counter value
			 */
			this.subtract = function(n){
				if (isNumber(n)){
					x = o.value;
					o.value -= n;
					if (o.value >= 0){
						y = o.value;
					}
					else{
						y = "0";
						o.value = 0;
					}
					digitCheck(x,y);
				}
				return this;
			};
			
			/**
			 * Increments counter to given value, animating by current pace and increment.
			 *
			 * @param {int} n
			 *   Number to increment to
			 * @param {int} t (optional)
			 *   Time duration in seconds - makes increment a 'smart' increment
			 * @param {int} p (optional)
			 *   Desired pace for counter if 'smart' increment
			 */
			this.incrementTo = function(n, t, p){
				if (nextCount) clearNext();
				
				// Smart increment
				if (typeof t != 'undefined'){
					var time = isNumber(t) ? t * 1000 : 10000,
					pace = typeof p != 'undefined' && isNumber(p) ? p : o.pace,
					diff = typeof n != 'undefined' && isNumber(n) ? n - o.value : 0,
					cycles, inc, check, i = 0;
					best.q = null;
					
					// Initial best guess
					pace = (time / diff > pace) ? Math.round((time / diff) / 10) * 10 : pace;
					cycles = Math.floor(time / pace);
					inc = Math.floor(diff / cycles);
					
					check = checkSmartValues(diff, cycles, inc, pace, time);
					
					if (diff > 0){
						while (check.result === false && i < 100){				
							pace += 10;
							cycles = Math.floor(time / pace);
							inc = Math.floor(diff / cycles);
							
							check = checkSmartValues(diff, cycles, inc, pace, time);					
							i++;
						}
						
						if (i == 100){
							// Could not find optimal settings, use best found so far
							o.inc = best.inc;
							o.pace = best.pace;
						}
						else{
							// Optimal settings found, use those
							o.inc = inc;
							o.pace = pace;
						}
						
						doIncrement(n, true, cycles);
					}
				
				}
				// Regular increment
				else{
					doIncrement(n);
				}
				
			}
			
			/**
			 * Gets current value of counter.
			 */
			this.getValue = function(){
				return o.value;
			}
			
			/**
			 * Stops all running increments.
			 */
			this.stop = function(){
				if (nextCount) clearNext();
				return this;
			}
			
			//---------------------------------------------------------------------------//
			
			function doCount(){
				x = o.value;
				o.value += o.inc;
				y = o.value;
				digitCheck(x,y);
				if (o.auto === true) nextCount = setTimeout(doCount, o.pace);
			}
			
			function doIncrement(n, s, c){
				var val = o.value,
				smart = (typeof s == 'undefined') ? false : s,
				cycles = (typeof c == 'undefined') ? 1 : c;
				
				if (smart === true) cycles--;
				
				if (val != n){
					x = o.value,
					o.auto = true;
		
					if (val + o.inc <= n && cycles != 0) val += o.inc
					else val = n;
					
					o.value = val;
					y = o.value;
					
					digitCheck(x,y);
					nextCount = setTimeout(function(){doIncrement(n, smart, cycles)}, o.pace);
				}
				else o.auto = false;
			}
			
			function digitCheck(x,y){
				digitsOld = splitToArray(x);
				digitsNew = splitToArray(y);
				var diff,
				xlen = digitsOld.length,
				ylen = digitsNew.length;
				if (ylen > xlen){
					diff = ylen - xlen;
					while (diff > 0){
						addDigit(ylen - diff + 1, digitsNew[ylen - diff]);
						diff--;
					}
				}
				if (ylen < xlen){
					diff = xlen - ylen;
					while (diff > 0){
						removeDigit(xlen - diff);
						diff--;
					}
				}
				for (var i = 0; i < xlen; i++){
					if (digitsNew[i] != digitsOld[i]){
						animateDigit(i, digitsOld[i], digitsNew[i]);
					}
				}
			}
			
			function animateDigit(n, oldDigit, newDigit){
				var speed, step = 0, w, a,
				bp = [
					'-' + o.fW + 'px -' + (oldDigit * o.tFH) + 'px',
					(o.fW * -2) + 'px -' + (oldDigit * o.tFH) + 'px',
					'0 -' + (newDigit * o.tFH) + 'px',
					'-' + o.fW + 'px -' + (oldDigit * o.bFH + o.bOffset) + 'px',
					(o.fW * -2) + 'px -' + (newDigit * o.bFH + o.bOffset) + 'px',
					(o.fW * -3) + 'px -' + (newDigit * o.bFH + o.bOffset) + 'px',
					'0 -' + (newDigit * o.bFH + o.bOffset) + 'px'
				];
		
				if (o.auto === true && o.pace <= 300){
					switch (n){
						case 0:
							speed = o.pace/6;
							break;
						case 1:
							speed = o.pace/5;
							break;
						case 2:
							speed = o.pace/4;
							break;
						case 3:
							speed = o.pace/3;
							break;
						default:
							speed = o.pace/1.5;
							break;
					}
				}
				else{
					speed = 80;
				}
				// Cap on slowest animation can go
				speed = (speed > 80) ? 80 : speed;
				
				function animate(){
					if (step < 7){
						w = step < 3 ? 't' : 'b';
						a = doc.getElementById(divId + "_" + w + "_d" + n);
						if (a) a.style.backgroundPosition = bp[step];
						step++;
						if (step != 3) setTimeout(animate, speed);
						else animate();
					}
				}
				
				animate();
			}
			
			// Creates array of digits for easier manipulation
			function splitToArray(input){
				return input.toString().split("").reverse();
			}
		
			// Adds new digit
			function addDigit(len, digit){
				var li = Number(len) - 1;
				newDigit = doc.createElement("ul");
				newDigit.className = 'cd';
				newDigit.id = divId + '_d' + li;
				newDigit.innerHTML = '<li class="t" id="' + divId + '_t_d' + li + '"></li><li class="b" id="' + divId + '_b_d' + li + '"></li>';
				
				if (li % 3 == 0){
					newComma = doc.createElement("ul");
					newComma.className = 'cd';
					newComma.innerHTML = '<li class="s"></li>';
					div.insertBefore(newComma, div.firstChild);
				}
				
				div.insertBefore(newDigit, div.firstChild);
				doc.getElementById(divId + "_t_d" + li).style.backgroundPosition = '0 -' + (digit * o.tFH) + 'px';
				doc.getElementById(divId + "_b_d" + li).style.backgroundPosition = '0 -' + (digit * o.bFH + o.bOffset) + 'px';
			}
			
			// Removes digit
			function removeDigit(id){
				var remove = doc.getElementById(divId + "_d" + id);
				div.removeChild(remove);
		
				// Check for leading comma
				var first = div.firstChild.firstChild;
				if ((" " + first.className + " ").indexOf(" s ") > -1 ){
					remove = first.parentNode;
					div.removeChild(remove);
				}
			}
		
			// Sets the correct digits on load
			function initialDigitCheck(init){
				// Creates the right number of digits
				var initial = init.toString(),
				count = initial.length,
				bit = 1, i;
				for (i = 0; i < count; i++){
					newDigit = doc.createElement("ul");
					newDigit.className = 'cd';
					newDigit.id = divId + '_d' + i;
					newDigit.innerHTML = newDigit.innerHTML = '<li class="t" id="' + divId + '_t_d' + i + '"></li><li class="b" id="' + divId + '_b_d' + i + '"></li>';
					div.insertBefore(newDigit, div.firstChild);
					if (bit != (count) && bit % 3 == 0){
						newComma = doc.createElement("ul");
						newComma.className = 'cd';
						newComma.innerHTML = '<li class="s"></li>';
						div.insertBefore(newComma, div.firstChild);
					}
					bit++;
				}
				// Sets them to the right number
				var digits = splitToArray(initial);
				for (i = 0; i < count; i++){
					doc.getElementById(divId + "_t_d" + i).style.backgroundPosition = '0 -' + (digits[i] * o.tFH) + 'px';
					doc.getElementById(divId + "_b_d" + i).style.backgroundPosition = '0 -' + (digits[i] * o.bFH + o.bOffset) + 'px';
				}
				// Do first animation
				if (o.auto === true) nextCount = setTimeout(doCount, o.pace);
			}
			
			// Checks values for smart increment and creates debug text
			function checkSmartValues(diff, cycles, inc, pace, time){
				var r = {result: true}, q;
				// Test conditions, all must pass to continue:
				// 1: Unrounded inc value needs to be at least 1
				r.cond1 = (diff / cycles >= 1) ? true : false;
				// 2: Don't want to overshoot the target number
				r.cond2 = (cycles * inc <= diff) ? true : false;
				// 3: Want to be within 10 of the target number
				r.cond3 = (Math.abs(cycles * inc - diff) <= 10) ? true : false;
				// 4: Total time should be within 100ms of target time.
				r.cond4 = (Math.abs(cycles * pace - time) <= 100) ? true : false;
				// 5: Calculated time should not be over target time
				r.cond5 = (cycles * pace <= time) ? true : false;
				
				// Keep track of 'good enough' values in case can't find best one within 100 loops
				if (r.cond1 && r.cond2 && r.cond4 && r.cond5){
					q = Math.abs(diff - (cycles * inc)) + Math.abs(cycles * pace - time);
					if (best.q === null) best.q = q;
					if (q <= best.q){
						best.pace = pace;
						best.inc = inc;
					}
				}
				
				for (var i = 1; i <= 5; i++){
					if (r['cond' + i] === false){
						r.result = false;
					}			
				}
				return r;
			}
			
			// http://stackoverflow.com/questions/18082/validate-numbers-in-javascript-isnumeric/1830844
			function isNumber(n) {
				return !isNaN(parseFloat(n)) && isFinite(n);
			}
			
			function clearNext(){
				clearTimeout(nextCount);
				nextCount = null;
			}
			
			// Start it up
			initialDigitCheck(o.value);
		};
	</script>
	
	<!--- Original code from http://stuntsnippets.com/javascript-countdown/ -->
	<script type="text/javascript">
	var javascript_countdown = function () {
			var time_left = 0;
			var countdowns = new Array();
			
			function countdown() {
				time_left = time_left - 1;
			}
			
			function add_leading_zero(n) {
				if(n.toString().length < 2) {
					return '0' + n;
				} else {
					return n;
				}
			}
			
			function get_values() {
				var days, hours, minutes, seconds;
				days = Math.floor( (time_left/86400));
				hours = Math.floor( (time_left/3600) % 24);
				minutes = Math.floor( (time_left/60) % 60);
				seconds = Math.floor( time_left % 60);
				
				seconds = add_leading_zero( seconds );
				minutes = add_leading_zero( minutes );
				hours = add_leading_zero( hours );
				
				day.setValue(days);
				hour.setValue(hours);
				minute.setValue(minutes);
				second.setValue(seconds);
			}
		 
			return {
				count: function () {
					countdown();
				},
				timer: function () {
					javascript_countdown.count();
					get_values();
					
					setTimeout("javascript_countdown.timer();", 1000);
				},
				//Kristian Messer requested recalculation of time that is left
				setTimeLeft: function (t) {
					time_left = t;
				},
				init: function (t) {
					time_left = t;
					javascript_countdown.timer();
				}
			};
		}();
	</script>
</head>

<body>
<table border="0" style="width: 100%">
<?php
/*
	<tr>
		<td colspan="2" align="center" valign="top" style="width: 100%;">
			
		</td>
	</tr>
*/
?>
	<tr>
		<td align="center" valign="top" style="width: 50%;">
			<img src="themes/header_img.png" alt="Logo">
			<br style="clear: both">
			<table class="messagebox">
			<tr>
				<td class="messagepicture" rowspan="2"></td>
				<td class="messagetitle">Szabadegyetem...</td>
			</tr>
			<tr>
			<td class="messagebody" valign="top">
				<!--- <span style="font-size: 18px; font-weight: bold; padding: 1px 1px 1px 1px; font-family: Trebuchet MS, Verdana, Geneva, Arial, Helvetica, sans-serif;"><span>-->
				<br style="clear: both">
				<table border="0" style="width: 100%;">
					<tr>
						<td><div id="day" class="flip-counter"></div></td>
						<td class="big">.&nbsp;</td>
						<td><div id="hour" class="flip-counter"></div></td>
						<td class="big">:</td>
						<td><div id="minute" class="flip-counter"></div></td>
						<td class="big">:</td>
						<td><div id="second" class="flip-counter"></div></td>
					</tr>
				</table>
				
				<script type="text/javascript">
					var day = new flipCounter('day', {value:00, inc:-1, pace:86400000, auto:false});
					var hour = new flipCounter('hour', {value:00, inc:-1, pace:3600000, auto:false});
					var minute = new flipCounter('minute', {value:00, inc:-1, pace:60000, auto:false});
					var second = new flipCounter('second', {value:00, inc:-1, pace:1000, auto:false});
					
					var current = new Date();
					var target = new Date("November 10, 2012 09:00:00");
					var diff_ms = (target - current);
					
					javascript_countdown.init(diff_ms / 1000);
				</script>
				
			</td>
			</tr>
			</table>
		</td>
		<td align="center" valign="top" style="width: 50%;">
			<img style="width: 100%;" src="toasty.png">
		</td>
	</tr>
</table>
<?php
/*
<table border="0" style="width: 100%">
	<tr>
		<td align="center" valign="top" style="width: 50%;"></td>
		<td align="center" valign="top">
			<table class="messagebox">
			<tr>
				<td class="messagepicture" rowspan="2"></td>
				<td class="messagetitle">Karbantartás alatt!</td>
			</tr>
			<tr>
			<td class="messagebody" valign="top">
				<span style="font-size: 18px; font-weight: bold; padding: 1px 1px 1px 1px; font-family: Trebuchet MS, Verdana, Geneva, Arial, Helvetica, sans-serif;">&bdquo;Ez lesz az az este mire évek óta vágytál / Megvalósult álmainkból összeáll a Gála!&rdquo;</span><br> 
				<br style="clear: both">
				A rendszer karbantartás alatt áll, ezért a nagyközönség általi elérése letiltásra került.<br>
				<br>
				A kódbázisban a következő módosítások lettek végrehajta az utóbbi időben, nagy valószínűséggel ezek valamelyike (vagy mindegyike) kerül telepítésre:<br>
				<tt><?php
					//exec("svn log -r HEAD:726 -l 5", $svnlog);
					
					//foreach ($svnlog as &$logline)
						//echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $logline)."<br>\n";
					sleep( rand(0,1) );
					echo "Failed to download the changelog of freeuniversity-organiser: Download queue destroyed.";
				?></tt><br>
				<br>
				Természetesen előfordulhat, hogy a karbantartási munka a tőprojekt helyett a kiszolgálói környezetet érinti (frissítés, konfigurálás, hardvercsere). <span style="font-family: Geneva, Arial, Helvetica, sans-serif;">Ne aggódj, vissza fogunk térni.</span>
			</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
*/
?>
</body>
</html>
