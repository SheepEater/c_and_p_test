<?php

/*
入力される値は以下の通り
・入店日時（DateTimeImmutableクラス）
・退店日時（DateTimeImmutableクラス）
・コースの種別

コース	　　　　　　　　　　　　料金（税抜）
・通常料金（入室から1時間）	　　500円
・3時間パック（入室から3時間）	800円
・5時間パック（入室から5時間）	1500円
・8時間パック（入室から8時間）	1900円
延長10分ごと	　　　　　　　　100円 

・22:00〜翌朝5:00までは深夜料金 延長料金15%割増
→深夜は延長10分：115円
・コースは入店時のみ決定することができ、コースの時間を過ぎた場合は自動的に10分ごとに延長料金が発生する。
　1分でも過ぎた場合延長の対象となる。
（1秒でも超過していれば切り上げで延長とする）
・税込、税抜両方の金額を算出できる機能とすること。（税率10%）
*/

/*
~~~~入出力例~~~~
・入力
2025-10-30-22:01:05,2025-10-31-05:22:02,5時間パック

・出力
税込：3547円　税抜：3225円
(税抜　1500円＋1725円＝3225円)
(税込　3225円x1.1＝3547.5円　→3547円(※端数は切り捨てることにする))
(140.95分延長→切り上げて、141分延長→実質150分延長計算→1500円*深夜1.15=1725円)


*/

class Course 
{
    // 入店日時
    private DateTimeImmutable $enteringTime;
    // 退店日時
    private DateTimeImmutable $exitingTime;
    private $course;
    
    // コース一覧
    private $normalCourse = 500;
    private $threeHourPack = 800;
    private $fiveHourPack = 1500;
    private $eightHourPack = 1900;
    // 10分延長料金
    private $extraTenMinutes = 100;
    //　延長料金
    private $extensionFee;

    // 税抜
    private $excludingTax;
    // 税込
    private $taxIncluded;

    // 料金計算
    // public function calculationPrice()
    // {
    //     ~~~~
    //     echo "税込：", $taxIncluded . PHP_EOL;
    //     echo "税抜：", excludingTax;
    // }

}

// 入店時間入力
while(true){
    echo "入店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30-10:32:36）：" . PHP_EOL;
    $startInput = trim(fgets(STDIN));
    if (preg_match('|\d{4}\-\d{1,2}\-\d{1,2}\-\d{2}\:\d{2}\:\d{2}|', $startInput)){
        break;
    }else{
        echo '入力内容が正しくありません。正しく入力してください' . PHP_EOL;
    }

}

// 退店時間入力
while(true){
    echo "退店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30-15:41:26）：" . PHP_EOL;
    $endInput = trim(fgets(STDIN));
    if (preg_match('|\d{4}\-\d{1,2}\-\d{1,2}\-\d{2}\:\d{2}\:\d{2}|', $endInput)){
        break;
    }else{
        echo '入力内容が正しくありません。正しく入力してください' . PHP_EOL;
    }
}

//　コース入力
$course = [
    "通常",
    "3時間パック",
    "5時間パック",
    "8時間パック",
];

while(true){
    echo "コースを {通常、3時間パック、5時間パック、8時間パック} から選択して入力してください" . PHP_EOL;
    echo "（例：3時間パック）：" . PHP_EOL;
    $courseInput = trim(fgets(STDIN));
    if (in_array($courseInput, $course)){
        break;
    }else{
        echo '入力内容が正しくありません。正しく入力してください' . PHP_EOL;
    }
}

/**
 * startInput:開始時間
 * endInput:終了時間
 * courseInput:コース
 * 
 * まず、コースによる金額出力
 * 　税込、税抜ともに出力
 * 次に、利用時間出力
 *  退店時間ー入店時間
 * 延長料金も計算
 * 　合計利用時間ーパック時間＝延長時間
 * 深夜割り増し、
 */

if(){

}


