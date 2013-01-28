<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// add some typoscript constants
$pdf_creator_parameters = unserialize($_EXTCONF);
t3lib_extMgm::addTypoScriptConstants('extension.pdf_creator.typeNum = '.$pdf_creator_parameters['typeNum']);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_pdfcreator_pi1.php', '_pi1', 'list_type', 1);

if (t3lib_div::int_from_ver($GLOBALS["TYPO_VERSION"])>= 3007000) {
    // add some hooks
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] =
    'EXT:pdf_creator/class.tx_pdfcreator.php:&tx_pdfcreator->tslib_fe_checkAlternativeIdMethods';
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 
    'EXT:pdf_creator/class.tx_pdfcreator.php:&tx_pdfcreator->tslib_fe_processOutput';
    
} else {
  // override class.tslib_fe
    $TYPO3_CONF_VARS["FE"]["XCLASS"]["tslib/class.tslib_fe.php"] =
    t3lib_extMgm::extPath($_EXTKEY,"lib/class.pc_ux_tslib_fe.php");
}
    
?>
