# emergency_api.py
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import joblib
import pandas as pd
import pymysql
from typing import List, Dict, Any, Optional
import uvicorn

# Import your triage functions
try:
    from patient_triage import predict_patient_urgency, FEATURES, MODEL_FILENAME
except ImportError:
    # Define these if the import fails
    FEATURES = ['heart_rate', 'blood_pressure_systolic', 'blood_pressure_diastolic', 
                'temperature', 'oxygen_saturation', 'respiratory_rate']
    MODEL_FILENAME = 'patient_triage_model.pkl'

app = FastAPI(title="Patient Emergency API")

# Enable CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # For production, restrict this to your domain
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# --- MySQL connection settings (adjust as needed) ---
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'restorativecare',
    'port': 3307
}

# Fetch latest vitals for all patients
def fetch_patients_with_latest_vitals():
    conn = pymysql.connect(**MYSQL_CONFIG, cursorclass=pymysql.cursors.DictCursor)
    try:
        with conn.cursor() as cur:
            # Get all patients with their user info
            cur.execute("""
                SELECT p.id as patient_id, u.name, u.id as user_id, YEAR(CURDATE())-YEAR(p.dob) as age
                FROM patients p
                JOIN users u ON p.user_id = u.id
            """)
            patients = cur.fetchall()
            # For each patient, get their latest vitals
            for patient in patients:
                cur.execute("""
                    SELECT heart_rate, bp, temperature, spo2, respiratory_rate
                    FROM patient_vitals
                    WHERE patient_id = %s
                    ORDER BY logged_at DESC LIMIT 1
                """, (patient['patient_id'],))
                vitals = cur.fetchone()
                if vitals:
                    # Split bp into systolic/diastolic
                    bp_sys, bp_dia = 0, 0
                    if vitals['bp'] and '/' in vitals['bp']:
                        try:
                            bp_sys, bp_dia = map(int, vitals['bp'].split('/'))
                        except:
                            bp_sys, bp_dia = 0, 0
                    patient['vitals'] = {
                        'heart_rate': vitals['heart_rate'] or 0,
                        'blood_pressure_systolic': bp_sys,
                        'blood_pressure_diastolic': bp_dia,
                        'temperature': float(vitals['temperature']) if vitals['temperature'] is not None else 0.0,
                        'oxygen_saturation': vitals['spo2'] or 0,
                        'respiratory_rate': vitals['respiratory_rate'] or 0
                    }
                else:
                    patient['vitals'] = {
                        'heart_rate': 0,
                        'blood_pressure_systolic': 0,
                        'blood_pressure_diastolic': 0,
                        'temperature': 0.0,
                        'oxygen_saturation': 0,
                        'respiratory_rate': 0
                    }
            return patients
    finally:
        conn.close()

def load_model():
    try:
        model = joblib.load(MODEL_FILENAME)
        return model
    except:
        # Return None if model can't be loaded
        return None

@app.get("/")
def read_root():
    return {"message": "Patient Emergency API is running"}

@app.get("/patients/")
def get_patients():
    return fetch_patients_with_latest_vitals()

@app.get("/emergency-priorities/")
def get_emergency_priorities():
    model = load_model()
    patients = fetch_patients_with_latest_vitals()
    results = []
    if model is None:
        for patient in patients:
            vitals = patient["vitals"]
            score = 0
            if vitals["heart_rate"] > 100 or vitals["heart_rate"] < 60:
                score += 20
            if vitals["blood_pressure_systolic"] > 160 or vitals["blood_pressure_systolic"] < 90:
                score += 20
            if vitals["blood_pressure_diastolic"] > 90 or vitals["blood_pressure_diastolic"] < 60:
                score += 15
            if vitals["temperature"] > 38.5 or vitals["temperature"] < 36.0:
                score += 15
            if vitals["oxygen_saturation"] < 94:
                score += 20
            if vitals["respiratory_rate"] > 20 or vitals["respiratory_rate"] < 12:
                score += 10
            emergency = score >= 40
            results.append({
                "patient_id": patient["patient_id"],
                "name": patient["name"],
                "age": patient["age"],
                "vitals": vitals,
                "is_emergency": emergency,
                "emergency_score": score,
                "priority": "High" if score >= 60 else "Medium" if score >= 40 else "Low"
            })
    else:
        for patient in patients:
            vitals = patient["vitals"]
            prediction = predict_patient_urgency(vitals, model)
            results.append({
                "patient_id": patient["patient_id"],
                "name": patient["name"],
                "age": patient["age"],
                "vitals": vitals,
                "is_emergency": prediction["is_emergency"],
                "emergency_score": int(prediction["probability_emergency"] * 100),
                "priority": "High" if prediction["probability_emergency"] > 0.7 else 
                           "Medium" if prediction["probability_emergency"] > 0.4 else "Low"
            })
    results.sort(key=lambda x: x["emergency_score"], reverse=True)
    return results

if __name__ == "__main__":
    uvicorn.run("emergency_api:app", host="127.0.0.1", port=8000, reload=True)