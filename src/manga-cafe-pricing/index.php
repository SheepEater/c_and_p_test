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
=======================================================================
*/

/**
 * 日時入力チェック。　正しい場合、DateTimeImmutableを返す
 */
function parseDateTimeInput(string $input)
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input, new DateTimeZone('Asia/Tokyo'));
    if($date === false){
        return null;
    }

    $errors = DateTimeImmutable::getLastErrors();
    if($errors !== false){
        if(($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0){
            return null;
        }
    }
    return $date;
}

/**
 * 入店時間入力
 */
$startTime = null;
while(true){
    echo "入店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30 10:32:36）：" . PHP_EOL;
    $startInput = trim(fgets(STDIN));

    $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startInput, new DateTimeZone('Asia/Tokyo'));
    $errors = DateTimeImmutable::getLastErrors();

    $parsed = parseDateTimeInput($startInput);
    if($parsed !== null){
        $startTime = $parsed;
        break;
    }
    echo "入力内容が正しくありません。正しい日時形式で入力してください" . PHP_EOL;
}
$startInput = $startTime->format('Y-m-d H:i:s');

/**
 * 退店時間入力
 */
$endTime = null;
while(true){
    echo "退店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30 15:41:26）：" . PHP_EOL;
    $endInput = trim(fgets(STDIN));

    $parsed = parseDateTimeInput($endInput);
    if($parsed === null){
        echo "入力内容が正しくありません。正しい日時形式で入力してください" . PHP_EOL;
        continue;
    }

    if($parsed <= $startTime){
        echo "入店日時より後の日時を入力してください" . PHP_EOL;
        continue;
    }

    $endTime = $parsed;
    break;
}
$endInput = $endTime->format('Y-m-d H:i:s');

// コース配列。キー（a,b,c,d）、バリュー（name,price,time）
$course = [
    "a" => [
        "name" => "通常",
        "price" => 500,
        "time" => 60,
    ],
    "b" => [
        "name" => "3時間パック",
        "price" => 800,
        "time" => 180,
    ],
    "c" => [
        "name" => "5時間パック",
        "price" => 1500,
        "time" => 300,
    ],
    "d" => [
        "name" => "8時間パック",
        "price" => 1900,
        "time" => 480 ,
    ],
];

/**
 * コース入力
 */
