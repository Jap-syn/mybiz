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
class Google_Service_V1_MyBusinessAccount extends Google_Service
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
    $this->rootUrl = 'https://mybusinessaccountmanagement.googleapis.com/';
//$this->rootUrl = 'https://business.googleapis.com/';  
    $this->servicePath = '';
    $this->batchPath = 'batch';
    $this->version = 'v1';
    $this->serviceName = 'mybusiness';
    $this->accounts = new Google_Service_V1_MyBusiness_Accounts_Resource(
      $this,
      $this->serviceName,
      'accounts',
      array(
        'methods' => array(
          'get' => array(
            'path' => 'v1/{+name}',
            'httpMethod' => 'GET',
            'parameters' => array(
              'name' => array(
                'location' => 'path',
                'type' => 'string',
                'required' => true,
              ),
            ),
          ), 'list' => array(
            'path' => 'v1/accounts',
            'httpMethod' => 'GET',
            'parameters' => array(
              'parentAccount' => array(
                'location' => 'query',
                'type' => 'string',
              ),
              'pageSize' => array(
                'location' => 'query',
                'type' => 'integer',
              ),
              'pageToken' => array(
                'location' => 'query',
                'type' => 'string',
              ),

              'filter' => array(
                'location' => 'query',
                'type' => 'string',
              ),
            ),
          )
        )
      )
    );
  }
}

class Google_Service_V1_MyBusiness_Accounts_Resource extends Google_Service_Resource
{

  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_V1_MyBusiness_Account");
  }

  public function listAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_V1_MyBusiness_ListAccountsResponse");
  }
}



class Google_Service_V1_MyBusiness_Account extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountName;
  public $accountNumber;
  public $name;
  protected $organizationInfoType = 'Google_Service_V1_MyBusiness_OrganizationInfo';
  protected $organizationInfoDataType = '';
  public $permissionLevel;
  public $role;
  protected $stateType = 'Google_Service_MyBusiness_AccountState';
  protected $stateDataType = '';
  public $type;


  public function setAccountName($accountName)
  {
    $this->accountName = $accountName;
  }
  public function getAccountName()
  {
    return $this->accountName;
  }
  public function setAccountNumber($accountNumber)
  {
    $this->accountNumber = $accountNumber;
  }
  public function getAccountNumber()
  {
    return $this->accountNumber;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOrganizationInfo(Google_Service_V1_MyBusiness_OrganizationInfo $organizationInfo)
  {
    $this->organizationInfo = $organizationInfo;
  }
  public function getOrganizationInfo()
  {
    return $this->organizationInfo;
  }
  public function setPermissionLevel($permissionLevel)
  {
    $this->permissionLevel = $permissionLevel;
  }
  public function getPermissionLevel()
  {
    return $this->permissionLevel;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
  }
  public function setState(Google_Service_V1_MyBusiness_AccountState   $state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}
class Google_Service_V1_MyBusiness_OrganizationInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $phoneNumber;
  protected $postalAddressType = 'Google_Service_V1_MyBusiness_PostalAddress';
  protected $postalAddressDataType = '';
  public $registeredDomain;


  public function setPhoneNumber($phoneNumber)
  {
    $this->phoneNumber = $phoneNumber;
  }
  public function getPhoneNumber()
  {
    return $this->phoneNumber;
  }
  public function setPostalAddress(Google_Service_V1_MyBusiness_PostalAddress $postalAddress)
  {
    $this->postalAddress = $postalAddress;
  }
  public function getPostalAddress()
  {
    return $this->postalAddress;
  }
  public function setRegisteredDomain($registeredDomain)
  {
    $this->registeredDomain = $registeredDomain;
  }
  public function getRegisteredDomain()
  {
    return $this->registeredDomain;
  }
}

class Google_Service_V1_MyBusiness_PostalAddress extends Google_Collection
{
  protected $collection_key = 'recipients';
  protected $internal_gapi_mappings = array(
  );
  public $addressLines;
  public $administrativeArea;
  public $languageCode;
  public $locality;
  public $organization;
  public $postalCode;
  public $recipients;
  public $regionCode;
  public $revision;
  public $sortingCode;
  public $sublocality;


  public function setAddressLines($addressLines)
  {
    $this->addressLines = $addressLines;
  }
  public function getAddressLines()
  {
    return $this->addressLines;
  }
  public function setAdministrativeArea($administrativeArea)
  {
    $this->administrativeArea = $administrativeArea;
  }
  public function getAdministrativeArea()
  {
    return $this->administrativeArea;
  }
  public function setLanguageCode($languageCode)
  {
    $this->languageCode = $languageCode;
  }
  public function getLanguageCode()
  {
    return $this->languageCode;
  }
  public function setLocality($locality)
  {
    $this->locality = $locality;
  }
  public function getLocality()
  {
    return $this->locality;
  }
  public function setOrganization($organization)
  {
    $this->organization = $organization;
  }
  public function getOrganization()
  {
    return $this->organization;
  }
  public function setPostalCode($postalCode)
  {
    $this->postalCode = $postalCode;
  }
  public function getPostalCode()
  {
    return $this->postalCode;
  }
  public function setRecipients($recipients)
  {
    $this->recipients = $recipients;
  }
  public function getRecipients()
  {
    return $this->recipients;
  }
  public function setRegionCode($regionCode)
  {
    $this->regionCode = $regionCode;
  }
  public function getRegionCode()
  {
    return $this->regionCode;
  }
  public function setRevision($revision)
  {
    $this->revision = $revision;
  }
  public function getRevision()
  {
    return $this->revision;
  }
  public function setSortingCode($sortingCode)
  {
    $this->sortingCode = $sortingCode;
  }
  public function getSortingCode()
  {
    return $this->sortingCode;
  }
  public function setSublocality($sublocality)
  {
    $this->sublocality = $sublocality;
  }
  public function getSublocality()
  {
    return $this->sublocality;
  }
}

class Google_Service_V1_MyBusiness_AccountState extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $status;


  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_V1_MyBusiness_ListAccountsResponse extends Google_Collection
{
  protected $collection_key = 'accounts';
  protected $internal_gapi_mappings = array(
  );
  protected $accountsType = 'Google_Service_V1_MyBusiness_Account';
  protected $accountsDataType = 'array';
  public $nextPageToken;


  public function setAccounts($accounts)
  {
    $this->accounts = $accounts;
  }
  public function getAccounts()
  {
    return $this->accounts;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

