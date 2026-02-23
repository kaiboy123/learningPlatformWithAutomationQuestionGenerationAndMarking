import sys
import pymysql
from PIL import Image, ImageDraw, ImageFont
from datetime import datetime
import openai
import os

# Database configuration
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASSWORD = ''
DB_NAME = 'learningplatform'

# OpenAI API Key
openai.api_key = 'xxx'

# Validate command-line arguments
if len(sys.argv) != 3:
    print("Usage: generatecert.py <LearnerID> <CourseID>")
    sys.exit(1)

learner_id = sys.argv[1]
course_id = sys.argv[2]

try:
    # Connect to the database
    conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME)
    cursor = conn.cursor()

    # Fetch learner details
    cursor.execute("SELECT LearnerName FROM learner WHERE LearnerID = %s", (learner_id,))
    learner = cursor.fetchone()

    if not learner:
        print("Learner not found")
        sys.exit(1)

    learner_name = learner[0]

    # Fetch course details
    cursor.execute("SELECT CourseTitle, CourseDescription FROM course WHERE CourseID = %s", (course_id,))
    course = cursor.fetchone()

    if not course:
        print("Course not found")
        sys.exit(1)

    course_title, course_description = course

    # Generate a certificate description using OpenAI
    prompt = (
        f"Generate a congratulatory certificate text for a course titled '{course_title}'. "
        f"Include details about the learner's completion and emphasize the skills gained in the course. "
        f"Here is the course description for reference: {course_description}."
    )

    response = openai.Completion.create(
        engine="text-davinci-003",
        prompt=prompt,
        max_tokens=150,
        temperature=0.7
    )
    achievement_description = response['choices'][0]['text'].strip()

    # Prepare certificate details
    achievement_title = "Certificate of Completion"
    achievement_date = datetime.now().strftime("%Y-%m-%d")
    cert_file_path = f"certificates/certificate_{learner_id}_{course_id}.png"

    # Check if certificate already exists
    cursor.execute("SELECT * FROM achievements WHERE LearnerID = %s AND CourseID = %s", (learner_id, course_id))
    if cursor.fetchone():
        print("Certificate already exists")
        sys.exit(0)

    # Generate certificate image
    if not os.path.exists("certificates"):
        os.makedirs("certificates")

    image = Image.new('RGB', (800, 600), color=(255, 255, 255))
    draw = ImageDraw.Draw(image)
    font_title = ImageFont.truetype("arial.ttf", 40)
    font_body = ImageFont.truetype("arial.ttf", 20)

    draw.text((200, 150), "Certificate of Completion", font=font_title, fill=(0, 0, 0))
    draw.text((150, 250), f"Presented to: {learner_name}", font=font_body, fill=(0, 0, 0))
    draw.text((150, 300), f"For successfully completing: {course_title}", font=font_body, fill=(0, 0, 0))
    draw.text((150, 400), f"Date: {achievement_date}", font=font_body, fill=(0, 0, 0))
    draw.text((150, 450), achievement_description, font=font_body, fill=(0, 0, 0), spacing=4)

    image.save(cert_file_path)

    # Insert achievement into database
    cursor.execute("""
        INSERT INTO achievements (LearnerID, CourseID, AchievementTitle, AchievementDescription, AchievementDate, AchievementImage)
        VALUES (%s, %s, %s, %s, %s, %s)
    """, (learner_id, course_id, achievement_title, achievement_description, achievement_date, cert_file_path))
    conn.commit()

    print("Certificate generated and saved to database")

except Exception as e:
    print(f"Error: {e}")

finally:
    cursor.close()
    conn.close()
