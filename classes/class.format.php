<?php

/**
* format conversions
* @version $Id$
* @package application
*/

////////////////////////////////////////////////////////////////////////////////
// Name: class.format.php
// Appl: ILIAS3
// Func: Konvertierung von Datum/Zeit-Angaben, Formatierung von Zahlwerten
//
// (c) 2001 Sascha Hofmann
//
// Autor: Sascha Hofmann
//        Hohenstaufenring 23, 50674 K�ln
//        +49-179-1305023
//        saschahofmann@gmx.de
//
// Last change: 30.Oktober 2001
////////////////////////////////////////////////////////////////////////////////


class TFormat
{
	var $ClassName = "TFormat";
	
	function TFormat ()
	{
		return;
	}


	// Holt das aktuelle Datum und gibt es im Format TT.MM.JJJJ zur�ck
	function getDateDE ()
	{
		$date = getdate();
		$datum = sprintf("%02d.%02d.%04d", $date["mday"],$date["mon"],$date["year"]);
		return $datum;
	}


	// Pr�ft eingegebes Datum und wandelt es in DB-konformen Syntax um
	// Eingabe: TT.MM.JJJJ oder T.M.JJ oder TT.MM.JJJJ HH:MM:SS oder T.M.JJ HH:MM:SS
	// Bei zweistelliger Jahresangabe wird bei YY > 70 19, bei YY < 70 20 vorgestellt
	// Ausgabe: YYYY-MM-DD oder YYYY-MM-DD HH:MM:SS
	// OPTIONAL wird die aktuelle Systemzeit hinzugef�gt (Ausgabe: YYYY-MM-DD hh:mm:ss)
	function input2date ($AInputDate)
	{
		$date=""; $y=""; $m=""; $d="";

		if (ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})",$idate,$p))
		{
			$d = $p[1];
			$m = $p[2];
			$y = $p[3];
			
			if (($d>0 && $d<32) && ($m>0 && $m<13) && (strlen($y)!=3))
			{
				if (strlen($d) == 1) $d = "0".$d;
				if (strlen($m) == 1) $m = "0".$m;

				if (strlen($y) == 2)
				{
					if ($y>=70) $y = $y + 1900;
					if ($y<70) $y = $y + 2000;
				}
				
				// is valid?
				checkdate($m, $d, $y);

				// Ausgabe mit Uhrzeit

            	// Uhrzeit holen
            	$uhrzeit = substr($AInputDate, -8);
            
            	// Uhrzeit auf G�ltigkeit pr�fen
            	if (ereg("([0-9]{2}):([0-9]{2}):([0-9]{2})",$AInputDate,$p))
            	{
					$h   = $p[1];
					$min = $p[2];
					$s   = $p[3];
					
					if (($h>-1 && $h<24) && ($min>-1 && $min<60) && ($s>-1 && $s<60))
					{
						// Uhrzeit stimmt/ist vorhanden
						$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$y,$m,$d,$h,$min,$s);
					}
				}
				else
				{
					// Uhrzeit ist falsch/fehlt; h�nge aktuelle Zeit an
					$zeit = getdate();
					$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$y,$m,$d,$zeit["hours"],$zeit["minutes"],$zeit["seconds"]);
				}
				
				// Ausgabe ohne Uhrzeit
				
				//$date = sprintf("%04d-%02d-%02d",$y,$m,$d);
				return $date;
			}
		}
	}
	

	// db-datetime to timestamp
	function dateDB2timestamp ($ADatumSQL)
	{
		$timestamp = substr($ADatumSQL, 0, 4).
					 substr($ADatumSQL, 5, 2).
					 substr($ADatumSQL, 8, 2).
					 substr($ADatumSQL, 11, 2).
					 substr($ADatumSQL, 14, 2).
					 substr($ADatumSQL, 17, 2);

		return $timestamp;
	}


	// German datetime to timestamp
	function dateDE2timestamp ($ADatum)
	{
		$timestamp = substr($ADatum, 6, 4).
					 substr($ADatum, 3, 2).
					 substr($ADatum, 0, 2).
					 substr($ADatum, 11, 2).
					 substr($ADatum, 14, 2).
					 substr($ADatum, 17, 2);
					 
		return $timestamp;
	}


	// formats db-datetime to german date
	function fdateDB2dateDE ($t)
	{
		return sprintf("%02d.%02d.%04d",substr($t, 8, 2),substr($t, 5, 2),substr($t, 0, 4));
	}


	// formats timestamp to german date
	function ftimestamp2dateDE ($t)
	{
		return sprintf("%02d.%02d.%04d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4));
	}


	// formats timestamp to german datetime
	function ftimestamp2datetimeDE ($t)
	{
		return sprintf("%02d.%02d.%04d %02d:%02d:%02d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4),substr($t, 8, 2),substr($t, 10, 2),substr($t, 12, 2));
	}


	// formats timestamp to db-datetime
	function ftimestamp2dateDB ($t)
	{
		return sprintf("%04d-%02d-%02d",substr($t, 0, 4),substr($t, 4, 2),substr($t, 6, 2));
	}


	// Datum vergleichen
	// Erwartet timestamps
	// Liefert das aktuellere Datum als Timestamp zur�ck
	function compareDates ($ADate1,$ADate2)
	{
		if ($ADate1 > $ADate2)
		{
			return $ADate1;
		}
		
		return $ADate2;
	}
	

	// Pr�ft Zahlen mit Nachkommastellen und erlaubt ein Komma als Nachstellentrenner
	function checkDecimal ($var)
	{
		return doubleval(ereg_replace (",",".",$var));
	}


	// formatiert Geldwerte (Format: 00,00 + Eurosymbol). Weiteres siehe fProzent
	function fGeld ()
	{
		$num_args = func_num_args();
		
		$geld = func_get_arg(0);
		
		if ($num_args == 1)
		{
			$test = intval($geld);
			
			if (!$test)
				return "&nbsp;";
		}
		
		return number_format($geld,2,",",".")." &euro;";
	}


	// formatiert Prozentzahlen (Format: 00,00%). Wenn nix oder eine Null �bergeben wird, wird ein Leerzeichen zur�ckgegeben
	// Wenn mehr als ein Parameter �bergeben wird, wird die Ausgabe auch bei Wert Null erzwungen
	function fProzent ()
	{
		$num_args = func_num_args();
		
		$prozent = func_get_arg(0);

		if ($num_args == 1)
		{
			$test = intval($prozent);
			
			if (!$test)
				return "&nbsp;";
		}
		
		return number_format($prozent,2,",",".")."%";
	}
	

	// Floats auf 2 Nachkommastellen runden 
	function runden ($value)
	{
		return round($value * 100) / 100;
	}
}

?>