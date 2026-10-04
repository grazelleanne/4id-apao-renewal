<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .header { background: #1a2025; color: #33b481; padding: 24px 32px; font-size: 1.1rem; font-weight: bold; }
    .body { padding: 32px; color: #333; line-height: 1.7; }
    .otp-box { display: inline-block; background: #1a2025; color: #3ec6ff; font-size: 2.2rem; font-weight: 900; letter-spacing: 0.5em; padding: 16px 36px; border-radius: 10px; margin: 24px 0; border: 2px solid #3ec6ff; }
    .note { background: #f8fafc; border-left: 4px solid #33b481; padding: 12px 16px; border-radius: 4px; font-size: 0.88rem; color: #555; margin-top: 20px; }
    .footer { background: #f4f4f4; padding: 16px 32px; font-size: 0.78rem; color: #888; text-align: center; border-top: 1px solid #e5e5e5; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">APAO Renewal System — Password Reset Code</div>
    <div class="body">
      <p>Hello,</p>
      <p>You requested to reset your password for the <strong>APAO Renewal System</strong>. Use the one-time verification code below:</p>
      <div style="text-align:center;">
        <div class="otp-box"><?php echo e($otp); ?></div>
      </div>
      <p>Enter this code on the password reset page to continue.</p>
      <div class="note">
        ⏱ This code expires in <strong>10 minutes</strong>.<br>
        🔒 Do not share this code with anyone.
      </div>
      <p style="margin-top:20px;">If you did not request a password reset, please ignore this email — your account remains secure.</p>
      <br>
      <p>Regards,<br><strong>APAO Renewal System</strong></p>
    </div>
    <div class="footer">This is an automated message from the APAO Renewal System. Please do not reply to this email.</div>
  </div>
</body>
</html>