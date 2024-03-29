<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 arieedzig <arieedzig@yahoo.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
$PATH_CLASSES = PATH_typo3conf . "ext/pdf_creator/";
require_once($PATH_CLASSES . 'class.tx_pdfcreator.php');

/**
 * Plugin 'PDF Creator link' for the 'pdf_creator' extension.
 *
 * @author    arieedzig <arieedzig@yahoo.com>
 * @package    TYPO3
 * @subpackage    tx_pdfcreator
 */
class tx_pdfcreator_pi1 extends tslib_pibase {
    var $prefixId      = 'tx_pdfcreator_pi1';        // Same as class name
    var $scriptRelPath = 'pi1/class.tx_pdfcreator_pi1.php';    // Path to this script relative to the extension dir.
    var $extKey        = 'pdf_creator';    // The extension key.
    var $pi_checkCHash = true;
    var $conf;
    
    /**
     * The main method of the PlugIn
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    The content that is displayed on the website
     */
    function main($content, $conf) {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        
        //require_once(t3lib_div::getFileAbsFileName('EXT:pdf_creator/class.tx_pdfcreator.php'));
        $conf['target'] = '_blank';
        $conf['no_user_int'] = 0;
        
        $content = t3lib_div::callUserFunction("tx_pdfcreator->makePdfLink",$conf,$conf);
        return $this->pi_wrapInBaseClass($content);
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pdf_creator/pi1/class.tx_pdfcreator_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pdf_creator/pi1/class.tx_pdfcreator_pi1.php']);
}

?>