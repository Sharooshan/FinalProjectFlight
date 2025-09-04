from flask import Flask, request, jsonify
import pandas as pd
import xgboost as xgb

app = Flask(__name__)

# Load trained XGBoost model
model = xgb.Booster()
model.load_model("C:/xampp/htdocs/Intelliflight/flask_model/xgb_trained_model.json")  # Absolute path

def duration_to_minutes(duration_str):
    """Convert HH:MM string to total minutes as int."""
    try:
        hours, minutes = duration_str.split(":")
        return int(hours) * 60 + int(minutes)
    except:
        return 0

@app.route("/")
def home():
    return "Flask API is running!"

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json  # JSON payload

    # Convert travelDuration to minutes
    if 'travelDuration' in data:
        data['travelDurationMinutes'] = duration_to_minutes(data['travelDuration'])
        del data['travelDuration']

    # Ensure boolean/int columns
    bool_cols = ['isBasicEconomy', 'isRefundable', 'isNonStop']
    for col in bool_cols:
        if col in data:
            data[col] = int(data[col])

    # Fill in missing features with defaults
    required_features = [
        'travelDurationMinutes', 'elapsedDays', 'isBasicEconomy', 'isRefundable', 'isNonStop',
        'baseFare', 'seatsRemaining', 'totalTravelDistance', 'segmentsDistance_total',
        'segmentsDepartureTimeEpochSeconds_total', 'segmentsArrivalTimeEpochSeconds_total', 'numSegments'
    ]
    for f in required_features:
        if f not in data:
            data[f] = 0  # default value

    # Make sure features are in the same order as the model expects
    df = pd.DataFrame([data], columns=required_features)

    dmatrix = xgb.DMatrix(df)
    prediction = model.predict(dmatrix)
    predicted_fare = float(prediction[0])

    # Add 200 to the predicted fare
    predicted_fare += 200

    # Prepare response
    response = {"predicted_fare": predicted_fare}
    if "actual_fare" in data:
        actual_fare = float(data["actual_fare"])
        error = abs(actual_fare - predicted_fare)
        response["actual_fare"] = actual_fare
        response["error"] = error

    return jsonify(response)

if __name__ == "__main__":
    app.run(port=5000, debug=True)
