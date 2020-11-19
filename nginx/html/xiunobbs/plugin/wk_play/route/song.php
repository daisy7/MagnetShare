<?php

!defined('DEBUG') AND exit('Forbidden');

$songid = param('id');
empty($songid) AND $songid = param(1);
$songid = trim($songid);

$songid_decode = xn_urldecode($songid);
echo $songid_decode
return;
if($songid){
    $url = 'http://localhost:3000/song/url?id='.$songid_decode;
    $json = file_get_contents($url);
    $song_info = json_decode($json);
    if($song_info) {
        if($song_info->code != 200) {
            echo $song_info->code;
        } else {
            echo $song_info->data[0]->url;
        }
    }
}
?>
