import pandas as pd

# Load CSV
df = pd.read_csv(r"C:/xampp/htdocs/Intelliflight/flask_model/flights.csv")

# Convert boolean columns TRUE/FALSE → 1/0
bool_cols = ['isBasicEconomy', 'isRefundable', 'isNonStop']
for col in bool_cols:
    df[col] = df[col].map({True: 1, False: 0, 'TRUE': 1, 'FALSE': 0})

# Convert dates to YYYY-MM-DD
df['searchDate'] = pd.to_datetime(df['searchDate'], errors='coerce').dt.strftime('%Y-%m-%d')
df['flightDate'] = pd.to_datetime(df['flightDate'], errors='coerce').dt.strftime('%Y-%m-%d')

# Ensure numeric fields are valid
numeric_cols = ['elapsedDays', 'baseFare', 'totalFare', 'seatsRemaining', 
                'totalTravelDistance', 'segmentsDurationInSeconds', 'segmentsDistance',
                'segmentsDepartureTimeEpochSeconds', 'segmentsArrivalTimeEpochSeconds']
for col in numeric_cols:
    df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)

# Save cleaned CSV
df.to_csv(r"C:/xampp/htdocs/Intelliflight/flask_model/flights_cleaned2.csv", index=False)
print("CSV preprocessing completed!")

