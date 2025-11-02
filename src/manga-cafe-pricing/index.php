<?php
declare(strict_types=1);
// functions.php読み込み
require __DIR__ . '/functions.php';

// コース定義をcourses.phpから読み込む
$courses = require __DIR__ . '/courses.php';

/**
 * 入店時間入力
 */
$startTime = null;
while(true){
    echo "入店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30 10:32:36）：" . PHP_EOL;
    $inputRaw = fgets(STDIN);
    if ($inputRaw === false) {
        echo "入力が取得できませんでした。再度入力してください" . PHP_EOL;
        continue;
    }
    $input = trim($inputRaw);

    $parsed = parseDateTimeInput($input);
    if($parsed !== null){
        $startTime = $parsed;
        break;
    }

    echo "入力内容が正しくありません。正しい日時形式で入力してください" . PHP_EOL;
}

/**
 * 退店時間入力
 */
$endTime = null;
while(true){
    echo "退店日時を入力してください" . PHP_EOL;
    echo "（例：2025-10-30 15:41:26）：" . PHP_EOL;
    $inputRaw = fgets(STDIN);
    if ($inputRaw === false) {
        echo "入力が取得できませんでした。再度入力してください。" . PHP_EOL;
        continue;
    }
    $input = trim($inputRaw);

    $parsed = parseDateTimeInput($input);
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

/**
 * コース入力
 */
while(true){
    echo "コースを {a:通常、b:3時間パック、c:5時間パック、d:8時間パック} から選択して入力してください" . PHP_EOL;
    echo "例(3時間パックの場合：b)：" . PHP_EOL;
    $courseInputRaw = fgets(STDIN);
    if ($courseInputRaw === false) {
        echo "入力が取得できませんでした。再度入力してください。" . PHP_EOL;
        continue;
    }
    $courseInput = trim($courseInputRaw);
    if (array_key_exists($courseInput, $courses)){
        // $selectedCourse ：選んだコース名
        $selectedCourse = $courses[$courseInput];
        echo "コース「{$selectedCourse['name']}」が選択されました"  . PHP_EOL;
        break;
    }else{
        echo '入力内容が正しくありません。正しく入力してください' . PHP_EOL;
    }
}

// 経過時間計算
$elapsedTime = $startTime->diff($endTime);

echo "入店：", $startTime->format("Y-m-d H:i:s") . PHP_EOL;
echo "退店：", $endTime->format("Y-m-d H:i:s") . PHP_EOL;
// echo "経過時間：", $elapsedTime->format("%H:%I:%S") . PHP_EOL;

$totalMinutes = calculateTotalMinutes($startTime, $endTime);
$extensionFee = 0;

// コース時間を超えた場合のみ、延長料金と開始時刻を求める
if ($totalMinutes > $selectedCourse['time']) {
    $extensionStart = $startTime->modify("+{$selectedCourse['time']} minutes");
    $extensionFee = calculateExtensionFee($extensionStart, $endTime);
}

$taxExcludedTotal = $selectedCourse['price'] + $extensionFee;
$taxIncludedTotal = calculateTaxIncluded($taxExcludedTotal);

echo "税抜価格：{$taxExcludedTotal}円" . PHP_EOL;
echo "税込価格：{$taxIncludedTotal}円" . PHP_EOL;
