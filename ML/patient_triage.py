# patient_triage.py
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix
import joblib
import os

# Constants
FEATURES = ['heart_rate', 'blood_pressure_systolic', 'blood_pressure_diastolic', 
            'temperature', 'oxygen_saturation', 'respiratory_rate']
MODEL_FILENAME = 'patient_triage_model.pkl'

def load_data(file_path=None):
    """
    Load patient data from CSV file or generate synthetic data for testing
    
    Args:
        file_path: Path to CSV file with patient data
        
    Returns:
        DataFrame with patient data
    """
    if file_path and os.path.exists(file_path):
        return pd.read_csv(file_path)
    else:
        # Generate synthetic data for testing
        print("No data file found. Generating synthetic data for testing...")
        np.random.seed(42)
        n_samples = 10000
        
        data = {
            'patient_id': range(1, n_samples + 1),
            'heart_rate': np.random.normal(75, 15, n_samples),
            'blood_pressure_systolic': np.random.normal(120, 20, n_samples),
            'blood_pressure_diastolic': np.random.normal(80, 10, n_samples),
            'temperature': np.random.normal(37, 1, n_samples),
            'oxygen_saturation': np.random.normal(97, 3, n_samples),
            'respiratory_rate': np.random.normal(16, 4, n_samples)
        }
        
        # Generate target variable based on vital signs
        df = pd.DataFrame(data)
        
        # Define emergency conditions (simplistic medical rules for demonstration)
        emergency_conditions = (
            (df['heart_rate'] > 100) | 
            (df['heart_rate'] < 50) |
            (df['blood_pressure_systolic'] > 160) |
            (df['blood_pressure_systolic'] < 90) |
            (df['oxygen_saturation'] < 92) |
            (df['temperature'] > 39) |
            (df['temperature'] < 35) |
            (df['respiratory_rate'] > 24) |
            (df['respiratory_rate'] < 8)
        )
        
        # Add some noise to make it more realistic
        random_factor = np.random.random(n_samples) < 0.1
        df['is_emergency'] = (emergency_conditions | random_factor).astype(int)
        
        return df

def preprocess_data(data):
    """
    Preprocess the data for training
    
    Args:
        data: DataFrame with patient data
        
    Returns:
        X: Features DataFrame
        y: Target Series
    """
    # Handle missing values
    data = data.dropna(subset=FEATURES + ['is_emergency'] if 'is_emergency' in data.columns else FEATURES)
    
    # Extract features and target
    X = data[FEATURES]
    
    # If this is training data with labels
    if 'is_emergency' in data.columns:
        y = data['is_emergency']
        return X, y
    else:
        return X, None

def train_model(X, y):
    """
    Train a Random Forest model
    
    Args:
        X: Feature DataFrame
        y: Target Series
        
    Returns:
        Trained model
    """
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train Random Forest model
    model = RandomForestClassifier(
        n_estimators=100,
        max_depth=10,
        min_samples_split=5,
        min_samples_leaf=2,
        random_state=42
    )
    
    model.fit(X_train, y_train)
    
    # Evaluate model
    y_pred = model.predict(X_test)
    print("\nModel Evaluation:")
    print(classification_report(y_test, y_pred))
    print("\nConfusion Matrix:")
    print(confusion_matrix(y_test, y_pred))
    
    # Feature importance
    feature_importance = pd.DataFrame({
        'Feature': FEATURES,
        'Importance': model.feature_importances_
    }).sort_values('Importance', ascending=False)
    
    print("\nFeature Importance:")
    print(feature_importance)
    
    # Save the model
    joblib.dump(model, MODEL_FILENAME)
    print(f"\nModel saved as {MODEL_FILENAME}")
    
    return model

def predict_patient_urgency(patient_vitals, model=None):
    """
    Predict whether a patient needs emergency care
    
    Args:
        patient_vitals: Dictionary with patient vital measurements
        model: Trained model (optional, will load from file if not provided)
        
    Returns:
        Dictionary with prediction results
    """
    # Load model if not provided
    if model is None:
        if os.path.exists(MODEL_FILENAME):
            model = joblib.load(MODEL_FILENAME)
        else:
            raise FileNotFoundError(f"Model file {MODEL_FILENAME} not found. Train a model first.")
    
    # Create DataFrame from input
    input_data = pd.DataFrame([patient_vitals])
    
    # Ensure all required features are present
    missing_features = [f for f in FEATURES if f not in input_data.columns]
    if missing_features:
        raise ValueError(f"Missing vital signs: {', '.join(missing_features)}")
    
    # Make prediction
    probability = model.predict_proba(input_data[FEATURES])[0]
    is_emergency = model.predict(input_data[FEATURES])[0]
    
    # Calculate confidence score
    confidence = probability[1] if is_emergency == 1 else probability[0]
    
    # Return result
    result = {
        "is_emergency": bool(is_emergency),
        "confidence": float(confidence),
        "probability_emergency": float(probability[1]),
        "recommendation": "IMMEDIATE ATTENTION REQUIRED" if is_emergency else "Patient can wait"
    }
    
    return result