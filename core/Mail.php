<?php

namespace Core;

/**
 * Email Sending System
 */
class Mail
{
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $subject = '';
    private $body = '';
    private $htmlBody = '';
    private $attachments = [];
    private $headers = [];
    private $fromEmail = '';
    private $fromName = '';
    
    public function __construct()
    {
        // Load email settings
        $settings = new \App\Models\Setting();
        $this->fromEmail = $settings->getValue('mail_from_address', 'noreply@hoteldigilab.com');
        $this->fromName = $settings->getValue('mail_from_name', 'Hotel DigiLab');
    }
    
    /**
     * Set recipient
     */
    public function to($email, $name = null)
    {
        if (is_array($email)) {
            $this->to = array_merge($this->to, $email);
        } else {
            $this->to[] = $name ? "{$name} <{$email}>" : $email;
        }
        return $this;
    }
    
    /**
     * Set CC
     */
    public function cc($email, $name = null)
    {
        if (is_array($email)) {
            $this->cc = array_merge($this->cc, $email);
        } else {
            $this->cc[] = $name ? "{$name} <{$email}>" : $email;
        }
        return $this;
    }
    
    /**
     * Set BCC
     */
    public function bcc($email, $name = null)
    {
        if (is_array($email)) {
            $this->bcc = array_merge($this->bcc, $email);
        } else {
            $this->bcc[] = $name ? "{$name} <{$email}>" : $email;
        }
        return $this;
    }
    
    /**
     * Set from address
     */
    public function from($email, $name = null)
    {
        $this->fromEmail = $email;
        if ($name) {
            $this->fromName = $name;
        }
        return $this;
    }
    
    /**
     * Set subject
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Set plain text body
     */
    public function text($body)
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Set HTML body
     */
    public function html($body)
    {
        $this->htmlBody = $body;
        return $this;
    }
    
    /**
     * Set body from view template
     */
    public function view($template, $data = [])
    {
        $view = View::getInstance();
        $this->htmlBody = $view->render($template, $data);
        return $this;
    }
    
    /**
     * Add attachment
     */
    public function attach($filePath, $name = null)
    {
        if (file_exists($filePath)) {
            $this->attachments[] = [
                'path' => $filePath,
                'name' => $name ?: basename($filePath),
                'type' => mime_content_type($filePath)
            ];
        }
        return $this;
    }
    
    /**
     * Add custom header
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }
    
    /**
     * Send email
     */
    public function send()
    {
        if (empty($this->to) || empty($this->subject)) {
            throw new \Exception('Email recipient and subject are required');
        }
        
        // Check if we should use SMTP or PHP mail
        $settings = new \App\Models\Setting();
        $mailDriver = $settings->getValue('mail_driver', 'mail');
        
        if ($mailDriver === 'smtp') {
            return $this->sendViaSMTP();
        } else {
            return $this->sendViaPHPMail();
        }
    }
    
    /**
     * Send via PHP mail() function
     */
    private function sendViaPHPMail()
    {
        $to = implode(', ', $this->to);
        $subject = $this->subject;
        
        // Build headers
        $headers = [];
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "X-Mailer: Hotel DigiLab";
        
        if (!empty($this->cc)) {
            $headers[] = "Cc: " . implode(', ', $this->cc);
        }
        
        if (!empty($this->bcc)) {
            $headers[] = "Bcc: " . implode(', ', $this->bcc);
        }
        
        // Add custom headers
        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
        
        // Determine content type and body
        if (!empty($this->htmlBody) && !empty($this->body)) {
            // Multipart message
            $boundary = md5(time());
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
            
            $message = "--{$boundary}\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\n";
            $message .= "Content-Transfer-Encoding: 7bit\n\n";
            $message .= $this->body . "\n\n";
            
            $message .= "--{$boundary}\n";
            $message .= "Content-Type: text/html; charset=UTF-8\n";
            $message .= "Content-Transfer-Encoding: 7bit\n\n";
            $message .= $this->htmlBody . "\n\n";
            
            $message .= "--{$boundary}--";
        } elseif (!empty($this->htmlBody)) {
            // HTML only
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $message = $this->htmlBody;
        } else {
            // Plain text only
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
            $message = $this->body;
        }
        
        $headerString = implode("\r\n", $headers);
        
        return mail($to, $subject, $message, $headerString);
    }
    
