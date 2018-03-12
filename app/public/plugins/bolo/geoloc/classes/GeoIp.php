<?php namespace Bolo\Geoloc\Classes;

use GeoIp2\WebService\Client;

class GeoIp
{
    /**
     * Backward compatibility
     *
     * @param $ip
     * @return array
     */
    public static function getLocation($ip)
    {
        $record = self::getRawLocation($ip);

        if($record->isError){
            return self::$default_location;
        }

        $location = array(
            "ip"			=> $ip,
            "isoCode" 		=> $record->country->isoCode,
            "country" 		=> $record->country->name,
            "city" 			=> $record->city->name,
            "state" 		=> $record->mostSpecificSubdivision->isoCode,
            "postal_code"   => $record->postal->code,
            "lat" 			=> $record->location->latitude,
            "lon" 			=> $record->location->longitude,
            "timezone" 		=> $record->location->timeZone,
            "continent"		=> $record->continent->code,
            "default"       => false
        );

        return $location;
    }

    /**
     * @param $ip
     * @return \GeoIp2\Model\City
     */
    public static function getRawLocation($ip){

        // debug china ip
        //$ip = '180.76.3.151';
        //$ip = '61.184.192.42';

        // debug hong kong ip
        //$ip = '123.1.128.1';

        // debug philippines ip
        //$ip = '119.92.1.1';

        // debug Taiwan ip
        //$ip = '49.216.1.1';

        // debug Macao ip
        //$ip = '125.31.1.1';

        //England UK
        //$ip = '104.238.169.18';

        if(!self::checkIp($ip))
            return (object)['isError' => true];

        return \Cache::remember('geoip_'.$ip, 24 * 60 * 7, function() use($ip){
            $client = new Client(\Config::get('maxmind.id'), \Config::get('maxmind.key'));
            try {
                $record = $client->city($ip);
                $record->isError = false;
            }
            catch(\GeoIp2\Exception\AddressNotFoundException $e)
            {
                return (object)['isError' => true];
            }
            catch(\Exception $e){
                \Log::warning("Maxmind error: ".$e->getMessage());
                return (object)['isError' => true];
            }

            return $record;
        });
    }

    private static function getClientIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        }
        else if(getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        }
        else if(isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        else {
            $ipaddress = '127.0.0.0';
        }

        return $ipaddress;
    }

    protected static $default_location = array (
        "ip" 			=> "127.0.0.0",
        "isoCode" 		=> "US",
        "country" 		=> "United States",
        "city" 			=> "New Haven",
        "state" 		=> "CT",
        "postal_code"   => "06510",
        "lat" 			=> 41.31,
        "lon" 			=> -72.92,
        "timezone" 		=> "America/New_York",
        "continent"		=> "NA",
        "default"       => true
    );


    protected static $reserved_ips = array (
        array('0.0.0.0','2.255.255.255'),
        array('10.0.0.0','10.255.255.255'),
        array('127.0.0.0','127.255.255.255'),
        array('169.254.0.0','169.254.255.255'),
        array('172.16.0.0','172.31.255.255'),
        array('192.0.2.0','192.0.2.255'),
        array('192.168.0.0','192.168.255.255'),
        array('255.255.255.0','255.255.255.255')
    );


    private static function checkIp($ip)
    {
        $longip = ip2long($ip);

        if (!empty($ip)) {

            foreach (self::$reserved_ips as $r)
            {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);

                if ($longip >= $min && $longip <= $max) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

}
