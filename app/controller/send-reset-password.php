<?php
require 'C:\xampp\htdocs\new\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\new\phpmailer\src\SMTP.php';
require 'C:\xampp\htdocs\new\phpmailer\src\Exception.php';
include '../../config/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in learner table
    $sql = "SELECT LearnerID FROM learner WHERE LearnerEmail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $learner = $result->fetch_assoc();
        $learnerId = $learner['LearnerID'];

        // Generate token and expiry
        $token = bin2hex(random_bytes(16));
        $tokenHash = password_hash($token, PASSWORD_DEFAULT); // Hash the token
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes from now

        // Update the reset token and expiry in the learner table
        $updateSql = "UPDATE learner SET ResetTokenHash = ?, ResetTokenExpiresAt = ? WHERE LearnerID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sss', $tokenHash, $expiry, $learnerId);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            // Send password reset email using PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'xxx@gmail.com'; // Your Gmail
                $mail->Password = 'xxx';   // Use Your Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email setup
                $mail->setFrom('hankai0115@gmail.com', 'Online Learning');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Click <a href='http://localhost/PhpAssignmentKai/reset-password.php?token=$token'>here</a> to reset your password.</p>
                    <p>This link will expire in 30 minutes.</p>
                ";

                $mail->send();
                echo "<script>
                        alert('Reset link sent! Check your email.');
                        window.location.href = 'login.php';
                      </script>";
            } catch (Exception $e) {
                echo "<script>
                        alert('Email could not be sent. Mailer Error: {$mail->ErrorInfo}');
                      </script>";
            }
        } else {
            echo "<script>alert('Failed to update reset token. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No learner found with this email address.');</script>";
    }
}
?>
