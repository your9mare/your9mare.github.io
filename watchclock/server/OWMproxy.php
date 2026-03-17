<?php
require_once '../.OWMproxyCfg.php';

function encode ($clrtxt)
{
	$ivlen = openssl_cipher_iv_length(CIPHER);
	$iv = openssl_random_pseudo_bytes($ivlen);
	$encry_raw = openssl_encrypt($clrtxt, CIPHER, ENCRY_KEY, OPENSSL_RAW_DATA, $iv);

	// combine iv for storage
	$encry_encoded = base64_encode($encry_raw);
	$iv_encoded = base64_encode($iv);
	$storage_str = $iv_encoded . '::' . $encry_encoded;
	return $storage_str;
}

function decode ($storage_str)
{
	// To decrypt later, first split the IV and encrypted data from stored string
	list($iv_encoded, $encry_encoded) = explode('::', $storage_str, 2);
	$encry_raw = base64_decode($encry_encoded);
	$iv = base64_decode($iv_encoded);

	$decry_raw = openssl_decrypt($encry_raw, CIPHER, ENCRY_KEY, OPENSSL_RAW_DATA, $iv);
	return $decry_raw;
}

define ('OWM', 'https://api.openweathermap.org/');
// http://api.openweathermap.org/geo/1.0/direct?q=Palo%20Alto,CA,US&appid={}
function format_geo_url($city, $appid) {
	$url = OWM . 'geo/1.0/direct?q=' . $city . '&appid=' . $appid;
	return $url;
}

// https://api.openweathermap.org/data/2.5/forecast?lat={lat}lon={lon}&units=imperial&appid={appid}
function format_forecast_url($lat, $lon, $appid) {
	$url = OWM . 'data/2.5/forecast?lat=' . $lat . '&lon=' . $lon . '&units=imperial&appid=' . $appid;
	return $url;
}

// https://api.openweathermap.org/data/2.5/weather?lat=37.4443293&lon=-122.1598465&appid={}
function format_weather_url($lat, $lon, $appid) {
	$url = OWM . 'data/2.5/forecast?lat=' . $lat . '&lon=' . $lon . '&units=imperial&appid=' . $appid;
	return $url;
}

function method() {
	//$method = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_EMAIL);
	if (isset($_SERVER['QUERY_STRING'])) {
		parse_str($_SERVER['QUERY_STRING'], $query);
		if (isset($query['m'])) {
			$appid = decode(APPID_ENC);

			$method = $query['m'];
			switch ($method) {
				case 'geocode':
					if (isset($query['q'])) {
						$city = $query['q'];
						$url = format_geo_url($city, $appid);
						$data = file_get_contents($url);
						echo $data;
					}
					break;
				case 'weather':
					if (isset($query['lat']) && isset($query['lon'])) {
						$lat = $query['lat'];
						$lon = $query['lon'];
						$url = format_weather_url($lat, $lon, $appid);
						$data = file_get_contents($url);
						echo $data;
					}
					break;
				case 'forecast':
					if (isset($query['lat']) && isset($query['lon'])) {
						$lat = $query['lat'];
						$lon = $query['lon'];
						$url = format_forecast_url($lat, $lon, $appid);
						$data = file_get_contents($url);
						echo $data;
					}
					break;
				default:
					break;
			}
			return 1;
		}
	}
	return 0;
}

if (!method()) {
	// testing by command line. Use it to encode encrypted app id and save it to .config.php.
	// only encoded app id will be used while clear app id can be removed
	$options = getopt("s::e:d:", ["show::", "encode:", "decode"]);
	if (isset($options['s']) || isset($options['show'])) {
		$decoded = decode(APPID_ENC);
		echo $decoded . "\n";
	}
	elseif (isset($options['e']) || isset($options['encode'])) {
		$to_enc = $options['e'] ?? $options['encode'];
		$encoded = encode($to_enc);
		echo $encoded;
	}
	elseif (isset($options['d']) || isset($options['decode'])) {
		$to_dec = $options['d'] ?? $options['decode'];
		$decoded = decode($to_dec);
		echo $decoded;
	}
}
?>