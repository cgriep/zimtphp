<?php
/**
 * Klasse Lehrer
 * Kapselt die Funktionalit�ten f�r die Bestimmung des Lehrernamens, des 
 * K�rzels und des Benutzernamens auf dem WebServer
 */

DEFINE('LEHRERID_KUERZEL', 'Kuerzel');
DEFINE('LEHRERID_EMAIL', 'EMail');
 
DEFINE('LEHRER_MAENNLICH','M');
DEFINE('LEHRER_WEIBLICH','W');
DEFINE('LEHRER_UNBEKANNT','U');

$LEHRER_ANREDE = array(LEHRER_MAENNLICH=>'Lieber Kollege', LEHRER_WEIBLICH=>'Liebe Kollegin',LEHRER_UNBEKANNT=>'');
$LEHRER_HERRFRAU = array(LEHRER_MAENNLICH=>'Herr', LEHRER_WEIBLICH=>'Frau',LEHRER_UNBEKANNT=>'');
$LEHRER_LEHRER = array(LEHRER_MAENNLICH=>'Lehrer', LEHRER_WEIBLICH=>'Lehrerin',LEHRER_UNBEKANNT=>'');
 
class Lehrer {
	
  var $Vorname;
  var $Name;
  var $Kuerzel;
  var $Username;
  var $Sollstundenzahl;
  var $ErteilteStunden;
  // Verrechnete Stunden: + zuviel, - zuwenig Arbeit im laufenden Jahr
  var $Ueberstunden;
  var $Taetigkeit;
  var $Geschlecht;
  var $Bild;
  var $Telefon;
  var $zeigeBildOnline;
    
  function Lehrer($Kennung, $Art=LEHRERID_KUERZEL)
  {
  	$MKennung = '1';
  	switch ( $Art )
  	{
  		case LEHRERID_KUERZEL:
  		  $MKennung = 'Kuerzel="'.$Kennung.'"';
  		  if ( trim($Kennung) == '' ) $MKennung = 'Taetigkeit="XXX"';
  		  break;
  		case LEHRERID_EMAIL:
  		  $MKennung = 'EMail="'.$Kennung.'"';
  		  break;
  	}
    $sql = 'SELECT * FROM T_Lehrer WHERE '.$MKennung;
  	$query = mysql_query($sql);
  	if ( $row = mysql_fetch_array($query))
  	{
  		$this->Vorname = $row['Vorname'];
  		$this->Kuerzel = $row['Kuerzel'];
  		$this->Name = $row['Name'];
  		if ( $row['Name'] == '') $this->Name = $row['EMail'];
  		$this->Sollstundenzahl = $row['Sollstunden'];
  		$this->ErteilteStunden = $row['ErteilteStunden'];
  		$this->Ueberstunden = $row['Ermaessigungsstunden'];
  		$this->Username = $row['EMail'];
  		$this->Taetigkeit = $row['Taetigkeit'];
  		$this->Geschlecht = $row['Geschlecht'];
  		$this->Bild = $row['Bild'];
  		$this->Telefon = $row['Telefon'];
      $this->zeigeBildOnline = $row['zeigeBildOnline'];
  	}
  	else
  	{
  	  // Lehrer nicht gefunden
  	  $this->Vorname = '';
  	  $this->Kuerzel = '';
  	  $this->Name = $Kennung;
  	  $this->Sollstundenzahl = 0;
  	  $this->ErteilteStunden = 0;
  	  $this->Ermaessigungsstunden = 0;
  	  $this->Username = ''; 
  	  $this->Telefon = '';
  	  $this->Bild = '';
  	  $this->Geschlecht = ''; 
  	  if ( $Art == LEHRERID_KUERZEL ) $this->Kuerzel = $Kennung;
      if ( $Art == LEHRERID_EMAIL ) $this->Username = $Kennung;  	  	 
  	}
  	mysql_free_result($query);
  }
  
