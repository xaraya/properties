/* 
    calendar-cs-win.js
    language: Czech
    encoding: windows-1250
    author: Lubos Jerabek (xnet@seznam.cz)
            Jan Uhlir (espinosa@centrum.cz)
*/

// ** I18N
Calendar._DN  = new Array('Ned?le','Pond?lí','Úterý','St?eda','?tvrtek','Pátek','Sobota','Ned?le');
Calendar._SDN = new Array('Ne','Po','Út','St','?t','Pá','So','Ne');
Calendar._MN  = new Array('Leden','Únor','B?ezen','Duben','Kv?ten','?erven','?ervenec','Srpen','Zá?í','?íjen','Listopad','Prosinec');
Calendar._SMN = new Array('Led','Úno','B?e','Dub','Kv?','?rv','?vc','Srp','Zá?','?íj','Lis','Pro');

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O komponent? kalendá?";
Calendar._TT["TOGGLE"] = "Zm?na prvního dne v týdnu";
Calendar._TT["PREV_YEAR"] = "P?edchozí rok (p?idrž pro menu)";
Calendar._TT["PREV_MONTH"] = "P?edchozí m?síc (p?idrž pro menu)";
Calendar._TT["GO_TODAY"] = "Dnešní datum";
Calendar._TT["NEXT_MONTH"] = "Další m?síc (p?idrž pro menu)";
Calendar._TT["NEXT_YEAR"] = "Další rok (p?idrž pro menu)";
Calendar._TT["SEL_DATE"] = "Vyber datum";
Calendar._TT["DRAG_TO_MOVE"] = "Chy? a táhni, pro p?esun";
Calendar._TT["PART_TODAY"] = " (dnes)";
Calendar._TT["MON_FIRST"] = "Ukaž jako první Pond?lí";
//Calendar._TT["SUN_FIRST"] = "Ukaž jako první Ned?li";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Výb?r datumu:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Použijte tla?ítka " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " k výb?ru m?síce\n" +
"- Podržte tla?ítko myši na jakémkoliv z t?ch tla?ítek pro rychlejší výb?r.";

Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Výb?r ?asu:\n" +
"- Klikn?te na jakoukoliv z ?ástí výb?ru ?asu pro zvýšení.\n" +
"- nebo Shift-click pro snížení\n" +
"- nebo klikn?te a táhn?te pro rychlejší výb?r.";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Zobraz %s první";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zav?ít";
Calendar._TT["TODAY"] = "Dnes";
Calendar._TT["TIME_PART"] = "(Shift-)Klikni nebo táhni pro zm?nu hodnoty";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "d.m.yy";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "?as:";
