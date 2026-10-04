<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:24px;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="background:#085041;padding:20px 24px;">
            <p style="color:#ffffff;font-size:18px;font-weight:700;margin:0;">AFP Personnel Management System</p>
            <p style="color:rgba(255,255,255,0.7);font-size:12px;margin:4px 0 0;">Official</p>
        </div>
        <div style="padding:24px;">
            <p style="font-size:14px;color:#111827;line-height:1.7;">
                <?php echo e(nl2br(e($message))); ?>

            </p>

            <?php if($personnel): ?>
            <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                <tr>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;background:#f9fafb;font-weight:600;font-size:13px;">AFP Serial #</td>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;font-size:13px;"><?php echo e($personnel->afpSerialNumber ?? '—'); ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;background:#f9fafb;font-weight:600;font-size:13px;">Nomenclature</td>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;font-size:13px;"><?php echo e($personnel->pistolNomenclature ?? '—'); ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;background:#f9fafb;font-weight:600;font-size:13px;">Status</td>
                    <td style="padding:6px 12px;border:1px solid #d1d5db;font-size:13px;"><?php echo e(strtoupper($personnel->approvedStatus ?? '—')); ?></td>
                </tr>
            </table>
            <?php endif; ?>

            <p style="font-size:12px;color:#9ca3af;margin-top:20px;">This is a system-generated email. Please do not reply.</p>
        </div>
        <div style="background:#f9fafb;padding:12px 24px;border-top:1px solid #e5e7eb;">
            <span style="font-size:11px;color:#9ca3af;">AFP-PMS &copy; Philippine Army</span>
        </div>
    </div>
</body>
</html>