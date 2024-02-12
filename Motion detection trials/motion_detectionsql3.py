import cv2
import os
import time
import mysql.connector

# Connect to MySQL database
mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="motion_detection"
)

# initialize the camera
camera = cv2.VideoCapture(0)

# create the images directory if it doesn't exist
if not os.path.exists("images"):
    os.makedirs("images")

# capture the first frame as the background
background = None

# initialize variables for image capture-
capture_time = time.time() + 1000000
capture_count = 0
last_capture_time = time.time()

# loop over the frames from the camera
while True:
    # read the current frame from the camera
    ret, frame = camera.read()

    if not ret:
        print("Error reading frame from camera")
        break

    # convert the frame to grayscale and blur it
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    gray = cv2.GaussianBlur(gray, (21, 21), 0)

    # if the background is not set, initialize it to the first frame
    if background is None:
        background = gray
        continue

    # compute the absolute difference between the current frame and the background
    diff = cv2.absdiff(background, gray)

    # apply a threshold to the difference image
    thresh = cv2.threshold(diff, 25, 255, cv2.THRESH_BINARY)[1]

    # dilate the thresholded image to fill in the holes
    thresh = cv2.dilate(thresh, None, iterations=2)

    # find contours in the thresholded image
    contours, hierarchy = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    # loop over the contours
    for contour in contours:
        # if the contour is too small, ignore it
        if cv2.contourArea(contour) < 500:
            continue

        # compute the bounding box for the contour
        (x, y, w, h) = cv2.boundingRect(contour)

        # draw the bounding box on the frame
        cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
        filename = ""

        # capture images if not already capturing
        if capture_time is not None and time.time() - capture_time < 10 and capture_count < 5 and time.time() - last_capture_time >= 2:
            filename = "images/image{}_{}.jpg".format(capture_count, int(time.time()))
            cv2.imwrite(filename, frame)

            # insert image file name and capture time into MySQL database
            mycursor = mydb.cursor()
            sql = "INSERT INTO images (filename, capture_time) VALUES (%s, %s)"
            val = (filename, time.strftime('%Y-%m-%d %H:%M:%S', time.localtime()))
            mycursor.execute(sql, val)
            mydb.commit()

            capture_count += 1
            last_capture_time = time.time()

        # reset image capture variables if capturing finished
        if capture_time is not None and time.time() - capture_time >= 10:
            capture_time = time.time() + 30
            capture_count = 0

    # update capture time if motion is detected
    # update capture time if motion is detected
    if len(contours) > 0:
        capture_time = time.time()
        # Connect to database
        conn = mysql.connector.connect(host='localhost', user='root', password='', database='motion_detection')
        cursor = conn.cursor()

        # Insert image and capture time into database
        if capture_count == 5:
            for i in range(5):
                filename = "images/image{}_{}.jpg".format(i, int(time.time()))
                current_time = time.strftime('%Y-%m-%d %H:%M:%S')
                with open(filename, 'rb') as f:
                    image_data = f.read()
                insert_query = "INSERT INTO images (image_data, capture_time) VALUES (%s, %s)"
                cursor.execute(insert_query, (image_data, current_time))
                conn.commit()
            cursor.close()
            conn.close()

    # display the frame
    cv2.imshow("Motion Detection", frame)

# check for key presses
    key = cv2.waitKey(1) & 0xFF

# if the 'q' key is pressed, stop the loop
    if key == ord("q"):
        break
# release the camera and close the window
camera.release()
cv2.destroyAllWindows()
