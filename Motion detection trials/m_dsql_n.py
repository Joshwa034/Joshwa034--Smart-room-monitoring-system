import cv2
import time
import mysql.connector

# Connect to the database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="motion_detection2"
)
cursor = db.cursor()

# Set up the camera and motion detection parameters
camera = cv2.VideoCapture(0)
fgbg = cv2.createBackgroundSubtractorMOG2()
min_area = 5000
delay = 2
wait_time = 30

# Start the motion detection loop
while True:
    # Wait for a moment to allow the camera to adjust
    time.sleep(0.1)
    # Capture a frame from the camera
    ret, frame = camera.read()
    # Convert the frame to grayscale and apply background subtraction
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    fgmask = fgbg.apply(gray)
    # Apply a threshold to the background subtraction result
    thresh = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY)[1]
    # Find contours in the thresholded image
    contours, hierarchy = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    # Check if any of the contours meet the minimum area requirement
    for contour in contours:
        if cv2.contourArea(contour) > min_area:
            # Wait for the delay period
            time.sleep(delay)
            # Capture five images
            for i in range(5):
                ret, frame = camera.read()
                filename = f"image_{int(time.time())}.jpg"
                cv2.imwrite(filename, frame)
                # Store the image and its metadata in the database
                sql = f"INSERT INTO images (filename, date, time) VALUES ('{filename}', NOW(), NOW())"
                cursor.execute(sql)
                db.commit()
            # Wait for the specified waiting period
            time.sleep(wait_time)

# Release the camera and close the database connection
camera.release()
db.close()
