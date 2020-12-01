<?php

namespace App\Utils;

/**
 * This class retrieves get parameter on the url import by the user 
 */
class ManageVideoUrl
{
    public function getParametersOnUrl($videoLink, $hostAcceptedVideos)
    {
        $video = parse_url($videoLink);
        if (!(in_array($video["host"], $hostAcceptedVideos))) {
            throw new \Exception('Cette url ne peut pas être ajoutée');
        }

        if ($video["host"] == 'youtu.be') {
            $video = explode('/', $video["path"]);
            return 'https://www.youtube.com/embed/' . $video[1];
        }

        if ($video["host"] == 'www.youtube.com') {
            $video = explode('=', $video["query"]);
            return 'https://www.youtube.com/embed/' . $video[1];
        } 
        
        if ($video["host"] == 'www.dailymotion.com') {
            $video = explode('video/', $video["path"]);
            return 'https://www.dailymotion.com/embed/video/' . $video[1];
        }

        if ($video["host"] == 'dai.ly') {
            $video = explode('/', $video["path"]);
            return 'https://www.dailymotion.com/embed/video/' . $video[1];
        }
    }
}


