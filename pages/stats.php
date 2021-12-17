<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

const ACTIVE_PAGE = 'stats';
$statspage = new BasicPage();
$statspage->setPageTitleAddition('Stats');

$html       = '<script type="text/javascript" src="' . PUB_PATH . 'js/jquery.jqplot-1.0.9.min.js"></script>';
$html       .= '<script type="text/javascript" src="' . PUB_PATH .
    'js/jqplot/jqplot.canvasAxisTickRenderer-1.0.9.js"></script>';
$html       .= '<script type="text/javascript" src="' . PUB_PATH .
    'js/jqplot/jqplot.canvasTextRenderer-1.0.9.js"></script>';
$html       .= '<script type="text/javascript" src="' . PUB_PATH . 'js/jqplot/jqplot.highlighter-1.0.9.js"></script>';
$html       .= '<script type="text/javascript" src="' . PUB_PATH .
    'js/jqplot/jqplot.dateAxisRenderer-1.0.9.js"></script>';
$javascript = '$(document).ready(function(){
	var l5P = [];
	var l15P = [];
	var ucnt = [];
	var ltm = [];
	var ltmm = [];
	var pgv = [];' . "\n" . '	';

$tmpd    = strtotime("-24 hours");
$startd  = gmmktime(
    (int)gmdate('H', $tmpd),
    0,
    0,
    (int)gmdate('n', $tmpd),
    (int)gmdate('j', $tmpd),
    (int)gmdate('Y', $tmpd)
);
$endd    = $startd + 86400;
$procd   = $startd;
$timearr = [];
while ($procd < $endd) {
    $timearr[gmdate('Y-m-d H:i', $procd)] = [0, 0, 0, 0, 0, 0, 0];
    $procd                                += 300;
}
$res = $db->query(
    "SELECT * FROM serverloadstats WHERE `time` BETWEEN ? AND ? ORDER BY `time`",
    [gmdate('Y-m-d H:i:s', ($startd - 25)), gmdate('Y-m-d H:i:s', ($endd + 5))]
);
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $timearr[gmdate('Y-m-d H:i', strtotime($row['time'] . ' UTC'))] = [
        $row['avg1'],
        $row['avg5'],
        $row['avg15'],
        $row['users_online'],
        0,
        0,
        0,
    ];
}
$maxld = 0.1;
$res   = $db->query(
    "SELECT * FROM page_loadtime_avg WHERE `time` BETWEEN ? AND ? ORDER BY `time`",
    [gmdate('Y-m-d H:i:s', ($startd - 25)), gmdate('Y-m-d H:i:s', ($endd + 5))]
);
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $timearr[gmdate('Y-m-d H:i', strtotime($row['time'] . ' UTC'))][4] = $row['load_time'];
    $timearr[gmdate('Y-m-d H:i', strtotime($row['time'] . ' UTC'))][5] = $row['load_time_max'];
    if ($row['load_time_max'] > $maxld) {
        $maxld = ceil($row['load_time_max'] * 100) / 100;
    }
}
$res = $db->query(
    "SELECT * FROM pageview_stats WHERE `time` BETWEEN ? AND ? ORDER BY `time`",
    [gmdate('Y-m-d H:i:s', ($startd - 25)), gmdate('Y-m-d H:i:s', ($endd + 5))]
);
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $timearr[gmdate('Y-m-d H:i', strtotime($row['time'] . ' UTC'))][6] = $row['views'];
}
foreach ($timearr as $plottime => $plotvar) {
    $javascript .= 'l5P.push([\'' . $plottime . '\', ' . $plotvar[1] . ']);';
    $javascript .= 'l15P.push([\'' . $plottime . '\', ' . $plotvar[2] . ']);';
    $javascript .= 'ucnt.push([\'' . $plottime . '\', ' . $plotvar[3] . ']);';
    if (substr($plottime, -2) == '00' || substr($plottime, -2) == '30') {
        $javascript .= 'ltm.push([\'' . $plottime . '\', ' . $plotvar[4] . ']);';
        $javascript .= 'ltmm.push([\'' . $plottime . '\', ' . $plotvar[5] . ']);';
    }
    if (substr($plottime, -2) == '00') {
        $javascript .= 'pgv.push([\'' . $plottime . '\', ' . $plotvar[6] . ']);';
    }
}
$mind       = gmdate('Y-m-d H:i', $startd);
$maxd       = gmdate('Y-m-d H:i', $endd);
$javascript .= "\n" . '	var plot1 = $.jqplot ("loadchart", [l15P, l5P], {
		series:[
			{
				color: "#9ED89F",
				fill: true,
				fillAlpha: 0.7,
				markerOptions: {
					show: false
				}
			},
			{
				color: "#444444",
				lineWidth: 1,
				markerOptions: {
					show: false
				}
			}
		],
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				fontSize: "10pt"
			}
		},
		axes: {
			xaxis: {
				renderer:$.jqplot.DateAxisRenderer,
				tickOptions:{formatString:\'%a %H:%M UTC\'},
				max: \'' . $maxd . '\',
				min: \'' . $mind . '\',
				showTicks: false,
				showTickMarks: false
			},
			yaxis: {
				min: 0,
				tickOptions:{formatString:\'%.2f\'}
			}
		},
		highlighter: {
			show: true,
			tooltipAxes: "yx",
			sizeAdjust: 1,
			useAxesFormatters: true
		}
	});
	/*var plot2 = $.jqplot("userchart", [ucnt], {
		series: [
			{
				lineWidth: 1.5,
				color: "#EAA228",
				//fill: true,
				markerOptions: {
					show: false
				}
			}
		],
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				fontSize: "10pt"
			}
		},
		axes: {
			xaxis: {
				renderer:$.jqplot.DateAxisRenderer,
				tickOptions:{formatString:\'%a %H:%M UTC\'},
				max: \'' . $maxd . '\',
				min: \'' . $mind . '\',
				showTicks: false,
				showTickMarks: false
			},
			yaxis: {
				min: 0,
			}
		},
		highlighter: {
			show: true,
			tooltipAxes: "yx",
			sizeAdjust: 1,
			useAxesFormatters: true
		}
	});*/
	var plot3 = $.jqplot("ltmchart", [ltmm, ltm], {
		series: [
			{
				lineWidth: 1.5,
				color: "#AE2020",
				//fill: true,
				//markerOptions: {
				//	show: false
				//},
				rendererOptions: {
					smooth: true
				}
			},
			{
				lineWidth: 1.5,
				color: "#4bb2c5",
				//fill: true,
				//markerOptions: {
				//	show: false
				//},
				rendererOptions: {
					smooth: true
				}
			}
		],
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				fontSize: "10pt"
			}
		},
		axes: {
			xaxis: {
				renderer:$.jqplot.DateAxisRenderer,
				tickOptions:{formatString:\'%a %H:%M UTC\'},
				max: \'' . $maxd . '\',
				min: \'' . $mind . '\',
				showTicks: false,
				showTickMarks: false
			},
			yaxis: {
				min: 0.03,
				//max: ' . $maxld . ',
				tickOptions:{formatString:\'%.4f s\'}
			}
		},
		highlighter: {
			show: true,
			tooltipAxes: "yx",
			sizeAdjust: 4,
			useAxesFormatters: true
		}
	});
	var plot4 = $.jqplot("pagevchart", [pgv], {
		series: [
			{
				lineWidth: 1.5,
				color: "#1B2BF5",
				//fill: true,
				//markerOptions: {
				//      show: false
				//},
				rendererOptions: {
					smooth: true
				}
			}
		],
		axes: {
			xaxis: {
				renderer:$.jqplot.DateAxisRenderer,
				tickOptions:{formatString:\'%a %H:%M UTC\'},
				max: \'' . $maxd . '\',
				min: \'' . $mind . '\',
				showTicks: false,
				showTickMarks: false
			},
			yaxis: {
				min: 0
			}
		},
		highlighter: {
			show: true,
			tooltipAxes: "yx",
			sizeAdjust: 4,
			useAxesFormatters: true
		}
	});
});';

