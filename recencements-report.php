<?php

/*
Plugin Name: Recencement graphical statistics report
Description: This plug-in will display the graphical report for admin about, monthly user registration, monthly post summary, monthly comments posted summary and category wise post summary.
Version: 0.1
Author: amsayk
*/

function recencement_report_deactivate() {}

function recencement_report_activation() {}


/*
 *  Render graph for on keyword
 *
 * */
function renderReport0($start, $end, $keyword){
	$siteurl = get_option('siteurl');
	$pluginurl = "/wp-content/plugins/recencement-statistics-report";
	$fullpluginurl = $siteurl.$pluginurl;

	$title = __('Recencement Graphical statistics');
	$title_mentions_HTML = __("Monthly mentions of ") . "<b>" . strtoupper($keyword) . "</b>";

	include("recencements-charts.php");

    ?>

    <div class="wrap">
    <h2><?php echo wp_specialchars( $title ); ?></h2>
    <h5><?php echo $title_mentions_HTML; ?></h5>
    </div>

        <?php

    $data = get_results($start, $end, $keyword);

    echo "<SCRIPT LANGUAGE=\"Javascript\" SRC=\"$fullpluginurl/FusionCharts.js\"></SCRIPT>";

    $i = 0;
    $graph_ststus=0;
    $arrposts = array();
    $monthnames = array(1 => 'January',2 => 'February',3 => 'March',4 => 'April',5 => 'May',6 => 'June',7 => 'July',8 => 'August',9 => 'September',10 => 'October',11 => 'November',12 => 'December');
    foreach ( $data as $row ) {
        $arrposts[$i][1] = $monthnames[$row->month];
        $arrposts[$i][2] = $row->posts;
        $arrposts[$i][3] = $row->year;
        $i = $i+1;
    }
    if($i > 0) { $graph_ststus = 1; }
    $strXML = "<graph caption='Monthly mentions summary for ...' subcaption='This will display the monhtly mentions of ... ' xAxisName='Months' yAxisMinValue='0' yAxisName='Total monthly mentions' decimalPrecision='0' formatNumberScale='0' numberPrefix=' ' showNames='1' showValues='0' showAlternateHGridColor='1' AlternateHGridColor='bbd8e7' canvasBorderColor='ECF4F9' baseFontColor='1A5873' lineColor='2EA0D1' divLineColor='8cbdd5' divLineAlpha='20' alternateHGridAlpha='5' rotateNames='1'>";
    foreach ($arrposts as $arSubData) {
        $strXML = $strXML . "<set name='" . $arSubData[1] . " " . $arSubData[3] . "' value='" . $arSubData[2] ."' hoverText='" . $arSubData[1] . "' />";
    }
    $strXML = $strXML . "</graph>";
    if($graph_ststus==1) {
        echo renderChart("$fullpluginurl/FCF_Line.swf", "", $strXML, "wp_posts", 800, 350, false, false);
    } else {
        echo "<div align='center'>At present monthly post summary graph not available.</div>";
    }
}

function create_dataset_and_category($data, $monthnames) {
    $i = 0;
    $ds = array();
    $cats = array();
    $graph_status=false;
    foreach ( $data as $row ) {

        $key = $monthnames[$row->month];
        $val = $key . " " . $row->year;
        $cats[$key] || $cats[$key] = $val;
        $ds[$val] = array($row->posts, $monthnames[$row->month]);
        $i = $i+1;
    }
    if($i > 0) { $graph_status = true; }

    return array($graph_status, $ds, $cats);
}

function get_results($start, $end, $keyword) {
    global $wpdb;
    
	$sSql = "SELECT MONTH(post_date) as month, YEAR(post_date) as year, COUNT(*) as posts";
	$sSql = $sSql . " FROM $wpdb->posts";
    $sSql = $sSql . " WHERE post_type = 'post' AND post_date >= '" . date('Y-m-d', $start) . "' AND post_date <= '" . date('Y-m-d', $end) . "'";
    $sSql = $sSql . " AND ucase(post_title) like '%" . strtolower($keyword) . "%'";
    $sSql = $sSql . " OR ucase(post_content) like '%" . strtolower($keyword) . "%'";
    $sSql = $sSql . " GROUP BY MONTH(post_date), YEAR(post_date) order by YEAR(post_date) desc, MONTH(post_date) desc limit 0,12";

    return @$wpdb->get_results($sSql);
}

