//POST前入力チェック処理
function postInputForm(){
    var errorMessage = '';
    var playerName = document.getElementById('playerName').value;
    var rp = document.getElementById('RP').value;
    var rpCheckOnOff = document.getElementsByName('rpCheckOnOff')[0].checked;
    // var rpCheckOnOff_Id = document.getElementById('rpCheckOnOff').checked;
    // console.log(rpCheckOnOff);
    // console.log(rpCheckOnOff_Id);

    //プレイヤー名のチェック処理
    if(playerName == ""){
        errorMessage = 'プレイヤー名を入力してください。';
    }
    else if(playerName.length > 8){
        errorMessage = 'プレイヤー名は8文字以下で入力してください。';
    }
    //RPのチェック処理
    else if(rp == "" && rpCheckOnOff == true){
        errorMessage = 'RPを入力してください。';
    }
    else if(rp.length > 4 && rpCheckOnOff == true){
        errorMessage = 'RPは4桁で入力してください。';
    }
    //エラーが発生している場合メッセージを表示して終了
    if(errorMessage != ""){
        alert(errorMessage);
        return;
    }

    disabledSubmitButton();

    //エラーがない場合は正常処理を行う
    document.inputForm.checkPost = 'POSTED';
    document.forms['inputForm'].submit();
}

function disabledSubmitButton(){
    var submitButton = document.getElementById('output');
    submitButton.disabled = true;
    submitButton.value = 'スターバーストストリーム';
    submitButton.style.background = 'black';
}