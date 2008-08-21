<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}
wfLoadExtensionMessages( 'RatingHistory' );
wfLoadExtensionMessages( 'FlaggedRevs' );

class RatingHistory extends UnlistedSpecialPage
{
    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'RatingHistory', 'feedback' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;
		$this->setHeaders();
		if( $wgUser->isAllowed( 'feedback' ) ) {
			if( $wgUser->isBlocked() ) {
				$wgOut->blockedPage();
				return;
			}
		} else {
			$wgOut->permissionRequired( 'feedback' );
			return;
		}
		if( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->target = $wgRequest->getText( 'target' );
		$this->page = Title::newFromUrl( $this->target );
		# We need a page...
		if( is_null($this->page) ) {
			$wgOut->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}
		if( !FlaggedRevs::isPageRateable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('readerfeedback-main',array('parse')) );
			return;
		}
		$period = $wgRequest->getInt( 'period' );
		$validPeriods = array(31,365,1095);
		if( !in_array($period,$validPeriods) ) {
			$period = 31; // default
		}
		$this->period = $period;
		
		$this->showHeader();
		$this->showForm();
		$this->showGraphs();
	}
	
	protected function showHeader() {
		global $wgOut;
		if( FlaggedRevs::userAlreadyVoted( $this->page ) ) {
			$wgOut->addWikiText( wfMsg('ratinghistory-thanks') . '<hr/>' );
		}
		$wgOut->addWikiText( wfMsg('ratinghistory-text',$this->page->getPrefixedText()) );
		$wgOut->addWikiText( wfMsg('ratinghistory-legend') );
	}
	
	protected function showForm() {
		global $wgOut, $wgTitle, $wgScript;
		$form = Xml::openElement( 'form', array( 'name' => 'reviewedpages', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= "<fieldset><legend>".wfMsg('ratinghistory-leg')."</legend>\n";
		$form .= Xml::hidden( 'title', $wgTitle->getPrefixedDBKey() );
		$form .= Xml::hidden( 'target', $this->page->getPrefixedDBKey() );
		$form .= $this->getPeriodMenu( $this->period );
		$form .= " ".Xml::submitButton( wfMsg( 'go' ) );
		$form .= "</fieldset></form>\n";
		$wgOut->addHTML( $form );
	}
	
   	/**
	* Get a selector of time period options
	* @param int $selected, selected level
	*/
	protected function getPeriodMenu( $selected=null ) {
		$s = "<label for='period'>" . wfMsgHtml('ratinghistory-period') . "</label>&nbsp;";
		$s .= Xml::openElement( 'select', array('name' => 'period', 'id' => 'period') );
		$s .= Xml::option( wfMsg( "ratinghistory-month" ), 31, $selected===31 );
		$s .= Xml::option( wfMsg( "ratinghistory-year" ), 365, $selected===365 );
		$s .= Xml::option( wfMsg( "ratinghistory-3years" ), 1095, $selected===1095 );
		$s .= Xml::closeElement('select')."\n";
		return $s;
	}
	
	protected function showGraphs() {
		global $wgOut;
		$data = false;
		// Do each graphs for said time period
		foreach( FlaggedRevs::getFeedbackTags() as $tag => $weight ) {
			// Check if cached version is available.
			// If not, then generate a new one.
			$filePath = $this->getFilePath( $tag );
			$url = $this->getUrlPath( $tag );
			$ext = self::getCachedFileExtension();
			if( $ext === 'svg' ) {
				if( !$this->fileExpired($tag,$filePath) || $this->makeSvgGraph( $tag, $filePath ) ) {
					$data = true;
					$wgOut->addHTML( '<h2>' . wfMsgHtml("readerfeedback-$tag") . '</h2>' );
					$wgOut->addHTML( 
						Xml::openElement( 'div', array('class' => 'reader_feedback_graph',
							'style' => "width:100%; overflow:scroll;") ) .
						Xml::openElement( 'object', array('data' => $url, 'type' => 'image/svg+xml') ) . 
						Xml::closeElement( 'object' ) .
						Xml::closeElement( 'div' ) . "\n"
					);
				}
			} else if( $ext === 'png' ) {
				if( !$this->fileExpired($tag,$filePath) || $this->makePngGraph( $tag, $filePath ) ) {
					$data = true;
					$wgOut->addHTML( '<h2>' . wfMsgHtml("readerfeedback-$tag") . '</h2>' );
					$wgOut->addHTML( 
						Xml::openElement( 'div', array('class' => 'reader_feedback_graph',
							'style' => "width:100%; overflow:scroll;") ) .
						Xml::openElement( 'img', array('src' => $url,'alt' => $tag) ) . 
						Xml::closeElement( 'img' ) .
						Xml::closeElement( 'div' ) . "\n"
					);
				}
			} else {
				if( !$this->fileExpired($tag,$filePath) ) {
					$data = true;
					$fp = @fopen( $filePath, 'r' );
					$table = fread( $fp, filesize($filePath) );
					$wgOut->addHTML( '<h2>' . wfMsgHtml("readerfeedback-$tag") . '</h2>' );
					$wgOut->addHTML( $table . "\n" );
				} else if( $table = $this->makeHTMLTable( $tag, $filePath ) ) {
					$data = true;
					$wgOut->addHTML( '<h2>' . wfMsgHtml("readerfeedback-$tag") . '</h2>' );
					$wgOut->addHTML( $table . "\n" );
				}
			} 
		}
		if( !$data ) {
			$wgOut->addHTML( wfMsg('ratinghistory-none') );
		}
	}
	
	/**
	* Generate an HTML table for this tag
	* @param string $tag
	* @param string $filePath
	* @returns string, html table
	*/
	public function makeHTMLTable( $tag, $filePath ) {
		$dir = dirname($filePath);
		// Make sure directory exists
		if( !is_dir($dir) && !wfMkdirParents( $dir, 0777 ) ) {
			return false;
		}
		// Set cutoff time for period
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_unixtime = time() - ($this->period * 24 * 3600);
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->addQuotes( wfTimestamp( TS_MW, $cutoff_unixtime ) );
		// Define the data using the DB rows
		$totalVal = $totalCount = $n = 0;
		$lastDay = 31; // init to not trigger first time
		$lastMonth = 12; // init to not trigger first time
		$lastYear = 9999; // init to not trigger first time
		$res = $dbr->select( 'reader_feedback_history',
			array( 'rfh_total', 'rfh_count', 'rfh_date' ),
			array( 'rfh_page_id' => $this->page->getArticleId(), 
				'rfh_tag' => $tag,
				"rfh_date >= {$cutoff}"),
			__METHOD__,
			array( 'ORDER BY' => 'rfh_date ASC' ) );
		// Label spacing
		if( $row = $dbr->fetchObject( $res ) ) {
			$lower = wfTimestamp( TS_UNIX, $row->rfh_date );
			$res->seek( $dbr->numRows($res)-1 );
			$upper = wfTimestamp( TS_UNIX, $dbr->fetchObject( $res )->rfh_date );
			$days = intval( ($upper - $lower)/86400 );
			$int = intval( ceil($days/10) ); // 10 labels at most
			$res->seek( 0 );
		}
		$dates = $drating = $arating = "";
		$n = 0;
		while( $row = $dbr->fetchObject( $res ) ) {
			$totalVal += (int)$row->rfh_total;
			$totalCount += (int)$row->rfh_count;
			$dayAve = sprintf( '%4.2f', (real)$row->rfh_total/(real)$row->rfh_count );
			$cumAve = sprintf( '%4.2f', (real)$totalVal/(real)$totalCount );
			$year = intval( substr( $row->rfh_date, 0, 4 ) );
			$month = intval( substr( $row->rfh_date, 4, 2 ) );
			$day = intval( substr( $row->rfh_date, 6, 2 ) );
			$date = ($this->period > 31) ? "{$month}/{$day}/".substr( $year, 2, 2 ) : "{$month}/{$day}";
			$dates .= "<th>$date</th>";
			$drating .= "<td>$dayAve</td>";
			$arating .= "<td>$cumAve</td>";
			$n++;
		}
		// Minimum sample size
		if( $n < 2 || $totalCount < 10 ) {
			return "";
		}
		$chart = Xml::openElement( 'div', array('style' => "width:100%; overflow:scroll;") );
		$chart .= "<table width='100%' class='wikitable' style='white-space:nowrap' border='1px'>\n";
		$chart .= "<tr>$dates</tr>\n";
		$chart .= "<tr align='center' class='fr-rating-dave'>$drating</tr>\n";
		$chart .= "<tr align='center' class='fr-rating-rave'>$arating</tr>\n";
		$chart .= "</table>\n";
		$chart .= Xml::closeElement( 'div' );
		// Write to file for cache
		$fp = @fopen( $filePath, 'w' );
		@fwrite($fp, $chart );
		return $chart;
	}
	
	/**
	* Generate a graph for this tag
	* @param string $tag
	* @param string $filePath
	* @returns bool, success
	*/
	public function makePngGraph( $tag, $filePath ) {
		if( !function_exists( 'ImageCreate' ) ) {
			// GD is not installed
			return false;
		}
		
		global $wgPHPlotDir;
		require_once( "$wgPHPlotDir/phplot.php" ); // load classes
		// Define the object
		$plot = new PHPlot( 1000, 400 );
		// Set file path
		$dir = dirname($filePath);
		// Make sure directory exists
		if( !is_dir($dir) && !wfMkdirParents( $dir, 0777 ) ) {
			return false;
		}
		$plot->SetOutputFile( $filePath );
		$plot->SetIsInline( true );
		// Set cutoff time for period
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_unixtime = time() - ($this->period * 24 * 3600);
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->addQuotes( wfTimestamp( TS_MW, $cutoff_unixtime ) );
		// Define the data using the DB rows
		$data = array();
		$totalVal = $totalCount = $n = 0;
		$lastDay = 31; // init to not trigger first time
		$lastMonth = 12; // init to not trigger first time
		$lastYear = 9999; // init to not trigger first time
		$res = $dbr->select( 'reader_feedback_history',
			array( 'rfh_total', 'rfh_count', 'rfh_date' ),
			array( 'rfh_page_id' => $this->page->getArticleId(), 
				'rfh_tag' => $tag,
				"rfh_date >= {$cutoff}"),
			__METHOD__,
			array( 'ORDER BY' => 'rfh_date ASC' ) );
		// Label spacing
		if( $row = $dbr->fetchObject( $res ) ) {
			$lower = wfTimestamp( TS_UNIX, $row->rfh_date );
			$res->seek( $dbr->numRows($res)-1 );
			$upper = wfTimestamp( TS_UNIX, $dbr->fetchObject( $res )->rfh_date );
			$days = intval( ($upper - $lower)/86400 );
			$int = intval( ceil($days/10) ); // 10 labels at most
			$res->seek( 0 );
		}
		while( $row = $dbr->fetchObject( $res ) ) {
			$totalVal += (int)$row->rfh_total;
			$totalCount += (int)$row->rfh_count;
			$dayAve = (real)$row->rfh_total/(real)$row->rfh_count;
			$cumAve = (real)$totalVal/(real)$totalCount;
			$year = intval( substr( $row->rfh_date, 0, 4 ) );
			$month = intval( substr( $row->rfh_date, 4, 2 ) );
			$day = intval( substr( $row->rfh_date, 6, 2 ) );
			# Fill in days with no votes to keep spacing even
			# Year gaps...
			for( $i=($lastYear + 1); $i < $year; $i++ ) {
				for( $x=1; $x <= 365; $x++ ) {
					$data[] = array("",'','');
					$n++;
				}
			}
			# Month gaps...
			for( $i=($lastMonth + 1); $i < $month; $i++ ) {
				for( $x=1; $x <= 31; $x++ ) {
					$data[] = array("",'','');
					$n++;
				}
			}
			# Day gaps...
			for( $x=($lastDay + 1); $x < $day; $x++ ) {
				$data[] = array("",'','');
				$n++;
			}
			# Label point?
			if( $n >= $int || !count($data) ) {
				$p = ($this->period > 31) ? "{$month}/{$day}/".substr( $year, 2, 2 ) : "{$month}/{$day}";
				$n = 0;
			} else {
				$p = "";
				$n++;
			}
			$data[] = array( $p, $dayAve, $cumAve);
			$lastDay = $day;
			$lastMonth = $month;
			$lastYear = $year;
		}
		// Minimum sample size
		if( count($data) < 2 || $totalCount < 10 ) {
			return false;
		}
		$plot->SetDataValues($data);
		$plot->SetPointShapes('dot');
		$plot->setPointSizes( 1 );
		$plot->SetBackgroundColor('#fffff0');
		// Turn off X axis ticks and labels because they get in the way:
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		// Set plot area
		$plot->SetYTickIncrement( .5 );
		$plot->SetPlotAreaWorld( 0, 0, null, 4 );
		// Show total number of votes
		$plot->SetLegend( array("#{$totalCount}") );
		// Draw it!
		$plot->DrawGraph();
		return true;
	}
	
	/**
	* Generate a graph for this tag
	* @param string $tag
	* @param string $filePath
	* @returns bool, success
	*/
	public function makeSvgGraph( $tag, $filePath ) {
		global $wgSvgGraphDir;
		require_once( "$wgSvgGraphDir/svggraph.php" ); // load classes
		require_once( "$wgSvgGraphDir/svggraph2.php" ); // load classes
		// Define the object
		$plot = new svgGraph2(); // some CONST double redefine notices
		// Set file path
		$dir = dirname($filePath);
		// Make sure directory exists
		if( !is_dir($dir) && !wfMkdirParents( $dir, 0777 ) ) {
			return false;
		}
		// Set some parameters
		$plot->graphicWidth = 1000;
		$plot->graphicHeight = 450;
		$plot->plotWidth = 950;
		$plot->plotHeight = 400;
		$plot->decimalPlacesY = 2;
		$plot->plotOffsetX = 40;
		$plot->plotOffsetY = 10;
		$plot->numGridlinesY = 5;
		$plot->innerPaddingX = 5;
		$plot->innerPaddingY = 0;
		$plot->outerPadding = 0;
		$plot->offsetGridlinesX  = 0;
		$plot->maxY = 4;
		// Set cutoff time for period
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_unixtime = time() - ($this->period * 24 * 3600);
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->addQuotes( wfTimestamp( TS_MW, $cutoff_unixtime ) );
		// Define the data using the DB rows
		$dataX = $dave = $rave = array();
		$totalVal = $totalCount = $n = 0;
		$lastDay = 31; // init to not trigger first time
		$lastMonth = 12; // init to not trigger first time
		$lastYear = 9999; // init to not trigger first time
		$res = $dbr->select( 'reader_feedback_history',
			array( 'rfh_total', 'rfh_count', 'rfh_date' ),
			array( 'rfh_page_id' => $this->page->getArticleId(), 
				'rfh_tag' => $tag,
				"rfh_date >= {$cutoff}"),
			__METHOD__,
			array( 'ORDER BY' => 'rfh_date ASC' ) );
		// Label spacing
		if( $row = $dbr->fetchObject( $res ) ) {
			$lower = wfTimestamp( TS_UNIX, $row->rfh_date );
			$res->seek( $dbr->numRows($res)-1 );
			$upper = wfTimestamp( TS_UNIX, $dbr->fetchObject( $res )->rfh_date );
			$days = intval( ($upper - $lower)/86400 );
			$int = intval( ceil($days/10) ); // 10 labels at most
			$res->seek( 0 );
		}
		while( $row = $dbr->fetchObject( $res ) ) {
			$totalVal += (int)$row->rfh_total;
			$totalCount += (int)$row->rfh_count;
			$dayAve = (real)$row->rfh_total/(real)$row->rfh_count;
			$cumAve = (real)$totalVal/(real)$totalCount;
			$year = intval( substr( $row->rfh_date, 0, 4 ) );
			$month = intval( substr( $row->rfh_date, 4, 2 ) );
			$day = intval( substr( $row->rfh_date, 6, 2 ) );
			# Fill in days with no votes to keep spacing even
			# Year gaps...
			for( $i=($lastYear + 1); $i < $year; $i++ ) {
				for( $x=1; $x <= 365; $x++ ) {
					$dataX[] = "";
					$dave[] = $lastDAve;
					$rave[] = $lastRAve;
					$n++;
				}
			}
			# Month gaps...
			for( $i=($lastMonth + 1); $i < $month; $i++ ) {
				for( $x=1; $x <= 31; $x++ ) {
					$dataX[] = "";
					$dave[] = $lastDAve;
					$rave[] = $lastRAve;
					$n++;
				}
			}
			# Day gaps...
			for( $x=($lastDay + 1); $x < $day; $x++ ) {
				$dataX[] = "";
				$dave[] = $lastDAve;
				$rave[] = $lastRAve;
				$n++;
			}
			# Label point?
			if( $n >= $int || !count($dataX) ) {
				$p = ($this->period > 31) ? "{$month}/{$day}/".substr( $year, 2, 2 ) : "{$month}/{$day}";
				$n = 0;
			} else {
				$p = "";
				$n++;
			}
			$dataX[] = $p;
			$dave[] = $dayAve;
			$rave[] = $cumAve;
			$lastDay = $day;
			$lastMonth = $month;
			$lastYear = $year;
			$lastDAve = $dayAve;
			$lastRAve = $cumAve;
		}
		// Minimum sample size
		if( count($dataX) < 2 || $totalCount < 10 ) {
			return false;
		}
		$plot->dataX = $dataX;
		$plot->dataY['dave'] = $dave;
		$plot->dataY['rave'] = $rave;
		$plot->styleTagsX = 'font-family: monospace; font-size: 8pt;';
		$plot->format['dave'] = array( 'style' => 'stroke:blue; stroke-width:1;');
		$plot->format['rave'] = array( 'style' => 'stroke:green; stroke-width:1;');
		# Create the graph
		$plot->init();
		$plot->drawGraph();
		$plot->line('dave');
		$plot->line('rave');
		$plot->generateSVG();
		// Write to file for cache
		$fp = @fopen( $filePath, 'w' );
		@fwrite($fp, $plot->svg );
		return true;
	}
	
	/**
	* Get the path to where the corresponding graph file should be
	* @param string $tag
	* @returns string
	*/
	public function getFilePath( $tag ) {
		global $wgUploadDirectory;
		$rel = self::getRelPath( $tag );
		return "{$wgUploadDirectory}/graphs/{$rel}";
	}
	
	/**
	* Get the url to where the corresponding graph file should be
	* @param string $tag
	* @returns string
	*/
	public function getUrlPath( $tag ) {
		global $wgUploadPath;
		$rel = self::getRelPath( $tag );
		return "{$wgUploadPath}/graphs/{$rel}";
	}
	
	public function getRelPath( $tag ) {
		$ext = self::getCachedFileExtension();
		$pageId = $this->page->getArticleId();
		# Paranoid check. Should not be necessary, but here to be safe...
		if( !preg_match('/^[a-zA-Z]{1,20}$/',$tag) ) {
			throw new MWException( 'Invalid tag name!' );
		}
		return "{$pageId}/{$tag}/l{$this->period}d.{$ext}";
	}
	
	public static function getCachedFileExtension() {
		global $wgSvgGraphDir, $wgPHPlotDir;
		if( $wgSvgGraphDir ) {
			$ext = 'svg';
		} else if( $wgPHPlotDir ) {
			$ext = 'png';
		} else {
			$ext = 'html';
		}
		return $ext;
	}
	
	/**
	* Check if a graph file is expired.
	* @param string $tag
	* @param string $path, filepath to existing file
	* @returns string
	*/
	public function fileExpired( $tag, $path ) {
		if( !file_exists($path) ) {
			return true;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$tagTimestamp = $dbr->selectField( 'reader_feedback_pages', 
			'rfp_touched',
			array( 'rfp_page_id' => $this->page->getArticleId(), 'rfp_tag' => $tag ),
			__METHOD__ );
		$tagTimestamp = wfTimestamp( TS_MW, $tagTimestamp );
		$fileTimestamp = wfTimestamp( TS_MW, filemtime($path) );
		return ($fileTimestamp < $tagTimestamp );
	}
}
