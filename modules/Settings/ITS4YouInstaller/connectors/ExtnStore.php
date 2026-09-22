<?php
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

include_once dirname(__FILE__) . '/../../ExtensionStore/libraries/NetClient.php';

class Settings_ITS4YouInstaller_ExtnStore_Connector
{
    /**
     * @var
     */
    protected $url;

    /**
     * @var string
     */
    protected $identifier_name = 'its4you_installer';

    /**
     * Settings_ITS4YouInstaller_ExtnStore_Connector constructor.
     * @param $url
     */
    protected function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function getInstance($url)
    {
        static $singletons = null;
        if ($singletons === null) {
            $singletons = array();
        }
        if (!isset($singletons[$url])) {
            $singletons[$url] = new self($url);
        }

        return $singletons[$url];
    }

    /**
     * @return string
     */
    public function getSessionIdentifier()
    {
        return $this->identifier_name;
    }

    /**
     * @param null $id
     * @param string $type
     * @return array
     */
    public function getListings($id = null, $type = 'Extension')
    {
        $config = array(
            'type' => $type,
            'l' => Settings_ITS4YouInstaller_License_Model::getLicenseKeys(),
            'id' => $id
        );
        $config = $this->getDefaultConfig($config);

        try {
            $response = $this->api('/app/listings/v1', 'GET', ['q' => Zend_Json::encode($config)]);

            return array('success' => true, 'response' => $response);
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }

    public function getDefaultConfig($config)
    {
        $config['vtiger_version'] = Vtiger_Version::current();
        $config['v'] = vglobal('vtiger_current_version');
        $config['i'] = $_SERVER['REMOTE_ADDR'];

        return $config;
    }

    /**
     * @param $uri
     * @param $method
     * @param $params
     * @param $auth
     * @return array|null
     * @throws Exception
     */
    protected function api($uri, $method, $params)
    {
        $fn = ($method == "GET" || $method == "DLD") ? "doGet" : "doPost";

        if ($method == "PUT") {
            $fn = "doPut";
        }

        $client = $this->getNetClientInstance($method, $uri);
        $content = $client->$fn($params);
        $response = $content['response'];
        $status = $content['status'];

        if (($status != 200)) {
            throw new Exception(isset($content['errorMessage']) ? $content['errorMessage'] : $response);
        }

        if ($method == "DLD") {
            return $response;
        } else {
            $response = preg_replace('/[\000-\031\200-\377]/', '', $response);
            $json = Zend_Json::decode($response);

            if ($json) {
                if ($json['success']) {
                    $json_result = $json['result'];
                    if (in_array($json_result, ['vterr', 'supperr']) || $json['result'] == 'false') {
                        $error = vtranslate('LBL_UNAUTHORIZED', 'Settings:ExtensionStore');
                        throw new Exception($error);
                    }

                    return $json_result;
                } else {
                    throw new Exception($json['error']['message']);
                }
            }
        }

        return null;
    }

    /**
     * @param $method
     * @param $uri
     * @return Settings_ExtensionStore_NetClient
     */
    protected function getNetClientInstance($method, $uri)
    {
        return new Settings_ExtensionStore_NetClient($method == "DLD" ? $uri : ($this->url . $uri));
    }

    /**
     * @param $config
     * @return array
     */
    public function getLicenses($config)
    {
        try {
            $config = $this->getDefaultConfig($config);
            $response = $this->api('/app/licenses/v1', 'GET', ['q' => Zend_Json::encode($config)]);

            return array('success' => true, 'response' => $response);
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }

    /**
     * @param $downloadurl
     * @return array
     */
    public function download($downloadurl)
    {
        try {
            $response = $this->api($downloadurl, 'DLD', null);

            return array('success' => true, 'response' => $response);
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }

    public function getChangeLog($data)
    {
        try {
            $q = array('m' => $data['moduleName'], 'cv' => $data['currentVersion'], 'uv' => $data['updateVersion']);
            $url = $data['url'] . '/app/changelog/v1?q=' . Zend_Json::encode($q);

            return $this->api($url, 'DLD', '');
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function getHostingInfo($data)
    {
        try {
            return $this->api('/app/hosting/v1', 'GET', ['q' => Zend_Json::encode($data)]);
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateUsedCount($config)
    {
        try {
            $q = array(
                'li' => $config['license_id'],
                'uc' => $config['used_count'],
                'uct' => $config['used_count_type']
            );

            return $this->api('/app/license_update/v1/', 'GET', ['q' => Zend_Json::encode($q)]);
        } catch (Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }
    }
}
