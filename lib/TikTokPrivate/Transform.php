<?php

namespace Sovit\TikTokPrivate;

if (!\class_exists('\Sovit\TikTokPrivate\Transform')) {
    class Transform {
        /**
         * Transform Challenge data
         *
         * @param obejct $data
         * @return object
         */
        public static function Challenge($data) {
            $challenge = $data->ch_info;
            $result    = [
                'challenge' => [
                    'id'            => @$challenge->cid,
                    'title'         => @$challenge->cha_name,
                    'desc'          => @$challenge->desc,
                    'profileLarger' => @$challenge->hashtag_profile,
                    'profileMedium' => @$challenge->hashtag_profile,
                    'profileThumb'  => @$challenge->hashtag_profile,
                    'coverLarger'   => @$challenge->cover_photo,
                    'coverMedium'   => @$challenge->cover_photo,
                    'coverThumb'    => @$challenge->cover_photo,
                    'isCommerce'    => @$challenge->is_commerce,
                ],
                'stats'     => [
                    'videoCount' => @$challenge->user_count,
                    'viewCount'  => @$challenge->view_count,
                ],
            ];
            return (object) $result;
        }

        /**
         * Transform Feed data
         *
         * @param object $data
         * @return object
         */
        public static function Feed($data) {
            $result = [
                'statusCode' => 0,
                'hasMore'    => true == $data->has_more,
                'maxCursor'  => isset($data->max_cursor) ? $data->max_cursor : (isset($data->cursor) ? $data->cursor : 0),
                'items'      => self::Items($data->aweme_list),
            ];
            return (object) $result;
        }

        public static function Item($item) {
            $result = [
                'id'            => @$item->aweme_id,
                'desc'          => @$item->desc,
                'createTime'    => @$item->create_time,
                'video'         => [
                    'id'              => @$item->video->play_addr->uri,
                    'height'          => @$item->video->height,
                    'width'           => @$item->video->width,
                    'duration'        => @$item->video->duration,
                    'ratio'           => @$item->video->ratio,
                    'cover'           => @$item->video->cover->url_list[0],
                    'originCover'     => @$item->video->origin_cover->url_list[0],
                    'dynamicCover'    => @$item->video->animated_cover->url_list[0],
                    'playAddr'        => @$item->video->download_addr->url_list[0],
                    'downloadAddr'    => @$item->video->download_addr->url_list[0],
                    'noWatermarkAddr' => @$item->video->play_addr->url_list[0],

                ],
                'author'        => [
                    'id'             => @$item->author->uid,
                    'shortId'        => @$item->author->short_id,
                    'uniqueId'       => @$item->author->unique_id,
                    'nickname'       => @$item->author->nickname,
                    'avatarLarger'   => @$item->author->avatar_larger->url_list[0],
                    'avatarMedium'   => @$item->author->avatar_medium->url_list[0],
                    'avatarThumb'    => @$item->author->avatar_thumb->url_list[0],
                    'verified'       => @!empty($item->author->custom_verify),
                    'secUid'         => @$item->author->sec_uid,
                    'commentSetting' => @$item->author->comment_setting,
                    'duetSetting'    => @$item->author->duet_setting,
                    'stitchSetting'  => @$item->author->stitch_setting,
                    'privateAccount' => @$item->author->secret,
                    'secret'         => @$item->author->secret,
                    'roomId'         => @$item->author->room_id,
                ],
                'music'         => [
                    'id'          => @$item->music->mid,
                    'title'       => @$item->music->title,
                    'playUrl'     => @$item->music->play_url->url_list[0],
                    'coverLarge'  => @$item->music->cover_large->url_list[0],
                    'coverMedium' => @$item->music->cover_medium->url_list[0],
                    'coverThumb'  => @$item->music->cover_thumb->url_list[0],
                    'authorName'  => @$item->music->author,
                    'original'    => @$item->music->is_original,
                    'duration'    => @$item->music->duration,
                    'album'       => @$item->music->album,
                ],
                'stats'         => [
                    'diggCount'          => @$item->statistics->digg_count,
                    'shareCount'         => @$item->statistics->share_count,
                    'commentCount'       => @$item->statistics->comment_count,
                    'playCount'          => @$item->statistics->play_count,
                    'downloadCount'      => @$item->statistics->download_count,
                    'whatsAppShareCount' => @$item->statistics->whatsapp_share_count,
                    'forwardCount'       => @$item->statistics->forward_count,
                ],
                'region'        => @$item->region,
                'secret'        => false,
                'privateItem'   => false,
                'duetEnabled'   => @$item->video_control->allow_duet,
                'stitchEnabled' => @$item->video_control->allow_stitch,
                'shareEnabled'  => @$item->status->allow_share,
                'reactEnabled'  => @$item->status->allow_react,
                'isAd'          => @$item->is_ads,
            ];
            return (object) $result;
        }

        /**
         * Process Feed Items
         *
         * @param object $items
         * @return object
         */
        public static function Items($items) {
            $result = [];
            foreach ($items as $item) {
                $result[] = self::Item($item);
            }
            return (object) $result;
        }

        /**
         * Transform Music Data
         *
         * @param object $data
         * @return object
         */
        public static function Music($data) {
            $music  = $data->music_info;
            $result = [
                'music' => [
                    'id'          => @$music->mid,
                    'title'       => @$music->title,
                    'playUrl'     => @$music->play_url->url_list[0],
                    'coverLarge'  => @$music->cover_large->url_list[0],
                    'coverMedium' => @$music->cover_medium->url_list[0],
                    'coverThumb'  => @$music->cover_thumb->url_list[0],
                    'authorName'  => @$music->author,
                    'original'    => @$music->is_original,
                    'duration'    => @$music->duration,
                    'album'       => @$music->album,
                ],
                'stats' => [
                    'videoCount' => @$music->user_count,
                ],
            ];
            return (object) $result;
        }

        /**
         * Transform User data
         *
         * @param object $data
         * @return object
         */
        public static function User($data) {
            $user   = $data->user;
            $result = [
                'user'  => [
                    'id'               => @$user->uid,
                    'shortId'          => @$user->short_id,
                    'uniqueId'         => @$user->unique_id,
                    'nickname'         => @$user->nickname,
                    'avatarLarger'     => @$user->avatar_larger->url_list[0],
                    'avatarMedium'     => @$user->avatar_medium->url_list[0],
                    'avatarThumb'      => @$user->avatar_thumb->url_list[0],
                    'signature'        => @$user->signature,
                    'verified'         => !empty($user->custom_verify),
                    'secUid'           => @$user->sec_uid,
                    'openFavorite'     => @true == $user->show_favorite_list,
                    "bioLink"          => @isset($user->bio_url) ? [
                        "link" => @$user->bio_url,
                        "risk" => 0,
                    ] : null,
                    "bioEmail"         => @$user->bio_email,
                    "category"         => @$user->category,
                    "twitter"          => @$user->twitter_id,
                    "youtubeChannelId" => @$user->youtube_channel_id,
                    'commentSetting'   => @true == $user->comment_setting,
                    'duetSetting'      => @true == $user->duet_setting,
                    'privateAccount'   => @true == $user->secret,
                    'secret'           => @true == $user->secret,
                    'isADVirtual'      => @true == $user->ad_virtual,
                    'roomId'           => @$user->room_id,
                ],
                'stats' => [
                    'followerCount'  => @$user->follower_count,
                    'followingCount' => @$user->following_count,
                    'heart'          => @$user->total_favorited,
                    'heartCount'     => @$user->total_favorited,
                    'videoCount'     => @$user->aweme_count,
                    'diggCount'      => @$user->favoriting_count,
                ],
            ];
            return (object) $result;
        }
    }
}
