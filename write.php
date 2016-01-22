<?php
	//PHP7.0 Support 

 	$db = new PDO("");

	function file_get_contents_curl($url) {
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;
	}

	function query($query, $column) {
		$db = new PDO();
		foreach ($db->query($query) as $row) {
			return $row[$column];
		}
	}


	
	date_default_timezone_set('Asia/Seoul');
	$jpg_image = imagecreatefromjpeg('Background-Image.jpg');
	$black = imagecolorallocate($jpg_image, 0, 0 ,0 );
	$white = imagecolorallocate($jpg_image, 255, 255 ,255 );
	$font_path = 'Your-Font-File-TTF';


	$loc = array();
	$city_get = $_GET["city"];

	switch ($city_get) {
		case "Seoul":
			$list_city = array("강서구" => 11110, "관악구" => 11620, "은평구" => 11380, "성북구" => 11290 , "강남구" => 11680 ,"송파구" => 11710);
			imagettftext($jpg_image, 16 , 0, 238 , 40, $black, $font_path, "서울 날씨정보");
			header('Content-type: image/png');
			break;
		case "Gyeonggi-1": // 41
			imagettftext($jpg_image, 16 , 0, 215 , 40, $black, $font_path, "경기도 날씨정보 (1/3)");
			$list_city = array("평택" => 41220, "오산" => 41370, "화성" => 41590, "용인" => 41463 , "안성" => 41550 ,"수원" => 41117);
			header('Content-type: image/png');
			break;
		case "Gyeonggi-2": // 41
			imagettftext($jpg_image, 16 , 0, 215 , 40, $black, $font_path, "경기도 날씨정보 (2/3)");
			$list_city = array("안산" => 41271, "의왕" => 41430, "시흥" => 41390, "과천" => 41290 , "성남" => 41135 ,"이천" => 41500);
			header('Content-type: image/png');
			break;
		case "Gyeonggi-3":
			imagettftext($jpg_image, 16 , 0, 215 , 40, $black, $font_path, "경기도 날씨정보 (3/3)");
			$list_city = array("광명" => 41210, "하남시"=>41450, "구리" => 41310, "김포" => 41570 ,"의정부" => 41150,  "포천시" =>41650);
			header('Content-type: image/png');
			break;
		default:
			echo "No city selected";
			exit();
	}
	
	$loc_y_arr = array(0 => 168 , 1 => 210, 2 => 252 , 3 =>294 , 4 => 333, 5 => 374);

	$city_count = 0;
	
	foreach ($list_city as $city => $city_code) {
		$json = file_get_contents_curl("http://www.kma.go.kr/DFSROOT/POINT/DATA/leaf.$city_code.json.txt");
		$decode = json_decode($json, TRUE);
		$x = $decode[0]["x"];
		$y = $decode[0]["y"];

		$xmldata = file_get_contents_curl("http://www.kma.go.kr/wid/queryDFS.jsp?gridx=$x&gridy=$y");
		$xml = simplexml_load_string($xmldata);
		$json = json_encode($xml);
		$weather = json_decode($json,TRUE);

		$hour = date("H");
		$data = $weather["body"]["data"][0]; // today

		$date_today = date("Y-m-d");
		$time = time();
		$date_yest = date("Y-m-d", strtotime("-1 day", $time));
		$temp = $data["temp"];
		$status = $data["wfKor"];
		$yesterday_temp = query("SELECT temp from weather_db where date = '$date_yest' and hour = '$hour' and city_code = '$city_code'", "temp");
		$uniqid = uniqid('s.', true);

		$check = query("SELECT uniqid from weather_db where hour = '$hour' and city_code = '$city_code' and date = '$date_today'", "uniqid");

		if (!$check) {
			$query = $db->query("INSERT into weather_db(uniqid,city, temp, hour, date, status ,city_code) VALUES('$uniqid', '$city', '$temp', '$hour', '$date_today', '$status', '$city_code')");
		}
		
		if (!$yesterday_temp) {
			$yesterday_temp = "-";
		}

		$my_data = array("list_x" => array(122,215,330,401), "text" => array($city, $status, $yesterday_temp, $temp . "°C") , "location_y" => $loc_y_arr[$city_count]);
		array_push($loc, $my_data);
		$city_count++;
	}
	//create table weather_db(idx int(20), city varchar(20), temp int(20), hour int(20), date DATE)

	foreach ($loc as $value) {
		$list_x = $value["list_x"];
		$y = $value["location_y"];
		$inlist_idx = 0;
		foreach ($list_x as $xval) {
			imagettftext($jpg_image, 14 , 0, $xval , $y, $black, $font_path, $value["text"][$inlist_idx]);
			$inlist_idx++;
		}
	}
	$data = shell_exec('uptime');
	$uptime = explode(' up ', $data);
	$uptime = explode(', ', $uptime[1]);
	$uptime = $uptime[0].', '.$uptime[1];

	imagettftext($jpg_image, 10 , 0, 20 , 480, $white, $font_path, "RainC Lab, RainWeather");
	imagettftext($jpg_image, 7 , 0, 250, 490, $white, $font_path, "Server Uptime : " . $uptime );
	imagettftext($jpg_image, 10 , 0, 410 , 460, $white, $font_path, "업데이트 시간 : " . date("m-d H:i:s"));
	imagettftext($jpg_image, 10 , 0, 490 , 480, $white , $font_path, "날씨정보 : 기상청");
	imagejpeg($jpg_image);
	
	
	
?> 
