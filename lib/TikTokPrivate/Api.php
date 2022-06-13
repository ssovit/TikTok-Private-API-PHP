<?php

namespace Sovit\TikTokPrivate;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

if (!\class_exists('\Sovit\TikTokPrivate\Api')) {
    /**
     * TikTok Private API Wrapper
     */
    class Api
    {
        /**
         * API Base
         *
         * @var string
         */
        protected $api_base = "https://api-3.wppress.net";

        /**
         * Config
         *
         * @var array
         */
        private $_config = [];

        /**
         * If Cache is enabled
         *
         * @var boolean
         */
        private $cacheEnabled = false;

        /**
         * Cache Engine
         *
         * @var object
         */
        private $cacheEngine;

        /**
         * Default config
         *
         * * proxy @link https://docs.guzzlephp.org/en/stable/request-options.html#proxy
         * * cache_timeout Cache Timeout
         * * transfrom_result If to transform result or not
         *
         * @var array
         */
        private $defaults = [
            "proxy"            => null,
            "cache_timeout"    => 3600,
            "transform_result" => true,
        ];

        /**
         * Class Constructor
         *
         * @param array $config API Config
         * @param object|false $cacheEngine
         * @return void
         */
        public function __construct($config = [], $cacheEngine = false)
        {
            /**
             * Initialize the config array
             */
            $this->_config = array_merge([], $this->defaults, $config);

            /**
             * If Cache Engine is enabled
             */
            if ($cacheEngine) {
                $this->cacheEnabled = true;
                $this->cacheEngine  = $cacheEngine;
            }
        }

        /**
         * Get Challenge detail
         *
         * @param string $challenge_id Challenge ID
         * @return object|false Returns object or false on failure
         */
        public function getChallenge($challenge_id)
        {
            /**
             * Check if challenge is not empty
             */
            if (empty($challenge_id)) {
                throw new \Exception("Invalid Challenge");
            }
            $cacheKey = 'challenge-' . $challenge_id;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("challenge/" . $challenge_id);
            if (isset($result->ch_info)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Challenge($result->ch_info);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get Challenge Feed
         *
         * @param string $challenge_id Challenge ID
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getChallengeFeed($challenge_id, $cursor = 0)
        {
            if (empty($challenge_id)) {
                throw new \Exception("Invalid Challenge");
            }
            $cacheKey = 'challenge-' . $challenge_id . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("challenge/" . $challenge_id . "/feed", [
                'maxCursor' => $cursor,
            ]);
            if (isset($result->aweme_list)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Feed($result);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get Challenge ID By Challenge name
         *
         * @param string $challenge_name Challenge Name
         * @return string|false Returns challenge ID or false on failure
         */
        public function getChallengeID($challenge_name)
        {
            if (empty($challenge_name)) {
                throw new \Exception("Invalid Challenge");
            }
            $cacheKey = 'chid-' . $challenge_name;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->searchChallenge($challenge_name);
            if ($result) {
                $result = Util::find($result->challenge_list, function ($item) use ($challenge_name) {
                    return $item->challenge_info->cha_name === $challenge_name;
                });
                if ($result) {
                    if ($this->cacheEnabled) {
                        $this->cacheEngine->set($cacheKey, $result->challenge_info->cid, 86400 * 365);
                    }
                    return $result->challenge_info->cid;
                }
            }
            return $this->failure();
        }

        /**
         * Get video comments
         *
         * @param string $video_id Video ID
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getComments($video_id, $cursor = 0)
        {
            if (empty($video_id)) {
                throw new \Exception("Invalid Video ID");
            }
            $cacheKey = 'comments-' . $video_id;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("comments/" . $video_id, [
                'maxCursor' => $cursor,
            ]);
            if (isset($result->comments)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Trending Feed
         *
         * @return object|false Returns object or false on failure
         */
        public function getForYou($cursor = 0)
        {
            $cacheKey = 'trending-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("trending");
            if (isset($result->aweme_list)) {
                if (true === $this->_config['transform_result']) {
                    $result            = Transform::Feed($result);
                    $result->maxCursor = ++$cursor;
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get Music detail
         *
         * @param string $music_id Music ID
         * @return object|false Returns object or false on failure
         */
        public function getMusic($music_id)
        {
            if (empty($music_id)) {
                throw new \Exception("Invalid Music ID");
            }
            $cacheKey = 'music-' . $music_id;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("music/" . $music_id);
            if (isset($result->music_info)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Music($result->music_info);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get music feed
         *
         * @param string $music_id Music ID
         * @param int $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getMusicFeed($music_id, $cursor = 0)
        {
            if (empty($music_id)) {
                throw new \Exception("Invalid Music ID");
            }
            $cacheKey = 'music-feed-' . $music_id . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("music/{$music_id}/feed", [
                "maxCursor" => $cursor,
            ]);
            if (isset($result->aweme_list)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Feed($result);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get user detail by username
         *
         * @param  string $username
         * @return object|false Returns object or false on failure
         */
        function getUser($username)
        {
            return $this->getUserFromSearch($username);
        }

        /**
         * Get User detail by ID
         *
         * @param string $user_id User ID
         * @return object|false Returns object or false on failure
         */
        public function getUserByID($user_id)
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid Username");
            }
            $cacheKey = 'user-' . $user_id;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("user/{$user_id}");
            if (isset($result->user)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::User($result->user);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get user detail from search by username as keyword
         *
         * @param  string $username
         * @return object|false Returns object or false on failure
         */
        public function getUserFromSearch($username)
        {
            if (empty($username)) {
                throw new \Exception("Invalid Username");
            }
            $search = $this->searchUser($username);
            if ($search !== false  && !empty($search->user_list)) {
                $result = Util::find($search->user_list, function ($item) use ($username) {
                    return $item->user_info->unique_id === $username;
                });
                if ($result) {
                    $result = $result->user_info;
                    if (true === $this->_config['transform_result']) {
                        $result = Transform::User($result);
                    }
                    return $result;
                }
                return $this->failure();
            }
        }

        /**
         * Get user feed
         *
         * @param string $user_id User ID
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getUserFeed($user_id, $cursor = 0)
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid Username");
            }
            $cacheKey = 'user-feed-' . $user_id . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("user/{$user_id}/feed", [
                "maxCursor" => $cursor,
            ]);
            if (isset($result->aweme_list)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Feed($result);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get User Followers
         *
         * @param string $user_id User ID
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getUserFollowers($user_id, $cursor = 0)
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid User ID");
            }
            $cacheKey = 'follower-' . $user_id . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }

            $result = $this->remote_call("followers/{$user_id}", [
                "maxCursor" => $cursor,

            ]);
            if (isset($result->followers)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get User Followings
         *
         * @param string $user_id User ID
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function getUserFollowings($user_id, $cursor = 0)
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid User ID");
            }
            $cacheKey = 'follower-' . $user_id . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }

            $result = $this->remote_call("following/{$user_id}", [
                "maxCursor" => $cursor,

            ]);
            if (isset($result->followings)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get user id by username
         *
         * @param string $username Username
         * @return string|false Returns user ID or false on failure
         */
        public function getUserID($username)
        {
            if (empty($username)) {
                throw new \Exception("Invalid User");
            }
            $cacheKey = 'userid-' . $username;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("suggestion/{$username}");
            if ($result) {
                $result = Util::find($result->sug_list, function ($item) use ($username) {
                    return $item->extra_info->sug_uniq_id === $username;
                });
                if ($result) {
                    if ($this->cacheEnabled) {
                        $this->cacheEngine->set($cacheKey, $result->extra_info->sug_user_id, 86400 * 365);
                    }
                    return $result->extra_info->sug_user_id;
                }
            }
            return $this->failure();
        }

        /**
         * Get video by video id
         *
         * @param string $video_id Video ID
         * @return object|false Returns object or false on failure
         */
        public function getVideoByID($video_id)
        {
            if (empty($video_id)) {
                throw new \Exception("Invalid Video ID");
            }
            $cacheKey = 'video-' . $video_id;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("video/{$video_id}");
            if (isset($result->aweme_detail)) {
                if (true === $this->_config['transform_result']) {
                    $result = Transform::Item($result->aweme_detail);
                }
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Get Video by TikTok URL
         *
         * @param string $url Video URL
         * @return object|false Returns object or false on failure
         */
        public function getVideoByUrl($url)
        {
            if (!preg_match("/https?:\/\/([^\.]+)?\.tiktok\.com/", $url)) {
                throw new \Exception("Invalid Video URL");
            }
            if (!preg_match("/(video|v)\/([\d]+)/", $url)) {
                $url = $this->finalUrl($url);
            }
            if (preg_match("/(video|v)\/([\d]+)/", $url, $match)) {
                return $this->getVideoByID($match[2]);
            }
            return $this->failure();
        }

        /**
         * Search challenge by challenge name
         *
         * @param string $keyword Search Keyword
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function searchChallenge($keyword, $cursor = 0)
        {
            if (empty($keyword)) {
                throw new \Exception("Invalid keyword");
            }
            $cacheKey = 'search-challenge-' . $keyword . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("search/challenge/" . $keyword, [
                'maxCursor' => $cursor,
            ]);
            if (isset($result->challenge_list)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Search User by username
         *
         * @param string $keyword Serch Keyword
         * @param integer $cursor Offset Cursor
         * @return object|false Returns object or false on failure
         */
        public function searchUser($keyword, $cursor = 0)
        {
            if (empty($keyword)) {
                throw new \Exception("Invalid keyword");
            }
            $cacheKey = 'search-user-' . $keyword . '-' . $cursor;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call("search/user/" . $keyword, [
                'maxCursor' => $cursor,
            ]);
            if (isset($result->user_list)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache_timeout']);
                }
                return $result;
            }
            return $this->failure();
        }

        /**
         * Check if api key is provided
         *
         * @return void
         */
        private function checkAPIKey()
        {
            if (empty($this->_config['api_key'])) {
                throw new \Exception("Valid API Key is required");
            }
        }

        /**
         * Failure
         * 
         * Be a man and accept the failure.
         * 
         *
         * @return false Returns false
         */
        private function failure()
        {
            //\error_log("Something went wrong");
            return false;
        }

        /**
         * Get final redirect URL
         *
         * @param string $url Video Post URL
         * @return string Returns final redirect url
         */
        private function finalUrl($url)
        {
            try {
                $client = new Client();

                $result = $client->get($url, [
                    "headers"=>["user-agent"=>"okhttp"],
                    "allow_redirects"=>["max"=>1],
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    },
                    'verify'  => false,
                    "proxy"    => $this->_config['proxy'],
                ]);
                return $url;
            } catch (\Exception $e) {
                return $url;
            }
        }

        /**
         * Make remote call
         *
         * @param string $path Remote path
         * @param array $params Parameters
         * @return object|false Returns object or false on failure
         */
        private function remote_call($path, $params = [])
        {
            $this->checkAPIKey();
            $params['key'] = $this->_config['api_key'];
            try {
                $client = new Client();

                $response = $client->get(trim($this->api_base, "/") . "/" . trim($path, "/"), [
                    "query"  => $params,
                    'verify' => false,
                ]);
                $result = json_decode($response->getBody(), true);
                $response = $client->get($result['url'], [
                    "headers" => $result['headers'],
                    "proxy"   => $this->_config['proxy'],
                    'verify'  => false,
                ]);
                $result = json_decode($response->getBody(), false);

                return $result;
            } catch (\Exception $e) {
                return false;
            }
            return false;
        }
    }
}
