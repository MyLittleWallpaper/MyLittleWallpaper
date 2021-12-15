<?php

// Just the list of listed colours and similarities (which also exists in the database)

//ffffff => ffffff
//cccccc => cccccc, 999999
//666666 => 666666, 999999, 333333
//000000 => 000000 333333 330000 331c12 341a00 333300 183400 003300 00341a 003433 001b34 000044 180034 320034 34001c
//990000 => 990000 550000 330000 552b2b aa5555 9a0053 56002f 34001c 552b40 aa5683
//ff6666 => ff6666 ff0000 aa5555 c89797 ffaaaa ff018a fe68b9 ffabd8 aa5683 c898b2
//7f462c => 7f462c b3623e 542e1d 331c12 562a00 341a00 55402b aa8056 9a4c00
//ff8d58 => ff8d58 b3623e ffbc9e ffd5ab
//ff7f01 => ff7f01 feb268 ffd5ab c8b098
//aaaa00 => aaaa00 555500 333300 555533 aaaa55 3f552b
//ffff00 => ffff00 dbdc99 ffff99
//00aa00 => 00aa00 005500 183400 2b552b 55aa55 489a00 285600 183400 3f552b 7eaa56 009a4c 00562a 00341a 2b5540 56aa80
//00ff00 => 00ff00 97c897 99ff99 79ff01 affe68 afc898 d3ffab 01ff7f 68feb2 abffd5 98c8b0 55aa55 7eaa56 56aa80
//009a98 => 009a98 005655 003433 2b5555 56aaa9 2b5540 56aa80
//68fefc => 68fefc 01fffc abfffe 56aaa9 98c8c7 98c8b0
//00509a => 00509a 002d56 001b34 2b4055 2b2b55 
//68b6fe => 68b6fe 0184ff 5681aa 98b1c8 abd6ff
//0000ff => 0000ff 000088 000044 2b2b55 5555aa
//aaaaff => aaaaff 6666ff 9797c8
//48009a => 48009a 280056 180034 402b55
//af68fe => af68fe 7901ff 7e56aa af98c8 d3abff
//95009a => 95009a 530056 320034 552b55 a756aa
//f868fe => f868fe f601ff fcabff c698c8
//fe68b9 => fe68b9 ff018a ffabd8 aa5683 c898b2

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

$html    = '';
$colours = [
    'ffffff' => 'ffffff',
    'cccccc' => 'cccccc',
    '666666' => '666666',
    '000000' => '000000',
    '990000' => '990000',
    'ff6666' => 'ff6666',
    '7f462c' => '7f462c',
    'ff8d58' => 'ff8d58',
    'ff7f01' => 'ff7f01',
    'aaaa00' => 'aaaa00',
    'ffff00' => 'ffff00',
    '00aa00' => '00aa00',
    '00ff00' => '00ff00',
    '009a98' => '009a98',
    '68fefc' => '68fefc',
    '00509a' => '00509a',
    '68b6fe' => '68b6fe',
    '0000ff' => '0000ff',
    'aaaaff' => 'aaaaff',
    '48009a' => '48009a',
    'af68fe' => 'af68fe',
    '95009a' => '95009a',
    'f868fe' => 'f868fe',
    'fe68b9' => 'fe68b9',
];
$cnt     = 0;
$html    .= '<h2 style="padding:6px 6px 4px 6px;">Colours</h2>';
foreach ($colours as $scol => $dcol) {
    $cnt++;

    $html .= '<a style="display:inline-block;height:25px;overflow:hidden;box-shadow:0px 1px 6px #888;margin:6px;border-radius:10px;" href="' .
        PUB_PATH_CAT . '?search=' . urlencode('colour:' . $scol) . '">';
    $html .= '<span style="font-family:monospace;display:block;float:left;height:21px;width:75px;border-radius:10px 0 0 10px;padding:5px 0 0 7px;">#' .
        Format::htmlEntities($scol) . '</span>';
    $html .= '<span style="display:block;float:left;width:85px;height:25px;background:#' . $dcol .
        ';border-radius:0 10px 10px 0;box-shadow:-1px 0 6px #aaa;">&nbsp;</span>';
    $html .= '</a> ';
    if ($cnt == 5) {
        $html .= '<br />';
        $cnt  = 0;
    }
}
$cnt  = 0;
$html .= '<h2 style="padding:12px 6px 4px 6px;">Major colours</h2>';
foreach ($colours as $scol => $dcol) {
    $cnt++;

    $html .= '<a style="display:inline-block;height:25px;overflow:hidden;box-shadow:0px 1px 6px #888;margin:6px;border-radius:10px;" href="' .
        PUB_PATH_CAT . '?search=' . urlencode('major-colour:' . $scol) . '">';
    $html .= '<span style="font-family:monospace;display:block;float:left;height:21px;width:75px;border-radius:10px 0 0 10px;padding:5px 0 0 7px;">#' .
        Format::htmlEntities($scol) . '</span>';
    $html .= '<span style="display:block;float:left;width:85px;height:25px;background:#' . $dcol .
        ';border-radius:0 10px 10px 0;box-shadow:-1px 0 6px #aaa;">&nbsp;</span>';
    $html .= '</a> ';
    if ($cnt == 5) {
        $html .= '<br />';
        $cnt  = 0;
    }
}

$colorlist = new BasicPage();
$colorlist->setHtml($html);
$colorlist->setNoContainer();

$response = new Response($colorlist);
$response->setDisableHeaderAndFooter();
$response->output();
