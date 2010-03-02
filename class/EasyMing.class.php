<?php
/**
 * EasyMing eine Abstraktionsschicht fuer Ming
 *
 * Diese Datei stellt eine Sammlung von Klassen
 * da die die Benutzung von Ming vereinfachen
 * @author Philipp Rosenkranz <ph.rosenkranz@gmail.com>
 */
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'inc'. DIRECTORY_SEPARATOR .'colornames.inc.php';

/**
 * definiert Methoden die von allen Klassen verwendet werden sollen
 * @package allgemein
 */
class allgemein {
	/**
	 * Rechnet hexadezimale Farbwerte in
	 * Farbraumkoordinaten um.
	 *
	 * @param string $farbe
	 * @return array
	 */
	public static function hexfarbeToRGB($farbe) {
		$r = hexdec(substr($farbe, 0, 2));
		$g = hexdec(substr($farbe, 2, 2));
		$b = hexdec(substr($farbe, 4, 2));
		return array($r,$g,$b);
	}


}

/**
 * Definiert Methoden die bei allen Formen (Linien, Rechtecke usw.) verwendung finden
 * @package primitiveFormen
 */
class primitiveFormen extends allgemein {

	public $SWFShape;
	public $changedLinienArt;
	public $changedFuellFarbe;
	
	private $fuellFarbe;
	public $linienDicke;
	public $linienFarbe;

	/**
	 * Legt die Liniendicke und Farbe fuer zu malende Objekte fest
	 *
	 * @param integer $dicke
	 * @param string $farbe
	 */
	public function linienArt($dicke, $farbe) {
		$this->linienDicke = $dicke;
		$this->linienFarbe = $farbe;
		
		$farbe = self::hexfarbeToRGB($farbe);
		$this->SWFShape->setLine($dicke,$farbe[0],$farbe[1],$farbe[2]);
		$this->changedLinienArt = true;		
	}
	
	/**
	 * Legt die Fuellfarbe fuer ein Object fest
	 * 
	 * @param $farbe 
	 */
	public function fuellFarbe($farbe) {
		$farbe = self::hexfarbeToRGB($farbe);
		$this->SWFShape->setLeftFill($farbe[0],$farbe[1],$farbe[2]);
		$this->changedFuellFarbe = true;	
		$this->fuellFarbe = $farbe;	
	}
}

/**
 * Erweitert SWFMovie 
 * 
 * soweit das es lediglich als canvas bzw. Arbeitsblatt
 * zu benutzen ist.
 * @package Arbeitsblatt
 */
class Arbeitsblatt extends allgemein {

	private $width;
	private $height;
	private $bgcolor = black;
	private $bilderwechsel;
	private $bilderrate;
	public  $SWFMovie;


	/**
	 * Class constructor Methode
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return Arbeitsblatt
	 */
	function __construct($width = 800, $height = 700) {
		$this->width = $width;
		$this->height = $height;
		$this->bilderrate = 2;

		$this->SWFMovie = new SWFMovie();
		$this->SWFMovie->setDimension($this->width, $this->height);
		$this->SWFMovie->setRate($this->bilderrate);
	}

	/**
	 * Zeichnet das Dokument
	 *
	 * @return string
	 */
	public function zeichne() {
		header('Content-type: application/x-shockwave-flash');
		return $this->SWFMovie->output();
	}

	/**
	 * setzt die Breite eines Arbeitsblattes
	 *
	 * @param integer $width
	 * @return void
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}
	
	/**
	 * Gibt die Breite eines Arbeitsblattes zurueck
	 * 
	 * @return integer
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * Setzt die hoehe eines Arbeitsblattes
	 *
	 * @param integer $height
	 * @return void
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}
	
	/**
	 * Gibt die Hoehe eines Arbeitsblattes zurueck
	 * 
	 * @return integer
	 */
	public function getHeight()
	{
		return $this->height;
	}
	
