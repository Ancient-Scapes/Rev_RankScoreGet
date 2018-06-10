<?php
  //定数の読み込み
  require_once("require/scraping.php");

  //ログ書き込みに使用するパス
  define("LOG_PATH_EXECUTE",'Log/Execute/ExecuteLog_'. date('Ymd') .'.txt');
  define("LOG_PATH_ERROR"  ,'Log/Error/ErrorLog_'  . date('Ymd') .'.txt');
  define("LOG_PATH_DEBUG"  ,'Log/Debug/DebugLog_'  . date('Ymd') .'.txt');

  //正常処理ログ書き込みモード
  define("WRITE_MODE_LOG", 0);    //ログ用
  define("WRITE_MODE_OUTPUT", 1); //通常出力用

  //ログ書き込み文字列(ヘッダ）の長さ
  define("LENGTH_DIFFICULTY_H", 13);
  define("LENGTH_RANK_H"      , 6);
  define("LENGTH_SCORE_H"     , 11);
  define("LENGTH_TOPDIFF_H"   , 10);
  define("LENGTH_HIGHDIFF_H"  , 10);
  define("LENGTH_LOWDIFF_H"   , 10);

  //ログ書き込み文字列の長さ
  define("LENGTH_DIFFICULTY", 10);
  define("LENGTH_RANK"      , 4);
  define("LENGTH_SCORE"     , 8);
  define("LENGTH_TOPDIFF"   , 8);
  define("LENGTH_HIGHDIFF"  , 8);
  define("LENGTH_LOWDIFF"   , 8);

  //エラーハンドラの定義を行いcatchでエラー内容を出力するように設定
  set_error_handler(function($errno, $errstr, $errfile, $errline){
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  });

  //$address...開くURL
  //cgetを用いてHTMLを取得する
  function file_cget_contents($address)
  {
  	$ch = curl_init($address); // 初期化
  	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // 出力内容を受け取る設定
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false); // httpsへ接続できるように証明書関係の設定を行う
  	$result = curl_exec( $ch ); // データの取得
  	curl_close($ch); // cURLのクローズ
  	//日本語が文字化けしない設定
  	$result = mb_convert_encoding($result,"HTML-ENTITIES","auto");
  	return $result;
  }

  //$top10Data...書き込み全10情報
  //$cntTop10Data...全10の数
  //$name...プレイヤー名
  //$rp...RP
  //$pageNumber...検索対象ページ番号
  //$musicNameLength...全10曲で一番長い曲名の長さ
  //$mode...ログモードと通常出力モードでIPアドレスの出力を決める
  //正常処理ログ出力
  function outputExecuteLog($top10Data,$cntTop10Data,$name,$rp,$pageNumber,$musicNameLength,$mode)
  {
    $fp = fopen(LOG_PATH_EXECUTE, "a");
    flock($fp, 2);
    fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    if($mode === WRITE_MODE_LOG){
      fwrite($fp,'['. date('Y/m/d H:i:s')    .']' . 'アクセスIPアドレス:'. $_SERVER["REMOTE_ADDR"] . "\n");
    }
    else{
      fwrite($fp,'['. date('Y/m/d H:i:s')    .']'. "\n");
    }
    fwrite($fp, '      プレイヤー名:' . $name . "\n");
    //RPが入力されている場合
    if(isset($rp)){
        fwrite($fp, '                RP:' . $rp   . "\n");
    }
    fwrite($fp, '検索対象ページ番号:' . $pageNumber   . 'ページ目' . "\n");
    $musicNameLength_H = $musicNameLength + 2;
    //ログ出力内容ヘッダ
    $writeText =   str_pad(OUTPUT_MUSICNAME,  $musicNameLength_H ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_DIFFICULTY, LENGTH_DIFFICULTY_H,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_RANK,       LENGTH_RANK_H      ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_SCORE,      LENGTH_SCORE_H     ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_TOPDIFF,    LENGTH_TOPDIFF_H   ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_HIGHDIFF,   LENGTH_HIGHDIFF_H  ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad(OUTPUT_LOWDIFF,    LENGTH_LOWDIFF_H   ,' ',STR_PAD_BOTH) . ' | ' . "\n" ;

    fwrite($fp, $writeText);
    //全国TOP10情報を書き込む
    for ($i=0; $i < $cntTop10Data ; $i++){
      $writeText = str_pad($top10Data[$i]['musicName'],    $musicNameLength ,' ',STR_PAD_RIGHT). ' | ' .
                   str_pad($top10Data[$i]['difficulty'],   LENGTH_DIFFICULTY,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad($top10Data[$i]['rank'],         LENGTH_RANK      ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad($top10Data[$i]['score'],        LENGTH_SCORE     ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad($top10Data[$i]['scoreTopDiff'], LENGTH_TOPDIFF   ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad($top10Data[$i]['scoreHighDiff'],LENGTH_HIGHDIFF  ,' ',STR_PAD_BOTH) . ' | ' .
                   str_pad($top10Data[$i]['scoreLowDiff'], LENGTH_LOWDIFF   ,' ',STR_PAD_BOTH) . ' | ' . "\n" ;

      fwrite($fp, $writeText);
    }
    fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    flock($fp, 3);
    fclose($fp);
  }

  //$message...エラー内容
  //エラー時ログ出力
  function outputErrorLog($message)
  {
    $fp = fopen(LOG_PATH_ERROR, "a");
    flock($fp, 2);
    fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    fwrite($fp,'['. date('Y/m/d H:i:s')    .']'. "\n");
    fwrite($fp, $message . "\n");
    fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    flock($fp, 3);
    fclose($fp);
  }

  //$message...出力内容
  //デバッグ用変数等ログ出力
  function outputDebugLog($message)
  {
    //出力内容が配列の場合はVar_dumpを使用
    if(is_array($message)){
        ob_start();
        var_dump($message);
        $result = ob_get_contents();
        ob_end_clean();
    }
    else{
      $result = $message;
    }

    $fp = fopen(LOG_PATH_DEBUG, "a");
    flock($fp, 2);
    // fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    fwrite($fp,'['. date('Y/m/d H:i:s')    .']'. $result . "\n");
    // fwrite($fp, '────────────────────────────────────────────────────────────────────────────────'."\n");
    flock($fp, 3);
    fclose($fp);
  }

  //$strUrl...Xpath生成対象URL
  //XPathを生成する
  function getXpath($strUrl)
  {
    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $cget = file_cget_contents($strUrl);
    @$dom->loadHTML($cget);
    libxml_clear_errors();
    $xpathGet = new DOMXPath($dom);
    return $xpathGet;
  }

  //$pageNumber...画面上で選択したページ番号
  //ページ番号からハイスコアページのURLを取得する
  function getHighscoreUrl($pageNumber)
  {
    $url = TARGET_ROOT . HIGHSCORE_HTML;
    if($pageNumber > 1){
        $url = $url . '?page=' . $pageNumber .'#l';
    }
    return $url;
  }

?>