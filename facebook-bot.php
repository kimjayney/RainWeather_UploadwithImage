<?php
	date_default_timezone_set('Asia/Seoul');

	class Request {
		public $access_token;
		public function init() {
			$this->access_token = "Page-Access-Token"; // you should extend token from token debugger
		}

		public function do_request($url, $postdata) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}

		public function create_album($album_name, $message) {
			$graph_url= "https://graph.facebook.com/Page-ID/albums";
			$result = $this->do_request($graph_url, "is_default=false&name=$album_name&message=$message&access_token=" . $this->access_token);
			$encode = json_decode($result, TRUE);
			echo $encode["id"]; 
			return $encode["id"];
		}

		public function image_upload($album_id, $photo_url, $message) {
			$this->do_request("https://graph.facebook.com/$album_id/photos", "access_token=" . $this->access_token . "&url=" . $photo_url . "&name=$message");
		}

	}
	

	$req = new Request();

	$req->init();
	$gall_id = $req->create_album("경기도/서울 지역 날씨 정보", "경기도/서울 날씨 입니다.");
	$citys = array("경기도" => "Gyeonggi-1","경기도" =>  "Gyeonggi-2","경기도" =>  "Gyeonggi-3","서울" =>  "Seoul");

	foreach ($citys as $key =>$value) {
		$req->image_upload($gall_id, "IMAGE_URL",  $key . "지역 날씨");
	}
	

?>
