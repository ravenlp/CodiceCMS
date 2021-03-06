<?php
/**
 * GeSHi - Generic Syntax Highlighter
 *
 * The GeSHi class for Generic Syntax Highlighting. Please refer to the
 * documentation at http://qbnz.com/highlighter/documentation.php for more
 * information about how to use this class.
 *
 * For changes, release notes, TODOs etc, see the relevant files in the docs/
 * directory.
 *
 *   This file is part of GeSHi.
 *
 *  GeSHi is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  GeSHi is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with GeSHi; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package    geshi
 * @subpackage core
 * @author     Nigel McNie <nigel@geshi.org>, Benny Baumann <BenBE@omorphia.de>
 * @copyright  (C) 2004 - 2007 Nigel McNie, (C) 2007 - 2008 Benny Baumann
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

//
// GeSHi Constants
// You should use these constant names in your programs instead of
// their values - you never know when a value may change in a future
// version
//

/** The version of this GeSHi file */
define('GESHI_VERSION', '1.0.8');

// Define the root directory for the GeSHi code tree
if (!defined('GESHI_ROOT')) {
    /** The root directory for GeSHi */
    define('GESHI_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
/** The language file directory for GeSHi
    @access private */
define('GESHI_LANG_ROOT', GESHI_ROOT . 'geshi' . DIRECTORY_SEPARATOR);


// Line numbers - use with enable_line_numbers()
/** Use no line numbers when building the result */
define('GESHI_NO_LINE_NUMBERS', 0);
/** Use normal line numbers when building the result */
define('GESHI_NORMAL_LINE_NUMBERS', 1);
/** Use fancy line numbers when building the result */
define('GESHI_FANCY_LINE_NUMBERS', 2);

// Container HTML type
/** Use nothing to surround the source */
define('GESHI_HEADER_NONE', 0);
/** Use a "div" to surround the source */
define('GESHI_HEADER_DIV', 1);
/** Use a "pre" to surround the source */
define('GESHI_HEADER_PRE', 2);
/** Use a pre to wrap lines when line numbers are enabled or to wrap the whole code. */
define('GESHI_HEADER_PRE_VALID', 3);
/**
 * Use a "table" to surround the source:
 *
 *  <table>
 *    <thead><tr><td colspan="2">$header</td></tr></thead>
 *    <tbody><tr><td><pre>$linenumbers</pre></td><td><pre>$code></pre></td></tr></tbody>
 *    <tfooter><tr><td colspan="2">$footer</td></tr></tfoot>
 *  </table>
 *
 * this is essentially only a workaround for Firefox, see sf#1651996 or take a look at
 * https://bugzilla.mozilla.org/show_bug.cgi?id=365805
 * @note when linenumbers are disabled this is essentially the same as GESHI_HEADER_PRE
 */
define('GESHI_HEADER_PRE_TABLE', 4);

// Capatalisation constants
/** Lowercase keywords found */
define('GESHI_CAPS_NO_CHANGE', 0);
/** Uppercase keywords found */
define('GESHI_CAPS_UPPER', 1);
/** Leave keywords found as the case that they are */
define('GESHI_CAPS_LOWER', 2);

// Link style constants
/** Links in the source in the :link state */
define('GESHI_LINK', 0);
/** Links in the source in the :hover state */
define('GESHI_HOVER', 1);
/** Links in the source in the :active state */
define('GESHI_ACTIVE', 2);
/** Links in the source in the :visited state */
define('GESHI_VISITED', 3);

// Important string starter/finisher
// Note that if you change these, they should be as-is: i.e., don't
// write them as if they had been run through htmlentities()
/** The starter for important parts of the source */
define('GESHI_START_IMPORTANT', '<BEGIN GeSHi>');
/** The ender for important parts of the source */
define('GESHI_END_IMPORTANT', '<END GeSHi>');

/**#@+
 *  @access private
 */
// When strict mode applies for a language
/** Strict mode never applies (this is the most common) */
define('GESHI_NEVER', 0);
/** Strict mode *might* apply, and can be enabled or
    disabled by {@link GeSHi->enable_strict_mode()} */
define('GESHI_MAYBE', 1);
/** Strict mode always applies */
define('GESHI_ALWAYS', 2);

// Advanced regexp handling constants, used in language files
/** The key of the regex array defining what to search for */
define('GESHI_SEARCH', 0);
/** The key of the regex array defining what bracket group in a
    matched search to use as a replacement */
define('GESHI_REPLACE', 1);
/** The key of the regex array defining any modifiers to the regular expression */
define('GESHI_MODIFIERS', 2);
/** The key of the regex array defining what bracket group in a
    matched search to put before the replacement */
define('GESHI_BEFORE', 3);
/** The key of the regex array defining what bracket group in a
    matched search to put after the replacement */
define('GESHI_AFTER', 4);
/** The key of the regex array defining a custom keyword to use
    for this regexp's html tag class */
define('GESHI_CLASS', 5);

/** Used in language files to mark comments */
define('GESHI_COMMENTS', 0);

/** Used to work around missing PHP features **/
define('GESHI_PHP_PRE_433', !(version_compare(PHP_VERSION, '4.3.3') === 1));

/** make sure we can call stripos **/
if (!function_exists('stripos')) {
    // the offset param of preg_match is not supported below PHP 4.3.3
    if (GESHI_PHP_PRE_433) {
        /**
         * @ignore
         */
        function stripos($haystack, $needle, $offset = null) {
            if (!is_null($offset)) {
                $haystack = substr($haystack, $offset);
            }
            if (preg_match('/'. preg_quote($needle, '/') . '/', $haystack, $match, PREG_OFFSET_CAPTURE)) {
                return $match[0][1];
            }
            return false;
        }
    }
    else {
        /**
         * @ignore
         */
        function stripos($haystack, $needle, $offset = null) {
            if (preg_match('/'. preg_quote($needle, '/') . '/', $haystack, $match, PREG_OFFSET_CAPTURE, $offset)) {
                return $match[0][1];
            }
            return false;
        }
    }
}

/** some old PHP / PCRE subpatterns only support up to xxx subpatterns in
    regular expressions. Set this to false if your PCRE lib is up to date
    @see GeSHi->optimize_regexp_list()
    TODO: are really the subpatterns the culprit or the overall length of the pattern?
    **/
define('GESHI_MAX_PCRE_SUBPATTERNS', 500);

//Number format specification
/** Basic number format for integers */
define('GESHI_NUMBER_INT_BASIC', 1);        //Default integers \d+
/** Enhanced number format for integers like seen in C */
define('GESHI_NUMBER_INT_CSTYLE', 2);       //Default C-Style \d+[lL]?
/** Number format to highlight binary numbers with a suffix "b" */
define('GESHI_NUMBER_BIN_SUFFIX', 16);           //[01]+[bB]
/** Number format to highlight binary numbers with a prefix % */
define('GESHI_NUMBER_BIN_PREFIX_PERCENT', 32);   //%[01]+
/** Number format to highlight binary numbers with a prefix 0b (C) */
define('GESHI_NUMBER_BIN_PREFIX_0B', 64);        //0b[01]+
/** Number format to highlight octal numbers with a leading zero */
define('GESHI_NUMBER_OCT_PREFIX', 256);           //0[0-7]+
/** Number format to highlight octal numbers with a suffix of o */
define('GESHI_NUMBER_OCT_SUFFIX', 512);           //[0-7]+[oO]
/** Number format to highlight hex numbers with a prefix 0x */
define('GESHI_NUMBER_HEX_PREFIX', 4096);           //0x[0-9a-fA-F]+
/** Number format to highlight hex numbers with a suffix of h */
define('GESHI_NUMBER_HEX_SUFFIX', 8192);           //[0-9][0-9a-fA-F]*h
/** Number format to highlight floating-point numbers without support for scientific notation */
define('GESHI_NUMBER_FLT_NONSCI', 65536);          //\d+\.\d+
/** Number format to highlight floating-point numbers without support for scientific notation */
define('GESHI_NUMBER_FLT_NONSCI_F', 131072);       //\d+(\.\d+)?f
/** Number format to highlight floating-point numbers with support for scientific notation (E) and optional leading zero */
define('GESHI_NUMBER_FLT_SCI_SHORT', 262144);      //\.\d+e\d+
/** Number format to highlight floating-point numbers with support for scientific notation (E) and required leading digit */
define('GESHI_NUMBER_FLT_SCI_ZERO', 524288);       //\d+(\.\d+)?e\d+
//Custom formats are passed by RX array

// Error detection - use these to analyse faults
/** No sourcecode to highlight was specified
 * @deprecated
 */
define('GESHI_ERROR_NO_INPUT', 1);
/** The language specified does not exist */
define('GESHI_ERROR_NO_SUCH_LANG', 2);
/** GeSHi could not open a file for reading (generally a language file) */
define('GESHI_ERROR_FILE_NOT_READABLE', 3);
/** The header type passed to {@link GeSHi->set_header_type()} was invalid */
define('GESHI_ERROR_INVALID_HEADER_TYPE', 4);
/** The line number type passed to {@link GeSHi->enable_line_numbers()} was invalid */
define('GESHI_ERROR_INVALID_LINE_NUMBER_TYPE', 5);
/**#@-*/


/**
 * The GeSHi Class.
 *
 * Please refer to the documentation for GeSHi 1.0.X that is available
 * at http://qbnz.com/highlighter/documentation.php for more information
 * about how to use this class.
 *
 * @package   geshi
 * @author    Nigel McNie <nigel@geshi.org>, Benny Baumann <BenBE@omorphia.de>
 * @copyright (C) 2004 - 2007 Nigel McNie, (C) 2007 - 2008 Benny Baumann
 */
class GeSHi {
    /**#@+
     * @access private
     */
    /**
     * The source code to highlight
     * @var string
     */
    var $source = '';

    /**
     * The language to use when highlighting
     * @var string
     */
    var $language = '';

    /**
     * The data for the language used
     * @var array
     */
    var $language_data = array();

    /**
     * The path to the language files
     * @var string
     */
    var $language_path = GESHI_LANG_ROOT;

    /**
     * The error message associated with an error
     * @var string
     * @todo check err reporting works
     */
    var $error = false;

    /**
     * Possible error messages
     * @var array
     */
    var $error_messages = array(
        GESHI_ERROR_NO_SUCH_LANG => 'GeSHi could not find the language {LANGUAGE} (using path {PATH})',
        GESHI_ERROR_FILE_NOT_READABLE => 'The file specified for load_from_file was not readable',
        GESHI_ERROR_INVALID_HEADER_TYPE => 'The header type specified is invalid',
        GESHI_ERROR_INVALID_LINE_NUMBER_TYPE => 'The line number type specified is invalid'
    );

    /**
     * Whether highlighting is strict or not
     * @var boolean
     */
    var $strict_mode = false;

    /**
     * Whether to use CSS classes in output
     * @var boolean
     */
    var $use_classes = false;

    /**
     * The type of header to use. Can be one of the following
     * values:
     *
     * - GESHI_HEADER_PRE: Source is outputted in a "pre" HTML element.
     * - GESHI_HEADER_DIV: Source is outputted in a "div" HTML element.
     * - GESHI_HEADER_NONE: No header is outputted.
     *
     * @var int
     */
    var $header_type = GESHI_HEADER_PRE;

    /**
     * Array of permissions for which lexics should be highlighted
     * @var array
     */
    var $lexic_permissions = array(
        'KEYWORDS' =>    array(),
        'COMMENTS' =>    array('MULTI' => true),
        'REGEXPS' =>     array(),
        'ESCAPE_CHAR' => true,
        'BRACKETS' =>    true,
        'SYMBOLS' =>     false,
        'STRINGS' =>     true,
        'NUMBERS' =>     true,
        'METHODS' =>     true,
        'SCRIPT' =>      true
    );

    /**
     * The time it took to parse the code
     * @var double
     */
    var $time = 0;

    /**
     * The content of the header block
     * @var string
     */
    var $header_content = '';

    /**
     * The content of the footer block
     * @var string
     */
    var $footer_content = '';

    /**
     * The style of the header block
     * @var string
     */
    var $header_content_style = '';

    /**
     * The style of the footer block
     * @var string
     */
    var $footer_content_style = '';

    /**
     * Tells if a block around the highlighted source should be forced
     * if not using line numbering
     * @var boolean
     */
    var $force_code_block = false;

    /**
     * The styles for hyperlinks in the code
     * @var array
     */
    var $link_styles = array();

    /**
     * Whether important blocks should be recognised or not
     * @var boolean
     * @deprecated
     * @todo REMOVE THIS FUNCTIONALITY!
     */
    var $enable_important_blocks = false;

    /**
     * Styles for important parts of the code
     * @var string
     * @deprecated
     * @todo As above - rethink the whole idea of important blocks as it is buggy and
     * will be hard to implement in 1.2
     */
    var $important_styles = 'font-weight: bold; color: red;'; // Styles for important parts of the code

    /**
     * Whether CSS IDs should be added to the code
     * @var boolean
     */
    var $add_ids = false;

    /**
     * Lines that should be highlighted extra
     * @var array
     */
    var $highlight_extra_lines = array();

    /**
     * Styles of lines that should be highlighted extra
     * @var array
     */
    var $highlight_extra_lines_styles = array();

    /**
     * Styles of extra-highlighted lines
     * @var string
     */
    var $highlight_extra_lines_style = 'background-color: #ffc;';

    /**
     * The line ending
     * If null, nl2br() will be used on the result string.
     * Otherwise, all instances of \n will be replaced with $line_ending
     * @var string
     */
    var $line_ending = null;

    /**
     * Number at which line numbers should start at
     * @var int
     */
    var $line_numbers_start = 1;

    /**
     * The overall style for this code block
     * @var string
     */
    var $overall_style = 'font-family:monospace;';

    /**
     *  The style for the actual code
     * @var string
     */
    var $code_style = 'font-family: monospace; font-weight: normal; font-style: normal; margin:0; padding:0; background:inherit;';

    /**
     * The overall class for this code block
     * @var string
     */
    var $overall_class = '';

    /**
     * The overall ID for this code block
     * @var string
     */
    var $overall_id = '';

    /**
     * Line number styles
     * @var string
     */
    var $line_style1 = 'font-weight: normal;';

    /**
     * Line number styles for fancy lines
     * @var string
     */
    var $line_style2 = 'font-weight: bold;';

    /**
     * Style for line numbers when GESHI_HEADER_PRE_TABLE is chosen
     * @var string
     */
    var $table_linenumber_style = 'width:1px;font-weight: normal;text-align:right;margin:0;padding:0 2px;';

    /**
     * Flag for how line numbers are displayed
     * @var boolean
     */
    var $line_numbers = GESHI_NO_LINE_NUMBERS;

    /**
     * Flag to decide if multi line spans are allowed. Set it to false to make sure
     * each tag is closed before and reopened after each linefeed.
     * @var boolean
     */
    var $allow_multiline_span = true;

    /**
     * The "nth" value for fancy line highlighting
     * @var int
     */
    var $line_nth_row = 0;

    /**
     * The size of tab stops
     * @var int
     */
    var $tab_width = 8;

    /**
     * Should we use language-defined tab stop widths?
     * @var int
     */
    var $use_language_tab_width = false;

    /**
     * Default target for keyword links
     * @var string
     */
    var $link_target = '';

    /**
     * The encoding to use for entity encoding
     * NOTE: Used with Escape Char Sequences to fix UTF-8 handling (cf. SF#2037598)
     * @var string
     */
    var $encoding = 'utf-8';

    /**
     * Should keywords be linked?
     * @var boolean
     */
    var $keyword_links = true;

    /**
     * Currently loaded language file
     * @var string
     * @since 1.0.7.22
     */
    var $loaded_language = '';

    /**
     * Wether the caches needed for parsing are built or not
     *
     * @var bool
     * @since 1.0.8
     */
    var $parse_cache_built = false;

    /**
     * Work around for Suhosin Patch with disabled /e modifier
     *
     * Note from suhosins author in config file:
     * <blockquote>
     *   The /e modifier inside <code>preg_replace()</code> allows code execution.
     *   Often it is the cause for remote code execution exploits. It is wise to
     *   deactivate this feature and test where in the application it is used.
     *   The developer using the /e modifier should be made aware that he should
     *   use <code>preg_replace_callback()</code> instead
     * </blockquote>
     *
     * @var array
     * @since 1.0.8
     */
    var $_kw_replace_group = 0;
    var $_rx_key = 0;

    /**
     * some "callback parameters" for handle_multiline_regexps
     *
     * @since 1.0.8
     * @access private
     * @var string
     */
    var $_hmr_before = '';
    var $_hmr_replace = '';
    var $_hmr_after = '';
    var $_hmr_key = 0;

    /**#@-*/

    /**
     * Creates a new GeSHi object, with source and language
     *
     * @param string The source code to highlight
     * @param string The language to highlight the source with
     * @param string The path to the language file directory. <b>This
     *               is deprecated!</b> I've backported the auto path
     *               detection from the 1.1.X dev branch, so now it
     *               should be automatically set correctly. If you have
     *               renamed the language directory however, you will
     *               still need to set the path using this parameter or
     *               {@link GeSHi->set_language_path()}
     * @since 1.0.0
     */
    function GeSHi($source = '', $language = '', $path = '') {
        if (!empty($source)) {
            $this->set_source($source);
        }
        if (!empty($language)) {
            $this->set_language($language);
        }
        $this->set_language_path($path);
    }

    /**
     * Returns an error message associated with the last GeSHi operation,
     * or false if no error has occured
     *
     * @return string|false An error message if there has been an error, else false
     * @since  1.0.0
     */
    function error() {
        if ($this->error) {
            //Put some template variables for debugging here ...
            $debug_tpl_vars = array(
                '{LANGUAGE}' => $this->language,
                '{PATH}' => $this->language_path
            );
            $msg = str_replace(
                array_keys($debug_tpl_vars),
                array_values($debug_tpl_vars),
                $this->error_messages[$this->error]);

            return "<br /><strong>GeSHi Error:</strong> $msg (code {$this->error})<br />";
        }
        return false;
    }

    /**
     * Gets a human-readable language name (thanks to Simon Patterson
     * for the idea :))
     *
     * @return string The name for the current language
     * @since  1.0.2
     */
    function get_language_name() {
        if (GESHI_ERROR_NO_SUCH_LANG == $this->error) {
            return $this->language_data['LANG_NAME'] . ' (Unknown Language)';
        }
        return $this->language_data['LANG_NAME'];
    }

    /**
     * Sets the source code for this object
     *
     * @param string The source code to highlight
     * @since 1.0.0
     */
    function set_source($source) {
        $this->source = $source;
        $this->highlight_extra_lines = array();
    }

    /**
     * Sets the language for this object
     *
     * @note since 1.0.8 this function won't reset language-settings by default anymore!
     *       if you need this set $force_reset = true
     *
     * @param string The name of the language to use
     * @since 1.0.0
     */
    function set_language($language, $force_reset = false) {
        if ($force_reset) {
            $this->loaded_language = false;
        }

        //Clean up the language name to prevent malicious code injection
        $language = preg_replace('#[^a-zA-Z0-9\-_]#', '', $language);

        $language = strtolower($language);

        //Retreive the full filename
        $file_name = $this->language_path . $language . '.php';
        if ($file_name == $this->loaded_language) {
            // this language is already loaded!
            return;
        }

        $this->language = $language;

        $this->error = false;
        $this->strict_mode = GESHI_NEVER;

        //Check if we can read the desired file
        if (!is_readable($file_name)) {
            $this->error = GESHI_ERROR_NO_SUCH_LANG;
            return;
        }

        // Load the language for parsing
        $this->load_language($file_name);
    }

    /**
     * Sets the path to the directory containing the language files. Note
     * that this path is relative to the directory of the script that included
     * geshi.php, NOT geshi.php itself.
     *
     * @param string The path to the language directory
     * @since 1.0.0
     * @deprecated The path to the language files should now be automatically
     *             detected, so this method should no longer be needed. The
     *             1.1.X branch handles manual setting of the path differently
     *             so this method will disappear in 1.2.0.
     */
    function set_language_path($path) {
        if ($path) {
            $this->language_path = ('/' == $path[strlen($path) - 1]) ? $path : $path . '/';
            $this->set_language($this->language);        // otherwise set_language_path has no effect
        }
    }

    /**
     * Sets the type of header to be used.
     *
     * If GESHI_HEADER_DIV is used, the code is surrounded in a "div".This
     * means more source code but more control over tab width and line-wrapping.
     * GESHI_HEADER_PRE means that a "pre" is used - less source, but less
     * control. Default is GESHI_HEADER_PRE.
     *
     * From 1.0.7.2, you can use GESHI_HEADER_NONE to specify that no header code
     * should be outputted.
     *
     * @param int The type of header to be used
     * @since 1.0.0
     */
    function set_header_type($type) {
        //Check if we got a valid header type
        if (!in_array($type, array(GESHI_HEADER_NONE, GESHI_HEADER_DIV,
            GESHI_HEADER_PRE, GESHI_HEADER_PRE_VALID, GESHI_HEADER_PRE_TABLE))) {
            $this->error = GESHI_ERROR_INVALID_HEADER_TYPE;
            return;
        }

        //Set that new header type
        $this->header_type = $type;
    }

    /**
     * Sets the styles for the code that will be outputted
     * when this object is parsed. The style should be a
     * string of valid stylesheet declarations
     *
     * @param string  The overall style for the outputted code block
     * @param boolean Whether to merge the styles with the current styles or not
     * @since 1.0.0
     */
    function set_overall_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->overall_style = $style;
        } else {
            $this->overall_style .= $style;
        }
    }

    /**
     * Sets the overall classname for this block of code. This
     * class can then be used in a stylesheet to style this object's
     * output
     *
     * @param string The class name to use for this block of code
     * @since 1.0.0
     */
    function set_overall_class($class) {
        $this->overall_class = $class;
    }

    /**
     * Sets the overall id for this block of code. This id can then
     * be used in a stylesheet to style this object's output
     *
     * @param string The ID to use for this block of code
     * @since 1.0.0
     */
    function set_overall_id($id) {
        $this->overall_id = $id;
    }

    /**
     * Sets whether CSS classes should be used to highlight the source. Default
     * is off, calling this method with no arguments will turn it on
     *
     * @param boolean Whether to turn classes on or not
     * @since 1.0.0
     */
    function enable_classes($flag = true) {
        $this->use_classes = ($flag) ? true : false;
    }

    /**
     * Sets the style for the actual code. This should be a string
     * containing valid stylesheet declarations. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * Note: Use this method to override any style changes you made to
     * the line numbers if you are using line numbers, else the line of
     * code will have the same style as the line number! Consult the
     * GeSHi documentation for more information about this.
     *
     * @param string  The style to use for actual code
     * @param boolean Whether to merge the current styles with the new styles
     * @since 1.0.2
     */
    function set_code_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->code_style = $style;
        } else {
            $this->code_style .= $style;
        }
    }

    /**
     * Sets the styles for the line numbers.
     *
     * @param string The style for the line numbers that are "normal"
     * @param string|boolean If a string, this is the style of the line
     *        numbers that are "fancy", otherwise if boolean then this
     *        defines whether the normal styles should be merged with the
     *        new normal styles or not
     * @param boolean If set, is the flag for whether to merge the "fancy"
     *        styles with the current styles or not
     * @since 1.0.2
     */
    function set_line_style($style1, $style2 = '', $preserve_defaults = false) {
        //Check if we got 2 or three parameters
        if (is_bool($style2)) {
            $preserve_defaults = $style2;
            $style2 = '';
        }

        //Actually set the new styles
        if (!$preserve_defaults) {
            $this->line_style1 = $style1;
            $this->line_style2 = $style2;
        } else {
            $this->line_style1 .= $style1;
            $this->line_style2 .= $style2;
        }
    }

    /**
     * Sets whether line numbers should be displayed.
     *
     * Valid values for the first parameter are:
     *
     *  - GESHI_NO_LINE_NUMBERS: Line numbers will not be displayed
     *  - GESHI_NORMAL_LINE_NUMBERS: Line numbers will be displayed
     *  - GESHI_FANCY_LINE_NUMBERS: Fancy line numbers will be displayed
     *
     * For fancy line numbers, the second parameter is used to signal which lines
     * are to be fancy. For example, if the value of this parameter is 5 then every
     * 5th line will be fancy.
     *
     * @param int How line numbers should be displayed
     * @param int Defines which lines are fancy
     * @since 1.0.0
     */
    function enable_line_numbers($flag, $nth_row = 5) {
        if (GESHI_NO_LINE_NUMBERS != $flag && GESHI_NORMAL_LINE_NUMBERS != $flag
            && GESHI_FANCY_LINE_NUMBERS != $flag) {
            $this->error = GESHI_ERROR_INVALID_LINE_NUMBER_TYPE;
        }
        $this->line_numbers = $flag;
        $this->line_nth_row = $nth_row;
    }

    /**
     * Sets wether spans and other HTML markup generated by GeSHi can
     * span over multiple lines or not. Defaults to true to reduce overhead.
     * Set it to false if you want to manipulate the output or manually display
     * the code in an ordered list.
     *
     * @param boolean Wether multiline spans are allowed or not
     * @since 1.0.7.22
     */
    function enable_multiline_span($flag) {
        $this->allow_multiline_span = (bool) $flag;
    }

    /**
     * Get current setting for multiline spans, see GeSHi->enable_multiline_span().
     *
     * @see enable_multiline_span
     * @return bool
     */
    function get_multiline_span() {
        return $this->allow_multiline_span;
    }

    /**
     * Sets the style for a keyword group. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param int     The key of the keyword group to change the styles of
     * @param string  The style to make the keywords
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_keyword_group_style($key, $style, $preserve_defaults = false) {
        //Set the style for this keyword group
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['KEYWORDS'][$key] = $style;
        } else {
            $this->language_data['STYLES']['KEYWORDS'][$key] .= $style;
        }

        //Update the lexic permissions
        if (!isset($this->lexic_permissions['KEYWORDS'][$key])) {
            $this->lexic_permissions['KEYWORDS'][$key] = true;
        }
    }

    /**
     * Turns highlighting on/off for a keyword group
     *
     * @param int     The key of the keyword group to turn on or off
     * @param boolean Whether to turn highlighting for that group on or off
     * @since 1.0.0
     */
    function set_keyword_group_highlighting($key, $flag = true) {
        $this->lexic_permissions['KEYWORDS'][$key] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for comment groups.  If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param int     The key of the comment group to change the styles of
     * @param string  The style to make the comments
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_comments_style($key, $style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['COMMENTS'][$key] = $style;
        } else {
            $this->language_data['STYLES']['COMMENTS'][$key] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for comment groups
     *
     * @param int     The key of the comment group to turn on or off
     * @param boolean Whether to turn highlighting for that group on or off
     * @since 1.0.0
     */
    function set_comments_highlighting($key, $flag = true) {
        $this->lexic_permissions['COMMENTS'][$key] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for escaped characters. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param string  The style to make the escape characters
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_escape_characters_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['ESCAPE_CHAR'][0] = $style;
        } else {
            $this->language_data['STYLES']['ESCAPE_CHAR'][0] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for escaped characters
     *
     * @param boolean Whether to turn highlighting for escape characters on or off
     * @since 1.0.0
     */
    function set_escape_characters_highlighting($flag = true) {
        $this->lexic_permissions['ESCAPE_CHAR'] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for brackets. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * This method is DEPRECATED: use set_symbols_style instead.
     * This method will be removed in 1.2.X
     *
     * @param string  The style to make the brackets
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     * @deprecated In favour of set_symbols_style
     */
    function set_brackets_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['BRACKETS'][0] = $style;
        } else {
            $this->language_data['STYLES']['BRACKETS'][0] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for brackets
     *
     * This method is DEPRECATED: use set_symbols_highlighting instead.
     * This method will be remove in 1.2.X
     *
     * @param boolean Whether to turn highlighting for brackets on or off
     * @since 1.0.0
     * @deprecated In favour of set_symbols_highlighting
     */
    function set_brackets_highlighting($flag) {
        $this->lexic_permissions['BRACKETS'] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for symbols. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param string  The style to make the symbols
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @param int     Tells the group of symbols for which style should be set.
     * @since 1.0.1
     */
    function set_symbols_style($style, $preserve_defaults = false, $group = 0) {
        // Update the style of symbols
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['SYMBOLS'][$group] = $style;
        } else {
            $this->language_data['STYLES']['SYMBOLS'][$group] .= $style;
        }

        // For backward compatibility
        if (0 == $group) {
            $this->set_brackets_style ($style, $preserve_defaults);
        }
    }

    /**
     * Turns highlighting on/off for symbols
     *
     * @param boolean Whether to turn highlighting for symbols on or off
     * @since 1.0.0
     */
    function set_symbols_highlighting($flag) {
        // Update lexic permissions for this symbol group
        $this->lexic_permissions['SYMBOLS'] = ($flag) ? true : false;

        // For backward compatibility
        $this->set_brackets_highlighting ($flag);
    }

    /**
     * Sets the styles for strings. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param string  The style to make the escape characters
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_strings_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['STRINGS'][0] = $style;
        } else {
            $this->language_data['STYLES']['STRINGS'][0] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for strings
     *
     * @param boolean Whether to turn highlighting for strings on or off
     * @since 1.0.0
     */
    function set_strings_highlighting($flag) {
        $this->lexic_permissions['STRINGS'] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for numbers. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param string  The style to make the numbers
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_numbers_style($style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['NUMBERS'][0] = $style;
        } else {
            $this->language_data['STYLES']['NUMBERS'][0] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for numbers
     *
     * @param boolean Whether to turn highlighting for numbers on or off
     * @since 1.0.0
     */
    function set_numbers_highlighting($flag) {
        $this->lexic_permissions['NUMBERS'] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for methods. $key is a number that references the
     * appropriate "object splitter" - see the language file for the language
     * you are highlighting to get this number. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param int     The key of the object splitter to change the styles of
     * @param string  The style to make the methods
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_methods_style($key, $style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['METHODS'][$key] = $style;
        } else {
            $this->language_data['STYLES']['METHODS'][$key] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for methods
     *
     * @param boolean Whether to turn highlighting for methods on or off
     * @since 1.0.0
     */
    function set_methods_highlighting($flag) {
        $this->lexic_permissions['METHODS'] = ($flag) ? true : false;
    }

    /**
     * Sets the styles for regexps. If $preserve_defaults is
     * true, then styles are merged with the default styles, with the
     * user defined styles having priority
     *
     * @param string  The style to make the regular expression matches
     * @param boolean Whether to merge the new styles with the old or just
     *                to overwrite them
     * @since 1.0.0
     */
    function set_regexps_style($key, $style, $preserve_defaults = false) {
        if (!$preserve_defaults) {
            $this->language_data['STYLES']['REGEXPS'][$key] = $style;
        } else {
            $this->language_data['STYLES']['REGEXPS'][$key] .= $style;
        }
    }

    /**
     * Turns highlighting on/off for regexps
     *
     * @param int     The key of the regular expression group to turn on or off
     * @param boolean Whether to turn highlighting for the regular expression group on or off
     * @since 1.0.0
     */
    function set_regexps_highlighting($key, $flag) {
        $this->lexic_permissions['REGEXPS'][$key] = ($flag) ? true : false;
    }

    /**
     * Sets whether a set of keywords are checked for in a case sensitive manner
     *
     * @param int The key of the keyword group to change the case sensitivity of
     * @param boolean Whether to check in a case sensitive manner or not
     * @since 1.0.0
     */
    function set_case_sensitivity($key, $case) {
        $this->language_data['CASE_SENSITIVE'][$key] = ($case) ? true : false;
    }

    /**
     * Sets the case that keywords should use when found. Use the constants:
     *
     *  - GESHI_CAPS_NO_CHANGE: leave keywords as-is
     *  - GESHI_CAPS_UPPER: convert all keywords to uppercase where found
     *  - GESHI_CAPS_LOWER: convert all keywords to lowercase where found
     *
     * @param int A constant specifying what to do with matched keywords
     * @since 1.0.1
     */
    function set_case_keywords($case) {
        if (in_array($case, array(
            GESHI_CAPS_NO_CHANGE, GESHI_CAPS_UPPER, GESHI_CAPS_LOWER))) {
            $this->language_data['CASE_KEYWORDS'] = $case;
        }
    }

    /**
     * Sets how many spaces a tab is substituted for
     *
     * Widths below zero are ignored
     *
     * @param int The tab width
     * @since 1.0.0
     */
    function set_tab_width($width) {
        $this->tab_width = intval($width);

        //Check if it fit's the constraints:
        if ($this->tab_width < 1) {
            //Return it to the default
            $this->tab_width = 8;
        }
    }

    /**
     * Sets whether or not to use tab-stop width specifed by language
     *
     * @param boolean Whether to use language-specific tab-stop widths
     * @since 1.0.7.20
     */
    function set_use_language_tab_width($use) {
        $this->use_language_tab_width = (bool) $use;
    }

    /**
     * Returns the tab width to use, based on the current language and user
     * preference
     *
     * @return int Tab width
     * @since 1.0.7.20
     */
    function get_real_tab_width() {
        if (!$this->use_language_tab_width ||
            !isset($this->language_data['TAB_WIDTH'])) {
            return $this->tab_width;
        } else {
            return $this->language_data['TAB_WIDTH'];
        }
    }

    /**
     * Enables/disables strict highlighting. Default is off, calling this
     * method without parameters will turn it on. See documentation
     * for more details on strict mode and where to use it.
     *
     * @param boolean Whether to enable strict mode or not
     * @since 1.0.0
     */
    function enable_strict_mode($mode = true) {
        if (GESHI_MAYBE == $this->language_data['STRICT_MODE_APPLIES']) {
            $this->strict_mode = ($mode) ? GESHI_ALWAYS : GESHI_NEVER;
        }
    }

    /**
     * Disables all highlighting
     *
     * @since 1.0.0
     * @todo  Rewrite with array traversal
     * @deprecated In favour of enable_highlighting
     */
    function disable_highlighting() {
        $this->enable_highlighting(false);
    }

    /**
     * Enables all highlighting
     *
     * The optional flag parameter was added in version 1.0.7.21 and can be used
     * to enable (true) or disable (false) all highlighting.
     *
     * @since 1.0.0
     * @param boolean A flag specifying whether to enable or disable all highlighting
     * @todo  Rewrite with array traversal
     */
    function enable_highlighting($flag = true) {
        $flag = $flag ? true : false;
        foreach ($this->lexic_permissions as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->lexic_permissions[$key][$k] = $flag;
                }
            } else {
                $this->lexic_permissions[$key] = $flag;
            }
        }

        // Context blocks
        $this->enable_important_blocks = $flag;
    }

    /**
     * Given a file extension, this method returns either a valid geshi language
     * name, or the empty string if it couldn't be found
     *
     * @param string The extension to get a language name for
     * @param array  A lookup array to use instead of the default one
     * @since 1.0.5
     * @todo Re-think about how this method works (maybe make it private and/or make it
     *       a extension->lang lookup?)
     * @todo static?
     */
    function get_language_name_from_extension( $extension, $lookup = array() ) {
        if ( !is_array($lookup) || empty($lookup)) {
            $lookup = array(
                'actionscript' => array('as'),
                'ada' => array('a', 'ada', 'adb', 'ads'),
                'apache' => array('conf'),
                'asm' => array('ash', 'asm'),
                'asp' => array('asp'),
                'bash' => array('sh'),
                'c' => array('c', 'h'),
                'c_mac' => array('c', 'h'),
                'caddcl' => array(),
                'cadlisp' => array(),
                'cdfg' => array('cdfg'),
                'cobol' => array('cbl'),
                'cpp' => array('cpp', 'h', 'hpp'),
                'csharp' => array(),
                'css' => array('css'),
                'delphi' => array('dpk', 'dpr', 'pp', 'pas'),
                'dos' => array('bat', 'cmd'),
                'gettext' => array('po', 'pot'),
                'html4strict' => array('html', 'htm'),
                'java' => array('java'),
                'javascript' => array('js'),
                'klonec' => array('kl1'),
                'klonecpp' => array('klx'),
                'lisp' => array('lisp'),
                'lua' => array('lua'),
                'mpasm' => array(),
                'nsis' => array(),
                'objc' => array(),
                'oobas' => array(),
                'oracle8' => array(),
                'pascal' => array(),
                'perl' => array('pl', 'pm'),
                'php' => array('php', 'php5', 'phtml', 'phps'),
                'python' => array('py'),
                'qbasic' => array('bi'),
                'sas' => array('sas'),
                'smarty' => array(),
                'vb' => array('bas'),
                'vbnet' => array(),
                'visualfoxpro' => array(),
                'xml' => array('xml')
            );
        }

        foreach ($lookup as $lang => $extensions) {
            if (in_array($extension, $extensions)) {
                return $lang;
            }
        }
        return '';
    }

    /**
     * Given a file name, this method loads its contents in, and attempts
     * to set the language automatically. An optional lookup table can be
     * passed for looking up the language name. If not specified a default
     * table is used
     *
     * The language table is in the form
     * <pre>array(
     *   'lang_name' => array('extension', 'extension', ...),
     *   'lang_name' ...
     * );</pre>
     *
     * @param string The filename to load the source from
     * @param array  A lookup array to use instead of the default one
     * @todo Complete rethink of this and above method
     * @since 1.0.5
     */
    function load_from_file($file_name, $lookup = array()) {
        if (is_readable($file_name)) {
            $this->set_source(file_get_contents($file_name));
            $this->set_language($this->get_language_name_from_extension(substr(strrchr($file_name, '.'), 1), $lookup));
        } else {
            $this->error = GESHI_ERROR_FILE_NOT_READABLE;
        }
    }

    /**
     * Adds a keyword to a keyword group for highlighting
     *
     * @param int    The key of the keyword group to add the keyword to
     * @param string The word to add to the keyword group
     * @since 1.0.0
     */
    function add_keyword($key, $word) {
        if (!in_array($word, $this->language_data['KEYWORDS'][$key])) {
            $this->language_data['KEYWORDS'][$key][] = $word;

            //NEW in 1.0.8 don't recompile the whole optimized regexp, simply append it
            if ($this->parse_cache_built) {
                $subkey = count($this->language_data['CACHED_KEYWORD_LISTS'][$key]) - 1;
                $this->language_data['CACHED_KEYWORD_LISTS'][$key][$subkey] .= '|' . preg_quote($word, '/');
            }
        }
    }

    /**
     * Removes a keyword from a keyword group
     *
     * @param int    The key of the keyword group to remove the keyword from
     * @param string The word to remove from the keyword group
     * @param bool   Wether to automatically recompile the optimized regexp list or not.
     *               Note: if you set this to false and @see GeSHi->parse_code() was already called once,
     *               for the current language, you have to manually call @see GeSHi->optimize_keyword_group()
     *               or the removed keyword will stay in cache and still be highlighted! On the other hand
     *               it might be too expensive to recompile the regexp list for every removal if you want to
     *               remove a lot of keywords.
     * @since 1.0.0
     */
    function remove_keyword($key, $word, $recompile = true) {
        $key_to_remove = array_search($word, $this->language_data['KEYWORDS'][$key]);
        if ($key_to_remove !== false) {
            unset($this->language_data['KEYWORDS'][$key][$key_to_remove]);

            //NEW in 1.0.8, optionally recompile keyword group
            if ($recompile && $this->parse_cache_built) {
                $this->optimize_keyword_group($key);
            }
        }
    }

    /**
     * Creates a new keyword group
     *
     * @param int    The key of the keyword group to create
     * @param string The styles for the keyword group
     * @param boolean Whether the keyword group is case sensitive ornot
     * @param array  The words to use for the keyword group
     * @since 1.0.0
     */
    function add_keyword_group($key, $styles, $case_sensitive = true, $words = array()) {
        $words = (array) $words;
        if  (empty($words)) {
            // empty word lists mess up highlighting
            return false;
        }

        //Add the new keyword group internally
        $this->language_data['KEYWORDS'][$key] = $words;
        $this->lexic_permissions['KEYWORDS'][$key] = true;
        $this->language_data['CASE_SENSITIVE'][$key] = $case_sensitive;
        $this->language_data['STYLES']['KEYWORDS'][$key] = $styles;

        //NEW in 1.0.8, cache keyword regexp
        if ($this->parse_cache_built) {
            $this->optimize_keyword_group($key);
        }
    }

    /**
     * Removes a keyword group
     *
     * @param int    The key of the keyword group to remove
     * @since 1.0.0
     */
    function remove_keyword_group ($key) {
        //Remove the keyword group internally
        unset($this->language_data['KEYWORDS'][$key]);
        unset($this->lexic_permissions['KEYWORDS'][$key]);
        unset($this->language_data['CASE_SENSITIVE'][$key]);
        unset($this->language_data['STYLES']['KEYWORDS'][$key]);

        //NEW in 1.0.8
        unset($this->language_data['CACHED_KEYWORD_LISTS'][$key]);
    }

    /**
     * compile optimized regexp list for keyword group
     *
     * @param int   The key of the keyword group to compile & optimize
     * @since 1.0.8
     */
    function optimize_keyword_group($key) {
        $this->language_data['CACHED_KEYWORD_LISTS'][$key] =
            $this->optimize_regexp_list($this->language_data['KEYWORDS'][$key]);
    }

    /**
     * Sets the content of the header block
     *
     * @param string The content of the header block
     * @since 1.0.2
     */
    function set_header_content($content) {
        $this->header_content = $content;
    }

    /**
     * Sets the content of the footer block
     *
     * @param string The content of the footer block
     * @since 1.0.2
     */
    function set_footer_content($content) {
        $this->footer_content = $content;
    }

    /**
     * Sets the style for the header content
     *
     * @param string The style for the header content
     * @since 1.0.2
     */
    function set_header_content_style($style) {
        $this->header_content_style = $style;
    }

    /**
     * Sets the style for the footer content
     *
     * @param string The style for the footer content
     * @since 1.0.2
     */
    function set_footer_content_style($style) {
        $this->footer_content_style = $style;
    }

    /**
     * Sets whether to force a surrounding block around
     * the highlighted code or not
     *
     * @param boolean Tells whether to enable or disable this feature
     * @since 1.0.7.20
     */
    function enable_inner_code_block($flag) {
        $this->force_code_block = (bool)$flag;
    }

    /**
     * Sets the base URL to be used for keywords
     *
     * @param int The key of the keyword group to set the URL for
     * @param string The URL to set for the group. If {FNAME} is in
     *               the url somewhere, it is replaced by the keyword
     *               that the URL is being made for
     * @since 1.0.2
     */
    function set_url_for_keyword_group($group, $url) {
        $this->language_data['URLS'][$group] = $url;
    }

    /**
     * Sets styles for links in code
     *
     * @param int A constant that specifies what state the style is being
     *            set for - e.g. :hover or :visited
     * @param string The styles to use for that state
     * @since 1.0.2
     */
    function set_link_styles($type, $styles) {
        $this->link_styles[$type] = $styles;
    }

    /**
     * Sets the target for links in code
     *
     * @param string The target for links in the code, e.g. _blank
     * @since 1.0.3
     */
    function set_link_target($target) {
        if (!$target) {
            $this->link_target = '';
        } else {
            $this->link_target = ' target="' . $target . '" ';
        }
    }

    /**
     * Sets styles for important parts of the code
     *
     * @param string The styles to use on important parts of the code
     * @since 1.0.2
     */
    function set_important_styles($styles) {
        $this->important_styles = $styles;
    }

    /**
     * Sets whether context-important blocks are highlighted
     *
     * @param boolean Tells whether to enable or disable highlighting of important blocks
     * @todo REMOVE THIS SHIZ FROM GESHI!
     * @deprecated
     * @since 1.0.2
     */
    function enable_important_blocks($flag) {
        $this->enable_important_blocks = ( $flag ) ? true : false;
    }

    /**
     * Whether CSS IDs should be added to each line
     *
     * @param boolean If true, IDs will be added to each line.
     * @since 1.0.2
     */
    function enable_ids($flag = true) {
        $this->add_ids = ($flag) ? true : false;
    }

    /**
     * Specifies which lines to highlight extra
     *
     * The extra style parameter was added in 1.0.7.21.
     *
     * @param mixed An array of line numbers to highlight, or just a line
     *              number on its own.
     * @param string A string specifying the style to use for this line.
     *              If null is specified, the default style is used.
     *              If false is specified, the line will be removed from
     *              special highlighting
     * @since 1.0.2
     * @todo  Some data replication here that could be cut down on
     */
    function highlight_lines_extra($lines, $style = null) {
        if (is_array($lines)) {
            //Split up the job using single lines at a time
            foreach ($lines as $line) {
                $this->highlight_lines_extra($line, $style);
            }
        } else {
            //Mark the line as being highlighted specially
            $lines = intval($lines);
            $this->highlight_extra_lines[$lines] = $lines;

            //Decide on which style to use
            if ($style === null) { //Check if we should use default style
                unset($this->highlight_extra_lines_styles[$lines]);
            } else if ($style === false) { //Check if to remove this line
                unset($this->highlight_extra_lines[$lines]);
                unset($this->highlight_extra_lines_styles[$lines]);
            } else {
                $this->highlight_extra_lines_styles[$lines] = $style;
            }
        }
    }

    /**
     * Sets the style for extra-highlighted lines
     *
     * @param string The style for extra-highlighted lines
     * @since 1.0.2
     */
    function set_highlight_lines_extra_style($styles) {
        $this->highlight_extra_lines_style = $styles;
    }

    /**
     * Sets the line-ending
     *
     * @param string The new line-ending
     * @since 1.0.2
     */
    function set_line_ending($line_ending) {
        $this->line_ending = (string)$line_ending;
    }

    /**
     * Sets what number line numbers should start at. Should
     * be a positive integer, and will be converted to one.
     *
     * <b>Warning:</b> Using this method will add the "start"
     * attribute to the &lt;ol&gt; that is used for line numbering.
     * This is <b>not</b> valid XHTML strict, so if that's what you
     * care about then don't use this method. Firefox is getting
     * support for the CSS method of doing this in 1.1 and Opera
     * has support for the CSS method, but (of course) IE doesn't
     * so it's not worth doing it the CSS way yet.
     *
     * @param int The number to start line numbers at
     * @since 1.0.2
     */
    function start_line_numbers_at($number) {
        $this->line_numbers_start = abs(intval($number));
    }

    /**
     * Sets the encoding used for htmlspecialchars(), for international
     * support.
     *
     * NOTE: This is not needed for now because htmlspecialchars() is not
     * being used (it has a security hole in PHP4 that has not been patched).
     * Maybe in a future version it may make a return for speed reasons, but
     * I doubt it.
     *
     * @param string The encoding to use for the source
     * @since 1.0.3
     */
    function set_encoding($encoding) {
        if ($encoding) {
          $this->encoding = strtolower($encoding);
        }
    }

    /**
     * Turns linking of keywords on or off.
     *
     * @param boolean If true, links will be added to keywords
     * @since 1.0.2
     */
    function enable_keyword_links($enable = true) {
        $this->keyword_links = (bool) $enable;
    }

    /**
     * Setup caches needed for styling. This is automatically called in
     * parse_code() and get_stylesheet() when appropriate. This function helps
     * stylesheet generators as they rely on some style information being
     * preprocessed
     *
     * @since 1.0.8
     * @access private
     */
    function build_style_cache() {
        //Build the style cache needed to highlight numbers appropriate
        if($this->lexic_permissions['NUMBERS']) {
            //First check what way highlighting information for numbers are given
            if(!isset($this->language_data['NUMBERS'])) {
                $this->language_data['NUMBERS'] = 0;
            }

            if(is_array($this->language_data['NUMBERS'])) {
                $this->language_data['NUMBERS_CACHE'] = $this->language_data['NUMBERS'];
            } else {
                $this->language_data['NUMBERS_CACHE'] = array();
                if(!$this->language_data['NUMBERS']) {
                    $this->language_data['NUMBERS'] =
                        GESHI_NUMBER_INT_BASIC |
                        GESHI_NUMBER_FLT_NONSCI;
                }

                for($i = 0, $j = $this->language_data['NUMBERS']; $j > 0; ++$i, $j>>=1) {
                    //Rearrange style indices if required ...
                    if(isset($this->language_data['STYLES']['NUMBERS'][1<<$i])) {
                        $this->language_data['STYLES']['NUMBERS'][$i] =
                            $this->language_data['STYLES']['NUMBERS'][1<<$i];
                        unset($this->language_data['STYLES']['NUMBERS'][1<<$i]);
                    }

                    //Check if this bit is set for highlighting
                    if($j&1) {
                        //So this bit is set ...
                        //Check if it belongs to group 0 or the actual stylegroup
                        if(isset($this->language_data['STYLES']['NUMBERS'][$i])) {
                            $this->language_data['NUMBERS_CACHE'][$i] = 1 << $i;
                        } else {
                            if(!isset($this->language_data['NUMBERS_CACHE'][0])) {
                                $this->language_data['NUMBERS_CACHE'][0] = 0;
                            }
                            $this->language_data['NUMBERS_CACHE'][0] |= 1 << $i;
                        }
                    }
                }
            }
        }
    }

    /**
     * Setup caches needed for parsing. This is automatically called in parse_code() when appropriate.
     * This function makes stylesheet generators much faster as they do not need these caches.
     *
     * @since 1.0.8
     * @access private
     */
    function build_parse_cache() {
        // cache symbol regexp
        //As this is a costy operation, we avoid doing it for multiple groups ...
        //Instead we perform it for all symbols at once.
        //
        //For this to work, we need to reorganize the data arrays.
        if ($this->lexic_permissions['SYMBOLS'] && !empty($this->language_data['SYMBOLS'])) {
            $this->language_data['MULTIPLE_SYMBOL_GROUPS'] = count($this->language_data['STYLES']['SYMBOLS']) > 1;

            $this->language_data['SYMBOL_DATA'] = array();
            $symbol_preg_multi = array(); // multi char symbols
            $symbol_preg_single = array(); // single char symbols
            foreach ($this->language_data['SYMBOLS'] as $key => $symbols) {
                if (is_array($symbols)) {
                    foreach ($symbols as $sym) {
                        $sym = $this->hsc($sym);
                        if (!isset($this->language_data['SYMBOL_DATA'][$sym])) {
                            $this->language_data['SYMBOL_DATA'][$sym] = $key;
                            if (isset($sym[1])) { // multiple chars
                                $symbol_preg_multi[] = preg_quote($sym, '/');
                            } else { // single char
                                if ($sym == '-') {
                                    // don't trigger range out of order error
                                    $symbol_preg_single[] = '\-';
                                } else {
                                    $symbol_preg_single[] = preg_quote($sym, '/');
                                }
                            }
                        }
                    }
                } else {
                    $symbols = $this->hsc($symbols);
                    if (!isset($this->language_data['SYMBOL_DATA'][$symbols])) {
                        $this->language_data['SYMBOL_DATA'][$symbols] = 0;
                        if (isset($symbols[1])) { // multiple chars
                            $symbol_preg_multi[] = preg_quote($symbols, '/');
                        } else if ($symbols == '-') {
                            // don't trigger range out of order error
                            $symbol_preg_single[] = '\-';
                        } else { // single char
                            $symbol_preg_single[] = preg_quote($symbols, '/');
                        }
                    }
                }
            }

            //Now we have an array with each possible symbol as the key and the style as the actual data.
            //This way we can set the correct style just the moment we highlight ...
            //
            //Now we need to rewrite our array to get a search string that
            $symbol_preg = array();
            if (!empty($symbol_preg_single)) {
                $symbol_preg[] = '[' . implode('', $symbol_preg_single) . ']';
            }
            if (!empty($symbol_preg_multi)) {
                $symbol_preg[] = implode('|', $symbol_preg_multi);
            }
            $this->language_data['SYMBOL_SEARCH'] = implode("|", $symbol_preg);
        }

        // cache optimized regexp for keyword matching
        // remove old cache
        $this->language_data['CACHED_KEYWORD_LISTS'] = array();
        foreach (array_keys($this->language_data['KEYWORDS']) as $key) {
            if (!isset($this->lexic_permissions['KEYWORDS'][$key]) ||
                    $this->lexic_permissions['KEYWORDS'][$key]) {
                $this->optimize_keyword_group($key);
            }
        }

        // brackets
        if ($this->lexic_permissions['BRACKETS']) {
            $this->language_data['CACHE_BRACKET_MATCH'] = array('[', ']', '(', ')', '{', '}');
            if (!$this->use_classes && isset($this->language_data['STYLES']['BRACKETS'][0])) {
                $this->language_data['CACHE_BRACKET_REPLACE'] = array(
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#91;|>',
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#93;|>',
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#40;|>',
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#41;|>',
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#123;|>',
                    '<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#125;|>',
                );
            }
            else {
                $this->language_data['CACHE_BRACKET_REPLACE'] = array(
                    '<| class="br0">&#91;|>',
                    '<| class="br0">&#93;|>',
                    '<| class="br0">&#40;|>',
                    '<| class="br0">&#41;|>',
                    '<| class="br0">&#123;|>',
                    '<| class="br0">&#125;|>',
                );
            }
        }

        //Build the parse cache needed to highlight numbers appropriate
        if($this->lexic_permissions['NUMBERS']) {
            //Check if the style rearrangements have been processed ...
            //This also does some preprocessing to check which style groups are useable ...
            if(!isset($this->language_data['NUMBERS_CACHE'])) {
                $this->build_style_cache();
            }

            //Number format specification
            //All this formats are matched case-insensitively!
            static $numbers_format = array(
                GESHI_NUMBER_INT_BASIC =>
                    '(?<![0-9a-z_\.%])(?<![\d\.]e[+\-])[1-9]\d*?(?![0-9a-z\.])',
                GESHI_NUMBER_INT_CSTYLE =>
                    '(?<![0-9a-z_\.%])(?<![\d\.]e[+\-])[1-9]\d*?l(?![0-9a-z\.])',
                GESHI_NUMBER_BIN_SUFFIX =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])[01]+?b(?![0-9a-z\.])',
                GESHI_NUMBER_BIN_PREFIX_PERCENT =>
                    '(?<![0-9a-z_\.%])(?<![\d\.]e[+\-])%[01]+?(?![0-9a-z\.])',
                GESHI_NUMBER_BIN_PREFIX_0B =>
                    '(?<![0-9a-z_\.%])(?<![\d\.]e[+\-])0b[01]+?(?![0-9a-z\.])',
                GESHI_NUMBER_OCT_PREFIX =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])0[0-7]+?(?![0-9a-z\.])',
                GESHI_NUMBER_OCT_SUFFIX =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])[0-7]+?o(?![0-9a-z\.])',
                GESHI_NUMBER_HEX_PREFIX =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])0x[0-9a-f]+?(?![0-9a-z\.])',
                GESHI_NUMBER_HEX_SUFFIX =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])\d[0-9a-f]*?h(?![0-9a-z\.])',
                GESHI_NUMBER_FLT_NONSCI =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])\d+?\.\d+?(?![0-9a-z\.])',
                GESHI_NUMBER_FLT_NONSCI_F =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])(?:\d+?(?:\.\d*?)?|\.\d+?)f(?![0-9a-z\.])',
                GESHI_NUMBER_FLT_SCI_SHORT =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])\.\d+?(?:e[+\-]?\d+?)?(?![0-9a-z\.])',
                GESHI_NUMBER_FLT_SCI_ZERO =>
                    '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])(?:\d+?(?:\.\d*?)?|\.\d+?)(?:e[+\-]?\d+?)?(?![0-9a-z\.])'
                );

            //At this step we have an associative array with flag groups for a
            //specific style or an string denoting a regexp given its index.
            $this->language_data['NUMBERS_RXCACHE'] = array();
            foreach($this->language_data['NUMBERS_CACHE'] as $key => $rxdata) {
                if(is_string($rxdata)) {
                    $regexp = $rxdata;
                } else {
                    //This is a bitfield of number flags to highlight:
                    //Build an array, implode them together and make this the actual RX
                    $rxuse = array();
                    for($i = 1; $i <= $rxdata; $i<<=1) {
                        if($rxdata & $i) {
                            $rxuse[] = $numbers_format[$i];
                        }
                    }
                    $regexp = implode("|", $rxuse);
                }

                $this->language_data['NUMBERS_RXCACHE'][$key] =
                    "/(?<!<\|\/NUM!)(?<!\d\/>)($regexp)(?!\|>)/i";
            }
        }

        $this->parse_cache_built = true;
    }

    /**
     * Returns the code in $this->source, highlighted and surrounded by the
     * nessecary HTML.
     *
     * This should only be called ONCE, cos it's SLOW! If you want to highlight
     * the same source multiple times, you're better off doing a whole lot of
     * str_replaces to replace the &lt;span&gt;s
     *
     * @since 1.0.0
     */
    function parse_code () {
        // Start the timer
        $start_time = microtime();

        // Firstly, if there is an error, we won't highlight
        if ($this->error) {
            //Escape the source for output
            $result = $this->hsc($this->source);

            //This fix is related to SF#1923020, but has to be applied regardless of
            //actually highlighting symbols.
            $result = str_replace(array('<SEMI>', '<PIPE>'), array(';', '|'), $result);

            // Timing is irrelevant
            $this->set_time($start_time, $start_time);
            $this->finalise($result);
            return $result;
        }

        // make sure the parse cache is up2date
        if (!$this->parse_cache_built) {
            $this->build_parse_cache();
        }

        // Replace all newlines to a common form.
        $code = str_replace("\r\n", "\n", $this->source);
        $code = str_replace("\r", "\n", $code);

        // Add spaces for regular expression matching and line numbers
//        $code = "\n" . $code . "\n";

        // Initialise various stuff
        $length           = strlen($code);
        $COMMENT_MATCHED  = false;
        $stuff_to_parse   = '';
        $endresult        = '';

        // "Important" selections are handled like multiline comments
        // @todo GET RID OF THIS SHIZ
        if ($this->enable_important_blocks) {
            $this->language_data['COMMENT_MULTI'][GESHI_START_IMPORTANT] = GESHI_END_IMPORTANT;
        }

        if ($this->strict_mode) {
            // Break the source into bits. Each bit will be a portion of the code
            // within script delimiters - for example, HTML between < and >
            $k = 0;
            $parts = array();
            $matches = array();
            $next_match_pointer = null;
            // we use a copy to unset delimiters on demand (when they are not found)
            $delim_copy = $this->language_data['SCRIPT_DELIMITERS'];
            $i = 0;
            while ($i < $length) {
                $next_match_pos = $length + 1; // never true
                foreach ($delim_copy as $dk => $delimiters) {
                    if(is_array($delimiters)) {
                        foreach ($delimiters as $open => $close) {
                            // make sure the cache is setup properly
                            if (!isset($matches[$dk][$open])) {
                                $matches[$dk][$open] = a