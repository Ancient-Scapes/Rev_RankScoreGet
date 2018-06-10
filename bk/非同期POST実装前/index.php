<?php
    //自作関数の読み込み
    require_once("require/library.php");
    //スクレイピング用コンフィグの読み込み
    require_once("require/scraping.php");

    //データ取得件数を初期化
    $cntGetData = 2;

    $arrGetData = array(
        0 => array(
            "name"       => 'Minerva',
            "difficulty" => 'MASTER',
            "rank"       => '6',
            "score"      => '84000'),
        1 => array(
            "name"       => 'WE GO',
            "difficulty" => 'UNLIMITED',
            "rank"       => '1',
            "score"      => '54800'),
    );



    // 出力ボタンが押された場合
    if (isset($_POST["output"])) {
        //セッション開始
        session_start();

        try{
            //入力チェック
            if (empty($_POST["playername"])) {
                throw new Exception('プレイヤー名が未入力です。');
            } else if (empty($_POST["RP"])) {
                throw new Exception('RPが未入力です。');
            }

            //プレイヤー名およびRPの格納
            if (!empty($_POST["playername"]) && !empty($_POST["RP"])) {
                // 入力したプレイヤー名とRPを格納
                $playername = $_POST["playername"];
                $RP = $_POST["RP"];
                // $_SESSION['playername'] = $_POST['playername'];
                // $_SESSION['RP'] = $_POST['RP'];
            }

            echo str_pad(" ",4096)."<br />\n";
            ob_end_flush();
            ob_start('mb_output_handler');
            $arrURL = array();

            for ($i=0; $i < 2; $i++) { 
                $arrURL[$i] = $sc['targetHtml'];
                if($i > 0){
                    $arrURL[$i] = $arrURL[$i] . '?page=' . $i .'#l';
                }
                $doc = file_cget_contents($sc['targetHtml']);
                $dom = new DOMDocument();
                @$dom->loadHTML($doc);


                sleep(1);
                echo($i . '件処理を完了 <br/>');
                ob_flush();
                flush();
            }

        }catch(Exception $e){
            print($e);
        }
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
<title>PHPのテストページ</title>
<style type="text/css">
</style>
<script type="text/javascript">
</script>
</head>
<body>
<div class="main">
    <h1>ハイスコアランキング全国トップ10ユーザー抽出ツール</h1>
    <form id="inputForm" name="inputForm" action="" method="POST">
        <fieldset>
            <legend>入力フォーム</legend>
            <table class="inputTable">
                <thead></thead>
                <tbody>
                    <tr>
                        <td style="text-align: right;"><label for="playername">プレイヤー名:</label></td>
                        <td>
                            <span class="textboxArea"><input type="text" id="playername" name="playername" placeholder="プレイヤー名を入力" value="もぎもぎフルーツ">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label for="RP">RP(上4桁):</label></td>
                        <td>
                            <input type="text" id="RP" name="RP" value="2227" placeholder="RP(上4桁)を入力">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label for="難易度">難易度:</label></td>
                        <td>
                        <select id="difficulty" name="difficulty" class="selectDifficulty">
                            <option value="unl" selected>UNLIMITED</option>
                            <option value="mas">MASTER</option>
                            <option value="hrd">HARD</option>
                            <option value="std">STANDARD</option>
                            <option value="esy">EASY</option>
                        </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <input type="submit" id="output" name="output" value="出力">
        </fieldset>
    </form>

    <br>

    <fieldset>
        <legend>出力データ</legend>
        <!-- 出力ボタン押下後に表示 -->
        <table class="dataTable">
            <thead>
                <tr>
                    <th>曲名</th>
                    <th>難易度</th>
                    <th>順位</th>
                    <th>スコア</th>
                </tr>
            </thead>
            <tbody>
<?php
// 出力ボタンが押された場合
if (isset($_POST["output"])) {
    //データの取得件数から表の内容を生成する
    for ($i=0; $i < $cntGetData ; $i++)
    {
?>
                <tr>
                    <td style="text-align: right;"><?php print($arrGetData[$i]['name']); ?></td>
                    <td style="text-align: right;"><?php print($arrGetData[$i]['difficulty']); ?></td>
                    <td style="text-align: center;"><?php print($arrGetData[$i]['rank']); ?></td>
                    <td style="text-align: center;"><?php print($arrGetData[$i]['score']); ?></td>
                </tr>
<?php
    }
}
?>
            </tbody>
        </table>
        <br>
<?php
// 出力ボタンが押された場合
if (isset($_POST["output"])) {
    //データの取得件数から表の内容を生成する
    for ($i=0; $i < 2 ; $i++)
    {
?>
        <p><?php print($i+1 . '回目出力URL:' . $arrURL[$i]); ?></p>
<?php
    }
}
?>

    </fieldset>
</div>
</body>
</html>