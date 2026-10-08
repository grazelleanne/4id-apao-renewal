<?php
declare(strict_types=1);
namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use PDO;
use PDOException;
use Throwable;
use Exception;
use App\Support\PdfReport;

final class ActionOtpService
{
    public static function require_action_otp(array $user, string $action, array $input): void
    {
        $code = $input['otp_code'] ?? '';
        $recipientEmail = $action === 'create-user' ? strtolower(trim((string) ($input['username'] ?? ''))) : $user['email'];
        $recipientName = $action === 'create-user' ? trim((string) ($input['fullName'] ?? '')) : $user['name'];
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Enter a valid email for the new account.'], 422);
        }
        $deliveryMessage = $action === 'create-user'
            ? 'A verification code was sent to the new user at ' . $recipientEmail . '. Enter that code to complete account creation.'
            : 'A verification code was sent to your account email.';
        unset($input['otp_code'], $input['_token'], $input['_csrf']);
        ksort($input);
        $binding = hash_hmac('sha256', json_encode([$user['id'], $user['session_version'], $action, $input], JSON_THROW_ON_ERROR), csrf_token());
        $challenge = \App\Support\SessionState::$data['action_otp'] ?? null;
        if ($code !== '') {
            if (!$challenge || $challenge['expires'] <= time() || !hash_equals($challenge['binding'], $binding)) {
                unset(\App\Support\SessionState::$data['action_otp']);
                json_response(['success' => false, 'message' => 'Verification expired or details changed. Submit again for a new code.'], 422);
            }
            \App\Support\SessionState::$data['action_otp']['attempts']++;
            if (!is_string($code) || !preg_match('/^\d{6}$/', $code) || !password_verify($code, $challenge['hash'])) {
                $locked = \App\Support\SessionState::$data['action_otp']['attempts'] >= 5;
                if ($locked) unset(\App\Support\SessionState::$data['action_otp']);
                json_response(['success' => false, 'message' => $locked ? 'Too many incorrect codes. Try again later.' : 'Incorrect verification code. Submit again and enter the code.'], $locked ? 429 : 422);
            }
            unset(\App\Support\SessionState::$data['action_otp']);
            return;
        }
        if ($challenge && $challenge['expires'] > time() && hash_equals($challenge['binding'], $binding)) {
            json_response(['success' => true, 'otpRequired' => true, 'message' => $action === 'create-user'
                ? 'Enter the code already sent to the new user at ' . $recipientEmail . '.'
                : 'Enter the code already sent to your account email.']);
        }
        if (!rate_limit('action-otp:' . $user['id'], 5, 900)) {
            json_response(['success' => false, 'message' => 'Too many verification emails. Try again later.'], 429);
        }
        unset(\App\Support\SessionState::$data['action_otp']);
        $code = (string) random_int(100000, 999999);
        try {
            \App\Services\BrevoService::brevo_send_transactional_email($recipientEmail, $recipientName, 'APAO account verification',
                '<p>Confirm your ' . ($action === 'create-user' ? 'user-account creation' : 'password change')
                . ' with this code:</p><p><strong>' . $code . '</strong></p><p>Expires in 5 minutes. Do not share this code.</p>');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            json_response(['success' => false, 'message' => 'Unable to send verification email. Try again later.'], 502);
        }
        \App\Support\SessionState::$data['action_otp'] = ['binding' => $binding, 'hash' => password_hash($code, PASSWORD_DEFAULT), 'expires' => time() + 300, 'attempts' => 0];
        json_response(['success' => true, 'otpRequired' => true, 'message' => $deliveryMessage]);
    }

}
