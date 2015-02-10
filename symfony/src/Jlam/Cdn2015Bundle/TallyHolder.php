<?php

namespace Jlam\Cdn2015Bundle;

/**
 *
 * @author jlam
 *        
 */
class TallyHolder {
	
	protected $tally;
	
	const DEFAULT_SEPERATOR = '>';
	const DEFAULT_PREFIX	= '';
	
	/**
	 * Hello
	 */
	public function __construct() {
		$this->tally = array();
	}
	
	/**
	 * Adds the additive to the existing tally 
	 */
	public function add($additive) {
		
		if(!is_array($additive)) {
			$this->tally += $additive;
			return;
		}
		
		$flatAdditive =	 self::array_flat($additive);
		
		foreach($flatAdditive as $path => $addValue) {
			$this->tally[$path] += $addValue;
		}
	}
	
	/**
	 * Transforms a multidimensional array
	 * into a flat array, keys representing path to that array 
	 * 
	 * @param array $array
	 * @param string $prefix
	 * @author J.Bruni http://stackoverflow.com/questions/9546181/flatten-multidimensional-array-concatenating-keys#answer-9546302
	 */
	protected static function array_flat($array, $seperator = self::DEFAULT_SEPERATOR, $prefix = self::DEFAULT_PREFIX)
	{
	    $result = array();
	
	    foreach ($array as $key => $value)
	    {
	        $new_key = $prefix . (empty($prefix) ? '' : $seperator) . $key;
	
	        if (is_array($value))
	        {
	            $result = array_merge($result, self::array_flat($value, $new_key));
	        }
	        else
	        {
	            $result[$new_key] = $value;
	        }
	    }
	
	    return $result;
	}
	
	/**
	 * Transforms a one dimensional array given above back into 
	 * a multidimensional array 
	 * 
	 * Assumes empty prefix was used
	 * 
	 * @author jlam@credil.org
	 * @param unknown $array
	 * @param unknown $seperator
	 */
	protected static function array_unflat($array, $seperator = self::DEFAULT_SEPERATOR)
	{
		$result = array();
		foreach ( $array as $path => $value ) {
			$newValue = null;
			$pathParts = explode ( $seperator, $path );
			
			if (count ( $pathParts ) == 1) {
				$newValue = $value;
			} else {
				$newValue = self::array_unflat(
					array(implode($seperator, array_shift($pathParts)) => $value)
					, $seperator);
			}
			
			$result[$pathParts[0]] = $newValue;
		}
		
		return $result;
	}
	
	
	public function getTally() {
		return self::array_unflat($this->tally);
	}
	
}

?>