$html .= '<div id="content"><div>';
$html .= '<h1>Stats</h1>';
$html .= '<p>Some site statistics...</p>';
$html .= '<h2>Wallpapers</h2>';

$res      = $db->query("SELECT count(*) cnt FROM wallpaper WHERE deleted = 0");
$totalcnt = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $totalcnt = $row['cnt'];
}
$res    = $db->query("SELECT count(*) cnt FROM wallpaper WHERE deleted = 1");
$delcnt = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $delcnt = $row['cnt'];
}
$html      .= '<p>There are total of <span class="total-of">' . number_format($totalcnt, 0, ',', ' ') .
    '</span> listed wallpapers on the website.</p>';
$html      .= '<p>Total of <span class="total-of">' . number_format($delcnt, 0, ',', ' ') .
    '</span> wallpapers have been removed from the list.</p>';
$html      .= '<h2>Site usage</h2>';
$res       = $db->query("SELECT count(*) cnt FROM page_loadtime");
$pageviews = 600000;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $pageviews = 600000 + $row['cnt'];
}
$res         = $db->query(
    "SELECT count(*) cnt FROM page_loadtime WHERE time BETWEEN ? AND ?",
    [gmdate('Y-m-d H:i:s', $startd), gmdate('Y-m-d H:i:s', $endd)]
);
$pageviews24 = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $pageviews24 = $row['cnt'];
}
$html     .= '<p>After launch in February 2012, the site has received <span class="total-of">' .
    number_format($pageviews, 0, ',', ' ') . '</span> pageviews. Of those, <span class="total-of">' .
    number_format($pageviews24, 0, ',', ' ') . '</span> were in the last 24 hours.</p>';
