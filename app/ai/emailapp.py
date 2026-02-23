from flask import Flask, jsonify, request, redirect 
from flask_cors import CORS
import mysql.connector
import bcrypt
import secrets
from email.message import EmailMessage
import smtplib
from datetime import datetime, timedelta

# Initialize Flask app
app = Flask(__name__)
CORS(app)

# Database connection function
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="learningplatform"
    )

# Forgot password route
@app.route('/forgot-password', methods=['POST'])
def forgot_password():
    data = request.get_json()
    email = data.get('email')

    if not email:
        return jsonify({"error": "Email is required"}), 400

    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)

    cursor.execute("SELECT * FROM learner WHERE LearnerEmail = %s", (email,))
    user = cursor.fetchone()

    if user:
        # Generate token and expiry time
        token = secrets.token_urlsafe(16)
        token_expiry = datetime.now() + timedelta(minutes=30)
        token_hash = bcrypt.hashpw(token.encode(), bcrypt.gensalt()).decode()

        # Store token in the database
        cursor.execute(
            "UPDATE learner SET ResetTokenHash = %s, ResetTokenExpiresAt = %s WHERE LearnerEmail = %s",
            (token_hash, token_expiry, email)
        )
        connection.commit()

        # Send reset email
        send_reset_email(email, token)

        cursor.close()
        connection.close()

        return jsonify({"message": "A reset link has been sent to your email. Please check your inbox."}), 200
    else:
        cursor.close()
        connection.close()
        return jsonify({"error": "No account found with that email address."}), 404


@app.route('/reset-password', methods=['GET', 'POST'])
def reset_password():
    token = request.args.get('token')  # Get the token from the URL

    if request.method == 'GET':
        if not token:
            return jsonify({"error": "Token is missing"}), 400

        # Validate the token
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM learner WHERE ResetTokenHash IS NOT NULL AND ResetTokenExpiresAt > NOW()")

        user = None
        for row in cursor.fetchall():
            if bcrypt.checkpw(token.encode(), row["ResetTokenHash"].encode()):
                user = row
                break

        cursor.close()
        connection.close()

        if not user:
            return jsonify({"error": "Invalid or expired token."}), 400

        # Render reset password form with design and CSS
        return '''
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
        </head>
        <body style="font-family: Arial, sans-serif; background: #f4f4f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); max-width: 400px; width: 100%;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #333;">Reset Password</h3>
                <form method="POST" action="/reset-password?token={}">
                    <input type="password" name="password" placeholder="Enter new password" required 
                           style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    <button type="submit" 
                            style="width: 100%; padding: 10px; background: #007BFF; color: white; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
                        Reset Password
                    </button>
                </form>
                <p style="text-align: center; color: #777; margin-top: 10px; font-size: 12px;">Ensure your password is strong and secure.</p>
            </div>
        </body>
        </html>
        '''.format(token)

    if request.method == 'POST':
        try:
            # Get the new password
            data = request.form
            password = data.get('password')

            if not password:
                return jsonify({"error": "Password is required"}), 400

            # Validate the token again
            connection = get_db_connection()
            cursor = connection.cursor(dictionary=True)
            cursor.execute("SELECT * FROM learner WHERE ResetTokenHash IS NOT NULL AND ResetTokenExpiresAt > NOW()")

            user = None
            for row in cursor.fetchall():
                if bcrypt.checkpw(token.encode(), row["ResetTokenHash"].encode()):
                    user = row
                    break

            if not user:
                cursor.close()
                connection.close()
                return jsonify({"error": "Invalid or expired token."}), 400

            # Hash the new password
            hashed_password = bcrypt.hashpw(password.encode(), bcrypt.gensalt()).decode()

            # Update the password in the database and clear the token
            cursor.execute(
                "UPDATE learner SET Password = %s, ResetTokenHash = NULL, ResetTokenExpiresAt = NULL WHERE LearnerID = %s",
                (hashed_password, user['LearnerID'])
            )
            connection.commit()

            # Clean up resources
            cursor.close()
            connection.close()

            # Redirect to login.php
            return redirect('http://localhost:3000/xampp/htdocs/new/app/controller/login.php?message=Password+reset+successful', code=302)

        except Exception as e:
            # Handle exceptions and ensure resources are cleaned up
            if 'cursor' in locals():
                cursor.close()
            if 'connection' in locals():
                connection.close()
            return jsonify({"error": f"An unexpected error occurred: {str(e)}"}), 500


# Function to send reset email
def send_reset_email(email, token):
    reset_url = f"http://127.0.0.1:5000/reset-password?token={token}"
    msg = EmailMessage()
    msg.set_content(f"Click the link to reset your password: {reset_url}\nThis link expires in 30 minutes.")
    msg['Subject'] = "Password Reset Request"
    msg['From'] = "yeaphk-pm21@student.tarc.edu.my"
    msg['To'] = email

    with smtplib.SMTP('smtp.gmail.com', 587) as server:
        server.starttls()
        server.login("yeaphk-pm21@student.tarc.edu.my", "rcjl ycgg zxlg pcmb")
        server.send_message(msg)

# Run the Flask app
if __name__ == '__main__':
    app.run(debug=True)
