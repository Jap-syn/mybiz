<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
/**
 * Service definition for MyBusiness (v4).
 *
 * <p>
 * The Google My Business API provides an interface for managing business
 * location information on Google.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/my-business/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_V1_MyBusinessLocation extends Google_Service
{
  public $v1_accounts;
  // public $v1_accounts_locations;
  /**
   * Constructs the internal representation of the MyBusiness service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://mybusinessbusinessinformation.googleapis.com/';
    //$this->rootUrl = 'https://business.googleapis.com/';  
    $this->servicePath = '';
    $this->batchPath = 'batch';
    $this->version = 'v1';
    $this->serviceName = 'mybusiness';
    $this->locations = new Google_Service_V1_MyBusiness_Locations_Resource(
      $this,
      $this->serviceName,
      'locations',
      [
        'methods' => [
          'get' => [
            'path' => 'v1/{+name}',
            'httpMethod' => 'GET',
            'parameters' => [
              'name' => [
                'location' => 'path',
                'type' => 'string',
                'required' => true,
              ],
              'readMask' => [
                'location' => 'query',
                'type' => 'string',
              ],
            ],
          ]
        ]
      ]
    );
    $this->accounts_locations = new Google_Service_V1_MyBusiness_AccountsLocations_Resource(
      $this,
      $this->serviceName,
      'locations',
      [
        'methods' => [
          'list' => [
            'path' => 'v1/{+parent}/locations',
            'httpMethod' => 'GET',
            'parameters' => [
              'parent' => [
                'location' => 'path',
                'type' => 'string',
                'required' => true,
              ],
              'filter' => [
                'location' => 'query',
                'type' => 'string',
              ],
              'orderBy' => [
                'location' => 'query',
                'type' => 'string',
              ],
              'pageSize' => [
                'location' => 'query',
                'type' => 'integer',
              ],
              'pageToken' => [
                'location' => 'query',
                'type' => 'string',
              ],
              'readMask' => [
                'location' => 'query',
                'type' => 'string',
              ],
            ],
          ],
          'patch' => array(
            'path' => 'v1/{+name}',
            'httpMethod' => 'PATCH',
            'parameters' => array(
              'name' => array(
                'location' => 'path',
                'type' => 'string',
                'required' => true,
              ),
              'updateMask' => array(
                'location' => 'query',
                'type' => 'string',
              ),
              'validateOnly' => array(
                'location' => 'query',
                'type' => 'boolean',
              )
            ),
          )
        ]
      ]
    );
  }
}
class Google_Service_V1_MyBusiness_AccountsLocations_Resource extends Google_Service_Resource
{
  public function listAccountsLocations($parent, $optParams = array())
  {
    try {
      $params = array('parent' => $parent);
      $params = array_merge($params, array('readMask' => "name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems"));

      return $this->call('list', array($params), "Google_Service_V1_MyBusiness_ListLocationsResponse");
    } catch (Exception $e) {
      dd($e);
    }
  }
  public function patch($name, Google_Service_MyBusiness_Location $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params,  array('readMask' => "name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems"));
    return $this->call('patch', array($params), "Google_Service_MyBusiness_Location");
  }
}
class Google_Service_V1_MyBusiness_Locations_Resource extends Google_Service_Resource
{

  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, array('readMask' => "name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems"));
    return $this->call('get', array($params), "Google_Service_V1_MyBusiness_Location");
  }
}
class Google_Service_V1_MyBusiness_Location extends Google_Collection
{
  protected $collection_key = 'priceLists';
  protected $internal_gapi_mappings = array();
  protected $adWordsLocationExtensionsType = 'Google_Service_MyBusiness_AdWordsLocationExtensions';
  protected $adWordsLocationExtensionsDataType = '';
  protected $additionalCategoriesType = 'Google_Service_MyBusiness_Category';
  protected $additionalCategoriesDataType = 'array';
  public $additionalPhones;
  protected $addressType = 'Google_Service_MyBusiness_PostalAddress';
  protected $addressDataType = '';
  protected $attributesType = 'Google_Service_MyBusiness_Attribute';
  protected $attributesDataType = 'array';
  public $labels;
  public $languageCode;
  protected $latlngType = 'Google_Service_MyBusiness_LatLng';
  protected $latlngDataType = '';
  protected $locationKeyType = 'Google_Service_MyBusiness_LocationKey';
  protected $locationKeyDataType = '';
  public $locationName;
  protected $locationStateType = 'Google_Service_MyBusiness_LocationState';
  protected $locationStateDataType = '';
  protected $metadataType = 'Google_Service_MyBusiness_Metadata';
  protected $metadataDataType = '';
  public $name;
  protected $openInfoType = 'Google_Service_MyBusiness_OpenInfo';
  protected $openInfoDataType = '';
  protected $priceListsType = 'Google_Service_MyBusiness_PriceList';
  protected $priceListsDataType = 'array';
  protected $primaryCategoryType = 'Google_Service_MyBusiness_Category';
  protected $primaryCategoryDataType = '';
  public $primaryPhone;
  protected $profileType = 'Google_Service_MyBusiness_Profile';
  protected $profileDataType = '';
  protected $regularHoursType = 'Google_Service_MyBusiness_BusinessHours';
  protected $regularHoursDataType = '';
  protected $relationshipDataType = 'Google_Service_MyBusiness_RelationshipData';
  protected $relationshipDataDataType = '';
  protected $serviceAreaType = 'Google_Service_MyBusiness_ServiceAreaBusiness';
  protected $serviceAreaDataType = '';
  protected $specialHoursType = 'Google_Service_MyBusiness_SpecialHours';
  protected $specialHoursDataType = '';
  public $storeCode;
  public $websiteUrl;
  public function setAdWordsLocationExtensions(Google_Service_MyBusiness_AdWordsLocationExtensions $adWordsLocationExtensions)
  {
    $this->adWordsLocationExtensions = $adWordsLocationExtensions;
  }
  public function getAdWordsLocationExtensions()
  {
    return $this->adWordsLocationExtensions;
  }
  public function setAdditionalCategories($additionalCategories)
  {
    $this->additionalCategories = $additionalCategories;
  }
  public function getAdditionalCategories()
  {
    return $this->additionalCategories;
  }
  public function setAdditionalPhones($additionalPhones)
  {
    $this->additionalPhones = $additionalPhones;
  }
  public function getAdditionalPhones()
  {
    return $this->additionalPhones;
  }
  public function setAddress(Google_Service_MyBusiness_PostalAddress $address)
  {
    $this->address = $address;
  }
  public function getAddress()
  {
    return $this->address;
  }
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  public function getAttributes()
  {
    return $this->attributes;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setLanguageCode($languageCode)
  {
    $this->languageCode = $languageCode;
  }
  public function getLanguageCode()
  {
    return $this->languageCode;
  }
  public function setLatlng(Google_Service_MyBusiness_LatLng $latlng)
  {
    $this->latlng = $latlng;
  }
  public function getLatlng()
  {
    return $this->latlng;
  }
  public function setLocationKey(Google_Service_MyBusiness_LocationKey $locationKey)
  {
    $this->locationKey = $locationKey;
  }
  public function getLocationKey()
  {
    return $this->locationKey;
  }
  public function setLocationName($locationName)
  {
    $this->locationName = $locationName;
  }
  public function getLocationName()
  {
    return $this->locationName;
  }
  public function setLocationState(Google_Service_MyBusiness_LocationState $locationState)
  {
    $this->locationState = $locationState;
  }
  public function getLocationState()
  {
    return $this->locationState;
  }
  public function setMetadata(Google_Service_MyBusiness_Metadata $metadata)
  {
    $this->metadata = $metadata;
  }
  public function getMetadata()
  {
    return $this->metadata;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOpenInfo(Google_Service_MyBusiness_OpenInfo $openInfo)
  {
    $this->openInfo = $openInfo;
  }
  public function getOpenInfo()
  {
    return $this->openInfo;
  }
  public function setPriceLists($priceLists)
  {
    $this->priceLists = $priceLists;
  }
  public function getPriceLists()
  {
    return $this->priceLists;
  }
  public function setPrimaryCategory(Google_Service_MyBusiness_Category $primaryCategory)
  {
    $this->primaryCategory = $primaryCategory;
  }
  public function getPrimaryCategory()
  {
    return $this->primaryCategory;
  }
  public function setPrimaryPhone($primaryPhone)
  {
    $this->primaryPhone = $primaryPhone;
  }
  public function getPrimaryPhone()
  {
    return $this->primaryPhone;
  }
  public function setProfile(Google_Service_MyBusiness_Profile $profile)
  {
    $this->profile = $profile;
  }
  public function getProfile()
  {
    return $this->profile;
  }
  public function setRegularHours(Google_Service_MyBusiness_BusinessHours $regularHours)
  {
    $this->regularHours = $regularHours;
  }
  public function getRegularHours()
  {
    return $this->regularHours;
  }
  public function setRelationshipData(Google_Service_MyBusiness_RelationshipData $relationshipData)
  {
    $this->relationshipData = $relationshipData;
  }
  public function getRelationshipData()
  {
    return $this->relationshipData;
  }
  public function setServiceArea(Google_Service_MyBusiness_ServiceAreaBusiness $serviceArea)
  {
    $this->serviceArea = $serviceArea;
  }
  public function getServiceArea()
  {
    return $this->serviceArea;
  }
  public function setSpecialHours(Google_Service_MyBusiness_SpecialHours $specialHours)
  {
    $this->specialHours = $specialHours;
  }
  public function getSpecialHours()
  {
    return $this->specialHours;
  }
  public function setStoreCode($storeCode)
  {
    $this->storeCode = $storeCode;
  }
  public function getStoreCode()
  {
    return $this->storeCode;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}
class Google_Service_V1_MyBusiness_AdWordsLocationExtensions extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $adPhone;
  public function setAdPhone($adPhone)
  {
    $this->adPhone = $adPhone;
  }
  public function getAdPhone()
  {
    return $this->adPhone;
  }
}
class Google_Service_V1_MyBusiness_LatLng extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $latitude;
  public $longitude;
  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;
  }
  public function getLatitude()
  {
    return $this->latitude;
  }
  public function setLongitude($longitude)
  {
    $this->longitude = $longitude;
  }
  public function getLongitude()
  {
    return $this->longitude;
  }
}
class Google_Service_V1_MyBusiness_LocationKey extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $explicitNoPlaceId;
  public $placeId;
  public $plusPageId;
  public $requestId;
  public function setExplicitNoPlaceId($explicitNoPlaceId)
  {
    $this->explicitNoPlaceId = $explicitNoPlaceId;
  }
  public function getExplicitNoPlaceId()
  {
    return $this->explicitNoPlaceId;
  }
  public function setPlaceId($placeId)
  {
    $this->placeId = $placeId;
  }
  public function getPlaceId()
  {
    return $this->placeId;
  }
  public function setPlusPageId($plusPageId)
  {
    $this->plusPageId = $plusPageId;
  }
  public function getPlusPageId()
  {
    return $this->plusPageId;
  }
  public function setRequestId($requestId)
  {
    $this->requestId = $requestId;
  }
  public function getRequestId()
  {
    return $this->requestId;
  }
}
class Google_Service_V1_MyBusiness_LocationState extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $canDelete;
  public $canModifyServiceList;
  public $canUpdate;
  public $hasPendingEdits;
  public $hasPendingVerification;
  public $isDisabled;
  public $isDisconnected;
  public $isDuplicate;
  public $isGoogleUpdated;
  public $isLocalPostApiDisabled;
  public $isPendingReview;
  public $isPublished;
  public $isSuspended;
  public $isVerified;
  public $needsReverification;
  public function setCanDelete($canDelete)
  {
    $this->canDelete = $canDelete;
  }
  public function getCanDelete()
  {
    return $this->canDelete;
  }
  public function setCanModifyServiceList($canModifyServiceList)
  {
    $this->canModifyServiceList = $canModifyServiceList;
  }
  public function getCanModifyServiceList()
  {
    return $this->canModifyServiceList;
  }
  public function setCanUpdate($canUpdate)
  {
    $this->canUpdate = $canUpdate;
  }
  public function getCanUpdate()
  {
    return $this->canUpdate;
  }
  public function setHasPendingEdits($hasPendingEdits)
  {
    $this->hasPendingEdits = $hasPendingEdits;
  }
  public function getHasPendingEdits()
  {
    return $this->hasPendingEdits;
  }
  public function setHasPendingVerification($hasPendingVerification)
  {
    $this->hasPendingVerification = $hasPendingVerification;
  }
  public function getHasPendingVerification()
  {
    return $this->hasPendingVerification;
  }
  public function setIsDisabled($isDisabled)
  {
    $this->isDisabled = $isDisabled;
  }
  public function getIsDisabled()
  {
    return $this->isDisabled;
  }
  public function setIsDisconnected($isDisconnected)
  {
    $this->isDisconnected = $isDisconnected;
  }
  public function getIsDisconnected()
  {
    return $this->isDisconnected;
  }
  public function setIsDuplicate($isDuplicate)
  {
    $this->isDuplicate = $isDuplicate;
  }
  public function getIsDuplicate()
  {
    return $this->isDuplicate;
  }
  public function setIsGoogleUpdated($isGoogleUpdated)
  {
    $this->isGoogleUpdated = $isGoogleUpdated;
  }
  public function getIsGoogleUpdated()
  {
    return $this->isGoogleUpdated;
  }
  public function setIsLocalPostApiDisabled($isLocalPostApiDisabled)
  {
    $this->isLocalPostApiDisabled = $isLocalPostApiDisabled;
  }
  public function getIsLocalPostApiDisabled()
  {
    return $this->isLocalPostApiDisabled;
  }
  public function setIsPendingReview($isPendingReview)
  {
    $this->isPendingReview = $isPendingReview;
  }
  public function getIsPendingReview()
  {
    return $this->isPendingReview;
  }
  public function setIsPublished($isPublished)
  {
    $this->isPublished = $isPublished;
  }
  public function getIsPublished()
  {
    return $this->isPublished;
  }
  public function setIsSuspended($isSuspended)
  {
    $this->isSuspended = $isSuspended;
  }
  public function getIsSuspended()
  {
    return $this->isSuspended;
  }
  public function setIsVerified($isVerified)
  {
    $this->isVerified = $isVerified;
  }
  public function getIsVerified()
  {
    return $this->isVerified;
  }
  public function setNeedsReverification($needsReverification)
  {
    $this->needsReverification = $needsReverification;
  }
  public function getNeedsReverification()
  {
    return $this->needsReverification;
  }
}
class Google_Service_V1_MyBusiness_Metadata extends Google_Model
{
  protected $internal_gapi_mappings = array();
  protected $duplicateType = 'Google_Service_MyBusiness_Duplicate';
  protected $duplicateDataType = '';
  public $mapsUrl;
  public $newReviewUrl;
  public function setDuplicate(Google_Service_MyBusiness_Duplicate $duplicate)
  {
    $this->duplicate = $duplicate;
  }
  public function getDuplicate()
  {
    return $this->duplicate;
  }
  public function setMapsUrl($mapsUrl)
  {
    $this->mapsUrl = $mapsUrl;
  }
  public function getMapsUrl()
  {
    return $this->mapsUrl;
  }
  public function setNewReviewUrl($newReviewUrl)
  {
    $this->newReviewUrl = $newReviewUrl;
  }
  public function getNewReviewUrl()
  {
    return $this->newReviewUrl;
  }
}
class Google_Service_V1_MyBusiness_Duplicate extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $access;
  public $locationName;
  public $placeId;
  public function setAccess($access)
  {
    $this->access = $access;
  }
  public function getAccess()
  {
    return $this->access;
  }
  public function setLocationName($locationName)
  {
    $this->locationName = $locationName;
  }
  public function getLocationName()
  {
    return $this->locationName;
  }
  public function setPlaceId($placeId)
  {
    $this->placeId = $placeId;
  }
  public function getPlaceId()
  {
    return $this->placeId;
  }
}
class Google_Service_V1_MyBusiness_OpenInfo extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $canReopen;
  protected $openingDateType = 'Google_Service_MyBusiness_Date';
  protected $openingDateDataType = '';
  public $status;
  public function setCanReopen($canReopen)
  {
    $this->canReopen = $canReopen;
  }
  public function getCanReopen()
  {
    return $this->canReopen;
  }
  public function setOpeningDate(Google_Service_MyBusiness_Date $openingDate)
  {
    $this->openingDate = $openingDate;
  }
  public function getOpeningDate()
  {
    return $this->openingDate;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}
class Google_Service_V1_MyBusiness_Date extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $day;
  public $month;
  public $year;
  public function setDay($day)
  {
    $this->day = $day;
  }
  public function getDay()
  {
    return $this->day;
  }
  public function setMonth($month)
  {
    $this->month = $month;
  }
  public function getMonth()
  {
    return $this->month;
  }
  public function setYear($year)
  {
    $this->year = $year;
  }
  public function getYear()
  {
    return $this->year;
  }
}
class Google_Service_V1_MyBusiness_Category extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $categoryId;
  public $displayName;
  public function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  public function getCategoryId()
  {
    return $this->categoryId;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
}
class Google_Service_V1_MyBusiness_Profile extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $description;
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
}
class Google_Service_V1_MyBusiness_BusinessHours extends Google_Collection
{
  protected $collection_key = 'periods';
  protected $internal_gapi_mappings = array();
  protected $periodsType = 'Google_Service_MyBusiness_TimePeriod';
  protected $periodsDataType = 'array';
  public function setPeriods($periods)
  {
    $this->periods = $periods;
  }
  public function getPeriods()
  {
    return $this->periods;
  }
}
class Google_Service_V1_MyBusiness_RelationshipData extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $parentChain;
  public function setParentChain($parentChain)
  {
    $this->parentChain = $parentChain;
  }
  public function getParentChain()
  {
    return $this->parentChain;
  }
}
class Google_Service_V1_MyBusiness_ServiceAreaBusiness extends Google_Model
{
  protected $internal_gapi_mappings = array();
  public $businessType;
  protected $placesType = 'Google_Service_MyBusiness_Places';
  protected $placesDataType = '';
  protected $radiusType = 'Google_Service_MyBusiness_PointRadius';
  protected $radiusDataType = '';
  public function setBusinessType($businessType)
  {
    $this->businessType = $businessType;
  }
  public function getBusinessType()
  {
    return $this->businessType;
  }
  public function setPlaces(Google_Service_MyBusiness_Places $places)
  {
    $this->places = $places;
  }
  public function getPlaces()
  {
    return $this->places;
  }
  public function setRadius(Google_Service_MyBusiness_PointRadius $radius)
  {
    $this->radius = $radius;
  }
  public function getRadius()
  {
    return $this->radius;
  }
}
class Google_Service_V1_MyBusiness_SpecialHours extends Google_Collection
{
  protected $collection_key = 'specialHourPeriods';
  protected $internal_gapi_mappings = array();
  protected $specialHourPeriodsType = 'Google_Service_MyBusiness_SpecialHourPeriod';
  protected $specialHourPeriodsDataType = 'array';
  public function setSpecialHourPeriods($specialHourPeriods)
  {
    $this->specialHourPeriods = $specialHourPeriods;
  }
  public function getSpecialHourPeriods()
  {
    return $this->specialHourPeriods;
  }
}
class Google_Service_V1_MyBusiness_ListLocationsResponse extends Google_Collection
{
  protected $collection_key = 'locations';
  protected $internal_gapi_mappings = array();
  protected $locationsType = 'Google_Service_V1_MyBusiness_Location';
  protected $locationsDataType = 'array';
  public $nextPageToken;
  public $totalSize;
  public function setLocations($locations)
  {
    $this->locations = $locations;
  }
  public function getLocations()
  {
    return $this->locations;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTotalSize($totalSize)
  {
    $this->totalSize = $totalSize;
  }
  public function getTotalSize()
  {
    return $this->totalSize;
  }
}