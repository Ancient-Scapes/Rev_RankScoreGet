<?php
  //情報取得ドメイン
  define("TARGET_ROOT","http://www.capcom.co.jp/arcade/rev/PC/");
  //ログイン後情報取得ドメイン
  define("TARGET_ROOT_AFTERLOGIN","https://rev-srw.ac.capcom.jp/");

  //ハイスコアランキングデータ取得ページ
  define("HIGHSCORE_HTML","ranking_highscore.html");
  //RP取得html
  define("RP_HTML","ranking_RP.html");
  //自身のハイスコア取得ページ
  define("PLAYDATAMUSIC_HTML","playdatamusic.html");
  //ログインページ
  define("WEBLOGIN_HTML" , "weblogin.html");
  //難易度配列定義 (スコープの関係上別function内で使用することができないので注意！)
  $sc['difficulty'] = array(
                             'easy'          =>  'EASY',
                             'standard'      =>  'STANDARD',
                             'hard'          =>  'HARD',
                             'master'        =>  'MASTER',
                             'unlimited'     =>  'UNLIMITED',
                             'maxdifficulty' =>  '最高難易度'
                      );
  //ハイスコアランキングページ数
  define("PAGE_NUMBER_LAST",13);
  //ランキング内プレイヤー表示数
  define("RANKING_PLAYER_COUNT",10);

  //出力内容表記
  define("OUTPUT_MUSICNAME" ,'曲名');
  define("OUTPUT_DIFFICULTY",'難易度');
  define("OUTPUT_RANK"      ,'順位');
  define("OUTPUT_SCORE"     ,'スコア');
  define("OUTPUT_TOPDIFF"   ,'全１差分');
  define("OUTPUT_HIGHDIFF"  ,'上位差分');
  define("OUTPUT_LOWDIFF"   ,'下位差分');
?>