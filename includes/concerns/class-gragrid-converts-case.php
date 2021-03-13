<?php
/**
 * Gragrid case trait
 *
 * @since 2.1.0
 *
 * @package Gragrid
 * @author  Vladimir Contreras
 */

/**
 * Gragrid case trait
 *
 * @since 2.1.0
 *
 * @package Gragrid
 * @author  Vladimir Contreras
 */
trait Gragrid_Converts_Case {
	/**
	 * Convert a string from snake_case to Title Case.
	 *
	 * @since 2.1.0
	 *
	 * @param string $string String to convert.
	 * @return string
	 */
	public static function snake_to_title( string $string ) {
		$string = str_replace( '_', ' ', $string );

		return ucwords( $string );
	}
}
