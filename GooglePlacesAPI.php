<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class GooglePlacesAPI
{
	private $_apiUrl = "https://maps.googleapis.com/maps/api/place";	// Google Places API Link
	private $_apiResultFormat = "json";									// Defines the format of the results, default is "JSON"
	private $_apiSearchType = "";										// Search type, could be Nearby, Radar, Text, Details or Next page.

	// Mandatory fields
	private $_apiLanguage = "en";										// Search results language, default is english.
	private $_apiKey = "";												// Google Places API Key
	private $_fieldSensor = "false";									// Indicates whether or not the Place request came from a device using a location sensor (e.g. a GPS) to determine the location sent in this request.
	private $_fieldQuery = "";											// "query" field, used for Text Search Requests
	private $_fieldLocation = "";										// "location" fields, used for Radar Search Requests and Nearby Search Requests
	private $_fieldRadius = 0;										// Defines the distance (in meters) within which to return Place results. The maximum allowed radius is 50 000 meters.

	// Special Fields
	private $_fieldNextPageToken = "";									// Google Places API returns only 20 results in 3 pages. In each result "next_page_token" will be specified in order to get the next page.
	private $_fieldDetailsReference = "";								// Used for extra details on a result. 

	// Optional Fields
	private $_extraParams = array();									// Extra params that you wish to send, like "types", name", "keyword" and etc. You can see list of this params here: https://developers.google.com/places/documentation/search

    /**
     * constructor - Creates and initalize the Google Places API object.
     *
     * @param $apiKey - Your API Key.
     */
	public function __construct($apiKey)
	{
		// Support for CodeIgniter framework default initalization for libraries.
		if (is_array($apiKey))
			$apiKey = $apiKey["apiKey"];

		$this->_apiKey = $apiKey;
	}

    /**
     * doNearbySearch - Makes a Nearby Search
     *
     * @param location - "location" field
     * @param radius - "radius" field
     */
	public function doNearbySearch($location, $radius)
	{
		$this->_apiSearchType = GooglePlaceAPISearchType::NEARBY;
		$this->setLocation($location);
		$this->setRadius($radius);

		return $this->doAPICall();
	}

    /**
     * doRadarSearch - Makes a Radar Search
     *
     * @param location - "location" field
     * @param radius - "radius" field
     */
	public function doRadarSearch($location, $radius)
	{
		$this->_apiSearchType = GooglePlaceAPISearchType::RADAR;
		$this->setLocation($location);
		$this->setRadius($radius);

		return $this->doAPICall();
	}

    /**
     * doTextSearch - Makes a Text Search
     *
     * @param query - "query" field, Example: "hotels in New York"
     */
	public function doTextSearch($query)
	{
		$this->_apiSearchType = GooglePlaceAPISearchType::TEXT;
		$this->setQuery($query);

		return $this->doAPICall();
	}

    /**
     * doNextPageSearch - Gets the next results page
     *
     * @param next_page_token - "next_page_token" field, that comes from the previous page response.
     */
	public function doNextPageSearch($next_page_token)
	{
		$this->_apiSearchType = GooglePlaceAPISearchType::NEXT_PAGE;
		$this->setNextPageToken($next_page_token);

		return $this->doAPICall();
	}

    /**
     * doDetailsSearch - Gets extra details by the place reference.
     *
     * @param detailsReference - "reference" field from search results response.
     */
	public function doDetailsSearch($detailsReference)
	{
		$this->_apiSearchType = GooglePlaceAPISearchType::DETAILS;
		$this->setDetailsReference($detailsReference);

		return $this->doAPICall();
	}


    /**
     * doAPICall - Makes a Google Places API call
     *
     */
	private function doAPICall()
	{
		$requestParams = $this->createRequestParams();
		$requestURL = $this->createRequestURL();
		$response = $this->doHTTPGetRequest($requestURL . $requestParams);

		if ($this->_apiResultFormat == "json")
			$response = json_decode($response);

		return $response; 
	}

    /**
     * doHTTPGetRequest - Makes the GET request to the server.
     *
     * @return url - URL & params.
     */
	private function doHTTPGetRequest($url)
	{
		return file_get_contents($url);
	}

    /**
     * createRequestURL - Creates the request URL.
     *
     */
	private function createRequestURL()
	{
		return $this->_apiUrl . "/" . $this->_apiSearchType . "/" . $this->_apiResultFormat . "?";
	}

    /**
     * createRequestParams - Creates the request GET params.
     *
     * @return format - HTTP params request format.
     */
	private function createRequestParams()
	{
		$params = "key=" . $this->_apiKey . "&sensor=" .  $this->_fieldSensor;

		switch ($this->_apiSearchType)
		{
			case GooglePlaceAPISearchType::NEARBY:
			case GooglePlaceAPISearchType::RADAR:
			{
				$params .= "&location=" . $this->_fieldLocation . "&radius=" . $this->_fieldRadius;

				break;
			}

			case GooglePlaceAPISearchType::TEXT:
			{
				$params .= "&query=" . $this->_fieldQuery;

				break;
			}

			case GooglePlaceAPISearchType::DETAILS:
			{
				$params .= "&reference=" . $this->_fieldDetailsReference;

				break;
			}

			case GooglePlaceAPISearchType::NEXT_PAGE:
			{
				$params .= "&pagetoken=" . $this->_fieldNextPageToken;
				$this->_apiSearchType = GooglePlaceAPISearchType::TEXT;

				break;
			}

			default:
			{
				break;
			}
		}

		foreach ($this->_extraParams as $key => $value) 
		{
			$params .= "&" . $key . "=" . $value;
		}

		return $params;
	}

    /**
     * addExtraParam - Adds an optional param to the request.
     *
     * @param paramName - Name of the param.
     * @param paramValue - Value of the param.
     */
	public function addExtraParam($paramName, $paramValue)
	{
		$this->_extraParams['" . $paramName ."'] = $paramValue;
	}

    /**
     * setNextPageToken - Sets the value of "next_page_token" field.
     *
     * @param next_page_token - value of "next_page_token" from previous response
     */
	public function setNextPageToken($next_page_token)
	{
		$this->_fieldNextPageToken = $next_page_token;
	}

    /**
     * setDetailsReference - Sets the value of "reference" field.
     *
     * @param next_page_token - value of "reference" from previous response
     */
	public function setDetailsReference($detailsReference)
	{
		$this->_fieldDetailsReference = $detailsReference;
	}

    /**
     * setSensor - Sets the value of "sensor" field.
     *
     * @param sensor - "sensor" field new value, must be "true" of "false"
     */
	public function setSensor($sensor)
	{
		$this->_fieldSensor = $sensor;
	}

    /**
     * setLanguage - Sets the value of "language" field.
     *
     * @param language - "language" field new value, must be from the list of supported languages: https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
     */
	public function setLanguage($language)
	{
		$this->_apiLanguage = $language;
	}

    /**
     * setLocation - Sets the value of "location" field.
     *
     * @param location - "location" field new value, must be a Geo point seperated with ","
     */
	public function setLocation($location)
	{
		$this->_fieldLocation = $location;
	}

    /**
     * setRadius - Sets the value of "radius" field.
     *
     * @param radius - "radius" field new value, must be a positive number, max value is 50000.
     */
	public function setRadius($radius)
	{
		$this->_fieldRadius = $radius;
	}

    /**
     * setQuery - Sets the value of "query" field.
     *
     * @param query - "query" field new value
     */
	public function setQuery($query)
	{
		$this->_fieldQuery = $query;
	}

    /**
     * setResultsFormat - Sets the format of the results.
     *
     * @param format - Format type, must be "xml" or "json"
     */
	public function setResultsFormat($format)
	{
		$this->_apiResultFormat = $format;
	}
}

class GooglePlaceAPISearchType
{
    const NEARBY = 'nearbysearch';
    const TEXT = 'textsearch';
	const RADAR = 'radarsearch';
	const NEXT_PAGE = 'next_page';
    const DETAILS = 'details';
}