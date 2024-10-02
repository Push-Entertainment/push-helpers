<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;
use Rinvex\Country\CountryLoader;
use Rinvex\Country\CountryLoaderException;

/**
 * Class Misc
 *
 * @package Push\Functions
 */
class Countries
{

    use Error;

	/**
	 * @param string $iso2
	 *
	 * @return array
	 */
    public static function getCountries( string $iso2 = '' ): array
    {
	    try {
		    return CountryLoader::countries( false, false );
	    } catch ( CountryLoaderException $e ) {
		    return [];
	    }
    }
}