	/**
	 * Bilderwechsel
	 * 
	 * Legt fest ob vor dem hinzufuegen eines Elements (line2d usw.) 
	 * ein neues transparentes Bild ueber die alten gelegt werden soll.
	 *  
	 * @param bool $bool
	 * @return void
	 */
	public function setBilderwechsel($bool = true) 
	{
		$this->bilderwechsel = $bool;
		if(!$this->bilderwechsel) $this->as2Inject("gotoFrame(1000000000000); stop();");		
	}
	
	/**
	 * Gibt an ob Bilderwechsel aktiv ist oder nicht
	 * 
	 * @return integer
	 */
	public function getBilderwechsel() 
	{
		return $this->bilderwechsel;
	}
	
	/**
	 * Bilderwechsel
	 * 
	 * Legt fest ob vor dem hinzufuegen eines Elements (line2d usw.) 
	 * ein neues transparentes Bild ueber die alten gelegt werden soll.
	 *  
	 * @param bool $rate
	 * @return void
	 */
	public function setBilderrate($rate = 2) 
	{
		$this->bilderrate = $rate;
		$this->SWFMovie->setRate($this->bilderrate);
	}
	
	/**
	 * Gibt die Bilderrate ( Bildwiederholungsfrequenz an )
	 * 
	 * @return integer
	 */
	public function getBilderrate() 
	{
		return $this->bilderrate;
	}

	public function as2Inject($string = "play();")
	{
		$this->SWFMovie->add(new SWFAction($string));
	}
	
	/**
	 * Fuegt ein Objekt dem Arbeitsblatt hinzu
	 *
	 * @param object $object
	 * @return void
	 */
	public function addElement($object)
	{
		$object = $object->zeichne();
				
		if(is_object($object->SWFShape))
		{
			$this->SWFMovie->add($object->SWFShape);
			$this->SWFMovie->nextFrame();
		}
		elseif(is_object($object->SWFText))
		{
			$this->SWFMovie->add($object->SWFText);
			$this->SWFMovie->nextFrame();
		}
		elseif(is_array($object))
		{
			foreach($object as $type => $payloadObj)
			{					
				switch ($type)
				{
					case "SWFText":
						$this->SWFMovie->add($payloadObj);
						//$this->SWFMovie->nextFrame();							
						break;
					case "SWFShape":
						$this->SWFMovie->add($payloadObj);
						//$this->SWFMovie->nextFrame();							
						break;							
				}								
			}
			$this->SWFMovie->nextFrame();				
		}
		else
		{
			throw new Exception("Parameter ist kein bekanntes Objekt!");
		}
	}

	public function zeichneKoordinatenGitter($GridVisible = true, $XTickDistance = 40, $YTickDistance = 40)
	{
		$koord = new SWFShape();
		$koord->setLine(0, 212, 212, 212);
		$koord->movePenTo( 0, 0);
		$koord->drawlineto($this->width-1, 0);
		$koord->drawlineto($this->width, $this->height-1);
		$koord->drawlineto(0, $this->height-1);
		$koord->drawlineto(0, 0);

		$koordtext = new SWFText();
		$t = new SWFFont(dirname(__FILE__) . DIRECTORY_SEPARATOR ."..".
											 DIRECTORY_SEPARATOR . "fonts".
											 DIRECTORY_SEPARATOR ."FreeSans.fdb");
		$koordtext->setfont($t);
		$koordtext->setColor(212, 212, 212);


		// Skala Striche
		$i = $XTickDistance;
		while($i < $this->width)
		{
			$koordtext->moveto($i,20);
			$koordtext->addString($i);
			if($GridVisible)
			{
				$koord->movePenTo($i,0);
				$koord->drawlineto($i,$this->height);
			}
			else
			{
				$koord->movePenTo($i,0);
				$koord->drawlineto($i,20);
			}
			$i += $XTickDistance;
		}
		$i = $YTickDistance;
		while($i < $this->height)
		{
			$koordtext->moveto(20,$i);
			$koordtext->addString($i);
			if($GridVisible)
			{
				$koord->movePenTo(0,$i);
				$koord->drawlineto($this->width,$i);
			}
			else
			{
				$koord->movePenTo(0,$i);
				$koord->drawlineto(20,$i);
			}
			$i += $YTickDistance;
		}
		$this->SWFMovie->add($koordtext);
		$this->SWFMovie->add($koord);
		$this->SWFMovie->nextFrame();
	}

