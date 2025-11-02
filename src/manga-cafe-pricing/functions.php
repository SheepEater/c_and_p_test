<?php
declare(strict_types=1);
/**
 * 日時入力チェック。　正しい場合、DateTimeImmutableを返す
 * 'Y-m-d H:i:s'
 */
function parseDateTimeInput(string $input): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input, new DateTimeZone('Asia/Tokyo'));
    if ($date === false) {
        return null;
    }

    $errors = DateTimeImmutable::getLastErrors();
    if ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0)) {
        return null;
    }

    return $date;
}

/**
 * 経過時間を秒単位で計算し、分単位を切り上げて計算
 */
function calculateTotalMinutes(DateTimeImmutable $start, DateTimeImmutable $end): int
{
    $diff = $start->diff($end);
    $totalSeconds =
        ($diff->days * 24 * 60 * 60) +
        ($diff->h * 60 * 60) +
        ($diff->i * 60) +
        $diff->s;

    return (int)ceil($totalSeconds / 60);
}

/**
 * 税込価格計算。税率10%
 */
function calculateTaxIncluded(int $price): int
{
    return (int)floor($price * 1.1);
}

/**
 * 延長料金計算
 * 10分-100円、（深夜帯）10分-115円
 * 22:00 ~ 翌05:00 までが深夜帯。100円の15％割増
 */
function calculateExtensionFee(DateTimeImmutable $extensionStart, DateTimeImmutable $extensionEnd): int{
    if ($extensionEnd <= $extensionStart) {
        return 0;
    }

    $baseUnitPrice = 100; // 通常の10分延長料金
    $nightUnitPrice = 115; // 深夜帯の10分延長料金
    $tenMinutesToSeconds = 600; // 10分→600秒

    $totalSeconds = $extensionEnd->getTimestamp() - $extensionStart->getTimestamp();
    $tenMinutesCount = (int)ceil($totalSeconds / $tenMinutesToSeconds);

    $fee = 0;
    $currentStart = $extensionStart;

    for ($i = 0; $i < $tenMinutesCount; $i++) {
        $slotEnd = $currentStart->modify('+10 minutes');
        $actualSlotEnd = $slotEnd < $extensionEnd ? $slotEnd : $extensionEnd;

        if(overlapsNightPeriod($currentStart, $actualSlotEnd)){
            $fee += $nightUnitPrice;
        } else {
            $fee += $baseUnitPrice;
        }
        $currentStart = $slotEnd;
    }

    return $fee;
}

/**
 * 指定した時間帯が深夜時間（22:00~翌日05:00）のどこかと重なっているか判定する
 */
function overlapsNightPeriod(DateTimeImmutable $start, DateTimeImmutable $end): bool
{
    if ($end <= $start) {
        return false;
    }

    $currentDay = $start->setTime(0, 0);
    $endDay = $end->setTime(0, 0);

    while ($currentDay->getTimestamp() <= $endDay->getTimestamp()) {
        $nightStart = $currentDay->setTime(22, 0);
        // 翌05:00
        $nightEnd = $nightStart->modify('+7 hours');

        if ($start < $nightEnd && $end > $nightStart) {
            return true;
        }

        $currentDay = $currentDay->modify('+1 day');
    }

    return false;
}
