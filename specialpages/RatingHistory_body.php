<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class RatingHistory extends UnlistedSpecialPage
{
    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'RatingHistory', 'feedback' );
		wfLoadExtensionMessages( 'RatingHistory' );
		wfLoadExtensionMessages( 'FlaggedRevs' );
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
		$this->doPurge = $wgUser->isAllowed( 'purge' ) && 'purge' === $wgRequest->getVal( 'action' );
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
		$this->dScale = 10;
		# Thank voters
		if( ReaderFeedback::userAlreadyVoted( $this->page ) ) {
			$wgOut->setSubtitle( wfMsgExt('ratinghistory-thanks','parse') );
		}
		$this->showLead();
		$this->showForm();
		$this->showHeader();
		/*
		 * Allow client caching.
		 */
		if( !$this->doPurge && $wgOut->checkLastModified( $this->getTouched() ) ) {
			return; // Client cache fresh and headers sent, nothing more to do
		} else {
			$wgOut->enableClientCache( false ); // don't show stale graphs
		}
		$this->showGraphs();
	}
	
	protected function showLead() {
		global $wgOut;
		$wgOut->addWikiText( wfMsg('ratinghistory-text',$this->page->getPrefixedText()) );
	}
	
	protected function showHeader() {
		global $wgOut;
		$wgOut->addWikiText( wfMsg('ratinghistory-legend', $this->dScale) );
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
		$wgOut->addHTML( '<h2>' . wfMsgHtml('ratinghistory-chart') . '</h2>' );
		// Do each graphs for said time period
		foreach( FlaggedRevs::getFeedbackTags() as $tag => $weight ) {
			// Check if cached version is available.
			// If not, then generate a new one.
			$filePath = $this->getFilePath( $tag );
			$url = $this->getUrlPath( $tag );
			$ext = self::getCachedFileExtension();
			// Output chart...
			if( $ext === 'svg' ) {
				if( !$this->fileExpired($tag,$filePath) || $this->makeSvgGraph( $tag, $filePath ) ) {
					$data = true;
					$wgOut->addHTML( '<h3>' . wfMsgHtml("readerfeedback-$tag") . '</h3>' );
					$wgOut->addHTML( 
						Xml::openElement( 'div', array('class' => 'fr_reader_feedback_graph') ) .
						Xml::openElement( 'object', array('data' => $url, 'type' => 'image/svg+xml', 
							'width' => '1000px', 'height' => '400px') ) . 
						Xml::closeElement( 'object' ) .
						Xml::closeElement( 'div' ) . "\n"
					);
				}
			} else if( $ext === 'png' ) {
				if( !$this->fileExpired($tag,$filePath) || $this->makePngGraph( $tag, $filePath ) ) {
					$data = true;
					$wgOut->addHTML( '<h3>' . wfMsgHtml("readerfeedback-$tag") . '</h3>' );
					$wgOut->addHTML( 
						Xml::openElement( 'div', array('class' => 'fr_reader_feedback_graph') ) .
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
		// Add voter list
		global $wgMiserMode;
		if( $data && !$wgMiserMode ) {
			$userTable = $this->getUserList($tag);
			if( $userTable ) {
				$wgOut->addHTML( '<h2>' . wfMsgHtml('ratinghistory-users') . '</h2>' );
				$wgOut->addHTML( 
					Xml::openElement( 'div', array('class' => 'fr_reader_feedback_users') ) .
					$userTable .
					Xml::closeElement( 'div' ) . "\n"
				);
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
		$dates = $drating = $arating = $dcount = "";
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
			$dcount .= "<td>#{$row->rfh_total}</td>";
			$n++;
		}
		$chart = Xml::openElement( 'div', array('style' => "width:100%; overflow:auto;") );
		$chart .= "<table class='wikitable' style='white-space: nowrap; border=1px; font-size: 8pt;'>\n";
		$chart .= "<tr>$dates</tr>\n";
		$chart .= "<tr align='center' class='fr-rating-dave'>$drating</tr>\n";
		$chart .= "<tr align='center' class='fr-rating-rave'>$arating</tr>\n";
		$chart .= "<tr align='center' class='fr-rating-dcount'>$dcount</tr>\n";
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
			$int = ($days > 31) ? 31 : intval( ceil($days/12) );
			$res->seek( 0 );
		}
		while( $row = $dbr->fetchObject( $res ) ) {
			$totalVal += (int)$row->rfh_total;
			$totalCount += (int)$row->rfh_count;
			$dayCount = (real)$row->rfh_count;
			$dayAve = (real)$row->rfh_total/(real)$row->rfh_count;
			$cumAve = (real)$totalVal/(real)$totalCount;
			$year = intval( substr( $row->rfh_date, 0, 4 ) );
			$month = intval( substr( $row->rfh_date, 4, 2 ) );
			$day = intval( substr( $row->rfh_date, 6, 2 ) );
			# Fill in days with no votes to keep spacing even
			if( isset($lastDate) ) {
				$dayGap = wfTimestamp(TS_UNIX,$row->rfh_date) - wfTimestamp(TS_UNIX,$lastDate);
				$x = intval( $dayGap/86400 );
				# Day gaps...
				for( $x; $x > 1; --$x ) {
					$data[] = array("",$lastDAve,$lastRAve,0);
					$n++;
				}
			}
			$n++;
			# Label point?
			if( $n >= $int || !count($data) ) {
				$p = ($days > 31) ? "{$month}-".substr( $year, 2, 2 ) : "{$month}/{$day}";
				$n = 0;
			} else {
				$p = "";
			}
			$data[] = array( $p, $dayAve, $cumAve, $dayCount );
			$lastDate = $row->rfh_date;
			$lastDAve = $dayAve;
			$lastRAve = $cumAve;
		}
		$dbr->freeResult( $res );
		// Minimum sample size
		if( count($data) < 2 ) {
			return false;
		}
		// Fit to [0,4]
		foreach( $data as $x => $dataRow ) {
			$data[$x][3] = $dataRow[3]/$this->dScale;
		}
		$plot->SetDataValues($data);
		$plot->SetPointShapes( array('dot','dot','dot') );
		$plot->setPointSizes( array(1,1,4) );
		$plot->SetDataColors( array('blue','green','red') );
		$plot->SetLineStyles( array('solid','solid','none') );
		$plot->SetBackgroundColor('#F8F8F8');
		// Turn off X axis ticks and labels because they get in the way:
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->SetYTickIncrement( .5 );
		// Set plot area
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
		require_once( "$wgSvgGraphDir/svgGraph.php" ); // load classes
		require_once( "$wgSvgGraphDir/svgGraph2.php" ); // load classes
		// Define the object
		$plot = new svgGraph2();
		// Set file path
		$dir = dirname($filePath);
		// Make sure directory exists
		if( !is_dir($dir) && !wfMkdirParents( $dir, 0777 ) ) {
			return false;
		}
		// Set some parameters
		$plot->graphicWidth = 1000;
		$plot->graphicHeight = 400;
		$plot->plotWidth = 950;
		$plot->plotHeight = 350;
		$plot->decimalPlacesY = 1;
		$plot->plotOffsetX = 30;
		$plot->plotOffsetY = 25;
		$plot->numGridlinesY = 9;
		$plot->innerPaddingX = 5;
		$plot->innerPaddingY = 2;
		$plot->outerPadding = 0;
		$plot->offsetGridlinesX = 0;
		$plot->minY = 0;
		$plot->maxY = 4;
		// Set cutoff time for period
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_unixtime = time() - ($this->period * 24 * 3600);
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->addQuotes( wfTimestamp( TS_MW, $cutoff_unixtime ) );
		// Define the data using the DB rows
		$dataX = $dave = $rave = $dcount = array();
		$totalVal = $totalCount = $n = 0;
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
			$int = ($days > 31) ? 31 : intval( ceil($days/12) );
			$res->seek( 0 );
		}
		while( $row = $dbr->fetchObject( $res ) ) {
			$totalVal += (int)$row->rfh_total;
			$totalCount += (int)$row->rfh_count;
			$dayCount = (real)$row->rfh_count;
			$dayAve = (real)$row->rfh_total/(real)$row->rfh_count;
			$cumAve = (real)$totalVal/(real)$totalCount;
			$year = intval( substr( $row->rfh_date, 0, 4 ) );
			$month = intval( substr( $row->rfh_date, 4, 2 ) );
			$day = intval( substr( $row->rfh_date, 6, 2 ) );
			# Fill in days with no votes to keep spacing even
			if( isset($lastDate) ) {
				$dayGap = wfTimestamp(TS_UNIX,$row->rfh_date) - wfTimestamp(TS_UNIX,$lastDate);
				$x = intval( $dayGap/86400 );
				# Day gaps...
				for( $x; $x > 1; --$x ) {
					$dataX[] = "";
					$dave[] = $lastDAve;
					$rave[] = $lastRAve;
					$dcount[] = 0;
					$n++;
				}
			}
			$n++;
			# Label point?
			if( $n >= $int || !count($dataX) ) {
				$p = ($this->period > 31) ? "{$month}-".substr( $year, 2, 2 ) : "{$month}/{$day}";
				$n = 0;
			} else {
				$p = "";
			}
			$dataX[] = $p;
			$dave[] = $dayAve;
			$rave[] = $cumAve;
			$dcount[] = $dayCount;
			$lastDate = $row->rfh_date;
			$lastDAve = $dayAve;
			$lastRAve = $cumAve;
		}
		$dbr->freeResult( $res );
		// Minimum sample size
		if( count($dataX) < 2 ) {
			return false;
		}
		// Fit to [0,4]
		foreach( $dcount as $x => $c ) {
			$dcount[$x] = $c/$this->dScale;
		}
		$plot->dataX = $dataX;
		$plot->dataY['dave'] = $dave;
		$plot->dataY['rave'] = $rave;
		$plot->dataY['dcount'] = $dcount;
		$plot->styleTagsX = 'font-family: monospace; font-size: 7.5pt;';
		$plot->styleTagsY = 'font-family: sans-serif; font-size: 10pt;';
		$plot->format['dave'] = array( 'style' => 'stroke:blue; stroke-width:1;');
		$plot->format['rave'] = array( 'style' => 'stroke:green; stroke-width:1;');
		$plot->format['dcount'] = array( 'style' => 'stroke:none; stroke-width:1;', 
			'attributes' => "marker-end='url(#circle)'");
		$plot->title = wfMsgExt('ratinghistory-graph',array('parsemag'),
			$totalCount, wfMsgHtml("readerfeedback-$tag"), $this->page->getPrefixedText() );
		$plot->styleTitle = 'font-family: sans-serif; font-size: 10pt;';
		// extra code for markers
		$plot->extraSVG = 
			'<defs>
			  <marker id="circle" style="stroke:red; stroke-width:0; fill:red; "
				viewBox="0 0 10 10" refX="5" refY="5" orient="0"
				markerUnits="strokeWidth" markerWidth="5" markerHeight="5">
				<circle cx="5" cy="5" r="4"/>
			  </marker>
			</defs>';
		# Create the graph
		$plot->init();
		$plot->drawGraph();
		$plot->polyLine('dave');
		$plot->polyLine('rave');
		$plot->line('dcount');
		// Fucking IE...
		$nsParams = self::renderForIE() ? 
			"" : "xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'";
		$plot->generateSVG( $nsParams );
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
		$suffix = self::renderForIE() ? '-ie' : '';
		$pageId = $this->page->getArticleId();
		# Paranoid check. Should not be necessary, but here to be safe...
		if( !preg_match('/^[a-zA-Z]{1,20}$/',$tag) ) {
			throw new MWException( 'Invalid tag name!' );
		}
		return "{$pageId}/{$tag}/l{$this->period}d{$suffix}.{$ext}";
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
	
	private static function renderForIE() {
		if( isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== false ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getUserList( $tag ) {
		// Set cutoff time for period
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_unixtime = time() - ($this->period * 24 * 3600);
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->addQuotes( wfTimestamp( TS_MW, $cutoff_unixtime ) );
		$firstRevTS = $dbr->selectField( 'revision',
			'rev_timestamp',
			array( "rev_timestamp <= $cutoff" ),
			__METHOD__,
			array( 'ORDER BY' => 'rev_timestamp DESC' )
		);
		if( !$firstRevTS ) {
			return false;
		}
		$res = $dbr->select( array( 'revision', 'reader_feedback', 'user' ),
			array( 'rfb_user', 'rfb_ip', 'user_name', 'COUNT(*) as n' ),
			array( 'rev_page' => $this->page->getArticleId(),
				"rev_timestamp >= $firstRevTS",
				"rev_id = rfb_rev_id",
				"rfb_timestamp >= $firstRevTS" ),
			__METHOD__,
			array( 'GROUP BY' => 'rfb_user, rfb_ip' ),
			array( 'user' => array( 'LEFT JOIN', 'user_id = rfb_user') )
		);
		$total = $res->numRows();
		if( !$total ) {
			return false;
		}
		$middle = ceil( count($total)/2 );
		$count = 0;
		
		$html = "<table class='fr_reader_feedback_users' style='width: 100%;'><tr>";
		$html .= "<td width='30%' valign='top'><ul>\n";
		while( $row = $res->fetchObject() ) {
			if( !$row->rfb_user ) {
				$name = htmlspecialchars( $row->rfb_ip );
			} else {
				$name = $row->user_name;
			}
			$title = Title::makeTitleSafe( NS_USER, $name );
			$html .= '<li>'.$this->skin->makeLinkObj( $title, $title->getText() )." [{$row->n}]</li>";
			$count++;
			if( $total > 3 && $count == $middle ) {
				$html .= "</ul></td><td width='10%'></td><td width='30%' valign='top'><ul>";
			}
		}
		$html .= "</ul></td><td width='30%' valign='top'></td></tr></table>\n";
		return $html;
	}
	
	/**
	* Check if a graph file is expired.
	* @param string $tag
	* @param string $path, filepath to existing file
	* @returns string
	*/
	public function fileExpired( $tag, $path ) {
		if( $this->doPurge || !file_exists($path) ) {
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
	
	/**
	* Get highest touch timestamp of the tags. This uses a tiny filesort.
	* @returns string
	*/
	public function getTouched() {
		$dbr = wfGetDB( DB_SLAVE );
		$tagTimestamp = $dbr->selectField( 'reader_feedback_pages', 
			'MAX(rfp_touched)',
			array( 'rfp_page_id' => $this->page->getArticleId() ),
			__METHOD__ );
		return wfTimestamp( TS_MW, $tagTimestamp );
	}
}