/*
 *  Render graph for comparison
 *
 * */
function renderReport1($start, $end, $keyword,$keyword2){
	$siteurl = get_option('siteurl');
	$pluginurl = "/wp-content/plugins/recencement-statistics-report";
	$fullpluginurl = $siteurl.$pluginurl;

	$title = __('Recencement Graphical statistics');
	$title_mentions_HTML = __("Comparison of Monthly mentions (... vs. ...) ") . "<b>" . strtoupper($keyword) . "</b>";

	include("recencements-charts.php");

    ?>

    <div class="wrap">
    <h2><?php echo wp_specialchars( $title ); ?></h2>
    <h5><?php echo $title_mentions_HTML; ?></h5>
    </div>

        <?php

    echo "<SCRIPT LANGUAGE=\"Javascript\" SRC=\"$fullpluginurl/FusionCharts.js\"></SCRIPT>";

    //--------------------- Generate the chart ---------------------------------------------

    $monthnames = array(1 => 'January',2 => 'February',3 => 'March',4 => 'April',5 => 'May',6 => 'June',7 => 'July',8 => 'August',9 => 'September',10 => 'October',11 => 'November',12 => 'December');
    $all_categories = array(1 => 'January 2011',2 => 'February 2011',3 => 'March 2011',4 => 'April 2011',5 => 'May 2011',6 => 'June 2011',7 => 'July 2011',8 => 'August 2011',9 => 'September 2011',10 => 'October 2011',11 => 'November 2011',12 => 'December 2011');

    $strXML = "<chart caption='Comparison between monthly mentions of ...  and ...' xAxisName='Months' yAxisName='Total monthly mentions'>";
    @list($graph_status1, $dataset1, $cats1) = create_dataset_and_category(get_results($start, $end, $keyword), $monthnames);
    @list($graph_status2, $dataset2, $cats2) = create_dataset_and_category(get_results($start, $end, $keyword2), $monthnames);

    $ds1XML = '<dataset seriesName=\'' . $keyword . '\'>';
    $ds2XML = '<dataset seriesName=\'' . $keyword2 . '\'>';
    $strXML .= '<categories>';
    foreach($all_categories as $cat) {
        if (array_search($cat, $cats1) || array_search($cat, $cats2)) {
            $strXML .= '<category label=\'' . $cat . '\' />';
        }

        if (array_key_exists($cat, $dataset1) || array_key_exists($cat, $dataset2)) {
            $ds1XML .= '<set value=\'' . (empty($dataset1[$cat][0]) ? '0' : $dataset1[$cat][0]) . '\' hoverText=\'' . (empty($dataset1[$cat][1]) ? '0' : $dataset1[$cat][1]) . '\' />';
            $ds2XML .= '<set value=\'' . (empty($dataset2[$cat][0]) ? '0' : $dataset2[$cat][0]) . '\' hoverText=\'' . (empty($dataset2[$cat][1]) ? '0' : $dataset2[$cat][1]) . '\' />';
        }
    }
    $strXML .= '</categories>';
    $ds1XML .= '</dataset>';
    $ds2XML .= '</dataset>';

    $strXML .= $ds1XML;
    $strXML .= $ds2XML;
    $strXML .=  '</chart>';

    if($graph_status1 || $graph_status2) { // Some rows were returned
        echo renderChart("$fullpluginurl/MSLine.swf", "", $strXML, "wp_posts", 800, 350, false, false);
    } else {
        echo "<div align='center'>At present monthly post summary graph not available.</div>";
    }
}

