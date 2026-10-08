<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>APAO License Notification</title>
</head>
<?php
    $normalizedBody = strtolower($body);
    $isExpired = str_contains($normalizedBody, 'expired');
    $isRenewed = str_contains($normalizedBody, 'renewed') || str_contains($normalizedBody, 'approved');
    $notice = $isExpired
        ? ['label' => 'ACTION REQUIRED', 'title' => 'License renewal required', 'color' => '#b42318', 'soft' => '#fef3f2', 'border' => '#fecdca']
        : ($isRenewed
            ? ['label' => 'RENEWAL APPROVED', 'title' => 'License renewal confirmed', 'color' => '#067647', 'soft' => '#ecfdf3', 'border' => '#abefc6']
            : ['label' => 'RENEWAL REMINDER', 'title' => 'License status notification', 'color' => '#b54708', 'soft' => '#fffaeb', 'border' => '#fedf89']);
?>
<body style="margin:0; padding:0; background-color:#f1f5f4; color:#1f2937; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">Important pistol license notification from the Property Accountability Office.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f1f5f4;">
        <tr><td align="center" style="padding:32px 12px;">
            <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:620px; background-color:#ffffff; border:1px solid #dbe4e1; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.08);">
                <tr><td style="height:5px; background-color:#16865e; font-size:0; line-height:0;">&nbsp;</td></tr>
                <tr><td style="padding:27px 34px 24px; background-color:#16231f;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
                        <td width="50" valign="middle"><div style="width:42px; height:42px; line-height:42px; border-radius:10px; background-color:#16865e; color:#ffffff; text-align:center; font-size:15px; font-weight:700; letter-spacing:0.5px;">PA</div></td>
                        <td valign="middle" style="padding-left:12px;"><div style="color:#ffffff; font-size:18px; font-weight:700; line-height:1.3;">Property Accountability Office</div><div style="padding-top:4px; color:#a7c8bb; font-size:12px; line-height:1.4; letter-spacing:0.3px;">10TH FIELD &bull; APAO RENEWAL SYSTEM</div></td>
                    </tr></table>
                </td></tr>
                <tr><td style="padding:32px 34px 12px;">
                    <span style="display:inline-block; padding:6px 10px; border-radius:999px; background-color:<?php echo e($notice['soft']); ?>; border:1px solid <?php echo e($notice['border']); ?>; color:<?php echo e($notice['color']); ?>; font-size:11px; line-height:1; font-weight:700; letter-spacing:0.8px;"><?php echo e($notice['label']); ?></span>
                    <h1 style="margin:16px 0 8px; color:#17231f; font-size:25px; line-height:1.25; font-weight:700;"><?php echo e($notice['title']); ?></h1>
                    <p style="margin:0; color:#66756f; font-size:14px; line-height:1.6;">Official notice for <?php echo e($personnelName); ?></p>
                </td></tr>
                <tr><td style="padding:16px 34px 30px;"><div style="padding:22px 24px; background-color:#f8faf9; border:1px solid #e1e8e5; border-left:4px solid <?php echo e($notice['color']); ?>; border-radius:8px; color:#303b37; font-size:15px; line-height:1.75; white-space:pre-line;"><?php echo e(trim($body)); ?></div></td></tr>
                <tr><td style="padding:0 34px 30px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#eef7f3; border-radius:8px;"><tr><td style="padding:16px 18px; color:#36584b; font-size:13px; line-height:1.6;"><strong style="color:#174d39;">Need assistance?</strong><br>Please coordinate directly with the Property Accountability Office regarding your renewal requirements.</td></tr></table></td></tr>
                <tr><td style="padding:21px 34px; background-color:#f8faf9; border-top:1px solid #e5ebe8; text-align:center; color:#75827d; font-size:11px; line-height:1.6;">This is an automated notification from the APAO Renewal System.<br>Please do not reply to this email.</td></tr>
            </table>
            <div style="padding:16px 12px 0; color:#8a9691; font-size:11px; line-height:1.5; text-align:center;">Philippine Army &bull; Property Accountability Office</div>
        </td></tr>
    </table>
</body>
</html>
