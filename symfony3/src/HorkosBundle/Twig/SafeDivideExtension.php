<?php
namespace App\HorkosBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class SafeDivideExtension extends AbstractExtension
{
	public function getFunctions()
	{
	   	return array(
            'safe_divide' => new TwigFunction('safeDivide', [$this, 'safeDevide'])
        );

    }

	public function safeDivide($a, $b) {
			if($b == 0)  return 0;

			return $a/$b;
	}

	public function getName()
	{
		return 'HorkosBundle';
	}
}
