<?php
    //自作関数の読み込み
    require_once("require/library.php");
    //定数の読み込み
    require_once("require/scraping.php");

    //全10格納配列
    $top10Data = array();
    //全10の数
    $cntTop10Data = 0;
    //曲名最大文字数
    $musicNameMaxLength = 0;

    //セッション開始
    session_start();

    // 出力ボタンが押された場合
    if (isset($_POST["checkPost"])) {
        try{
//◆1.データのセット
            //処理時間の計測
            $time_start = microtime(true);
            //POST値を変数へ保存
            $playerName   = $_POST["playerName"];
            $rp           = $_POST["RP"];

            $rpCheckOnOff = false;

            //チェックされている場合はtrueにする
            if(isset($_POST["rpCheckOnOff"])){
                $rpCheckOnOff = true;
            }

            $difficulty   = $_POST["difficulty"];
            $pageNumber   = $_POST["pageNumber"];

            //セッション値へ保存
            $_SESSION['playerName']   = $playerName;
            $_SESSION['RP']           = $rp;
            $_SESSION['rpCheckOnOff'] = $rpCheckOnOff;
            $_SESSION['difficulty']   = $difficulty;
            $_SESSION['pageNumber']   = $pageNumber;
//◆2.遷移前ページの取得を行う
            //ハイスコアランキング遷移前ページの取得
            $xpath = getXpath(getHighscoreUrl($pageNumber));
            //曲名と遷移先URLをセット
            $musicInfo = getUrlAndMusicData($xpath,$xpath->query('//div[@class="rkHiscoreCv"]'),$difficulty);
//◆3.遷移後ページの処理
            //1曲ごとに処理を行う
            $top10Data = getTop10Data($playerName,$rp,$difficulty,$musicInfo,$rpCheckOnOff);

            outputDebugLog($top10Data);
            $musicNameMaxLength = 20;
            //検索結果ログ出力
            if($top10Data != false){
                outputExecuteLog($top10Data,$cntTop10Data,$playerName,$rp,$pageNumber,$musicNameMaxLength,WRITE_MODE_LOG);
            }

        }catch(\Exception $e){
            outputErrorLog($e);
        }
    }

    //$xp...元Xpath
    //$xpathData...ランキングデータ全体Xpath
    //$difficulty...難易度
    //曲名と遷移先URLを配列にセットする処理
    function getUrlAndMusicData($xp,$xpathData,$difficulty)
    {
        $cnt = 0;
        $temp = false;
        //曲名と遷移先URLをセット
        foreach ($xpathData as $node) {
            //難易度指定が最高難易度の場合
            if($difficulty == 'maxdifficulty'){
                //UNLが存在するか
                $urlUnlimited = $xp->evaluate('string(.//a[@class="rkDiffLink-unlimited"]/@href)',$node);
                //存在しなければMASのリンク先をセット
                if(empty($urlUnlimited)){
                    $setDifficulty = 'master';
                    $urlAhead = $xp->evaluate('string(.//a[@class="rkDiffLink-'. $setDifficulty .'"]/@href)',$node);
                }
                //存在すればUNLのリンク先をセット
                else{
                    $setDifficulty = 'unlimited';
                    $urlAhead = $urlUnlimited;
                }
            }
            //難易度指定がEASY～UNLの場合
            else{
                $urlAhead = $xp->evaluate('string(.//a[@class="rkDiffLink-'. $difficulty .'"]/@href)',$node);
                $setDifficulty = $difficulty;
            }

            //曲に該当難易度のランキングが存在しない場合は処理自体を行わないので次へ
            if(empty($urlAhead)){
                continue;
            }
            $temp[$cnt++] = [
                'name'  => $xp->evaluate('string(.//p[@class="rkHiName"])',$node),
                'difficulty'  => strtoupper($setDifficulty),
                'link'  => TARGET_ROOT . substr($urlAhead,2),
            ];
        }
        return $temp;
    }

    //$playerName...入力プレイヤー名
    //$rp...........入力RP
    //$difficulty...難易度
    //$musicInfo....曲情報配列(曲名とリンク)
    //$rpCheckOnOff...RPチェック可否
    //1曲ごとに検索結果を収納する
    function getTop10Data($playerName,$rp,$difficulty,$musicInfo,$rpCheckOnOff)
    {
        //全10スコアが見つかった時に加算するのでグローバル定義
        global $cntTop10Data;
        global $musicNameMaxLength;
        $musicProcessCount = count($musicInfo);
        $temp = false;

        //リンクがセットされた曲の数だけ処理を行う
        for ($i=0; $i < $musicProcessCount; $i++) {
            //遷移後ページの取得
            $xpathAfter = getXpath($musicInfo[$i]['link']);
            //ランキングに乗っているプレイヤーの情報全体を取得
            $rkData = getRankingData($xpathAfter,$xpathAfter->query('//div[@class="rkHiScoreDetail"]'));

            //ランキング内の10人を一人ずつ見ていって自分が見つかったら情報を入れて終了
            for ($j=0; $j <RANKING_PLAYER_COUNT ; $j++) {

                //RPを含めた検索を行う
                if($rpCheckOnOff == true){
                    //プレイヤー名またはRPが一致しない場合次のプレイヤーデータを確認
                    if($playerName != $rkData[$j]['player'] || $rp != $rkData[$j]['rp']){
                        continue;
                    }
                }
                //RPを含めた検索を行わない
                else{
                    //プレイヤー名が一致しない場合次のプレイヤーデータを確認
                    if($playerName != $rkData[$j]['player']){
                        continue;
                    }
                }

                //差分を設定
                //1位......下位差分のみ
                //2～9位...上位差分、下位差分、全1差分、
                //10位.....上位差分、全1差分
                switch ($rkData[$j]['rank']) {
                    //1位
                    case '1':
                        $scoreHighDiff = '-----';
                        $scoreLowDiff  = $rkData[$j]['score'] - $rkData[$j+1]['score'];
                        $scoreLowDiff  = '-' . $scoreLowDiff . 'pt';
                        $scoreTopDiff  = '-----';
                        break;
                    //10位
                    case '10':
                        $scoreHighDiff = $rkData[$j-1]['score'] - $rkData[$j]['score'];
                        $scoreHighDiff  = '+' . $scoreHighDiff . 'pt';
                        $scoreLowDiff  = '-----';
                        $scoreTopDiff  = $rkData[0]['score']  - $rkData[$j]['score'];
                        $scoreTopDiff  = '+' . $scoreTopDiff . 'pt';
                        break;
                    //2～9位
                    default:
                        $scoreHighDiff = $rkData[$j-1]['score'] - $rkData[$j]['score'];
                        $scoreHighDiff  = '+' . $scoreHighDiff . 'pt';
                        $scoreLowDiff  = $rkData[$j]['score'] - $rkData[$j+1]['score'];
                        $scoreLowDiff  = '-' . $scoreLowDiff . 'pt';
                        $scoreTopDiff  = $rkData[0]['score']  - $rkData[$j]['score'];
                        $scoreTopDiff  = '+' . $scoreTopDiff . 'pt';
                        break;
                }
                $temp[$cntTop10Data++] = [
                    'musicName'     => $musicInfo[$i]['name'],
                    'difficulty'    => $musicInfo[$i]['difficulty'],
                    'rank'          => $rkData[$j]['rank'],
                    'score'         => $rkData[$j]['score'],
                    'scoreHighDiff' => $scoreHighDiff,
                    'scoreLowDiff'  => $scoreLowDiff,
                    'scoreTopDiff'  => $scoreTopDiff,
                ];
                break;
            }
        }

        return $temp;
    }

    //$xp...元Xpath
    //$xpathData...ランキングデータ全体Xpath
    //ランキングデータの集計をする処理
    function getRankingData($xp,$xpathData)
    {
        $cnt = 0;
        //1位から10位までのデータをそれぞれ種類別に格納
        foreach ($xpathData as $node) {
            $rkName = $xp->evaluate('string(.//p[@class="rkName"])',$node);
            $rkRp   = substr($xp->evaluate('string(.//p[@class="rkRp"]/span)',$node),0,4);
            $temp[$cnt++] = [
                'rank'    => $xp->evaluate('string(div/div[@class="rkRankCv"])',$node),
                'player'  => $rkName,
                'rp'      => $rkRp,
                'score'   => $xp->evaluate('string(.//p[@class="rankScore"])',$node),
            ];

        }
        return $temp;
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="robots" content="" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<link rel="stylesheet" href="css/All.css">
<link rel="shortcut icon" href="images/favicon.ico">
<title>ハイスコアランキング全国トップ10ユーザー抽出ツール</title>
<style type="text/css"></style>
<script type="text/javascript" src="js/highScoreOutput.js"></script>
<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
</head>
<body>
<div class="main">
    <h1>ハイスコアランキング全国トップ10ユーザー抽出ツール</h1>
    <a href="howto.php"><h2>◆このツールについて</h2></a>
    <!-- POST対象フォーム -->
    <form id="inputForm" name="inputForm" action="" method="POST">
        <fieldset>
            <legend>入力フォーム</legend>
            <table class="inputTable">
                <thead></thead>
                <tbody>
                    <tr>
                        <td class="inputTdCaption"><label for="playerName">プレイヤー名:</label></td>
                        <td>
                            <span class="textboxArea"><input type="text" id="playerName" name="playerName" value="<?php 
                            if(isset($_SESSION['playerName'])){
                                echo $_SESSION['playerName'];
                            } ?>" placeholder="プレイヤー名を入力">
                        </td>
                        <td class="inputCheckBox" bgcolor="gray">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="inputTdCaption"><label for="RP">RP(上4桁):</label></td>
                        <td>
                            <input type="text" id="RP" name="RP" placeholder="RP(上4桁)を入力"  value="<?php 
                            if(isset($_SESSION['rp'])){
                                echo $_SESSION['rp'] ;
                            } ?>" maxlength="4">
                        </td>
                        <td class="inputCheckBox">
                            <input type="checkBox" name="rpCheckOnOff" class="rpCheckOnOff">
                        </td>
                    </tr>
                    <tr>
                        <td class="inputTdCaption"><label for="難易度">難易度:</label></td>
                        <td>
                        <select id="difficulty" name="difficulty" class="selectDifficulty">
<?php
    //セッション値にプレイヤー名とRPが保存されている場合セレクトボックスを動的に生成する
    if(isset($_SESSION['playerName'])){
        //セッション値と一致する場合selectedで生成
        foreach ($sc['difficulty'] as $key => $value) {
            if($_SESSION['difficulty'] === $key){
                echo '<option value="' . $key . '" selected>'. $value .'</option>';
            }
            else{
                echo '<option value="' . $key . '">'. $value .'</option>';
            }
        }
    }
    else{
?>
                            <option value="maxdifficulty" selected>最高難易度</option>
                            <option value="unlimited">UNLIMITED</option>
                            <option value="master">MASTER</option>
                            <option value="hard">HARD</option>
                            <option value="standard">STANDARD</option>
                            <option value="easy">EASY</option>
<?php
    }
?>
                        </select>
                        </td>
                        <td class="inputCheckBox" bgcolor="gray"></td>
                    </tr>
                    <tr>
                        <td class="inputTdCaption"><label for="ページNo">ページNo:</label></td>
                        <td>
                        <select id="pageNumber" name="pageNumber" class="selectPageNumber">
<?php
    //セッション値にプレイヤー名とRPが保存されている場合セレクトボックスを動的に生成する
    if(isset($_SESSION['playerName'])){
?>
<?php
        //セッション値と一致する場合selectedで生成
        for ($i=1; $i <= PAGE_NUMBER_LAST ; $i++) { 
            if($_SESSION['pageNumber'] == $i){
                echo '<option value="' . $i . '" selected>'. $i .'</option>';
            }
            else{
                echo '<option value="' . $i . '">'. $i .'</option>';
            }
        }
?>
<?php
    }
    else{
?>
                            <option value="1" selected>1</option>
                            <option value="2">2</option>
                            <option value="3" >3</option>
                            <option value="4" >4</option>
                            <option value="5" >5</option>
                            <option value="6" >6</option>
                            <option value="7" >7</option>
                            <option value="8" >8</option>
                            <option value="9" >9</option>
                            <option value="10" >10</option>
                            <option value="11" >11</option>
                            <option value="12" >12</option>
<?php
    }
?>
                        </select>
                        </td>
                        <td class="inputCheckBox" bgcolor="gray">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <br>
            <p>RPのチェックボックスを無しにした場合、プレイヤー名のみでの検索を行います。<br>有りの場合はRPも含めた検索を行います。<br>(プレイヤー名重複の可能性がある場合に使用してください)</p>
            <input type="hidden" id="checkPost" name="checkPost" value="">
            <input type="button" id="output" name="output" value="出力" onclick="postInputForm();">
<?php
    //処理時間計測
    if(isset($_SESSION['playerName']) && isset($time_start)){
?>
        <p>処理時間:<?php echo substr(microtime(true) - $time_start,0,4)  . '秒' ; ?></p>
<?php
    }
?>
        </fieldset>
    </form>

    <br>

    <fieldset>
        <legend>出力データ</legend>

<?php
    //結果が無い時の判定
    if(isset($_SESSION['playerName']) && isset($time_start) && $cntTop10Data == 0){
?>
        <p>検索結果存在しなくて草</p>
<?php
    }
    else{
?>

        <!-- 出力ボタン押下後に表示 -->
        <table class="dataTable">
            <thead>
                <tr>
                    <th><?php echo OUTPUT_MUSICNAME ?></th>
                    <th><?php echo OUTPUT_DIFFICULTY ?></th>
                    <th><?php echo OUTPUT_RANK ?></th>
                    <th><?php echo OUTPUT_SCORE ?></th>
                    <th><?php echo OUTPUT_TOPDIFF ?></th>
                    <th><?php echo OUTPUT_HIGHDIFF ?></th>
                    <th><?php echo OUTPUT_LOWDIFF ?></th>
                </tr>
            </thead>
            <tbody>
<?php
    //データの取得件数から表の内容を生成する
    for ($i=0; $i < $cntTop10Data ; $i++)
    {
?>
                <tr>
                    <td style="text-align: right;"><?php echo $top10Data[$i]['musicName']; ?></td>
                    <td style="text-align: right;"><?php echo $top10Data[$i]['difficulty']; ?></td>
                    <td style="text-align: center;"><?php echo $top10Data[$i]['rank']; ?></td>
                    <td style="text-align: center;"><?php echo $top10Data[$i]['score']; ?></td>
                    <td style="text-align: center;"><?php echo $top10Data[$i]['scoreTopDiff']; ?></td>
                    <td style="text-align: center;"><?php echo $top10Data[$i]['scoreHighDiff']; ?></td>
                    <td style="text-align: center;"><?php echo $top10Data[$i]['scoreLowDiff']; ?></td>
                </tr>
<?php
    }
?>
            </tbody>
        </table>
<?php
    }
?>
        <br>
    </fieldset>
</div>
</body>
</html>