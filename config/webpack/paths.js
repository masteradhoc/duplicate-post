/* global require, module */
const path = require( "path" );

const jsDistPath = path.resolve( "js", "dist" );
const jsSrcPath = path.resolve( "js", "src" );

// Entries for webpack to make bundles from.
const entry = {
	"duplicate-post-edit": "./duplicate-post-edit-script.js",
	"duplicate-post-strings": "./duplicate-post-strings.js",
	"duplicate-post-quick-edit": "./duplicate-post-quick-edit-script.js",
	"duplicate-post-options": "./duplicate-post-options.js",
	"duplicate-post-elementor": "./duplicate-post-elementor.js",
	"duplicate-post-command-palette": "./command-palette.js",
};

/**
 * Flattens a version for usage in a filename.
 *
 * @param {string} version The version to flatten.
 *
 * @returns {string} The flattened version.
 */
function flattenVersionForFile( version ) {
	const versionParts = version.split( "." );
	if ( versionParts.length === 2 && /^\d+$/.test( versionParts[1] ) ) {
		versionParts.push( 0 );
	}

	return versionParts.join( "" );
}

module.exports = {
	entry,
	jsDist: jsDistPath,
	jsSrc: jsSrcPath,
	flattenVersionForFile,
};
