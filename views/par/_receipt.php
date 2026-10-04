<?php
    $firearmTotal = (float) $par->firearm_unit_cost * (int) $par->firearm_quantity;
    $ammoTotal = (float) $par->ammunition_unit_cost * (int) $par->ammunition_quantity;

    $total = $firearmTotal + $ammoTotal;
    $tax = $total * 0.0176;
    $net = $total - $tax;

    $personnel = $par->personnel;

    /*
    |--------------------------------------------------------------------------
    | SIGNATURE NORMALIZER
    |--------------------------------------------------------------------------
    | Supports:
    | - complete data:image/png;base64,...
    | - raw base64
    | - /storage/... paths
    | - http(s) URLs
    */

    $normalizeSignature = function ($signature) {
        if (!$signature) {
            return null;
        }

        $signature = trim(trim($signature), "\"'");

        if ($signature === '') {
            return null;
        }

        if (
            str_starts_with($signature, 'data:image/') ||
            str_starts_with($signature, 'http://') ||
            str_starts_with($signature, 'https://') ||
            str_starts_with($signature, '/')
        ) {
            return $signature;
        }

        if (
            str_starts_with($signature, 'storage/') ||
            str_starts_with($signature, 'images/')
        ) {
            return '/' . $signature;
        }

        return 'data:image/png;base64,' . preg_replace('/\s+/', '', $signature);
    };

    /*
    |--------------------------------------------------------------------------
    | RECEIVED BY SIGNATURE
    |--------------------------------------------------------------------------
    | Priority:
    | 1. PAR receiver signature
    | 2. Personnel signature captured during New Registration
    */

    $receivedSignatureSrc = $normalizeSignature(
        $par->receiver_signature
            ?: optional($personnel)->signature
    );

    /*
    |--------------------------------------------------------------------------
    | APPROVED BY / ISSUED BY SIGNATURES
    |--------------------------------------------------------------------------
    */

    $localSignature = function ($fileName) {
        $path = public_path('images/' . $fileName);
        if (!is_file($path)) return null;
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    $approvedSignatureSrc = $normalizeSignature($par->approved_by_signature)
        ?: $localSignature('SINGUEO EVAGELINE.png');

    $issuedSignatureSrc = $normalizeSignature($par->issued_by_signature)
        ?: $localSignature('ROSEMARIE VILBAR.png');

    $items = $par->equipment_items ?: [
        '4 pcs Back Straps',
        '4 pcs Magazine (17 rds Cap)',
        '1 set Cleaning Kit',
        '1 pc Speed Loader',
        '1 pc User’s Manual',
        '1 pc Gun Case',
        '1 pc Holster w/Hanger',
        '1 pc Magazine Pouch 3 magazine Capacity',
    ];

    $logoPath = public_path('images/logo.png');

    $logoData = is_file($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : '';

    /*
    |--------------------------------------------------------------------------
    | FIREARM MAKE / MODEL
    |--------------------------------------------------------------------------
    */

    $lastSegment = trim(
        collect(explode(',', $par->firearm))->last()
    );

    $segmentParts = preg_split('/\s+/', $lastSegment);

    $model = count($segmentParts) > 1
        ? array_pop($segmentParts)
        : '';

    $make = implode(' ', $segmentParts) ?: $lastSegment;

    /*
    |--------------------------------------------------------------------------
    | FOOTER LOGOS
    |--------------------------------------------------------------------------
    */

    $footerLogos = [
        'pgs'  => public_path('images/footer/pgs.png'),
        'seal' => public_path('images/footer/seal.png'),
        'ac'   => public_path('images/footer/ac.png'),
        'atr'  => public_path('images/footer/atr.png'),
    ];

    $footerLogoData = [];

    foreach ($footerLogos as $key => $path) {
        $footerLogoData[$key] = is_file($path)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($path))
            : null;
    }
?>

<p class="par-number">
    <strong>PAR No.:</strong>
    <span><?php echo e($par->par_number); ?></span>
</p>

<?php if($logoData): ?>
    <img class="par-watermark" src="<?php echo e($logoData); ?>" alt="">
<?php endif; ?>

<table class="par-items">
    <thead>
        <tr>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Description</th>
            <th>SERIAL NUMBER</th>
            <th>UNIT COST</th>
        </tr>
    </thead>

    <tbody>
        <tr class="main-item">
            <td><?php echo e($par->firearm_quantity); ?></td>
            <td>ea</td>

            <td class="description">
                <?php echo e($par->firearm); ?> with<br>
                the following accessories:

                <ul>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $equipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($equipment); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <?php if($par->remarks): ?>
                    <div class="remarks">
                        <?php echo e($par->remarks); ?>

                    </div>
                <?php endif; ?>
            </td>

            <td>
                <?php echo e($par->firearm_serial_number ?: '—'); ?>

            </td>

            <td>
                ₱ <?php echo e(number_format((float) $par->firearm_unit_cost, 2)); ?>

            </td>
        </tr>

        <?php if($par->ammunition_quantity > 0): ?>
            <tr>
                <td><?php echo e($par->ammunition_quantity); ?></td>
                <td>rds</td>

                <td class="description">
                    Ctg. 9mm, Ball
                    (₱<?php echo e(number_format((float) $par->ammunition_unit_cost, 2)); ?>/rd)
                </td>

                <td>—</td>

                <td>
                    ₱ <?php echo e(number_format($ammoTotal, 2)); ?>

                </td>
            </tr>
        <?php endif; ?>

        <tr class="total">
            <td colspan="4">TOTAL</td>
            <td>₱ <?php echo e(number_format($total, 2)); ?></td>
        </tr>

        <tr class="total">
            <td colspan="4">LESS: Withholding Tax (1.76%)</td>
            <td>₱ <?php echo e(number_format($tax, 2)); ?></td>
        </tr>

        <tr class="total">
            <td colspan="4">NET TOTAL</td>
            <td>₱ <?php echo e(number_format($net, 2)); ?></td>
        </tr>
    </tbody>
</table>

<table class="par-details">
    <tr>
        <td>
            <p>
                <strong>Make:</strong>
                <span><?php echo e(strtoupper($make)); ?></span>
            </p>

            <p>
                <strong>Model:</strong>
                <span><?php echo e($par->firearm); ?></span>
            </p>

            <p>
                <strong>Serial Number:</strong>
                <span><?php echo e($par->firearm_serial_number ?: '—'); ?></span>
            </p>
        </td>

        <td class="approval">
            <strong>APPROVED:</strong>

            <div class="signature-space">
                <?php if($approvedSignatureSrc): ?>
                    <img
                        src="<?php echo e($approvedSignatureSrc); ?>"
                        alt="Approved signature"
                    >
                <?php endif; ?>
            </div>

            <b>
                <?php echo e(strtoupper($par->approved_by ?: 'MS EVANGELINE M SINGUEO, Ph.D.')); ?>

            </b>

            <small>
                Chief APAO, PA
            </small>

            <p>
                Date Approved:
                <span>
                    <?php echo e($par->issued_date?->format('M d, Y')); ?>

                </span>
            </p>
        </td>
    </tr>
</table>

<table class="par-signatures">
    <tr>
        <td>
            <h3>RECEIVED BY</h3>

            <div class="receiver-signature-row">
                <span class="receiver-signature-label">
                    SIGNATURE:
                </span>

                <span class="signature-line">
                    <?php if($receivedSignatureSrc): ?>
                        <img
                            src="<?php echo e($receivedSignatureSrc); ?>"
                            alt="Receiver signature"
                        >
                    <?php endif; ?>
                </span>
            </div>

            <b>
                <?php echo e(strtoupper(
                    trim(
                        (optional($personnel)->rank ? optional($personnel)->rank . ' ' : '') .
                        (optional($personnel)->full_name ?? '')
                    )
                )); ?>

            </b>

            <small>
                (RANK) (NAME) (MI) (LNAME) (AFPSN) (BR of SVC)
            </small>

            <p>
                Unit Assignment:
                <strong>
                    <?php echo e($par->unit ?: '—'); ?>

                </strong>
            </p>

            <p>
                Date of Birth:
                <strong>
                    <?php echo e(optional($personnel)->date_of_birth
                            ? \Carbon\Carbon::parse($personnel->date_of_birth)->format('d F Y')
                            : '—'); ?>

                </strong>
            </p>

            <p>
                Valid up to:
                <strong>
                    <?php echo e($par->valid_until?->format('M d, Y') ?: '—'); ?>

                </strong>
            </p>
        </td>

        <td>
            <h3>ISSUED BY</h3>

            <div class="signature-space">
                <?php if($issuedSignatureSrc): ?>
                    <img
                        src="<?php echo e($issuedSignatureSrc); ?>"
                        alt="Issuer signature"
                    >
                <?php endif; ?>
            </div>

            <b>
                <?php echo e(strtoupper($par->issued_by ?: 'MS ROSEMARIE O VILBAR')); ?>

            </b>

            <small>
                Signature Over Printed Name
            </small>

            <p class="office">
                <strong>
                    Chief, PAOGS, APAO PA
                </strong>
                <br>
                Position/Office
            </p>

            <p>
                Date Issued:
                <strong>
                    <?php echo e($par->issued_date?->format('M d, Y')); ?>

                </strong>
            </p>
        </td>
    </tr>
</table>

<?php if($par->previousPar): ?>
    <p class="replacement-note">
        Replacement for <?php echo e($par->previousPar->par_number); ?>

        — <?php echo e($par->replacement_reason); ?>

    </p>
<?php endif; ?>

<footer>
    <div class="footer-badges">
        <?php if($footerLogoData['pgs']): ?>
            <img src="<?php echo e($footerLogoData['pgs']); ?>" alt="PGS">
        <?php else: ?>
            <span class="badge-fallback">PGS</span>
        <?php endif; ?>

        <?php if($footerLogoData['seal']): ?>
            <img src="<?php echo e($footerLogoData['seal']); ?>" alt="">
        <?php endif; ?>

        <?php if($footerLogoData['ac']): ?>
            <img src="<?php echo e($footerLogoData['ac']); ?>" alt="AC">
        <?php else: ?>
            <span class="badge-fallback">AC</span>
        <?php endif; ?>
    </div>

    <strong>
        HONOR.PATRIOTISM. DUTY.
    </strong>

    <div class="footer-badges footer-badges-right">
        <?php if($footerLogoData['atr']): ?>
            <img src="<?php echo e($footerLogoData['atr']); ?>" alt="atr">
        <?php else: ?>
            <span class="badge-fallback">atr</span>
        <?php endif; ?>

        <span>
            ISO 9001:2015<br>
            CERTIFIED
        </span>
    </div>
</footer>