    /**
     * Send via SMTP (basic implementation)
     */
    private function sendViaSMTP()
    {
        $settings = new \App\Models\Setting();
        $host = $settings->getValue('mail_host', 'localhost');
        $port = $settings->getValue('mail_port', 587);
        $username = $settings->getValue('mail_username', '');
        $password = $settings->getValue('mail_password', '');
        
        // This is a simplified SMTP implementation
        // In production, you might want to use a proper SMTP library
        
        $socket = fsockopen($host, $port, $errno, $errstr, 30);
        
        if (!$socket) {
            throw new \Exception("SMTP connection failed: {$errstr} ({$errno})");
        }
        
        // SMTP conversation
        $this->smtpCommand($socket, null, '220'); // Welcome message
        $this->smtpCommand($socket, "EHLO " . $_SERVER['SERVER_NAME'], '250');
        
        if ($username && $password) {
            $this->smtpCommand($socket, "AUTH LOGIN", '334');
            $this->smtpCommand($socket, base64_encode($username), '334');
            $this->smtpCommand($socket, base64_encode($password), '235');
        }
        
        $this->smtpCommand($socket, "MAIL FROM: <{$this->fromEmail}>", '250');
        
        foreach ($this->to as $recipient) {
            $email = $this->extractEmail($recipient);
            $this->smtpCommand($socket, "RCPT TO: <{$email}>", '250');
        }
        
        $this->smtpCommand($socket, "DATA", '354');
        
        // Send headers and body
        $message = $this->buildEmailMessage();
        $this->smtpCommand($socket, $message . "\r\n.", '250');
        
        $this->smtpCommand($socket, "QUIT", '221');
        
        fclose($socket);
        
        return true;
    }
    
    /**
     * Execute SMTP command
     */
    private function smtpCommand($socket, $command, $expectedCode)
    {
        if ($command !== null) {
            fwrite($socket, $command . "\r\n");
        }
        
        $response = fgets($socket, 512);
        $code = substr($response, 0, 3);
        
        if ($code !== $expectedCode) {
            throw new \Exception("SMTP Error: Expected {$expectedCode}, got {$code} - {$response}");
        }
        
        return $response;
    }
    
    /**
     * Build complete email message
     */
    private function buildEmailMessage()
    {
        $message = [];
        
        // Headers
        $message[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $message[] = "To: " . implode(', ', $this->to);
        
        if (!empty($this->cc)) {
            $message[] = "Cc: " . implode(', ', $this->cc);
        }
        
        $message[] = "Subject: {$this->subject}";
        $message[] = "Date: " . date('r');
        $message[] = "Message-ID: <" . md5(uniqid()) . "@" . $_SERVER['SERVER_NAME'] . ">";
        
        // Content
        if (!empty($this->htmlBody)) {
            $message[] = "MIME-Version: 1.0";
            $message[] = "Content-Type: text/html; charset=UTF-8";
            $message[] = "";
            $message[] = $this->htmlBody;
        } else {
            $message[] = "Content-Type: text/plain; charset=UTF-8";
            $message[] = "";
            $message[] = $this->body;
        }
        
        return implode("\r\n", $message);
    }
    
    /**
     * Extract email address from "Name <email>" format
     */
    private function extractEmail($recipient)
    {
        if (preg_match('/<(.+?)>/', $recipient, $matches)) {
            return $matches[1];
        }
        return $recipient;
    }
    
    /**
     * Static method to send simple email
     */
    public static function send($to, $subject, $body, $isHtml = false)
    {
        $mail = new self();
        $mail->to($to)->subject($subject);
        
        if ($isHtml) {
            $mail->html($body);
        } else {
            $mail->text($body);
        }
        
        return $mail->send();
    }
    
    /**
     * Send welcome email
     */
    public static function sendWelcome($user, $password = null)
    {
        $mail = new self();
        
        return $mail->to($user['email'], $user['namesurname'])
                   ->subject('Welcome to Hotel DigiLab')
                   ->view('emails.welcome', [
                       'user' => $user,
                       'password' => $password,
                       'loginUrl' => url('/login')
                   ])
                   ->send();
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordReset($user, $token)
    {
        $mail = new self();
        
        $resetUrl = url("/reset-password/{$token}");
        
        return $mail->to($user['email'], $user['namesurname'])
                   ->subject('Password Reset - Hotel DigiLab')
                   ->view('emails.password-reset', [
                       'user' => $user,
                       'resetUrl' => $resetUrl
                   ])
                   ->send();
    }
    
    /**
     * Send invitation email
     */
    public static function sendInvitation($email, $inviteCode, $invitedBy)
    {
        $mail = new self();
        
        $inviteUrl = url("/invite/{$inviteCode}");
        
        return $mail->to($email)
                   ->subject('Invitation to Hotel DigiLab')
                   ->view('emails.invitation', [
                       'inviteUrl' => $inviteUrl,
                       'invitedBy' => $invitedBy
                   ])
                   ->send();
    }
}