	/**
	 * setzt die Hintergrundfarbe eines Arbeitsblattes
	 *
	 * @param string $farbe
	 * @return void
	 */
	public function hintergrundFarbe($farbe) {
		$farbe = self::hexfarbeToRGB($farbe);
		$this->SWFMovie->setBackground($farbe[0],$farbe[1],$farbe[2]);
	}
}

/**
 * linie2d zeichnet eine 2-Dimensionale Linie
 * @package linie2d
 */
class linie2d extends primitiveFormen {

	private $x_start;
	private $y_start;
	private $x_end;
	private $y_end;

	/**
	 * Zeichnet eine Linie von einem
	 * beliebigen Punkt
	 *
	 * @param integer $x_start
	 * @param integer $y_start
	 * @param integer $x_end
	 * @param integer $y_end
	 */
	function __construct($x_start,$y_start,$x_end,$y_end) {

		$this->x_start = $x_start;
		$this->y_start = $y_start;
		$this->x_end = $x_end;
		$this->y_end = $y_end;

		$this->SWFShape = new SWFShape();

	}

	/**
	 * Zeichnet die Linie (muss aber noch zum Arbeitsblatt
	 * hinzugefuegt werden)
	 *
	 * @return object
	 */
	public function zeichne() {
		if(!($this->changedLinienArt))
		{
			$this->linienArt(5,black);
		}
		$this->SWFShape->movePenTo($this->x_start,$this->y_start);
		$this->SWFShape->drawLineTo($this->x_end,$this->y_end);
		return $this;
	}

}

/**
 * Erstellt einen Kreis
 * @package kreis
 */
class kreis extends primitiveFormen {

	private $x_koord;
	private $y_koord;
	private $radius;

	/**
	 * Class constructor Methode
	 *
	 * @param integer $x_koord
	 * @param integer $y_koord
	 * @param float $radius
	 * @param mixed $farbe
	 */
	function __construct($x_koord = 0, $y_koord = 0, $radius =0, $farbe = false) {
			$this->x_koord = $x_koord;
			$this->y_koord = $y_koord;
			$this->radius = $radius;
			$this->farbe = $farbe;			

			$this->SWFShape = new SWFShape();
	}

	/**
	 * Zeichnet den Kreis (muss aber noch zum Arbeitsblatt
	 * hinzugefuegt werden)
	 *
	 * @return object
	 */
	public function zeichne() {
		if(!($this->changedLinienArt))
		{
			$this->linienArt(5,black);
		}
		$this->SWFShape->movePenTo($this->x_koord,$this->y_koord);
		$this->SWFShape->drawCircle($this->radius);
		if($this->changedFuellFarbe) {
			$this->fuellFarbe($this->fuellFarbe);
		}		
		return $this;
	}
}

/**
 * Zeichnet einen 2d Punkt
 * @package point2d
 */
class point2d extends primitiveFormen {

	private $bezeichnung;
	private $x_koord;
	private $y_koord;
	private $form;
	private $radius;
	private $zeichen;

	
	function __construct($bezeichnung = "P", $x_koord = 0, $y_koord = 0, $form = "kreis", $radius = 10) {		
		$this->x_koord = $x_koord;
		$this->y_koord = $y_koord;
		$this->form = $form;
		$this->radius  = $radius;
		$this->bezeichnung = $bezeichnung;
		
		if( $this->form == "kreis")
		{
			$kreis = new kreis($this->x_koord, $this->y_koord, $this->radius);
			$this->SWFShape = $kreis->SWFShape;
			$this->zeichen = $kreis;					
		} 
		elseif( $this->form == "kreuz") 
		{
			$kreuz = new kreuz($this->x_koord, $this->y_koord, $this->radius);
			$this->SWFShape = $kreuz->SWFShape;
			$this->zeichen = $kreuz;
		}		
	}

