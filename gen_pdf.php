<?php
    ob_start();

    // include classes for pdf generation
    define('HTML2PS_DIR',t3lib_extMgm::extPath('pdf_creator','html2ps/'));

    // allow file's from everywhere
    define('FILE_PROTOCOL_RESTRICT', '');

    require_once(t3lib_extMgm::extPath('pdf_creator','lib/').'class.pc_wkhtmltopdf.php');
//    require_once(HTML2PS_DIR.'pipeline.factory.class.php');
    /*
error_reporting(E_ALL);
ini_set("display_errors","1");
@set_time_limit(10000);
    */

  // -------------------------------------------------------------------

  // turn off admin panel
  $GLOBALS{TSFE}->config['config']['admPanel']=0;
  // generate original content
  require_once(PATH_tslib.'class.tslib_pagegen.php');
  include(PATH_tslib.'pagegen.php');

  // instead of calling processOutput...
  //---------------------------- begin ProcessOutput --------------
  // substitute fe user
  $token = trim($GLOBALS{TSFE}->config['config']['USERNAME_substToken']);
  $token = $token ? $token : '<!--###USERNAME###-->';
  if (strpos($GLOBALS{TSFE}->content, $token)) {
    $GLOBALS{TSFE}->set_no_cache();
    if ($GLOBALS{TSFE}->fe_user->user['uid'])    {
      $GLOBALS{TSFE}->content = str_replace($token,$GLOBALS{TSFE}->fe_user->user['uid'],$GLOBALS{TSFE}->content);
    }
  }
  // Substitutes get_URL_ID in case of GET-fallback
  if ($GLOBALS{TSFE}->getMethodUrlIdToken)    {
    $GLOBALS{TSFE}->content = str_replace($GLOBALS{TSFE}->getMethodUrlIdToken, $GLOBALS{TSFE}->fe_user->get_URL_ID, $GLOBALS{TSFE}->content);
  }

  // Tidy up the code, if flag...
  if ($GLOBALS{TSFE}->TYPO3_CONF_VARS['FE']['tidy_option'] == 'output')        {
    $GLOBALS['TT']->push('Tidy, output','');
    $GLOBALS{TSFE}->content = $GLOBALS{TSFE}->tidyHTML($GLOBALS{TSFE}->content);
    $GLOBALS['TT']->pull();
  }
  // XHTML-clean the code, if flag set
  if ($GLOBALS{TSFE}->doXHTML_cleaning() == 'output')        {
    $GLOBALS['TT']->push('XHTML clean, output','');
    $XHTML_clean = t3lib_div::makeInstance('t3lib_parsehtml');
    $GLOBALS{TSFE}->content = $XHTML_clean->XHTML_clean($GLOBALS{TSFE}->content);
    $GLOBALS['TT']->pull();
  }
  //---------------------------- end ProcessOutput --------------


   // ------------------------ Handle UserInt Objects --------------------------
    // ********************************
    // $GLOBALS['TSFE']->config['INTincScript']
    // *******************************
    if ($TSFE->isINTincScript())        {
    
        $TT->push('Non-cached objects','');
            $INTiS_config = $GLOBALS['TSFE']->config['INTincScript'];
        $GLOBALS{TSFE}->set_no_cache();
                // Special feature: Include libraries
            $TT->push('Include libraries');
            reset($INTiS_config);
            while(list(,$INTiS_cPart)=each($INTiS_config))    {
                if ($INTiS_cPart['conf']['includeLibs'])    {
                    $INTiS_resourceList = t3lib_div::trimExplode(',',$INTiS_cPart['conf']['includeLibs'],1);
                    $GLOBALS['TT']->setTSlogMessage('Files for inclusion: "'.implode(', ',$INTiS_resourceList).'"');
                    reset($INTiS_resourceList);
                    while(list(,$INTiS_theLib)=each($INTiS_resourceList))    {
                        $INTiS_incFile=$GLOBALS['TSFE']->tmpl->getFileName($INTiS_theLib);
                        if ($INTiS_incFile)    {
                            require_once('./'.$INTiS_incFile);
                        } else {
                            $GLOBALS['TT']->setTSlogMessage('Include file "'.$INTiS_theLib.'" did not exist!',2);
                        }
                    }
                }
            }
            $TT->pull();
            $TSFE->INTincScript();
        $TT->pull();
    }
      
  //---------------------------- end Handle UserInt Objects --------------

  //---------------------------- parse html2df parameters --------------

    $html2pdf_browserwidth=$GLOBALS{TSFE}->config['config']['pdf_creator.']['browserwidth'];
    $html2pdf_left=$GLOBALS{TSFE}->config['config']['pdf_creator.']['left'];
    $html2pdf_right=$GLOBALS{TSFE}->config['config']['pdf_creator.']['right'];
    $html2pdf_top=$GLOBALS{TSFE}->config['config']['pdf_creator.']['top'];
    $html2pdf_bottom=$GLOBALS{TSFE}->config['config']['pdf_creator.']['bottom'];
    $html2pdf_size=$GLOBALS{TSFE}->config['config']['pdf_creator.']['size'];
    $html2pdf_landscape=$GLOBALS{TSFE}->config['config']['pdf_creator.']['landscape'];
    $html2pdf_renderlinks=$GLOBALS{TSFE}->config['config']['pdf_creator.']['renderlinks'];
    $html2pdf_renderfields=$GLOBALS{TSFE}->config['config']['pdf_creator.']['renderfields'];
    $html2pdf_renderforms=$GLOBALS{TSFE}->config['config']['pdf_creator.']['renderforms'];
    $html2pdf_pdfversion=preg_replace("#[^\.\d]#", "", $GLOBALS{TSFE}->config['config']['pdf_creator.']['pdfversion']);
    $html2pdf_cssmedia=$GLOBALS{TSFE}->config['config']['pdf_creator.']['cssmedia'];
    $html2pdf_use_pdflib=$GLOBALS{TSFE}->config['config']['pdf_creator.']['use_pdflib'];
    $html2pdf_js=$GLOBALS{TSFE}->config['config']['pdf_creator.']['js'];
    
  //---------------------------- apply replaces --------------

    $i=0;
    while(++$i) {
      if ($GLOBALS{TSFE}->config['config']['pdf_creator.']['string_search'.$i]) {
        $GLOBALS{TSFE}->content = str_replace($GLOBALS{TSFE}->config['config']['pdf_creator.']['string_search'.$i],
          $GLOBALS{TSFE}->config['config']['pdf_creator.']['string_replace'.$i],$GLOBALS{TSFE}->content);
      } elseif ($i>4) {
          break;
      }
    };
    $i=0;
    while(++$i) {
      if ($GLOBALS{TSFE}->config['config']['pdf_creator.']['regexp_search'.$i]) {
        $GLOBALS{TSFE}->content = preg_replace($GLOBALS{TSFE}->config['config']['pdf_creator.']['regexp_search'.$i],
          $GLOBALS{TSFE}->config['config']['pdf_creator.']['regexp_replace'.$i],$GLOBALS{TSFE}->content);
      } elseif ($i>4) {
          break;
      }
    }