function recencement_report(){

  $action = isset($_REQUEST['act']) ? urldecode($_REQUEST['act']) : 'start';
  $state = isset($_REQUEST['state']) ? urldecode($_REQUEST['state']) : '';

  $start_date = isset($_REQUEST['start_date']) ? urldecode($_REQUEST['start_date']) : null;
  $end_date = isset($_REQUEST['end_date']) ? urldecode($_REQUEST['end_date']) : date('Y-m-d');
  $keyword = isset($_REQUEST['keyword']) ? trim(urldecode($_REQUEST['keyword'])) : null;
  $keyword2 = isset($_REQUEST['keyword2']) ? trim(urldecode($_REQUEST['keyword2'])) : null;

    if($state == 'edit') {
        echo recencement_report_generate_form($action, $start_date, $end_date, $keyword, $keyword2);
        return;
    }

    switch($action) {

        case 'form':

            $startLong = strtotime($start_date);
            if(! isset($start_date) && ($startLong == false || $startLong < 0)) {
                echo recencement_report_generate_form('form', '', $end_date, $keyword, '', 'Please enter a valid start date', 'start');
                return;
            }

            $endLong = strtotime($end_date);
            if(! isset($end_date) && ($endLong == false || $endLong < 0)) {
                echo recencement_report_generate_form('form', $start_date, '', $keyword, '', 'Please enter a valid end date', 'end');
                return;
            }

            if(! isset($keyword)) {
                echo recencement_report_generate_form('form', $start_date, $end_date, '', '', 'Please enter a valid keyword', 'keyword');
                return;
            }

            //no errors: render the graph here
            echo '<br/><br/><a href="' . $_SERVER['PHP_SELF'] . "?state=edit&act=form&start_date=$start_date&end_date=$end_date&keyword=$keyword&keyword2=$keyword2&page=recencement-statistics-report/recencements-report.php" . '">Edit options</a> | ';
            echo '<a href="' . $_SERVER['PHP_SELF'] . "?state=edit&act=cmp&start_date=$start_date&end_date=$end_date&keyword=$keyword&keyword2=$keyword2&page=recencement-statistics-report/recencements-report.php" . '">Compare to another keyword</a><br/><br/>';
            renderReport0($startLong, $endLong, $keyword);
            break;

        case 'cmp':

            $startLong = strtotime($start_date);
            if(! isset($start_date) && ($startLong == false || $startLong < 0)) {
                echo recencement_report_generate_form('cmp', '', $end_date, $keyword, '', 'Please enter a valid start date', 'start');
                return;
            }

            $endLong = strtotime($end_date);
            if(! isset($end_date) && ($endLong == false || $endLong < 0)) {
                echo recencement_report_generate_form('cmp', $start_date, '', $keyword, '', 'Please enter a valid end date', 'end');
                return;
            }

            if(! isset($keyword)) {
                echo recencement_report_generate_form('cmp', $start_date, $end_date, '', $keyword2, 'Please enter a valid keyword', 'keyword');
                return;
            }

            if(! isset($keyword2)) {
                echo recencement_report_generate_form('cmp', $start_date, $end_date, $keyword, '', 'Please enter a valid second keyword', 'keyword2');
                return;
            }

            echo '<br/><br/><a href="' . $_SERVER['PHP_SELF'] . "?state=edit&act=cmp&start_date=$start_date&end_date=$end_date&keyword=$keyword&keyword2=$keyword2&page=recencement-statistics-report/recencements-report.php" . '">Edit options</a><br/><br/>';
            renderReport1($startLong, $endLong, $keyword, $keyword2);
            break;

        case 'start';

            echo recencement_report_generate_form('form', $start_date, $end_date, $keyword, $keyword2);
            break;
    }
}

