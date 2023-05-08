import os
import numpy as np
from keras.layers import Dense, Flatten
from keras.models import Model
from keras.applications.inception_v3 import InceptionV3, preprocess_input
from tensorflow.keras.preprocessing.image import ImageDataGenerator, load_img, img_to_array
import keras
import requests
import tensorflow as tf
from flask import Flask, render_template, request, send_from_directory
import mysql.connector
import random
from datetime import datetime

app = Flask(__name__)
dir_path = os.path.dirname(os.path.realpath(__file__))
UPLOAD_FOLDER = "images"
STATIC_FOLDER = "static"

# Load model
cnn_model = tf.keras.models.load_model(STATIC_FOLDER + "/models/" + "best_model.h5")

# Connect to MySQL database
db = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="data"
)

# Create a table to store image filename, user location, prediction label, and image file
cursor = db.cursor()
cursor.execute("CREATE TABLE IF NOT EXISTS predictions (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255), location VARCHAR(255), prediction VARCHAR(255), image BLOB, status VARCHAR(255), complaint_id INT(10))")

# Preprocess an image

# home page
@app.route('/', methods=['GET', 'POST'])
def home():
    if request.method == 'POST':
        complaint_id = request.form['complaint_id']
        
        # Connect to the database and fetch the status for the customer
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="data"
        )
        cursor = db.cursor()
        cursor.execute("SELECT status FROM predictions WHERE complaint_id=%s", (complaint_id,))
        status = cursor.fetchone()[0]
        
        # Do something with the data and status
    else:
        # Display the form
        status = ''
    
    return render_template('home.html', status=status)

@app.route("/classify", methods=["POST", "GET"])
def upload_file():
    imagefile = request.files['image']
    image_path = "./images/"+ imagefile.filename
    imagefile.save(image_path)
    with open(image_path, 'rb') as f:
        image_data = f.read()
    imgage = load_img(image_path, target_size=(256, 256))
    i = img_to_array(imgage)
    i = preprocess_input(i)
    input_arr = np.array([i])
    input_arr.shape
    pred = np.argmax(cnn_model.predict(input_arr))
    if pred == 0:
        lab = "garbage"
        label="we have sucessfully received your request for cleaning garbage"
    else:
        lab = "potholes"
        label="we have sucessfully received your request for repairing"
    # Get user location from request headers
    now = datetime.now()
    complaint_id = now.strftime("%m%d") + str(np.random.randint(1000, 9999))
    
    location = request.headers.get('X-Real-IP')
    latitude = request.form.get('latitude')
    longitude = request.form.get('longitude')
    location = f"{latitude}, {longitude}"
    # Insert image filename, user location, prediction label, and image file into the database
    cursor.execute("INSERT INTO predictions (filename, location, prediction, image ,complaint_id) VALUES (%s, %s, %s, %s, %s)", (imagefile.filename, location, lab, image_data, complaint_id))
    db.commit()
    return render_template(
        "classify.html", image_file_name=imagefile.filename, label=label, complaint_id=f"Your complaint ID is {complaint_id}."
    )
# Get user location from request headers



@app.route("/classify/<filename>")
def send_file(filename):
    return send_from_directory(UPLOAD_FOLDER, filename)

if __name__== "__main__":
    app.debug = True
    app.run(debug=True)
    app.debug = True