  function Anrede($art, $mitVorname = true)
  {
  	$name = $this->Name;
  	if ( $mitVorname && $this->Vorname != '') $name = $this->Vorname.' '.$name;
  	if ( ! is_array($art))
  	{
  	  return 'Anrede fehlt - '.$name;
  	}
  	elseif ( $this->Geschlecht == LEHRER_MAENNLICH || $this->Geschlecht == LEHRER_WEIBLICH )
  	  return $art[$this->Geschlecht].' '.$name;  	      	   	
  	else 
  	  return $this->Geschlecht.' '.$name;
  }
  
  //Gibt die gekuerzte Fassung eines Lehrernamens
  //Es wird immer mind. der erste Buchstabe des Vornamens mit ausgegeben.
  //Falls noetig, wird auch der Nachname abgekuerzt.
  //@param $maxLaenge: Die max. Laenge des Namens
  //@return: Der gekuerzte Name
  function gibNameKurzform($maxLaenge = 15)
  {
    $laenge = strlen($this->Name) + strlen(', ') + strlen($this->Vorname);
	
    if ($laenge > $maxLaenge) //gesamter Name zu lang
    {
      if (strlen($this->Name) > $maxLaenge - strlen(', X.')) //Nachname muss gekuerzt werden
      {
        $nname = substr($this->Name, 0, $maxLaenge - 5) . '.';
        $vname = substr($this->Vorname, 0, 1) . '.';
        $nameKurzform = $nname . ', ' . $vname;
      }
      else //Vorname kuerzen
        $nameKurzform = substr($this->Name . ', ' . $this->Vorname, 0, $maxLaenge - 1) . '.';
    }
    else //keine Kuerzung notwendig
      $nameKurzform = $this->Name . ', ' . $this->Vorname;
    return $nameKurzform;
  }
} // Klasse Lehrer

/**
   * Sucht zu einem Benutzernamen/einer E-Mail das passende K�rzel des Kollegen.
   * @param $user Der Benutzername 
   * @return das K�rzel des Kollegen bzw. eine leere Zeichenkette im Fehlerfalle
   */
  function userToKuerzel($user)
  {
    $query = mysql_query('SELECT Kuerzel FROM T_Lehrer ' .
    		"WHERE EMail='$user'");
    if ( !$row = mysql_fetch_array($query) ) 
    {
    	$row['Kuerzel'] = '';    
    }
    mysql_free_result($query);
    return $row['Kuerzel'];
  }

  /**
   * Sucht zu einem K�rzel den passenden User / E-Mail-Namen
   *  @param $Kuerzel das K�rzel des Lehrers
   *  @return den E-Mail-/Usernamen bzw. eine leere Zeichenkette im Fehlerfalle
   */
  function KuerzelToUser($Kuerzel)
  {
    $Kuerzel = mysql_real_escape_string(trim($Kuerzel));
    if ( trim($Kuerzel) == '' ) return '';
    $query = mysql_query('SELECT EMail FROM T_Lehrer WHERE Kuerzel="$Kuerzel"');
    if ( ! $row = mysql_fetch_array($query) )
    {
      $row['EMail'] = '';
    }
    mysql_free_result($query);
    return $row['EMail'];
  }

  /**
   *  Sucht den Namen und Vornamen eines Lehrers zu einem K�rzel
   *  Wenn das K�rzel unbekannt ist, wird als Name das K�rzel, als Vorname 
   *  nichts zur�ckgegeben.
   *  @param $Kuerzel das K�rzel des Lehrers
   *  @return ein assoziiertes Feld mit Name und Vorname
   */
  function KuerzelToLehrer($Kuerzel)
  {
    $Kuerzel = mysql_real_escape_string(trim($Kuerzel));
    if ( trim($Kuerzel) == '' ) return array('Name'=>'', 'Vorname'=>'');
    
    $sql = 'SELECT DISTINCT Name, Vorname FROM T_Lehrer '.
           " WHERE Kuerzel='$Kuerzel' LIMIT 1";
    $query = mysql_query($sql);
    $Name = $Kuerzel;
    $Vorname = '';
    if ( $row = mysql_fetch_array($query) )
    {
      $Name = trim($row['Name']);
      $Vorname = trim($row['Vorname']);
    }
    mysql_free_result($query);
    return array('Name'=>$Name, 'Vorname'=>$Vorname);
  }
?>