$res      = $db->query("SELECT count(*) cnt FROM click_log");
$clickcnt = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $clickcnt = $row['cnt'];
}
$res        = $db->query(
    "SELECT count(*) cnt FROM click_log WHERE time BETWEEN ? AND ?",
    [gmdate('Y-m-d H:i:s', $startd), gmdate('Y-m-d H:i:s', $endd)]
);
$click24cnt = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $click24cnt = $row['cnt'];
}
$html .= '<p>Wallpaper download links haven been clicked total of <span class="total-of">' .
    number_format($clickcnt, 0, ',', ' ') . '</span> times. Of those, <span class="total-of">' .
    number_format($click24cnt, 0, ',', ' ') . '</span> were in the last 24 hours.</p>';

$html .= '<h2>Tags</h2>';
$html .= file_get_contents(ROOT_DIR . 'stats');

$html .= '<h2>Server load in the last 24 hours</h2>';
$html .= '<p>';
$html .= '<div style="background:#444;display:inline-block;width:12px;height:12px;margin-right:10px;"></div>';
$html .= 'Load average (5 min)';
$html .= sprintf(
    '<div style="%s"></div>',
    'background:#9ED89F;display:inline-block;width:12px;height:12px;margin-right:10px;margin-left:30px;'
);
$html .= 'Load average (15 min)';
$html .= '</p>';
$html .= '<p><div id="loadchart" style="height:250px;width:960px;position:relative;"></div></p>';
$html .= '<br /><h2>Page generation time in the last 24 hours</h2>';
$html .= '<p>';
$html .= '<div style="background:#4bb2c5;display:inline-block;width:12px;height:12px;margin-right:10px;"></div>';
$html .= 'Average (30 min)';
$html .= sprintf(
    '<div style="%s"></div>',
    'background:#AE2020;display:inline-block;width:12px;height:12px;margin-right:10px;margin-left:30px;'
);
$html .= 'Maximum (30 min)';
$html .= '</p>';
$html .= '<p><div id="ltmchart" style="height:250px;width:960px;position:relative;"></div></p>';
$html .= '<br /><h2>Pageviews in the last 24 hours</h2>';
$html .= '<p><div id="pagevchart" style="height:250px;width:960px;position:relative;"></div></p>';
$html .= '</div></div>';
$meta = "\n" . '		<meta name="twitter:card" content="summary" />' . "\n";
$meta .= '		<meta name="twitter:description" content="Site statistics." />' . "\n";

$statspage->setJavascript($javascript);
$statspage->setHtml($html);
$statspage->setMeta($meta);

$response = new Response($statspage);
$response->output();
