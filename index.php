<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR ."class". DIRECTORY_SEPARATOR ."EasyMing.class.php";

$farben = array(cornflowerblue,black,firebrick,
				lawngreen,peru,slategray,tomato,
				darkolivegreen,chocolate);

$pz = array("kreis","kreuz");
				
$Arbeitsblatt = new Arbeitsblatt(800, 700);
$Arbeitsblatt->zeichneKoordinatenGitter();
$Arbeitsblatt->setBilderwechsel(true);
for($i = 0; $i <= 10; $i++)
{	
	$punktzeichen = $pz[rand(0,1)];
	$punktfarbe = $farben[rand(0,5)];
	
	
	$linie = new linie2d(rand(0,800),rand(0,700),rand(0,800),rand(0,700));	
	$kreis = new kreis(rand(0,800),rand(0,700),rand(50,200));
	$text = new Text("All work and no play makes Jack a dull boy", rand(0,200), rand(0,700), rand(10,50), "FreeMono", $farben[rand(0,5)]);
	$punkt = new point2d("P".$i, rand(0,800),rand(0,700), $punktzeichen, rand(2,5));	

	$linie->linienArt(rand(1,20),$farben[rand(0,5)]);
	
	$kreisfarbe = $farben[rand(0,5)];
	$kreis->linienArt(rand(1,20),$kreisfarbe);
	if(rand(0,1)) $kreis->fuellFarbe($kreisfarbe);	
	
	
	$punkt->linienArt(2, $punktfarbe);
	if(($punktzeichen == "kreis")) $punkt->fuellFarbe($punktfarbe);	
	
	$Arbeitsblatt->addElement($kreis);	
	$Arbeitsblatt->addElement($linie);
	$Arbeitsblatt->addElement($text);
	$Arbeitsblatt->addElement($punkt);		
}


$Arbeitsblatt->zeichne();


?>