<?php
/**
 * Konfiguration der Lizenzverwaltung
 * (c) 2006 Christoph Griep
 */
$dbName = "msdnaa";
$dbUser = "msdnaa";
$dbPassword = "nVbyhCRsLqVRLFAK"; // neu: nVbyhCRsLqVRLFAK, alt: msaadn

// Datenbank �ffnen
$db = mysql_connect("localhost", $dbUser, $dbPassword);
mysql_select_db($dbName, $db);

function holeProdukte($einschraenkung = '')
{
  $query = mysql_query("SELECT * FROM T_Produkte $einschraenkung ORDER BY Produkt");
  $Produkte = array();
  while ( $row = mysql_fetch_object($query))
  {
    $Produkte[$row->id] = $row->Produkt;
  }
  mysql_free_result($query);
  return $Produkte;
}
?>