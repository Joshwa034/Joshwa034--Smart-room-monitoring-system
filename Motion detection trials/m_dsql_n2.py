import cv2
import time
import mysql.connector

# Connect to database
mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="motion_detection2"
)
cursor = mydb.cursor()

# Initialize camera and motion detector
cam = cv2.VideoCapture(0)
time.sleep(2)
first_frame = None
motion_detected = False

# Main loop
while True:
    ret, frame = cam.read()
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    gray = cv2.GaussianBlur(gray, (21, 21), 0)

    if first_frame is None:
        first_frame = gray
        continue

    delta_frame = cv2.absdiff(first_frame, gray)
    thresh_frame = cv2.threshold(delta_frame, 30, 255, cv2.THRESH_BINARY)[1]
    thresh_frame = cv2.dilate(thresh_frame, None, iterations=2)

    # Find contours of moving objects
    contours, _ = cv2.findContours(thresh_frame.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    for contour in contours:
        if cv2.contourArea(contour) < 10000:
            continue
        motion_detected = True

    if motion_detected:
        for i in range(5):
            # Capture image
            ret, frame = cam.read()
            filename = time.strftime("%Y%m%d-%H%M%S") + f"_{i}.jpg"
            print(filename)
            cv2.imwrite(filename, frame)

            # Store image information in database
            date = time.strftime("%Y-%m-%d")
            print(date)
            time_ = time.strftime("%H:%M:%S")
            print(time)
            sql = "INSERT INTO images (filename, date, time) VALUES (%s, %s, %s)"
            val = (filename, date, time_)
            cursor.execute(sql, val)
            mydb.commit()

            time.sleep(2)
            

        motion_detected = False

    # Wait for 30 seconds before detecting motion again
    time.sleep(30)
    
    

# Release camera and close database connection
cam.release()
cv2.destroyAllWindows()
cursor.close()
mydb.close()
