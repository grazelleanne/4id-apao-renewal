<?php
declare(strict_types=1);

$p = isset($p) && is_array($p) ? $p : [];
$inspection = isset($inspection) && is_array($inspection) ? $inspection : [];
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$formatDate = static function (mixed $value, string $fallback = ''): string {
    if (!is_string($value) || trim($value) === '') {
        return $fallback;
    }
    try {
        return (new DateTimeImmutable($value))->format('d F Y');
    } catch (Throwable) {
        return $fallback;
    }
};
$imageData = static function (string $path): string {
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = match ($extension) {
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        default => 'image/png',
    };
    $contents = file_get_contents($path);
    return $contents === false ? '' : 'data:' . $mime . ';base64,' . base64_encode($contents);
};
$publicImage = static fn (string $name): string => APP_ROOT . '/public/images/' . basename($name);
$signatureSource = static function (mixed $stored, string $defaultFile) use ($imageData, $publicImage): string {
    if (is_string($stored)
        && preg_match('#^data:image/(?:png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=]+$#', $stored)) {
        return $stored;
    }
    if (is_string($stored) && str_starts_with($stored, '/images/')) {
        $storedImage = $imageData($publicImage(basename($stored)));
        if ($storedImage !== '') {
            return $storedImage;
        }
    }
    return $imageData($publicImage($defaultFile));
};

$partLabels = [
    'barrel' => 'Barrel', 'slide' => 'Slide', 'recoil_spring_assembly' => 'Recoil Spring Assembly',
    'firing_pin' => 'Firing Pin / Striker', 'firing_pin_safety' => 'Firing Pin Safety',
    'extractor' => 'Extractor', 'rear_sight' => 'Rear Sight', 'front_sight' => 'Front Sight',
    'frame' => 'Frame', 'magazine' => 'Magazine', 'magazine_catch' => 'Magazine Catch',
    'magazine_catch_spring' => 'Magazine Catch Spring', 'trigger' => 'Trigger',
    'trigger_spring' => 'Trigger Spring', 'trigger_bar' => 'Trigger Bar',
    'slide_stop_lever' => 'Slide Stop Lever', 'trigger_pin' => 'Trigger Pin',
    'trigger_mechanism_housing' => 'Trigger Housing / Mechanism Housing',
    'trigger_housing_pin' => 'Trigger Housing Pin', 'locking_block' => 'Locking Block',
    'locking_block_pin' => 'Locking Block Pin', 'slide_lock' => 'Slide Lock',
    'slide_lock_spring' => 'Slide Lock Spring', 'connector' => 'Connector', 'guide_rod' => 'Guide Rod',
];
$allParts = [];
foreach ($partLabels as $key => $label) {
    $allParts[] = [$key, $label];
}
$leftParts = array_slice($allParts, 0, 13);
$rightParts = array_slice($allParts, 13);

