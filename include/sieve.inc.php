<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * This page will load in MANAGESIEVE and SIEVE includes.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve.inc.php,v 1.4 2007/01/17 13:46:11 avel Exp $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_getrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_buildrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/DO_Sieve.class.php');

?>
