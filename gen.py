import csv
import random
from datetime import datetime, timedelta
import uuid

# --- Configuration ---
routes = [
    ('ATL', 'BOS'),
    ('LAX', 'JFK'),
    ('SFO', 'ORD'),
    ('MIA', 'DFW')
]

airlines = [
    ('Delta', 'DL'),
    ('American Airlines', 'AA'),
    ('United', 'UA'),
    ('JetBlue Airways', 'B6')
]

travel_classes = ['coach', 'business']

# Number of flights per route per day
flights_per_day = 3

# Date range: next 60 days
start_date = datetime.today()
end_date = start_date + timedelta(days=60)

# Output CSV file
filename = 'future_flights.csv'

# --- CSV Header ---
header = [
    'legId', 'searchDate', 'flightDate', 'startingAirport', 'destinationAirport', 
    'fareBasisCode', 'travelDuration', 'elapsedDays', 'isBasicEconomy', 'isRefundable', 
    'isNonStop', 'baseFare', 'totalFare', 'seatsRemaining', 'totalTravelDistance', 
    'segmentsDepartureTimeRaw', 'segmentsArrivalTimeRaw', 'segmentsAirlineName', 
    'segmentsAirlineCode', 'segmentsCabinCode'
]

rows = []

current_date = start_date
while current_date <= end_date:
    searchDate = current_date.strftime('%Y-%m-%d')
    for route in routes:
        origin, destination = route
        for _ in range(flights_per_day):
            flightDate = (current_date + timedelta(days=random.randint(1, 30))).strftime('%Y-%m-%d')
            duration_minutes = random.randint(90, 360)
            hours = duration_minutes // 60
            minutes = duration_minutes % 60
            travelDuration = f'PT{hours}H{minutes}M'
            elapsedDays = (datetime.strptime(flightDate, '%Y-%m-%d') - datetime.strptime(searchDate, '%Y-%m-%d')).days
            isBasicEconomy = random.choice([0,1])
            isRefundable = random.choice([0,1])
            isNonStop = random.choice([0,1])
            baseFare = round(random.uniform(100, 500), 2)
            totalFare = baseFare + round(random.uniform(20, 50), 2)
            seatsRemaining = random.randint(1, 10)
            totalTravelDistance = random.randint(200, 2000)
            dep_hour = random.randint(5, 20)
            dep_minute = random.choice([0,15,30,45])
            dep_time = datetime.strptime(flightDate, '%Y-%m-%d') + timedelta(hours=dep_hour, minutes=dep_minute)
            arr_time = dep_time + timedelta(minutes=duration_minutes)
            airline = random.choice(airlines)
            cabin = random.choice(travel_classes)

            row = [
                str(uuid.uuid4()),
                searchDate,
                flightDate,
                origin,
                destination,
                'FARE'+str(random.randint(100,999)),
                travelDuration,
                elapsedDays,
                isBasicEconomy,
                isRefundable,
                isNonStop,
                baseFare,
                totalFare,
                seatsRemaining,
                totalTravelDistance,
                dep_time.isoformat(),
                arr_time.isoformat(),
                airline[0],
                airline[1],
                cabin
            ]
            rows.append(row)
    current_date += timedelta(days=1)

# Write CSV
with open(filename, 'w', newline='') as f:
    writer = csv.writer(f)
    writer.writerow(header)
    writer.writerows(rows)

print(f'Generated {len(rows)} future flights in {filename}')
