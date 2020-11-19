<?php exit;
$gid=isset($user['gid'])?$user['gid']:'0'; $need_refresh=0;
$preg_play = preg_match_all('/\[x_audio\](.*?)\[\/x_audio\]/i',$first['message_fmt'],$array_play);
if($preg_play) {
    $html_import='<link href="plugin/wk_play/APlayer/APlayer.min.css" type="text/css" rel="stylesheet"/>
                  <script src="plugin/wk_play/APlayer/APlayer.min.js"> </script> <div id="aplayer"></div> ';
    $array_count = count($array_play[0]);
    $html_items='';
    for($i=0;$i<$array_count;$i++){
        $raw = $array_play[0][$i];
        $song_ids = str_replace('&nbsp;','',$array_play[1][$i]);
        foreach(explode(',',$song_ids) as $key => $value){
            $song_id = trim($value);
            $json = file_get_contents('http://47.111.16.6:3000/song/url?id='.$song_id);
            $song_info = json_decode($json);
            if($song_info) {
                if($song_info->code != 200) {
                $song_url = $song_info->code;
                } else {
                    $song_url = $song_info->data[0]->url;
                }
            }
            $json = file_get_contents('http://47.111.16.6:3000/song/detail?ids='.$song_id);
            $song_detail = json_decode($json);
            if($song_detail) {
                if($song_detail->code != 200) {
                    $song_name = $song_detail->code;
                    $song_artist = $song_detail->code;
                    $song_cover = $song_detail->code;
                } else {
                    $song_name = $song_detail->songs[0]->name;
                    $song_artists = array_map(function($item){
                        return $item->name;
                    },isset($song_detail->songs[$i]->ar)?$song_detail->songs[$i]->ar:[]);
                    $song_cover = $song_detail->songs[0]->al->picUrl;
                }
            }
            $html_items.='{
                name: "'.$song_name.'",
                artist: "'.join("&",$song_artists).'",
                url: "'.$song_url.'",
                cover: "'.$song_cover.'",
                lrc:"",
                theme: "#ebd0c2"
            },';
        }
        if($array_count>1 && $i<$array_count - 1) {
            $first['message_fmt'] = str_replace($raw,'',$first['message_fmt']);
        }
    }
    $html_reply='<script>const ap = new APlayer({
        container: document.getElementById("aplayer"),
        audio: ['.$html_items.']
    });</script>';
    $first['message_fmt'] = str_replace($raw,$html_import.$html_reply,$first['message_fmt']);
    $need_refresh=1;
}
$preg_play = preg_match_all('/\[x_video\](.*?)\[\/x_video\]/i',$first['message_fmt'],$array_play);
if($preg_play) {
    $array_count = count($array_play[0]);
    $html_items='';
    for($i=0;$i<$array_count;$i++){
        $raw = $array_play[0][$i];
        $video_ids = str_replace('&nbsp;','',$array_play[1][$i]);
        foreach(explode(',',$video_ids) as $key => $value){
            $video_id = trim($value);
            $json = file_get_contents('http://47.111.16.6:3000/mv/url?id='.$video_id);
            $video_info = json_decode($json);
            if($video_info) {
                if($video_info->code != 200) {
                    $video_url = $video_info->code;
                } else {
                    $video_url = $video_info->data->url;
                }
            }
            $html_items.='<video src="'.$video_url.'" controls="controls" width="100%" height="100%" loop="loop"></video>';
        }
        if($array_count>1 && $i<$array_count - 1) {
            $first['message_fmt'] = str_replace($raw,'',$first['message_fmt']);
        }
    }
    $first['message_fmt'] = str_replace($raw,$html_items,$first['message_fmt']);
    $need_refresh=1;
}
$preg_reply = preg_match_all('/\[x_reply\](.*?)\[\/x_reply\]/i',$first['message_fmt'],$array);
if($preg_reply) {
    $array_count = count($array[0]);
    $html_reply ='<div class="alert alert-warning" role="alert">本帖含有隐藏内容，请您<a href="javascript:void(0);" data-tid="'.$first["tid"].'" class="post_reply" title="快速跳转"> 回复 </a>后刷新查看</div>';
    if($uid) $replied=db_find_one('post',array('uid'=>$uid,'tid'=>$thread['tid'])); else $replied=array();
    for($i=0;$i<$array_count;$i++){
        $a = $array[0][$i];
        $b = '<div class="alert alert-success" role="alert">'.$array[1][$i].'</div>';
        if($uid AND $replied)$first['message_fmt'] = str_replace($a,$b,$first['message_fmt']);
        if($uid AND isset($gid) AND $gid==1)$first['message_fmt'] = str_replace($a,$b,$first['message_fmt']);
        // hook read_p_reply_verify_end.php
        else {$first['message_fmt'] = str_replace($a,$html_reply,$first['message_fmt']);$need_refresh=1;}
    }
}
?>
