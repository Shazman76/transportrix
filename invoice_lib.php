<?php
/**
 * Shared invoice helpers: company defaults, numbering, MYR amount in words.
 */

function invoice_company_defaults(): array
{
    return [
        'name' => 'MSF MAJU GLOBAL ( CA0364558-A )',
        'address' => [
            'NO 373 LORONG 10 TAMAN DESA DAMAI',
            '28700 BENTONG',
            'PAHANG DARUL MAKMUR',
        ],
        'tel' => 'TEL: 016 6274287 / 016-376 8526',
        'bank_instructions' => 'Payment to be made to MAYBANK A/C : 556132079959 (MSF MAJU GLOBAL)',
        'cheque_instructions' => 'All cheques to be crossed and make payable to MSF MAJU GLOBAL',
        'manager_title' => 'MANAGER',
        'manager_name' => 'SHARIL FITRI BIN SHAFIEE',
    ];
}

/**
 * Web path (relative to site root) for the invoice header logo.
 * Place files in /image — first match wins: msf_logo.png, msf_logo.jpg, logo.jpg, logo.png, logofsppp.png
 */
function invoice_logo_relative_url(): ?string
{
    $candidates = ['msf_logo.png', 'msf_logo.jpg', 'logo.jpg', 'logo.png', 'logofsppp.png'];
    foreach ($candidates as $file) {
        $full = __DIR__ . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . $file;
        if (is_file($full)) {
            return 'image/' . $file;
        }
    }
    return null;
}

function invoice_escape(mysqli $connect, ?string $s): string
{
    if ($s === null) {
        return '';
    }
    return mysqli_real_escape_string($connect, $s);
}

function invoice_format_dmY(?string $ymd): string
{
    if (!$ymd) {
        return '';
    }
    $ts = strtotime($ymd);
    if ($ts === false) {
        return $ymd;
    }
    return date('d-m-Y', $ts);
}

function invoice_format_dmy_short(?string $ymd): string
{
    if (!$ymd) {
        return '';
    }
    $ts = strtotime($ymd);
    if ($ts === false) {
        return $ymd;
    }
    return date('d-m-y', $ts);
}

function invoice_next_number(mysqli $connect): string
{
    $year = date('Y');
    $prefix = 'MSF/' . $year . '/';
    $esc = invoice_escape($connect, $prefix);
    $q = mysqli_query(
        $connect,
        "SELECT invoice_no FROM invoice WHERE invoice_no LIKE '{$esc}%' ORDER BY invoice_no DESC LIMIT 1"
    );
    $next = 1;
    if ($q && ($row = mysqli_fetch_assoc($q))) {
        $parts = explode('/', $row['invoice_no']);
        $last = (int) end($parts);
        if ($last > 0) {
            $next = $last + 1;
        }
    }
    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function invoice_under_thousand_words(int $n, array $ones, array $tens): string
{
    $h = intdiv($n, 100);
    $r = $n % 100;
    $out = [];
    if ($h > 0) {
        $out[] = $ones[$h] . ' HUNDRED';
    }
    if ($r > 0) {
        if ($r < 20) {
            $out[] = $ones[$r];
        } else {
            $t = intdiv($r, 10);
            $u = $r % 10;
            $s = $tens[$t];
            if ($u > 0) {
                $s .= ' ' . $ones[$u];
            }
            $out[] = $s;
        }
    }
    return implode(' ', $out);
}

function invoice_int_to_words(int $n): string
{
    if ($n === 0) {
        return 'ZERO';
    }
    if ($n < 0) {
        return 'MINUS ' . invoice_int_to_words(-$n);
    }

    $ones = [
        '', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE',
        'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN',
    ];
    $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];

    $chunks = [];
    $tmp = $n;
    while ($tmp > 0) {
        $chunks[] = $tmp % 1000;
        $tmp = intdiv($tmp, 1000);
    }

    $scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
    $parts = [];
    for ($i = count($chunks) - 1; $i >= 0; $i--) {
        $c = $chunks[$i];
        if ($c === 0) {
            continue;
        }
        $w = invoice_under_thousand_words($c, $ones, $tens);
        if ($i > 0) {
            $w .= ' ' . $scales[$i];
        }
        $parts[] = $w;
    }

    return implode(' ', $parts);
}

function invoice_ringgit_in_words(float $amount): string
{
    $amount = round($amount, 2);
    $ringgit = (int) floor($amount + 1e-9);
    $cents = (int) round(($amount - $ringgit) * 100);
    if ($cents === 100) {
        $ringgit += 1;
        $cents = 0;
    }

    $r = invoice_int_to_words($ringgit);
    $c = invoice_int_to_words($cents);

    return 'RINGGIT MALAYSIA ' . $r . ' AND ' . $c . ' CENT ONLY';
}

function invoice_lines_total(array $lines): float
{
    $sum = 0.0;
    foreach ($lines as $row) {
        $sum += (float) ($row['line_total'] ?? 0);
    }
    return round($sum, 2);
}