function recencement_report_generate_form($action, $start_date, $end_date, $keyword, $keyword2='', $errMsgStr='', $errorKey='') {
    $siteurl = get_option('siteurl');
    $plugin_dir = '/wp-content/plugins/recencement-statistics-report';
    $fullurl = $siteurl.$plugin_dir;

    $errMsg = empty($errMsgStr) ? '' : '<span class="error"><img src="' . $fullurl. '/error.png"/>' . $errMsgStr . '</span>';
    $errorHtml = '<div id="msg" ' . ( empty($errMsg) ? 'style="display:none;"' : '' ) . ' class="clearfix">' . $errMsg . '</div>';

    $moreJs = $errorKey ? 'jQuery("#' . $errorKey . '").focus();' : '';
    $moreJs.= 'console.log("' . $action . '");';

    // Keyword 1
    $keywordTitle = __('Enter ' . ($action == 'cmp' ? 'first keyword' : 'keyword'));
    $keywordLabel = __($action == 'cmp' ? 'keyword 1' : 'Keyword');
    $keywordValidationMsg = __('Please enter a valid ' . ($action=='cmp'? 'first keyword' : 'keyword'));

    # keyword 2
    $keyword2Html = $action == 'cmp' ? '<label for="keyword2" title="Enter the second keyword"><span>Keyword 2:</span><input type="text" value="' . $keyword2 . '" name="keyword2" id="keyword2"/></label>' : '';

    $validateKeyword2 = $action == 'cmp' ? ' && validateEmpty("keyword2", "Please enter a valid second keyword")' : '';

    $SELF = $_SERVER['PHP_SELF'];

    return  <<<FORM

    <link rel='stylesheet' href="$fullurl/style.css" type='text/css' media='all' />
    <link rel="stylesheet" type="text/css" href="$fullurl/jquerytools/skin1.css"/>
    <script src="$fullurl/jquerytools/jquery.tools.min.js"></script>
    <script type="text/javascript">

      //<![CDATA[

          // the french localization
          jQuery.tools.dateinput.localize("fr",  {
             months:        'Janvier,F&eacute;vrier,Mars,Avril,Mai,Juin,Juillet,Ao&ucirc;t,' +
                             	'septembre,octobre,novembre,d&eacute;cembre',
             shortMonths:   'jan,f&eacute;v,mar,avr,mai,jun,jul,ao&ucirc;,sep,oct,nov,d&eacute;c',
             days:          'Dimanche,Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
             shortDays:     'dim,lun,mar,mer,jeu,ven,sam'
          });

        jQuery(function($){
            $('.date').dateinput({
                format: 'yyyy/mm/dd',
                firstDay: 1,
                speed: 'fast',
                lang: 'fr',
                min: -5 * 365,
                selectors: true,
                max: new Date()
            });

            $("#start").data("dateinput").change(function() {
	            $("#end").data("dateinput").setMin(this.getValue(), true);
            });

            $("#end").data("dateinput").change(function() {
	            $("#start").data("dateinput").setMax(this.getValue(), true);
            });

            $moreJs

            $('#report_form').submit(function (){
                var self = this;

                function validateEmpty(id, errorMsg) {
                  var it = $('#'+id, self),
                      val = it.val();

                      val = val != null ? val.trim(): '';

                      if(val == '') {
                          var msg = $('#msg', self),
                              msgId = 'msg-' + (new Date().getTime().toString());

                          msg.html('<span class="error"><img src="$fullurl/error.png"/>'+errorMsg+'</span>').slideDown();
                          window.setTimeout(function(){msg.slideUp().empty();}, 7000);
                          window.setTimeout(function(){it.focus();}, 400);
                          return false;
                        }

                        return true;
                }

                return validateEmpty('start', 'Please enter a valid start date') &&
                          validateEmpty('end', 'Please enter a valid end date') &&
                              validateEmpty('keyword', "$keywordValidationMsg") $validateKeyword2;

            });
        });

        //]]>

    </script>


<div class="wrap box" id="r_report_form">
    <h1>Recencement Statistics Reports</h1>
    <form id="report_form" method="post" action="$SELF?page=recencement-statistics-report/recencements-report.php">

        $errorHtml

        <input type="hidden" name="act" value="$action"/>

        <label for="start" title="Enter start date"><span>Start Date:</span>
            <input type="text" class="date" value="$start_date" name="start_date" id="start"/>
        </label>


        <label for="end" title="Enter end date"><span>End Date:</span>
            <input type="text"  class="date" name="end_date" id="end" value="$end_date"/>
        </label>


        <label for="keyword" title="$keywordTitle"><span>$keywordLabel:</span>
            <input type="text" value="$keyword" name="keyword" id="keyword"/>
        </label>

         $keyword2Html

        <div class="spacer">
            <input type="submit" value="Generate Report" id="generate_report_submit" name="generate_report"/>
        </div>

    </form>
</div>

FORM;

}

function recencement_report_add_to_menu() {
	add_options_page('Recencement graphical statistics', 'Recencement graphical statistics', 'manage_options', __FILE__, 'recencement_report' );
}

if (is_admin()) {
	add_action('admin_menu', 'recencement_report_add_to_menu');
}

register_activation_hook(__FILE__, 'recencement_report_activation');
add_action('admin_menu', 'recencement_report_add_to_menu');
register_deactivation_hook( __FILE__, 'recencement_report_deactivate' );


?>
