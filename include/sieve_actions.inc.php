<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: sieve_actions.inc.php,v 1.35 2007/05/04 12:44:48 avel Exp $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Root class for SIEVE actions.
 *
 * Each class that extends this class describes a SIEVE action and can contain
 * the following variables:
 *
 * num			Number of action
 * capability	Required capability(ies), if any
 * text			Textual description
 * helptxt		Explanation text
 * options		Array of Options and their default values
 *
 * It can also contain these functions:
 *
 * options_html()	Returns the HTML printout of the action's options 
 */
class avelsieve_action {
    /*
     * @var boolean Flag to enable use of images and visual enhancements.
     */
	var $useimages = true;

    /**
     * @var boolean Translate generated email messages?
     */
    var $translate_return_msgs = false;

    /**
     * @var int Level of Javascript support
     */
    var $js = 0;

	/**
     * Initialize variables that we get from the configuration of avelsieve and 
     * the environment of Squirrelmail.
     *
     * @return void
	 */
    function init() {
        global $translate_return_msgs, $useimages, $javascript_on, $plugins;

        if(isset($translate_return_msgs)) {
            $this->translate_return_msgs = $translate_return_msgs;
        }
        if(isset($useimages)) {
            $this->useimages = $useimages;
        }
		if($javascript_on) {
			$this->js++;
            if(in_array('javascript_libs', $plugins)) {
			    $this->js++;
            }
		}
    }

    /**
     * Initialize other properties based on the ones defined from child classes.
     * @return void
     */
	function avelsieve_action(&$s, $rule) {
		$this->rule = $rule;
        $this->s = $s;
        
		if ($this->useimages && isset($this->image_src)) {
			$this->text = ' <img src="'.$this->image_src.'" border="0" alt="'. $this->text.'" align="middle" style="margin-left: 2px; margin-right: 4px;"/> '.
				'<strong>' . $this->text . '</strong>';
		}
	}