$defaultSignatories = [
    'inspected' => ['name' => 'Rennan F. Maglasang Jr', 'rank' => 'Cpl (OS) PA', 'position' => 'Armaments NCO'],
    'witnessed' => ['name' => 'Marcelito H. Anino', 'rank' => 'MAJ (QMS) PA', 'position' => '901BDE, 9ID, PA'],
    'approved' => ['name' => 'Wenlie B. Enriola', 'rank' => 'CPT (OS) PA', 'position' => 'CO, Maintenance Coy'],
    'noted' => ['name' => 'Darrell P. Mariano', 'rank' => 'LTC OS (GSC) PA', 'position' => 'CO, 10FSSU, SPTCOM, PA'],
];
$signatureImages = [
    'inspected' => $signatureSource($inspection['inspected_by_sig'] ?? null, 'maglasang.png'),
    'witnessed' => $signatureSource($inspection['witnessed_by_sig'] ?? null, 'anino.png'),
    'approved' => $signatureSource($inspection['approved_by_sig'] ?? null, 'enriola.png'),
    'noted' => $signatureSource($inspection['noted_by_sig'] ?? null, 'mariano.png'),
];
$fullName = trim(
    ($p['last_name'] ?? '') . ', ' . ($p['first_name'] ?? '') . ' '
    . (!empty($p['middle_name']) ? strtoupper(substr((string) $p['middle_name'], 0, 1)) . '.' : '')
);
$status = strtolower(trim((string) ($inspection['status'] ?? 'pending')));
$partResults = array_map(static fn (string $key): string => (string) ($inspection[$key] ?? 'serviceable'), array_keys($partLabels));
$hasUnserviceable = in_array('unserviceable', $partResults, true);
$hasRepair = (bool) array_intersect(['repair', 'replace', 'missing', 'damaged'], $partResults);
$isServiceable = $status === 'approved' && !$hasUnserviceable && !$hasRepair;
$isNeedsRepair = !$hasUnserviceable && $hasRepair;
$isUnserviceable = $hasUnserviceable;
$dateToday = $formatDate($inspection['inspected_at'] ?? null, date('d F Y'));
$dateApproved = $formatDate($inspection['inspected_at'] ?? null, '-');
$nextRenewal = $formatDate(
    ($p['date_of_validity'] ?? '') ?: (($inspection['next_renewal_date'] ?? '') ?: null),
    '-'
);
$logo1Data = $imageData($publicImage('logo1.png'));
$logo2Data = $imageData($publicImage('logo2.png'));
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: Arial, sans-serif;
      font-size: 9px;
      color: #000;
      background: #fff;
      padding: 14px 18px;
    }

    .header-table { width:100%; border-collapse:collapse; margin-bottom:4px; }
    .header-table td { vertical-align:middle; }
    .header-logo { width:54px; height:54px; }
    .header-center { text-align:center; padding:0 8px; }
    .header-center p { font-size:8.5px; font-weight:400; line-height:1.55; }
    .header-center p.bold { font-weight:700; font-size:9.5px; }

    .doc-title {
      text-align:center;
      font-size:11.5px;
      font-weight:900;
      text-decoration:underline;
      margin: 7px 0 7px;
      letter-spacing:.02em;
    }

    .info-table { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .info-table td { font-size:9px; padding:2px 4px; vertical-align:middle; }
    .info-lbl { font-weight:700; width:125px; white-space:nowrap; }
    .info-colon { width:10px; text-align:center; }
    .info-val { border-bottom:1px solid #000; }
    .info-spacer { width:14px; }

    .section-header {
      border: 1.5px solid #000;
      font-weight:700;
      font-size:9px;
      padding: 3px 6px;
      margin: 5px 0 4px;
      background: #fff;
      color: #000;
    }

    .personnel-table { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .personnel-table td { font-size:9px; padding:2px 4px; vertical-align:middle; }
    .p-lbl { font-weight:700; width:110px; white-space:nowrap; }
    .p-colon { width:10px; text-align:center; }
    .p-val { border-bottom:1px solid #555; }
    .p-spacer { width:14px; }

    .checklist-wrap { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .checklist-wrap td.half       { width:50%; vertical-align:top; }
    .checklist-wrap td.half-right { width:50%; vertical-align:top; padding-left:5px; }

    .cl { width:100%; border-collapse:collapse; font-size:8px; }
    .cl th {
      background:#e0e0e0;
      border:1px solid #999;
      padding:3px 2px;
      font-size:7.5px;
      text-align:center;
      font-weight:700;
      line-height:1.3;
    }
    .cl th.th-item { text-align:left; padding-left:4px; }
    .cl td { border:1px solid #bbb; padding:2.5px 3px; vertical-align:middle; }
    .cl td.td-num  { text-align:right; width:16px; color:#333; padding-right:3px; }
    .cl td.td-item { text-align:left; font-size:7.8px; }
    .cl td.td-mark { text-align:center; width:30px; }

    .mark-ok    { color:#22a722; font-weight:900; font-size:10px; font-family:Arial; line-height:1; }
    .mark-empty { color:#aaa;    font-weight:400; font-size:9px;  line-height:1; }
    .mark-xx    { color:#cc0000; font-weight:700; font-size:8px;  line-height:1; }

    .remarks-table { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .remarks-table td { font-size:9px; padding:2px 4px; }
    .remarks-lbl { font-weight:700; width:65px; white-space:nowrap; }
    .remarks-val { color:#22a722; font-weight:700; border-bottom:1px solid #000; }

    .sig-table { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .sig-table td {
      width:25%; vertical-align:top; text-align:center;
      padding:4px 5px; border:1px solid #bbb;
    }
    .sig-role {
      font-weight:700; font-size:8px; text-transform:uppercase;
      border-bottom:1px solid #bbb; padding-bottom:2px; margin-bottom:3px;
    }
    .sig-space { height:50px; }
    .sig-img   { max-height:50px; max-width:110px; display:block; margin:0 auto; }
    .sig-name  {
      font-weight:700; font-size:8.5px; text-transform:uppercase;
      border-bottom:1px solid #000; padding-bottom:1px; margin-bottom:2px;
    }
    .sig-sub { font-size:8px; line-height:1.45; }

    .final-table { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .final-table td { border:1px solid #999; padding:5px 8px; vertical-align:top; }
    .final-title {
      font-weight:700; font-size:8.5px; text-transform:uppercase;
      display:block; border-bottom:1px solid #ccc;
      padding-bottom:3px; margin-bottom:5px;
    }

    .chk-row { width:100%; border-collapse:collapse; margin-bottom:4px; }
    .chk-row td { vertical-align:middle; padding:1px 3px; font-size:8.5px; }
    .chk-cell { width:14px; }

    .chk-box {
      display:inline-block;
      width:11px; height:11px;
      border:1.5px solid #333;
      background:#fff;
      font-size:0px;
    }
    .chk-green {
      display:inline-block;
      width:11px; height:11px;
      border:1.5px solid #22a722;
      background:#22a722;
      color:#fff;
      font-size:9px; font-weight:900; font-family:Arial;
      text-align:center; line-height:10px;
    }
    .chk-red {
      display:inline-block;
      width:11px; height:11px;
      border:1.5px solid #cc0000;
      background:#cc0000;
      color:#fff;
      font-size:9px; font-weight:900; font-family:Arial;
      text-align:center; line-height:10px;
    }

    .date-row { width:100%; border-collapse:collapse; margin-bottom:5px; }
    .date-row td { vertical-align:top; padding:2px 3px; font-size:8.5px; }
    .date-icon { width:16px; font-weight:700; font-size:9px; }
    .date-lbl  { font-weight:700; font-size:8px; display:block; }
    .date-val  { font-size:8.5px; display:block; }

    .footer {
      text-align:center; font-size:7.5px; color:#555;
      font-style:italic; border-top:1px solid #ccc;
      padding-top:4px; margin-top:4px;
    }

    .print-actions { position:fixed; top:12px; right:12px; z-index:10; }
    .print-actions button {
      border:0; border-radius:6px; padding:9px 14px; cursor:pointer;
      background:#166534; color:#fff; font-size:12px; font-weight:700;
    }

    @page { margin:0; size: A4 portrait; }
    @media print { .print-actions { display:none; } }
  </style>
</head>
<body>

  <div class="print-actions"><button type="button" onclick="window.print()">Print / Save as PDF</button></div>

  
  <table class="header-table">
    <tr>
      <td style="width:60px; text-align:left; vertical-align:middle;">
        <?php if($logo1Data): ?>
          <img class="header-logo" src="<?= $escape($logo1Data) ?>" alt="4ID Logo">
        <?php endif; ?>
      </td>
      <td class="header-center">
        <p>REPUBLIC OF THE PHILIPPINES</p>
        <p>ARMED FORCES OF THE PHILIPPINES</p>
        <p>PHILIPPINE ARMY</p>
        <p class="bold">4TH INFANTRY (DIAMOND) DIVISION, PA</p>
        <p class="bold">10TH FIELD PROPERTY ACCOUNTABILITY OFFICE (FPAO)</p>
      </td>
      <td style="width:60px; text-align:right; vertical-align:middle;">
        <?php if($logo2Data): ?>
          <img class="header-logo" src="<?= $escape($logo2Data) ?>" alt="FPAO Logo">
        <?php endif; ?>
      </td>
    </tr>
  </table>

  
  <div class="doc-title">
    INSPECTION REPORT OF SERVICEABLE AND UNSERVICEABLE FIREARMS
  </div>

  
  <table class="info-table">
    <tr>
      <td class="info-lbl">NOMENCLATURE</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($p['pistol_nomenclature'] ?? '') ?></td>
      <td class="info-spacer"></td>
      <td class="info-lbl">MAKE / MODEL</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($p['pistol_type'] ?? $p['pistol_nomenclature'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="info-lbl">UNIT</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($p['unit'] ?? '') ?></td>
      <td class="info-spacer"></td>
      <td class="info-lbl">PISTOL SERIAL NUMBER</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($p['pistol_serial_number'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="info-lbl">SERIAL NUMBER (AFP)</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($p['afp_serial_number'] ?? '') ?></td>
      <td class="info-spacer"></td>
      <td class="info-lbl">DATE INSPECTED</td>
      <td class="info-colon">:</td>
      <td class="info-val"><?= $escape($dateToday) ?></td>
    </tr>
  </table>

  
  <div class="section-header">PERSONNEL INFORMATION</div>

  <table class="personnel-table">
    <tr>
      <td class="p-lbl">NAME</td>
      <td class="p-colon">:</td>
      <td class="p-val"><?= $escape($fullName) ?></td>
      <td class="p-spacer"></td>
      <td class="p-lbl">ORGANIZATION / UNIT</td>
      <td class="p-colon">:</td>
      <td class="p-val"><?= $escape($p['unit'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="p-lbl">RANK</td>
      <td class="p-colon">:</td>
      <td class="p-val"><?= $escape($p['rank'] ?? '') ?></td>
      <td class="p-spacer"></td>
      <td class="p-lbl">EMAIL</td>
      <td class="p-colon">:</td>
      <td class="p-val"><?= $escape($p['email'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="p-lbl">DATE OF BIRTH</td>
      <td class="p-colon">:</td>
      <td class="p-val">
        <?= $escape($formatDate($p['date_of_birth'] ?? null)) ?>

      </td>
      <td class="p-spacer"></td>
      <td class="p-lbl">AFP SERIAL #</td>
      <td class="p-colon">:</td>
      <td class="p-val"><?= $escape($p['afp_serial_number'] ?? '') ?></td>
    </tr>
  </table>

  
  <table class="checklist-wrap">
    <tr>
      
      <td class="half">
        <table class="cl">
          <thead>
            <tr>
              <th class="th-item" colspan="2">ITEMS TO INSPECT</th>
              <th>(/) SERVICEABLE</th>
              <th>(XX) REPAIR (N/A)</th>
              <th>(XXX) REPLACE (O) MISSING</th>
              <th>(D) DAMAGE</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leftParts as $i => [$key, $label]): ?>
              <?php $val = $inspection[$key] ?? 'serviceable'; ?>
              <tr>
                <td class="td-num"><?= $escape($i + 1) ?>.</td>
                <td class="td-item"><?= $escape($label) ?></td>
                <td class="td-mark">
                  <?php if($val === 'serviceable'): ?>
                    <span class="mark-ok">V</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if(in_array($val, ['repair','unserviceable'])): ?>
                    <span class="mark-xx">XX</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if(in_array($val, ['replace','missing'])): ?>
                    <span class="mark-xx">XXX</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if($val === 'damaged'): ?>
                    <span class="mark-xx">D</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </td>

      
      <td class="half-right">
        <table class="cl">
          <thead>
            <tr>
              <th class="th-item" colspan="2">ITEMS TO INSPECT</th>
              <th>(/) SERVICEABLE</th>
              <th>(XX) REPAIR (N/A)</th>
              <th>(XXX) REPLACE (O) MISSING</th>
              <th>(D) DAMAGE</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rightParts as $i => [$key, $label]): ?>
              <?php $val = $inspection[$key] ?? 'serviceable'; ?>
              <tr>
                <td class="td-num"><?= $escape($i + 14) ?>.</td>
                <td class="td-item"><?= $escape($label) ?></td>
                <td class="td-mark">
                  <?php if($val === 'serviceable'): ?>
                    <span class="mark-ok">V</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if(in_array($val, ['repair','unserviceable'])): ?>
                    <span class="mark-xx">XX</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if(in_array($val, ['replace','missing'])): ?>
                    <span class="mark-xx">XXX</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
                <td class="td-mark">
                  <?php if($val === 'damaged'): ?>
                    <span class="mark-xx">D</span>
                  <?php else: ?>
                    <span class="mark-empty">O</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </td>
    </tr>
  </table>

  
  <table class="remarks-table">
    <tr>
      <td class="remarks-lbl">REMARKS :</td>
      <td class="remarks-val">
        <?= $escape($inspection['remarks'] ?? 'Firearm is Serviceable.') ?>

      </td>
    </tr>
  </table>

  
  <table class="sig-table">
    <tr>
      <td>
        <div class="sig-role">INSPECTED BY:</div>
        <?php if($signatureImages['inspected']): ?>
          <img class="sig-img" src="<?= $escape($signatureImages['inspected']) ?>" alt="Inspector signature">
        <?php else: ?>
          <div class="sig-space"></div>
        <?php endif; ?>
        <div class="sig-name"><?= $escape(($inspection['inspected_by_name'] ?? '') ?: $defaultSignatories['inspected']['name']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['inspected_by_rank'] ?? '') ?: $defaultSignatories['inspected']['rank']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['inspected_by_position'] ?? '') ?: $defaultSignatories['inspected']['position']) ?></div>
      </td>
      <td>
        <div class="sig-role">WITNESSED BY:</div>
        <?php if($signatureImages['witnessed']): ?>
          <img class="sig-img" src="<?= $escape($signatureImages['witnessed']) ?>" alt="Witness signature">
        <?php else: ?>
          <div class="sig-space"></div>
        <?php endif; ?>
        <div class="sig-name"><?= $escape(($inspection['witnessed_by_name'] ?? '') ?: $defaultSignatories['witnessed']['name']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['witnessed_by_rank'] ?? '') ?: $defaultSignatories['witnessed']['rank']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['witnessed_by_position'] ?? '') ?: $defaultSignatories['witnessed']['position']) ?></div>
      </td>
      <td>
        <div class="sig-role">APPROVED BY:</div>
        <?php if($signatureImages['approved']): ?>
          <img class="sig-img" src="<?= $escape($signatureImages['approved']) ?>" alt="Approver signature">
        <?php else: ?>
          <div class="sig-space"></div>
        <?php endif; ?>
        <div class="sig-name"><?= $escape(($inspection['approved_by_name'] ?? '') ?: $defaultSignatories['approved']['name']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['approved_by_rank'] ?? '') ?: $defaultSignatories['approved']['rank']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['approved_by_position'] ?? '') ?: $defaultSignatories['approved']['position']) ?></div>
      </td>
      <td>
        <div class="sig-role">NOTED BY:</div>
        <?php if($signatureImages['noted']): ?>
          <img class="sig-img" src="<?= $escape($signatureImages['noted']) ?>" alt="Noting officer signature">
        <?php else: ?>
          <div class="sig-space"></div>
        <?php endif; ?>
        <div class="sig-name"><?= $escape(($inspection['noted_by_name'] ?? '') ?: $defaultSignatories['noted']['name']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['noted_by_rank'] ?? '') ?: $defaultSignatories['noted']['rank']) ?></div>
        <div class="sig-sub"><?= $escape(($inspection['noted_by_position'] ?? '') ?: $defaultSignatories['noted']['position']) ?></div>
      </td>
    </tr>
  </table>

  
  <table class="final-table">
    <tr>
      <td style="width:42%;">
        <span class="final-title">FINAL INSPECTION STATUS:</span>

        <table class="chk-row">
          <tr>
            <td class="chk-cell">
              <?php if($isServiceable): ?>
                <span class="chk-green">V</span>
              <?php else: ?>
                <span class="chk-box"></span>
              <?php endif; ?>
            </td>
            <td style="font-weight:<?= $escape($isServiceable ? '700' : '400') ?>;
                       color:<?= $escape($isServiceable ? '#22a722' : '#000') ?>;
                       font-size:8.5px;">
              SERVICEABLE
            </td>
          </tr>
        </table>

        <table class="chk-row">
          <tr>
            <td class="chk-cell">
              <?php if($isNeedsRepair): ?>
                <span class="chk-red">V</span>
              <?php else: ?>
                <span class="chk-box"></span>
              <?php endif; ?>
            </td>
            <td style="font-size:8.5px;">REQUIRES REPAIR</td>
          </tr>
        </table>

        <table class="chk-row">
          <tr>
            <td class="chk-cell">
              <?php if($isUnserviceable): ?>
                <span class="chk-red">V</span>
              <?php else: ?>
                <span class="chk-box"></span>
              <?php endif; ?>
            </td>
            <td style="font-size:8.5px;">UNSERVICEABLE</td>
          </tr>
        </table>
      </td>

      <td style="width:58%;">
        <table class="date-row">
          <tr>
            <td class="date-icon">[*]</td>
            <td>
              <span class="date-lbl">DATE APPROVED:</span>
              <span class="date-val"><?= $escape($dateApproved) ?></span>
            </td>
          </tr>
        </table>
        <table class="date-row">
          <tr>
            <td class="date-icon">[*]</td>
            <td>
              <span class="date-lbl">NEXT RENEWAL DATE:</span>
              <span class="date-val"><?= $escape($nextRenewal) ?></span>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  
  <div class="footer">
    NOTE: This document is digitally generated and valid without handwritten entries.<br>
    This serves as an official record of inspection.
  </div>

  <script>window.addEventListener('load', function () { window.setTimeout(function () { window.print(); }, 250); });</script>

</body>
</html>
