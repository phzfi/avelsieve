<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: DO_Sieve_File.class.php,v 1.2 2007/01/17 13:46:10 avel Exp $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Skeleton for a file-based backend of Sieve scripts storage.
 */
class DO_Sieve_File extends DO_Sieve {
    /**
     * Class Constructor
     */
    function DO_Sieve_File() {
        global $sieve_capabilities, $avelsieve_hardcoded_capabilities, $avelsieve_file_backend_options, $username;
        $this->capabilities = $sieve_capabilities = $avelsieve_hardcoded_capabilities;

        sqgetGlobalVar('rules', $rules, SQ_SESSION);
        $this->rules = $rules;

        $this->filename = str_replace('%u', $username, $avelsieve_file_backend_options['avelsieve_default_file']);
    }

    /**
     * This function initializes the avelsieve environment. Basically, it makes
     * sure that there is a valid sieve_capability array.
     * 
     * Note: In this backend all initialisation is done in DO_Sieve_File()
     *
     * @return void
     */
    function init() {
    }

    /**
     * Login to SIEVE server. Also saves the capabilities in Session.
     *
     * @return boolean
     */
    function login() {
	    if(is_object($this->sieve)) {
		    return true;
	    }
        // fopen();
    }

    /**
     * Get scripts list from SIEVE server.
     */
    function listscripts() {
        $scripts = array();
        /* dirlist() ... */
        return $scripts;
    }

    /**
     * Get rules from specified script of Sieve server
     *
     * @param string $scriptname
     * @param array $scriptinfo
     * @return array
     */
    function load($scriptname = 'phpscript', &$rules, &$scriptinfo) {
        if (file_exists($this->filename)) {
            $sievescript = file_get_contents($this->filename);
        } else {
            // this is needed because the plugin tries to load the script without
            // previously checking whether it's there or not
            $sievescript = "";
        }
        /* If error: */
        if(false === $sievescript) {
            $prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
            textdomain ('avelsieve');
            $errormsg = _("Could not read SIEVE script from");
            $errormsg .= " \"" . $this->filename."\".<br />";
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            exit;
        }
        /* Extract rules from $sievescript. */
        $rules = avelsieve_extract_rules($sievescript, $scriptinfo);
        return true;
    }

    /**
     * Upload script
     *
     * @param string $newscript The SIEVE script to be uploaded
     * @param string $scriptname Name of script
     * @return true on success, false upon failure
     */
    function save($newscript, $scriptname = 'phpscript') {
        $ret = file_put_contents($this->filename, $newscript);
        /* If error: */
        if(false === $ret) {
            $errormsg = '<p>';
            $errormsg .= _("Unable to save script to");
            $errormsg .= " \"" . $this->filename."\".<br />";
            $errormsg .= '</p>';
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            return false;
        }
    }
    
    /**
     * Deletes a script on SIEVE server.
     *
     * @param object $sieve Sieve class connection handler.
     * @param string $script 
     * @return true on success, false upon failure
     */
    function delete($script = 'phpscript') {
        $ret = unlink($this->filename);
        /* If Error: */
        if(!$ret) {
            $errormsg = sprintf( _("Could not delete script \"%s\" from server."), $this->filename) .
                '<br/>';
		    $errormsg .= _("Please contact your administrator.");
		    print_errormsg($errormsg);
	        return false;
	    }
    }

    /**
     * Log Out
     */
    function logout() {
    }
}
?>
