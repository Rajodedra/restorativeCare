# emergency_api.py
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import joblib
import pandas as pd
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

# Sample patient data for demonstration
SAMPLE_PATIENTS = [
    {"id": 101, "name": "John Smith", "age": 72, "vitals": {
        "heart_rate": 110, "blood_pressure_systolic": 165, "blood_pressure_diastolic": 95,
        "temperature": 38.7, "oxygen_saturation": 91, "respiratory_rate": 22
    }},
    {"id": 102, "name": "Maria Garcia", "age": 65, "vitals": {
        "heart_rate": 88, "blood_pressure_systolic": 130, "blood_pressure_diastolic": 85,
        "temperature": 37.2, "oxygen_saturation": 96, "respiratory_rate": 16
    }},
    {"id": 103, "name": "David Lee", "age": 45, "vitals": {
        "heart_rate": 75, "blood_pressure_systolic": 120, "blood_pressure_diastolic": 80,
        "temperature": 36.8, "oxygen_saturation": 98, "respiratory_rate": 14
    }},
    {"id": 104, "name": "Emily Johnson", "age": 31, "vitals": {
        "heart_rate": 120, "blood_pressure_systolic": 145, "blood_pressure_diastolic": 90,
        "temperature": 39.1, "oxygen_saturation": 93, "respiratory_rate": 20
    }},
    {"id": 105, "name": "Robert Williams", "age": 58, "vitals": {
        "heart_rate": 95, "blood_pressure_systolic": 150, "blood_pressure_diastolic": 95,
        "temperature": 37.5, "oxygen_saturation": 94, "respiratory_rate": 18
    }}
]

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
    return SAMPLE_PATIENTS

@app.get("/emergency-priorities/")
def get_emergency_priorities():
    model = load_model()
    
    # If model is not available, use a simplified scoring algorithm
    if model is None:
        results = []
        for patient in SAMPLE_PATIENTS:
            vitals = patient["vitals"]
            
            # Simple heuristic algorithm for emergency scoring
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
                "patient_id": patient["id"],
                "name": patient["name"],
                "age": patient["age"],
                "vitals": vitals,
                "is_emergency": emergency,
                "emergency_score": score,
                "priority": "High" if score >= 60 else "Medium" if score >= 40 else "Low"
            })
    else:
        # Use the trained model
        results = []
        for patient in SAMPLE_PATIENTS:
            vitals = patient["vitals"]
            prediction = predict_patient_urgency(vitals, model)
            
            results.append({
                "patient_id": patient["id"],
                "name": patient["name"],
                "age": patient["age"],
                "vitals": vitals,
                "is_emergency": prediction["is_emergency"],
                "emergency_score": int(prediction["probability_emergency"] * 100),
                "priority": "High" if prediction["probability_emergency"] > 0.7 else 
                           "Medium" if prediction["probability_emergency"] > 0.4 else "Low"
            })
    
    # Sort by emergency score (highest first)
    results.sort(key=lambda x: x["emergency_score"], reverse=True)
    return results

if __name__ == "__main__":
    uvicorn.run("emergency_api:app", host="127.0.0.1", port=8000, reload=True)