//---------------------------- make links absolute --------------


function fix_links_callback($matches) 
{
    return $matches[1].t3lib_div::locationHeaderUrl($matches[2]).$matches[3];    
}

$GLOBALS{TSFE}->content = preg_replace_callback('/(<a [^>]*href=\")(?!#)(.*?)(\")/',
                                       'fix_links_callback',
                                       $GLOBALS{TSFE}->content );

$GLOBALS{TSFE}->content = preg_replace_callback('/(<form [^>]*action=\")(?!#)(.*?)(\")/',
                                       'fix_links_callback',
                                       $GLOBALS{TSFE}->content );
// write the html for debugging puposes
       
    $xhtml =  $GLOBALS{TSFE}->content;
    if (extension_loaded('tidy')) {
        $tidy_config = array(
            'output-xhtml' => true,
            'add-xml-decl' => false,
            'indent' => false,
            'tidy-mark' => false,
            //'input-encoding' => "latin1",
            'output-encoding' => "utf8",
            'doctype' => "auto",
            'wrap' => 0,
            'char-encoding' => "utf8",
        );
        $tidy = new tidy;
        $tidy->parseString($xhtml, $tidy_config, 'utf8');
        $tidy->cleanRepair();
        $xhtml = "$tidy";
    }

    $xhtml = str_replace("&nbsp;","&#160;",$xhtml);
    $GLOBALS{TSFE}->content = $xhtml;
    
    if (1) {
        $fd=fopen('typo3temp/html2ps.html', 'wb');
        fwrite($fd,$GLOBALS{TSFE}->content);
        fclose($fd);
    }
    //------------------------------------------

    $wkh_params = array(
        'path' => PATH_site.'typo3temp/',
        'page_size' => $html2pdf_size,
        'margins' => 
            array ( 'left'   => $html2pdf_left,
                    'right'  => $html2pdf_right,
                    'top'    => $html2pdf_top,
                    'bottom' => $html2pdf_bottom
            )
    );
    
    require_once(t3lib_div::getFileAbsFileName('EXT:pdf_creator/class.tx_pdfcreator.php'));
    $conf['target'] = '_blank';
    $conf['no_user_int'] = 0;
    $urll = urlencode(strip_tags(t3lib_div::callUserFunction("tx_pdfcreator->linkInPdf",$conf,$conf)));
    #$urll = urlencode(t3lib_div::getIndpEnv(TYPO3_REQUEST_URL));
    setlocale(LC_TIME, 'de_DE.utf8');
    $ndate = strftime("%d %b %Y");
      
    $footer = "file://". t3lib_extMgm::extPath('pdf_creator','res/footer.html?url='.$urll.'&ndate='.$ndate);
    $header = t3lib_extMgm::extPath('pdf_creator','res/header.html');

    $wkhtmltopdf = new pc_wkhtmltopdf($wkh_params);
    #$wkhtmltopdf->setTitle("Title");
    $wkhtmltopdf->setHeaderHtml($header);
    $wkhtmltopdf->setFooterHtml($footer);
    $wkhtmltopdf->setJS($html2pdf_js);
    #$wkhtmltopdf->setRunInVirtualX(true);
    $wkhtmltopdf->setHtml($GLOBALS{TSFE}->content);
    $test = $wkhtmltopdf->output(pc_wkhtmltopdf::MODE_STRING, "file.pdf");

    /*$hhost = t3lib_div::getIndpEnv(HTTP_HOST);
    $hhost = "www.ecomarathon.de";
    $t2url = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$hhost]['preVars'];
    if(!is_array($t2url)) {
        $t3 = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$hhost];
        $t2url = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$t3]['preVars'];
    }
        $pdf_params = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pdf_creator']);
        foreach ($t2url as $r){
            if($r['GETvar'] == "type"){
                foreach($r['valueMap'] as $vkey => $vval){
                    if ($vval == $pdf_params['typeNum']) $valdel = $vkey;
                }
            }
        }
    #$turl = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$hhost];
    throw new Exception("WKHTMLTOPDF ". print_r($valdel,1));
    */
    #$content='<html><title>html2ps problem</title>';
    #$content.= "==".print_r($GLOBALS{TSFE}->content,1)."==\n";
    #$content.= "==".print_r($GLOBALS{TSFE}->cObj->data,1)."==\n";
    #$GLOBALS{TSFE}->content = $content . $GLOBALS{TSFE}->content;
    $content = "";
    $GLOBALS{TSFE}->content = $content.$test;
    ob_end_clean();
    
    if (substr($GLOBALS{TSFE}->content,0,4) != '%PDF') {
        // don't cache errors
      $GLOBALS{TSFE}->set_no_cache();
      $GLOBALS{TSFE}->content = '<html><title>wkhtmltopdf problem</title><body><h1>WKHTMLTOPDF Problem:</h1>';
      if ($errors) {
          $GLOBALS{TSFE}->content.='wkhtmltopdf produced the following errors:';
          $GLOBALS{TSFE}->content.='<table borders=1 bgcolor="#e0e0e0"><tr><td>'.$test.'</td></tr></table>';
      } else {
          $GLOBALS{TSFE}->content.= 'wkhtmltopdf produced no pdf-output.<br>';
      }
      $GLOBALS{TSFE}->content.='</body></html>';
    }
          
?>