	public function zeichne() {
		if(!($this->changedLinienArt))
		{
			$this->linienArt(5,black);
		}
		
		$this->SWFShape->movePenTo($this->x_koord,$this->y_koord);
		
		if($this->form == "kreis")
		{
			$kreis = $this->SWFShape->drawCircle($this->radius);			
		} 
		elseif( $this->form == "kreuz")
		{
			if($this->changedLinienArt) $this->zeichen->linienArt($this->linienDicke, $this->linienFarbe);
			$kreuz = $this->zeichen->zeichne();				
		}
				
		$text = new Text($this->bezeichnung, $this->x_koord + $this->radius + 2, $this->y_koord + 2);
		$text_gezeichnet = $text->zeichne();			
			
		return array("SWFShape" => $this->SWFShape, "SWFText" => $text_gezeichnet->SWFText);
	}
}

class kreuz extends primitiveFormen {
	private $x_koord;
	private $y_koord;
	private $kanten_laenge;
	private $schnittwinkel;

	function __construct($x_koord = 0, $y_koord = 0, $kanten_laenge = 20, $schnittwinkel = 90) {		
		$this->x_koord = $x_koord;
		$this->y_koord = $y_koord;
		$this->kanten_laenge = $kanten_laenge;
		$this->schnittwinkel = $schnittwinkel;
		
		$this->SWFShape = new SWFShape();
	}

	public function zeichne() {
		if(!($this->changedLinienArt))
		{
			$this->linienArt(5,black);
		} 
		else 
		{
			$this->linienArt($this->linienDicke, $this->linienFarbe);
		}	
		
		$beta = 90 - ($this->schnittwinkel/2);
		$b = ($beta * M_PI / 180);	
		$hk = $this->kanten_laenge;
		
		$this->SWFShape->movePenTo($this->x_koord - $hk * cos($b),$this->y_koord + $hk * sin($b));
		$this->SWFShape->drawLineTo($this->x_koord + $hk * cos($b),$this->y_koord - $hk * sin($b));
		$this->SWFShape->movePenTo($this->x_koord + $hk * cos($b),$this->y_koord + $hk * sin($b));
		$this->SWFShape->drawLineTo($this->x_koord - $hk * cos($b),$this->y_koord - $hk * sin($b));
		
		if($this->changedFuellFarbe) {
			$this->fuellFarbe($this->fuellFarbe);
		}
				
		return $this;
	}	
}

class Text extends allgemein {

	private $x_koord;
	private $y_koord;
	private $radius;
	private $schrifttyp;
	private $groesse;
	private $color;

	public  $SWFText;
	public  $SWFFont;


	function __construct($text = "", $x_koord = 0, $y_koord = 0, $groesse = 12, $schrifttyp = "FreeSans", $color = black) {
			$this->x_koord    = $x_koord;
			$this->y_koord    = $y_koord;
			$this->schrifttyp = $schrifttyp;
			$this->text       = $text;
			$this->color      = $color;

			$this->SWFText = new SWFText();
			$this->SWFText->moveTo($this->x_koord, $this->y_koord);

			$this->schrifttyp($schrifttyp);
			$this->groesse($groesse);
			$this->color($color);
	}

	function groesse($groesse) {
		$this->groesse = $groesse;
		$this->SWFText->setHeight($groesse);
	}

	function schrifttyp($schrifttyp) {
		$this->schrifttyp = $schrifttyp;
		$this->SWFFont = new SWFFont(dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'fonts'. DIRECTORY_SEPARATOR . $this->schrifttyp .'.fdb');
		$this->SWFText->setFont($this->SWFFont);
	}

	function color($color) {
		$this->color = $color;
		$color = self::hexfarbeToRGB($color);
		$this->SWFText->setColor($color[0],$color[1],$color[2]);
	}

	/**
	 * Zeichnet den Text (muss aber noch zum Arbeitsblatt
	 * hinzugefuegt werden)
	 *
	 * @return object
	 */
	public function zeichne() {
		$this->SWFText->addString($this->text);
		return $this;
	}
}
?>