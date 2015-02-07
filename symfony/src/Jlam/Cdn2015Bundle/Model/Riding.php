<?php


namespace Jlam\Cdn2015Bundle\Model;



class RidingModel {
	
	private static $prod_path;
	
	private static $local_path;
	
	private $ridingID;
	
	public function __construct($ridingID) {
		$this->ridingID = $ridingID;
	}
	
	
	public static function setProdPath($prodPath) {
		self::$prod_path = $prodPath;
	}
	
	public static function setLocalPath($localPath) {
		self::$local_path = $localPath;
	}
}