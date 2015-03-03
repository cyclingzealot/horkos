<?php
namespace Jlam\Cdn2015Bundle\Twig;

class SafeDivideExtension extends \Twig_Extension
{
	public function getFunctions()
	{
	   	return array(
            'safe_divide' => new \Twig_Function_Method($this, 'safeDivide')
        );
	   	
    }

	public function safeDivide($a, $b) {
			if($b == 0)  return 0;
			
			return $a/$b;
	}
	
	public function getName()
	{
		return 'Cdn2015Bundle';
	}
}