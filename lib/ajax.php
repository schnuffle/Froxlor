<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2013 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Roman Schmerold <bnoize@froxlor.org>
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    AJAX
 *
 */

// Load the user settings
define('FROXLOR_INSTALL_DIR', dirname(dirname(__FILE__)));
if (! file_exists('./userdata.inc.php')) {
	die();
}
require './userdata.inc.php';
require './tables.inc.php';
require './classes/database/class.Database.php';
require './classes/settings/class.Settings.php';
require './functions/validate/function.validate_ip.php';
require './functions/validate/function.validateDomain.php';
require './classes/cURL/class.HttpClient.php';

if (isset($_POST['action'])) {
	$action = $_POST['action'];
} elseif (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = "";
}

if ($action == "newsfeed") {
	if (isset($_GET['role']) && $_GET['role'] == "customer") {
		$feed = Settings::Get("customer.news_feed_url");
	} else {
		$feed = "https://inside.froxlor.org/news/";
	}
	
	if (function_exists("simplexml_load_file") == false) {
		outputItem("Newsfeed not available due to missing php-simplexml extension", "Please install the php-simplexml extension in order to view our newsfeed.");
		exit();
	}
	
	if (function_exists('curl_version')) {
		$output = HttpClient::urlGet($feed);
		$news = simplexml_load_string(trim($output));
	} else {
		outputItem("Newsfeed not available due to missing php-curl extension", "Please install the php-curl extension in order to view our newsfeed.");
		exit();
	}
	
	if ($news !== false) {
		for ($i = 0; $i < 3; $i ++) {
			$item = $news->channel->item[$i];
			
			$title = (string) $item->title;
			$link = (string) $item->link;
			$date = date("Y-m-d G:i", strtotime($item->pubDate));
			$content = preg_replace("/[\r\n]+/", " ", strip_tags($item->description));
			$content = substr($content, 0, 150) . "...";
			
			outputItem($title, $content, $link, $date);
		}
	} else {
		echo "";
	}
} else {
	echo "No action set.";
}

function outputItem($title, $content, $link = null, $date = null)
{
	echo "<li class=\"clearfix\">
			<div class=\"newsfeed-body clearfix\">
				<div class=\"header\">
					<strong class=\"primary-font\">";
			if (! empty($link)) {
				echo "<a href=\"{$link}\" target=\"_blank\">";
			}
			echo $title;
			if (! empty($link)) {
				echo "</a>";
			}
			echo "</strong>";
			if (! empty($date)) {
				echo "<small class=\"pull-right text-muted\">
                            <i class=\"fa fa-clock-o fa-fw\"></i> {$date}
                        </small>";
			}
			echo "</div>
                    <p>
                        {$content}
                    </p>
                </div>
            </li>";
}
