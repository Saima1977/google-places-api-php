google-places-api-php
=====================

Google Places API for PHP.

## USAGE ##
```php
<?php
$apiKey = "YourAPIKeyHere";
$googlePlaces = new GooglePlaces($apiKey);

$textResults = $googlePlaces->doTextSearch("hotels in NewYork");
$nearByResults = $googlePlaces->doNearBySearch("34.002001,32.151155", 5000);
?>
```
