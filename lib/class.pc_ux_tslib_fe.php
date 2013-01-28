<?php
/***************************************************************
* Copyright notice
*
* (c) 2003 Jens Ellerbrock <je@hades.org>
* All rights reserved
*
* This script is part of the Typo3 project. The Typo3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
* A copy is found in the textfile GPL.txt and important notices to the license
* from the author is found in LICENSE.txt distributed with these scripts.
*
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* This class extends class.tslib_fe.php for the pdf_generator2 extension
*
* @author Jens Ellerbrock <je@hades.org>
*/

class pc_ux_tslib_fe extends tslib_fe {

    /**
     * Provides was to bypass the "?id=[xxx]&type=[xx]" format.
     *
     * Two options:
     * 1) Apache mod_rewrite: Here a .htaccess file maps all .html-files to index.php and then we extract the id and type from the name of that HTML-file.
     * 2) Use PATH_INFO (also Apache) to extract id and type from that var. Does not require any special modules compiled with apache.
     *
     * @see TSref.pdf
     */
    function checkAlternativeIdMethods()    {
        // call original Function
        parent::checkAlternativeIdMethods();
        
        $_params = array('pObj' => &$this);
        require_once(t3lib_div::getFileAbsFileName('EXT:pdf_creator/class.tx_pdfcreator.php'));
        t3lib_div::callUserFunction("tx_pdfcreator->tslib_fe_checkAlternativeIdMethods",$_params,$this);
    }

  // this function adds a Content-Length: header

    function processOutput()    {
         if ($GLOBALS['TSFE']->type != $GLOBALS['pdf_creator_parameters']['typeNum']) {
            parent::processOutput();
        } else {    
            $_params = array('pObj' => &$this);
            require_once(t3lib_div::getFileAbsFileName('EXT:pdf_creator/class.tx_pdfcreator.php'));
            t3lib_div::callUserFunction("tx_pdfcreator->tslib_fe_processOutput",$_params,$this);
        }
    }
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pdf_creator/class.pc_ux_tslib_fe.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pdf_creator/class.pc_ux_tslib_fe.php']);
}
?>