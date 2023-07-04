<?php
/**
 * Manage OGC request.
 *
 * @author    3liz
 * @copyright 2015 3liz
 *
 * @see      http://3liz.com
 *
 * @license Mozilla Public License : http://www.mozilla.org/MPL/
 */
class processStatus
{
    protected static $profile = 'wpsProcessStatus';
    protected $db;

    /**
     * constructor
     * project : the project has a lizmapProject Class
     * params : the params array.
     */
    public function __construct()
    {
        self::declareRedisProfile();
        $this->db = jKVDb::getConnection(self::$profile);
        $wps_url = jApp::config()->wps['wps_rootUrl'];
        $wps_url = ltrim($wps_url, '/');
        if (substr($wps_url, -1) != '/') {
            $wps_url .= '/';
        }

        $this->url = $wps_url.'status/';
    }

    public function all($identifier, $repository, $project)
    {
        $url = $this->url.'?SERVICE=WPS';
        $headers = $this->userHttpHeader($repository, $project);
        $options = array(
            'method' => 'get',
            'headers' => $headers,
        );
        list($data, $mime, $code) = lizmapProxy::getRemoteData($url, $options);

        if (empty($data) or floor($code / 100) >= 4) {
            $data = array();
        } else {
            $data = json_decode($data);
        }

        if (property_exists($data, 'status')) {
            $uuids = array();
            foreach ($data->status as $s) {
                $uuids[$s->uuid] = array(
                    'uuid' => $s->uuid,
                    'identifier' => $s->identifier,
                    'startTime' => $s->time_start,
                    'status' => $s->status,
                    'endTime' => $s->time_end,
                );
            }
            $data = $uuids;
        }

        return $data;
    }

    public function saved($identifier, $repository, $project)
    {
        $url = $this->url.'?SERVICE=WPS';
        $headers = $this->userHttpHeader($repository, $project);
        $options = array(
            'method' => 'get',
            'headers' => $headers,
        );
        list($data, $mime, $code) = lizmapProxy::getRemoteData($url, $options);

        if (empty($data) or floor($code / 100) >= 4) {
            jLog::log('Status saved data is empty or code error '.$code);
            $data = array();
        }

        $data = json_decode($data);

        if (property_exists($data, 'status')) {
            $uuids = array();
            foreach ($data->status as $s) {
                $uuids[] = $s->uuid;
            }
            $data = $uuids;
        } else {
            jLog::log('Status saved no status property in data');
            $data = array();
        }

        $saved = $this->db->get($identifier.':'.$repository.':'.$project);

        if (!$saved) {
            jLog::log('Status saved nothing for '.$identifier.':'.$repository.':'.$project);
            return array();
        }

        $saved = explode(',', $saved);
        if (count($saved) > 0) {
            $uuids = array();
            foreach ($saved as $s) {
                if (in_array($s, $data)) {
                    $uuids[] = $s;
                }
            }

            return $uuids;
        } else {
            jLog::log('Status saved is empty for '.$identifier.':'.$repository.':'.$project);
        }

        return array();
    }

    public function get($identifier, $repository, $project, $uuid)
    {
        $url = $this->url.$uuid.'?SERVICE=WPS';
        $headers = $this->userHttpHeader($repository, $project);
        $options = array(
            'method' => 'get',
            'headers' => $headers,
        );
        list($data, $mime, $code) = lizmapProxy::getRemoteData($url, $options);
        if (empty($data) or floor($code / 100) >= 4) {
            jLog::log('Status get data is empty or code error '.$code);
            return null;
        }

        //$saved = $this->saved($identifier, $repository, $project);

        $status = $this->db->get($uuid);

        if (!$status) {
            jLog::log('Status get nothing for '.$uuid);
            //unset($saved[array_search($uuid, $saved)]);
            //$this->db->set($identifier.':'.$repository.':'.$project, implode(',', $saved));

            return null;
        }

        return json_decode($status);
    }

    public function update($identifier, $repository, $project, $uuid, $status)
    {
        $saved = $this->saved($identifier, $repository, $project);

        if (!in_array($uuid, $saved)) {
            jLog::log('Status update uuid not in saved '.$uuid);
            $saved[] = $uuid;
        }

        if (is_object($status) || is_array($status)) {
            $this->db->set($uuid, json_encode($status));
        } else {
            $this->db->set($uuid, $status);
        }

        $this->db->set($identifier.':'.$repository.':'.$project, implode(',', $saved));
        jLog::log('Status update saved uuids: '.implode(',', $saved));

        return true;
    }

    public function delete($identifier, $repository, $project, $uuid)
    {
        $saved = $this->saved($identifier, $repository, $project);

        if (!in_array($uuid, $saved)) {
            return false;
        }

        $this->db->delete($uuid);
        unset($saved[array_search($uuid, $saved)]);

        $this->db->set($identifier.':'.$repository.':'.$project, implode(',', $saved));

        return true;
    }

    protected static function declareRedisProfile()
    {
        $wpsConfig = jApp::config()->wps;

        $statusRedisHost = $wpsConfig['redis_host'];
        $statusRedisPort = $wpsConfig['redis_port'];
        $statusRedisKeyPrefix = $wpsConfig['redis_key_prefix'];
        $statusRedisDb = $wpsConfig['redis_db'];

        if (extension_loaded('redis')) {
            $driver = 'redis_ext';
        } else {
            $driver = 'redis_php';
        }

        // Virtual status profile parameter
        $statusParams = array(
            'driver' => $driver,
            'host' => $statusRedisHost,
            'port' => $statusRedisPort,
            'key_prefix' => $statusRedisKeyPrefix,
            'db' => $statusRedisDb,
        );

        // Create the virtual status profile
        jProfiles::createVirtualProfile('jkvdb', self::$profile, $statusParams);
    }


    protected function userHttpHeader($repository, $project)
    {
        // Check if a user is authenticated
        if (!jAuth::isConnected()) {
            // return empty header array
            return array();
        }

        $user = jAuth::getUserSession();
        $userGroups = jAcl2DbUserGroup::getGroups();

        $headers = array(
            'X-Lizmap-User' => $user->login,
            'X-Lizmap-User-Groups' => implode(', ', $userGroups),
        );

        $wpsConfig = jApp::config()->wps;
        if (array_key_exists('restrict_to_authenticated_users', $wpsConfig)
            && $wpsConfig['restrict_to_authenticated_users']
            && array_key_exists('enable_job_realm', $wpsConfig)
            && $wpsConfig['enable_job_realm']) {
            $lrep = lizmap::getRepository($repository);
            $lproj = lizmap::getProject($repository.'~'.$project);
            $realm = jApp::coord()->request->getDomainName()
                .'~'. $lrep->getKey()
                .'~'. $lproj->getKey()
                .'~'. jAuth::getUserSession()->login;
            $headers['X-Job-Realm'] = sha1($realm);
            $headers['X-Job-Realm'] = 'e8c10c9dc66f62dec1d52af7549bfc67a11dd6a2';
            jLog::log('Status '.$realm.' '.$headers['X-Job-Realm']);
        }

        return $headers;
    }
}