while(true){
    echo "コースを {a:通常、b:3時間パック、c:5時間パック、d:8時間パック} から選択して入力してください" . PHP_EOL;
    echo "例(3時間パックの場合：b)：" . PHP_EOL;
    $courseInput = trim(fgets(STDIN));
    if (array_key_exists($courseInput, $course)){
        // $selectedCourse ：選んだコース名
        $selectedCourse = $course[$courseInput];
        echo "コース「{$selectedCourse['name']}」が選択されました"  . PHP_EOL;
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
 * まず、コースによる金額出力. OK
 * 　税込、税抜ともに出力. OK
 * 次に、利用時間出力. OK
 *  退店時間ー入店時間. OK
 * 延長料金も計算 OK
 * 　合計利用時間ーパック時間＝延長時間
 * 深夜割り増し、
 */

// 税抜価格表示（コース料金）
// echo "税抜(コースのみ)：{$selectedCourse['price']}円" . PHP_EOL;

// 税込計算
// if($selectedCourse['price'] == $selectedCourse['price']){
//     $taxIncluded = $selectedCourse['price'] * 1.1;
// }

// 税込価格表示（コース料金）
// echo "税込(コースのみ)：{$taxIncluded}円" . PHP_EOL;


/**
 *  利用時間計算(仮)　elapsedTime
 * 入店時間：startTime
 * 退店時間：endTime 、、、startTimeよりも大きい時間じゃないとだめ　validateする
 * 
 * 退店ー入店
 */

// $startTime = new DateTimeImmutable($startInput, new DateTimeZone("Asia/Tokyo"));
// $endTime = new DateTimeImmutable($endInput, new DateTimeZone("Asia/Tokyo"));

// 経過時間計算
$elapsedTime = $startTime->diff($endTime);

echo "入店：", $startTime->format("Y-m-d H:i:s") . PHP_EOL;
echo "退店：", $endTime->format("Y-m-d H:i:s") . PHP_EOL;
echo "経過時間：", $elapsedTime->format("%H:%I:%S") . PHP_EOL;

// 経過時間を秒単位で計算し、分単位を切り上げて計算
$totalSeconds = 
    ($elapsedTime->days * 24 * 60 * 60) +
    ($elapsedTime->h * 60 * 60) +          // 時間を分に換算
    ($elapsedTime->i * 60) +
    $elapsedTime->s;
$totalMinutes = (int)ceil($totalSeconds / 60);
// 経過時間を分に計算
// $totalMinutes =
//     ($elapsedTime->days * 24 * 60) +  // 日数があれば分に換算
//     ($elapsedTime->h * 60) +          // 時間を分に換算
//     $elapsedTime->i;
// echo "合計分数：{$totalMinutes}" . PHP_EOL;

// コースの設定時間よりも、経過時間が大きければ延長料金を計算
$extensionFee = 0;
if($selectedCourse['time'] < $totalMinutes){
    $extensionStart = $startTime->modify("+{$selectedCourse['time']} minutes");
    $extensionFee = calculateExtensionFee($extensionStart, $endTime);
    $totalPrice = $extensionFee + $selectedCourse['price'];
    // echo "延長料金：{$extensionFee}円" . PHP_EOL;
    echo "税抜価格：{$totalPrice}円" . PHP_EOL;
    calculateTaxIncluded($totalPrice);

} else {
    $totalPrice = $selectedCourse['price'];
    echo "税抜(コースのみ)：{$totalPrice}円" . PHP_EOL;
    calculateTaxIncluded($totalPrice);
}
// 税込計算＝＝＝＝＝＝＝＝＝＝＝＝
function calculateTaxIncluded($totalPrice){
    if($totalPrice == $totalPrice){
        $taxIncluded = $totalPrice * 1.1;
    }
    // 税込価格表示
    echo "税込価格：",floor($taxIncluded),"円" . PHP_EOL;
}
// if($totalPrice == $totalPrice){
//     $taxIncluded = $totalPrice * 1.1;
// }
// // 税込価格表示
// echo "税込価格：{$taxIncluded}円" . PHP_EOL;
// ===========================

/**
 * 延長料金計算
 * 10分-100円、（深夜帯）10分-115円
 * 22:00 ~ 翌05:00 までが深夜帯。100円の15％割増
 */
function calculateExtensionFee(DateTimeImmutable $extensionStart, DateTimeImmutable $extensionEnd)
{
    if($extensionEnd <= $extensionStart){
        return 0;
    }

    $baseUnitePrice = 100; // 通常の10分延長料金
    $nightUnitPrice = 115; // 深夜帯の10分延長料金
    $tenMinutesToSeconds = 600; // 10分→600秒

    $totalSeconds = $extensionEnd->getTimestamp() - $extensionStart->getTimestamp();
    // ceil(int $num) 端数の切り上げ
    // tenMinutesCount = 合計時間（秒）/　600秒（10分）
    $tenMinutesCount = (int)ceil($totalSeconds / $tenMinutesToSeconds);

    $fee = 0;
    $currentStart = $extensionStart;

    for($i = 0; $i < $tenMinutesCount; $i++){
        $slotEnd = $currentStart->modify("+10 minutes");
        $actualSlotEnd = $slotEnd < $extensionEnd ? $slotEnd : $extensionEnd;

        if(overlapsNightPeriod($currentStart, $actualSlotEnd)){
            $fee += $nightUnitPrice;
        } else {
            $fee += $baseUnitePrice;
        }

        $currentStart = $slotEnd;
    }

    return $fee;
}

/**
 * 指定した時間帯が深夜時間（22:00~翌日05:00）のどこかと重なっているか判定する
 */
function overlapsNightPeriod(DateTimeImmutable $start, DateTimeImmutable $end)
{
    if($end <= $start){
        return false;
    }

    $currentDay = $start->setTime(0, 0);
    $endDay = $end->setTime(0 ,0);

    while($currentDay->getTimestamp() <= $endDay->getTimestamp()){
        $nightStart = $currentDay->setTime(22, 0);
        // 翌05:00
        $nightEnd = $nightStart->modify("+7 hours");

        if($start < $nightEnd && $end > $nightStart){
            return true;
        }

        $currentDay = $currentDay->modify("+1 day");
    }
    
    return false;
}