	/**
	 * Check if this action is valid in the current server capabilities
	 * ($this->capabilities array).
	 * @return boolean
	 */
	function is_action_valid() {
		if(isset($this->capability) && !empty($this->capability)) {
			if(!$this->s->capability_exists($this->capability)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Return All HTML Code that describes this action.
     *
     * @return string
	 */
	function action_html() {
		/* Radio button */
		$out = $this->action_radio();
        $identifier = ($this->num ? 'action_'.$this->num : $this->name);

		/* Main text */
	    $out .= '<label for="'.$identifier.'">' . $this->text .'</label>';

		if(isset($this->helptxt)) {
                $out .= ' <span id="helptxt_'.$identifier.'"'.
                        ($this->is_selected() ? ' style="display:inline"' :
                            ($this->js ? 'style="display:none"': '') ) .
                        '> &#8211; '.$this->helptxt.'</span>';
		}

		/* Options */
		if(isset($this->options) and sizeof($this->options) > 0) {
			$optval = array();
			foreach($this->options as $opt=>$defaultval) {
				if(is_array($opt)) {
					/* Two - level options, e.g. notify */
					foreach($opt as $opt2=>$defaultval2) {
						if(isset($this->rule[$opt][$opt2])) {
							$optval[$opt][$opt2] = $this->rule[$opt][$opt2];
						} else {
							$optval[$opt][$opt2] = $defaultval2;
						}
					}
				} else {
					/* Flat-level options schema */
					if(isset($this->rule[$opt])) {
						$optval[$opt] = $this->rule[$opt];
					} else {
						$optval[$opt] = $defaultval;
					}
				}
			}
			if($this->num) {
			    /* Radio Button */
				$out .= '<div id="options_'.$this->num.'"';
				if(isset($this->rule['action']) && $this->rule['action'] == $this->num) {
					$out .= '';
				} elseif($this->js) {
					$out .= ' style="display:none"';
				}
			} else {
			    /* Checkbox */
				$out .= '<div id="options_'.$this->name.'"';
				if(isset($this->rule[$this->name]) && $this->rule[$this->name]) {
					$out .= '';
				} elseif($this->js) {
					$out .= ' style="display:none"';
				}
			}
			$out .= '>';

			$out .= '<blockquote>';
			if(method_exists($this, 'options_html')) {
				$out .= $this->options_html($optval);
			} else {
				$out .= $this->options_html_generic($optval);
			}
			$out .= '</blockquote>';
			$out .= '</div>';
			unset($val);
		}
		$out .= '<br />';
		return $out;
	}

    /**
     * Shows whether an action is selected for the current rule.
     *
     * @return boolean
     * @since 1.9.8
     */
    function is_selected() {
        if(is_numeric($this->num) && $this->num > 0) {
            // For Radio-style numeric id actions.
            if(isset($this->rule['action']) && $this->rule['action'] == $this->num) {
                return true;
            }

        } else {
            // For Checkbox-style actions.
			if(isset($this->two_dimensional_options) && $this->options[$this->name]['on']) {
                return true;
            } else {
				if(isset($this->rule[$this->name])) {
                    return true;
                }
            }
        }
        return false;
    }

	/**
	 * Generic Options for an action.
	 *
	 * @todo Not implemented yet.
	 */
	function options_html_generic($val) {
		return "Not implemented yet.";
	}

	/**
	 * Output radio or checkbox button for this action.
	 * @return string
	 */
	function action_radio() {
		if($this->num) {
			/* Radio */
            $out = '<input type="radio" name="action" ';
            if($this->js) {
                $out .= 'onClick="';
				for($i=0;$i<9;$i++) {
					if($i!=$this->num) {
                        if($this->js == 2) {
                            $out .= 'if(el(\'options_'.$i.'\')) { new Effect.BlindUp(\'options_'.$i.'\'); }
                                     if(el(\'helptxt_action_'.$i.'\')) { new Effect.Fade(\'helptxt_action_'.$i.'\'); } ';
                        } else {
						    $out .= 'HideDiv(\'options_'.$i.'\'); HideDiv(\'helptxt_action_'.$i.'\');';
                        }
					}
				}
                if($this->js == 2) {
                    $out .= 'if(el(\'options_'.$this->num.'\')) { new Effect.BlindDown(\'options_'.$this->num.'\'); }
                             if(el(\'helptxt_action_'.$this->num.'\')) { new Effect.Appear(\'helptxt_action_'.$this->num.'\'); }';
                } else {
                    $out .= 'ShowDiv(\'options_'.$this->num.'\'); ShowDiv(\'helptxt_action_'.$this->num.'\');';
                }
                $out .= ' return true;"';
            }
		    $out .= ' id="action_'.$this->num.'" value="'.$this->num.'" '.
			        ($this->is_selected() ? ' checked="CHECKED"' : '') . '/> ';

		} else {
			/* Checkbox */
			$out = '<input type="checkbox" name="'.$this->name;
			if(isset($this->two_dimensional_options)) {
				$out .= '[on]';
			}
			$out .= '" onClick="ToggleShowDiv(\'helptxt_'.$this->name.'\');ToggleShowDiv(\'options_'.$this->name.'\');return true;"'.
					' id="'.$this->name.'" ' . ( $this->is_selected() ? ' checked="CHECKED"' : '' ) .
			        '/> ';
		}
		return $out;
	}
}

/**
 * Keep Action
 */
class avelsieve_action_keep extends avelsieve_action {
	var $num = 1;
	var $capability = '';
	var $options = array(); 
    var $image_src = 'images/icons/accept.png';

	function avelsieve_action_keep(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Keep Message");
        $this->helptxt = _("Save the message in your INBOX.");
		if(!isset($rule['action'])) {
			/* Hack to make the radio button selected for a new rule, for GUI
			 * niceness */
			$this->rule['action'] = 1;
		}
		$this->avelsieve_action($s, $rule);
	}
}

/**
 * Discard Action
 */
class avelsieve_action_discard extends avelsieve_action {
	var $num = 2;
	var $capability = '';
	var $options = array(); 
    var $image_src = 'images/icons/cross.png';

	function avelsieve_action_discard(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Discard");
		$this->helptxt = _("Silently discards the message; use with caution.");
		$this->avelsieve_action($s, $rule);
	}
}

/**
 * Reject Action
 */
class avelsieve_action_reject extends avelsieve_action {
	var $num = 3;
	var $capability = 'reject';
	var $options = array(
		'excuse' => ''
	);
    var $image_src = 'images/icons/arrow_undo.png';
 	
	function avelsieve_action_reject(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Reject");
		$this->helptxt = _("Send the message back to the sender, along with an excuse");

		if($this->translate_return_msgs==true) {
			$this->options['excuse'] = _("Please do not send me large attachments.");
		} else {
			$this->options['excuse'] = "Please do not send me large attachments.";
		}
		$this->avelsieve_action($s, $rule);
	}

	function options_html($val) {
		return '<textarea name="excuse" rows="4" cols="50">'.$val['excuse'].'</textarea>';
	}
}

/**
 * Redirect Action
 */
class avelsieve_action_redirect extends avelsieve_action {
	var $num = 4;
    var $image_src = 'images/icons/arrow_divide.png';

	function avelsieve_action_redirect(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Redirect");
		$this->helptxt = _("Automatically redirect the message to a different email address");
		$this->options = array(
			'redirectemail' => _("someone@example.org"),
			'keep' => ''
		);
		$this->avelsieve_action($s, $rule);
	}

	function options_html($val) {
		$out = '<input type="text" name="redirectemail" size="26" maxlength="100" value="'.htmlspecialchars($val['redirectemail']).'"/>'.
				'<br />'.
				'<input type="checkbox" name="keep" id="keep" ';
        if(!empty($val['keep'])) {
				$out .= ' checked="CHECKED"';
		}
		$out .= '/>'.
				'<label for="keep">'. _("Keep a local copy as well.") . '</label>';
		return $out;
	}

	function validate($val, &$errormsg) {
		$onemailregex = "[a-zA-Z0-9]+[a-zA-Z0-9\._-]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+";
		
		if(!preg_match("/^$onemailregex(,$onemailregex)*$/" ,	$val['redirectemail'])){
		// if(!preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" ,
				$errormsg[] = _("Incorrect email address(es). You must enter one or more valid email addresses, separated by comma.");
		}
	}
}


/**
 * Fileinto Action
 */
class avelsieve_action_fileinto extends avelsieve_action {
	var $num = 5;
	var $capability = 'fileinto';
	var $options = array(
		'folder' => '',
	);
    var $image_src = 'images/icons/folder_go.png';

    /**
     * The fileinto constructor, unlike other actions, uses the
     * property "helptxt" to put the actual option box.
     *
     * @param object $s
     * @param array $rule
     * @return void
     */
	function avelsieve_action_fileinto(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Move to Folder");
		$this->avelsieve_action($s, $rule);
		if(isset($rule['folder'])) {
			$this->helptxt = mailboxlist('folder', $rule['folder']);
		} else {
			$this->helptxt = mailboxlist('folder', false);
		}
	}
	
    /**
     * Options for fileinto
     *
     * @param array $val
     * @todo Use "official" function sqimap_mailbox_option_list()
     */
    function options_html ($val) {
        /*
        if(isset($val['folder'])) {
            $this->helptxt = mailboxlist('folder', $val['folder']);
        } else {
            $this->helptxt = mailboxlist('folder', false);
        }
        */
            
        return sprintf( _("Or specify a new folder: %s to be created under %s"), 
                ' <input type="text" size="15" name="newfoldername" onclick="checkOther(\'5\');" /> ',
                mailboxlist('newfolderparent', false, true));
    }
}

/**
 * Vacation Action
 */
class avelsieve_action_vacation extends avelsieve_action {
	var $num = 6;
	var $capability = 'vacation';
	
	var $options = array(
		'vac_addresses' => '',
		'vac_days' => '7',
		'vac_subject' => '',
		'vac_message' => ''
	);
    var $image_src = 'images/icons/status_away.png';

	function avelsieve_action_vacation(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Vacation");
		$this->options['vac_addresses'] = get_user_addresses();

		if($this->translate_return_msgs==true) {
			$this->options['vac_message'] = _("This is an automated reply; I am away and will not be able to reply to you immediately.").
			_("I will get back to you as soon as I return.");
		} else {
			$this->options['vac_message'] = "This is an automated reply; I am away and will not be able to reply to you immediately.".
			"I will get back to you as soon as I return.";
		}
		
		$this->helptxt = _("The notice will be sent only once to each person that sends you mail, and will not be sent to a mailing list address.");

		$this->avelsieve_action($s, $rule);
	}


	function options_html($val) {
        /* Provide sane default for maxlength */
        $maxlength = 200;
        if(isset($val['vac_addresses']) && strlen($val['vac_addresses']) > 200) {
            $maxlength = (string) (strlen($val['vac_addresses']) + 50);
        }
        
        $out = '<table border="0" width="70%" cellpadding="3">'.
            '<tr><td align="right" valign="top">'.
            _("Subject:") .
            '</td><td align="left">'.
            '<input type="text" name="vac_subject" value="'.htmlspecialchars($val['vac_subject']).'" size="60" maxlength="300" />'.
            '<br/><small>'._("Optional subject of the vacation message.") .'</small>'.
            '</td></tr>'.

            '<tr><td align="right" valign="top">'.
            _("Your Addresses:").
            '</td><td align="left">'.
            ' <input type="text" name="vac_addresses" value="'.htmlspecialchars($val['vac_addresses']).'" size="60" maxlength="'.$maxlength.'" />'.
            '<br/><small>'._("A vacation message will be sent only if an email is sent explicitly to one of these addresses.") .'</small>'.
            '</td></tr>'.

            '<tr><td align="right" valign="top">'.
            _("Days:").
            '</td><td align="left">'.
            ' <input type="text" name="vac_days" value="'.htmlspecialchars($val['vac_days']).'" size="3" maxlength="4" /> ' . _("days").
            '<br/><small>'._("A vacation message will not be resent to the same address, within this number of days.") .'</small>'.
            '</td></tr>'.
            
            '<tr><td align="right" valign="top">'.
            _("Message:") . 
            '</td><td align="left">'.
            '<textarea name="vac_message" rows="4" cols="60">'.$val['vac_message'].'</textarea>'.
            '</td></tr>'.
        
            '</table>';

        return $out;
	}

	function validate($val, &$errormsg) {
		if(!is_numeric($val['vac_days']) || !($val['vac_days'] > 0)) {
			$errormsg[] = _("The number of days between vacation messages must be a positive number.");
		}
		if(!empty($val['vac_addresses'])) {
		    $onemailregex = "[a-zA-Z0-9]+[a-zA-Z0-9\._-]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+";
    		if(!preg_match("/^$onemailregex(,$onemailregex)*$/" ,	$val['vac_addresses'])){
	    		$errormsg[] = _("Incorrect email address(es). You must enter one or more valid email addresses, separated by comma.");
            }
		}
	}
}


/**
 * STOP Action
 */
class avelsieve_action_stop extends avelsieve_action {
	var $num = 0;
	var $name = 'stop';
	var $text = '';
	var $image_src = 'images/icons/stop.png';

	function avelsieve_action_stop(&$s, $rule = array()) {
        $this->init();
		$this->helptxt = _("If this rule matches, do not check any rules after it.");
		$this->text = _("STOP");
		$this->avelsieve_action($s, $rule);
	}
}

/**
 * Notify Action
 */
class avelsieve_action_notify extends avelsieve_action {
    var $num = 0;
    var $name = 'notify';
    var $options = array(
        'notify' => array(
            'on' => '',
            'method' => '',
            'id' => '',
            'options' => ''
        )
    );
    var $capability = 'notify';
    var $image_src = 'images/icons/email.png';
    var $two_dimensional_options = true;

    /**
     * The notification action is a bit more complex than the others. The
     * oldcyrus variable is for supporting the partially implemented notify
     * extension implementation of Cyrus < 2.3.
     *
     * @see https://bugzilla.andrew.cmu.edu/show_bug.cgi?id=2135
     */
    function avelsieve_action_notify(&$s, $rule = array()) {
        $this->init();
        global $notifymethods, $avelsieve_oldcyrus;
        if(isset($notifymethods)) {
            $this->notifymethods = $notifymethods;
        } else {
            $this->notifymethods = false;
        }
        
        $this->text = _("Notify");
        $this->helptxt = _("Send a notification ");
        $this->notifystrings = array(
            'sms' => _("Mobile Phone Message (SMS)") ,
            'mailto' => _("Email notification") ,
            'zephyr' => _("Notification via Zephyr") ,
            'icq' => _("Notification via ICQ")
        );
        
        $this->oldcyrus = $avelsieve_oldcyrus;
        $this->avelsieve_action($s, $rule);
    }

    /**
     * Notify Options
     * @param array $val
     * @return string
     */
    function options_html($val) {
        global $prioritystrings;
        $out = '<blockquote>
            <table border="0" width="70%">';

        $out .= '<tr><td align="right" valign="top">'.
            _("Method") . ': </td><td align="left">';

        if(is_array($this->notifymethods) && sizeof($this->notifymethods) == 1) {
                /* No need to provide listbox, there's only one choice */
                $out .= '<input type="hidden" name="notify[method]" value="'.htmlspecialchars($this->notifymethods[0]).'" />';
                if(array_key_exists($this->notifymethods[0], $this->notifystrings)) {
                    $out .= $this->notifystrings[$this->notifymethods[0]];
                } else {
                    $out .= $this->notifymethods[0];
                }
    
        } elseif(is_array($this->notifymethods)) {
                /* Listbox */
                $out .= '<select name="notify[method]">';
                foreach($this->notifymethods as $no=>$met) {
                    $out .= '<option value="'.htmlspecialchars($met).'"';
                    if(isset($val['notify']['method']) &&
                      $val['notify']['method'] == $met) {
                        $out .= ' selected=""';
                    }
                    $out .= '>';
        
                    if(array_key_exists($met, $this->notifystrings)) {
                        $out .= $this->notifystrings[$met];
                    } else {
                        $out .= $met;
                    }
                    $out .= '</option>';
                }
                $out .= '</select>';
                
        } elseif($this->notifymethods == false) {
                $out .= '<input name="notify[method]" value="'.htmlspecialchars($val['notify']['method']). '" size="20" />';
        }
    
        $out .= '</td></tr>';
        
            /* TODO Not really used, reconsider / remove it. */
            $dummy =  _("Notification ID"); // for gettext
            /*
            $out .= _("Notification ID") . ": ";
            $out .= '<input name="notify[id]" value="';
            if(isset($edit)) {
                if(isset($_SESSION['rules'][$edit]['notify']['id'])) {
                    $out .= htmlspecialchars($_SESSION['rules'][$edit]['notify']['id']);
                }
            }
            $out .= '" /><br />';
            */
        
        $out .= '<tr><td align="right">'.
            _("Destination") . ": ".
            '</td><td align="left" valign="top">'.
            '<input name="notify[options]" size="30" value="' . 
            ( isset($val['notify']['options']) ? htmlspecialchars($val['notify']['options']) : '') .
            '" /></td></tr>';
        
        $out .= '<tr><td align="right">'.
            _("Priority") . ':'.
            '</td><td align="left" valign="top">'.
            '<select name="notify[priority]">';
            foreach($prioritystrings as $pr=>$te) {
                $out .= '<option value="'.htmlspecialchars($pr).'"';
                if(isset($val['notify']['priority']) && $val['notify']['priority'] == $pr) {
                    $out .= ' checked="CHECKED"';
                }
                $out .= '>';
                $out .= $prioritystrings[$pr];
                $out .= '</option>';
            }
        $out .= '</select></td></tr>';
        
        $out .= '<tr><td align="right">'.
            _("Message") . ": ".
            '</td><td align="left" valign="top">'.
            '<textarea name="notify[message]" rows="4" cols="50">'.
            (isset($val['notify']['message']) ? htmlspecialchars($val['notify']['message']) : '') .
            '</textarea><br />';
            
        $out .= '<small>'. _("Help: Valid variables are:");
        if($this->oldcyrus) {
            /* $text$ is not supported by Cyrus IMAP < 2.3 . */
            $out .= ' $from$, $env-from$, $subject$</small>';
        } else {
            $out .= ' $from$, $env-from$, $subject$, $text$, $text[n]$</small>';
        }
        $out .= '</td></tr></table></blockquote>';
        return $out;
    }
}

/**
 * Keep a copy in INBOX marked as Deleted
 */
class avelsieve_action_keepdeleted extends avelsieve_action {
	var $num = 0;
	var $name = 'keepdeleted';
	var $capability = 'imapflags';
	var $image_src = 'images/icons/email_delete.png';

	function avelsieve_action_keepdeleted(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Also keep copy in INBOX, marked as deleted.");
		$this->avelsieve_action($s, $rule);
	}
}

/**
 * Disable rule
 */
class avelsieve_action_disabled extends avelsieve_action {
	var $num = 0;
	var $name = 'disabled';
	var $image_src = 'images/icons/disconnect.png';

	function avelsieve_action_disabled(&$s, $rule = array()) {
        $this->init();
		$this->text = _("Disable");
		$this->helptxt = _("The rule will have no effect for as long as it is disabled.");
		$this->avelsieve_action($s, $rule);
	}
}


/**
 * SPAM-rule-specific action: Store into junk folder.
 */
class avelsieve_action_junk extends avelsieve_action {
	var $num = 7;
	var $name = 'junk';
	var $image_src = 'images/icons/bin.png';
    
    function avelsieve_action_junk(&$s, $rule = array()) {
        global $junkfolder_days;
        $this->init();
        $this->text = _("Move to Junk");
        $this->helptxt = sprintf( _("Store message in your Junk Folder. Messages older than %s days will be deleted automatically."), $junkfolder_days).
               ' ' . _("Note that you can set the number of days in Folder Preferences.");
		$this->avelsieve_action($s, $rule);
    }
}

/**
 * SPAM-rule-specific action: Store into trash folder.
 */
class avelsieve_action_trash extends avelsieve_action {
	var $num = 8;
	var $name = 'junk';
	var $image_src = 'images/icons/bin.png';
    
    function avelsieve_action_trash(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Move to Trash");
        $this->helptxt = _("Store message in your Trash Folder. You will have to purge the folder yourself.");
		$this->avelsieve_action($s, $rule);
    }
}

