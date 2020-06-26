<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}                                             // Exit if accessed directly

//FixIn: 8.1.3.17

/** Linear Math Expression calculation.
 *  Please use instead of expression like this        5 - 3 * 2
 *  this expression                                 ( 5 - ( 3 * 2 ) )
 *  Otherwise,  the result will be -4 instead of correct -1
 *
 * @param $str_expr
 *
 * @return mixed
 */
function wpbc_str_calc( $str_expr ) {

	// Replace ,  to  .
	$str_expr = str_replace( ',', '.', $str_expr );

	// Remove any  whitespace and other non expected characters, use only: 0-9 . + _ * / ( )
	$str_expr = preg_replace( '/[^0-9\.\+\-\*\/\(\)]/', '', $str_expr );

	// Find deepest content in parentheses: (  .... )
	while ( $str_expr != ( $lowerest_expr = preg_replace_callback( '/\(([^()]*)\)/', 'wpbc_str_calc_numeric', $str_expr ) ) ) {

		$str_expr = $lowerest_expr;
	}

	// Simple linear expression without parentheses
	preg_match_all( '![-+/*].*?[\d.]+!', "+$str_expr", $m );

	return array_reduce( $m[0], 'wpbc_str_calc_math_operation' );
}


/**
 * Get in parentheses operation
 *
 * @param $m
 *
 * @return mixed
 */
function wpbc_str_calc_numeric( $m ) {

	return wpbc_str_calc( $m[1] );
}


/**
 * Make simple math  operation
 *
 * @param $n
 * @param $m
 *
 * @return float|int
 */
function wpbc_str_calc_math_operation( $n, $m ) {

	$o    = $m[0];
	$m[0] = ' ';
	return $o == '+' ? $n + $m : ( $o == '-' ? $n - $m : ( $o == '*' ? $n * $m : $n / $m ) );
}



/*  Tests
$calc = new WPBC_Calc_String();
$tests = [
	[
		'expr' => '( 5,3 + ( ( 5 - ( 3 * 2 ) ) * ( 2 - 3 .2) ) )',
		'res' => ( 5.3 + ( ( 5 - ( 3 * 2 ) ) * ( 2 - 3.2) ) )    //12.1
	],
	[
		'expr' => '(3/18)-(1/2 * 40)',
		'res' => 3/18-1/2 * 40  //-19.8333333
	],
	[
		'expr' => '13/18 * 40',
		'res' => 13/18 * 40 //28.88888888
	],
	[
		'expr' => '14 3/16/ 21 * 40',
		'res' => 143/16/ 21 * 40   // 17.02380952
	],
	[
		'expr' => '16 3/16/ 23 * 40',
		'res' => 163/16/ 23 * 40   // 17.7173913
	],
	[
		'expr' => '16-(3/16 / 30 * 60)',
		'res' => 15.625
	],
	[
		'expr' => '9.75 * 21.5/29.5',
		'res' =>  9.75 * 21.5/29.5    // 7.10593220339
	],
	[
		'expr' => '1/4 * 5',
		'res' => 1.25
	],
	[
		'expr' => '1/2 * 6',
		'res' => 3
	],
	[
		'expr' => '7 * 60',
		'res' => 420
	],
	[
		'expr' => '35/64 * 6',
		'res' => 3.28125
	],
	[
		'expr' => '100 -10',
		'res' => 90
	],
	[
		'expr' => '100 - 10',
		'res' => 90
	]
];
foreach($tests as $test){
	$res = wpbc_str_calc($test['expr']);
	echo '<pre>';
	echo "Expression: {$test['expr']}".PHP_EOL;
	echo "Expected:   #{$test['res']}#".PHP_EOL;
	echo "Calculated: #{$res}#".PHP_EOL;
	echo 'Test '.(($res ==  $test['res']  ) ? '<strong style="color:green;">pass</strong>' : '<strong style="color:red;">failed</strong>').PHP_EOL;;
	echo 'Result types: '. gettype($res) . ' - '. gettype($test['res']) .PHP_EOL;;
	echo '------------------------'.PHP_EOL.PHP_EOL.PHP_EOL;
	echo '</pre>';
}
die;
*/