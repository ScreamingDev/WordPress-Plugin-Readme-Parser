#!/usr/bin/env php
<?php

if ( ! isset( $argv[1] ) || ! $argv[1] || ! is_readable( $argv[1] ) ) {
	die( 'Please provide a readable README file: ' . basename( __FILE__ ) . ' path/to/README.md' . PHP_EOL );
}

$readme_path = $argv[1];

require_once __DIR__ . '/../src/parse-readme.php';

$parser = new WordPress_Readme_Parser();
$result = $parser->parse_readme( $readme_path );

function validate_readme( $result ) {

	$warnings     = array();
	$fatal_errors = array();
	$notes        = array();

	// fatal errors
	if ( ! isset( $result['name'] ) || ! $result['name'] ) {
		$fatal_errors[] = 'No plugin name detected.  Plugin names look like: === Plugin Name ===';
	}

	// warnings
	if ( ! isset( $result['requires_at_least'] ) || ! $result['requires_at_least'] ) {
		$warnings[] = 'Requires at least is missing';
	}
	if ( ! isset( $result['tested_up_to'] ) || ! $result['tested_up_to'] ) {
		$warnings[] = 'Tested up to is missing';
	}
	if ( ! isset( $result['stable_tag'] ) || ! $result['stable_tag'] ) {
		$warnings[] = 'Stable tag is missing.  Hint: If you treat /trunk/ as stable, put Stable tag: trunk';
	}
	if ( ! isset( $result['contributors'] ) || ! count( $result['contributors'] ) ) {
		$warnings[] = 'No Contributors listed';
	}
	if ( ! isset( $result['tags'] ) || ! count( $result['tags'] ) ) {
		$warnings[] = 'No Tags specified';
	}
	if ( ! isset( $result['is_excerpt'] ) || $result['is_excerpt'] ) {
		$warnings[] = 'No == Description == section was found... your short description section will be used instead';
	}
	if ( ! isset( $result['is_truncated'] ) || $result['is_truncated'] ) {
		$warnings[] = 'Your short description exceeds the 150 character limit';
	}


	// notes
	if ( ! isset( $result['sections'] ) || ! $result['sections']['installation'] ) {
		$notes[] = 'No == Installation == section was found';
	}
	if ( ! isset( $result['sections'] ) || ! $result['sections']['frequently_asked_questions'] ) {
		$notes[] = 'No == Frequently Asked Questions == section was found';
	}
	if ( ! isset( $result['sections'] ) || ! $result['sections']['changelog'] ) {
		$notes[] = 'No == Changelog == section was found';
	}
	if ( ! isset( $result['upgrade_notice'] ) || ! $result['upgrade_notice'] ) {
		$notes[] = 'No == Upgrade Notice == section was found';
	}
	if ( ! isset( $result['sections'] ) || ! $result['sections']['screenshots'] ) {
		$notes[] = 'No == Screenshots == section was found';
	}
//	if ( ! isset( $result['donate_link'] ) || ! $result['donate_link'] ) {
//		$notes[] = 'No donate link was found';
//	}

	return array(
		'warnings'     => $warnings,
		'fatal_errors' => $fatal_errors,
		'notes'        => $notes,
	);
}


$messages = validate_readme( $result );

if ( ! $messages['warnings'] && ! $messages['fatal_errors'] && ! $messages['notes'] ) {
	exit( 0 );
}

function show_block($head, $messages) {
	echo $head . ': ' . PHP_EOL . '  - ' . implode( PHP_EOL . '  - ', $messages );
}

// something is wrong => code 1
$exitCode = 1;

if ( $messages['fatal_errors'] ) {
	// some warnings => code 8
	show_block('Errors', $messages['fatal_errors']);
	$exitCode += 8;
}

if ( $messages['warnings'] ) {
	// some warnings => code 4
	show_block('Warnings', $messages['warnings']);
	$exitCode += 4;
}

if ( $messages['notes'] ) {
	// some notes => code 2
	show_block('Notices', $messages['notes']);
	$exitCode += 2;
}

echo PHP_EOL;

exit( $exitCode );