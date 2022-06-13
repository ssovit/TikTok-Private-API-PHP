# Unofficial TikTok Private API library for PHP
API Wrapper for private API access

# Installation
Via composer `composer require ssovit/tiktok-private-api`


# How does this work?
Monthly subscription of my private API server is required for this to function.
See below.


# Documentation

https://ssovit.github.io/TikTok-Private-API-PHP/

# Usage
Follow examples in `/example` directory

```php
$api=new \Sovit\TikTokPrivate\Api(array(/* config array*/));

$trendingFeed=$api->getForYou($maxCursor=0);

$userData=$api->getUser("USERNAME");

$userFeed=$api->getUserFeed("USER_ID",$maxCursor=0);

$challenge=$api->getChallenge("CHALLENGE_ID");

$challengeFeed=$api->getChallengeFeed("CHALLENGE_ID",$maxCursor=0);

$musc=$api->getMusic("6798898508385585925");

$musicFeed=$api->getMusicFeed("6798898508385585925",$maxCursor=0);

$videoData=$api->getVideoByID("6829540826570296577");

$videoData=$api->getVideoByUrl("https://www.tiktok.com/@zachking/video/6829303572832750853");

// More to come

```

# Available Options
```php
$api=new \Sovit\TikTokPrivate\Api(array(
	"proxy"		=> '', // proxy in url format like http://username:password@host:port
	"cache_timeout"		=> 3600 // 1 hours cache timeout
	"transform_result"		=> true, // false if you want to get json without transforming it to more readable JSON structure
	"api_key"		=> "API_KEY" // see below on how to get API key
	), $cache_engine=false);
```

# Cache Engine
You can build your own engine that will store and fetch cache from your local storage to prevent frequent requests to TikTok server. This can help being banned from TikTok server for too frequent requests.

Cache engine should have callable `get` and `set` methods that the API class uses
```php
// Example using WordPress transient as cache engine
Class MyCacheEngine{
	function get($cache_key){
		return get_transient($cache_key);
	}
	function set($cache_key,$data,$timeout=3600){
		return set_transient($cache_key,$data,$timeout);
	}
}

```
**Usage**
```php
$cache_engine=new MyCacheEngine();
$api=new \Sovit\TikTokPrivate\Api(array(/* config array*/),$cache_engine);
```

# Available methods
- `getForYou` - Get trending feed `getForYou($maxCursor)`
- `getUser` - Get profile data for TikTok User `getUser($username)`
- `getUserFeed` - Get user feed by ID `getUserFeed($user_id,$maxCursor)`
- `getChallenge` - Get challenge/hashtag info `getChallenge($challenge)`
- `getChallengeFeed` - Get challenge feed by ID `getChallengeFeed($challenge_id, $maxCursor)`
- `getMusic` - Get music info `getMusic($music_id)`
- `getMusicFeed` - Get music feed `getMusicFeed($music_id,$maxCursor)`
- `getVideoByID` - Get video by ID `getVideoByID($video_id)`
- `getVideoByUrl` - Get video by URL `getVideoByUrl($video_url)`
- *not all methods are documented, will update when I am free*
`$maxCursor` defaults to `0`, and is offset for results page. `maxCursor` for next page is exposed on current page call feed data.


## Pirvate API server subscription pricing
| Package | Cost(per month) | Quota(requests per day) | Quota (requests per month) |
| ------- | :---------------: | --------------: | -----------------: |
| **Pro** *(popular)* | 50 USD | 5,000 | ~150,000 |
| **Mega** | 100 USD | 12,000 | ~360,000 |
| **Ultra** | custom pricing | ? | ? |

mail me at sovit.tamrakar@gmail.com or contact via telegram at https://t.me/ssovit for subscription info

# Disclaimer
TikTok is always updating their API endpoints but I will try to keep this library whenever possible. I take no responsibility if you or your IP gets banned using this API. It's recommended that you use proxy.